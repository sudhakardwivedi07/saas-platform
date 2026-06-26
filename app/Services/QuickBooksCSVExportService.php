<?php

namespace App\Services;

use App\Models\QuickBooksConnection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Fetches QB financial data via direct HTTP and writes CSV files for insights generation.
 * Adapted from cfo_old; credentials are resolved externally and passed as $credentials array.
 */
class QuickBooksCSVExportService
{
    // ── Public entry point ────────────────────────────────────────────────────

    /**
     * Generate all 6 CSV files and return paths keyed by API field name.
     */
    public static function generateAllCSVFiles(
        QuickBooksConnection $connection,
        array $credentials,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $endDate   = $endDate   ?? Carbon::now()->format('Y-m-d');
        $startDate = $startDate ?? Carbon::now()->subMonths(11)->startOfMonth()->format('Y-m-d');

        $userId    = $connection->user_id;
        $timestamp = time();
        $basePath  = storage_path("app/quickbooks-exports/{$userId}/{$timestamp}");

        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }

        $baseUrl = self::resolveBaseUrl($credentials);
        $files   = [];

        try {
            $files['balance_sheet']  = self::generateBalanceSheetCSV($connection, $baseUrl, $startDate, $endDate, $basePath, $timestamp);
            $files['ar_aging']       = self::generateARAgingCSV($connection, $baseUrl, $startDate, $endDate, $basePath, $timestamp);
            $files['ap_aging']       = self::generateAPAgingCSV($connection, $baseUrl, $startDate, $endDate, $basePath, $timestamp);
            $files['profit_loss']    = self::generateProfitLossCSV($connection, $baseUrl, $startDate, $endDate, $basePath, $timestamp);
            $files['general_ledger'] = self::generateGeneralLedgerCSV($connection, $baseUrl, $startDate, $endDate, $basePath, $timestamp);
            $files['cash_flow']      = self::generateCashFlowCSV($connection, $baseUrl, $startDate, $endDate, $basePath, $timestamp);

            Log::info('QuickBooksCSVExportService: all files generated', [
                'user_id'    => $userId,
                'date_range' => "{$startDate} to {$endDate}",
                'files'      => array_keys($files),
            ]);

            return $files;
        } catch (Exception $e) {
            Log::error('QuickBooksCSVExportService: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function cleanupExportFiles(array $files): void
    {
        foreach ($files as $fileInfo) {
            $path = $fileInfo['path'] ?? null;
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    // ── CSV generators ────────────────────────────────────────────────────────

    protected static function generateBalanceSheetCSV(
        QuickBooksConnection $connection, string $baseUrl,
        string $startDate, string $endDate, string $basePath, string $timestamp
    ): array {
        $filename = 'Balance_Sheet.csv';
        $filepath = "{$basePath}/{$filename}";

        $monthlyData = self::fetchMonthlyBalanceSheetData($connection, $baseUrl, $startDate, $endDate);

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Month', 'Cash', 'AR', 'Inventory', 'Fixed Assets', 'Total Assets',
            'AP', 'Credit Cards', 'Loans', "Owner's Equity", 'Undeposited Funds',
            'Current Assets', 'Current Liabilities']);

        foreach ($monthlyData as $row) {
            fputcsv($handle, [
                $row['month'], $row['cash'], $row['ar'], $row['inventory'],
                $row['fixed_assets'], $row['total_assets'], $row['ap'],
                $row['credit_cards'], $row['loans'], $row['owners_equity'],
                $row['undeposited_funds'] ?? 0,
                $row['current_assets'] ?? 0,
                $row['current_liabilities'] ?? 0,
            ]);
        }
        fclose($handle);

        Log::info('Balance Sheet CSV generated', ['rows' => count($monthlyData)]);
        return ['path' => $filepath, 'name' => $filename];
    }

    protected static function generateARAgingCSV(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate, string $basePath, string $timestamp
    ): array {
        $filename = 'AR_Aging.csv';
        $filepath = "{$basePath}/{$filename}";

        $arData = self::fetchARAgingData($connection, $baseUrl, $startDate, $endDate);

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Customer', 'Current', '1-30', '31-60', '61-90', '>90', 'Total']);
        foreach ($arData as $row) {
            fputcsv($handle, [$row['customer'], $row['current'], $row['1_30'],
                $row['31_60'], $row['61_90'], $row['over_90'], $row['total']]);
        }
        fclose($handle);

        Log::info('AR Aging CSV generated', ['rows' => count($arData)]);
        return ['path' => $filepath, 'name' => $filename];
    }

    protected static function generateAPAgingCSV(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate, string $basePath, string $timestamp
    ): array {
        $filename = 'AP_Aging.csv';
        $filepath = "{$basePath}/{$filename}";

        $apData = self::fetchAPAgingData($connection, $baseUrl, $startDate, $endDate);

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Vendor', 'Current', '1-30', '31-60', '61-90', '>90', 'Total']);
        foreach ($apData as $row) {
            fputcsv($handle, [$row['vendor'], $row['current'], $row['1_30'],
                $row['31_60'], $row['61_90'] ?? 0, $row['over_90'], $row['total']]);
        }
        fclose($handle);

        Log::info('AP Aging CSV generated', ['rows' => count($apData)]);
        return ['path' => $filepath, 'name' => $filename];
    }

    protected static function generateProfitLossCSV(
        QuickBooksConnection $connection, string $baseUrl,
        string $startDate, string $endDate, string $basePath, string $timestamp
    ): array {
        $filename = 'Profit_and_Loss.csv';
        $filepath = "{$basePath}/{$filename}";

        $monthlyData = self::fetchMonthlyPLData($connection, $baseUrl, $startDate, $endDate);

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Month', 'Revenue', 'COGS', 'Gross Profit', 'Payroll',
            'Marketing', 'Rent', 'Software', 'Misc', 'Operating Income', 'Net Income']);
        foreach ($monthlyData as $row) {
            fputcsv($handle, [
                $row['month'], $row['revenue'], $row['cogs'], $row['gross_profit'],
                $row['payroll'], $row['marketing'], $row['rent'], $row['software'],
                $row['misc'], $row['operating_income'], $row['net_income'],
            ]);
        }
        fclose($handle);

        Log::info('P&L CSV generated', ['rows' => count($monthlyData)]);
        return ['path' => $filepath, 'name' => $filename];
    }

