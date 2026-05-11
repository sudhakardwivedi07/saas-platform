<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Services\EmailService;
use App\Models\Company;
use App\Models\EmailSmtpSetting;
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
            ->with('package')
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

            $this->dispatchCancellationEmail($company, $sub);

            return Reply::success('Subscription canceled.');
        }

        try {
            // StripeService constructor sets the API key from SuperAdmin's
            // GlobalPaymentGatewayCredentials — always used regardless of who
            // triggers the cancellation (admin, superadmin, etc.)
            new StripeService();

            // Cancel at period end — user keeps access until billing cycle ends
            StripeSubscription::update($sub->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            $sub->update([
                'status'      => 'canceled',
                'canceled_at' => now(),
            ]);

            $this->dispatchCancellationEmail($company, $sub);

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

    public function reactivateSubscription(Request $request)
    {
        abort_403(!auth()->user()->can('view_billing'));

        $company = company();
        $sub     = Subscription::where('company_id', $company->id)
            ->where('status', 'canceled')
            ->with('package')
            ->latest()
            ->first();

        if (!$sub) {
            return Reply::error('No canceled subscription found.');
        }

        if ($sub->ends_at && $sub->ends_at->isPast()) {
            return Reply::error('Your access period has ended. Please subscribe to a new plan.');
        }

        if (!$sub->stripe_subscription_id) {
            $sub->update(['status' => 'active', 'canceled_at' => null]);
            $this->dispatchReactivationEmail($company, $sub);
            return Reply::success('Subscription reactivated successfully.');
        }

        try {
            new StripeService();

            StripeSubscription::update($sub->stripe_subscription_id, [
                'cancel_at_period_end' => false,
            ]);

            $sub->update(['status' => 'active', 'canceled_at' => null]);

            $this->dispatchReactivationEmail($company, $sub);

            Log::channel('stripe')->info('Subscription reactivated by user', [
                'company_id'    => $company->id,
                'stripe_sub_id' => $sub->stripe_subscription_id,
            ]);

            return Reply::success('Your subscription has been reactivated. Welcome back!');
        } catch (\Exception $e) {
            Log::channel('stripe')->error('Failed to reactivate subscription', [
                'company_id' => $company->id,
                'error'      => $e->getMessage(),
            ]);
            return Reply::error('Could not reactivate subscription: ' . $e->getMessage());
        }
    }

    private function dispatchReactivationEmail(Company $company, Subscription $sub): void
    {
        if (!$company->company_email) {
            return;
        }

        $firmName     = $company->company_name ?? $company->app_name;
        $appName      = $company->app_name ?? $company->company_name;
        $periodEnd    = $sub->current_period_end?->format('M d, Y') ?? now()->addMonth()->format('M d, Y');
        $globalSmtp   = EmailSmtpSetting::whereNull('company_id')->where('is_active', true)->first();
        $supportEmail = $globalSmtp?->from_email ?? config('mail.from.address');

        app(EmailService::class)->send(
            toEmail:      $company->company_email,
            templateSlug: 'subscription_reactivated',
            placeholders: [
                '{{first_name}}'              => $firmName,
                '{{name}}'                    => $firmName,
                '{{firm_name}}'               => $firmName,
                '{{app_name}}'                => $appName,
                '{{plan_name}}'               => $sub->package?->name ?? '',
                '{{subscription_start_date}}' => now()->format('M d, Y'),
                '{{subscription_end_date}}'   => $periodEnd,
                '{{next_billing_date}}'       => $periodEnd,
                '{{portal_url}}'              => tenant_url($company->subdomain, 'login'),
                '{{login_url}}'               => tenant_url($company->subdomain, 'login'),
                '{{invoice_number}}'          => 'N/A',
                '{{support_email}}'           => $supportEmail,
            ],
            companyId: $company->id
        );
    }

    private function dispatchCancellationEmail(Company $company, Subscription $sub): void
    {
        if (!$company->company_email) {
            return;
        }

        $firmName     = $company->company_name ?? $company->app_name;
        $appName      = $company->app_name ?? $company->company_name;
        $endDate      = $sub->ends_at ?? $sub->current_period_end;
        $globalSmtp   = EmailSmtpSetting::whereNull('company_id')->where('is_active', true)->first();
        $supportEmail = $globalSmtp?->from_email ?? config('mail.from.address');

        app(EmailService::class)->send(
            toEmail:      $company->company_email,
            templateSlug: 'subscription_cancellation',
            placeholders: [
                '{{first_name}}'            => $firmName,
                '{{name}}'                  => $firmName,
                '{{firm_name}}'             => $firmName,
                '{{app_name}}'              => $appName,
                '{{plan_name}}'             => $sub->package?->name ?? '',
                '{{canceled_at}}'           => now()->format('M d, Y'),
                '{{subscription_end_date}}' => $endDate ? $endDate->format('M d, Y') : 'end of billing period',
                '{{login_url}}'             => tenant_url($company->subdomain, 'login'),
                '{{reactivation_url}}'      => tenant_url($company->subdomain, 'billing'),
                '{{support_email}}'         => $supportEmail,
            ],
            companyId: $company->id
        );
    }
}
