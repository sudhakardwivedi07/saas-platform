<?php

namespace App\Services\Kpis;

/**
 * Resolves formatted KPI payloads for a company (used by client admin and end-user dashboards).
 */
final class KpiFormattedDataService
{
    public static function get(int $companyId, string $kpiType): array
    {
        return match ($kpiType) {
            'runway-burn' => RunwayBurnKpiService::getFormattedData($companyId),
            'working-capital' => WorkingCapitalKpiService::getFormattedData($companyId),
            'operating-cashflow' => OperatingCashFlowKpiService::getFormattedData($companyId),
            'revenue' => RevenueKpiService::getFormattedData($companyId),
            'ebitda' => EbitdaKpiService::getFormattedData($companyId),
            'margin-trends' => MarginTrendsKpiService::getFormattedData($companyId),
            'ar-aging' => ArAgingKpiService::getFormattedData($companyId),
            'ap-leverage' => ApLeverageKpiService::getFormattedData($companyId),
            'revenue-concentration' => RevenueConcentrationKpiService::getFormattedData($companyId),
            default => throw new \InvalidArgumentException("Unknown KPI type: {$kpiType}"),
        };
    }
}
