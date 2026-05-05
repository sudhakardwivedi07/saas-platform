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
            // TODO: Replace with QuickBooks / GL AP aging when wired.
            $f = KpiService::companyDemoFactor($companyId, 'ap_amt');
            $raw = [
                ['label' => 'Current', 'amount' => 400.0],
                ['label' => '1-30', 'amount' => 350.0],
                ['label' => '31-60', 'amount' => 280.0],
                ['label' => '61-90', 'amount' => 220.0],
                ['label' => '90+', 'amount' => 353.0],
            ];
            $agingBuckets = [];
            foreach ($raw as $row) {
                $agingBuckets[] = [
                    'label' => $row['label'],
                    'amount' => round((float) $row['amount'] * $f, 2),
                ];
            }
            $totalAp = (float) array_sum(array_column($agingBuckets, 'amount'));
            foreach ($agingBuckets as $i => $row) {
                $amt = (float) $agingBuckets[$i]['amount'];
                $agingBuckets[$i]['percentage'] = $totalAp > 0
                    ? round($amt / $totalAp * 100, 1)
                    : 0.0;
            }
            $dpo = null; // Unknown until vendor terms / purchases are modeled

            $ft = KpiService::companyDemoFactor($companyId, 'ap_tr');
            $trendRaw = [
                ['month' => '2024-01', 'amount' => 1520],
                ['month' => '2024-02', 'amount' => 1580],
                ['month' => '2024-03', 'amount' => 1603],
                ['month' => '2024-04', 'amount' => 1590],
                ['month' => '2024-05', 'amount' => 1610],
                ['month' => '2024-06', 'amount' => 1603],
            ];
            $monthlyTrend = array_map(static function (array $row) use ($ft) {
                $row['amount'] = (int) round((float) $row['amount'] * $ft);

                return $row;
            }, $trendRaw);

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