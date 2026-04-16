<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Services\StripeService;
use Illuminate\Http\Request;

class SubscriptionController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Billing';
    }

    public function index()
    {
        $company   = company();
        $companyId = $company->id;

        $this->subscription = $company->subscription;
        $this->packages     = Package::active()->orderBy('sort')->get();

        // Sub-companies with their plan and QB data
        $this->subCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['clientPackage', 'package', 'quickbooksConnections', 'users' => fn($q) => $q->orderBy('id')->limit(1)])
            ->get();

        // Usage summary (wire to real insight model when available)
        $this->totalInsightsUsed      = 0;
        $this->totalInsightsRemaining = $this->subscription?->package?->max_documents ?? 0;
        $this->companiesAtRisk        = $this->subCompanies
            ->filter(fn($c) => $c->status === 'inactive' || $c->quickbooksConnections->where('is_active', true)->isEmpty())
            ->count();

        return view('billing.index', $this->data);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'package_id'    => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $company = company();
        $package = Package::findOrFail($request->package_id);

        $session = (new StripeService())->createCheckoutSession(
            $company,
            $package,
            $request->billing_cycle
        );

        return response()->json(['checkout_url' => $session->url]);
    }

    public function success()
    {
        return view('billing.success');
    }

    public function cancel()
    {
        return redirect()->route('billing.index')->with('info', 'Checkout was cancelled.');
    }
}
