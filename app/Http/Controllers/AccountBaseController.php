<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
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
        $this->branding      = $company?->branding;

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
