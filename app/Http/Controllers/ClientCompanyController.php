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
use App\Models\EventLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ClientCompanyController extends AccountBaseController
{
    private function companyOwnerKeyForCurrentUser(): int
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return (int) $user->company_id;
        }
        // Client users: attribute new companies to the admin's company so they appear
        // in the admin's Companies tab (which queries by created_by = admin_company_id).
        $adminCompanyId = Company::withoutGlobalScopes()
            ->where('id', $user->company_id)
            ->value('created_by');
        return $adminCompanyId ? (int) $adminCompanyId : (int) $user->id;
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
        return $user->can('manage_company') || $user->hasRole('client');
    }

    private function canAddCompanies(): bool
    {
        $user = auth()->user();
        return $user->can('add_company') || $user->hasRole('client');
    }

    private function enforceMaxCompaniesLimit(int $ownerCompanyId): void
    {
        $ownerCompany = Company::withoutGlobalScopes()->find($ownerCompanyId);
        $plan         = $ownerCompany?->activePlan();
        $maxCompanies = $plan?->max_companies;

        // No plan assigned or no limit set — allow freely
        if (!$plan || !$maxCompanies || (int) $maxCompanies <= 0) {
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
            // withoutGlobalScopes bypasses HasTenant scope so connections created by admin are visible
            'quickbooksConnections' => fn ($q) => $q->withoutGlobalScopes()->where('is_active', true),
        ]);

        // Check company limit — no plan means unlimited
        $ownerCompany          = Company::withoutGlobalScopes()->find($companyId);
        $plan                  = $ownerCompany?->activePlan();
        $maxCompanies          = (int) ($plan?->max_companies ?? 0);
        $currentCompaniesCount = $this->companies->count();
        $canAddCompany         = $maxCompanies === 0 || $currentCompaniesCount < $maxCompanies;

        $this->canAddCompany          = $canAddCompany;
        $this->maxCompanies           = $maxCompanies;
        $this->currentCompaniesCount  = $currentCompaniesCount;
        $this->hasActivePlan          = true; // plan no longer required to add companies

        return view('companies.index', $this->data);
    }

    // ── Show create form ─────────────────────────────────────────────────────

    public function create()
    {
        abort_unless($this->canAddCompanies(), 403, 'You do not have permission to add companies.');

        $companyId = auth()->user()->company_id;
        $this->enforceMaxCompaniesLimit($companyId);

        // Existing clients this admin can assign as company admin
        $this->existingClients = User::whereHas('roles', fn ($q) => $q->where('name', 'client'))
            ->whereHas('company', fn ($q) => $q->where('created_by', $companyId))
            ->orderBy('name')
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

        // Some companies may not have a dedicated admin user.
        // Fall back to the first linked user so details still render.
        $this->companyAdminUser = $this->company->adminUser ?: $this->company->users->first();

        $authUser = auth()->user();
        $this->isClientUserFlow = $authUser->hasRole('client');

        return view('companies.edit', $this->data);
    }

    public function update(Request $request, int $id)
    {
        abort_unless($this->canManageCompanies(), 403, 'You do not have permission to manage companies.');

        $company = $this->findEditableCompany($id);

        $data = $request->validate([
            'company_name'  => 'required|string|max:255',
            'industry_type' => 'nullable|string|max:100',
            'entity_type'   => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
        ]);

        $updatableKeys = ['company_name', 'industry_type', 'entity_type', 'state'];
        $safeData = collect($data)->only($updatableKeys)->toArray();

        $company->update($safeData);

        $isClientUserFlow = auth()->user()->hasRole('client');

        if ($isClientUserFlow) {
            $indexUrl = route('my-companies.index');
            if ($request->query('_imp')) {
                $indexUrl .= '?_imp=' . $request->query('_imp');
            }
            $successRedirect = redirect()->to($indexUrl);
        } elseif ($request->filled('_from_client_id')) {
            $successRedirect = redirect()->to(
                route('clients.show', $request->integer('_from_client_id')) . '?tab=companies'
            );
        } else {
            $successRedirect = redirect()->to(route('clients.index') . '?tab=companies');
        }

        return $successRedirect->with('success', 'Company <strong>' . e($company->company_name) . '</strong> updated successfully!');
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
        $authUser         = auth()->user();
        $isClientUserFlow = $authUser->hasRole('client');

        $rules = [
            'company_name'  => 'required|string|max:255',
            'industry_type' => 'nullable|string|max:100',
            'entity_type'   => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
        ];

        $adminType = $request->input('admin_type', 'new');

        if (!$isClientUserFlow) {
            if ($adminType === 'existing') {
                $rules['existing_client_id'] = 'required|exists:users,id';
            } else {
                $rules['admin_name']  = 'required|string|max:255';
                $rules['admin_email'] = 'required|email|max:255|unique:users,email';
                $rules['admin_phone'] = 'required|digits:10';
            }
        }

        $request->validate($rules);

        $setting      = global_setting();
        $createdByKey = $this->companyOwnerKeyForCurrentUser();

        // Pre-validate existing client ownership BEFORE creating the company so that
        // a 403 abort cannot leave an orphaned company record in the database.
        $existingUser = null;
        if (!$isClientUserFlow && $adminType === 'existing') {
            $existingUser = User::findOrFail($request->existing_client_id);
            abort_unless(
                (int) ($existingUser->company?->created_by) === (int) $companyId,
                403,
                'You cannot assign this user.'
            );
        }

        Log::info('ClientAdmin company creation', ['created_by' => $createdByKey]);

        // Auto-generate a unique subdomain from the company name
        $baseSubdomain = Str::slug($request->company_name);
        $subdomain     = $baseSubdomain;
        $counter       = 1;
        while (Company::withoutGlobalScopes()->where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSubdomain . '-' . $counter++;
        }

        // Create company — always active, no plan required
        // Derive company_email from the client account (not user-entered)
        $derivedEmail = auth()->user()->email;
        if ($adminType === 'existing' && isset($existingUser)) {
            $derivedEmail = $existingUser->email;
        } elseif ($adminType === 'new' && $request->filled('admin_email')) {
            $derivedEmail = $request->admin_email;
        }

        $company = Company::create([
            'created_by'    => $createdByKey,
            'company_name'  => $request->company_name,
            'app_name'      => $request->company_name,
            'company_email' => $derivedEmail,
            'subdomain'     => $subdomain,
            'status'        => 'active',
            'package_id'    => null,
            'industry_type' => $request->industry_type,
            'entity_type'   => $request->entity_type,
            'state'         => $request->state,
        ]);

        CompanyBranding::create(['company_id' => $company->id]);

        $newUser = null;
        if (!$isClientUserFlow) {
            if ($adminType === 'existing') {
                // $existingUser already validated and fetched above
                // Link the existing user to the new company via pivot WITHOUT changing their
                // primary company_id — preserving their association with existing companies.
                $existingUser->managedCompanies()->syncWithoutDetaching([
                    $company->id => ['role' => 'admin'],
                ]);
                $newUser = $existingUser;
            } else {
                $role    = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
                $newUser = User::create([
                    'company_id'        => $company->id,
                    'name'              => $request->admin_name,
                    'email'             => $request->admin_email,
                    'mobile'            => $request->admin_phone,
                    'password'          => Hash::make(Str::random(16)),
                    'status'            => 'active',
                    'admin_approval'    => true,
                    'email_verified_at' => $setting?->email_verification ? null : now(),
                ]);
                $newUser->assignRole($role);
            }
        }

        // Link the creating admin to the new company
        $authUser->managedCompanies()->syncWithoutDetaching([
            $company->id => ['role' => 'admin'],
        ]);

        try {
            EventLog::create([
                'company_id'  => $createdByKey,
                'user_id'     => auth()->id(),
                'event_type'  => 'company_created',
                'description' => "Company '{$company->company_name}' was created",
                'metadata'    => [
                    'actor_company_id' => $company->id,
                    'company_name'     => $company->company_name,
                ],
                'ip_address'  => request()->ip(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Company creation activity log failed: ' . $e->getMessage());
        }

        $this->activateCompany($company, $newUser, null, $setting);

        if ($isClientUserFlow) {
            $indexUrl = route('my-companies.index');
            if ($request->query('_imp')) {
                $indexUrl .= '?_imp=' . $request->query('_imp');
            }
            $storeRedirect = redirect()->to($indexUrl);
        } else {
            $storeRedirect = redirect()->to(route('clients.index') . '?tab=companies');
        }

        return $storeRedirect->with('success', 'Company <strong>' . e($company->company_name) . '</strong> created successfully!');
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
                ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
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

        // Create or update subscription with 'active' status if a plan is assigned
        if ($request->client_package_id) {
            $plan = ClientPackage::findOrFail($request->client_package_id);
            $existingSub = $company->subscription;
            if ($existingSub) {
                // Update existing subscription
                $existingSub->update([
                    'client_package_id' => $plan->id,
                    'status'            => 'active',
                    'canceled_at'       => null,
                ]);
            } else {
                // Create new subscription
                Subscription::create([
                    'company_id'           => $company->id,
                    'package_id'           => $company->package_id,
                    'client_package_id'    => $plan->id,
                    'status'               => 'active',
                    'billing_cycle'        => 'monthly',
                    'current_period_start' => now(),
                    'current_period_end'   => now()->addMonth(),
                    'trial_ends_at'        => null,
                ]);
            }
        }

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
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $companyId = company()?->id ?? auth()->user()->company_id;
        $this->enforceMaxCompaniesLimit($companyId);

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
