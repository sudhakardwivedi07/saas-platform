<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\Document;
use App\Models\Payment;
use App\Models\QuickBooksConnection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        // Recent activity — withoutGlobalScopes() bypasses TenantScope so we can query
        // across sub-companies (Document and QuickBooksConnection both use HasTenant,
        // which would otherwise append AND company_id = {parent_id} and hide all sub-company records)
        $allIds = $subCompanyIds->prepend($companyId);

        $this->recentDocs = Document::withoutGlobalScopes()
            ->with('uploader')
            ->whereIn('company_id', $allIds)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $this->qbConnections = QuickBooksConnection::withoutGlobalScopes()
            ->whereIn('company_id', $allIds)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Onboarding state (fresh user = no sub-companies yet)
        $this->isFreshUser  = $allCompanies->isEmpty();
        $this->brandingDone = $company->branding?->logo !== null;
        $this->settingsDone = ClientPackage::where('company_id', $companyId)->where('is_active', true)->exists();

        return view('dashboard.index', $this->data);
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
        $this->clientAdminName  = $parentCompany?->app_name
            ?? $parentCompany?->company_name
            ?? $this->appName;
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
