<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class ArAgingKpiService
{
    /**
     * Calculate AR Aging for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'ar-aging', function () use ($companyId) {
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $rows = $dataset['ar_rows'] ?? [];
            $pl = $dataset['profit_loss_monthly'] ?? [];
            if (empty($rows)) {
                return [
                    'aging_buckets' => [],
                    'total_ar' => null,
                    'dso' => null,
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
            $totalAr = (float) array_sum(array_column($agingBuckets, 'amount'));
            foreach ($agingBuckets as $i => $row) {
                $amt = (float) $agingBuckets[$i]['amount'];
                $agingBuckets[$i]['percentage'] = $totalAr > 0
                    ? round($amt / $totalAr * 100, 1)
                    : 0.0;
            }
            $revenueT12 = 0.0;
            foreach (array_slice(array_values($pl), -12) as $m) {
                $revenueT12 += (float) ($m['revenue'] ?? 0);
            }
            $avgDailySales = $revenueT12 / 365;
            $dso = $avgDailySales > 0 ? ($totalAr / $avgDailySales) : null;

            return [
                'aging_buckets' => $agingBuckets,
                'total_ar' => $totalAr,
                'dso' => $dso,
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
        $dso = $data['dso'] ?? null;
        $maxShare = 0.0;
        foreach ($data['aging_buckets'] as $b) {
            $maxShare = max($maxShare, (float) ($b['percentage'] ?? 0));
        }
        if ($maxShare <= 0) {
            $maxShare = 1.0;
        }

        return [
            'total_ar' => KpiService::formatCurrency($data['total_ar']),
            'dso' => $dso !== null ? (string) (int) round((float) $dso) : null,
            'dso_badge' => $dso !== null
                ? 'DSO: ' . (int) round((float) $dso) . ' Days'
                : 'DSO: N/A',
            'aging_buckets' => array_map(function ($bucket) use ($maxShare) {
                $pct = (float) ($bucket['percentage'] ?? 0);
                $barScale = $maxShare > 0 ? round(($pct / $maxShare) * 100, 1) : 0.0;

                return [
                    'label' => $bucket['label'] ?? ($bucket['range'] ?? ''),
                    'amount' => KpiService::formatCurrency($bucket['amount'] ?? null),
                    'percentage' => KpiService::formatPercentage($bucket['percentage'] ?? null),
                    'share_pct' => $pct,
                    'bar_scale_pct' => $barScale,
                ];
            }, $data['aging_buckets']),
        ];
    }
}