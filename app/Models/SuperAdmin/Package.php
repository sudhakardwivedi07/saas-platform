<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;
use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Package — subscription plan defined by SuperAdmin.
 */
class Package extends BaseModel
{
    protected $table = 'packages';

    protected $fillable = [
        'name',
        'description',
        'monthly_price',
        'annual_price',
        'stripe_monthly_price_id',
        'stripe_annual_price_id',
        'max_users',
        'max_storage_gb',
        'features',
        'is_active',
        'is_trial_allowed',
        'trial_days',
        'is_free',
        'is_recommended',
        'sort',
    ];

    protected $casts = [
        'features'        => 'array',
        'is_active'       => 'boolean',
        'is_trial_allowed' => 'boolean',
        'is_free'         => 'boolean',
        'is_recommended'  => 'boolean',
        'monthly_price'   => 'decimal:2',
        'annual_price'    => 'decimal:2',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
