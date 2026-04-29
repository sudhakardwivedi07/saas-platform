<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\EventLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogClientAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || $user->is_superadmin || in_array($request->method(), ['HEAD', 'OPTIONS'], true)) {
            return $response;
        }

        try {
            $routeName = optional($request->route())->getName();
            $method = strtoupper($request->method());
            $path = '/' . ltrim($request->path(), '/');
            $statusCode = $response->getStatusCode();

            if (!$this->shouldLogFunctionalAction($method, $routeName, $statusCode)) {
                return $response;
            }

            $eventType = $this->resolveEventType($routeName, $method);
            $description = $this->resolveDescription($routeName, $method, $path);

            EventLog::create([
                'company_id'  => $this->resolveOwnerCompanyId(),
                'user_id'     => $user->id,
                'event_type'  => $eventType,
                'description' => $description,
                'metadata'    => [
                    'route'      => $routeName,
                    'method'     => $method,
                    'path'       => $path,
                    'full_url'   => $request->fullUrl(),
                    'query'      => $request->query(),
                    'status'     => $statusCode,
                    'user_agent' => (string) $request->userAgent(),
                    'actor_company_id' => $user->company_id,
                ],
                'ip_address'  => $request->ip(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Activity logging must never interrupt the user flow.
        }

        return $response;
    }

    private function shouldLogFunctionalAction(string $method, ?string $routeName, int $statusCode): bool
    {
        if (!$routeName || $statusCode >= 400) {
            return false;
        }

        if ($routeName === 'logout') {
            // Logout is already logged explicitly in AuthController.
            return false;
        }

        if (Str::startsWith($routeName, 'quickbooks.')) {
            // QuickBooks logging is handled explicitly in QuickBooksController
            // to keep only meaningful events (connected/disconnected, etc.).
            return false;
        }

        $action = Str::lower((string) Str::of($routeName)->afterLast('.'));
        $functionalActions = [
            'store', 'update', 'destroy', 'delete', 'toggle', 'assign',
            'upload', 'checkout', 'sync', 'connect', 'disconnect', 'refresh',
            'test', 'create', 'submit', 'password', 'profile', 'notes', 'quick-store',
        ];

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return in_array($action, $functionalActions, true);
        }

        return false;
    }

    private function resolveEventType(?string $routeName, string $method): string
    {
        if (!$routeName) {
            return Str::lower($method);
        }

        $action = Str::lower((string) Str::of($routeName)->afterLast('.'));
        $resource = Str::lower((string) Str::of($routeName)->beforeLast('.')->replace('.', '_')->replace('-', '_'));
        if ($resource === 'support') {
            $resource = 'ticket';
        }

        $normalizedAction = match ($action) {
            'store', 'create'     => 'created',
            'update'              => 'updated',
            'destroy', 'delete'   => 'deleted',
            'toggle'              => 'status_changed',
            'assign', 'submit'    => 'submitted',
            'upload'              => 'uploaded',
            default               => Str::of($action)->replace('-', '_')->value(),
        };

        return $resource . '_' . $normalizedAction;
    }

    private function resolveDescription(?string $routeName, string $method, string $path): string
    {
        if (!$routeName) {
            return $method . ' ' . $path;
        }

        $resourceKey = Str::lower((string) Str::of($routeName)->beforeLast('.'));
        $action = Str::lower((string) Str::of($routeName)->afterLast('.'));

        $resourceLabel = match ($resourceKey) {
            'support' => 'Support Ticket',
            'clients' => 'Client',
            'users' => 'User',
            'plans' => 'Plan',
            'my-companies' => 'Company',
            'settings.roles' => 'Role',
            'settings.branding' => 'Branding',
            'settings.email' => 'Email Settings',
            'settings.sms' => 'SMS Settings',
            'settings.payment-gateway' => 'Payment Gateway Settings',
            'documents' => 'Document',
            'profile' => 'Profile',
            'account.profile' => 'Profile',
            'account.password', 'profile.password' => 'Password',
            default => Str::title((string) Str::of($resourceKey)->replace('.', ' ')->replace('-', ' ')),
        };

        $actionLabel = match ($action) {
            'store', 'create' => 'Created',
            'update' => 'Updated',
            'destroy', 'delete' => 'Deleted',
            'toggle' => 'Status Updated',
            'assign' => 'Assigned',
            'upload' => 'Uploaded',
            'submit' => 'Submitted',
            'password' => 'Changed',
            'profile' => 'Updated',
            default => Str::title((string) Str::of($action)->replace('-', ' ')),
        };

        return trim($resourceLabel . ' ' . $actionLabel);
    }

    private function resolveOwnerCompanyId(): ?int
    {
        $tenantCompany = company();
        if (!$tenantCompany) {
            return Auth::user()?->company_id;
        }

        $parentId = $tenantCompany->created_by;
        if ($parentId && Company::withoutGlobalScopes()->whereKey($parentId)->exists()) {
            return (int) $parentId;
        }

        return (int) $tenantCompany->id;
    }
}
