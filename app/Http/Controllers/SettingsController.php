<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\CompanyInsightSetting;
use App\Models\CompanyQuickbooksSetting;
use App\Models\EmailSmtpSetting;
use App\Models\EmailTemplate;
use App\Models\SmsSetting;
use App\Models\CompanyPaymentGateway;
use App\Models\ClientPackage;
use App\Models\EventLog;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
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
        $this->branding             = company()?->branding;
        $this->firmEmailTemplates   = EmailTemplate::where('company_id', company()->id)
                                                    ->orderBy('name')
                                                    ->get();
        $this->insightSetting       = CompanyInsightSetting::where('company_id', $companyId)->first();
        $this->companiesWithUsage   = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['users' => fn($q) => $q->orderBy('id')->limit(1)])
            ->orderBy('company_name')
            ->get();

        $subIds = $this->companiesWithUsage->pluck('id')->toArray();
        $this->clientsForLimits = User::whereIn('company_id', $subIds)
            ->with([
                'company',
                'managedCompanies' => fn($q) => $q
                    ->withoutGlobalScopes()
                    ->whereIn('companies.id', $subIds),
            ])
            ->orderBy('name')
            ->get();

        // Admin's total insight budget from superadmin plan
        $adminCompany = Company::withoutGlobalScopes()->find($companyId);
        $adminPlan    = $adminCompany?->activePlan();
        $this->adminInsightBudget = $adminPlan
            ? (int) ($adminPlan->max_insights_per_month ?? $adminPlan->insights_per_month ?? 0)
            : 0;
        // Total already allocated across all client companies with a custom limit set
        $this->totalAllocated = (int) $this->companiesWithUsage->sum('custom_insight_limit');

        // Roles & Permissions — count only users within this admin's company hierarchy
        $subCompanyIds = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->pluck('id')
            ->push($companyId);

        $this->roles = Role::with('permissions')
            ->withCount(['users as users_count' => fn ($q) => $q->whereIn('company_id', $subCompanyIds)])
            ->get();
        $this->allPermissions = Permission::all()->groupBy(function ($p) {
            return match (true) {
                // Tab-visibility permissions — checked first to avoid partial matches below
                str_starts_with($p->name, 'view_settings_')   => 'Settings Tabs',
                str_starts_with($p->name, 'view_account_')    => 'My Account Tabs',
                str_contains($p->name, '_users')              => 'Users',
                str_contains($p->name, '_documents') ||
                    str_contains($p->name, 'upload_documents') ||
                    str_contains($p->name, 'download_documents') => 'Documents',
                str_contains($p->name, '_quickbooks') ||
                    str_contains($p->name, 'connect_quickbooks')  => 'Connections',
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

        // ── Account tab data (merged from AccountController) ──────────────────
        $this->authUser = auth()->user();

        $this->activities = EventLog::query()
            ->where('company_id', company()?->id)
            ->with('user:id,name,email')
            ->latest('created_at')
            ->get();

        $this->eventTypes = EventLog::query()
            ->where('company_id', company()?->id)
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        $this->subscription = company()?->subscription;
        $this->packages     = Package::active()->orderBy('sort')->get();

        $this->payments = \App\Models\Payment::where('company_id', $companyId)
            ->with('subscription.package')
            ->orderByDesc('paid_at')
            ->get();

        $this->syncPaymentMethodIfMissing($this->subscription);

        $subCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['clientPackage', 'package', 'quickbooksConnections',
                    'users' => fn($q) => $q->orderBy('id')->limit(1)])
            ->get();
        $this->subCompanies = $subCompanies;

        $maxInsights = $this->subscription?->package?->max_documents ?? 0;
        $this->totalInsightsUsed      = $subCompanies->sum('insights_used');
        $this->totalInsightsRemaining = $maxInsights > 0
            ? max(0, $maxInsights - $this->totalInsightsUsed)
            : 0;

        $quotaFull = $subCompanies->filter(fn($c) => $c->clientPackage && $c->clientPackage->max_documents > 0 && $c->insights_used >= $c->clientPackage->max_documents)->count();
        $inactive  = $subCompanies->where('status', 'inactive')->count();
        $noQB      = $subCompanies->filter(fn($c) => $c->quickbooksConnections->where('is_active', true)->isEmpty())->count();

        $this->companiesAtRisk = $subCompanies->filter(fn($c) =>
            $c->status === 'inactive' ||
            $c->quickbooksConnections->where('is_active', true)->isEmpty() ||
            ($c->clientPackage && $c->clientPackage->max_documents > 0 && $c->insights_used >= $c->clientPackage->max_documents)
        )->count();
        $this->atRiskContext = collect([
            $quotaFull ? $quotaFull . ' quota full' : null,
            $inactive  ? $inactive  . ' inactive'   : null,
            $noQB      ? $noQB      . ' no QB'       : null,
        ])->filter()->implode(' · ');
        $this->uniqueClients = $subCompanies->map(fn($c) => $c->users->first()?->id)->filter()->unique()->count();

        return view('settings.index', $this->data);
    }

    private function syncPaymentMethodIfMissing(?Subscription $sub): void
    {
        if (!$sub || $sub->pm_last4 || !$sub->stripe_id) {
            return;
        }
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeSub = \Stripe\Subscription::retrieve($sub->stripe_id);
            $pmId      = $stripeSub->default_payment_method ?? null;
            if (!$pmId) return;
            $pm = \Stripe\PaymentMethod::retrieve($pmId);
            $sub->update([
                'pm_last4'     => $pm->card?->last4,
                'pm_exp_month' => $pm->card?->exp_month,
                'pm_exp_year'  => $pm->card?->exp_year,
                'pm_brand'     => $pm->card?->brand,
            ]);
        } catch (\Throwable $e) {
            // silent — don't break the page if Stripe is unavailable
        }
    }

    public function updateLoginPage(Request $request)
    {
        abort_403(!auth()->user()->can('edit_branding'));

        $request->validate([
            'login_heading'    => 'nullable|string|max:255',
            'login_subheading' => 'nullable|string|max:255',
            'login_bg_type'    => 'nullable|in:gradient,solid,image',
            'login_bg_color'   => ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'login_bg_color_to'=> ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'login_card_bg'    => ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'login_btn_color'  => ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'logo'             => 'nullable|image|max:2048',
            'login_background' => 'nullable|image|max:4096',
        ]);

        $branding = CompanyBranding::firstOrNew(['company_id' => company()->id]);

        foreach (['logo', 'login_background'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store(
                    'companies/' . company()->id . '/branding',
                    'public'
                );
                $branding->{$field} = $path;
            }
        }

        $branding->fill($request->only(
            'login_heading', 'login_subheading',
            'login_bg_type', 'login_bg_color', 'login_bg_color_to',
            'login_card_bg', 'login_btn_color'
        ));
        $branding->save();

        cache()->forget('branding_parent_' . company()->id);

        return Reply::success('Login page settings saved.');
    }

    public function updateForgotPasswordPage(Request $request)
    {
        abort_403(!auth()->user()->can('edit_branding'));
        $request->validate([
            'fp_heading'    => 'nullable|string|max:255',
            'fp_subheading' => 'nullable|string|max:255',
        ]);
        $branding = CompanyBranding::firstOrNew(['company_id' => company()->id]);
        $branding->fill($request->only('fp_heading', 'fp_subheading'));
        $branding->save();
        cache()->forget('branding_parent_' . company()->id);
        return Reply::success('Forgot password page settings saved.');
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
