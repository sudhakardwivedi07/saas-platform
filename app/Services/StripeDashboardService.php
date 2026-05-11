<?php

namespace App\Services;

class StripeDashboardService
{
    private ?string $invoiceBaseUrl;

    public function __construct()
    {
        $this->invoiceBaseUrl = config('services.stripe.dashboard_url') ?: null;
    }

    public function invoiceUrl(string $stripeInvoiceId): string
    {
        if (!$this->invoiceBaseUrl) {
            throw new \RuntimeException('STRIPE_DASHBOARD_URL is not configured in .env');
        }

        return rtrim($this->invoiceBaseUrl, '/') . '/' . $stripeInvoiceId;
    }
}
