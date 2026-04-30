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
            $files['ar_aging']       = self::generateARAgingCSV($connection, $baseUrl, $basePath, $timestamp);
            $files['ap_aging']       = self::generateAPAgingCSV($connection, $baseUrl, $basePath, $timestamp);
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
        QuickBooksConnection $connection, string $baseUrl, string $basePath, string $timestamp
    ): array {
        $filename = 'AR_Aging.csv';
        $filepath = "{$basePath}/{$filename}";

        $arData = self::fetchARAgingData($connection, $baseUrl);

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
        QuickBooksConnection $connection, string $baseUrl, string $basePath, string $timestamp
    ): array {
        $filename = 'AP_Aging.csv';
        $filepath = "{$basePath}/{$filename}";

        $apData = self::fetchAPAgingData($connection, $baseUrl);

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

    protected static function fetchARAgingData(QuickBooksConnection $connection, string $baseUrl): array
    {
        $url      = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/AgedReceivables?report_date=" . date('Y-m-d');
        $response = self::curlGet($url, $connection->access_token);

        if ($response['code'] !== 200 || empty($response['body'])) {
            Log::warning('AR Aging API failed', ['code' => $response['code']]);
            return [];
        }

        $data = json_decode($response['body'], true);
        return isset($data['Rows']['Row']) ? self::parseAgingRows($data['Rows']['Row'], 'customer') : [];
    }

    protected static function fetchAPAgingData(QuickBooksConnection $connection, string $baseUrl): array
    {
        $url      = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/AgedPayables?report_date=" . date('Y-m-d');
        $response = self::curlGet($url, $connection->access_token);

        if ($response['code'] !== 200 || empty($response['body'])) {
            Log::warning('AP Aging API failed', ['code' => $response['code']]);
            return [];
        }

        $data = json_decode($response['body'], true);
        return isset($data['Rows']['Row']) ? self::parseAgingRows($data['Rows']['Row'], 'vendor') : [];
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

        $parseVal = fn($v) => is_numeric(str_replace(',', '', $v ?? '')) ? (float) str_replace(',', '', $v) : 0;

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

            match ($group) {
                'Income'      => $data['revenue']          = abs($val),
                'COGS'        => $data['cogs']             = abs($val),
                'GrossProfit' => $data['gross_profit']     = $val,
                'Expenses'    => $data['misc']             = abs($val),
                'NetIncome'   => $data['net_income']       = $val,
                default       => null,
            };

            // Drill into expense sub-accounts for payroll, marketing, rent, software
            if (isset($row['Rows']['Row'])) {
                foreach ($row['Rows']['Row'] as $sub) {
                    $name    = strtolower($sub['ColData'][0]['value'] ?? $sub['Header']['ColData'][0]['value'] ?? '');
                    $subVal  = 0;
                    if (isset($sub['ColData'][1]['value'])) {
                        $subVal = abs($parseVal($sub['ColData'][1]['value']));
                    } elseif (isset($sub['Summary']['ColData'])) {
                        foreach ($sub['Summary']['ColData'] as $sc) {
                            $sv = str_replace(',', '', $sc['value'] ?? '');
                            if (is_numeric($sv)) { $subVal = abs((float) $sv); break; }
                        }
                    }

                    if (str_contains($name, 'payroll') || str_contains($name, 'salary') || str_contains($name, 'wages')) {
                        $data['payroll'] += $subVal;
                    } elseif (str_contains($name, 'market') || str_contains($name, 'advertis')) {
                        $data['marketing'] += $subVal;
                    } elseif (str_contains($name, 'rent') || str_contains($name, 'lease')) {
                        $data['rent'] += $subVal;
                    } elseif (str_contains($name, 'software') || str_contains($name, 'subscription') || str_contains($name, 'saas')) {
                        $data['software'] += $subVal;
                    }
                }
            }
        }

        $data['operating_income'] = $data['gross_profit'] - $data['misc'];

        return $data;
    }

    protected static function fetchMonthlyBalanceSheetData(
        QuickBooksConnection $connection, string $baseUrl, string $startDate, string $endDate
    ): array {
        // Try multi-month report first (summarize_column_by=Month)
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/BalanceSheet"
              . "?start_date={$startDate}&end_date={$endDate}&summarize_column_by=Month&accounting_method=Accrual";
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

        return array_values($monthlyData);
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
            // Summary value
            if (isset($row['Summary']['ColData'])) {
                foreach ($row['Summary']['ColData'] as $c) {
                    $v = str_replace(',', '', $c['value'] ?? '');
                    if (is_numeric($v)) { $val = (float) $v; break; }
                }
            }
            // Data row value
            if ($group !== 'TotalAssets' && isset($row['ColData'])) {
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
        } elseif ((str_contains($name, 'checking') || str_contains($name, 'savings') || str_contains($name, 'cash on hand') || str_contains($name, 'petty cash') || preg_match('/^cash$/i', trim($name))) && !str_contains($name, 'undeposited') && !str_contains($name, 'flow')) {
            if (!str_contains($name, 'total')) $data['cash'] += $val;
        } elseif (str_contains($name, 'total for accounts receivable')) {
            $data['ar'] = $val;
        } elseif ((str_contains($name, 'accounts receivable') || str_contains($name, 'a/r')) && !str_contains($name, 'total') && $data['ar'] == 0) {
            $data['ar'] += $val;
        } elseif (str_contains($name, 'inventory')) {
            if (str_contains($name, 'total')) $data['inventory'] = $val;
            elseif ($data['inventory'] == 0) $data['inventory'] = $val;
        } elseif (str_contains($name, 'total for fixed assets')) {
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
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/GeneralLedger"
              . "?start_date={$startDate}&end_date={$endDate}"
              . "&columns=tx_date,txn_type,doc_num,name,memo,account,debit_amount,credit_amount,dept_name"
              . "&sort_order=ascend";
        $resp = self::curlGet($url, $connection->access_token);

        if ($resp['code'] !== 200 || empty($resp['body'])) {
            Log::warning('General Ledger API failed', ['code' => $resp['code']]);
            return [];
        }

        $data = json_decode($resp['body'], true);
        return isset($data['Rows']['Row']) ? self::parseGLRows($data['Rows']['Row']) : [];
    }

    protected static function parseGLRows(array $rows): array
    {
        $glData         = [];
        $currentAccount = '';

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
                    $c       = $dataRow['ColData'];
                    $date    = $c[0]['value'] ?? '';
                    if (empty($date)) continue;
                    $glData[] = [
                        'date'            => $date,
                        'account'         => $c[5]['value'] ?? $currentAccount,
                        'description'     => trim(($c[1]['value'] ?? '') . ' ' . ($c[2]['value'] ?? '') . ' - ' . ($c[4]['value'] ?? '')),
                        'debit'           => (float) ($c[6]['value'] ?? 0),
                        'credit'          => (float) ($c[7]['value'] ?? 0),
                        'vendor_customer' => $c[3]['value'] ?? '',
                        'dept'            => $c[8]['value'] ?? '',
                    ];
                }
            }

            if (isset($row['ColData']) && !isset($row['Rows'])) {
                $c    = $row['ColData'];
                $date = $c[0]['value'] ?? '';
                if (!empty($date) && strtotime($date) !== false) {
                    $glData[] = [
                        'date'            => $date,
                        'account'         => $c[5]['value'] ?? $currentAccount,
                        'description'     => trim(($c[1]['value'] ?? '') . ' ' . ($c[4]['value'] ?? '')),
                        'debit'           => (float) ($c[6]['value'] ?? 0),
                        'credit'          => (float) ($c[7]['value'] ?? 0),
                        'vendor_customer' => $c[3]['value'] ?? '',
                        'dept'            => $c[8]['value'] ?? '',
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
        $url  = "{$baseUrl}/v3/company/{$connection->realm_id}/reports/CashFlow"
              . "?start_date={$startDate}&end_date={$endDate}&summarize_column_by=Month";
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
            $monthlyData[$idx] = ['month' => $label, 'net_income' => 0, 'operating' => 0,
                'investing' => 0, 'financing' => 0, 'net_change' => 0, 'ending_cash' => 0];
        }

        if (isset($reportData['Rows']['Row'])) {
            self::extractCashFlowValues($reportData['Rows']['Row'], $monthlyData, $monthLabels);
        }

        $prevCash = 0;
        foreach ($monthlyData as &$row) {
            if ($row['net_change'] == 0) {
                $row['net_change'] = $row['operating'] + $row['investing'] + $row['financing'];
            }
            $row['ending_cash'] = $prevCash + $row['net_change'];
            $prevCash           = $row['ending_cash'];
        }

        return array_values($monthlyData);
    }

    protected static function extractCashFlowValues(array $rows, array &$monthlyData, array $monthLabels): void
    {
        foreach ($rows as $row) {
            $group   = $row['group'] ?? '';
            $colData = $row['ColData'] ?? [];

            if (!empty($colData)) {
                foreach ($monthLabels as $idx => $label) {
                    if (!isset($monthlyData[$idx])) continue;
                    $v = str_replace(',', '', $colData[$idx]['value'] ?? '');
                    if (!is_numeric($v)) continue;
                    $val = (float) $v;

                    match ($group) {
                        'OperatingActivities' => $monthlyData[$idx]['operating']   += $val,
                        'InvestingActivities' => $monthlyData[$idx]['investing']   += $val,
                        'FinancingActivities' => $monthlyData[$idx]['financing']   += $val,
                        'NetIncome'           => $monthlyData[$idx]['net_income']  += $val,
                        'NetChange'           => $monthlyData[$idx]['net_change']   = $val,
                        default               => null,
                    };
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
            ? 'https://quickbooks.api.intuit.com'
            : 'https://sandbox-quickbooks.api.intuit.com';
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
