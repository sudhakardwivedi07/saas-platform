<?php

namespace App\Http\Controllers;

use App\Models\ClientDashboardSetting;
use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientController extends AccountBaseController
{
    private function enforceMaxCompaniesLimit(int $ownerCompanyId): void
    {
        $ownerCompany = Company::withoutGlobalScopes()->find($ownerCompanyId);
        $plan = $ownerCompany?->activePlan();
        $maxCompanies = $plan?->max_companies;

        if (!$plan) {
            abort(
                422,
                'No plan is assigned to your account. Please select a subscription plan before adding clients or companies.'
            );
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

    private function ensureInsightsQuotaAvailable(Company $company): void
    {
        $plan = $company->activePlan();
        $limit = (int) ($plan?->insights_per_month ?? 0);

        if ($limit <= 0) {
            return;
        }

        $used = (int) ($company->insights_used ?? 0);
        abort_if(
            $used >= $limit,
            422,
            "Monthly insights limit reached ({$limit}/{$limit}). Upgrade your plan to continue."
        );
    }

    private function resolveFallbackPackageId(): ?int
    {
        return Package::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->value('id');
    }

    private function resolveClientPackageId(int $parentCompanyId, ?int $requestedPackageId): ?int
    {
        if ($requestedPackageId) {
            $requestedPlan = ClientPackage::forCompany($parentCompanyId)
                ->where('is_active', true)
                ->find($requestedPackageId);

            if ($requestedPlan) {
                return $requestedPlan->id;
            }
        }

        // Default to the first active free plan when no explicit plan was selected.
        $freePlan = ClientPackage::forCompany($parentCompanyId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('is_free', true)
                    ->orWhere('monthly_price', '<=', 0);
            })
            ->orderBy('sort')
            ->orderBy('id')
            ->first();

        return $freePlan?->id;
    }

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Clients';
    }

    public function index()
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $companyId = company()?->id ?? auth()->user()->company_id;

        // Companies created by this client admin
        $this->companies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with([
                'clientPackage',
                'package',
                'quickbooksConnections',
                'users' => fn($q) => $q->orderBy('id')->limit(1),
            ])
            ->orderByDesc('created_at')
            ->get();

        // All unique clients (first user of each sub-company) for the filter dropdown
        $this->clientsList = $this->companies
            ->map(fn($c) => $c->users->first())
            ->filter()
            ->unique('id')
            ->values();

        $subCompanyIds = $this->companies->pluck('id');

        // Client packages for assign plan modal
        $this->clientPackages = \App\Models\ClientPackage::forCompany($companyId)
            ->where('is_active', true)->orderBy('sort')->get();

        // Clients = users whose company_id is one of those sub-companies
        $this->clients = User::whereIn('company_id', $subCompanyIds)
            ->with(['company.quickbooksConnections', 'company.clientPackage', 'company.package'])
            ->orderByDesc('created_at')
            ->get();

        $tenant = Company::withoutGlobalScopes()->find($companyId);
        $plan   = $tenant?->activePlan();
        $this->hasActivePlan = (bool) $plan;

        // Plan's Max Clients limit (max_users field on the plan)
        $planMaxClients = (int) ($plan?->max_users ?? 0);
        $currentClientCount = $this->companies->count();
        $this->planMaxClients    = $planMaxClients;
        $this->currentClientCount = $currentClientCount;
        $this->maxClientsReached  = $planMaxClients > 0 && $currentClientCount >= $planMaxClients;

        return view('clients.index', $this->data);
    }

    public function show($id)
    {
        $this->client = User::with(['company.quickbooksConnections'])->findOrFail($id);
        $this->pageTitle = $this->client->name;
        return view('clients.show', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!auth()->user()->can('create_users'));

        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email',
            'phone'             => 'nullable|string|max:30',
            'company_name'      => 'required|string|max:255',
            'industry_type'     => 'required|string|max:100',
            'entity_type'       => 'required|string|max:100',
            'client_package_id' => 'nullable|exists:client_packages,id',
            'password'          => 'required|string|min:8',
        ]);

        $parentCompanyId = company()->id;

        try {
            $this->enforceMaxCompaniesLimit($parentCompanyId);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 422) {
                return redirect()->back()->with('error', $e->getMessage());
            }
            throw $e;
        }

        // Enforce plan's Max Clients limit (max_users on the plan)
        $ownerCompany = Company::withoutGlobalScopes()->find($parentCompanyId);
        $plan = $ownerCompany?->activePlan();
        if ($plan && isset($plan->max_users) && (int) $plan->max_users > 0) {
            $currentClientCount = Company::withoutGlobalScopes()
                ->where('created_by', $parentCompanyId)
                ->count();
            if ($currentClientCount >= (int) $plan->max_users) {
                return redirect()->route('clients.index')
                    ->with('error', 'Maximum client limit reached. Please upgrade your plan to add more clients.');
            }
        }

        $resolvedClientPackageId = $this->resolveClientPackageId(
            $parentCompanyId,
            $request->filled('client_package_id') ? (int) $request->client_package_id : null
        );

        $subCompany = Company::create([
            'created_by'        => $parentCompanyId,
            'company_name'      => $request->company_name,
            'company_email'     => $request->email,
            'status'            => 'active',
            'package_id'        => $this->resolveFallbackPackageId(),
            'industry_type'     => $request->industry_type,
            'entity_type'       => $request->entity_type,
            'client_package_id' => $resolvedClientPackageId,
        ]);

        $user = User::create([
            'company_id' => $subCompany->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'mobile'     => $request->phone,
            'password'   => Hash::make($request->password),
        ]);

        $user->assignRole('client');

        return redirect()->route('clients.index')
            ->with('success', 'Client "' . $user->name . '" created successfully. The client can now access their account.');
    }

    public function bulkUpload(\Illuminate\Http\Request $request)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $parentCompanyId = company()->id;
        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        $header = fgetcsv($handle);

        $created = 0;
        $skipped = 0;
        $errors  = [];

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $this->enforceMaxCompaniesLimit($parentCompanyId);
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                if ($e->getStatusCode() === 422) {
                    $errors[] = $e->getMessage();
                    break;
                }
                throw $e;
            }

            if (count($row) < 2) continue;

            [$clientName, $email] = array_map('trim', $row);
            $planTier = trim($row[2] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $pkg = $planTier
                ? ClientPackage::forCompany($parentCompanyId)
                    ->where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [strtolower($planTier)])
                    ->first()
                : null;

            $resolvedClientPackageId = $this->resolveClientPackageId(
                $parentCompanyId,
                $pkg?->id
            );

            $subCompany = Company::create([
                'created_by'        => $parentCompanyId,
                'company_name'      => $clientName ?: ($email . "'s Company"),
                'company_email'     => $email,
                'status'            => 'active',
                'package_id'        => $this->resolveFallbackPackageId(),
                'client_package_id' => $resolvedClientPackageId,
            ]);

            $user = User::create([
                'company_id' => $subCompany->id,
                'name'       => $clientName ?: $email,
                'email'      => $email,
                'password'   => Hash::make(\Illuminate\Support\Str::random(12)),
            ]);
            $user->assignRole('client');
            $created++;
        }

        fclose($handle);

        return response()->json([
            'status'  => 'success',
            'message' => "$created client(s) imported" . ($skipped ? ", $skipped skipped." : '.') . (!empty($errors) ? ' ' . $errors[0] : ''),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $client = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        $client->name   = $request->name;
        $client->mobile = $request->phone;
        $client->save();

        return redirect()->route('clients.index')
            ->with('success', 'Client "' . $client->name . '" updated successfully.');
    }

    public function assignPlan(Request $request, $id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $client  = User::findOrFail($id);
        $company = $client->company;

        if (!$company) {
            return response()->json(['status' => 'error', 'message' => 'Client has no associated company.'], 404);
        }

        $request->validate([
            'client_package_id' => 'nullable|exists:client_packages,id',
        ]);

        $company->client_package_id = $request->client_package_id ?: null;
        $company->save();

        if ($request->client_package_id) {
            $plan = ClientPackage::findOrFail($request->client_package_id);
            $existingSub = $company->subscription;

            if ($existingSub) {
                $existingSub->update([
                    'client_package_id' => $plan->id,
                    'status'            => 'active',
                    'canceled_at'       => null,
                ]);
            } else {
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
            'message' => 'Plan assigned to ' . $client->name . '.',
        ]);
    }

    public function destroy($id)
    {
        return back()->with('success', 'Client removed.');
    }

    public function toggleStatus($id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));
        $client = User::findOrFail($id);
        $client->status = $client->status === 'active' ? 'inactive' : 'active';
        $client->save();
        return response()->json([
            'status'  => 'success',
            'message' => 'Client ' . ($client->status === 'active' ? 'enabled' : 'disabled') . '.',
            'new_status' => $client->status,
        ]);
    }

    public function dashboard($id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $clientUser = User::with(['company.quickbooksConnections', 'company.clientPackage'])->findOrFail($id);
        $company    = $clientUser->company;
        abort_if(!$company, 404, 'Client has no company');

        $parentId = $company->created_by;

        $plans = $parentId
            ? ClientPackage::where('company_id', $parentId)
                ->where('is_active', true)
                ->orderBy('sort')
                ->orderBy('monthly_price')
                ->get()
            : collect();

        $currentPlan = $company->activePlan();

        $isQuickbooksConnected = QuickBooksConnection::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        // Find latest analyzed report for this client user
        $portfolioIds = $clientUser->portfolioCompanies()
            ->pluck('id')
            ->map(fn ($i) => (int) $i)
            ->all();
        if (!in_array((int) $company->id, $portfolioIds, true)) {
            $portfolioIds[] = (int) $company->id;
        }

        $latestAnalyzedReport = Report::query()
            ->where('user_id', $clientUser->id)
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
            ->where(function ($q) use ($portfolioIds, $company, $clientUser) {
                $q->whereIn('company_id', $portfolioIds);
                if ($clientUser->company_id && (int) $company->id === (int) $clientUser->company_id) {
                    $q->orWhereNull('company_id');
                }
            })
            ->orderByDesc('created_at')
            ->first();

        $hasAnalyzedInsights  = (bool) $latestAnalyzedReport;
        $showKpiDashboard     = $hasAnalyzedInsights;
        $selectedKpiCompanyId = (int) ($latestAnalyzedReport?->company_id ?: $company->id);

        $kpiCompanies = $clientUser->kpiEligiblePortfolioCompanies();
        if ($kpiCompanies->isEmpty()) {
            $kpiCompanies = collect([$company]);
        }

        if ($showKpiDashboard) {
            $plans = collect();
        }

        $insightInProgress = Report::where('user_id', $clientUser->id)
            ->whereIn('status', ['Uploading', 'Analyzing'])
            ->first();

        $limitValue   = $currentPlan
            ? ($currentPlan->insights_per_month ?? $currentPlan->max_insights_per_month ?? null)
            : null;
        $monthlyLimit = $limitValue ? (int) $limitValue : null;
        $monthlyUsed  = Report::countMonthlyUsageForUserCompany(
            (int) $clientUser->id,
            (int) $company->id,
            (int) $clientUser->company_id
        );

        $this->plans               = $plans;
        $this->currentPlan         = $currentPlan;
        $this->subscription        = $company->subscription;
        $this->isQuickbooksConnected = $isQuickbooksConnected;
        $this->kpiCompanies        = $kpiCompanies->unique('id')->values();
        $this->selectedKpiCompanyId = $selectedKpiCompanyId;
        $this->hasAnalyzedInsights = $hasAnalyzedInsights;
        $this->showKpiDashboard    = $showKpiDashboard;
        $this->kpiDataUrl          = route('clients.kpi-data', $clientUser->id);
        $this->kpiDetailBaseUrl    = url('/clients/' . $clientUser->id . '/kpi-detail');
        $this->insightInProgress   = $insightInProgress;
        $this->monthlyLimit        = $monthlyLimit;
        $this->monthlyUsed         = $monthlyUsed;
        $dashSetting = ClientDashboardSetting::forCompany($company->id);
        $this->dashboardPublishStatus = $dashSetting->publish_status;
        $this->hiddenKpis             = $dashSetting->hidden_kpis ?? [];
        $this->draftHiddenKpis        = $dashSetting->draft_hidden_kpis ?? [];

        $this->viewingAsUser       = $clientUser;
        $this->isAdminView         = true;
        $this->backUrl             = route('dashboard');
        $this->pageTitle           = $clientUser->name . "'s Dashboard";

        return view('dashboard.user', $this->data);
    }

    public function saveDashboardDraft(Request $request, $id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $clientUser = User::findOrFail($id);
        $company    = $clientUser->company;
        abort_if(!$company, 404);

        $hiddenKpis = array_values(array_filter((array) $request->input('hidden_kpis', [])));

        $setting = ClientDashboardSetting::forCompany($company->id);
        $setting->update([
            'draft_hidden_kpis' => $hiddenKpis,
            'publish_status'    => 'draft',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Draft saved']);
    }

    public function publishDashboard(Request $request, $id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $clientUser = User::findOrFail($id);
        $company    = $clientUser->company;
        abort_if(!$company, 404);

        $hiddenKpis = array_values(array_filter((array) $request->input('hidden_kpis', [])));

        $setting = ClientDashboardSetting::forCompany($company->id);
        $setting->update([
            'hidden_kpis'       => $hiddenKpis,
            'draft_hidden_kpis' => $hiddenKpis,
            'publish_status'    => 'published',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Dashboard published']);
    }

    public function insights($id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $clientUser = User::findOrFail($id);
        $company    = $clientUser->company;
        abort_if(!$company, 404);

        $this->ensureInsightsQuotaAvailable($company);

        $qbConnection = QuickBooksConnection::withoutGlobalScopes()
            ->where('user_id', $clientUser->id)
            ->where('is_active', true)
            ->first();

        $allCompanies      = $clientUser->portfolioCompanies();
        $defaultId         = (int) ($allCompanies->first()?->id ?: $company->id);
        $selectedCompanyId = (int) request()->get('company_id', $defaultId);
        if (!$allCompanies->contains(fn ($c) => (int) $c->id === $selectedCompanyId)) {
            $selectedCompanyId = $defaultId;
        }

        $reports = Report::withTrashed()
            ->where('user_id', $clientUser->id)
            ->where(function ($q) use ($selectedCompanyId, $clientUser) {
                $q->where('company_id', $selectedCompanyId);
                if ((int) $selectedCompanyId === (int) $clientUser->company_id) {
                    $q->orWhereNull('company_id');
                }
            })
            ->orderByDesc('created_at')
            ->get();

        $currentPlan  = $company?->clientPackage ?? $company?->package;
        $limitValue   = $currentPlan
            ? ($currentPlan->insights_per_month ?? $currentPlan->max_insights_per_month ?? null)
            : null;
        $monthlyLimit = $limitValue ? (int) $limitValue : null;
        $monthlyUsed  = Report::countMonthlyUsageForUserCompany(
            (int) $clientUser->id,
            (int) $selectedCompanyId,
            (int) $clientUser->company_id
        );

        $this->client            = $clientUser;
        $this->qbConnection      = $qbConnection;
        $this->qbConfigured      = $qbConnection !== null;
        $this->companies         = $allCompanies;
        $this->selectedCompanyId = $selectedCompanyId;
        $this->selectedCompany   = $allCompanies->firstWhere('id', $selectedCompanyId);
        $this->reports           = $reports;
        $this->monthlyUsed       = $monthlyUsed;
        $this->monthlyLimit      = $monthlyLimit;
        $this->currentPlan       = $currentPlan;
        $this->viewingAsUser     = $clientUser;
        $this->isAdminView       = true;
        $this->backUrl           = route('dashboard');
        $this->pageTitle         = $clientUser->name . "'s Insights";

        return view('insights.index', $this->data);
    }

    public function insightDetail($clientId, $insightId)
    {
        $this->client = User::findOrFail($clientId);
        $this->ensureInsightsQuotaAvailable($this->client->company);
        $this->pageTitle = 'Insight Detail';
        return view('clients.insight-detail', $this->data);
    }
}
