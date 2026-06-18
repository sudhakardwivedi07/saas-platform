<?php

namespace App\Http\Controllers;

use App\Models\ClientDashboardSetting;
use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\EventLog;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientController extends AccountBaseController
{
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
                'users' => fn($q) => $q->orderBy('id')->limit(1)->with('quickbooksConnections'),
                // managers covers existing clients assigned via pivot (company_id not changed)
                'managers' => fn($q) => $q->whereHas('roles', fn($r) => $r->whereIn('name', ['client', 'client_admin']))->with('quickbooksConnections'),
            ])
            ->orderByDesc('created_at')
            ->get();

        // All unique clients for the filter dropdown — check users first, fall back to managers
        $this->clientsList = $this->companies
            ->map(fn($c) => $c->users->first() ?? $c->managers->first())
            ->filter()
            ->unique('id')
            ->values();

        $subCompanyIds = $this->companies->pluck('id');

        // Client packages for assign plan modal
        $this->clientPackages = \App\Models\ClientPackage::forCompany($companyId)
            ->where('is_active', true)->orderBy('sort')->get();

        // Clients = users whose company_id is one of those sub-companies
        $subCompanyIdsArray = $subCompanyIds->toArray();
        $this->clients = User::whereIn('company_id', $subCompanyIds)
            ->with([
                'company.quickbooksConnections',
                'company.clientPackage',
                'company.package',
                // Load only managed companies that belong to this admin's portfolio
                'managedCompanies' => fn($q) => $q->withoutGlobalScopes()->whereIn('companies.id', $subCompanyIdsArray)->with('quickbooksConnections'),
            ])
            ->orderByDesc('created_at')
            ->get();

        $tenant = Company::withoutGlobalScopes()->find($companyId);
        $plan   = $tenant?->activePlan();
        $this->hasActivePlan = (bool) $plan;

        // Plan's Max Clients limit (max_users field on the plan)
        $planMaxClients = (int) ($plan?->max_users ?? 0);
        $currentClientCount = $this->clientsList->count();
        $this->planMaxClients    = $planMaxClients;
        $this->currentClientCount = $currentClientCount;
        $this->maxClientsReached  = $planMaxClients > 0 && $currentClientCount >= $planMaxClients;

        $this->brandingDone = $tenant?->branding?->logo !== null;

        // Existing clients for the Add Company modal
        $this->existingClients = User::whereHas('roles', fn ($q) => $q->where('name', 'client'))
            ->whereHas('company', fn ($q) => $q->where('created_by', $companyId))
            ->orderBy('name')
            ->get();

        return view('clients.index', $this->data);
    }

    public function show($id)
    {
        $this->client = User::with(['company.quickbooksConnections', 'managedCompanies.quickbooksConnections'])->findOrFail($id);

        $companyIds = collect([$this->client->company_id])
            ->merge($this->client->managedCompanies->pluck('id'))
            ->filter()->unique()->values();

        $this->activityLogs = \App\Models\EventLog::withoutGlobalScopes()
            ->where(function ($q) use ($id, $companyIds) {
                // Events logged directly by the client user
                $q->where('user_id', $id)
                // Events stored against client's own companies
                  ->orWhereIn('company_id', $companyIds);

                // QB / company-creation events: stored under admin company but with
                // metadata->actor_company_id = the actual client company ID.
                foreach ($companyIds as $coId) {
                    $q->orWhere('metadata->actor_company_id', $coId);
                }

                // Insight events (generated/published by admin via PartnerInsightsController)
                // store the client's company in metadata->client_company_id.
                foreach ($companyIds as $coId) {
                    $q->orWhere('metadata->client_company_id', $coId);
                }

                // Middleware events (Client Updated, Company Updated, etc.) store
                // the entity ID only in metadata->path, e.g. /clients/24 or /my-companies/45.
                $q->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.path')) REGEXP ?",
                    ['^/clients/' . (int)$id . '(/|$)']
                );
                foreach ($companyIds as $coId) {
                    $q->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.path')) REGEXP ?",
                        ['^/my-companies/' . (int)$coId . '(/|$)']
                    );
                }
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $this->pageTitle = $this->client->name;
        return view('clients.show', $this->data);
    }

    public function checkEmailExists(Request $request)
    {
        $email = $request->input('email', '');
        $exists = User::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function store(Request $request)
    {
        abort_403(!auth()->user()->can('create_users'));

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'phone'         => 'required|digits:10',
            'company_name'  => 'required|string|max:255',
            'industry_type' => 'nullable|string|max:100',
            'entity_type'   => 'nullable|string|max:100',
        ], [
            'phone.required' => 'Phone number is required.',
            'phone.digits'   => 'Phone number must be exactly 10 digits.',
        ]);

        $parentCompanyId = company()->id;

        // Plan client-limit check (max_users on the firm's Package)
        $ownerCompany = Company::withoutGlobalScopes()->find($parentCompanyId);
        $plan         = $ownerCompany?->activePlan();
        $planLimit    = (int) ($plan?->max_users ?? 0);
        if ($planLimit > 0) {
            $currentCount = Company::withoutGlobalScopes()
                ->where('created_by', $parentCompanyId)
                ->count();
            if ($currentCount >= $planLimit) {
                return redirect()->back()->withInput()
                    ->with('error', "Client limit reached. Your plan allows {$planLimit} client" . ($planLimit !== 1 ? 's' : '') . " and you already have {$currentCount}. Please upgrade your plan to add more.");
            }
        }

        $subCompany = Company::create([
            'created_by'    => $parentCompanyId,
            'company_name'  => $request->company_name,
            'company_email' => $request->email,
            'status'        => 'active',
            'package_id'    => $this->resolveFallbackPackageId(),
            'industry_type' => $request->industry_type,
            'entity_type'   => $request->entity_type,
        ]);

        $user = User::create([
            'company_id' => $subCompany->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'mobile'     => $request->phone,
            'password'   => Hash::make(Str::random(16)),
        ]);

        $user->assignRole('client');

        return redirect()->route('clients.index')
            ->with('success', 'Client <strong>' . e($user->name) . '</strong> added successfully.');
    }

    public function bulkUpload(\Illuminate\Http\Request $request)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $parentCompanyId = company()->id;
        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        fgetcsv($handle);

        // ── Read all rows for pre-flight checks ───────────────
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        // ── Pre-flight: enforce 200-row cap ───────────────────
        $totalRows = count($rows);
        if ($totalRows > 200) {
            return response()->json([
                'status'  => 'error',
                'message' => "The CSV contains {$totalRows} rows. Maximum allowed is 200 clients per upload. Please split the file and retry.",
            ], 422);
        }

        // ── Pre-flight: plan client-limit check ───────────────
        $ownerCompany = Company::withoutGlobalScopes()->find($parentCompanyId);
        $plan         = $ownerCompany?->activePlan();
        $planLimit    = (int) ($plan?->max_users ?? 0);
        $currentCount = Company::withoutGlobalScopes()
                            ->where('created_by', $parentCompanyId)
                            ->count();

        // How many slots are still available (-1 = unlimited)
        $remaining = $planLimit > 0 ? max(0, $planLimit - $currentCount) : PHP_INT_MAX;

        if ($planLimit > 0 && $remaining === 0) {
            return response()->json([
                'status'  => 'error',
                'message' => "Client limit reached. Your plan allows {$planLimit} client" . ($planLimit !== 1 ? 's' : '') . " and you already have {$currentCount}. Please upgrade your plan to add more.",
            ], 422);
        }

        // ── Row processing ─────────────────────────────────────
        $added        = 0;
        $duplicates   = 0;
        $invalid      = 0;
        $limitSkipped = 0;

        foreach ($rows as $row) {
            // All 4 columns are required: client_name, email, phone, company_name
            if (count($row) < 4) {
                $invalid++;
                continue;
            }

            $clientName  = trim($row[0]);
            $email       = strtolower(trim($row[1]));
            $phone       = preg_replace('/\D/', '', trim($row[2]));
            $companyName = trim($row[3]);

            // Validate client_name: required, max 255 chars
            if (!$clientName || strlen($clientName) > 255) {
                $invalid++;
                continue;
            }

            // Validate email: required, valid format, max 255 chars
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                $invalid++;
                continue;
            }

            // Validate phone: required, exactly 10 digits
            if (!$phone || strlen($phone) !== 10) {
                $invalid++;
                continue;
            }

            // Validate company_name: required, max 255 chars
            if (!$companyName || strlen($companyName) > 255) {
                $invalid++;
                continue;
            }

            // Plan limit: stop adding once the remaining slots are exhausted
            if ($added >= $remaining) {
                $limitSkipped++;
                continue;
            }

            // Duplicate check: email already registered
            if (User::where('email', $email)->exists()) {
                $duplicates++;
                continue;
            }

            // Create sub-company and client user
            $subCompany = Company::create([
                'created_by'    => $parentCompanyId,
                'company_name'  => $companyName ?: ($clientName ?: ($email . "'s Company")),
                'company_email' => $email,
                'status'        => 'active',
                'package_id'    => $this->resolveFallbackPackageId(),
            ]);

            $user = User::create([
                'company_id' => $subCompany->id,
                'name'       => $clientName,
                'email'      => $email,
                'mobile'     => $phone ?: null,
                'password'   => Hash::make(Str::random(16)),
            ]);
            $user->assignRole('client');
            $added++;

            try {
                EventLog::create([
                    'company_id'  => $parentCompanyId,
                    'user_id'     => auth()->id(),
                    'event_type'  => 'company_created',
                    'description' => "Company '{$subCompany->company_name}' was created via bulk upload",
                    'metadata'    => [
                        'company_name'     => $subCompany->company_name,
                        'actor_company_id' => $subCompany->id,
                        'via'              => 'bulk_upload',
                    ],
                    'ip_address'  => request()->ip(),
                    'created_at'  => now(),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Bulk upload activity log failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status'       => 'success',
            'added'        => $added,
            'duplicates'   => $duplicates,
            'invalid'      => $invalid,
            'limitSkipped' => $limitSkipped,
            'planLimit'    => $planLimit,
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $client = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|min:2|max:100',
            'phone' => 'nullable|digits:10',
        ]);

        $client->name   = $request->name;
        $client->mobile = $request->phone;
        $client->save();

        return redirect()->route('clients.index')
            ->with('success', 'Client <strong>' . e($client->name) . '</strong> updated successfully.');
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
        $canEdit = auth()->user()->hasAnyRole(['admin', 'manager']) || impersonating_admin_id();
        abort_403(!$canEdit);

        $clientUser = User::findOrFail($id);
        $requestedCompanyId = (int) $request->input('company_id', 0);
        if ($requestedCompanyId && $clientUser->portfolioCompanies()->contains(fn ($c) => (int) $c->id === $requestedCompanyId)) {
            $company = Company::withoutGlobalScopes()->find($requestedCompanyId);
        } else {
            $company = $clientUser->company;
        }
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
        $canEdit = auth()->user()->hasAnyRole(['admin', 'manager']) || impersonating_admin_id();
        abort_403(!$canEdit);

        $clientUser = User::findOrFail($id);
        $requestedCompanyId = (int) $request->input('company_id', 0);
        if ($requestedCompanyId && $clientUser->portfolioCompanies()->contains(fn ($c) => (int) $c->id === $requestedCompanyId)) {
            $company = Company::withoutGlobalScopes()->find($requestedCompanyId);
        } else {
            $company = $clientUser->company;
        }
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

    // ── Impersonation ──────────────────────────────────────────────────────

    public function loginAs(Request $request, int $id)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $adminId   = (int) auth()->id();
        $companyId = company()?->id ?? auth()->user()->company_id;

        $subCompanyIds = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->pluck('id');

        $client = User::whereIn('company_id', $subCompanyIds)->findOrFail($id);

        // ── Token-based "View as Client" ───────────────────────────────────────
        // We deliberately do NOT touch the session. Browsers share the session
        // cookie across ALL tabs, so any session change here would break the
        // original admin tab. Instead, we generate a long-lived token stored in
        // the cache. Every request from the client-view tab carries ?_imp=TOKEN;
        // ImpersonateClientViaToken middleware swaps auth per-request only.
        // sessionStorage (tab-specific in the browser) keeps the token alive
        // across navigations within the client-view tab.
        $token = Str::random(64);
        Cache::put('imp_view_' . $token, [
            'admin_id'     => $adminId,
            'client_id'    => $client->id,
            'client_name'  => $client->name,
            'client_email' => $client->email,
        ], now()->addHours(8));

        $redirectTo = $request->input('redirect_to');
        $allowed    = ['/dashboard', '/insights'];
        $startPath  = ($redirectTo && in_array($redirectTo, $allowed, true))
            ? $redirectTo
            : '/dashboard';

        $tokenUrl = url($startPath) . '?_imp=' . $token;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['token_url' => $tokenUrl]);
        }

        return redirect($tokenUrl);
    }

    public function stopImpersonating()
    {
        $originalId = session('impersonating_client_original_id');

        // Graceful fallback: if session data is gone (expired or cookie issue),
        // log the user out and send them back to the login page instead of 403.
        if (!$originalId) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login')
                ->with('warning', 'Your impersonation session expired. Please log in again.');
        }

        // Fetch the original admin bypassing any tenant/global scopes that would
        // filter them out while we are currently scoped to the client's company.
        $originalUser = User::withoutGlobalScopes()->find($originalId);

        if (!$originalUser) {
            // Admin account no longer exists — clear everything and go to login.
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login')
                ->with('warning', 'The original admin account could not be found. Please log in again.');
        }

        // Clear impersonation markers, then restore the admin session.
        session()->forget([
            'impersonating_client_original_id',
            'impersonating_client_name',
            'impersonating_client_email',
        ]);

        Auth::login($originalUser);

        return redirect()->route('clients.index')->with('success', 'Returned to admin account.');
    }
}
