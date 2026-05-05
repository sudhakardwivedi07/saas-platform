<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class KpiService
{
    /**
     * Normalize numeric values from various formats
     */
    public static function normalizeNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convert to string for processing
        $value = (string) $value;

        // Remove currency symbols and commas
        $value = preg_replace('/[\$,]/', '', $value);

        // Handle parentheses for negative numbers (123) -> -123
        if (preg_match('/^\((.+)\)$/', $value, $matches)) {
            $value = '-' . $matches[1];
        }

        // Remove any remaining non-numeric characters except decimal point and minus
        $value = preg_replace('/[^0-9\.\-]/', '', $value);

        // Convert to float
        $numeric = (float) $value;

        return is_nan($numeric) ? null : $numeric;
    }

    /**
     * Format currency value
     */
    public static function formatCurrency($value, $decimals = 0): string
    {
        if ($value === null) {
            return '$—';
        }

        $normalized = self::normalizeNumber($value);
        if ($normalized === null) {
            return '$—';
        }

        return '$' . number_format($normalized, $decimals);
    }

    /**
     * Format percentage value
     */
    public static function formatPercentage($value, $decimals = 1): string
    {
        if ($value === null) {
            return '—%';
        }

        $normalized = self::normalizeNumber($value);
        if ($normalized === null) {
            return '—%';
        }

        return number_format($normalized, $decimals) . '%';
    }

    /**
     * Deterministic factor in ~[0.88, 1.12] from company id (and optional salt).
     * Used so placeholder KPI payloads differ per company until real data is wired.
     */
    public static function companyDemoFactor(int $companyId, string $salt = ''): float
    {
        $h = crc32((string) $companyId . "\0" . $salt);
        $u = $h & 0xffff;

        return 0.88 + ($u / 65535) * 0.24;
    }

    /**
     * Get cached KPI data for a company
     */
    public static function getCachedKpiData(int $companyId, string $kpiType, callable $callback)
    {
        $cacheKey = "kpi_{$companyId}_{$kpiType}";

        return Cache::remember($cacheKey, 3600, $callback); // Cache for 1 hour
    }

    /**
     * Clear KPI cache for a company
     */
    public static function clearKpiCache(int $companyId, ?string $kpiType = null): void
    {
        if ($kpiType) {
            Cache::forget("kpi_{$companyId}_{$kpiType}");
        } else {
            // Clear all KPI cache for the company
            Cache::forget("kpi_{$companyId}_runway-burn");
            Cache::forget("kpi_{$companyId}_working-capital");
            Cache::forget("kpi_{$companyId}_operating-cashflow");
            Cache::forget("kpi_{$companyId}_revenue");
            Cache::forget("kpi_{$companyId}_ebitda");
            Cache::forget("kpi_{$companyId}_margin-trends");
            Cache::forget("kpi_{$companyId}_ar-aging");
            Cache::forget("kpi_{$companyId}_ap-leverage");
            Cache::forget("kpi_{$companyId}_revenue-concentration");
        }
    }
}