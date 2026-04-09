<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

/**
 * IdentifyTenant — resolves which Company this request belongs to.
 *
 * Resolution order:
 *  1. Custom domain match (highest priority)
 *  2. Subdomain match against ROOT_DOMAIN
 *
 * Sets app('currentCompany') for use by the company() helper.
 */
class IdentifyTenant
{
    public function handle(Request $request, Closure $next): mixed
    {
        $company = $this->resolveCompany($request);

        if ($company) {
            app()->instance('currentCompany', $company);

            // Inject branding CSS variables into view data
            if ($branding = $company->branding) {
                view()->share('tenantBranding', $branding);
            }

            view()->share('tenantCompany', $company);
        }

        return $next($request);
    }

    private function resolveCompany(Request $request): ?Company
    {
        $host = $request->getHost();

        // 1. Custom domain
        $company = Company::where('custom_domain', $host)
            ->where('status', 'active')
            ->first();

        if ($company) return $company;

        // 2. Subdomain
        $rootDomain = config('tenancy.root_domain', env('ROOT_DOMAIN', 'yourdomain.com'));

        if (str_ends_with($host, '.' . $rootDomain)) {
            $subdomain = str_replace('.' . $rootDomain, '', $host);
            return Company::where('subdomain', $subdomain)
                ->where('status', 'active')
                ->first();
        }

        return null;
    }
}
