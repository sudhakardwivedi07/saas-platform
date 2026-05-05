<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class RevenueConcentrationKpiService
{
    /**
     * Calculate Revenue Concentration for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'revenue-concentration', function () use ($companyId) {
            // TODO: Implement actual data fetching
            $f = KpiService::companyDemoFactor($companyId, 'rc');
            $customers = [
                ['name' => 'Client Alpha', 'revenue' => (int) round(252500 * $f), 'percentage' => 0.0],
                ['name' => 'Client Beta', 'revenue' => (int) round(156000 * $f), 'percentage' => 0.0],
                ['name' => 'Client Gamma', 'revenue' => (int) round(45000 * $f), 'percentage' => 0.0],
                ['name' => 'Client Delta', 'revenue' => (int) round(28000 * $f), 'percentage' => 0.0],
                ['name' => 'Client Epsilon', 'revenue' => (int) round(18500 * $f), 'percentage' => 0.0],
                ['name' => 'Others', 'revenue' => (int) round(195000 * $f), 'percentage' => 0.0],
            ];

            $totalRevenue = (float) array_sum(array_column($customers, 'revenue'));
            foreach ($customers as $i => $c) {
                $customers[$i]['percentage'] = $totalRevenue > 0
                    ? round(((float) $c['revenue'] / $totalRevenue) * 100, 1)
                    : 0.0;
            }
            $top5Percentage = round(
                array_sum(array_column(array_slice($customers, 0, 5), 'percentage')),
                1
            );

            return [
                'customers' => $customers,
                'total_revenue' => $totalRevenue,
                'top5_percentage' => $top5Percentage,
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
        $maxPct = 0.0;
        foreach ($data['customers'] as $c) {
            $maxPct = max($maxPct, (float) ($c['percentage'] ?? 0));
        }
        if ($maxPct <= 0) {
            $maxPct = 1.0;
        }

        return [
            'subtitle' => 'Top 5 Clients % of Total ARR',
            'top5_percentage' => KpiService::formatPercentage($data['top5_percentage']),
            'top5_share_pct' => (float) $data['top5_percentage'],
            'customers' => array_map(function ($customer) use ($maxPct) {
                $pct = (float) ($customer['percentage'] ?? 0);
                $barScale = $maxPct > 0 ? round(($pct / $maxPct) * 100, 1) : 0.0;

                return [
                    'name' => $customer['name'],
                    'revenue' => KpiService::formatCurrency($customer['revenue'] ?? null),
                    'percentage' => KpiService::formatPercentage($customer['percentage'] ?? null),
                    'share_pct' => $pct,
                    'bar_scale_pct' => $barScale,
                ];
            }, $data['customers']),
        ];
    }
}