<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\User;

/**
 * The preset date formats offered in the SuperAdmin and Admin UI.
 * Key = PHP date() format string, Value = human-readable label shown in the picker.
 */
const DATE_FORMAT_PRESETS = [
    'M j, Y'  => 'Jun 2, 2026',
    'd M Y'   => '02 Jun 2026',
    'm/d/Y'   => '06/02/2026',
    'd/m/Y'   => '02/06/2026',
    'd.m.Y'   => '02.06.2026',
    'Y-m-d'   => '2026-06-02',
    'F j, Y'  => 'June 2, 2026',
    'j F Y'   => '2 June 2026',
];

if (!function_exists('company')) {
    /**
     * Get the current tenant company.
     * Returns NULL for SuperAdmin context.
     */
    function company(): ?Company
    {
        // First try authenticated user's company
        if (auth()->hasUser() && auth()->user()->company_id) {
            return cache()->remember(
                'company_' . auth()->user()->company_id,
                now()->addMinutes(5),
                fn () => Company::find(auth()->user()->company_id)
            );
        }

        // Then try middleware-resolved company (for guest pages like login)
        if (app()->has('currentCompany')) {
            return app('currentCompany');
        }

        return null;
    }
}

if (!function_exists('user')) {
    /**
     * Get the authenticated user.
     */
    function user(): ?User
    {
        return auth()->user();
    }
}

if (!function_exists('is_superadmin')) {
    /**
     * Check if the current user is a SuperAdmin.
     */
    function is_superadmin(): bool
    {
        return auth()->hasUser() && (bool) auth()->user()->is_superadmin;
    }
}

if (!function_exists('global_setting')) {
    /**
     * Get global (SuperAdmin-level) settings singleton.
     */
    function global_setting(): ?GlobalSetting
    {
        return cache()->remember('global_setting', now()->addHour(), fn () => GlobalSetting::first());
    }
}

if (!function_exists('company_or_global')) {
    /**
     * Return company settings if in tenant context, else global settings.
     */
    function company_or_global(): Company|GlobalSetting|null
    {
        return company() ?? global_setting();
    }
}

if (!function_exists('abort_403')) {
    /**
     * Abort with 403 if condition is truthy.
     * Pattern from reference: abort_403(!$permission)
     */
    function abort_403(bool $condition): void
    {
        if ($condition) {
            abort(403, 'This action is unauthorized.');
        }
    }
}

if (!function_exists('tenant_url')) {
    /**
     * Build a URL for a given tenant by subdomain.
     */
    function tenant_url(string $subdomain, string $path = ''): string
    {
        $rootDomain = config('tenancy.root_domain', env('ROOT_DOMAIN', 'yourdomain.com'));
        $base = 'https://' . $subdomain . '.' . $rootDomain;
        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }
}

if (!function_exists('active_date_format')) {
    /**
     * Resolve the effective date format for the current request.
     *
     * Resolution chain (first non-empty value wins):
     *   1. Admin company's own override  (companies.date_format, set via My Account → Branding)
     *   2. Parent company override       (for sub-company users whose admin set a format)
     *   3. SuperAdmin global default     (global_settings.date_format)
     *   4. Hard-coded fallback           ('M j, Y')
     *
     * The result is request-scoped via the service container so the DB/cache
     * is only queried once per request regardless of how many dates are formatted.
     */
    function active_date_format(): string
    {
        if (app()->bound('_active_date_format')) {
            return app('_active_date_format');
        }

        $format = _resolve_active_date_format();
        app()->instance('_active_date_format', $format);
        return $format;
    }
}

if (!function_exists('_resolve_active_date_format')) {
    /** @internal Called once per request by active_date_format(). */
    function _resolve_active_date_format(): string
    {
        $fallback = global_setting()?->date_format ?: 'M j, Y';

        $company = company();
        if (!$company) {
            // SuperAdmin context — use global setting directly.
            return $fallback;
        }

        // Does the current company have an explicit override?
        if ($company->date_format) {
            return $company->date_format;
        }

        // For sub-company users, check the parent admin company's override.
        if ($company->created_by) {
            $parentFormat = cache()->remember(
                'company_date_format_' . $company->created_by,
                now()->addMinutes(10),
                fn () => Company::withoutGlobalScopes()->find($company->created_by)?->date_format
            );
            if ($parentFormat) {
                return $parentFormat;
            }
        }

        return $fallback;
    }
}

if (!function_exists('format_bytes')) {
    /**
     * Format bytes into human-readable size.
     */
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
