<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\Kpis\KpiFormattedDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardKPIController extends AccountBaseController
{
    private const KPI_TYPES = [
        'runway-burn',
        'working-capital',
        'operating-cashflow',
        'revenue',
        'ebitda',
        'margin-trends',
        'ar-aging',
        'ap-leverage',
        'revenue-concentration',
    ];

    private const KPI_VIEW_MAP = [
        'runway-burn' => 'dashboard.kpis.runway-burn.detail',
        'working-capital' => 'dashboard.kpis.working-capital.detail',
        'operating-cashflow' => 'dashboard.kpis.operating-cashflow.detail',
        'revenue' => 'dashboard.kpis.revenue.detail',
        'ebitda' => 'dashboard.kpis.ebitda.detail',
        'margin-trends' => 'dashboard.kpis.margin-trends.detail',
        'ar-aging' => 'dashboard.kpis.ar-aging.detail',
        'ap-leverage' => 'dashboard.kpis.ap-leverage.detail',
        'revenue-concentration' => 'dashboard.kpis.revenue-concentration.detail',
    ];

    private const KPI_TITLE_MAP = [
        'runway-burn' => 'Runway & Burn',
        'working-capital' => 'Working Capital',
        'operating-cashflow' => 'Operating Cash Flow',
        'revenue' => 'Revenue',
        'ebitda' => 'EBITDA',
        'margin-trends' => 'Margin Trends',
        'ar-aging' => 'AR Aging',
        'ap-leverage' => 'AP Leverage',
        'revenue-concentration' => 'Revenue Concentration',
    ];

    private function resolveKpiCompany(User $user, Request $request): int
    {
        $requested = (int) $request->query('company_id', 0);
        $tenantId = (int) (company()?->id ?? $user->company_id ?? 0);

        if ($requested > 0) {
            if ((int) $tenantId === $requested) {
                return $requested;
            }
            $allowed = $user->portfolioCompanies()->contains(fn ($c) => (int) $c->id === $requested);
            if ($allowed) {
                return $requested;
            }
        }

        return $tenantId;
    }

    private function assertCanAccessCompany(User $user, int $companyId): void
    {
        $tenantId = (int) (company()?->id ?? $user->company_id ?? 0);
        if ($tenantId && $companyId === $tenantId) {
            return;
        }
        if ($user->portfolioCompanies()->contains(fn ($c) => (int) $c->id === $companyId)) {
            return;
        }
        abort(403);
    }

    private function detailView(string $kpiType, string $routeName, Request $request)
    {
        abort_unless(in_array($kpiType, self::KPI_TYPES, true), 404);
        $user = auth()->user();
        abort_unless($user, 403);

        $companyId = $this->resolveKpiCompany($user, $request);
        $this->assertCanAccessCompany($user, $companyId);

        $this->pageTitle = (self::KPI_TITLE_MAP[$kpiType] ?? 'KPI') . ' Details';

        return view(self::KPI_VIEW_MAP[$kpiType], array_merge($this->data, [
            'kpiType' => $kpiType,
            'detailTitle' => self::KPI_TITLE_MAP[$kpiType] ?? 'KPI',
            'kpiPageTitle' => self::KPI_TITLE_MAP[$kpiType] ?? 'KPI',
            'selectedCompanyId' => $companyId,
            'kpiDataUrl' => route('dashboard-kpi.' . $routeName),
            'kpiListBackUrl' => auth()->user()->hasRole('client_admin')
                ? route('my-financials.index')
                : route('dashboard'),
        ]));
    }

    private function dataFor(string $kpiType, Request $request): JsonResponse
    {
        abort_unless(in_array($kpiType, self::KPI_TYPES, true), 404);
        $user = auth()->user();
        abort_unless($user, 403);

        $companyId = $this->resolveKpiCompany($user, $request);
        $this->assertCanAccessCompany($user, $companyId);

        if (! $user->companyHasFinancialKpiSource($companyId)) {
            return response()->json([
                'success' => true,
                'kpis_unavailable' => true,
                'data' => [],
            ]);
        }

        try {
            return response()->json([
                'success' => true,
                'data' => KpiFormattedDataService::get($companyId, $kpiType),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load KPI details',
            ], 500);
        }
    }

    public function runwayBurnDetails(Request $request) { return $this->detailView('runway-burn', 'cash-burn-runway', $request); }
    public function workingCapitalDetails(Request $request) { return $this->detailView('working-capital', 'working-capital', $request); }
    public function operatingCashFlowDetails(Request $request) { return $this->detailView('operating-cashflow', 'operating-cash-flow', $request); }
    public function revenueDetails(Request $request) { return $this->detailView('revenue', 'revenue-trend', $request); }
    public function ebitdaDetails(Request $request) { return $this->detailView('ebitda', 'ebitda', $request); }
    public function marginTrendsDetails(Request $request) { return $this->detailView('margin-trends', 'margin-trends', $request); }
    public function arAgingDetails(Request $request) { return $this->detailView('ar-aging', 'ar-aging', $request); }
    public function apAgingDetails(Request $request) { return $this->detailView('ap-leverage', 'ap-aging', $request); }
    public function revenueConcentrationDetails(Request $request) { return $this->detailView('revenue-concentration', 'revenue-concentration', $request); }

    public function cashBurnRunway(Request $request): JsonResponse { return $this->dataFor('runway-burn', $request); }
    public function workingCapital(Request $request): JsonResponse { return $this->dataFor('working-capital', $request); }
    public function operatingCashFlow(Request $request): JsonResponse { return $this->dataFor('operating-cashflow', $request); }
    public function revenueTrend(Request $request): JsonResponse { return $this->dataFor('revenue', $request); }
    public function ebitda(Request $request): JsonResponse { return $this->dataFor('ebitda', $request); }
    public function marginTrends(Request $request): JsonResponse { return $this->dataFor('margin-trends', $request); }
    public function arAging(Request $request): JsonResponse { return $this->dataFor('ar-aging', $request); }
    public function apAging(Request $request): JsonResponse { return $this->dataFor('ap-leverage', $request); }
    public function revenueConcentration(Request $request): JsonResponse { return $this->dataFor('revenue-concentration', $request); }
}

