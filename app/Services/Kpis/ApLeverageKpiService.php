<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class ApLeverageKpiService
{
    /**
     * Calculate AP Leverage for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'ap-leverage', function () use ($companyId) {
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $rows = $dataset['ap_rows'] ?? [];
            $pl = $dataset['profit_loss_monthly'] ?? [];
            $bs = $dataset['balance_sheet_monthly'] ?? [];
            if (empty($rows)) {
                return [
                    'total_ap' => null,
                    'dpo' => null,
                    'aging_buckets' => [],
                    'monthly_trend' => [],
                    'last_updated' => now(),
                ];
            }
            $agingBuckets = [
                ['label' => 'Current', 'amount' => (float) array_sum(array_column($rows, 'current'))],
                ['label' => '1-30', 'amount' => (float) array_sum(array_column($rows, '1_30'))],
                ['label' => '31-60', 'amount' => (float) array_sum(array_column($rows, '31_60'))],
                ['label' => '61-90', 'amount' => (float) array_sum(array_column($rows, '61_90'))],
                ['label' => '90+', 'amount' => (float) array_sum(array_column($rows, 'over_90'))],
            ];
            $totalAp = (float) array_sum(array_column($agingBuckets, 'amount'));
            foreach ($agingBuckets as $i => $row) {
                $amt = (float) $agingBuckets[$i]['amount'];
                $agingBuckets[$i]['percentage'] = $totalAp > 0
                    ? round($amt / $totalAp * 100, 1)
                    : 0.0;
            }
            $cogsT12 = 0.0;
            foreach (array_slice(array_values($pl), -12) as $m) {
                $cogsT12 += (float) ($m['cogs'] ?? 0);
            }
            $avgDailyCogs = $cogsT12 / 365;
            $dpo = $avgDailyCogs > 0 ? ($totalAp / $avgDailyCogs) : null;

            $monthlyTrend = array_map(static function (array $row) {
                return [
                    'month' => $row['month'] ?? '',
                    'amount' => abs((float) ($row['ap'] ?? 0)),
                ];
            }, array_slice(array_values($bs), -12));

            return [
                'total_ap' => $totalAp,
                'dpo' => $dpo,
                'aging_buckets' => $agingBuckets,
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
        $dpo = $data['dpo'] ?? null;
        $maxShare = 0.0;
        foreach ($data['aging_buckets'] ?? [] as $b) {
            $maxShare = max($maxShare, (float) ($b['percentage'] ?? 0));
        }
        if ($maxShare <= 0) {
            $maxShare = 1.0;
        }

        return [
            'total_ap' => KpiService::formatCurrency($data['total_ap']),
            'dpo' => $dpo !== null ? (string) (int) round((float) $dpo) : null,
            'dpo_badge' => $dpo !== null
                ? 'DPO: ' . (int) round((float) $dpo) . ' Days'
                : 'DPO: N/A',
            'aging_buckets' => array_map(function ($bucket) use ($maxShare) {
                $pct = (float) ($bucket['percentage'] ?? 0);
                $barScale = $maxShare > 0 ? round(($pct / $maxShare) * 100, 1) : 0.0;

                return [
                    'label' => $bucket['label'] ?? '',
                    'amount' => KpiService::formatCurrency($bucket['amount'] ?? null),
                    'percentage' => KpiService::formatPercentage($bucket['percentage'] ?? null),
                    'share_pct' => $pct,
                    'bar_scale_pct' => $barScale,
                ];
            }, $data['aging_buckets'] ?? []),
            'monthly_trend' => array_map(function ($item) {
                return [
                    'month' => $item['month'],
                    'amount' => KpiService::formatCurrency($item['amount']),
                ];
            }, $data['monthly_trend']),
        ];
    }
}