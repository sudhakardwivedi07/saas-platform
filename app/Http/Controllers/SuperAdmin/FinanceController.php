<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\Payment;

class FinanceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Finance';
    }

    public function index()
    {
        $this->mrr = Payment::withoutGlobalScopes()
            ->where('status', 'complete')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $this->arr = $this->mrr * 12;

        $this->totalRevenue = Payment::withoutGlobalScopes()
            ->where('status', 'complete')
            ->sum('amount');

        $this->recentPayments = Payment::withoutGlobalScopes()
            ->with('company')
            ->where('status', 'complete')
            ->orderByDesc('paid_at')
            ->limit(25)
            ->get();

        $this->revenueByCompany = Company::withoutGlobalScopes()
            ->with(['payments' => function ($q) {
                $q->where('status', 'complete');
            }])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('super-admin.finance.index', $this->data);
    }
}
