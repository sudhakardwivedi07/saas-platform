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
            $dataset = QuickBooksKpiDataService::dataset($companyId) ?? QuickBooksKpiDataService::emptyDatasetShape();
            $pl = array_values($dataset['profit_loss_monthly'] ?? []);
            if (empty($pl)) {
                return [
                    'ebitda' => null,
                    'operating_income' => null,
                    'depreciation' => 0,
                    'amortization' => 0,
                    'revenue' => null,
                    'margin' => null,
                    'monthly_bars_raw' => [],
                    'last_updated' => now(),
                ];
            }
            $pl = self::activeProfitLossRows($pl);

            $recent = array_slice($pl, -12);
            $monthlyBars = [];
            $revenueLtm = 0.0;
            foreach ($recent as $row) {
                $rev = (float) ($row['revenue'] ?? 0);
                $cogs = (float) ($row['cogs'] ?? 0);
                $opex = (float) ($row['misc'] ?? 0);
                $revenueLtm += $rev;
                $monthlyBars[] = $rev - $cogs - $opex;
            }
            $ebitda = array_sum($monthlyBars);
            $latest = end($recent) ?: [];
            $revenue = (float) ($latest['revenue'] ?? 0);
            $ebitdaMargin = abs($revenueLtm) > 0.00001 ? ($ebitda / $revenueLtm) * 100 : null;

            return [
                'ebitda' => $ebitda,
                'operating_income' => (float) ($latest['operating_income'] ?? 0),
                'depreciation' => 0,
                'amortization' => 0,
                'revenue' => $revenue,
                'margin' => $ebitdaMargin,
                'monthly_bars_raw' => $monthlyBars,
                'last_updated' => now(),
            ];
        });
    }

    private static function activeProfitLossRows(array $rows): array
    {
        $active = array_values(array_filter($rows, static function (array $row) {
            foreach (['revenue', 'cogs', 'gross_profit', 'operating_income', 'net_income'] as $key) {
                if (abs((float) ($row[$key] ?? 0)) > 0.00001) {
                    return true;
                }
            }

            return false;
        }));

        return $active ?: $rows;
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