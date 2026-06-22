<?php

namespace App\Services\Kpis;

use App\Services\KpiService;
use Illuminate\Support\Collection;

class RunwayBurnKpiService
{
    /**
     * Calculate runway and burn metrics for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'runway-burn', function () use ($companyId) {
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $bs = array_values($dataset['balance_sheet_monthly'] ?? []);
            $pl = array_values($dataset['profit_loss_monthly'] ?? []);

            if (empty($bs) || empty($pl)) {
                return [
                    'cash_balance' => null,
                    'monthly_burn' => null,
                    'runway_months' => null,
                    'status' => self::determineRunwayStatus(null),
                    'burn_type' => null,
                    'runway_percent' => 0,
                    'monthly_data' => [],
                    'last_updated' => now(),
                ];
            }

            $latestBs = end($bs) ?: [];
            $cashBalance = (float) ($latestBs['cash'] ?? 0);

            // Enforce exactly 12 months from P&L regardless of how many rows the CSV contains.
            // Using P&L (not cash flow statement) avoids unreliable operating-CF months where
            // QB returns $0 and the average collapses, inflating runway to absurd levels.
            $recent12 = array_slice($pl, -12);
            $months = max(1, count($recent12));

            $totalCogs        = array_sum(array_map(static fn($r) => (float) ($r['cogs']         ?? 0), $recent12));
            $totalGrossProfit = array_sum(array_map(static fn($r) => (float) ($r['gross_profit'] ?? 0), $recent12));
            $netIncomeTotal   = array_sum(array_map(static fn($r) => (float) ($r['net_income']   ?? 0), $recent12));

            // total_opex = gross_profit − net_income captures ALL operating expenses, not only
            // named buckets (payroll/marketing/rent/software/misc) which miss implicit costs.
            $totalOpex = $totalGrossProfit - $netIncomeTotal;

            // gross_burn_rate = (COGS + all opex) / 12
            $grossBurnRate = ($totalCogs + max(0.0, $totalOpex)) / $months;

            // net_burn = |net_income| / 12 when losing; 0 when profitable
            $netBurnRate = $netIncomeTotal >= 0 ? 0.0 : abs($netIncomeTotal) / $months;

            // Runway uses the rate that matches actual cash depletion:
            // - Losing money → net_burn (how fast NI erodes cash)
            // - Profitable   → gross_burn (conservative coverage baseline)
            $burnRateForRunway = $netIncomeTotal < 0 ? $netBurnRate : $grossBurnRate;
            $runwayMonths = $burnRateForRunway > 0.00001 ? $cashBalance / $burnRateForRunway : null;

            $status   = self::determineRunwayStatus($runwayMonths);
            $burnType = $netIncomeTotal >= 0 ? 'build' : 'burn';

            $monthlyData = array_map(static fn($r) => [
                'month'      => $r['month'] ?? '',
                'net_change' => (float) ($r['net_income'] ?? 0),
                'operating'  => (float) ($r['net_income'] ?? 0),
                'investing'  => 0.0,
                'financing'  => 0.0,
            ], $recent12);

            return [
                'cash_balance'    => $cashBalance,
                'monthly_burn'    => $burnRateForRunway, // rate used for runway; net when losing, gross when profitable
                'gross_burn_rate' => $grossBurnRate,
                'net_burn_rate'   => $netBurnRate,
                'runway_months'   => $runwayMonths,
                'status'          => $status,
                'burn_type'       => $burnType,
                'runway_percent'  => $runwayMonths !== null ? min(100, max(0, ($runwayMonths / 24) * 100)) : 0,
                'monthly_data'    => $monthlyData,
                'last_updated'    => now(),
            ];
        });
    }

    /**
     * Determine runway status based on months
     */
    private static function determineRunwayStatus(?float $months): array
    {
        if ($months === null) {
            return [
                'level' => 'unknown',
                'text' => 'Unknown',
                'icon' => 'fa-question-circle',
                'class' => 'warning-zone'
            ];
        }

        if ($months >= 12) {
            return [
                'level' => 'safe',
                'text' => 'Safe Zone',
                'icon' => 'fa-check-circle',
                'class' => 'safe-zone'
            ];
        } elseif ($months >= 6) {
            return [
                'level' => 'warning',
                'text' => 'Warning Zone',
                'icon' => 'fa-exclamation-triangle',
                'class' => 'warning-zone'
            ];
        } else {
            return [
                'level' => 'critical',
                'text' => 'Critical Zone',
                'icon' => 'fa-exclamation-circle',
                'class' => 'critical-zone'
            ];
        }
    }

    /**
     * Get formatted data for frontend
     */
    public static function getFormattedData(int $companyId): array
    {
        $data = self::calculate($companyId);

        $avg = $data['monthly_burn']; // gross_burn_rate (always >= 0)
        $avgFormatted = '—';
        if ($avg !== null) {
            $avgFormatted = KpiService::formatCurrency(abs($avg)) . '/mo';
        }

        $runwayDisplay = '—';
        if ($data['runway_months'] !== null) {
            $runwayDisplay = ($data['runway_months'] > 0 && $data['runway_months'] < 1)
                ? '< 1'
                : number_format((float) $data['runway_months'], 1);
        }

        return [
            'runway_months' => $runwayDisplay,
            'cash_balance' => KpiService::formatCurrency($data['cash_balance']),
            'monthly_burn' => $avgFormatted,
            'burn_class' => $avg !== null ? ($avg < 0 ? 'negative' : 'positive') : null,
            'status' => $data['status'],
            'runway_percent' => (float) ($data['runway_percent'] ?? 0),
            'burn_type' => $data['burn_type'],
            'monthly_data' => array_map(static function (array $m) {
                return [
                    'month' => $m['month'] ?? '',
                    'amount' => KpiService::formatCurrency($m['net_change'] ?? 0),
                    'raw_amount' => (float) ($m['net_change'] ?? 0),
                ];
            }, $data['monthly_data'] ?? []),
        ];
    }

}