<?php

namespace App\Services;

class StripeDashboardService
{
    private string $invoiceBaseUrl;

    public function __construct()
    {
        $this->invoiceBaseUrl = config('services.stripe.dashboard_url');
    }

    public function invoiceUrl(string $stripeInvoiceId): string
    {
        return rtrim($this->invoiceBaseUrl, '/') . '/' . $stripeInvoiceId;
    }
}
