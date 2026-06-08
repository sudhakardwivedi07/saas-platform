<?php

namespace App\Services\Kpis;

use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Models\User;
use App\Services\KpiService;
use App\Services\QuickBooksCSVExportService;
use App\Services\QuickBooksService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class QuickBooksKpiDataService
{
    /** When set (e.g. firm admin viewing a client KPI), report CSV fallback uses this user_id. */
    private static ?int $reportOwnerUserIdOverride = null;

    /**
     * Run $callback with KPI dataset resolution scoped to this user's analyzed reports (QuickBooks CSV attachments).
     */
    public static function usingReportOwner(?int $userId, callable $callback): mixed
    {
        $prev = self::$reportOwnerUserIdOverride;
        self::$reportOwnerUserIdOverride = $userId;
        try {
            return $callback();
        } finally {
            self::$reportOwnerUserIdOverride = $prev;
        }
    }

    /**
     * Financial dataset for KPI calculations: prefer the same CSVs stored on the
     * latest analyzed insight, then use live QuickBooks only when no analyzed
     * report data exists. This keeps dashboard KPIs aligned with generated Excel files.
     */
    public static function dataset(int $companyId): ?array
    {
        $userId = self::$reportOwnerUserIdOverride ?? Auth::id();
        $cacheKey = self::datasetCacheKey($companyId, $userId ? (int) $userId : 0);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        if ($userId) {
            $fromReport = self::datasetFromLatestAnalyzedReportCsvs((int) $companyId, (int) $userId);
            if ($fromReport !== null) {
                Cache::put($cacheKey, $fromReport, 900);

                return $fromReport;
            }
        }

        $live = self::fetchLiveDataset($companyId, $userId ? (int) $userId : null);
        if ($live !== null) {
            Cache::put($cacheKey, $live, 900);

            return $live;
        }

        // Do not cache misses — avoids locking in empty state after new insights are saved.
        return null;
    }

    public static function datasetCacheKey(int $companyId, int $userId): string
    {
        return "qb_kpi_dataset_{$companyId}_{$userId}";
    }

    public static function forgetDatasetCache(int $companyId, ?int $userId): void
    {
        if ($userId) {
            Cache::forget(self::datasetCacheKey($companyId, $userId));
        }
        // Legacy / pre-user-scoped keys
        Cache::forget("qb_kpi_dataset_{$companyId}");
    }

    /** Safe shape when live QB and stored report CSVs are both unavailable. */
    public static function emptyDatasetShape(): array
    {
        return [
            'balance_sheet_monthly' => [],
            'cash_flow_monthly' => [],
            'profit_loss_monthly' => [],
            'ar_rows' => [],
            'ap_rows' => [],
        ];
    }

    private static function fetchLiveDataset(int $companyId, ?int $userId): ?array
    {
        $connection = QuickBooksConnection::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $connection && $userId) {
            $connection = QuickBooksConnection::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->latest('id')
                ->first();
        }

        if (! $connection) {
            return null;
        }

        $credentialCompanyId = (int) ($connection->company_id ?: $companyId);
        $credentials = self::resolveCredentials($credentialCompanyId);
        if (! $credentials) {
            return null;
        }

        try {
            $qb = new QuickBooksService($connection, $credentials);
            $qb->refreshTokenIfNeeded();
            $connection->refresh();

            // Multiple sequential QB API calls can exceed PHP's default 30s limit.
            // The curl layer has its own 60s per-request timeout, so we defer to that.
            set_time_limit(0);

            $endDate = Carbon::now()->format('Y-m-d');
            $startDate = Carbon::now()->subMonths(23)->startOfMonth()->format('Y-m-d');

            $files = QuickBooksCSVExportService::generateAllCSVFiles($connection, $credentials, $startDate, $endDate);
            try {
                return [
                    'balance_sheet_monthly' => self::readBalanceSheetCsv(data_get($files, 'balance_sheet.path')),
                    'cash_flow_monthly' => self::readCashFlowCsv(data_get($files, 'cash_flow.path')),
                    'profit_loss_monthly' => self::readProfitLossCsv(data_get($files, 'profit_loss.path')),
                    'ar_rows' => self::readAgingCsv(data_get($files, 'ar_aging.path'), 'customer'),
                    'ap_rows' => self::readAgingCsv(data_get($files, 'ap_aging.path'), 'vendor'),
                ];
            } finally {
                QuickBooksCSVExportService::cleanupExportFiles($files);
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Build the same structure as live QB export from ReportAttachment CSVs (insight zip / stored files).
     */
    private static function datasetFromLatestAnalyzedReportCsvs(int $companyId, int $userId): ?array
    {
        $report = Report::query()
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
            ->where(function ($q) use ($companyId, $userId) {
                $q->where('company_id', $companyId);
                $user = User::query()->find($userId);
                if ($user && (int) $user->company_id === $companyId) {
                    $q->orWhereNull('company_id');
                }
            })
            ->with('attachments')
            ->orderByDesc('created_at')
            ->first();

        if (! $report || $report->attachments->isEmpty()) {
            return null;
        }

        $paths = [
            'balance_sheet' => null,
            'cash_flow' => null,
            'profit_loss' => null,
            'ar_aging' => null,
            'ap_aging' => null,
        ];

        foreach ($report->attachments as $att) {
            $kind = self::classifyInsightCsvFilename((string) ($att->original_filename ?? ''));
            if (! $kind || ($paths[$kind] ?? null) !== null) {
                continue;
            }
            $diskPath = 'reports/' . $att->attachment;
            if (! Storage::disk('public')->exists($diskPath)) {
                continue;
            }
            $paths[$kind] = Storage::disk('public')->path($diskPath);
        }

        if (! $paths['balance_sheet'] && ! $paths['profit_loss'] && ! $paths['cash_flow']) {
            return null;
        }

        return [
            'balance_sheet_monthly' => self::readBalanceSheetCsv($paths['balance_sheet']),
            'cash_flow_monthly' => self::readCashFlowCsv($paths['cash_flow']),
            'profit_loss_monthly' => self::readProfitLossCsv($paths['profit_loss']),
            'ar_rows' => self::readAgingCsv($paths['ar_aging'], 'customer'),
            'ap_rows' => self::readAgingCsv($paths['ap_aging'], 'vendor'),
        ];
    }

    private static function classifyInsightCsvFilename(string $name): ?string
    {
        $n = strtolower(str_replace([' ', '-'], '_', pathinfo($name, PATHINFO_FILENAME)));

        if (str_contains($n, 'balance') && str_contains($n, 'sheet')) {
            return 'balance_sheet';
        }
        if (str_contains($n, 'cash') && str_contains($n, 'flow')) {
            return 'cash_flow';
        }
        if (str_contains($n, 'profit') || str_contains($n, 'loss') || str_contains($n, 'p_and_l')) {
            return 'profit_loss';
        }
        if (str_contains($n, 'ar') && str_contains($n, 'aging')) {
            return 'ar_aging';
        }
        if (str_contains($n, 'ap') && str_contains($n, 'aging')) {
            return 'ap_aging';
        }

        return null;
    }

    private static function resolveCredentials(int $companyId): ?array
    {
        $setting = CompanyQuickbooksSetting::where('company_id', $companyId)->first();
        if (! $setting) {
            $parentId = Company::withoutGlobalScopes()->where('id', $companyId)->value('created_by');
            if ($parentId) {
                $setting = CompanyQuickbooksSetting::where('company_id', $parentId)->first();
            }
        }
        if (! $setting) {
            return null;
        }

        return [
            'client_id' => $setting->client_id,
            'client_secret' => $setting->client_secret,
            'redirect_uri' => $setting->redirect_uri,
            'base_url' => $setting->base_url,
        ];
    }

    private static function readCsv(string $path): array
    {
        if (! $path || ! is_file($path)) {
            return [];
        }

        $rows = [];
        $h = fopen($path, 'r');
        if ($h === false) {
            return [];
        }
        try {
            $header = fgetcsv($h);
            if (! is_array($header)) {
                return [];
            }
            while (($row = fgetcsv($h)) !== false) {
                if (! is_array($row)) {
                    continue;
                }
                $rows[] = array_pad($row, count($header), null);
            }
        } finally {
            fclose($h);
        }

        return $rows;
    }

    private static function n($v): float
    {
        return (float) (KpiService::normalizeNumber($v) ?? 0.0);
    }

    private static function readBalanceSheetCsv(?string $path): array
    {
        $out = [];
        foreach (self::readCsv((string) $path) as $r) {
            $out[] = [
                'month' => (string) ($r[0] ?? ''),
                'cash' => self::n($r[1] ?? 0),
                'ar' => self::n($r[2] ?? 0),
                'inventory' => self::n($r[3] ?? 0),
                'fixed_assets' => self::n($r[4] ?? 0),
                'total_assets' => self::n($r[5] ?? 0),
                'ap' => self::n($r[6] ?? 0),
                'credit_cards' => self::n($r[7] ?? 0),
                'loans' => self::n($r[8] ?? 0),
                'owners_equity' => self::n($r[9] ?? 0),
                'undeposited_funds' => self::n($r[10] ?? 0),
                'current_assets' => self::n($r[11] ?? 0),
                'current_liabilities' => abs(self::n($r[12] ?? 0)),
            ];
        }

        return $out;
    }

    private static function readCashFlowCsv(?string $path): array
    {
        $out = [];
        foreach (self::readCsv((string) $path) as $r) {
            $out[] = [
                'month' => (string) ($r[0] ?? ''),
                'net_income' => self::n($r[1] ?? 0),
                'operating' => self::n($r[2] ?? 0),
                'investing' => self::n($r[3] ?? 0),
                'financing' => self::n($r[4] ?? 0),
                'net_change' => self::n($r[5] ?? 0),
                'ending_cash' => self::n($r[6] ?? 0),
            ];
        }

        return $out;
    }

    private static function readProfitLossCsv(?string $path): array
    {
        $out = [];
        foreach (self::readCsv((string) $path) as $r) {
            $out[] = [
                'month' => (string) ($r[0] ?? ''),
                'revenue' => self::n($r[1] ?? 0),
                'cogs' => self::n($r[2] ?? 0),
                'gross_profit' => self::n($r[3] ?? 0),
                'payroll' => self::n($r[4] ?? 0),
                'marketing' => self::n($r[5] ?? 0),
                'rent' => self::n($r[6] ?? 0),
                'software' => self::n($r[7] ?? 0),
                'misc' => self::n($r[8] ?? 0),
                'operating_income' => self::n($r[9] ?? 0),
                'net_income' => self::n($r[10] ?? 0),
            ];
        }

        return $out;
    }

    private static function readAgingCsv(?string $path, string $kind): array
    {
        $out = [];
        foreach (self::readCsv((string) $path) as $r) {
            $name = trim((string) ($r[0] ?? ''));
            if ($name === '') {
                continue;
            }
            $out[] = [
                $kind => $name,
                'current' => self::n($r[1] ?? 0),
                '1_30' => self::n($r[2] ?? 0),
                '31_60' => self::n($r[3] ?? 0),
                '61_90' => self::n($r[4] ?? 0),
                'over_90' => self::n($r[5] ?? 0),
                'total' => self::n($r[6] ?? 0),
            ];
        }

        return $out;
    }
}
