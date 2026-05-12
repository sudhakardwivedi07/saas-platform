<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payment;
use App\Services\StripeDashboardService;
use Illuminate\Support\Collection;

class FinanceController extends AccountBaseController
{
    public function __construct(private StripeDashboardService $stripeDashboard)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Finance';
    }

    public function index()
    {
        abort_403(!auth()->user()->hasRole('admin'));

        $ownerCompanyId = (int) (company()?->id ?? auth()->user()->company_id);

        /** @var Collection<int,int> $portfolioCompanyIds — client firms created under this admin */
        $portfolioCompanyIds = Company::withoutGlobalScopes()
            ->where('created_by', $ownerCompanyId)
            ->pluck('id');

        if ($portfolioCompanyIds->isEmpty()) {
            $this->mrr = 0;
            $this->totalRevenue = 0;
            $this->recentPayments = collect();
            $this->activeSubscriptions = 0;
            $this->subscribedCompanies = collect();
            $this->isFreshFinance = true;

            return view('finance.index', $this->data);
        }

        // Payments are stored against each client company id, not the admin tenant company id.
        $paymentsBase = Payment::query()->whereIn('company_id', $portfolioCompanyIds);

        $this->mrr = (clone $paymentsBase)
            ->where('status', 'complete')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $this->totalRevenue = (clone $paymentsBase)
            ->where('status', 'complete')
            ->sum('amount');

        $this->recentPayments = Payment::query()
            ->whereIn('company_id', $portfolioCompanyIds)
            ->with([
                'company' => fn ($q) => $q->with([
                    'users' => fn ($uq) => $uq->orderBy('id')->limit(1),
                    'clientPackage',
                ]),
                'subscription.clientPackage',
                'subscription.package',
            ])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $this->activeSubscriptions = Company::withoutGlobalScopes()
            ->where('created_by', $ownerCompanyId)
            ->where(function ($query) {
                $query->whereHas('subscription', fn ($q) => $q->whereIn('status', ['active', 'trialing']))
                      ->orWhere(function ($subQuery) {
                          $subQuery->whereNotNull('client_package_id')
                                   ->where('status', 'active');
                      });
            })
            ->count();

        $this->subscribedCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $ownerCompanyId)
            ->with([
                'subscription.clientPackage',
                'subscription.package',
                'clientPackage',
                'package',
                'users' => fn ($q) => $q->orderBy('id')->limit(1),
            ])
            ->orderBy('company_name')
            ->get();

        // Show onboarding UI only when no portfolio companies exist yet.
        $this->isFreshFinance = false;

        return view('finance.index', $this->data);
    }

    /**
     * Open Stripe Dashboard for the invoice (staff must be logged into Stripe).
     */
    public function invoice(Payment $payment)
    {
        abort_403(!auth()->user()->hasRole('admin'));

        $ownerCompanyId = (int) (company()?->id ?? auth()->user()->company_id);
        $portfolioIds = Company::withoutGlobalScopes()
            ->where('created_by', $ownerCompanyId)
            ->pluck('id');

        abort_unless($portfolioIds->contains($payment->company_id), 404);

        if (!$payment->stripe_invoice_id) {
            return redirect()->route('finance.index')
                ->with('error', 'No Stripe invoice ID is stored for this payment.');
        }

        return redirect()->away($this->stripeDashboard->invoiceUrl($payment->stripe_invoice_id));
    }
}
