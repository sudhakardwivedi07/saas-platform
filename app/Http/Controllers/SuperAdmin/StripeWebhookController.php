<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $stripeService = new StripeService();

        $payload   = $request->getContent();
        $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');

        Log::channel('stripe')->info('Stripe webhook received', [
            'type'      => $request->input('type'),
            'timestamp' => now()->toIso8601String(),
        ]);

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $stripeService->getWebhookSecret()
            );
        } catch (\Exception $e) {
            Log::error('Invalid Stripe signature: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        $object = $event->data->object->toArray();

        match ($event->type) {
            'checkout.session.completed'    => $this->onCheckoutCompleted($stripeService, $object),
            'invoice.payment_succeeded'     => $this->onInvoicePaid($stripeService, $object),
            'invoice.payment_failed'        => $this->onPaymentFailed($stripeService, $object),
            'customer.subscription.deleted' => $this->onSubscriptionDeleted($stripeService, $object),
            default => Log::info("Unhandled Stripe event: {$event->type}"),
        };

        return response('OK', 200);
    }

    private function onCheckoutCompleted(StripeService $stripeService, array $session): void
    {
        $stripeService->handleCheckoutCompleted($session);

        $companyId = $session['metadata']['company_id'] ?? null;
        if (!$companyId) return;

        $company = Company::withoutGlobalScopes()->find($companyId);
        $admin   = $company?->users()->first();
        if (!$admin) return;

        SendEmailJob::dispatch(
            toEmail:      $admin->email,
            templateSlug: 'payment_success',
            placeholders: [
                '{{name}}'       => $admin->name,
                '{{app_name}}'   => $company->app_name ?? $company->company_name,
                '{{amount}}'     => '$' . number_format(($session['amount_total'] ?? 0) / 100, 2),
                '{{plan_name}}'  => Package::find($session['metadata']['package_id'] ?? null)?->name ?? '',
                '{{period_end}}' => now()->addMonth()->format('Y-m-d'),
                '{{login_url}}'  => tenant_url($company->subdomain, 'login'),
            ],
            companyId: (int) $companyId
        )->onQueue('emails');
    }

    private function onInvoicePaid(StripeService $stripeService, array $invoice): void
    {
        $stripeService->handleInvoicePaid($invoice);
    }

    private function onPaymentFailed(StripeService $stripeService, array $invoice): void
    {
        $stripeService->handlePaymentFailed($invoice);

        $sub = Subscription::withoutGlobalScopes()
            ->where('stripe_subscription_id', $invoice['subscription'] ?? '')
            ->with('package')
            ->first();

        if (!$sub) return;

        $company = Company::withoutGlobalScopes()->find($sub->company_id);
        $admin   = $company?->users()->first();

        if ($admin) {
            SendEmailJob::dispatch(
                toEmail:      $admin->email,
                templateSlug: 'payment_failed',
                placeholders: [
                    '{{name}}'        => $admin->name,
                    '{{app_name}}'    => $company->app_name ?? $company->company_name,
                    '{{plan_name}}'   => $sub->package?->name ?? '',
                    '{{billing_url}}' => tenant_url($company->subdomain, 'billing'),
                ],
                companyId: $sub->company_id
            )->onQueue('emails');
        }
    }

    private function onSubscriptionDeleted(StripeService $stripeService, array $stripeSub): void
    {
        $stripeService->handleSubscriptionDeleted($stripeSub);
    }
}
