<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class EbitdaKpiService
{
    /**
     * Calculate EBITDA for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'ebitda', function () use ($companyId) {
            // TODO: Implement actual data fetching
            $fo = KpiService::companyDemoFactor($companyId, 'ebit_oi');
            $fd = KpiService::companyDemoFactor($companyId, 'ebit_dep');
            $fa = KpiService::companyDemoFactor($companyId, 'ebit_am');
            $fr = KpiService::companyDemoFactor($companyId, 'ebit_rev');

            $operatingIncome = (int) round(125000 * $fo);
            $depreciation = (int) round(15000 * $fd);
            $amortization = (int) round(8000 * $fa);
            $ebitda = $operatingIncome + $depreciation + $amortization;

            $revenue = (int) round(500000 * $fr);
            $ebitdaMargin = $revenue != 0 ? ($ebitda / $revenue) * 100 : 0;

            $monthlyBars = [];
            for ($i = 0; $i < 12; $i++) {
                $wave = 0.82 + (($i + $companyId) % 5) * 0.06;
                $monthlyBars[] = (int) max(0, round(($ebitda / 12) * $wave));
            }

            return [
                'ebitda' => $ebitda,
                'operating_income' => $operatingIncome,
                'depreciation' => $depreciation,
                'amortization' => $amortization,
                'revenue' => $revenue,
                'margin' => $ebitdaMargin,
                'monthly_bars_raw' => $monthlyBars,
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
            'ebitda' => KpiService::formatCurrency($data['ebitda']),
            'margin' => KpiService::formatPercentage($data['margin']),
            'monthly_bars_raw' => $data['monthly_bars_raw'] ?? [],
        ];
    }
}