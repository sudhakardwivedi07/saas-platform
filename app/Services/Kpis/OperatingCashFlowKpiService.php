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
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $cf = array_values($dataset['cash_flow_monthly'] ?? []);
            $monthlyData = array_map(static function (array $row) {
                return [
                    'month' => (string) ($row['month'] ?? ''),
                    'amount' => (float) ($row['operating'] ?? 0),
                ];
            }, $cf);

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