<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\Payment;
use Illuminate\Http\Request;

class FinanceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Finance';
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $this->period = $period;

        [$from, $to] = $this->periodRange($period);

        $this->mrr = Payment::withoutGlobalScopes()
            ->where('status', 'complete')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $this->arr = $this->mrr * 12;

        $this->totalRevenue = Payment::withoutGlobalScopes()
            ->where('status', 'complete')
            ->when($from, fn($q) => $q->whereBetween('paid_at', [$from, $to]))
            ->sum('amount');

        $this->recentPayments = Payment::withoutGlobalScopes()
            ->with('company')
            ->where('status', 'complete')
            ->when($from, fn($q) => $q->whereBetween('paid_at', [$from, $to]))
            ->orderByDesc('paid_at')
            ->limit(25)
            ->get();

        $this->revenueByCompany = Company::withoutGlobalScopes()
            ->with(['payments' => function ($q) use ($from, $to) {
                $q->where('status', 'complete');
                if ($from) {
                    $q->whereBetween('paid_at', [$from, $to]);
                }
            }, 'package'])
            ->withCount('users')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('super-admin.finance.index', $this->data);
    }

    public function data(Request $request)
    {
        $period = $request->get('period', 'this_month');

        [$from, $to] = $this->periodRange($period);

        $mrr = Payment::withoutGlobalScopes()
            ->where('status', 'complete')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $totalRevenue = Payment::withoutGlobalScopes()
            ->where('status', 'complete')
            ->when($from, fn($q) => $q->whereBetween('paid_at', [$from, $to]))
            ->sum('amount');

        $companies = Company::withoutGlobalScopes()
            ->with(['payments' => function ($q) use ($from, $to) {
                $q->where('status', 'complete');
                if ($from) {
                    $q->whereBetween('paid_at', [$from, $to]);
                }
            }, 'package'])
            ->withCount('users')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $firmCount  = $companies->count();
        $avgPerFirm = $firmCount > 0 ? round($totalRevenue / $firmCount) : 0;
        $rootDomain = config('tenancy.root_domain');

        $revenueByFirm = $companies->map(function ($co) use ($rootDomain) {
            $total       = $co->payments->sum('amount');
            $lastPayment = $co->payments->sortByDesc('paid_at')->first();

            return [
                'initials'    => strtoupper(substr($co->company_name, 0, 2)),
                'name'        => $co->company_name,
                'domain'      => $co->subdomain . '.' . $rootDomain,
                'email'       => $co->company_email ?? '—',
                'plan'        => $co->package?->name ?? '—',
                'total'       => '$' . number_format($total, 0),
                'nextBilling' => $lastPayment ? $lastPayment->paid_at?->addMonth()->format('M d, Y') : '—',
                'joined'      => $co->created_at?->format('M d, Y') ?? '—',
                'payStatus'   => $total > 0 ? 'Paid' : 'Pending',
                'payBadge'    => $total > 0 ? 'b-green' : 'b-amber',
            ];
        });

        $upcomingRenewals = $companies->take(8)->map(function ($co) {
            $last         = $co->payments->sortByDesc('paid_at')->first();
            $renewal      = $last ? $last->paid_at?->addMonth() : null;
            $daysLeft     = $renewal ? (int) now()->diffInDays($renewal, false) : null;
            $maxInsights  = max(1, $co->package?->max_users ?? 50);
            $usedInsights = $co->users_count ?? 0;
            $usagePct     = min(100, (int) round(($usedInsights / $maxInsights) * 100));
            $riskLabel    = $usagePct >= 80 ? 'High' : ($usagePct >= 60 ? 'Medium' : 'Low');
            $riskBadge    = $usagePct >= 80 ? 'b-red' : ($usagePct >= 60 ? 'b-amber' : 'b-green');
            $planName     = $co->package?->name ?? '';
            $planBadge    = match (strtolower($planName)) {
                'enterprise' => 'b-blue',
                'starter'    => 'b-amber',
                'pro'        => 'b-purple',
                default      => 'b-gray',
            };

            return [
                'initials'     => strtoupper(substr($co->company_name, 0, 2)),
                'name'         => $co->company_name,
                'plan'         => $planName ?: null,
                'planBadge'    => $planBadge,
                'renewalDate'  => $renewal ? $renewal->format('M j, Y') : null,
                'daysLeft'     => $daysLeft,
                'amount'       => '$' . number_format($co->package?->monthly_price ?? 0, 2),
                'lastLogin'    => $co->last_active_at ? $co->last_active_at->diffForHumans() : null,
                'usedInsights' => $usedInsights,
                'maxInsights'  => $maxInsights,
                'usagePct'     => $usagePct,
                'barColor'     => $usagePct >= 80 ? 'var(--orange)' : 'var(--blue)',
                'riskLabel'    => $riskLabel,
                'riskBadge'    => $riskBadge,
            ];
        })->values();

        return response()->json([
            'totalRevenue'     => '$' . number_format($totalRevenue, 0),
            'firmCount'        => $firmCount,
            'mrr'              => '$' . number_format($mrr, 0),
            'avgPerFirm'       => '$' . number_format($avgPerFirm, 0),
            'revenueByFirm'    => $revenueByFirm,
            'upcomingRenewals' => $upcomingRenewals,
            'renewalCount'     => $upcomingRenewals->count(),
        ]);
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'all_time');

        [$from, $to] = $this->periodRange($period);

        $companies = Company::withoutGlobalScopes()
            ->with(['payments' => function ($q) use ($from, $to) {
                $q->where('status', 'complete');
                if ($from) {
                    $q->whereBetween('paid_at', [$from, $to]);
                }
            }, 'package'])
            ->withCount('users')
            ->orderByDesc('created_at')
            ->get();

        $filename = 'finance-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($companies) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Firm', 'Subdomain', 'Contact Email', 'Plan',
                'Monthly Fee', 'Total Collected', 'Billing', 'Next Billing',
                'Joined', 'Users', 'Status',
            ]);

            foreach ($companies as $co) {
                $total       = $co->payments->sum('amount');
                $lastPayment = $co->payments->sortByDesc('paid_at')->first();
                $nextBilling = $lastPayment ? $lastPayment->paid_at?->addMonth()->format('Y-m-d') : '';

                fputcsv($handle, [
                    $co->company_name,
                    $co->subdomain,
                    $co->company_email,
                    $co->package?->name ?? '',
                    number_format($co->package?->monthly_price ?? 0, 2),
                    number_format($total, 2),
                    'Monthly',
                    $nextBilling,
                    $co->created_at?->format('Y-m-d'),
                    $co->users_count,
                    $co->status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'this_month'    => [now()->startOfMonth(), now()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfDay(), now()->endOfDay()],
            'this_year'     => [now()->startOfYear(), now()->endOfYear()],
            default         => [null, null],
        };
    }
}
