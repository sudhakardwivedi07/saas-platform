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
            // TODO: Implement actual data fetching
            $f = KpiService::companyDemoFactor($companyId, 'rev');
            $currentMonthRevenue = (int) round(285000 * $f);
            $previousMonthRevenue = (int) round(265000 * $f);
            $lastYearSameMonth = (int) round(245000 * $f);

            $momGrowth = $previousMonthRevenue != 0 ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : 0;
            $yoyGrowth = $lastYearSameMonth != 0 ? (($currentMonthRevenue - $lastYearSameMonth) / $lastYearSameMonth) * 100 : 0;

            $trendBase = [
                ['month' => '2023-01', 'current_year' => 220000, 'last_year' => 200000],
                ['month' => '2023-02', 'current_year' => 235000, 'last_year' => 210000],
                ['month' => '2023-03', 'current_year' => 250000, 'last_year' => 225000],
                ['month' => '2023-04', 'current_year' => 265000, 'last_year' => 240000],
                ['month' => '2023-05', 'current_year' => 280000, 'last_year' => 255000],
                ['month' => '2023-06', 'current_year' => 285000, 'last_year' => 245000],
            ];
            $monthlyTrend = array_map(static function (array $row) use ($f) {
                $row['current_year'] = (int) round($row['current_year'] * $f);
                $row['last_year'] = (int) round($row['last_year'] * $f);

                return $row;
            }, $trendBase);

            return [
                'current_month_revenue' => $currentMonthRevenue,
                'mom_growth' => $momGrowth,
                'yoy_growth' => $yoyGrowth,
                'monthly_trend' => $monthlyTrend,
                'last_updated' => now(),
            ];
        });
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
            'yoy_class' => $data['yoy_growth'] >= 0 ? 'positive' : 'negative',
            'monthly_trend' => $data['monthly_trend'],
        ];
    }
}