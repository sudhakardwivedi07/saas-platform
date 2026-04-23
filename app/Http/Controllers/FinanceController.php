<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payment;

class FinanceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Finance';
    }

    public function index()
    {
        abort_403(!auth()->user()->hasRole('client_admin'));

        $companyId = company()?->id ?? auth()->user()->company_id;

        $this->mrr = Payment::where('company_id', $companyId)
            ->where('status', 'complete')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount') / 100;

        $this->totalRevenue = Payment::where('company_id', $companyId)
            ->where('status', 'complete')
            ->sum('amount') / 100;

        $this->recentPayments = Payment::where('company_id', $companyId)
            ->where('status', 'complete')
            ->orderByDesc('paid_at')
            ->limit(50)
            ->get();

        $this->activeSubscriptions = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->whereHas('subscription', fn($q) => $q->whereIn('status', ['active', 'trialing']))
            ->count();

        $this->subscribedCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['subscription.package', 'clientPackage'])
            ->orderBy('company_name')
            ->get();

        $this->isFreshFinance = $this->recentPayments->isEmpty();

        return view('finance.index', $this->data);
    }
}
