<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Jobs\SyncQuickBooksJob;
use App\Models\Company;
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
            $this->connection = QuickBooksConnection::where('user_id', auth()->id())
                ->where('is_active', true)
                ->first();
            return view('quickbooks.user-connection', $this->data);
        }

        // Firm panel (client_admin, manager, etc.)
        $companyId = company()?->id ?? auth()->user()->company_id;

        $managedIds = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->pluck('id')
            ->push($companyId);

        $this->connections = QuickBooksConnection::with('connectedBy', 'company', 'syncLogs')
            ->whereIn('company_id', $managedIds)
            ->orderByDesc('created_at')
            ->get();

        return view('quickbooks.index', $this->data);
    }

    public function connect()
    {
        abort_403(!auth()->user()->can('connect_quickbooks'));

        $companyId = company()?->id ?? auth()->user()->company_id;
        abort_403(!$companyId);
        session(['qb_company_id' => $companyId, 'qb_user_id' => auth()->id()]);

        return redirect(QuickBooksService::getAuthorizationUrl());
    }

    public function callback(Request $request)
    {
        $code    = $request->get('code');
        $realmId = $request->get('realmId');
        $error   = $request->get('error');

        if ($error) {
            Log::error('QuickBooks OAuth Error: ' . $error);
            return redirect()->route('quickbooks.index')
                ->with('error', 'QuickBooks authorization failed. Please try again.');
        }

        if (!$code || !$realmId) {
            return redirect()->route('quickbooks.index')
                ->with('error', 'Invalid callback from QuickBooks. Missing required parameters.');
        }

        $userId    = session('qb_user_id');
        $companyId = session('qb_company_id');
        session()->forget(['qb_company_id', 'qb_user_id']);

        if (!$userId || !$companyId) {
            Log::error('QuickBooks OAuth: Missing session data', [
                'user_id'    => $userId,
                'company_id' => $companyId,
            ]);
            return redirect()->route('quickbooks.index')
                ->with('error', 'Authorization session expired. Please try connecting again.');
        }

        Log::info('QuickBooks OAuth: Processing callback', [
            'user_id'    => $userId,
            'company_id' => $companyId,
            'realm_id'   => $realmId,
        ]);

        try {
            $tokenData = QuickBooksService::handleCallback($code, $realmId);

            // Use withoutGlobalScopes to bypass TenantScope and find any existing record
            $connection = QuickBooksConnection::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->where('company_id', $companyId)
                ->first();

            if ($connection) {
                $connection->update(array_merge($tokenData, ['is_active' => true]));
                $message = 'QuickBooks reconnected successfully!';
            } else {
                QuickBooksConnection::create([
                    'user_id'    => $userId,
                    'company_id' => $companyId,
                    'is_active'  => true,
                    ...$tokenData,
                ]);
                $message = 'QuickBooks connected successfully!';
            }

            return redirect()->route('quickbooks.index')
                ->with('success', $message);

        } catch (\Throwable $e) {
            Log::error('QuickBooks Callback Error: ' . $e->getMessage(), [
                'user_id'    => $userId,
                'company_id' => $companyId,
            ]);
            return redirect()->route('quickbooks.index')
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
        return Reply::success('QuickBooks disconnected.');
    }

    // ── User Role: personal connect / disconnect ──────────────────────────────

    public function userConnect()
    {
        $companyId = auth()->user()->company_id;
        abort_403(!$companyId);
        session(['qb_company_id' => $companyId, 'qb_user_id' => auth()->id()]);
        return redirect(QuickBooksService::getAuthorizationUrl());
    }

    public function userDisconnect()
    {
        $connection = QuickBooksConnection::where('user_id', auth()->id())->first();

        if (!$connection) {
            return Reply::error('No active QuickBooks connection found.');
        }

        $connection->update(['is_active' => false]);
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

        $ok = QuickBooksService::testConnection($connection);

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

        try {
            $service = new QuickBooksService($connection);
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
}
