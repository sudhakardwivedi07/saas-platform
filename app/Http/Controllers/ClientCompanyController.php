<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\GlobalSetting;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ClientCompanyController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth']);
        $this->pageTitle = 'My Companies';
    }

    // ── List companies managed by this admin ─────────────────────────────────

    public function index()
    {
        abort_unless(auth()->user()->can('manage_company'), 403, 'You do not have permission to manage companies.');

        $authUser = auth()->user();

        // Companies the user created (via created_by) OR linked via user_companies
        $ownedCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $authUser->id)
            ->with(['package', 'clientPackage', 'subscription'])
            ->get();

        $linkedCompanies = $authUser->managedCompanies()
            ->withoutGlobalScopes()
            ->with(['package', 'clientPackage', 'subscription'])
            ->get()
            ->filter(fn ($c) => !$ownedCompanies->contains('id', $c->id));

        $companies = $ownedCompanies->merge($linkedCompanies);

        // Also include their primary company if not already listed
        $primaryCompany = $authUser->company;
        if ($primaryCompany && !$companies->contains('id', $primaryCompany->id)) {
            $companies->prepend($primaryCompany->load(['package', 'clientPackage', 'subscription']));
        }

        $this->companies = $companies;

        return view('companies.index', $this->data);
    }

    // ── Show create form ─────────────────────────────────────────────────────

    public function create()
    {
        abort_unless(auth()->user()->can('add_company'), 403, 'You do not have permission to add companies.');

        $companyId = auth()->user()->company_id;

        // Load only THIS client_admin's own plans (not SuperAdmin packages)
        $this->packages = ClientPackage::forCompany($companyId)
            ->active()
            ->orderBy('sort')
            ->get();

        return view('companies.create', $this->data);
    }

    // ── Store new company ────────────────────────────────────────────────────

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('add_company'), 403);

        $companyId = auth()->user()->company_id;

        $request->validate([
            'company_name'    => 'required|string|max:255',
            'subdomain'       => 'required|string|max:63|alpha_dash|unique:companies,subdomain',
            'company_email'   => 'required|email|max:255',
            'admin_name'      => 'required|string|max:255',
            'admin_email'     => 'required|email|max:255|unique:users,email',
            'admin_password'  => 'required|string|min:8|confirmed',
            'package_id'      => 'nullable|exists:client_packages,id',
            'industry_type'   => 'nullable|string|max:100',
            'entity_type'     => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:100',
        ]);

        $setting  = global_setting();
        $authUser = auth()->user();

        // Resolve the chosen client plan (scoped to this admin's company)
        $package = $request->package_id
            ? ClientPackage::forCompany($companyId)->find($request->package_id)
            : ClientPackage::forCompany($companyId)->active()->orderBy('sort')->first();
 
        // Determine if Stripe payment is required
        $stripeReady     = $this->stripeIsConfigured();
        $requiresPayment = $package
            && !$package->is_free
            && $package->monthly_price > 0
            && $stripeReady;

        Log::info('ClientAdmin company creation', [
            'created_by'       => $authUser->id,
            'client_plan'      => $package?->name,
            'stripe_ready'     => $stripeReady,
            'requires_payment' => $requiresPayment,
        ]);

        // Create the new company (inactive until payment confirmed)
        $company = Company::create([
            'created_by'        => $authUser->id,
            'company_name'      => $request->company_name,
            'app_name'          => $request->company_name,
            'company_email'     => $request->company_email,
            'subdomain'         => Str::slug($request->subdomain),
            'status'            => $requiresPayment ? 'inactive' : 'active',
            'package_id'        => null,
            'client_package_id' => $package?->id,
            'industry_type'     => $request->industry_type,
            'entity_type'       => $request->entity_type,
            'state'             => $request->state,
            'trial_ends_at'     => (!$requiresPayment && $package?->is_trial_allowed)
                ? now()->addDays($package->trial_days)
                : null,
        ]);

        CompanyBranding::create(['company_id' => $company->id]);

        // Create trial subscription when no payment is needed
        if ($package && !$requiresPayment) {
            Subscription::create([
                'company_id'           => $company->id,
                'package_id'           => null,
                'client_package_id'    => $package->id,
                'status'               => 'trialing',
                'billing_cycle'        => 'monthly',
                'trial_ends_at'        => $company->trial_ends_at,
                'current_period_start' => now(),
                'current_period_end'   => $company->trial_ends_at ?? now()->addDays(14),
            ]);
        }

        // Create the new company's admin user
        $role    = Role::firstOrCreate(['name' => 'client_admin', 'guard_name' => 'web']);
        $newUser = User::create([
            'company_id'        => $company->id,
            'name'              => $request->admin_name,
            'email'             => $request->admin_email,
            'password'          => Hash::make($request->admin_password),
            'status'            => $requiresPayment ? 'inactive' : 'active',
            'admin_approval'    => true,
            'email_verified_at' => ($setting?->email_verification || $requiresPayment) ? null : now(),
        ]);
        $newUser->assignRole($role);

        // Link the CREATING admin to the new company via user_companies
        $authUser->managedCompanies()->syncWithoutDetaching([
            $company->id => ['role' => 'client_admin'],
        ]);

        // ── Path A: Stripe checkout ─────────────────────────────────────────
        if ($requiresPayment) {
            try {
                $stripe  = new StripeService();
                $session = $stripe->createCheckoutSession(
                    company: $company,
                    package: $package,           // ClientPackage — compatible duck-type
                    billingCycle: 'monthly',
                    successUrl: route('my-companies.payment.success')
                        . '?session_id={CHECKOUT_SESSION_ID}&company_id=' . $company->id,
                    cancelUrl: route('my-companies.payment.cancel', $company->id),
                );

                session([
                    'pending_company_id'      => $company->id,
                    'pending_company_user_id' => $newUser->id,
                ]);

                Log::info('ClientAdmin company: redirecting to Stripe', [
                    'company_id' => $company->id,
                    'session_id' => $session->id,
                ]);

                return redirect($session->url);

            } catch (\Throwable $e) {
                Log::warning('Stripe checkout failed for client company creation: ' . $e->getMessage());
                // Fall through to immediate activation
            }
        }

        // ── Path B: Activate immediately ────────────────────────────────────
        $this->activateCompany($company, $newUser, $package, $setting);

        return redirect()->route('my-companies.index')
            ->with('success', "Company '{$company->company_name}' created successfully!");
    }

    // ── Called by Stripe after successful payment ────────────────────────────

    public function paymentSuccess(\Illuminate\Http\Request $request)
    {
        $companyId = $request->input('company_id') ?? session('pending_company_id');
        $userId    = session('pending_company_user_id');

        $company = $companyId
            ? Company::withoutGlobalScopes()->find($companyId)
            : null;

        if (!$company) {
            return redirect()->route('my-companies.index')
                ->with('error', 'Could not find the company. Please contact support.');
        }

        $setting = global_setting();
        $newUser = $userId
            ? User::withoutGlobalScopes()->find($userId)
            : User::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'client_admin'))
                ->first();

        $package = $company->client_package_id
            ? ClientPackage::find($company->client_package_id)
            : null;

        $this->activateCompany($company, $newUser, $package, $setting);

        session()->forget(['pending_company_id', 'pending_company_user_id']);

        return redirect()->route('my-companies.index')
            ->with('success', "Payment successful! Company '{$company->company_name}' is now active.");
    }

    // ── Called when user cancels Stripe checkout ─────────────────────────────

    public function paymentCancel(\Illuminate\Http\Request $request, int $id)
    {
        $company = Company::withoutGlobalScopes()
            ->where('id', $id)
            ->where('status', 'inactive')
            ->where('created_by', auth()->id())
            ->first();

        if ($company) {
            User::withoutGlobalScopes()->where('company_id', $company->id)->delete();
            CompanyBranding::where('company_id', $company->id)->delete();
            Subscription::where('company_id', $company->id)->delete();
            auth()->user()->managedCompanies()->detach($company->id);
            $company->delete();
        }

        session()->forget(['pending_company_id', 'pending_company_user_id']);

        return redirect()->route('my-companies.create')
            ->with('error', 'Payment was cancelled. The company was not created.');
    }

    // ── Assign Plan ──────────────────────────────────────────────────────────

    public function assignPlan(Request $request, int $id)
    {
        $companyId = company()?->id ?? auth()->user()->company_id;

        $company = Company::withoutGlobalScopes()
            ->where('id', $id)
            ->where('created_by', $companyId)
            ->firstOrFail();

        $request->validate([
            'client_package_id' => 'nullable|exists:client_packages,id',
        ]);

        $company->client_package_id = $request->client_package_id ?: null;
        $company->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Plan assigned to ' . $company->company_name . '.',
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function activateCompany(
        Company         $company,
        ?User           $adminUser,
        ?\App\Models\ClientPackage $package,
        ?GlobalSetting  $setting
    ): void {
        $company->update([
            'status'        => 'active',
            'trial_ends_at' => $package?->is_trial_allowed
                ? now()->addDays($package->trial_days)
                : $company->trial_ends_at,
        ]);

        if ($adminUser) {
            $adminUser->update([
                'status'            => 'active',
                'email_verified_at' => $adminUser->email_verified_at
                    ?? ($setting?->email_verification ? null : now()),
            ]);
        }

        if ($package && !Subscription::where('company_id', $company->id)->exists()) {
            Subscription::create([
                'company_id'           => $company->id,
                'package_id'           => null,
                'client_package_id'    => $package->id,
                'status'               => 'trialing',
                'billing_cycle'        => 'monthly',
                'trial_ends_at'        => $company->fresh()->trial_ends_at,
                'current_period_start' => now(),
                'current_period_end'   => $company->fresh()->trial_ends_at ?? now()->addDays(14),
            ]);
        }
    }

    // ── Quick-add company from modal (clients page) ──────────────────────────

    public function quickStore(Request $request)
    {
        abort_403(!auth()->user()->hasAnyRole(['client_admin', 'manager']));

        $companyId = company()?->id ?? auth()->user()->company_id;

        $data = $request->validate([
            'company_name'  => 'required|string|max:255',
            'industry_type' => 'nullable|string|max:100',
            'entity_type'   => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'client_id'     => 'nullable|exists:users,id',
        ]);

        $subdomain = \Illuminate\Support\Str::slug($data['company_name']) . '-' . rand(100, 999);

        // Resolve client email for company_email
        $clientEmail = null;
        if (!empty($data['client_id'])) {
            $clientUser  = User::withoutGlobalScopes()->find($data['client_id']);
            $clientEmail = $clientUser?->email;
        }

        $company = Company::create([
            'created_by'    => $companyId,
            'company_name'  => $data['company_name'],
            'app_name'      => $data['company_name'],
            'company_email' => $clientEmail ?? auth()->user()->email,
            'subdomain'     => $subdomain,
            'status'        => 'active',
            'industry_type' => $data['industry_type'] ?? null,
            'entity_type'   => $data['entity_type'] ?? null,
            'state'         => $data['state'] ?? null,
        ]);

        CompanyBranding::create(['company_id' => $company->id]);

        // Link the selected client user to this new company
        if (!empty($data['client_id']) && isset($clientUser)) {
            $clientUser->company_id = $company->id;
            $clientUser->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Company '{$company->company_name}' added successfully.",
        ]);
    }

    private function stripeIsConfigured(): bool
    {
        try {
            $creds = \App\Models\SuperAdmin\GlobalPaymentGatewayCredentials::first();
            return (bool) $creds?->getActiveSecret();
        } catch (\Throwable) {
            return false;
        }
    }
}
