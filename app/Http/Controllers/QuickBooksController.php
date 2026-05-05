<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Jobs\SyncQuickBooksJob;
use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\EventLog;
use App\Models\QuickBooksConnection;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuickBooksController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'QuickBooks';
    }

    public function index()
    {
        // User role: show personal connect / disconnect page
        if (auth()->user()->hasRole('user')) {
            $this->connection  = QuickBooksConnection::where('user_id', auth()->id())
                ->where('is_active', true)
                ->first();
            $this->qbConfigured = $this->resolveCredentials(auth()->user()->company_id) !== null;
            return view('quickbooks.user-connection', $this->data);
        }

        // Firm panel (client_admin, manager, etc.)
        $companyId = company()?->id ?? auth()->user()->company_id;

        $managedIds = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->pluck('id')
            ->push($companyId);

        $this->connections  = QuickBooksConnection::with('connectedBy', 'company', 'syncLogs')
            ->whereIn('company_id', $managedIds)
            ->orderByDesc('created_at')
            ->get();
        $this->qbConfigured = $this->resolveCredentials($companyId) !== null;

        return view('quickbooks.index', $this->data);
    }

    public function connect()
    {
        abort_403(!auth()->user()->can('connect_quickbooks'));

        $companyId   = company()?->id ?? auth()->user()->company_id;
        $credentials = $this->resolveCredentials($companyId);

        if (!$credentials) {
            return redirect()->route('quickbooks.index')
                ->with('error', 'QuickBooks credentials are not configured. Please set them up in Settings → QuickBooks.');
        }

        session(['qb_company_id' => $companyId, 'qb_user_id' => auth()->id()]);
        return redirect(QuickBooksService::getAuthorizationUrl($credentials));
    }

    public function callback(Request $request)
    {
        $code        = $request->get('code');
        $realmId     = $request->get('realmId');
        $error       = $request->get('error');
        $returnRoute = session('qb_return_route', 'quickbooks.index');

        if ($error) {
            Log::error('QuickBooks OAuth Error: ' . $error);
            return redirect()->route($returnRoute)
                ->with('error', 'QuickBooks authorization failed. Please try again.');
        }

        if (!$code || !$realmId) {
            return redirect()->route($returnRoute)
                ->with('error', 'Invalid callback from QuickBooks. Missing required parameters.');
        }

        $userId    = session('qb_user_id');
        $companyId = session('qb_company_id');
        session()->forget(['qb_company_id', 'qb_user_id', 'qb_return_route']);

        if (!$userId || !$companyId) {
            Log::error('QuickBooks OAuth: Missing session data', compact('userId', 'companyId'));
            return redirect()->route($returnRoute)
                ->with('error', 'Authorization session expired. Please try connecting again.');
        }

        $credentials = $this->resolveCredentials($companyId);

        if (!$credentials) {
            return redirect()->route($returnRoute)
                ->with('error', 'QuickBooks credentials are not configured for this company.');
        }

        Log::info('QuickBooks OAuth: Processing callback', compact('userId', 'companyId', 'realmId'));

        try {
            $tokenData = QuickBooksService::handleCallback($code, $realmId, $credentials);

            $connection = QuickBooksConnection::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->where('company_id', $companyId)
                ->first();

            if ($connection) {
                $connection->update(array_merge($tokenData, ['is_active' => true]));
                $message = 'QuickBooks reconnected successfully!';
                $eventType = 'quickbooks_reconnected';
            } else {
                QuickBooksConnection::create([
                    'user_id'    => $userId,
                    'company_id' => $companyId,
                    'is_active'  => true,
                    ...$tokenData,
                ]);
                $message = 'QuickBooks connected successfully!';
                $eventType = 'quickbooks_connected';
            }

            $this->logQuickBooksActivity(
                eventType: $eventType,
                description: $message,
                actorCompanyId: (int) $companyId,
                meta: [
                    'route' => 'quickbooks.callback',
                    'realm_id' => $realmId,
                ],
                actorUserId: (int) $userId
            );

            return redirect()->route($returnRoute)->with('success', $message);

        } catch (\Throwable $e) {
            Log::error('QuickBooks Callback Error: ' . $e->getMessage(), compact('userId', 'companyId'));
            return redirect()->route($returnRoute)
                ->with('error', 'Failed to connect QuickBooks: ' . $e->getMessage());
        }
    }

    public function sync(Request $request, int $connectionId)
    {
        abort_403(!auth()->user()->can('sync_quickbooks'));

        $request->validate(['sync_type' => 'required|in:customers,invoices']);
        $connection = QuickBooksConnection::findOrFail($connectionId);

        SyncQuickBooksJob::dispatch($connection->id, $request->sync_type)
            ->onQueue('quickbooks');

        return Reply::success('Sync job dispatched. Results will appear shortly.');
    }

    public function disconnect(int $connectionId)
    {
        $connection = QuickBooksConnection::findOrFail($connectionId);
        $connection->update(['is_active' => false]);

        $this->logQuickBooksActivity(
            eventType: 'quickbooks_disconnected',
            description: 'QuickBooks disconnected',
            actorCompanyId: (int) $connection->company_id,
            meta: ['route' => 'quickbooks.disconnect']
        );

        return Reply::success('QuickBooks disconnected.');
    }

    // ── User Role: personal connect / disconnect ──────────────────────────────

    public function userConnect()
    {
        $companyId   = auth()->user()->company_id;
        $credentials = $this->resolveCredentials($companyId);

        if (!$credentials) {
            return redirect()->route('quickbooks.index')
                ->with('error', 'QuickBooks credentials are not configured. Please contact your administrator.');
        }

        session(['qb_company_id' => $companyId, 'qb_user_id' => auth()->id()]);
        return redirect(QuickBooksService::getAuthorizationUrl($credentials));
    }

    public function userDisconnect()
    {
        $connection = QuickBooksConnection::where('user_id', auth()->id())->first();

        if (!$connection) {
            return Reply::error('No active QuickBooks connection found.');
        }

        $connection->update(['is_active' => false]);
        $this->logQuickBooksActivity(
            eventType: 'quickbooks_disconnected',
            description: 'QuickBooks disconnected',
            actorCompanyId: (int) $connection->company_id,
            meta: ['route' => 'quickbooks.user-disconnect']
        );
        return Reply::success('QuickBooks disconnected successfully.');
    }

    public function userTestConnection()
    {
        $connection = QuickBooksConnection::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$connection) {
            return Reply::error('No active QuickBooks connection found.');
        }

        $credentials = $this->resolveCredentials($connection->company_id);

        if (!$credentials) {
            return Reply::error('QuickBooks credentials are not configured for this company.');
        }

        $ok = QuickBooksService::testConnection($connection, $credentials);

        return $ok
            ? Reply::success('QuickBooks connection is working!')
            : Reply::error('QuickBooks connection test failed. Try refreshing the token.');
    }

    public function userRefreshToken()
    {
        $connection = QuickBooksConnection::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$connection) {
            return Reply::error('No active QuickBooks connection found.');
        }

        $credentials = $this->resolveCredentials($connection->company_id);

        if (!$credentials) {
            return Reply::error('QuickBooks credentials are not configured for this company.');
        }

        try {
            $service = new QuickBooksService($connection, $credentials);
            $service->refreshTokenIfNeeded();
            $connection->refresh();
            return Reply::successWithData('Token refreshed successfully!', [
                'access_token_expires_at' => $connection->access_token_expires_at->format('M d, Y h:i A'),
            ]);
        } catch (\Throwable $e) {
            Log::error('QuickBooks user refresh token error: ' . $e->getMessage());
            return Reply::error('Failed to refresh token: ' . $e->getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Load QB credentials for the given company.
     * Falls back to the parent company (created_by) so sub-company users
     * inherit the partner's credentials automatically.
     */
    private function resolveCredentials(int $companyId): ?array
    {
        $setting = CompanyQuickbooksSetting::where('company_id', $companyId)->first();

        if (!$setting) {
            $parentId = Company::withoutGlobalScopes()
                ->where('id', $companyId)
                ->value('created_by');

            if ($parentId) {
                $setting = CompanyQuickbooksSetting::where('company_id', $parentId)->first();
            }
        }

        if (!$setting) return null;

        return [
            'client_id'     => $setting->client_id,
            'client_secret' => $setting->client_secret,
            'redirect_uri'  => $setting->redirect_uri,
            'base_url'      => $setting->base_url,
        ];
    }

    private function logQuickBooksActivity(
        string $eventType,
        string $description,
        int $actorCompanyId,
        array $meta = [],
        ?int $actorUserId = null
    ): void
    {
        try {
            $ownerCompanyId = $this->resolveOwnerCompanyId($actorCompanyId);

            EventLog::create([
                'company_id'  => $ownerCompanyId,
                'user_id'     => $actorUserId ?? auth()->id(),
                'event_type'  => $eventType,
                'description' => $description,
                'metadata'    => array_merge($meta, [
                    'actor_company_id' => $actorCompanyId,
                ]),
                'ip_address'  => request()->ip(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('QuickBooks activity log failed: ' . $e->getMessage());
        }
    }

    private function resolveOwnerCompanyId(int $actorCompanyId): int
    {
        $parentId = Company::withoutGlobalScopes()
            ->where('id', $actorCompanyId)
            ->value('created_by');

        if ($parentId && Company::withoutGlobalScopes()->whereKey($parentId)->exists()) {
            return (int) $parentId;
        }

        return $actorCompanyId;
    }
}
