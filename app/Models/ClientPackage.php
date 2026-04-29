<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ClientPackage — subscription plan defined by a client_admin for their sub-companies.
 * Mirrors the SuperAdmin Package model but scoped to a company_id.
 *
 * Properties match SuperAdmin\Package column-for-column so either model can be passed
 * to StripeService::createCheckoutSession() transparently.
 */
class ClientPackage extends BaseModel
{
    protected $table = 'client_packages';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'monthly_price',
        'annual_price',
        'insights_per_month',
        'max_companies',
        'features',
        'is_active',
        'is_trial_allowed',
        'trial_days',
        'is_free',
        'is_recommended',
        'sort',
    ];

    protected $casts = [
        'features'         => 'array',
        'is_active'        => 'boolean',
        'is_trial_allowed' => 'boolean',
        'is_free'          => 'boolean',
        'is_recommended'   => 'boolean',
        'monthly_price'    => 'decimal:2',
        'annual_price'     => 'decimal:2',
        'insights_per_month' => 'integer',
        'max_companies'      => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope to the calling company only. */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
