<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\Company;
use App\Models\EmailSmtpSetting;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    private function slog(string $level, string $message, array $context = []): void
    {
        Log::channel('stripe')->{$level}($message, $context);
    }

    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');
        $eventType = $request->input('type', 'unknown');

        // ── 1. Log the raw incoming hit ───────────────────────────────────────
        $this->slog('info', '⬇ Webhook received', [
            'event_type'       => $eventType,
            'has_signature'    => !empty($sigHeader),
            'payload_bytes'    => strlen($payload),
            'ip'               => $request->ip(),
            'timestamp'        => now()->toIso8601String(),
        ]);

        // ── 2. Verify Stripe signature ────────────────────────────────────────
        try {
            $stripeService = new StripeService();
        } catch (\Exception $e) {
            $this->slog('critical', '✗ StripeService init failed — secret key not configured', [
                'error' => $e->getMessage(),
            ]);
            return response('Configuration error', 500);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $stripeService->getWebhookSecret()
            );
            $this->slog('info', '✓ Signature verified', ['event_id' => $event->id]);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->slog('error', '✗ Invalid Stripe signature — request rejected', [
                'error'            => $e->getMessage(),
                'signature_header' => substr((string) $sigHeader, 0, 60) . '…',
            ]);
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            $this->slog('error', '✗ Webhook construction failed', [
                'error' => $e->getMessage(),
            ]);
            return response('Webhook error', 400);
        }

        // ── 3. Dispatch to the correct handler ───────────────────────────────
        $object = $event->data->object->toArray();

        $this->slog('info', "▶ Handling event [{$event->type}]", [
            'event_id'   => $event->id,
            'event_type' => $event->type,
            'object_id'  => $object['id'] ?? 'n/a',
        ]);

        try {
            match ($event->type) {
                'checkout.session.completed'    => $this->onCheckoutCompleted($stripeService, $object, $event->id),
                'invoice.payment_succeeded'     => $this->onInvoicePaid($stripeService, $object, $event->id),
                'invoice.payment_failed'        => $this->onPaymentFailed($stripeService, $object, $event->id),
                'customer.subscription.deleted' => $this->onSubscriptionDeleted($stripeService, $object, $event->id),
                default => $this->slog('info', "→ Unhandled event [{$event->type}] — ignored", ['event_id' => $event->id]),
            };
        } catch (\Exception $e) {
            $this->slog('error', "✗ Handler threw exception for [{$event->type}]", [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
                'trace'    => substr($e->getTraceAsString(), 0, 800),
            ]);
        }

        $this->slog('info', "✓ Webhook [{$event->type}] processed — returning 200", ['event_id' => $event->id]);
        return response('OK', 200);
    }

    // ── Handlers ─────────────────────────────────────────────────────────────

    private function onCheckoutCompleted(StripeService $stripeService, array $session, string $eventId): void
    {
        $companyId = $session['metadata']['company_id'] ?? null;
        $packageId = $session['metadata']['package_id'] ?? null;

        $this->slog('info', 'checkout.session.completed — starting', [
            'event_id'     => $eventId,
            'session_id'   => $session['id'] ?? 'n/a',
            'company_id'   => $companyId,
            'package_id'   => $packageId,
            'subscription' => $session['subscription'] ?? 'n/a',
            'amount_total' => ($session['amount_total'] ?? 0) / 100,
            'currency'     => $session['currency'] ?? 'usd',
        ]);

        // Snapshot existing active subscription BEFORE handleCheckoutCompleted overwrites it
        $previousSub = $companyId
            ? Subscription::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->whereIn('status', ['active', 'trialing'])
                ->with('package')
                ->latest()
                ->first()
            : null;

        $stripeService->handleCheckoutCompleted($session);

        $this->slog('info', 'checkout.session.completed — StripeService handler done', [
            'event_id' => $eventId,
        ]);

        if (!$companyId) {
            $this->slog('warning', 'checkout.session.completed — no company_id in metadata, skipping email', [
                'event_id' => $eventId,
            ]);
            return;
        }

        $company = Company::withoutGlobalScopes()->find($companyId);
        if (!$company) {
            $this->slog('warning', 'checkout.session.completed — company not found in DB', [
                'event_id'   => $eventId,
                'company_id' => $companyId,
            ]);
            return;
        }

        if (!$company->company_email) {
            $this->slog('warning', 'checkout.session.completed — no company_email found, skipping email', [
                'event_id'   => $eventId,
                'company_id' => $companyId,
            ]);
            return;
        }

        $newPackage   = Package::find($packageId);
        $firmName     = $company->company_name ?? $company->app_name;
        $appName      = $company->app_name ?? $company->company_name;
        $globalSmtp   = EmailSmtpSetting::whereNull('company_id')->where('is_active', true)->first();
        $supportEmail = $globalSmtp?->from_email ?? config('mail.from.address');

        // Detect plan change: existing active sub switching to a different plan
        $isPlanChange = $previousSub
            && $newPackage
            && (int) $packageId !== (int) $previousSub->package_id;

        $isUpgrade   = $isPlanChange && ($newPackage->monthly_price ?? 0) > ($previousSub->package?->monthly_price ?? 0);
        $isDowngrade = $isPlanChange && ($newPackage->monthly_price ?? 0) < ($previousSub->package?->monthly_price ?? 0);

        if ($isUpgrade) {
            $oldPlanName   = $previousSub->package?->name ?? 'previous plan';
            $newPlanName   = $newPackage->name;
            $effectiveDate = $previousSub->current_period_end
                ? $previousSub->current_period_end->format('M d, Y')
                : now()->addMonth()->format('M d, Y');

            $this->slog('info', 'checkout.session.completed — detected plan upgrade, dispatching plan_upgraded email', [
                'event_id'       => $eventId,
                'company_id'     => $companyId,
                'old_plan'       => $oldPlanName,
                'new_plan'       => $newPlanName,
                'effective_date' => $effectiveDate,
            ]);

            SendEmailJob::dispatch(
                toEmail:      $company->company_email,
                templateSlug: 'plan_upgraded',
                placeholders: [
                    '{{name}}'                  => $firmName,
                    '{{firm_name}}'             => $firmName,
                    '{{app_name}}'              => $appName,
                    '{{old_plan_name}}'         => $oldPlanName,
                    '{{new_plan_name}}'         => $newPlanName,
                    '{{current_plan_end_date}}' => $effectiveDate,
                    '{{login_url}}'             => tenant_url($company->subdomain, 'login'),
                    '{{support_email}}'         => $supportEmail,
                ],
                companyId: (int) $companyId
            )->onQueue('emails');

        } elseif ($isDowngrade) {
            $oldPlanName   = $previousSub->package?->name ?? 'previous plan';
            $newPlanName   = $newPackage->name;
            $effectiveDate = $previousSub->current_period_end
                ? $previousSub->current_period_end->format('M d, Y')
                : now()->addMonth()->format('M d, Y');
            $newMaxClients = $newPackage->max_users    ? number_format($newPackage->max_users)    . ' clients'           : 'Unlimited clients';
            $newMaxDocs    = $newPackage->max_documents ? number_format($newPackage->max_documents) . ' insights / month' : 'Unlimited insights';

            $this->slog('info', 'checkout.session.completed — detected plan downgrade, dispatching plan_downgraded email', [
                'event_id'       => $eventId,
                'company_id'     => $companyId,
                'old_plan'       => $oldPlanName,
                'new_plan'       => $newPlanName,
                'effective_date' => $effectiveDate,
            ]);

            SendEmailJob::dispatch(
                toEmail:      $company->company_email,
                templateSlug: 'plan_downgraded',
                placeholders: [
                    '{{name}}'                  => $firmName,
                    '{{firm_name}}'             => $firmName,
                    '{{app_name}}'              => $appName,
                    '{{old_plan_name}}'         => $oldPlanName,
                    '{{new_plan_name}}'         => $newPlanName,
                    '{{current_plan_end_date}}' => $effectiveDate,
                    '{{new_plan_clients}}'      => $newMaxClients,
                    '{{new_plan_insights}}'     => $newMaxDocs,
                    '{{login_url}}'             => tenant_url($company->subdomain, 'login'),
                    '{{support_email}}'         => $supportEmail,
                ],
                companyId: (int) $companyId
            )->onQueue('emails');

        } else {
            // Fresh signup or reactivation via new checkout
            $planName    = $newPackage?->name ?? 'Unknown';
            $amountPaid  = '$' . number_format(($session['amount_total'] ?? 0) / 100, 2);
            $invoiceId   = $session['payment_intent'] ?? $session['id'] ?? 'N/A';
            $startDate   = now()->format('M d, Y');
            $renewDate   = now()->addMonth()->format('M d, Y');

            $this->slog('info', 'checkout.session.completed — dispatching payment_successful_subscription_activated email', [
                'event_id'   => $eventId,
                'company_id' => $companyId,
                'plan'       => $planName,
            ]);

            SendEmailJob::dispatch(
                toEmail:      $company->company_email,
                templateSlug: 'payment_successful_subscription_activated',
                placeholders: [
                    '{{name}}'               => $firmName,
                    '{{firm_name}}'          => $firmName,
                    '{{app_name}}'           => $appName,
                    '{{plan_name}}'          => $planName,
                    '{{amount}}'             => $amountPaid,
                    '{{invoice_id}}'         => $invoiceId,
                    '{{subscription_start}}' => $startDate,
                    '{{period_end}}'         => $renewDate,
                    '{{login_url}}'          => tenant_url($company->subdomain, 'login'),
                    '{{support_email}}'      => $supportEmail,
                ],
                companyId: (int) $companyId
            )->onQueue('emails');
        }

        $this->slog('info', 'checkout.session.completed — completed successfully', [
            'event_id'   => $eventId,
            'company_id' => $companyId,
        ]);
    }

    private function onInvoicePaid(StripeService $stripeService, array $invoice, string $eventId): void
    {
        $this->slog('info', 'invoice.payment_succeeded — starting', [
            'event_id'       => $eventId,
            'invoice_id'     => $invoice['id']           ?? 'n/a',
            'subscription'   => $invoice['subscription'] ?? 'n/a',
            'amount_paid'    => ($invoice['amount_paid'] ?? 0) / 100,
            'currency'       => $invoice['currency']     ?? 'usd',
            'customer'       => $invoice['customer']     ?? 'n/a',
        ]);

        $stripeService->handleInvoicePaid($invoice);

        $this->slog('info', 'invoice.payment_succeeded — completed', ['event_id' => $eventId]);
    }

    private function onPaymentFailed(StripeService $stripeService, array $invoice, string $eventId): void
    {
        $this->slog('warning', 'invoice.payment_failed — starting', [
            'event_id'     => $eventId,
            'invoice_id'   => $invoice['id']             ?? 'n/a',
            'subscription' => $invoice['subscription']   ?? 'n/a',
            'amount_due'   => ($invoice['amount_due']    ?? 0) / 100,
            'currency'     => $invoice['currency']       ?? 'usd',
            'customer'     => $invoice['customer']       ?? 'n/a',
            'attempt_count'=> $invoice['attempt_count']  ?? 'n/a',
        ]);

        $stripeService->handlePaymentFailed($invoice);

        $sub = Subscription::withoutGlobalScopes()
            ->where('stripe_subscription_id', $invoice['subscription'] ?? '')
            ->with('package')
            ->first();

        if (!$sub) {
            $this->slog('warning', 'invoice.payment_failed — subscription not found in DB', [
                'event_id'       => $eventId,
                'stripe_sub_id'  => $invoice['subscription'] ?? 'n/a',
            ]);
            return;
        }

        $company = Company::withoutGlobalScopes()->find($sub->company_id);

        if ($company && $company->company_email) {
            $firmName = $company->company_name ?? $company->app_name;

            $this->slog('warning', 'invoice.payment_failed — dispatching payment_failed email', [
                'event_id'   => $eventId,
                'company_id' => $sub->company_id,
                'firm_email' => $company->company_email,
            ]);

            SendEmailJob::dispatch(
                toEmail:      $company->company_email,
                templateSlug: 'payment_failed',
                placeholders: [
                    '{{name}}'        => $firmName,
                    '{{firm_name}}'   => $firmName,
                    '{{app_name}}'    => $company->app_name ?? $company->company_name,
                    '{{plan_name}}'   => $sub->package?->name ?? '',
                    '{{billing_url}}' => tenant_url($company->subdomain, 'billing'),
                ],
                companyId: $sub->company_id
            )->onQueue('emails');
        }

        $this->slog('warning', 'invoice.payment_failed — processed', ['event_id' => $eventId]);
    }

    private function onSubscriptionDeleted(StripeService $stripeService, array $stripeSub, string $eventId): void
    {
        $this->slog('info', 'customer.subscription.deleted — starting', [
            'event_id'      => $eventId,
            'stripe_sub_id' => $stripeSub['id']      ?? 'n/a',
            'customer'      => $stripeSub['customer'] ?? 'n/a',
            'status'        => $stripeSub['status']   ?? 'n/a',
        ]);

        $stripeService->handleSubscriptionDeleted($stripeSub);

        $sub = Subscription::withoutGlobalScopes()
            ->where('stripe_subscription_id', $stripeSub['id'] ?? '')
            ->with('package')
            ->first();

        if ($sub) {
            $company = Company::withoutGlobalScopes()->find($sub->company_id);

            if ($company && $company->company_email) {
                $firmName     = $company->company_name ?? $company->app_name;
                $endDate      = $sub->ends_at ?? $sub->current_period_end;
                $globalSmtp   = EmailSmtpSetting::whereNull('company_id')->where('is_active', true)->first();
                $supportEmail = $globalSmtp?->from_email ?? config('mail.from.address');

                SendEmailJob::dispatch(
                    toEmail:      $company->company_email,
                    templateSlug: 'subscription_cancellation',
                    placeholders: [
                        '{{name}}'                  => $firmName,
                        '{{firm_name}}'             => $firmName,
                        '{{app_name}}'              => $company->app_name ?? $company->company_name,
                        '{{plan_name}}'             => $sub->package?->name ?? '',
                        '{{subscription_end_date}}' => $endDate ? $endDate->format('M d, Y') : 'end of billing period',
                        '{{canceled_at}}'           => now()->format('M d, Y'),
                        '{{login_url}}'             => tenant_url($company->subdomain, 'login'),
                        '{{support_email}}'         => $supportEmail,
                    ],
                    companyId: $sub->company_id
                )->onQueue('emails');

                $this->slog('info', 'customer.subscription.deleted — dispatched subscription_cancellation email', [
                    'event_id'   => $eventId,
                    'company_id' => $sub->company_id,
                    'firm_email' => $company->company_email,
                ]);
            }
        }

        $this->slog('info', 'customer.subscription.deleted — completed', ['event_id' => $eventId]);
    }
}
