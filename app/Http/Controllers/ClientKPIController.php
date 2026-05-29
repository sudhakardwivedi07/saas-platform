<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\Kpis\KpiFormattedDataService;
use App\Services\Kpis\QuickBooksKpiDataService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewContract;

class ClientKPIController extends AccountBaseController
{
    /**
     * Display the Client KPI Dashboard
     */
    public function index($clientId): ViewContract
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $this->clientUser = User::with(['managedCompanies'])->findOrFail($clientId);
        $this->pageTitle = $this->clientUser->name . "'s KPI Dashboard";

        $companies = EloquentCollection::make($this->clientUser->portfolioCompanies()->all())->load([
            'package',
            'clientPackage',
            'subscription',
        ]);
        $selectedCompanyId = request('company_id') ?: $companies->first()?->id;

        $this->data = array_merge($this->data, [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
        ]);

        return view('clients.kpi-dashboard', $this->data);
    }

    /**
     * Show KPI detail page
     */
    public function showKpiDetail($clientId, $kpiType): ViewContract
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $this->clientUser = User::with(['managedCompanies'])->findOrFail($clientId);
        $this->pageTitle = $this->getKpiTitle($kpiType) . ' Details';

        $companies = EloquentCollection::make($this->clientUser->portfolioCompanies()->all())->load([
            'package',
            'clientPackage',
            'subscription',
        ]);

        $selectedCompanyId = request('company_id') ?: $companies->first()?->id;

        $specific = "dashboard.kpis.{$kpiType}.detail";
        $viewPath = View::exists($specific) ? $specific : 'dashboard.kpis.generic-detail';

        $this->data = array_merge($this->data, [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'kpiType' => $kpiType,
            'clientId' => $clientId,
            'kpiDataUrl' => route('clients.kpi-data', $clientId),
            'kpiListBackUrl' => route('clients.dashboard', $clientId),
            'kpiPageTitle' => $this->getKpiTitle($kpiType),
        ]);

        return view($viewPath, $this->data);
    }

    /**
     * Get KPI title for page title
     */
    private function getKpiTitle(string $kpiType): string
    {
        return match ($kpiType) {
            'runway-burn' => 'Runway & Burn',
            'working-capital' => 'Working Capital',
            'operating-cashflow' => 'Operating Cash Flow',
            'revenue' => 'Revenue',
            'ebitda' => 'EBITDA',
            'margin-trends' => 'Margin Trends',
            'ar-aging' => 'AR Aging',
            'ap-leverage' => 'AP Leverage',
            'revenue-concentration' => 'Revenue Concentration',
            default => 'KPI',
        };
    }

    /**
     * Get KPI data for a specific company
     */
    public function getKpiData($clientId, Request $request)
    {
        abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));

        $companyId = (int) $request->get('company_id');
        $kpiType = $request->get('kpi');

        $clientUser = User::findOrFail($clientId);
        $company = $clientUser->portfolioCompanies()->firstWhere('id', $companyId);
        abort_if(! $company instanceof Company, 404);

        if (! $clientUser->companyHasFinancialKpiSource($companyId)) {
            return response()->json([
                'success' => true,
                'kpis_unavailable' => true,
                'data' => [],
            ]);
        }

        try {
            $data = QuickBooksKpiDataService::usingReportOwner((int) $clientUser->id, function () use ($companyId, $kpiType) {
                return KpiFormattedDataService::get($companyId, $kpiType);
            });
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load KPI data'
            ], 500);
        }
    }
}