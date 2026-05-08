<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

/**
 * AccountBaseController — base for all authenticated controllers.
 *
 * Runs middleware that loads company context, global settings, locale,
 * and shares them with all views — same pattern as reference project.
 */
class AccountBaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            if (!user() && !auth()->check()) {
                return redirect()->route('login');
            }

            $this->loadCommonData();

            return $next($request);
        });
    }

    private function loadCommonData(): void
    {
        $company       = company();
        $globalSetting = global_setting();

        // White-label: use company settings if in tenant context
        $this->company       = $company;
        $this->globalSetting = $globalSetting;
        $this->appName       = $company?->app_name ?? $globalSetting?->global_app_name ?? config('app.name');
        $this->companyName   = $company?->company_name ?? $globalSetting?->global_app_name ?? config('app.name');
        // Always load branding fresh (don't trust the cached Company's relation)
        $branding = $company
            ? \App\Models\CompanyBranding::where('company_id', $company->id)->first()
            : null;

        // Client users belong to sub-companies (created_by = admin company_id).
        // withoutGlobalScopes() is required: TenantScope silently appends
        // "AND company_id = {current_company_id}" to every CompanyBranding query,
        // which would make a cross-company lookup always return null.
        if ($company?->created_by) {
            $parentBranding = \App\Models\CompanyBranding::withoutGlobalScopes()
                ->where('company_id', $company->created_by)
                ->first();

            if ($parentBranding) {
                $branding = $parentBranding;

                // Inherit the parent's app name so client users see the portal's branded name,
                // not the global platform name.
                $parentCompany = \App\Models\Company::withoutGlobalScopes()
                    ->find($company->created_by);
                if ($parentCompany) {
                    $this->appName   = $parentCompany->app_name ?? $parentCompany->company_name ?? $this->appName;
                    $this->companyName = $parentCompany->company_name ?? $this->companyName;
                }
            }
        }

        $this->branding = $branding;

        // Locale
        $locale = $company?->locale ?? $globalSetting?->locale ?? 'en';
        App::setLocale($locale);
        Carbon::setLocale($locale);

        // Share with all views
        view()->share([
            'authUser'      => user(),
            'appName'       => $this->appName,
            'companyName'   => $this->companyName,
            'tenantCompany' => $company,
            'branding'      => $this->branding,
            'isSuperAdmin'  => is_superadmin(),
            'globalSetting' => $globalSetting,
        ]);
    }
}
