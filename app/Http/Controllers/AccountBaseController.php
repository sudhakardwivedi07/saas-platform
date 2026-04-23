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

        // If this company has no custom colors (user-role sub-company), fall back
        // to the parent firm's branding. created_by stores the parent company_id
        // when a client_admin creates sub-companies via ClientController.
        if (!($branding?->primary_color) && $company?->created_by) {
            $parentBranding = cache()->remember(
                'branding_parent_' . $company->created_by,
                now()->addMinutes(1),
                fn () => \App\Models\CompanyBranding::where('company_id', $company->created_by)->first()
            );
            if ($parentBranding?->primary_color) {
                $branding = $parentBranding;
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
