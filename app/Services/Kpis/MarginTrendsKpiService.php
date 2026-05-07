<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class MarginTrendsKpiService
{
    /**
     * Calculate margin trends for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'margin-trends', function () use ($companyId) {
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $pl = array_values($dataset['profit_loss_monthly'] ?? []);
            $pl = self::completedPeriodRows($pl);
            $summaryRows = self::revenueRows($pl);
            $summaryRevenue = array_sum(array_map(static fn (array $row) => (float) ($row['revenue'] ?? 0), $summaryRows));
            $summaryCogs = array_sum(array_map(static fn (array $row) => (float) ($row['cogs'] ?? 0), $summaryRows));
            $summaryNetIncome = array_sum(array_map(static fn (array $row) => (float) ($row['net_income'] ?? 0), $summaryRows));
            $recent = array_slice($pl, -12);
            $monthlyData = array_map(static function (array $row) {
                $rev = (float) ($row['revenue'] ?? 0);
                $cogs = (float) ($row['cogs'] ?? 0);
                $ni = (float) ($row['net_income'] ?? 0);
                return [
                    'month' => $row['month'] ?? '',
                    'gross_margin' => $rev > 0 ? round((($rev - $cogs) / $rev) * 100, 1) : null,
                    'net_margin' => $rev > 0 ? round(($ni / $rev) * 100, 1) : null,
                ];
            }, $recent);

            return [
                'monthly_data' => $monthlyData,
                'gross_margin_ltm' => $summaryRevenue > 0 ? round((($summaryRevenue - $summaryCogs) / $summaryRevenue) * 100, 1) : null,
                'net_margin_ltm' => $summaryRevenue > 0 ? round(($summaryNetIncome / $summaryRevenue) * 100, 1) : null,
                'last_updated' => now(),
            ];
        });
    }

    private static function completedPeriodRows(array $rows): array
    {
        $active = array_values(array_filter($rows, static function (array $row) {
            $month = (string) ($row['month'] ?? '');

            return ! preg_match('/^\d{4}-\d{2}$/', $month) || $month < now()->format('Y-m');
        }));

        return $active ?: $rows;
    }

    private static function revenueRows(array $rows): array
    {
        return array_values(array_filter($rows, static function (array $row) {
            return abs((float) ($row['revenue'] ?? 0)) > 0.00001;
        }));
    }

    /**
     * Get formatted data for frontend
     */
    public static function getFormattedData(int $companyId): array
    {
        $data = self::calculate($companyId);

        return [
            'monthly_data' => $data['monthly_data'],
            'gross_margin_ltm' => $data['gross_margin_ltm'],
            'net_margin_ltm' => $data['net_margin_ltm'],
        ];
    }
}