    protected static function generateGeneralLedgerCSV(
        QuickBooksConnection $connection, string $baseUrl,
        string $startDate, string $endDate, string $basePath, string $timestamp
    ): array {
        $filename = 'General_Ledger.csv';
        $filepath = "{$basePath}/{$filename}";

        $glData = self::fetchGeneralLedgerData($connection, $baseUrl, $startDate, $endDate);

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Date', 'Account', 'Description', 'Debit', 'Credit', 'Vendor/Customer', 'Dept']);
        foreach ($glData as $row) {
            fputcsv($handle, [$row['date'], $row['account'], $row['description'],
                $row['debit'], $row['credit'], $row['vendor_customer'], $row['dept']]);
        }
        fclose($handle);

        Log::info('General Ledger CSV generated', ['rows' => count($glData)]);
        return ['path' => $filepath, 'name' => $filename];
    }

    protected static function generateCashFlowCSV(
        QuickBooksConnection $connection, string $baseUrl,
        string $startDate, string $endDate, string $basePath, string $timestamp
    ): array {
        $filename = 'Cash_Flow.csv';
        $filepath = "{$basePath}/{$filename}";

        $cfData = self::fetchCashFlowData($connection, $baseUrl, $startDate, $endDate);

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Month', 'Net Income', 'Operating', 'Investing', 'Financing',
            'Net Change in Cash', 'Ending Cash']);
        foreach ($cfData as $row) {
            fputcsv($handle, [$row['month'], $row['net_income'], $row['operating'],
                $row['investing'], $row['financing'], $row['net_change'], $row['ending_cash']]);
        }
        fclose($handle);

        Log::info('Cash Flow CSV generated', ['rows' => count($cfData)]);
        return ['path' => $filepath, 'name' => $filename];
    }

    // ── QB API data fetchers ──────────────────────────────────────────────────

    /**
     * Fetch AR aging by querying Invoices within the selected date range (by TxnDate),
     * then calculate aging buckets ourselves relative to $endDate.
     * This ensures only period-relevant invoices appear, correctly distributed across buckets.
     */
    protected static function fetchARAgingData(QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate): array
    {
        $endTs   = strtotime($endDate);
        $result  = [];
        $offset  = 1;
        $perPage = 100;

        do {
            $sql  = "SELECT * FROM Invoice WHERE TxnDate >= '{$startDate}' AND TxnDate <= '{$endDate}' STARTPOSITION {$offset} MAXRESULTS {$perPage}";
            $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/query?" . http_build_query(['query' => $sql]);
            $resp = self::curlGet($url, $connection->access_token);

            if ($resp['code'] !== 200 || empty($resp['body'])) break;

            $body     = json_decode($resp['body'], true);
            $invoices = $body['QueryResponse']['Invoice'] ?? [];
            $fetched  = count($invoices);

            foreach ($invoices as $inv) {
                $customer = $inv['CustomerRef']['name'] ?? 'Unknown';

                // Always register the customer so they appear even if fully paid
                if (!isset($result[$customer])) {
                    $result[$customer] = ['customer' => $customer, 'current' => 0.0, '1_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, 'over_90' => 0.0, 'total' => 0.0];
                }

                $balance = round((float) ($inv['Balance'] ?? 0), 2);
                if ($balance <= 0) continue; // registered but nothing to add to buckets

                $dueDate     = $inv['DueDate'] ?? $inv['TxnDate'] ?? $endDate;
                $daysOverdue = (int) floor(($endTs - strtotime($dueDate)) / 86400);

                $result[$customer]['total'] += $balance;
                if ($daysOverdue <= 0)      $result[$customer]['current'] += $balance;
                elseif ($daysOverdue <= 30) $result[$customer]['1_30']    += $balance;
                elseif ($daysOverdue <= 60) $result[$customer]['31_60']   += $balance;
                elseif ($daysOverdue <= 90) $result[$customer]['61_90']   += $balance;
                else                        $result[$customer]['over_90'] += $balance;
            }

            $offset += $fetched;
        } while ($fetched === $perPage);

        // Fallback: no invoices in the selected period → use AgedReceivables report as-of endDate
        if (empty($result)) {
            Log::info('AR Aging: no invoices in range, falling back to AgedReceivables report', ['range' => "{$startDate} to {$endDate}"]);
            return self::fetchARAgingFromReport($connection, $baseUrl, $startDate, $endDate);
        }

        Log::info('AR Aging fetched via Invoice query', ['records' => count($result), 'range' => "{$startDate} to {$endDate}"]);
        return array_values($result);
    }

    protected static function fetchARAgingFromReport(QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate): array
    {
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/AgedReceivables?report_date={$endDate}";
        $resp = self::curlGet($url, $connection->access_token);
        if ($resp['code'] !== 200 || empty($resp['body'])) return [];
        $data = json_decode($resp['body'], true);
        $rows = isset($data['Rows']['Row']) ? self::parseAgingRows($data['Rows']['Row'], 'customer') : [];
        return self::applyPeriodConstraint($rows, $startDate, $endDate);
    }

    /**
     * Fetch AP aging by querying Bills within the selected date range (by TxnDate),
     * then calculate aging buckets ourselves relative to $endDate.
     * Falls back to AgedPayables report if no bills exist in the period.
     */
    protected static function fetchAPAgingData(QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate): array
    {
        $endTs   = strtotime($endDate);
        $result  = [];
        $offset  = 1;
        $perPage = 100;

        do {
            $sql  = "SELECT * FROM Bill WHERE TxnDate >= '{$startDate}' AND TxnDate <= '{$endDate}' STARTPOSITION {$offset} MAXRESULTS {$perPage}";
            $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/query?" . http_build_query(['query' => $sql]);
            $resp = self::curlGet($url, $connection->access_token);

            if ($resp['code'] !== 200 || empty($resp['body'])) break;

            $body    = json_decode($resp['body'], true);
            $bills   = $body['QueryResponse']['Bill'] ?? [];
            $fetched = count($bills);

            foreach ($bills as $bill) {
                $vendor = $bill['VendorRef']['name'] ?? 'Unknown';

                // Always register the vendor so they appear even if fully paid
                if (!isset($result[$vendor])) {
                    $result[$vendor] = ['vendor' => $vendor, 'current' => 0.0, '1_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, 'over_90' => 0.0, 'total' => 0.0];
                }

                $balance = round((float) ($bill['Balance'] ?? 0), 2);
                if ($balance <= 0) continue; // registered but nothing to add to buckets

                $dueDate     = $bill['DueDate'] ?? $bill['TxnDate'] ?? $endDate;
                $daysOverdue = (int) floor(($endTs - strtotime($dueDate)) / 86400);

                $result[$vendor]['total'] += $balance;
                if ($daysOverdue <= 0)      $result[$vendor]['current'] += $balance;
                elseif ($daysOverdue <= 30) $result[$vendor]['1_30']    += $balance;
                elseif ($daysOverdue <= 60) $result[$vendor]['31_60']   += $balance;
                elseif ($daysOverdue <= 90) $result[$vendor]['61_90']   += $balance;
                else                        $result[$vendor]['over_90'] += $balance;
            }

            $offset += $fetched;
        } while ($fetched === $perPage);

        // Fallback: no bills in the selected period → use AgedPayables report as-of endDate
        if (empty($result)) {
            Log::info('AP Aging: no bills in range, falling back to AgedPayables report', ['range' => "{$startDate} to {$endDate}"]);
            return self::fetchAPAgingFromReport($connection, $baseUrl, $startDate, $endDate);
        }

        Log::info('AP Aging fetched via Bill query', ['records' => count($result), 'range' => "{$startDate} to {$endDate}"]);
        return array_values($result);
    }

    protected static function fetchAPAgingFromReport(QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate): array
    {
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/AgedPayables?report_date={$endDate}";
        $resp = self::curlGet($url, $connection->access_token);
        if ($resp['code'] !== 200 || empty($resp['body'])) return [];
        $data = json_decode($resp['body'], true);
        $rows = isset($data['Rows']['Row']) ? self::parseAgingRows($data['Rows']['Row'], 'vendor') : [];
        return self::applyPeriodConstraint($rows, $startDate, $endDate);
    }

    /**
     * Zero out aging buckets whose minimum day threshold exceeds the selected period length.
     *
     * Example: "This Quarter" = 86 days → ">90" bucket is impossible for period-specific
     * transactions, so zero it out. Recalculate total from remaining bucket values.
     */
    protected static function applyPeriodConstraint(array $rows, string $startDate, string $endDate): array
    {
        $periodDays = (int) floor((strtotime($endDate) - strtotime($startDate)) / 86400);

        foreach ($rows as &$row) {
            if ($periodDays < 91) { $row['over_90'] = 0.0; }
            if ($periodDays < 61) { $row['61_90']   = 0.0; }
            if ($periodDays < 31) { $row['31_60']   = 0.0; }
            if ($periodDays < 1)  { $row['1_30']    = 0.0; }

            $row['total'] = round(
                ($row['current'] ?? 0.0) + ($row['1_30'] ?? 0.0) + ($row['31_60'] ?? 0.0) +
                ($row['61_90'] ?? 0.0)   + ($row['over_90'] ?? 0.0),
                2
            );
        }
        unset($row);

        return $rows;
    }

    /**
     * Parse AR or AP aging summary rows.
     * ColData structure: [Name, Current, 1-30, 31-60, 61-90, >90, Total]
     */
    protected static function parseAgingRows(array $rows, string $nameKey): array
    {
        $result     = [];
        $parseValue = fn($v) => (empty($v) || $v === '-') ? 0 : (float) str_replace(',', '', $v);

        foreach ($rows as $row) {
            if (isset($row['ColData'])) {
                $c    = $row['ColData'];
                $name = $c[0]['value'] ?? '';
                if (empty($name) || stripos($name, 'total') !== false) continue;

                $result[] = [
                    $nameKey  => $name,
                    'current' => $parseValue($c[1]['value'] ?? 0),
                    '1_30'    => $parseValue($c[2]['value'] ?? 0),
                    '31_60'   => $parseValue($c[3]['value'] ?? 0),
                    '61_90'   => $parseValue($c[4]['value'] ?? 0),
                    'over_90' => $parseValue($c[5]['value'] ?? 0),
                    'total'   => $parseValue($c[6]['value'] ?? 0),
                ];
            } elseif (isset($row['Rows']['Row'])) {
                $result = array_merge($result, self::parseAgingRows($row['Rows']['Row'], $nameKey));
            }
        }

        return $result;
    }

    protected static function fetchMonthlyPLData(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate
    ): array {
        $start        = Carbon::parse($startDate);
        $end          = Carbon::parse($endDate);
        $currentMonth = $start->copy()->startOfMonth();
        $monthlyData  = [];

        while ($currentMonth->lte($end)) {
            $mStart = $currentMonth->format('Y-m-d');
            $mEnd   = min($currentMonth->copy()->endOfMonth()->format('Y-m-d'), $endDate);

            $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/ProfitAndLoss"
                  . "?start_date={$mStart}&end_date={$mEnd}&accounting_method=Accrual";
            $resp = self::curlGet($url, $connection->access_token);

            $row = [
                'month'            => $currentMonth->format('Y-m'),
                'revenue'          => 0,
                'cogs'             => 0,
                'gross_profit'     => 0,
                'payroll'          => 0,
                'marketing'        => 0,
                'rent'             => 0,
                'software'         => 0,
                'misc'             => 0,
                'operating_income' => 0,
                'net_income'       => 0,
            ];

            if ($resp['code'] === 200 && !empty($resp['body'])) {
                $reportData = json_decode($resp['body'], true);
                if (isset($reportData['Rows']['Row'])) {
                    $row = self::parsePLRow($reportData['Rows']['Row'], $currentMonth->format('Y-m'));
                }
            }

            $monthlyData[] = $row;
            $currentMonth->addMonth();
        }

        return $monthlyData;
    }

    protected static function parsePLRow(array $rows, string $month): array
    {
        $data = [
            'month'            => $month,
            'revenue'          => 0,
            'cogs'             => 0,
            'gross_profit'     => 0,
            'payroll'          => 0,
            'marketing'        => 0,
            'rent'             => 0,
            'software'         => 0,
            'misc'             => 0,
            'operating_income' => 0,
            'net_income'       => 0,
        ];

        $parseVal      = fn($v) => is_numeric(str_replace(',', '', $v ?? '')) ? (float) str_replace(',', '', $v) : 0;
        $totalExpenses = 0;

        foreach ($rows as $row) {
            $group = $row['group'] ?? '';

            // Summary value (section totals)
            $val = 0;
            if (isset($row['Summary']['ColData'])) {
                foreach ($row['Summary']['ColData'] as $col) {
                    $v = str_replace(',', '', $col['value'] ?? '');
                    if (is_numeric($v)) { $val = (float) $v; break; }
                }
            }

            // Track total expenses separately; do NOT write it to misc
            if ($group === 'Expenses') {
                $totalExpenses = abs($val);
            } elseif ($group === 'OtherExpenses' || $group === 'OtherExpense') {
                $totalExpenses += abs($val);
            }

            match ($group) {
                'Income'              => $data['revenue']          = abs($val),
                'COGS'                => $data['cogs']             = abs($val),
                'NetOperatingIncome'  => $data['operating_income'] = $val,
                'NetIncome'           => $data['net_income']       = $val,
                default               => null,
            };

            // Drill into expense sub-accounts — only for expense sections
            if (($group === 'Expenses' || $group === 'OtherExpenses' || $group === 'OtherExpense') && isset($row['Rows']['Row'])) {
                self::parsePLExpenses($row['Rows']['Row'], $data, $parseVal);
            }
        }

        // Always derive gross_profit from revenue - cogs (matches CFO behaviour)
        $data['gross_profit'] = $data['revenue'] - $data['cogs'];

        // Fallback: calculate operating_income if not returned by QB as NetOperatingIncome
        if ($data['operating_income'] == 0 && $totalExpenses > 0) {
            $data['operating_income'] = $data['gross_profit'] - $totalExpenses;
        }

        return $data;
    }

    /**
     * Recursively categorise P&L expense rows into named buckets.
     * Only leaf nodes (no child rows) contribute values — parent section totals are
     * skipped to avoid double-counting when accounts are nested multiple levels deep.
     */
    protected static function parsePLExpenses(array $rows, array &$data, callable $parseVal): void
    {
        foreach ($rows as $sub) {
            // Section node — recurse into children and skip the section total itself.
            if (isset($sub['Rows']['Row'])) {
                self::parsePLExpenses($sub['Rows']['Row'], $data, $parseVal);
                continue;
            }

            // Leaf node — extract its value.
            $name   = strtolower($sub['ColData'][0]['value'] ?? $sub['Header']['ColData'][0]['value'] ?? '');
            $subVal = 0;

            if (isset($sub['ColData'][1]['value'])) {
                $subVal = abs($parseVal($sub['ColData'][1]['value']));
            } elseif (isset($sub['Summary']['ColData'])) {
                foreach ($sub['Summary']['ColData'] as $sc) {
                    $sv = str_replace(',', '', $sc['value'] ?? '');
                    if (is_numeric($sv)) { $subVal = abs((float) $sv); break; }
                }
            }

            if ($subVal <= 0) continue;

            if (str_contains($name, 'payroll') || str_contains($name, 'salary') || str_contains($name, 'wages')) {
                $data['payroll'] += $subVal;
            } elseif (str_contains($name, 'market') || str_contains($name, 'advertis')) {
                $data['marketing'] += $subVal;
            } elseif (str_contains($name, 'rent') || str_contains($name, 'lease')) {
                $data['rent'] += $subVal;
            } elseif (str_contains($name, 'software') || str_contains($name, 'subscription') || str_contains($name, 'saas')) {
                $data['software'] += $subVal;
            } elseif (str_contains($name, 'miscellaneous') || $name === 'misc' || str_contains($name, 'other misc')) {
                $data['misc'] += $subVal;
            }
        }
    }

    protected static function fetchMonthlyBalanceSheetData(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate
    ): array {
        // QB omits a month column when end_date falls mid-month — extend to end-of-month so all
        // months within the selected period are included (QB won't have future-dated transactions).
        $apiEndDate = Carbon::parse($endDate)->endOfMonth()->format('Y-m-d');

        // Try multi-month report first (summarize_column_by=Month)
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/BalanceSheet"
              . "?start_date={$startDate}&end_date={$apiEndDate}&summarize_column_by=Month&accounting_method=Accrual";
        $resp = self::curlGet($url, $connection->access_token);

        if ($resp['code'] === 200 && !empty($resp['body'])) {
            $reportData = json_decode($resp['body'], true);
            if (!empty($reportData['Columns']['Column']) && !empty($reportData['Rows']['Row'])) {
                return self::parseMultiMonthBalanceSheet($reportData);
            }
        }

        // Fallback: fetch month by month
        return self::fetchBalanceSheetMonthByMonth($connection, $baseUrl, $startDate, $endDate);
    }

    protected static function parseMultiMonthBalanceSheet(array $reportData): array
    {
        $columns     = $reportData['Columns']['Column'] ?? [];
        $monthLabels = [];

        foreach ($columns as $index => $col) {
            if ($index === 0) continue;
            $title = $col['ColTitle'] ?? '';
            if (!empty($title) && $title !== 'Total') {
                $parsed = self::parseMonthLabel($title);
                if ($parsed) $monthLabels[$index] = $parsed;
            }
        }

        $monthlyData = [];
        foreach ($monthLabels as $idx => $label) {
            $monthlyData[$idx] = self::emptyBSRow($label);
        }

        if (!empty($reportData['Rows']['Row'])) {
            self::extractMultiMonthBSValues($reportData['Rows']['Row'], $monthlyData, $monthLabels);
        }

        // Fixed Assets section rows store their totals only in Summary.ColData at arbitrary
        // nesting depth. A targeted tree search reliably catches what the general pass misses.
        self::findFixedAssetsInTree($reportData, $monthlyData, $monthLabels);

        return array_values($monthlyData);
    }

    protected static function findFixedAssetsInTree(array $node, array &$monthlyData, array $monthLabels): void
    {
        if (isset($node['Summary']['ColData'])) {
            $summaryData = $node['Summary']['ColData'];
            $label       = strtolower($summaryData[0]['value'] ?? '');

            if (str_contains($label, 'total for fixed assets') || str_contains($label, 'total fixed assets')) {
                foreach ($monthLabels as $colIndex => $monthLabel) {
                    if (!isset($monthlyData[$colIndex])) continue;
                    if ($monthlyData[$colIndex]['fixed_assets'] != 0) continue; // already captured

                    $raw = str_replace([',', '$', ' '], '', $summaryData[$colIndex]['value'] ?? '');
                    if (is_numeric($raw) && (float) $raw != 0) {
                        $monthlyData[$colIndex]['fixed_assets'] = (float) $raw;
                    }
                }
                return; // found — no need to recurse further
            }
        }

        if (isset($node['Rows']['Row']) && is_array($node['Rows']['Row'])) {
            foreach ($node['Rows']['Row'] as $row) {
                self::findFixedAssetsInTree($row, $monthlyData, $monthLabels);
            }
        }
    }

    protected static function fetchBalanceSheetMonthByMonth(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate
    ): array {
        $start        = Carbon::parse($startDate);
        $end          = Carbon::parse($endDate);
        $currentMonth = $start->copy()->startOfMonth();
        $monthlyData  = [];

        while ($currentMonth->lte($end)) {
            $asOf     = min($currentMonth->copy()->endOfMonth()->format('Y-m-d'), $endDate);
            $url      = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/BalanceSheet"
                      . "?as_of_date={$asOf}&accounting_method=Accrual";
            $resp     = self::curlGet($url, $connection->access_token);
            $row      = self::emptyBSRow($currentMonth->format('Y-m'));

            if ($resp['code'] === 200 && !empty($resp['body'])) {
                $data = json_decode($resp['body'], true);
                if (isset($data['Rows']['Row'])) {
                    $row = self::parseSingleMonthBS($data['Rows']['Row'], $currentMonth->format('Y-m'));
                }
            }

            $monthlyData[] = $row;
            $currentMonth->addMonth();
        }

        return $monthlyData;
    }

    protected static function parseSingleMonthBS(array $rows, string $month): array
    {
        $data = self::emptyBSRow($month);
        self::extractSingleMonthBSValues($rows, $data);
        return $data;
    }

    protected static function extractSingleMonthBSValues(array $rows, array &$data, string $section = ''): void
    {
        foreach ($rows as $row) {
            $group  = $row['group'] ?? '';
            $name   = strtolower($row['Header']['ColData'][0]['value'] ?? $row['ColData'][0]['value'] ?? '');
            $curSec = $section;
            if ($group === 'Asset' || $group === 'TotalAssets')         $curSec = 'assets';
            elseif ($group === 'Liability' || $group === 'TotalLiabilities') $curSec = 'liabilities';
            elseif ($group === 'Equity' || $group === 'TotalEquity')    $curSec = 'equity';

            $val = 0;

            // Summary value always takes priority (section totals)
            if (isset($row['Summary']['ColData'])) {
                foreach ($row['Summary']['ColData'] as $c) {
                    $v = str_replace([',', '$', ' '], '', $c['value'] ?? '');
                    if (is_numeric($v)) { $val = (float) $v; break; }
                }
            }
            // Data row value — only when Summary produced nothing to avoid overwrite
            if ($val == 0 && $group !== 'TotalAssets' && isset($row['ColData'])) {
                foreach (array_slice($row['ColData'], 1) as $c) {
                    $v = str_replace(',', '', $c['value'] ?? '');
                    if (is_numeric($v)) { $val = (float) $v; break; }
                }
            }

            if ($val != 0) self::mapBSValue($name, $val, $group, $curSec, $data);

            if (isset($row['Rows']['Row'])) {
                self::extractSingleMonthBSValues($row['Rows']['Row'], $data, $curSec);
            }
        }
    }

    protected static function extractMultiMonthBSValues(array $rows, array &$monthlyData, array $monthLabels, string $section = ''): void
    {
        foreach ($rows as $row) {
            $group  = $row['group'] ?? '';
            $name   = strtolower($row['Header']['ColData'][0]['value'] ?? $row['ColData'][0]['value'] ?? '');
            $curSec = $section;
            if ($group === 'Asset' || $group === 'TotalAssets')         $curSec = 'assets';
            elseif ($group === 'Liability' || $group === 'TotalLiabilities') $curSec = 'liabilities';
            elseif ($group === 'Equity' || $group === 'TotalEquity')    $curSec = 'equity';

            // Use ColData (multi-month columns)
            $colData = $row['ColData'] ?? [];
            if (!empty($colData)) {
                foreach ($monthLabels as $colIndex => $monthLabel) {
                    if (!isset($monthlyData[$colIndex])) continue;
                    $v = str_replace(',', '', $colData[$colIndex]['value'] ?? '');
                    if (!is_numeric($v) || (float) $v == 0) continue;
                    self::mapBSValue($name, (float) $v, $group, $curSec, $monthlyData[$colIndex]);
                }
            }

            // Summary rows
            $summaryData = $row['Summary']['ColData'] ?? [];
            if (!empty($summaryData)) {
                $summaryName = strtolower($summaryData[0]['value'] ?? '');
                if (str_contains($summaryName, 'total')) {
                    foreach ($monthLabels as $colIndex => $monthLabel) {
                        if (!isset($monthlyData[$colIndex])) continue;
                        $v = str_replace(',', '', $summaryData[$colIndex]['value'] ?? '');
                        if (!is_numeric($v) || (float) $v == 0) continue;
                        self::mapBSValue($summaryName, (float) $v, $group, $curSec, $monthlyData[$colIndex]);
                    }
                }
            }

            if (isset($row['Rows']['Row'])) {
                self::extractMultiMonthBSValues($row['Rows']['Row'], $monthlyData, $monthLabels, $curSec);
            }
        }
    }

    protected static function mapBSValue(string $name, float $val, string $group, string $section, array &$data): void
    {
        if ($group === 'TotalAssets' || (str_contains($name, 'total') && str_contains($name, 'asset') && !str_contains($name, 'current') && !str_contains($name, 'fixed'))) {
            $data['total_assets'] = $val;
        } elseif (preg_match('/\btotal\s+(for\s+)?bank(\s+accounts?)?\b/i', $name)) {
            $data['cash'] = $val;
        } elseif ((str_contains($name, 'checking') || str_contains($name, 'savings') || str_contains($name, 'cash on hand') || str_contains($name, 'petty cash') || str_contains($name, 'money market') || preg_match('/^cash$/i', trim($name)) || preg_match('/^cash\s/i', trim($name))) && !str_contains($name, 'undeposited') && !str_contains($name, 'flow')) {
            if (!str_contains($name, 'total')) $data['cash'] += $val;
        } elseif (str_contains($name, 'total for accounts receivable')) {
            $data['ar'] = $val;
        } elseif ((str_contains($name, 'accounts receivable') || str_contains($name, 'a/r')) && !str_contains($name, 'total') && $data['ar'] == 0) {
            $data['ar'] += $val;
        } elseif (str_contains($name, 'inventory')) {
            if (str_contains($name, 'total')) $data['inventory'] = $val;
            elseif ($data['inventory'] == 0) $data['inventory'] = $val;
        } elseif (str_contains($name, 'total for fixed assets') || str_contains($name, 'total fixed assets') || $name === 'fixed assets') {
            $data['fixed_assets'] = $val;
        } elseif (str_contains($name, 'total for accounts payable') || (str_contains($name, 'total') && str_contains($name, 'accounts payable'))) {
            $data['ap'] = -abs($val);
        } elseif ((str_contains($name, 'accounts payable') || str_contains($name, 'a/p')) && !str_contains($name, 'total') && $data['ap'] == 0) {
            $data['ap'] = -abs($val);
        } elseif (str_contains($name, 'total for credit card') || (str_contains($name, 'total') && str_contains($name, 'credit card'))) {
            $data['credit_cards'] = -abs($val);
        } elseif (str_contains($name, 'loan payable') && !str_contains($name, 'total')) {
            $data['loans'] = -abs($val);
        } elseif (str_contains($name, 'total for equity') || str_contains($name, 'total equity') || ($group === 'TotalEquity') || ($group === 'Equity' && str_contains($name, 'total'))) {
            $data['owners_equity'] = $val;
        } elseif (str_contains($name, 'undeposited funds')) {
            $data['undeposited_funds'] = $val;
        } elseif (str_contains($name, 'total current assets') || str_contains($name, 'total for current assets')) {
            $data['current_assets'] = $val;
        } elseif (str_contains($name, 'total current liabilities') || str_contains($name, 'total for current liabilities')) {
            $data['current_liabilities'] = -abs($val);
        }
    }

    protected static function emptyBSRow(string $month): array
    {
        return [
            'month' => $month, 'cash' => 0, 'ar' => 0, 'inventory' => 0,
            'fixed_assets' => 0, 'total_assets' => 0, 'ap' => 0,
            'credit_cards' => 0, 'loans' => 0, 'owners_equity' => 0,
            'undeposited_funds' => 0, 'current_assets' => 0, 'current_liabilities' => 0,
        ];
    }

    protected static function fetchGeneralLedgerData(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate
    ): array {
        // QB GeneralLedger uses debit_amt / credit_amt (not debit_amount / credit_amount).
        // Requesting the wrong names causes QB to silently drop those columns, shifting
        // subsequent indices and returning 0 for all debit/credit values.
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/GeneralLedger"
              . "?start_date={$startDate}&end_date={$endDate}"
              . "&columns=tx_date,txn_type,doc_num,name,memo,account,debit_amt,credit_amt,dept_name"
              . "&sort_order=ascend";
        $resp = self::curlGet($url, $connection->access_token);

        if ($resp['code'] !== 200 || empty($resp['body'])) {
            Log::warning('General Ledger API failed', ['code' => $resp['code']]);
            return self::emptyGLFallback($startDate, $endDate);
        }

        $data = json_decode($resp['body'], true);

        // Build ColType → index map from the response so parsing is column-order-independent.
        $colMap = [];
        foreach (($data['Columns']['Column'] ?? []) as $i => $col) {
            $type = strtolower($col['ColType'] ?? '');
            if ($type !== '') $colMap[$type] = $i;
        }

        $glData = isset($data['Rows']['Row']) ? self::parseGLRows($data['Rows']['Row'], $colMap) : [];

        if (empty($glData)) {
            Log::info('General Ledger: no transactions in range, using empty fallback', ['range' => "{$startDate} to {$endDate}"]);
            return self::emptyGLFallback($startDate, $endDate);
        }

        return $glData;
    }

    protected static function emptyGLFallback(string $startDate, string $endDate): array
    {
        $data = [];
        $cur  = Carbon::parse($startDate)->startOfMonth();
        $end  = Carbon::parse($endDate);

        while ($cur->lte($end)) {
            $data[] = [
                'date'            => $cur->format('Y-m-d'),
                'account'         => '',
                'description'     => '',
                'debit'           => 0,
                'credit'          => 0,
                'vendor_customer' => '',
                'dept'            => '',
            ];
            $cur->addMonth();
        }

        return $data;
    }

    protected static function parseGLRows(array $rows, array $colMap = []): array
    {
        $glData         = [];
        $currentAccount = '';

        // Resolve column positions from the API-provided map; fall back to hardcoded defaults.
        $dateIdx   = $colMap['tx_date']      ?? 0;
        $typeIdx   = $colMap['txn_type']     ?? 1;
        $docIdx    = $colMap['doc_num']      ?? 2;
        $nameIdx   = $colMap['name']         ?? 3;
        $memoIdx   = $colMap['memo']         ?? 4;
        $acctIdx   = $colMap['account_name'] ?? $colMap['account'] ?? 5;
        $debitIdx  = $colMap['debit_amt']    ?? $colMap['debit_amount'] ?? 6;
        $creditIdx = $colMap['credit_amt']   ?? $colMap['credit_amount'] ?? 7;
        $deptIdx   = $colMap['dept_name']    ?? $colMap['dept'] ?? 8;

        foreach ($rows as $row) {
            if (isset($row['Header']['ColData'])) {
                foreach ($row['Header']['ColData'] as $col) {
                    $v = $col['value'] ?? '';
                    if (!empty($v) && !is_numeric($v)) { $currentAccount = $v; break; }
                }
            }

            if (isset($row['Rows']['Row'])) {
                foreach ($row['Rows']['Row'] as $dataRow) {
                    if (!isset($dataRow['ColData'])) continue;
                    $c    = $dataRow['ColData'];
                    $date = $c[$dateIdx]['value'] ?? '';
                    if (empty($date) || strtotime($date) === false) continue;
                    $glData[] = [
                        'date'            => $date,
                        'account'         => $c[$acctIdx]['value'] ?? $currentAccount,
                        'description'     => trim(($c[$typeIdx]['value'] ?? '') . ' ' . ($c[$docIdx]['value'] ?? '') . ' - ' . ($c[$memoIdx]['value'] ?? '')),
                        'debit'           => (float) ($c[$debitIdx]['value'] ?? 0),
                        'credit'          => (float) ($c[$creditIdx]['value'] ?? 0),
                        'vendor_customer' => $c[$nameIdx]['value'] ?? '',
                        'dept'            => $c[$deptIdx]['value'] ?? '',
                    ];
                }
            }

            if (isset($row['ColData']) && !isset($row['Rows'])) {
                $c    = $row['ColData'];
                $date = $c[$dateIdx]['value'] ?? '';
                if (!empty($date) && strtotime($date) !== false) {
                    $glData[] = [
                        'date'            => $date,
                        'account'         => $c[$acctIdx]['value'] ?? $currentAccount,
                        'description'     => trim(($c[$typeIdx]['value'] ?? '') . ' ' . ($c[$memoIdx]['value'] ?? '')),
                        'debit'           => (float) ($c[$debitIdx]['value'] ?? 0),
                        'credit'          => (float) ($c[$creditIdx]['value'] ?? 0),
                        'vendor_customer' => $c[$nameIdx]['value'] ?? '',
                        'dept'            => $c[$deptIdx]['value'] ?? '',
                    ];
                }
            }
        }

        return $glData;
    }

    protected static function fetchCashFlowData(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate
    ): array {
        $connection->refresh();
        // QB omits a month column when end_date falls mid-month — extend to end-of-month.
        $apiEndDate = Carbon::parse($endDate)->endOfMonth()->format('Y-m-d');
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/CashFlow"
              . "?start_date={$startDate}&end_date={$apiEndDate}&summarize_column_by=Month";
        $resp = self::curlGet($url, $connection->access_token);

        if ($resp['code'] !== 200 || empty($resp['body'])) {
            return self::emptyCashFlowFallback($startDate, $endDate);
        }

        $reportData  = json_decode($resp['body'], true);
        $columns     = $reportData['Columns']['Column'] ?? [];
        $monthLabels = [];

        foreach ($columns as $index => $col) {
            $title   = $col['ColTitle'] ?? '';
            $colType = $col['ColType'] ?? '';
            if ($colType === 'Account' || $title === 'Total' || empty($title)) continue;
            $parsed = self::parseMonthLabel($title);
            if ($parsed) $monthLabels[$index] = $parsed;
        }

        $monthlyData = [];
        foreach ($monthLabels as $idx => $label) {
            $monthlyData[$idx] = [
                'month' => $label, 'net_income' => 0, 'operating' => 0,
                'investing' => 0, 'financing' => 0, 'net_change' => 0,
                'beginning_cash' => 0, 'ending_cash' => 0,
            ];
        }

        if (isset($reportData['Rows']['Row'])) {
            self::extractCashFlowValues($reportData['Rows']['Row'], $monthlyData, $monthLabels);
        }

        foreach ($monthlyData as &$row) {
            if ($row['net_change'] == 0) {
                $row['net_change'] = $row['operating'] + $row['investing'] + $row['financing'];
            }
            // Use QB-provided ending cash if returned; otherwise derive from beginning + net change.
            if ($row['ending_cash'] == 0) {
                $row['ending_cash'] = ($row['beginning_cash'] ?? 0) + $row['net_change'];
            }
        }
        unset($row);

        return array_values($monthlyData);
    }

    protected static function extractCashFlowValues(array $rows, array &$monthlyData, array $monthLabels): void
    {
        foreach ($rows as $row) {
            // QB stores section totals (Operating/Investing/Financing) in Summary.ColData, not ColData.
            // Match by Summary label text to extract the correct totals.
            if (isset($row['Summary']['ColData'])) {
                $summaryData  = $row['Summary']['ColData'];
                $summaryLabel = strtolower($summaryData[0]['value'] ?? '');

                foreach ($monthLabels as $idx => $label) {
                    if (!isset($monthlyData[$idx])) continue;
                    $v = str_replace([',', '$', ' '], '', $summaryData[$idx]['value'] ?? '');
                    if (!is_numeric($v)) continue;
                    $val = (float) $v;

                    if (str_contains($summaryLabel, 'net cash provided by operating') || str_contains($summaryLabel, 'net cash used in operating')) {
                        $monthlyData[$idx]['operating'] = $val;
                    } elseif (str_contains($summaryLabel, 'net cash provided by investing') || str_contains($summaryLabel, 'net cash used in investing')) {
                        $monthlyData[$idx]['investing'] = $val;
                    } elseif (str_contains($summaryLabel, 'net cash provided by financing') || str_contains($summaryLabel, 'net cash used in financing')) {
                        $monthlyData[$idx]['financing'] = $val;
                    }
                }
            }

            // Individual line items (Net Income, Net Change) appear in ColData rows.
            // Match by the row label to assign to the correct field.
            if (isset($row['ColData'])) {
                $colData  = $row['ColData'];
                $colLabel = strtolower($colData[0]['value'] ?? '');

                foreach ($monthLabels as $idx => $label) {
                    if (!isset($monthlyData[$idx])) continue;
                    $v = str_replace([',', '$', ' '], '', $colData[$idx]['value'] ?? '');
                    if (!is_numeric($v)) continue;
                    $val = (float) $v;

                    if (str_contains($colLabel, 'net income') && !str_contains($colLabel, 'adjust')) {
                        $monthlyData[$idx]['net_income'] = $val;
                    } elseif (str_contains($colLabel, 'net cash increase') || str_contains($colLabel, 'net cash decrease') || str_contains($colLabel, 'net change in cash')) {
                        $monthlyData[$idx]['net_change'] = $val;
                    } elseif (str_contains($colLabel, 'net cash provided by operating') || str_contains($colLabel, 'net cash used in operating')) {
                        $monthlyData[$idx]['operating'] = $val;
                    } elseif (str_contains($colLabel, 'net cash provided by investing') || str_contains($colLabel, 'net cash used in investing')) {
                        $monthlyData[$idx]['investing'] = $val;
                    } elseif (str_contains($colLabel, 'net cash provided by financing') || str_contains($colLabel, 'net cash used in financing')) {
                        $monthlyData[$idx]['financing'] = $val;
                    } elseif (str_contains($colLabel, 'cash') && str_contains($colLabel, 'beginning')) {
                        $monthlyData[$idx]['beginning_cash'] = $val;
                    } elseif (str_contains($colLabel, 'cash') && (str_contains($colLabel, 'end of') || str_contains($colLabel, 'ending'))) {
                        $monthlyData[$idx]['ending_cash'] = $val;
                    }
                }
            }

            if (isset($row['Rows']['Row'])) {
                self::extractCashFlowValues($row['Rows']['Row'], $monthlyData, $monthLabels);
            }
        }
    }

    protected static function emptyCashFlowFallback(string $startDate, string $endDate): array
    {
        $data  = [];
        $cur   = Carbon::parse($startDate)->startOfMonth();
        $end   = Carbon::parse($endDate);

        while ($cur->lte($end)) {
            $data[] = ['month' => $cur->format('Y-m'), 'net_income' => 0,
                'operating' => 0, 'investing' => 0, 'financing' => 0,
                'net_change' => 0, 'ending_cash' => 0];
            $cur->addMonth();
        }

        return $data;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected static function curlGet(string $url, string $accessToken): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['code' => $code, 'body' => $body];
    }

    protected static function resolveBaseUrl(array $credentials): string
    {
        $env = strtolower(trim($credentials['base_url'] ?? 'development'));
        return $env === 'production'
            ? config('quickbooks.api_production_url')
            : config('quickbooks.api_sandbox_url');
    }

    protected static function parseMonthLabel(string $title): ?string
    {
        $title = trim($title);
        try {
            if (preg_match('/^([A-Za-z]+)\s+(\d{4})$/i', $title, $m)) {
                return Carbon::parse($m[1] . ' 1, ' . $m[2])->format('Y-m');
            }
            if (preg_match('/^([A-Za-z]+)\s+\d+\s*-\s*[A-Za-z]+\s+\d+[,]?\s*(\d{4})$/i', $title, $m)) {
                return Carbon::parse($m[1] . ' 1, ' . $m[2])->format('Y-m');
            }
            if (preg_match('/^([A-Za-z]+)$/i', $title, $m)) {
                return Carbon::parse($m[1] . ' 1')->format('Y-m');
            }
        } catch (Exception $e) {
            Log::debug('parseMonthLabel failed: ' . $title);
        }
        return null;
    }
}
