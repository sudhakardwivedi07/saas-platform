<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Jobs\SyncQuickBooksJob;
use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\EventLog;
use App\Models\QuickBooksConnection;
use App\Models\User;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (auth()->user()->hasRole('client')) {
            $this->connection  = QuickBooksConnection::where('user_id', auth()->id())
                ->where('is_active', true)
                ->first();
            $this->qbConfigured = $this->resolveCredentials(auth()->user()->company_id) !== null;
            return view('quickbooks.user-connection', $this->data);
        }

        // Firm panel (admin, manager, etc.)
        $companyId = company()?->id ?? auth()->user()->company_id;

        // Step 1: admin's own managed companies (primary + linked via user_companies)
        $adminCompanyIds = collect([$companyId])
            ->merge(
                auth()->user()->managedCompanies()
                    ->withoutGlobalScopes()
                    ->pluck('companies.id')
            )
            ->unique()->filter()->values()->all();

        // Step 2: direct client companies created by those admin companies
        $directClientIds = Company::withoutGlobalScopes()
            ->whereIn('created_by', $adminCompanyIds)
            ->pluck('id');

        // Step 3: users who belong to those direct client companies
        $clientUserIds = User::withoutGlobalScopes()
            ->whereIn('company_id', $directClientIds)
            ->pluck('id');

        // Step 4: all companies those client users are linked to via user_companies
        //         (covers sub-companies they created + companies assigned to them)
        $linkedCompanyIds = DB::table('user_companies')
            ->whereIn('user_id', $clientUserIds)
            ->pluck('company_id');

        $allClientCompanyIds = $directClientIds
            ->merge($linkedCompanyIds)
            ->unique()->filter()->values()->all();

        $this->managedCompanies = Company::withoutGlobalScopes()
            ->whereIn('id', $allClientCompanyIds)
            ->with([
                'users',
                'managers',
                'quickbooksConnections' => fn($q) => $q->withoutGlobalScopes()
                    ->where('is_active', true)
                    ->orderByDesc('created_at'),
                'creatorCompany',
            ])
            ->orderBy('company_name')
            ->get();

        // Show the "Partner" column only when multiple admin companies are in scope
        $this->showPartnerColumn = count($adminCompanyIds) > 1;

        $this->connectedCount    = $this->managedCompanies->filter(
            fn($co) => $co->quickbooksConnections->contains(
                fn($c) => !$c->isAccessTokenExpired()
            )
        )->count();
        $this->notConnectedCount = $this->managedCompanies->count() - $this->connectedCount;
        $this->qbConfigured      = $this->resolveCredentials($companyId) !== null;

        return view('quickbooks.index', $this->data);
    }

    public function connect()
    {
        abort_403(!auth()->user()->can('connect_quickbooks'));

        $firmCompanyId  = company()?->id ?? auth()->user()->company_id;
        $targetCompanyId = (int) request()->input('company_id', $firmCompanyId);

        // Validate the admin can manage the target company.
        // Uses the same multi-step resolution as index(): direct created_by check first,
        // then the user_companies path that covers client-owned sub-companies.
        if ($targetCompanyId !== $firmCompanyId) {
            abort_403(!$this->adminCanManageCompany($firmCompanyId, $targetCompanyId));
        }

        $credentials = $this->resolveCredentials($firmCompanyId);

        if (!$credentials) {
            return redirect()->route('quickbooks.index')
                ->with('error', 'QuickBooks credentials are not configured. Please set them up in Settings → QuickBooks.');
        }

        session([
            'qb_company_id'      => $targetCompanyId,
            'qb_user_id'         => auth()->id(),
            'qb_firm_company_id' => $firmCompanyId,
        ]);
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

        $userId         = session('qb_user_id');
        $companyId      = session('qb_company_id');
        $firmCompanyId  = session('qb_firm_company_id', $companyId);
        session()->forget(['qb_company_id', 'qb_user_id', 'qb_firm_company_id', 'qb_return_route']);

        if (!$userId || !$companyId) {
            Log::error('QuickBooks OAuth: Missing session data', compact('userId', 'companyId'));
            return redirect()->route($returnRoute)
                ->with('error', 'Authorization session expired. Please try connecting again.');
        }

        // Resolve credentials from the firm company (always has credentials configured).
        // Using $companyId directly fails for client sub-companies whose created_by is a
        // user ID rather than a company ID, so the parent lookup cannot find credentials.
        $credentials = $this->resolveCredentials($firmCompanyId);

        if (!$credentials) {
            return redirect()->route($returnRoute)
                ->with('error', 'QuickBooks credentials are not configured. Please set them up in Settings → QuickBooks.');
        }

        Log::info('QuickBooks OAuth: Processing callback', compact('userId', 'companyId', 'realmId'));

        try {
            $tokenData = QuickBooksService::handleCallback($code, $realmId, $credentials);

            // Look up by (company_id, realm_id) first — the actual unique business key.
            // Falling back to (user_id, company_id) handles the case where the realm
            // has changed (rare). Without this, a reconnect by a different user than
            // the original connector would violate the company_id+realm_id unique index.
            $connection = QuickBooksConnection::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('realm_id', $realmId)
                ->first()
                ?? QuickBooksConnection::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->where('company_id', $companyId)
                    ->first();

            if ($connection) {
                $connection->update(array_merge($tokenData, [
                    'user_id'        => $userId,
                    'is_active'      => true,
                    'last_synced_at' => now(),
                ]));
                $message   = 'QuickBooks reconnected successfully!';
                $eventType = 'quickbooks_reconnected';
            } else {
                QuickBooksConnection::create([
                    'user_id'        => $userId,
                    'company_id'     => $companyId,
                    'is_active'      => true,
                    'last_synced_at' => now(),
                    ...$tokenData,
                ]);
                $message   = 'QuickBooks connected successfully!';
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
        $connection = QuickBooksConnection::withoutGlobalScopes()->findOrFail($connectionId);

        $connection->update(['last_synced_at' => now()]);

        SyncQuickBooksJob::dispatch($connection->id, $request->sync_type)
            ->onQueue('quickbooks');

        return Reply::successWithData('Sync job dispatched. Results will appear shortly.', [
            'last_synced_at' => $connection->last_synced_at->format('M j, Y'),
        ]);
    }

    public function disconnect(int $connectionId)
    {
        $connection = QuickBooksConnection::withoutGlobalScopes()->findOrFail($connectionId);
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

    /**
     * Returns true when the given admin company (or any company it manages) is
     * allowed to connect/reconnect on behalf of $targetCompanyId.
     *
     * Mirrors the four-step resolution used in index():
     *  1. Direct created_by match (fast path)
     *  2. Admin's user_companies-linked companies (multi-partner scenario)
     *  3. Companies linked via client users' user_companies entries
     */
    private function adminCanManageCompany(int $firmCompanyId, int $targetCompanyId): bool
    {
        // Step 1: direct created_by (fast path)
        $adminCompanyIds = collect([$firmCompanyId])
            ->merge(
                auth()->user()->managedCompanies()
                    ->withoutGlobalScopes()
                    ->pluck('companies.id')
            )
            ->unique()->filter()->values()->all();

        if (Company::withoutGlobalScopes()
            ->where('id', $targetCompanyId)
            ->whereIn('created_by', $adminCompanyIds)
            ->exists()) {
            return true;
        }

        // Step 2: via client users' user_companies
        $directClientIds = Company::withoutGlobalScopes()
            ->whereIn('created_by', $adminCompanyIds)
            ->pluck('id');

        $clientUserIds = User::withoutGlobalScopes()
            ->whereIn('company_id', $directClientIds)
            ->pluck('id');

        return DB::table('user_companies')
            ->whereIn('user_id', $clientUserIds)
            ->where('company_id', $targetCompanyId)
            ->exists();
    }
}
