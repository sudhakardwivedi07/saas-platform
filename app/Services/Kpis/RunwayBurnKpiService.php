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
            $cf = array_values($dataset['cash_flow_monthly'] ?? []);

            if (empty($bs) || empty($cf)) {
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

            $monthlySeries = array_map(static function (array $m) {
                $operating = (float) ($m['operating'] ?? 0);
                $netIncome = (float) ($m['net_income'] ?? 0);
                $net = (float) ($m['net_change'] ?? 0);
                if (abs($net) < 0.00001) {
                    $net = abs($operating) > 0.00001 ? $operating : $netIncome;
                }

                return [
                    'month' => $m['month'] ?? '',
                    'net_change' => $net,
                    'operating' => (float) ($m['operating'] ?? 0),
                    'investing' => (float) ($m['investing'] ?? 0),
                    'financing' => (float) ($m['financing'] ?? 0),
                ];
            }, $cf);
            $activeMonthlySeries = array_values(array_filter($monthlySeries, static function (array $m) {
                return abs((float) ($m['net_change'] ?? 0)) > 0.00001
                    || abs((float) ($m['operating'] ?? 0)) > 0.00001;
            }));
            if (! empty($activeMonthlySeries)) {
                $monthlySeries = $activeMonthlySeries;
            }

            $last12 = array_slice($monthlySeries, -12);
            $burnValues = array_values(array_filter(
                array_map(static fn(array $x) => (float) $x['net_change'], $last12),
                static fn(float $value) => $value < -0.00001
            ));

            $avg = ! empty($burnValues) ? array_sum($burnValues) / count($burnValues) : null;

            $runwayMonths = null;
            if ($avg !== null) {
                $runwayMonths = $cashBalance / abs($avg);
            }

            $status = self::determineRunwayStatus($runwayMonths);
            $burnType = $avg === null ? null : ($avg < 0 ? 'burn' : ($avg > 0 ? 'build' : 'stable'));

            return [
                'cash_balance' => $cashBalance,
                'monthly_burn' => $avg,
                'runway_months' => $runwayMonths,
                'status' => $status,
                'burn_type' => $burnType,
                'runway_percent' => $runwayMonths !== null ? min(100, max(0, ($runwayMonths / 24) * 100)) : 0,
                'monthly_data' => $last12,
                'last_updated' => now(),
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

        $avg = $data['monthly_burn'];
        $avgFormatted = '—';
        if ($avg !== null) {
            $avgFormatted = ($avg > 0 ? '+' : ($avg < 0 ? '-' : '')) . KpiService::formatCurrency(abs($avg)) . '/mo';
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