<?php

namespace App\Services;

use App\Models\QuickBooksConnection;
use App\Models\QuickBooksSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;

class QuickBooksService
{
    private DataService $dataService;

    public function __construct(
        private ?QuickBooksConnection $connection = null,
        private array $credentials = []
    ) {
        $this->dataService = self::buildDataService($connection, $credentials);
    }

    // ── OAuth ─────────────────────────────────────────────────────────────────

    public static function getAuthorizationUrl(array $credentials): string
    {
        $ds     = self::buildDataService(null, $credentials);
        $helper = $ds->getOAuth2LoginHelper();
        return $helper->getAuthorizationCodeURL();
    }

    public static function handleCallback(string $code, string $realmId, array $credentials): array
    {
        $ds     = self::buildDataService(null, $credentials);
        $helper = $ds->getOAuth2LoginHelper();

        Log::info('QuickBooks OAuth: Exchanging authorization code', [
            'realm_id'    => $realmId,
            'code_length' => strlen($code),
        ]);

        try {
            $tokenObj = $helper->exchangeAuthorizationCodeForToken($code, $realmId);
        } catch (\Throwable $e) {
            Log::error('QuickBooks OAuth callback failed: ' . $e->getMessage());
            throw $e;
        }

        $accessTokenExpiresIn  = $tokenObj->getAccessTokenExpiresAt();
        $refreshTokenExpiresIn = $tokenObj->getRefreshTokenExpiresAt();

        Log::info('QuickBooks Token Expiration Values', [
            'access_token_expires_raw'  => $accessTokenExpiresIn,
            'refresh_token_expires_raw' => $refreshTokenExpiresIn,
        ]);

        $accessTokenExpiresAt  = self::parseExpiry($accessTokenExpiresIn,  3600);
        $refreshTokenExpiresAt = self::parseExpiry($refreshTokenExpiresIn, 8_640_000);

        $ds->updateOAuth2Token($tokenObj);
        $companyInfo = $ds->getCompanyInfo();
        $companyName = $companyInfo?->CompanyName ?? null;
        $country     = $companyInfo?->Country     ?? null;

        Log::info('QuickBooks Token Expiration Dates', [
            'access_token_expires_at'  => $accessTokenExpiresAt->toDateTimeString(),
            'refresh_token_expires_at' => $refreshTokenExpiresAt->toDateTimeString(),
        ]);

        return [
            'realm_id'                  => $realmId,
            'access_token'              => $tokenObj->getAccessToken(),
            'refresh_token'             => $tokenObj->getRefreshToken(),
            'access_token_expires_at'   => $accessTokenExpiresAt,
            'refresh_token_expires_at'  => $refreshTokenExpiresAt,
            'qb_company_name'           => $companyName,
            'country'                   => $country,
        ];
    }

    // ── Token Refresh ─────────────────────────────────────────────────────────

    public function refreshTokenIfNeeded(): void
    {
        if (!$this->connection) return;

        if ($this->connection->isAccessTokenExpired()) {
            if ($this->connection->isRefreshTokenExpired()) {
                $this->connection->update(['is_active' => false]);
                throw new \RuntimeException('QuickBooks refresh token expired. Re-authentication required.');
            }

            $helper = $this->dataService->getOAuth2LoginHelper();
            try {
                $newToken = $helper->refreshAccessTokenWithRefreshToken($this->connection->refresh_token);
            } catch (\Throwable $e) {
                $raw = $e->getMessage();
                if (stripos($raw, 'invalid_grant') !== false) {
                    Log::warning('QuickBooks refresh failed (invalid_grant); connection deactivated.', [
                        'connection_id' => $this->connection->id,
                        'company_id'    => $this->connection->company_id,
                    ]);
                    $this->connection->update(['is_active' => false]);
                    throw new \RuntimeException(
                        'QuickBooks rejected the stored refresh token (invalid or revoked). Open QuickBooks and connect your company again.',
                        0,
                        $e
                    );
                }
                throw $e;
            }

            // Intuit rotates refresh tokens — always persist the latest pair from the refresh response.
            $updates = [
                'access_token'            => $newToken->getAccessToken(),
                'access_token_expires_at' => self::parseExpiry($newToken->getAccessTokenExpiresAt(), 3600),
            ];
            try {
                $newRefresh = $newToken->getRefreshToken();
                if (is_string($newRefresh) && $newRefresh !== '') {
                    $updates['refresh_token'] = $newRefresh;
                }
            } catch (\Throwable) {
                // SDK may omit refresh in edge cases; keep existing refresh_token in DB.
            }
            try {
                $updates['refresh_token_expires_at'] = self::parseExpiry($newToken->getRefreshTokenExpiresAt(), 8_640_000);
            } catch (\Throwable) {
                // Leave existing refresh_token_expires_at if not returned.
            }

            $this->connection->update($updates);
            $this->dataService->updateOAuth2Token($newToken);
            $this->connection->refresh();
        }
    }

