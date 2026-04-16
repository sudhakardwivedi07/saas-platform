<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Company;

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
        $companyId = auth()->user()->company_id;

        $this->mrr = Payment::where('company_id', $companyId)
            ->where('status', 'complete')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $this->totalRevenue = Payment::where('company_id', $companyId)
            ->where('status', 'complete')
            ->sum('amount');

        $this->recentPayments = Payment::where('company_id', $companyId)
            ->where('status', 'complete')
            ->orderByDesc('paid_at')
            ->limit(20)
            ->get();

        return view('finance.index', $this->data);
    }
}
