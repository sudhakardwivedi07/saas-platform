<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Payment;
use App\Models\User;
use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use App\Models\SuperAdmin\Package;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use App\Models\SuperAdmin\Subscription;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeCheckout;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;

class StripeService
{
    private GlobalPaymentGatewayCredentials $credentials;

    public function __construct()
    {
        $this->credentials = GlobalPaymentGatewayCredentials::firstOrFail();
        $secret = $this->credentials->getActiveSecret();
        if (!$secret) {
            throw new \RuntimeException('Stripe secret key is not configured. Please set it in the SuperAdmin payment settings.');
        }
        Stripe::setApiKey($secret);
    }

    // ── Customer ──────────────────────────────────────────────────────────────

    public function createOrRetrieveCustomer(Company $company): Customer
    {
        if ($company->stripe_customer_id) {
            return Customer::retrieve($company->stripe_customer_id);
        }

        $customer = Customer::create([
            'email'    => $company->company_email,
            'name'     => $company->company_name,
            'metadata' => ['company_id' => $company->id],
        ]);

        $company->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    // ── Checkout Session ──────────────────────────────────────────────────────

    /**
     * Create a Stripe Checkout Session for a subscription.
     * Redirects user to Stripe-hosted payment page.
     */
    /**
     * @param  Company       $company
     * @param  EloquentModel $package  Accepts SuperAdmin\Package OR ClientPackage — both share the same column names.
     */
    public function createCheckoutSession(
        Company      $company,
        EloquentModel $package,
        string        $billingCycle = 'monthly',
        string  $successUrl   = '',
        string  $cancelUrl    = ''
    ): StripeCheckout {
        $customer = $this->createOrRetrieveCustomer($company);
        $priceId  = $billingCycle === 'annual'
            ? $package->stripe_annual_price_id
            : $package->stripe_monthly_price_id;

        // Build the line-item: use a pre-created Stripe Price ID when available,
        // otherwise fall back to inline price_data so checkout always works even
        // when the SuperAdmin hasn't created Stripe products/prices yet.
        if ($priceId) {
            $lineItem = ['price' => $priceId, 'quantity' => 1];
        } else {
            $unitAmount = $billingCycle === 'annual'
                ? (int) round(($package->annual_price  ?? $package->monthly_price * 12) * 100)
                : (int) round($package->monthly_price * 100);

            $lineItem = [
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => $unitAmount,
                    'recurring'    => ['interval' => $billingCycle === 'annual' ? 'year' : 'month'],
                    'product_data' => [
                        'name'        => $package->name,
                        'description' => $package->description ?? $package->name . ' subscription',
                    ],
                ],
            ];
        }

        $trialDays = ($package->is_trial_allowed && $package->trial_days > 0)
            ? (int) $package->trial_days
            : null;

        $params = [
            'customer'             => $customer->id,
            'payment_method_types' => ['card'],
            'line_items'           => [$lineItem],
            'mode'                 => 'subscription',
            'success_url'          => $successUrl ?: route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $cancelUrl  ?: route('billing.cancel'),
            'metadata'             => [
                'company_id'    => $company->id,
                'package_id'    => $package->id,
                'billing_cycle' => $billingCycle,
            ],
            'subscription_data'    => array_filter([
                'trial_period_days' => $trialDays,
                'metadata'          => ['company_id' => $company->id],
            ]),
        ];

        return StripeCheckout::create($params);
    }

    // ── Subscription lifecycle ────────────────────────────────────────────────

    public function handleInvoicePaid(array $invoice): void
    {
        $stripeSubId = $invoice['subscription'] ?? null;
        if (!$stripeSubId) return;

        $sub = Subscription::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$sub) return;

        $stripeSub = StripeSubscription::retrieve($stripeSubId);

        $sub->update([
            'status'               => 'active',
            'current_period_start' => now()->createFromTimestamp($stripeSub->current_period_start),
            'current_period_end'   => now()->createFromTimestamp($stripeSub->current_period_end),
        ]);

        Payment::create([
            'company_id'       => $sub->company_id,
            'subscription_id'  => $sub->id,
            'stripe_invoice_id' => $invoice['id'],
            'transaction_id'   => $invoice['payment_intent'],
            'amount'           => $invoice['amount_paid'] / 100,
            'currency'         => $invoice['currency'],
            'status'           => 'complete',
            'paid_at'          => now(),
        ]);

        Log::info("Stripe invoice paid for company {$sub->company_id}");
    }

    public function handleSubscriptionDeleted(array $stripeSub): void
    {
        $sub = Subscription::where('stripe_subscription_id', $stripeSub['id'])->first();
        if (!$sub) return;

        $sub->update([
            'status'      => 'canceled',
            'canceled_at' => now(),
            'ends_at'     => now()->createFromTimestamp($stripeSub['current_period_end']),
        ]);

        Log::info("Stripe subscription canceled for company {$sub->company_id}");
    }

    public function handlePaymentFailed(array $invoice): void
    {
        $stripeSubId = $invoice['subscription'] ?? null;
        if (!$stripeSubId) return;

        $sub = Subscription::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$sub) return;

        $sub->update(['status' => 'past_due']);

        Payment::create([
            'company_id'       => $sub->company_id,
            'subscription_id'  => $sub->id,
            'stripe_invoice_id' => $invoice['id'],
            'transaction_id'   => $invoice['payment_intent'],
            'amount'           => $invoice['amount_due'] / 100,
            'currency'         => $invoice['currency'],
            'status'           => 'failed',
        ]);

        Log::warning("Stripe payment failed for company {$sub->company_id}");
    }

    public function handleCheckoutCompleted(array $session): void
    {
        $companyId    = $session['metadata']['company_id'] ?? null;
        $packageId    = $session['metadata']['package_id'] ?? null;
        $billingCycle = $session['metadata']['billing_cycle'] ?? 'monthly';
        $stripeSubId  = $session['subscription'] ?? null;

        if (!$companyId || !$stripeSubId) return;

        $stripeSub = StripeSubscription::retrieve($stripeSubId);

        Subscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeSubId],
            [
                'company_id'           => $companyId,
                'package_id'           => $packageId,
                'stripe_customer_id'   => $session['customer'],
                'status'               => $stripeSub->status,
                'billing_cycle'        => $billingCycle,
                'current_period_start' => now()->createFromTimestamp($stripeSub->current_period_start),
                'current_period_end'   => now()->createFromTimestamp($stripeSub->current_period_end),
                'trial_ends_at'        => $stripeSub->trial_end
                    ? now()->createFromTimestamp($stripeSub->trial_end)
                    : null,
            ]
        );

        // Update company's current package
        Company::where('id', $companyId)->update(['package_id' => $packageId]);

        // Activate company and its admin user if they were pending payment (registration flow)
        $company = Company::withoutGlobalScopes()->find($companyId);
        if ($company && $company->status === 'inactive') {
            $company->update(['status' => 'active']);
            User::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('status', 'inactive')
                ->update(['status' => 'active', 'email_verified_at' => now()]);
            Log::info("Activated company {$companyId} after Stripe checkout.");
        }

        Log::info("Stripe checkout completed for company {$companyId}");
    }

    public function getWebhookSecret(): string
    {
        return $this->credentials->getActiveWebhookSecret();
    }
}
