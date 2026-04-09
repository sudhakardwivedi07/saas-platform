<?php

use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\User;

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
