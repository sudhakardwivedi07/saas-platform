<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class OperatingCashFlowKpiService
{
    /**
     * Calculate operating cash flow for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'operating-cashflow', function () use ($companyId) {
            // TODO: Implement actual data fetching
            $f = KpiService::companyDemoFactor($companyId, 'ocf');
            $raw = [
                ['month' => '2024-01', 'amount' => -2200],
                ['month' => '2024-02', 'amount' => 12000],
                ['month' => '2024-03', 'amount' => -800],
                ['month' => '2024-04', 'amount' => 18000],
                ['month' => '2024-05', 'amount' => 9000],
                ['month' => '2024-06', 'amount' => -1500],
                ['month' => '2024-07', 'amount' => 24000],
                ['month' => '2024-08', 'amount' => 31000],
                ['month' => '2024-09', 'amount' => 5000],
                ['month' => '2024-10', 'amount' => 78000],
                ['month' => '2024-11', 'amount' => 42000],
                ['month' => '2024-12', 'amount' => 55000],
            ];
            $monthlyData = array_map(static function (array $row) use ($f) {
                $row['amount'] = (int) round((float) $row['amount'] * $f);

                return $row;
            }, $raw);

            return [
                'monthly_data' => $monthlyData,
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

        $formattedData = array_map(function ($item) {
            return [
                'month' => $item['month'],
                'amount' => KpiService::formatCurrency($item['amount']),
                'raw_amount' => $item['amount'],
            ];
        }, $data['monthly_data']);

        return [
            'monthly_data' => $formattedData,
        ];
    }
}