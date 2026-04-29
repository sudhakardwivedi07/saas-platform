<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Subscription as StripeSubscription;

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
        abort_403(!auth()->user()->can('view_billing'));

        $company   = company();
        $companyId = $company->id;

        $this->subscription = $company->subscription;
        $this->packages     = Package::active()->orderBy('sort')->get();

        // Sub-companies with their plan and QB data
        $this->subCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['clientPackage', 'package', 'quickbooksConnections', 'users' => fn($q) => $q->orderBy('id')->limit(1)])
            ->get();

        $subCompanies = $this->subCompanies;

        // Usage totals from companies.insights_used column
        $this->totalInsightsUsed = $subCompanies->sum('insights_used');
        $maxInsights             = $this->subscription?->package?->max_documents ?? 0;
        $this->totalInsightsRemaining = $maxInsights > 0
            ? max(0, $maxInsights - $this->totalInsightsUsed)
            : 0;

        // Companies at risk breakdown
        $quotaFull   = $subCompanies->filter(fn($c) => $c->clientPackage && $c->clientPackage->max_documents > 0 && $c->insights_used >= $c->clientPackage->max_documents)->count();
        $inactive    = $subCompanies->where('status', 'inactive')->count();
        $noQB        = $subCompanies->filter(fn($c) => $c->quickbooksConnections->where('is_active', true)->isEmpty())->count();
        $this->companiesAtRisk      = $subCompanies->filter(fn($c) =>
            $c->status === 'inactive' ||
            $c->quickbooksConnections->where('is_active', true)->isEmpty() ||
            ($c->clientPackage && $c->clientPackage->max_documents > 0 && $c->insights_used >= $c->clientPackage->max_documents)
        )->count();
        $this->atRiskContext   = collect([
            $quotaFull ? $quotaFull . ' quota full' : null,
            $inactive  ? $inactive . ' inactive' : null,
            $noQB      ? $noQB . ' no QB' : null,
        ])->filter()->implode(' · ');
        $this->uniqueClients   = $subCompanies->map(fn($c) => $c->users->first()?->id)->filter()->unique()->count();

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

    public function cancelSubscription(Request $request)
    {
        abort_403(!auth()->user()->can('view_billing'));

        $company = company();
        $sub     = Subscription::where('company_id', $company->id)
            ->whereNotIn('status', ['canceled'])
            ->latest()
            ->first();

        if (!$sub) {
            return Reply::error('No active subscription found.');
        }

        if (!$sub->stripe_subscription_id) {
            // No Stripe subscription linked — just mark canceled locally
            $sub->update([
                'status'      => 'canceled',
                'canceled_at' => now(),
            ]);
            return Reply::success('Subscription canceled.');
        }

        try {
            // StripeService constructor sets the API key from SuperAdmin's
            // GlobalPaymentGatewayCredentials — always used regardless of who
            // triggers the cancellation (client_admin, superadmin, etc.)
            new StripeService();

            // Cancel at period end — user keeps access until billing cycle ends
            StripeSubscription::update($sub->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            $sub->update([
                'status'      => 'canceled',
                'canceled_at' => now(),
            ]);

            Log::channel('stripe')->info('Subscription canceled at period end by user', [
                'company_id'    => $company->id,
                'stripe_sub_id' => $sub->stripe_subscription_id,
            ]);

            return Reply::success('Your subscription has been canceled. You will retain access until the end of the current billing period.');
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to cancel subscription', [
                'company_id' => $company->id,
                'error'      => $e->getMessage(),
            ]);
            return Reply::error('Could not cancel subscription: ' . $e->getMessage());
        }
    }
}
