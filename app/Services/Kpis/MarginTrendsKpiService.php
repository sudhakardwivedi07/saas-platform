<?php

namespace App\Services\Kpis;

use App\Services\KpiService;

class MarginTrendsKpiService
{
    /**
     * Calculate margin trends for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'margin-trends', function () use ($companyId) {
            // TODO: Implement actual data fetching
            $gOff = (crc32((string) $companyId . 'g') % 11) - 5; // -5 .. +5
            $nOff = (crc32((string) $companyId . 'n') % 9) - 4;
            $base = [
                ['month' => '2024-01', 'gross_margin' => 65.5, 'net_margin' => 18.2],
                ['month' => '2024-02', 'gross_margin' => 67.1, 'net_margin' => 19.8],
                ['month' => '2024-03', 'gross_margin' => 66.8, 'net_margin' => 17.9],
                ['month' => '2024-04', 'gross_margin' => 68.2, 'net_margin' => 20.1],
                ['month' => '2024-05', 'gross_margin' => 67.9, 'net_margin' => 18.7],
                ['month' => '2024-06', 'gross_margin' => 69.1, 'net_margin' => 21.3],
            ];
            $monthlyData = array_map(static function (array $row) use ($gOff, $nOff) {
                $row['gross_margin'] = round(max(0, min(95, $row['gross_margin'] + $gOff * 0.15)), 1);
                $row['net_margin'] = round(max(0, min(40, $row['net_margin'] + $nOff * 0.12)), 1);

                return $row;
            }, $base);

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

        return [
            'monthly_data' => $data['monthly_data'],
        ];
    }
}