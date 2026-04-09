<?php

namespace App\Models;

class CompanyPaymentGateway extends BaseModel
{
    protected $table = 'company_payment_gateways';

    protected $fillable = [
        'company_id', 'gateway', 'mode', 'status',
        'live_key', 'live_secret', 'live_webhook_secret',
        'test_key', 'test_secret', 'test_webhook_secret',
    ];

    protected $hidden = [
        'live_secret', 'live_webhook_secret',
        'test_secret', 'test_webhook_secret',
    ];

    public function getActiveKey(): ?string
    {
        return $this->mode === 'live' ? $this->live_key : $this->test_key;
    }

    public function getActiveSecret(): ?string
    {
        return $this->mode === 'live' ? $this->live_secret : $this->test_secret;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && (bool) $this->getActiveKey();
    }
}
