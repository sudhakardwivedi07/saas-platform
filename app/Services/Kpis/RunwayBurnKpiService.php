<?php

namespace App\Services\Kpis;

use App\Services\KpiService;
use Illuminate\Support\Collection;

class RunwayBurnKpiService
{
    /**
     * Calculate runway and burn metrics for a company
     */
    public static function calculate(int $companyId): array
    {
        return KpiService::getCachedKpiData($companyId, 'runway-burn', function () use ($companyId) {
            // TODO: Implement actual data fetching from QuickBooks or financial data
            $fc = KpiService::companyDemoFactor($companyId, 'rb_cash');
            $fb = KpiService::companyDemoFactor($companyId, 'rb_burn');
            $cashBalance = (int) round(500000 * $fc);
            $monthlyBurn = max(5000, (int) round(45000 * $fb));

            // Calculate runway
            $runwayMonths = $monthlyBurn > 0 ? $cashBalance / $monthlyBurn : null;

            // Determine status
            $status = self::determineRunwayStatus($runwayMonths);

            return [
                'cash_balance' => $cashBalance,
                'monthly_burn' => $monthlyBurn,
                'runway_months' => $runwayMonths,
                'status' => $status,
                'burn_type' => $monthlyBurn > 0 ? 'burn' : 'build',
                'last_updated' => now(),
            ];
        });
    }

    /**
     * Determine runway status based on months
     */
    private static function determineRunwayStatus(?float $months): array
    {
        if ($months === null) {
            return [
                'level' => 'unknown',
                'text' => 'Unknown',
                'icon' => 'fa-question-circle',
                'class' => 'warning-zone'
            ];
        }

        if ($months >= 12) {
            return [
                'level' => 'safe',
                'text' => 'Safe Zone',
                'icon' => 'fa-check-circle',
                'class' => 'safe-zone'
            ];
        } elseif ($months >= 6) {
            return [
                'level' => 'warning',
                'text' => 'Warning Zone',
                'icon' => 'fa-exclamation-triangle',
                'class' => 'warning-zone'
            ];
        } else {
            return [
                'level' => 'critical',
                'text' => 'Critical Zone',
                'icon' => 'fa-exclamation-circle',
                'class' => 'critical-zone'
            ];
        }
    }

    /**
     * Get formatted data for frontend
     */
    public static function getFormattedData(int $companyId): array
    {
        $data = self::calculate($companyId);

        return [
            'runway_months' => $data['runway_months'] ? number_format($data['runway_months'], 1) : '—',
            'cash_balance' => KpiService::formatCurrency($data['cash_balance']),
            'monthly_burn' => KpiService::formatCurrency(abs($data['monthly_burn'])) . '/mo',
            'burn_class' => $data['monthly_burn'] >= 0 ? 'negative' : 'positive',
            'status' => $data['status'],
            'runway_percent' => min(100, max(0, ($data['runway_months'] ?? 0) / 24 * 100)),
        ];
    }
}