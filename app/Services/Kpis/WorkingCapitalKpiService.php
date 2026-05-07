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
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $bs = array_values($dataset['balance_sheet_monthly'] ?? []);
            if (empty($bs)) {
                return [
                    'current_assets' => null,
                    'current_liabilities' => null,
                    'working_capital' => null,
                    'mom_change' => null,
                    'mom_percent' => null,
                    'last_updated' => now(),
                ];
            }

            $latest = end($bs) ?: [];
            $prev = count($bs) > 1 ? $bs[count($bs) - 2] : null;
            $currentAssets = (float) ($latest['current_assets'] ?? 0);
            $currentLiabilities = abs((float) ($latest['current_liabilities'] ?? 0));
            $workingCapital = $currentAssets - $currentLiabilities;
            $momChange = null;
            $momPercent = null;
            if ($prev) {
                $prevWc = (float) ($prev['current_assets'] ?? 0) - abs((float) ($prev['current_liabilities'] ?? 0));
                $momChange = $workingCapital - $prevWc;
                if (abs($prevWc) > 0.00001) {
                    $momPercent = ($momChange / abs($prevWc)) * 100;
                }
            }

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
        $den = max($ca, $cl, 0);

        return [
            'working_capital' => KpiService::formatCurrency($data['working_capital']),
            'current_assets' => KpiService::formatCurrency($data['current_assets']),
            'current_liabilities' => KpiService::formatCurrency($data['current_liabilities']),
            'mom_change' => KpiService::formatCurrency($data['mom_change']),
            'mom_percent' => KpiService::formatPercentage($data['mom_percent']),
            'mom_class' => $data['mom_change'] === null ? null : ($data['mom_change'] >= 0 ? 'positive' : 'negative'),
            'assets_share_pct' => $den > 0 ? round(($ca / $den) * 100, 1) : 0,
            'liabilities_share_pct' => $den > 0 ? round(($cl / $den) * 100, 1) : 0,
        ];
    }
}