<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class RevenueKpiService
{
    /**
     * Calculate revenue metrics for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'revenue', function () use ($companyId) {
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $pl = array_values($dataset['profit_loss_monthly'] ?? []);
            if (empty($pl)) {
                return [
                    'current_month_revenue' => null,
                    'mom_growth' => null,
                    'yoy_growth' => null,
                    'monthly_trend' => [],
                    'last_updated' => now(),
                ];
            }
            $pl = self::completedPeriodRows($pl);

            $n = count($pl);
            $latest = $pl[$n - 1];
            $prev = $n > 1 ? $pl[$n - 2] : null;
            $ly = $n > 12 ? $pl[$n - 13] : null;

            $currentMonthRevenue = max(0, (float) ($latest['revenue'] ?? 0));
            $previousMonthRevenue = $prev ? max(0, (float) ($prev['revenue'] ?? 0)) : 0;
            $lastYearSameMonth = $ly ? max(0, (float) ($ly['revenue'] ?? 0)) : 0;

            $momGrowth = $previousMonthRevenue > 0 ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : null;
            $yoyGrowth = $lastYearSameMonth > 0 ? (($currentMonthRevenue - $lastYearSameMonth) / $lastYearSameMonth) * 100 : null;

            $recent = array_slice($pl, -12);
            $monthlyTrend = [];
            foreach ($recent as $i => $row) {
                $monthlyTrend[] = [
                    'month' => $row['month'] ?? '',
                    'current_year' => max(0, (float) ($row['revenue'] ?? 0)),
                    'last_year' => ($n - 12 + $i - 12) >= 0 ? max(0, (float) ($pl[$n - 12 + $i - 12]['revenue'] ?? 0)) : 0,
                ];
            }

            return [
                'current_month_revenue' => $currentMonthRevenue,
                'mom_growth' => $momGrowth,
                'yoy_growth' => $yoyGrowth,
                'monthly_trend' => $monthlyTrend,
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

    /**
     * Get formatted data for frontend
     */
    public static function getFormattedData(int $companyId): array
    {
        $data = self::calculate($companyId);

        return [
            'current_month_revenue' => KpiService::formatCurrency($data['current_month_revenue']),
            'yoy_growth' => KpiService::formatPercentage($data['yoy_growth']),
            'yoy_class' => $data['yoy_growth'] === null ? null : ($data['yoy_growth'] >= 0 ? 'positive' : 'negative'),
            'monthly_trend' => $data['monthly_trend'],
        ];
    }
}