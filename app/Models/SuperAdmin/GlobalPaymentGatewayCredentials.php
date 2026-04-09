<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;

/**
 * GlobalPaymentGatewayCredentials — SuperAdmin stores Stripe keys here.
 * Pattern directly from reference project.
 */
class GlobalPaymentGatewayCredentials extends BaseModel
{
    protected $table = 'payment_gateway_credentials';

    protected $fillable = [
        'stripe_mode',
        'live_stripe_key',
        'live_stripe_secret',
        'live_stripe_webhook_secret',
        'test_stripe_key',
        'test_stripe_secret',
        'test_stripe_webhook_secret',
        'stripe_status',
    ];

    protected $hidden = [
        'live_stripe_secret',
        'test_stripe_secret',
        'live_stripe_webhook_secret',
        'test_stripe_webhook_secret',
    ];

    public function getActiveKey(): ?string
    {
        return $this->stripe_mode === 'live'
            ? $this->live_stripe_key
            : $this->test_stripe_key;
    }

    public function getActiveSecret(): ?string
    {
        return $this->stripe_mode === 'live'
            ? $this->live_stripe_secret
            : $this->test_stripe_secret;
    }

    public function getActiveWebhookSecret(): ?string
    {
        return $this->stripe_mode === 'live'
            ? $this->live_stripe_webhook_secret
            : $this->test_stripe_webhook_secret;
    }
}
