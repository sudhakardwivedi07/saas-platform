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
        // Only count companies that have an admin user (real tenant firms)
        $firmBase = Company::withoutGlobalScopes()
            ->whereHas('users', fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'admin')));

        $this->stats = [
            // Count only firms that have an admin user
            'total_companies'  => (clone $firmBase)->count(),
            'active_companies' => (clone $firmBase)->where('status', 'active')->count(),

            // Revenue: sum of all completed payments
            'total_revenue'   => Payment::withoutGlobalScopes()->where('status', 'complete')->sum('amount'),
            'monthly_revenue' => Payment::withoutGlobalScopes()
                                    ->where('status', 'complete')
                                    ->whereMonth('paid_at', now()->month)
                                    ->whereYear('paid_at', now()->year)
                                    ->sum('amount'),
            'yearly_revenue'  => Payment::withoutGlobalScopes()
                                    ->where('status', 'complete')
                                    ->whereYear('paid_at', now()->year)
                                    ->sum('amount'),

            'active_subscriptions' => Subscription::withoutGlobalScopes()
                                        ->whereIn('status', ['active', 'trialing'])->count(),

            // End-clients: users with the 'client' role
            'total_users' => User::withoutGlobalScopes()
                                ->whereNotNull('company_id')
                                ->whereHas('roles', fn($q) => $q->where('name', 'client'))
                                ->count(),
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
