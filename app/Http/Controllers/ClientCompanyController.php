<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\GlobalSetting;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ClientCompanyController extends AccountBaseController
{
    private function companyOwnerKeyForCurrentUser(): int
    {
        $user = auth()->user();
        return $user->hasAnyRole(['client_admin', 'manager'])
            ? (int) $user->company_id
            : (int) $user->id;
    }

    private function resolveFallbackPackageId(): ?int
    {
        return Package::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->value('id');
    }

    private function canManageCompanies(): bool
    {
        $user = auth()->user();
        return $user->can('manage_company') || $user->hasRole('user');
    }

    private function canAddCompanies(): bool
    {
        $user = auth()->user();
        return $user->can('add_company') || $user->hasRole('user');
    }

    private function enforceMaxCompaniesLimit(int $ownerCompanyId): void
    {
        $ownerCompany = Company::withoutGlobalScopes()->find($ownerCompanyId);
        $plan = $ownerCompany?->activePlan();
        $maxCompanies = $plan?->max_companies;

        if (!$plan) {
            abort(422, 'No plan is assigned to your account. Please select a plan before adding companies.');
        }

        if (!$maxCompanies || (int) $maxCompanies <= 0) {
            return;
        }

        $createdCompaniesCount = Company::withoutGlobalScopes()
            ->whereIn('created_by', array_filter([$ownerCompanyId, auth()->id()]))
            ->count();

        abort_if(
            $createdCompaniesCount >= (int) $maxCompanies,
            422,
            "Company limit reached for your plan ({$maxCompanies} max companies)."
        );
    }

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth']);
        $this->pageTitle = 'My Companies';
    }

    // ── List companies managed by this admin ─────────────────────────────────

    public function index()
    {
        abort_unless($this->canManageCompanies(), 403, 'You do not have permission to manage companies.');

        $authUser = auth()->user();
        $companyId = $authUser->company_id;

        // Same scope as Insights / KPI company pickers (see User::portfolioCompanies()).
        $this->companies = $authUser->portfolioCompanies()->load([
            'package',
            'clientPackage',
            'subscription',
        ]);

        // Check if user can add more companies based on their plan
        $ownerCompany = Company::withoutGlobalScopes()->find($companyId);
        $plan = $ownerCompany?->activePlan();
        $maxCompanies = $plan?->max_companies ?? 0;
        $currentCompaniesCount = $this->companies->count();
        $hasActivePlan = (bool) $plan;
        $canAddCompany = $hasActivePlan && ($maxCompanies == 0 || $currentCompaniesCount < $maxCompanies);

        $this->canAddCompany = $canAddCompany;
        $this->maxCompanies = $maxCompanies;
        $this->currentCompaniesCount = $currentCompaniesCount;
        $this->hasActivePlan = $hasActivePlan;

        return view('companies.index', $this->data);
    }

    // ── Show create form ─────────────────────────────────────────────────────

    public function create()
    {
        abort_unless($this->canAddCompanies(), 403, 'You do not have permission to add companies.');

        $companyId = auth()->user()->company_id;
        $this->enforceMaxCompaniesLimit($companyId);

        // Load only THIS client_admin's own plans (not SuperAdmin packages)
        $this->packages = ClientPackage::forCompany($companyId)
            ->active()
            ->orderBy('sort')
            ->get();

        return view('companies.create', $this->data);
    }

    public function edit(int $id)
    {
        abort_unless($this->canManageCompanies(), 403, 'You do not have permission to manage companies.');

        $company = $this->findEditableCompany($id);
        $this->company = $company;
        $this->company->loadMissing([
            'adminUser',
            'clientPackage',
            'users' => fn ($query) => $query->orderBy('id'),
        ]);

        // Some companies may not have a dedicated client_admin user.
        // Fall back to the first linked user so details still render.
        $this->companyAdminUser = $this->company->adminUser ?: $this->company->users->first();

        $authUser = auth()->user();
        $this->isClientUserFlow = $authUser->hasRole('user');

        if (!$this->isClientUserFlow) {
            $this->packages = ClientPackage::forCompany($authUser->company_id)
                ->active()
                ->orderBy('sort')
                ->get();
        }

        return view('companies.edit', $this->data);
    }

    public function update(Request $request, int $id)
    {
        abort_unless($this->canManageCompanies(), 403, 'You do not have permission to manage companies.');

        $company = $this->findEditableCompany($id);

        $data = $request->validate([
            'company_name'  => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'industry_type' => 'nullable|string|max:100',
            'entity_type'   => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
        ]);

        // Defense-in-depth: only these keys are ever writable from edit form.
        // Disabled/read-only fields (plan/admin/subdomain/package) are ignored
        // even if someone tampers with the request payload.
        $updatableKeys = ['company_name', 'company_email', 'industry_type', 'entity_type', 'state'];
        $safeData = collect($data)->only($updatableKeys)->toArray();

        $company->update($safeData);

        return redirect()->route('my-companies.index')
            ->with('success', "Company '{$company->company_name}' updated successfully!");
    }

    public function destroy(int $id)
    {
        abort_unless($this->canManageCompanies(), 403, 'You do not have permission to manage companies.');

        $user = auth()->user();
        $managedCompanyIds = $user->managedCompanies()->withoutGlobalScopes()->pluck('companies.id');
        $ownerKey = $this->companyOwnerKeyForCurrentUser();

        $company = Company::withoutGlobalScopes()
            ->where('id', $id)
            ->where(function ($query) use ($ownerKey, $managedCompanyIds, $user) {
                $query->where('created_by', $ownerKey)
                    ->orWhereIn('id', $managedCompanyIds)
                    ->orWhere('id', $user->company_id);
            })
            ->firstOrFail();

        if ((int) auth()->user()->company_id === (int) $company->id) {
            return redirect()->route('my-companies.index')
                ->with('error', 'Your primary company cannot be deleted.');
        }

        DB::transaction(function () use ($company) {
            User::withoutGlobalScopes()->where('company_id', $company->id)->delete();
            CompanyBranding::where('company_id', $company->id)->delete();
            Subscription::where('company_id', $company->id)->delete();
            DB::table('user_companies')->where('company_id', $company->id)->delete();
            $company->delete();
        });

        return redirect()->route('my-companies.index')
            ->with('success', 'Company deleted successfully!');
    }

    // ── Store new company ────────────────────────────────────────────────────

    public function store(Request $request)
    {
        abort_unless($this->canAddCompanies(), 403);

        $companyId = auth()->user()->company_id;
        $this->enforceMaxCompaniesLimit($companyId);
        $authUser = auth()->user();
        $isClientUserFlow = $authUser->hasRole('user');

        $rules = [
            'company_name'    => 'required|string|max:255',
            'subdomain'       => 'required|string|max:63|alpha_dash|unique:companies,subdomain',
            'company_email'   => 'required|email|max:255',
            'package_id'      => 'nullable|exists:client_packages,id',
            'industry_type'   => 'required|string|max:100',
            'entity_type'     => 'required|string|max:100',
            'state'           => 'nullable|string|max:100',
        ];

        if (!$isClientUserFlow) {
            $rules['admin_name'] = 'required|string|max:255';
            $rules['admin_email'] = 'required|email|max:255|unique:users,email';
            $rules['admin_password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $setting  = global_setting();

        // Resolve plan:
        // - user flow: always use the client's currently assigned plan
        // - admin/manager flow: use selected plan or default first active plan
        $package = $isClientUserFlow
            ? $authUser->company?->clientPackage
            : ($request->package_id
                ? ClientPackage::forCompany($companyId)->find($request->package_id)
                : ClientPackage::forCompany($companyId)->active()->orderBy('sort')->first());

        if ($isClientUserFlow && !$package) {
            return back()->withErrors([
                'package_id' => 'No plan is assigned to your account. Please contact your administrator.',
            ])->withInput();
        }
 
        // Determine if Stripe payment is required
        $stripeReady     = $this->stripeIsConfigured();
        $requiresPayment = $package
            && !$package->is_free
            && $package->monthly_price > 0
            && $stripeReady;

        $createdByKey = $this->companyOwnerKeyForCurrentUser();

        Log::info('ClientAdmin company creation', [
            'created_by'       => $createdByKey,
            'client_plan'      => $package?->name,
            'stripe_ready'     => $stripeReady,
            'requires_payment' => $requiresPayment,
        ]);

        // Create the new company (inactive until payment confirmed)
        $company = Company::create([
            'created_by'        => $createdByKey,
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
                'package_id'           => $this->resolveFallbackPackageId(),
                'client_package_id'    => $package->id,
                'status'               => 'trialing',
                'billing_cycle'        => 'monthly',
                'trial_ends_at'        => $company->trial_ends_at,
                'current_period_start' => now(),
                'current_period_end'   => $company->trial_ends_at ?? now()->addDays(14),
            ]);
        }

        $newUser = null;
        if (!$isClientUserFlow) {
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
        }

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
                    'pending_company_user_id' => $newUser?->id,
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
        $ownerKey = $this->companyOwnerKeyForCurrentUser();

        $company = Company::withoutGlobalScopes()
            ->where('id', $id)
            ->where('status', 'inactive')
            ->where(function ($query) use ($ownerKey) {
                $query->where('created_by', $ownerKey)
                    ->orWhere('created_by', auth()->id());
            })
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
        $company = $this->findEditableCompany($id);

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
                'package_id'           => $this->resolveFallbackPackageId(),
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
        $this->enforceMaxCompaniesLimit($companyId);

        $data = $request->validate([
            'company_name'  => 'required|string|max:255',
            'industry_type' => 'required|string|max:100',
            'entity_type'   => 'required|string|max:100',
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
            'industry_type' => $data['industry_type'],
            'entity_type'   => $data['entity_type'],
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

    private function findEditableCompany(int $id): Company
    {
        $user = auth()->user();
        $managedCompanyIds = $user->managedCompanies()->withoutGlobalScopes()->pluck('companies.id');
        $ownerKey = $this->companyOwnerKeyForCurrentUser();

        return Company::withoutGlobalScopes()
            ->where('id', $id)
            ->where(function ($query) use ($user, $managedCompanyIds, $ownerKey) {
                $query->where('created_by', $ownerKey)
                    ->orWhereIn('id', $managedCompanyIds)
                    ->orWhere('id', $user->company_id);
            })
            ->firstOrFail();
    }
}
