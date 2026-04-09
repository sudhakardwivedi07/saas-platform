<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\Payment;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;

class DashboardController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'SuperAdmin Dashboard';
    }

    public function index()
    {
        $this->stats = [
            'total_companies'      => Company::withoutGlobalScopes()->count(),
            'active_companies'     => Company::withoutGlobalScopes()->where('status', 'active')->count(),
            'total_revenue'        => Payment::withoutGlobalScopes()->where('status', 'complete')->sum('amount'),
            'monthly_revenue'      => Payment::withoutGlobalScopes()
                                        ->where('status', 'complete')
                                        ->whereMonth('paid_at', now()->month)
                                        ->sum('amount'),
            'active_subscriptions' => Subscription::withoutGlobalScopes()
                                        ->whereIn('status', ['active', 'trialing'])->count(),
            'total_users'          => User::whereNotNull('company_id')->count(),
        ];

        $this->recentCompanies = Company::withoutGlobalScopes()
            ->with('package')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $this->recentPayments = Payment::withoutGlobalScopes()
            ->with('company')
            ->where('status', 'complete')
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        $this->revenueChart = Payment::withoutGlobalScopes()
            ->where('status', 'complete')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('super-admin.dashboard.index', $this->data);
    }
}
