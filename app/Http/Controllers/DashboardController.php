<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\Document;
use App\Models\EventLog;
use App\Models\Payment;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Models\SuperAdmin\Package;
use App\Models\User;
use App\Services\Kpis\KpiFormattedDataService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class DashboardController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Dashboard';
    }

    public function index(Request $request)
    {
        $company = company();

        if (!$company) {
            return redirect()->route('superadmin.dashboard');
        }

        // Simple dashboard for basic users — no access to management stats
        if (!auth()->user()->hasAnyRole(['client_admin', 'manager'])) {
            $this->loadUserPlanData($company);
            return view('dashboard.user', $this->data);
        }

        $companyId = $company->id;

        // Resolve date range from period filter
        $period = in_array($request->input('period'), ['1W', '1M', '1Y', 'custom'])
            ? $request->input('period')
            : '1M';

        [$startDate, $endDate, $periodLabel] = $this->resolvePeriod($period, $request);

        // All sub-companies (used for activity feed, chart, QB lookup)
        $allCompanies  = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['quickbooksConnections'])
            ->get();
        $subCompanyIds = $allCompanies->pluck('id');

        // Period-filtered: companies created within selected date range
        $periodCompanies = $allCompanies->filter(
            fn($c) => $c->created_at->between($startDate, $endDate)
        );

        // Period-filtered: clients (users) created within selected date range
        $periodClients = User::whereIn('company_id', $subCompanyIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $this->clientCount        = $periodClients->count();
        $this->activeClientCount  = $periodClients->where('status', 'active')->count();
        $this->companyCount       = $periodCompanies->count();
        $this->activeCompanyCount = $periodCompanies->where('status', 'active')->count();
        $this->qbConnectedCount   = $periodCompanies->filter(
            fn($c) => $c->quickbooksConnections->where('is_active', true)->isNotEmpty()
        )->count();

        // Active subscriptions = period companies with active or trial status
        $this->activeSubscriptions = $periodCompanies->filter(
            fn($c) => in_array($c->status, ['active', 'trial'])
        )->count();

        // Insights (not yet implemented — no insights table)
        $this->insightsGenerated = 0;
        $this->insightsAvailable = 0;

        // Companies at risk in period = inactive
        $this->companiesAtRisk = $periodCompanies->filter(
            fn($c) => $c->status === 'inactive'
        )->count();

        // Finance — revenue from sub-companies (clients paying the firm), filtered by period
        // amount is stored in dollars (StripeService already divides cents by 100 before saving)
        $this->totalRevenue = Payment::whereIn('company_id', $subCompanyIds)
            ->where('status', 'complete')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        $this->paymentsReceived = Payment::whereIn('company_id', $subCompanyIds)
            ->where('status', 'complete')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->count();

        // Current subscription (the client_admin's own plan)
        $this->subscription = $company->subscription;

        // Period info passed to view
        $this->period         = $period;
        $this->periodLabel    = $periodLabel;
        $this->startDateInput = $startDate->format('Y-m-d');
        $this->endDateInput   = $endDate->format('Y-m-d');

        // Chart: cumulative company count per month for last 6 months
        $chartLabels    = [];
        $chartCompanies = [];
        $chartPayments  = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $chartLabels[]    = $m->format('M');
            $chartCompanies[] = $allCompanies->filter(
                fn($c) => $c->created_at->format('Y-m') <= $m->format('Y-m')
            )->count();
            $chartPayments[] = Payment::whereIn('company_id', $subCompanyIds)
                ->where('status', 'complete')
                ->whereYear('paid_at', $m->year)
                ->whereMonth('paid_at', $m->month)
                ->count();
        }
        $this->chartLabels    = $chartLabels;
        $this->chartCompanies = $chartCompanies;
        $this->chartPayments  = $chartPayments;

        // Recent activity — keep same source as Activity Log page for consistency.
        $this->recentActivities = EventLog::query()
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // Dashboard "Recent Activity" cards — documents & QB connections across client companies
        if ($subCompanyIds->isNotEmpty()) {
            $this->recentDocs = Document::withoutGlobalScopes()
                ->whereIn('company_id', $subCompanyIds)
                ->orderByDesc('created_at')
                ->limit(24)
                ->get();
            $this->qbConnections = QuickBooksConnection::withoutGlobalScopes()
                ->whereIn('company_id', $subCompanyIds)
                ->with('company')
                ->orderByDesc('created_at')
                ->limit(24)
                ->get();
        } else {
            $this->recentDocs = collect();
            $this->qbConnections = collect();
        }

        // Onboarding state (fresh user = no sub-companies yet)
        $this->isFreshUser  = $allCompanies->isEmpty();
        $this->brandingDone = $company->branding?->logo !== null;
        $this->settingsDone = ClientPackage::where('company_id', $companyId)->where('is_active', true)->exists();
        $this->clientPackages = ClientPackage::forCompany($companyId)
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('monthly_price')
            ->get();

        return view('dashboard.index', $this->data);
    }

    /**
     * Client admin "My Financials" page (KPI dashboard view).
     */
    public function myFinancials()
    {
        $user = auth()->user();
        abort_unless($user && $user->hasRole('client_admin'), 403);

        $company = company();
        abort_unless($company, 404);

        $this->pageTitle = 'My Financials';
        $tenantId = (int) $company->id;
        // Client admin "My Financials" is tenant-only (no sub-company picker).
        $this->kpiCompanies = collect([$company]);
        $this->selectedKpiCompanyId = $tenantId;
        $this->hasAnalyzedInsights = $user->hasAnalyzedInsightsForTenant($tenantId);
        $this->showKpiDashboard = $this->hasAnalyzedInsights;
        $this->isQuickbooksConnected = QuickBooksConnection::withoutGlobalScopes()
            ->where('company_id', $tenantId)
            ->where('is_active', true)
            ->exists();
        $this->kpiDataUrl = route('dashboard.kpi-data');
        $this->kpiDetailBaseUrl = url('/dashboard/kpi-detail');

        return view('dashboard.my-financials', $this->data);
    }

    public function assignPlan(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(!$user->hasAnyRole(['client_admin', 'manager']), 403);

        $company = company();
        abort_unless($company, 404);

        $parentId = $company->created_by;
        abort_unless($parentId, 403);

        $validated = $request->validate([
            'client_package_id' => 'required|integer',
        ]);

        $plan = ClientPackage::where('company_id', $parentId)
            ->where('is_active', true)
            ->findOrFail((int) $validated['client_package_id']);

        // Legacy schemas may still require package_id (super-admin package) to be non-null.
        if (empty($company->package_id)) {
            $fallbackPackageId = Package::query()
                ->where('is_active', true)
                ->orderBy('sort')
                ->value('id');

            if ($fallbackPackageId) {
                $company->package_id = $fallbackPackageId;
            }
        }

        $company->client_package_id = $plan->id;
        $company->save();

        return back()->with('success', "Plan '{$plan->name}' assigned successfully.");
    }

    /**
     * JSON KPI payloads for the logged-in end-user dashboard (and detail pages).
     */
    public function kpiData(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $companyId = (int) $request->query('company_id', 0);
        $kpiType   = (string) $request->query('kpi', '');
        abort_if($companyId <= 0 || $kpiType === '', 422);

        $this->assertUserCanAccessKpiCompany($user, $companyId);

        if (! $user->companyHasFinancialKpiSource($companyId)) {
            return response()->json([
                'success' => true,
                'kpis_unavailable' => true,
                'data' => [],
            ]);
        }

        try {
            $data = KpiFormattedDataService::get($companyId, $kpiType);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load KPI data'], 500);
        }
    }

    /**
     * KPI drill-down page.
     */
    public function kpiDetail(string $kpiType)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if (!preg_match('/^[a-z0-9\-]+$/', $kpiType)) {
            abort(404);
        }

        $specific = "dashboard.kpis.{$kpiType}.detail";
        $viewPath = View::exists($specific) ? $specific : 'dashboard.kpis.generic-detail';

        [$companies, $selectedCompanyId] = $this->kpiCompanyContext($user);

        $this->data = array_merge($this->data, [
            'companies'           => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'kpiType'             => $kpiType,
            'clientId'            => $user->id,
            'kpiDataUrl'          => route('dashboard.kpi-data'),
            'kpiListBackUrl'      => route('dashboard'),
            'kpiPageTitle'        => $this->kpiPageTitle($kpiType),
        ]);
        $this->pageTitle = $this->kpiPageTitle($kpiType) . ' Details';

        return view($viewPath, $this->data);
    }

    private function loadUserPlanData(Company $company): void
    {
        $parentId = $company->created_by;

        $plans = $parentId
            ? ClientPackage::where('company_id', $parentId)
                ->where('is_active', true)
                ->orderBy('sort')
                ->orderBy('monthly_price')
                ->get()
            : collect();

        $parentCompany = $parentId ? Company::find($parentId) : null;

        $this->plans            = $plans;
        $this->currentPlan      = $company->activePlan();
        $this->subscription     = $company->subscription;
        $this->isQuickbooksConnected = QuickBooksConnection::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        $user = auth()->user();
        $tenantId = (int) $company->id;
        $latestAnalyzedReport = $this->latestAnalyzedKpiReportForUser($user, $tenantId);
        $eligibleCompanies = $user->kpiEligiblePortfolioCompanies();

        if ($latestAnalyzedReport?->company_id && ! $eligibleCompanies->contains(fn ($c) => (int) $c->id === (int) $latestAnalyzedReport->company_id)) {
            $reportCompany = Company::withoutGlobalScopes()->find((int) $latestAnalyzedReport->company_id);
            if ($reportCompany) {
                $eligibleCompanies->push($reportCompany);
            }
        }

        // End-user dashboard should switch to KPI counters after an analyzed insight,
        // using the company that produced the latest analyzed report.
        $this->kpiCompanies = $eligibleCompanies->isNotEmpty() ? $eligibleCompanies->unique('id')->values() : collect([$company]);
        $this->selectedKpiCompanyId = (int) ($latestAnalyzedReport?->company_id ?: $tenantId);
        $this->hasAnalyzedInsights = (bool) $latestAnalyzedReport;
        // Executive KPI layout only after at least one analyzed insight (QB alone → classic dashboard).
        $this->showKpiDashboard = $this->hasAnalyzedInsights;

        if ($this->showKpiDashboard) {
            $this->plans = collect();
        }

        $this->kpiDataUrl       = route('dashboard.kpi-data');
        $this->kpiDetailBaseUrl = url('/dashboard/kpi-detail');

        $this->insightInProgress = Report::where('user_id', auth()->id())
            ->whereIn('status', ['Uploading', 'Analyzing'])
            ->first();

        // Monthly limit for the limit-reached modal
        $limitValue = $this->currentPlan
            ? ($this->currentPlan->insights_per_month ?? $this->currentPlan->max_insights_per_month ?? null)
            : null;
        $this->monthlyLimit = $limitValue ? (int) $limitValue : null;
        $this->monthlyUsed  = Report::countMonthlyUsageForUserCompany(
            (int) $user->id,
            (int) $company->id,
            (int) $user->company_id
        );

        $this->clientAdminName  = $parentCompany?->app_name
            ?? $parentCompany?->company_name
            ?? $this->appName;
    }

    private function latestAnalyzedKpiReportForUser(User $user, int $tenantCompanyId): ?Report
    {
        $portfolioIds = $user->portfolioCompanies()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! in_array($tenantCompanyId, $portfolioIds, true)) {
            $portfolioIds[] = $tenantCompanyId;
        }

        return Report::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
            ->where(function ($q) use ($portfolioIds, $tenantCompanyId, $user) {
                $q->whereIn('company_id', $portfolioIds);
                if ($user->company_id && (int) $tenantCompanyId === (int) $user->company_id) {
                    $q->orWhereNull('company_id');
                }
            })
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Same company list as My Companies / Insights (portfolio). KPI cards still respect
     * QuickBooks / analyzed data per company via kpiData → kpis_unavailable.
     */
    private function collectKpiCompaniesForUser(User $user): EloquentCollection
    {
        return EloquentCollection::make($user->portfolioCompanies()->all())->load([
            'package',
            'clientPackage',
            'subscription',
        ]);
    }

    private function resolveDefaultKpiCompanyId(User $user, \Illuminate\Support\Collection $companies): ?int
    {
        if ($companies->isEmpty()) {
            return null;
        }
        $req = request()->query('company_id');
        if ($req !== null && $req !== '' && $companies->contains(fn (Company $c) => (int) $c->id === (int) $req)) {
            return (int) $req;
        }
        $tenantId = (int) (company()?->id ?? 0);
        if ($tenantId && $companies->contains(fn (Company $c) => (int) $c->id === $tenantId)) {
            return $tenantId;
        }
        $withQb = $companies->first(fn (Company $c) => $this->companyHasActiveQuickBooks((int) $c->id));

        return $withQb?->id ?? $companies->first()->id;
    }

    private function companyHasActiveQuickBooks(int $companyId): bool
    {
        return QuickBooksConnection::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists();
    }

    private function assertUserCanAccessKpiCompany(User $user, int $companyId): void
    {
        if ($user->portfolioCompanies()->contains(fn ($c) => (int) $c->id === $companyId)) {
            return;
        }
        $tenantId = (int) (company()?->id ?? 0);
        if ($tenantId && (int) $companyId === $tenantId && $user->companyHasFinancialKpiSource($companyId)) {
            return;
        }
        abort(403);
    }

    private function kpiCompanyContext(User $user): array
    {
        $companies = $this->collectKpiCompaniesForUser($user);
        $defaultId = $this->resolveDefaultKpiCompanyId($user, $companies);

        return [$companies, $defaultId];
    }

    private function kpiPageTitle(string $kpiType): string
    {
        return match ($kpiType) {
            'runway-burn' => 'Runway & Burn Details',
            'working-capital' => 'Working Capital Details',
            'operating-cashflow' => 'Operating Cash Flow Details',
            'revenue' => 'Revenue Details',
            'ebitda' => 'EBITDA Details',
            'margin-trends' => 'Margin Trends Details',
            'ar-aging' => 'AR Aging Details',
            'ap-leverage' => 'AP Leverage Details',
            'revenue-concentration' => 'Revenue Concentration Details',
            default => 'KPI Details',
        };
    }

    private function resolvePeriod(string $period, Request $request): array
    {
        switch ($period) {
            case '1W':
                return [now()->subDays(7)->startOfDay(), now()->endOfDay(), 'Last 7 Days'];
            case '1Y':
                return [now()->startOfYear(), now()->endOfDay(), now()->format('Y')];
            case 'custom':
                $start = $request->filled('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : now()->startOfMonth();
                $end = $request->filled('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : now()->endOfDay();
                $label = $start->format('M j') . ' – ' . $end->format('M j, Y');
                return [$start, $end, $label];
            default: // 1M
                return [now()->startOfMonth(), now()->endOfMonth(), now()->format('F Y')];
        }
    }
}
