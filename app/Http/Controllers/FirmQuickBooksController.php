<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\EventLog;
use App\Models\QuickBooksConnection;
use App\Services\QuickBooksService;
use Illuminate\Support\Facades\Log;

class FirmQuickBooksController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'My QuickBooks';
    }

    public function index()
    {
        $companyId = $this->firmCompanyId();

        $this->connection = QuickBooksConnection::where('company_id', $companyId)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        $this->qbConfigured = $this->resolveCredentials($companyId) !== null;

        return view('firm.quickbooks', $this->data);
    }

    public function connect()
    {
        $companyId   = $this->firmCompanyId();
        $credentials = $this->resolveCredentials($companyId);

        if (!$credentials) {
            return redirect()->route('firm-quickbooks.index')
                ->with('error', 'QuickBooks credentials are not configured. Please set them up in Settings → QuickBooks.');
        }

        session([
            'qb_company_id'   => $companyId,
            'qb_user_id'      => auth()->id(),
            'qb_return_route' => 'firm-quickbooks.index',
        ]);

        return redirect(QuickBooksService::getAuthorizationUrl($credentials));
    }

    public function disconnect()
    {
        $companyId  = $this->firmCompanyId();
        $connection = QuickBooksConnection::where('company_id', $companyId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$connection) {
            return Reply::error('No active QuickBooks connection found.');
        }

        $connection->update(['is_active' => false]);

        $this->logEvent('quickbooks_disconnected', 'Firm QuickBooks disconnected', $companyId);

        return Reply::success('QuickBooks disconnected successfully.');
    }

    public function testConnection()
    {
        $companyId  = $this->firmCompanyId();
        $connection = QuickBooksConnection::where('company_id', $companyId)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$connection) {
            return Reply::error('No active QuickBooks connection found.');
        }

        $credentials = $this->resolveCredentials($companyId);

        if (!$credentials) {
            return Reply::error('QuickBooks credentials are not configured.');
        }

        $ok = QuickBooksService::testConnection($connection, $credentials);

        return $ok
            ? Reply::success('QuickBooks connection is working!')
            : Reply::error('QuickBooks connection test failed. Try refreshing the token.');
    }

    public function refreshToken()
    {
        $companyId  = $this->firmCompanyId();
        $connection = QuickBooksConnection::where('company_id', $companyId)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$connection) {
            return Reply::error('No active QuickBooks connection found.');
        }

        $credentials = $this->resolveCredentials($companyId);

        if (!$credentials) {
            return Reply::error('QuickBooks credentials are not configured.');
        }

        try {
            $service = new QuickBooksService($connection, $credentials);
            $service->refreshTokenIfNeeded();
            $connection->refresh();

            return Reply::successWithData('Token refreshed successfully!', [
                'access_token_expires_at' => $connection->access_token_expires_at->format('M d, Y h:i A'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Firm QuickBooks refresh token error: ' . $e->getMessage());
            return Reply::error('Failed to refresh token: ' . $e->getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function firmCompanyId(): int
    {
        return (int) (company()?->id ?? auth()->user()->company_id);
    }

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

    private function logEvent(string $type, string $desc, int $companyId): void
    {
        try {
            EventLog::create([
                'company_id'  => $companyId,
                'user_id'     => auth()->id(),
                'event_type'  => $type,
                'description' => $desc,
                'metadata'    => ['route' => 'firm-quickbooks'],
                'ip_address'  => request()->ip(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Firm QB event log failed: ' . $e->getMessage());
        }
    }
}
