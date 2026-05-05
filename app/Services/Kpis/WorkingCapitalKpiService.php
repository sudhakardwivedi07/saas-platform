<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class WorkingCapitalKpiService
{
    /**
     * Calculate working capital for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'working-capital', function () use ($companyId) {
            // TODO: Implement actual data fetching
            $fa = KpiService::companyDemoFactor($companyId, 'wc_ca');
            $fb = KpiService::companyDemoFactor($companyId, 'wc_cl');
            $currentAssets = (int) round(750000 * $fa);
            $currentLiabilities = (int) round(450000 * $fb);
            $workingCapital = $currentAssets - $currentLiabilities;

            $fp = KpiService::companyDemoFactor($companyId, 'wc_prev');
            $previousWorkingCapital = (int) round(680000 * $fp);
            $momChange = $workingCapital - $previousWorkingCapital;
            $momPercent = $previousWorkingCapital != 0 ? ($momChange / $previousWorkingCapital) * 100 : 0;

            return [
                'current_assets' => $currentAssets,
                'current_liabilities' => $currentLiabilities,
                'working_capital' => $workingCapital,
                'mom_change' => $momChange,
                'mom_percent' => $momPercent,
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

        $ca = (float) $data['current_assets'];
        $cl = (float) $data['current_liabilities'];
        $sum = $ca + $cl;

        return [
            'working_capital' => KpiService::formatCurrency($data['working_capital']),
            'current_assets' => KpiService::formatCurrency($data['current_assets']),
            'current_liabilities' => KpiService::formatCurrency($data['current_liabilities']),
            'mom_change' => KpiService::formatCurrency($data['mom_change']),
            'mom_percent' => KpiService::formatPercentage($data['mom_percent']),
            'mom_class' => $data['mom_change'] >= 0 ? 'positive' : 'negative',
            'assets_share_pct' => $sum > 0 ? min(100, max(8, round($ca / $sum * 100, 1))) : 50,
            'liabilities_share_pct' => $sum > 0 ? min(100, max(8, round($cl / $sum * 100, 1))) : 50,
        ];
    }
}