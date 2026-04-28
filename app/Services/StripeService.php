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
            try {
                $customer = Customer::retrieve($company->stripe_customer_id);

                // If the customer was deleted in Stripe, treat as non-existent
                if (!empty($customer->deleted)) {
                    throw new \Exception('Customer deleted in Stripe');
                }

                return $customer;
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Customer doesn't exist in Stripe (wrong mode, deleted, etc.)
                // Clear the stale ID and fall through to create a new one
                Log::channel('stripe')->warning('[StripeService] Stale stripe_customer_id — creating new customer', [
                    'company_id'          => $company->id,
                    'stale_customer_id'   => $company->stripe_customer_id,
                    'error'               => $e->getMessage(),
                ]);
                $company->update(['stripe_customer_id' => null]);
            }
        }

        $customer = Customer::create([
            'email'    => $company->company_email,
            'name'     => $company->company_name,
            'metadata' => ['company_id' => $company->id],
        ]);

        $company->update(['stripe_customer_id' => $customer->id]);

        Log::channel('stripe')->info('[StripeService] New Stripe customer created', [
            'company_id'  => $company->id,
            'customer_id' => $customer->id,
        ]);

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

        Log::channel('stripe')->info('[StripeService] handleInvoicePaid called', [
            'invoice_id'   => $invoice['id'] ?? 'n/a',
            'stripe_sub_id'=> $stripeSubId,
            'amount_paid'  => ($invoice['amount_paid'] ?? 0) / 100,
        ]);

        if (!$stripeSubId) {
            Log::channel('stripe')->warning('[StripeService] handleInvoicePaid — no subscription ID, skipping');
            return;
        }

        $sub = Subscription::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$sub) {
            Log::channel('stripe')->warning('[StripeService] handleInvoicePaid — subscription not found in DB', [
                'stripe_sub_id' => $stripeSubId,
            ]);
            return;
        }

        $stripeSub = StripeSubscription::retrieve($stripeSubId);

        $sub->update([
            'status'               => 'active',
            'current_period_start' => now()->createFromTimestamp($stripeSub->current_period_start),
            'current_period_end'   => now()->createFromTimestamp($stripeSub->current_period_end),
        ]);

        $payment = Payment::create([
            'company_id'        => $sub->company_id,
            'subscription_id'   => $sub->id,
            'stripe_invoice_id' => $invoice['id'],
            'transaction_id'    => $invoice['payment_intent'],
            'amount'            => $invoice['amount_paid'] / 100,
            'currency'          => $invoice['currency'],
            'status'            => 'complete',
            'paid_at'           => now(),
        ]);

        Log::channel('stripe')->info('[StripeService] handleInvoicePaid — payment record created', [
            'payment_id'    => $payment->id,
            'company_id'    => $sub->company_id,
            'amount'        => $payment->amount,
            'stripe_sub_id' => $stripeSubId,
        ]);
    }

    public function handleSubscriptionDeleted(array $stripeSub): void
    {
        Log::channel('stripe')->info('[StripeService] handleSubscriptionDeleted called', [
            'stripe_sub_id' => $stripeSub['id'] ?? 'n/a',
            'status'        => $stripeSub['status'] ?? 'n/a',
        ]);

        $sub = Subscription::where('stripe_subscription_id', $stripeSub['id'])->first();
        if (!$sub) {
            Log::channel('stripe')->warning('[StripeService] handleSubscriptionDeleted — subscription not found in DB', [
                'stripe_sub_id' => $stripeSub['id'] ?? 'n/a',
            ]);
            return;
        }

        $sub->update([
            'status'      => 'canceled',
            'canceled_at' => now(),
            'ends_at'     => now()->createFromTimestamp($stripeSub['current_period_end']),
        ]);

        Log::channel('stripe')->info('[StripeService] handleSubscriptionDeleted — subscription marked canceled', [
            'company_id'    => $sub->company_id,
            'stripe_sub_id' => $stripeSub['id'],
        ]);
    }

    public function handlePaymentFailed(array $invoice): void
    {
        $stripeSubId = $invoice['subscription'] ?? null;

        Log::channel('stripe')->warning('[StripeService] handlePaymentFailed called', [
            'invoice_id'    => $invoice['id'] ?? 'n/a',
            'stripe_sub_id' => $stripeSubId,
            'amount_due'    => ($invoice['amount_due'] ?? 0) / 100,
        ]);

        if (!$stripeSubId) {
            Log::channel('stripe')->warning('[StripeService] handlePaymentFailed — no subscription ID, skipping');
            return;
        }

        $sub = Subscription::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$sub) {
            Log::channel('stripe')->warning('[StripeService] handlePaymentFailed — subscription not found in DB', [
                'stripe_sub_id' => $stripeSubId,
            ]);
            return;
        }

        $sub->update(['status' => 'past_due']);

        $payment = Payment::create([
            'company_id'        => $sub->company_id,
            'subscription_id'   => $sub->id,
            'stripe_invoice_id' => $invoice['id'],
            'transaction_id'    => $invoice['payment_intent'],
            'amount'            => $invoice['amount_due'] / 100,
            'currency'          => $invoice['currency'],
            'status'            => 'failed',
        ]);

        Log::channel('stripe')->warning('[StripeService] handlePaymentFailed — failed payment record created', [
            'payment_id' => $payment->id,
            'company_id' => $sub->company_id,
            'amount'     => $payment->amount,
        ]);
    }

    public function handleCheckoutCompleted(array $session): void
    {
        $companyId    = $session['metadata']['company_id'] ?? null;
        $packageId    = $session['metadata']['package_id'] ?? null;
        $billingCycle = $session['metadata']['billing_cycle'] ?? 'monthly';
        $stripeSubId  = $session['subscription'] ?? null;

        Log::channel('stripe')->info('[StripeService] handleCheckoutCompleted called', [
            'session_id'    => $session['id'] ?? 'n/a',
            'company_id'    => $companyId,
            'package_id'    => $packageId,
            'billing_cycle' => $billingCycle,
            'stripe_sub_id' => $stripeSubId,
            'amount_total'  => ($session['amount_total'] ?? 0) / 100,
        ]);

        if (!$companyId || !$stripeSubId) {
            Log::channel('stripe')->error('[StripeService] handleCheckoutCompleted — missing company_id or subscription in session metadata', [
                'company_id'    => $companyId,
                'stripe_sub_id' => $stripeSubId,
            ]);
            return;
        }

        $stripeSub = StripeSubscription::retrieve($stripeSubId);

        $sub = Subscription::updateOrCreate(
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

        Log::channel('stripe')->info('[StripeService] handleCheckoutCompleted — subscription upserted', [
            'local_sub_id'  => $sub->id,
            'stripe_sub_id' => $stripeSubId,
            'status'        => $stripeSub->status,
            'wasRecentlyCreated' => $sub->wasRecentlyCreated,
        ]);

        // Update company's current package
        Company::where('id', $companyId)->update(['package_id' => $packageId]);

        Log::channel('stripe')->info('[StripeService] handleCheckoutCompleted — company package updated', [
            'company_id' => $companyId,
            'package_id' => $packageId,
        ]);

        // Activate company and its admin user if they were pending payment (registration flow)
        $company = Company::withoutGlobalScopes()->find($companyId);
        if ($company && $company->status === 'inactive') {
            $company->update(['status' => 'active']);
            $activated = User::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('status', 'inactive')
                ->update(['status' => 'active', 'email_verified_at' => now()]);

            Log::channel('stripe')->info('[StripeService] handleCheckoutCompleted — company activated after payment', [
                'company_id'      => $companyId,
                'users_activated' => $activated,
            ]);
        }

        Log::channel('stripe')->info('[StripeService] handleCheckoutCompleted — ✓ done', [
            'company_id' => $companyId,
        ]);
    }

    public function getWebhookSecret(): string
    {
        return $this->credentials->getActiveWebhookSecret();
    }
}