    // ── Data Sync ─────────────────────────────────────────────────────────────

    public function syncCustomers(): array
    {
        $this->refreshTokenIfNeeded();

        $log = QuickBooksSyncLog::create([
            'connection_id' => $this->connection->id,
            'sync_type'     => 'customers',
            'status'        => 'running',
            'started_at'    => now(),
            'created_at'    => now(),
        ]);

        try {
            $customers = $this->dataService->Query("SELECT * FROM Customer WHERE Active = true MAXRESULTS 100");
            $error     = $this->dataService->getLastError();

            if ($error) {
                throw new \RuntimeException($error->getResponseBody());
            }

            $count = count($customers ?? []);

            $log->update([
                'status'         => 'success',
                'records_synced' => $count,
                'completed_at'   => now(),
            ]);

            $this->connection->update(['last_synced_at' => now()]);

            return ['synced' => $count, 'customers' => $customers];

        } catch (\Throwable $e) {
            Log::error("QB sync customers failed: " . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'completed_at' => now()]);
            throw $e;
        }
    }

    public function syncInvoices(): array
    {
        $this->refreshTokenIfNeeded();

        $log = QuickBooksSyncLog::create([
            'connection_id' => $this->connection->id,
            'sync_type'     => 'invoices',
            'status'        => 'running',
            'started_at'    => now(),
            'created_at'    => now(),
        ]);

        try {
            $invoices = $this->dataService->Query("SELECT * FROM Invoice MAXRESULTS 100");
            $count    = count($invoices ?? []);

            $log->update(['status' => 'success', 'records_synced' => $count, 'completed_at' => now()]);
            $this->connection->update(['last_synced_at' => now()]);

            return ['synced' => $count, 'invoices' => $invoices];

        } catch (\Throwable $e) {
            Log::error("QB sync invoices failed: " . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'completed_at' => now()]);
            throw $e;
        }
    }

    // ── Connection Test ───────────────────────────────────────────────────────

    public static function testConnection(QuickBooksConnection $connection, array $credentials): bool
    {
        try {
            $ds          = self::buildDataService($connection, $credentials);
            $companyInfo = $ds->getCompanyInfo();
            return $companyInfo !== null;
        } catch (\Throwable $e) {
            Log::error('QuickBooks test connection failed: ' . $e->getMessage());
            return false;
        }
    }

    // ── Factory ───────────────────────────────────────────────────────────────

    private static function buildDataService(?QuickBooksConnection $conn = null, array $creds = []): DataService
    {
        $config = [
            'auth_mode'    => 'oauth2',
            'ClientID'     => $creds['client_id']     ?? config('quickbooks.client_id'),
            'ClientSecret' => $creds['client_secret']  ?? config('quickbooks.client_secret'),
            'RedirectURI'  => $creds['redirect_uri']   ?? config('quickbooks.redirect_uri'),
            'scope'        => 'com.intuit.quickbooks.accounting',
            'baseUrl'      => isset($creds['base_url'])
                                ? ucfirst(strtolower($creds['base_url']))
                                : config('quickbooks.environment', 'Development'),
        ];

        if ($conn) {
            $config['accessTokenKey']  = $conn->access_token;
            $config['refreshTokenKey'] = $conn->refresh_token;
            $config['QBORealmID']      = $conn->realm_id;
        }

        return DataService::Configure($config);
    }

    private static function parseExpiry(mixed $value, int $defaultSeconds): Carbon
    {
        if (is_numeric($value) && $value > 1_000_000_000) {
            return Carbon::createFromTimestamp($value);
        }
        if (is_string($value) && strtotime($value)) {
            return Carbon::parse($value);
        }
        if (is_numeric($value) && $value >= 60) {
            return Carbon::now()->addSeconds((int) $value);
        }
        return Carbon::now()->addSeconds($defaultSeconds);
    }
}
