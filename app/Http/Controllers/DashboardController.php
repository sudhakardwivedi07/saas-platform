<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Document;
use App\Models\Payment;
use App\Models\QuickBooksConnection;
use App\Models\User;

class DashboardController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Dashboard';
    }

    public function index()
    {
        $company = company();

        // SuperAdmin has no tenant context — send them to the SuperAdmin panel
        if (!$company) {
            return redirect()->route('superadmin.dashboard');
        }

        $companyId = $company->id;

        // Sub-companies created by this client admin
        $allCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['quickbooksConnections'])
            ->get();

        // Clients = users whose company_id is one of those sub-companies
        $subCompanyIds = $allCompanies->pluck('id');
        $allClients    = User::whereIn('company_id', $subCompanyIds)->get();
        $this->clientCount       = $allClients->count();
        $this->activeClientCount = $allClients->where('status', 'active')->count();
        $this->companyCount       = $allCompanies->count();
        $this->activeCompanyCount = $allCompanies->where('status', 'active')->count();
        $this->qbConnectedCount   = $allCompanies->filter(fn($c) => $c->quickbooksConnections->where('is_active', true)->isNotEmpty())->count();

        // Subscriptions
        $this->activeSubscriptions = $allCompanies->filter(fn($c) => in_array($c->status, ['active', 'trial']))->count();

        // Insights / risk (placeholder — wire to real data when available)
        $this->insightsGenerated = 0;
        $this->insightsAvailable = 0;
        $this->companiesAtRisk   = $allCompanies->filter(fn($c) => $c->status === 'inactive')->count();

        // Finance
        $this->totalRevenue    = Payment::where('company_id', $companyId)->where('status', 'complete')->sum('amount') / 100;
        $this->paymentsReceived = Payment::where('company_id', $companyId)->where('status', 'complete')->count();

        // Current subscription (the client_admin's own plan)
        $this->subscription  = $company->subscription;

        // Recent activity
        $this->recentDocs = Document::with('uploader')
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $this->qbConnections = QuickBooksConnection::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        return view('dashboard.index', $this->data);
    }
}
