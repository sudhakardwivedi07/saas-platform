<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;
use App\Models\ClientPackage;
use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscription — tracks Stripe subscription state per tenant.
 */
class Subscription extends BaseModel
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'company_id',
        'package_id',
        'client_package_id',        // set when the plan is a client-defined plan
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'canceled_at',
        'ends_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'trial_ends_at'        => 'datetime',
        'canceled_at'          => 'datetime',
        'ends_at'              => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function clientPackage(): BelongsTo
    {
        return $this->belongsTo(ClientPackage::class, 'client_package_id');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }
}
