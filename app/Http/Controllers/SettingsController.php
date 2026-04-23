<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\EmailSmtpSetting;
use App\Models\SmsSetting;
use App\Models\CompanyPaymentGateway;
use App\Models\ClientPackage;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SettingsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Settings';
    }

    public function index()
    {
        abort_403(!auth()->user()->can('view_settings'));

        $companyId = auth()->user()->company_id;

        $this->smtpSetting    = EmailSmtpSetting::where('company_id', $companyId)->first();
        $this->smsSetting     = SmsSetting::where('company_id', $companyId)->first();
        $this->paymentGateway = CompanyPaymentGateway::where('company_id', $companyId)->first();
        $this->qbSetting      = CompanyQuickbooksSetting::where('company_id', $companyId)->first();
        $this->clientPackages = ClientPackage::forCompany($companyId)->orderBy('sort')->get();
        $this->branding       = company()?->branding;

        // Roles & Permissions — count only users within this client_admin's company hierarchy
        $subCompanyIds = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->pluck('id')
            ->push($companyId);

        $this->roles = Role::with('permissions')
            ->withCount(['users as users_count' => fn ($q) => $q->whereIn('company_id', $subCompanyIds)])
            ->get();
        $this->allPermissions = Permission::all()->groupBy(function ($p) {
            return match (true) {
                str_contains($p->name, '_users')            => 'Users',
                str_contains($p->name, '_documents') ||
                    str_contains($p->name, 'upload_documents') ||
                    str_contains($p->name, 'download_documents') => 'Documents',
                str_contains($p->name, '_quickbooks') ||
                    str_contains($p->name, 'connect_quickbooks') => 'QuickBooks',
                str_contains($p->name, '_email_templates') ||
                    str_contains($p->name, 'add_email_templates') => 'Email Templates',
                str_contains($p->name, '_settings') ||
                    str_contains($p->name, 'edit_branding')       => 'Settings',
                str_contains($p->name, '_billing') ||
                    str_contains($p->name, 'manage_payment_gateway') => 'Billing',
                str_contains($p->name, '_reports')              => 'Reports',
                str_contains($p->name, '_company') ||
                    str_contains($p->name, 'add_company') ||
                    str_contains($p->name, '_plan') ||
                    str_contains($p->name, 'add_plan')           => 'Companies & Plans',
                default                                          => 'Other',
            };
        });

        return view('settings.index', $this->data);
    }

    public function updateQuickbooksSettings(Request $request)
    {
        abort_403(!auth()->user()->can('edit_settings'));

        $request->validate([
            'client_id'     => 'required|string|max:255',
            'client_secret' => 'nullable|string|max:500',
            'redirect_uri'  => 'required|string|max:500',
            'base_url'      => 'required|in:development,production',
        ]);

        $setting = CompanyQuickbooksSetting::firstOrNew(['company_id' => company()->id]);

        if (!$setting->exists && empty($request->client_secret)) {
            return response()->json(Reply::error('Client Secret is required.'), 422);
        }

        $setting->client_id    = $request->client_id;
        $setting->redirect_uri = $request->redirect_uri;
        $setting->base_url     = $request->base_url;
        $setting->company_id   = company()->id;

        if ($request->filled('client_secret')) {
            $setting->client_secret = $request->client_secret;
        }

        $setting->save();

        return Reply::success('QuickBooks settings saved.');
    }
}
