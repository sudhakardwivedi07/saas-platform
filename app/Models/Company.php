<?php

namespace App\Models;

use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Models\ClientPackage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Company — the tenant anchor. Every piece of client data links back here.
 *
 * @property int    $id
 * @property string $company_name
 * @property string $app_name         White-label app display name
 * @property string $company_email
 * @property string $company_phone
 * @property string $subdomain        e.g. "acme" → acme.yourdomain.com
 * @property string $custom_domain    e.g. "app.acme.com"
 * @property string $status           active|inactive|suspended
 * @property int    $package_id
 * @property string $stripe_customer_id
 * @property \Carbon\Carbon $trial_ends_at
 */
class Company extends BaseModel
{
    protected $table = 'companies';

    protected $fillable = [
        'created_by',
        'company_name',
        'app_name',
        'company_email',
        'company_phone',
        'subdomain',
        'custom_domain',
        'status',
        'package_id',
        'client_package_id',
        'stripe_customer_id',
        'trial_ends_at',
        'last_active_at',
        'timezone',
        'date_format',
        'time_format',
        'locale',
    ];

    protected $casts = [
        'trial_ends_at'  => 'datetime',
        'last_active_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function clientPackage(): BelongsTo
    {
        return $this->belongsTo(ClientPackage::class);
    }

    /** Returns whichever plan this company is on (SuperAdmin or client-defined). */
    public function activePlan(): ?object
    {
        return $this->clientPackage ?? $this->package;
    }

    public function branding(): HasOne
    {
        return $this->hasOne(CompanyBranding::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function managers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function quickbooksConnections(): HasMany
    {
        return $this->hasMany(QuickBooksConnection::class);
    }

    /** The SuperAdmin who created/onboarded this firm. */
    public function onboardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Primary admin user for this company (client_admin role). */
    public function adminUser(): HasOne
    {
        return $this->hasOne(User::class)->whereHas('roles', fn ($q) => $q->where('name', 'client_admin'))->latestOfMany();
    }

    // ─── Computed Properties ───────────────────────────────────────────────────

    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->app_name ?: $this->company_name);
    }

    protected function isOnTrial(): Attribute
    {
        return Attribute::get(
            fn () => $this->trial_ends_at && $this->trial_ends_at->isFuture()
        );
    }

    protected function loginUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->custom_domain) {
                return 'https://' . $this->custom_domain . '/login';
            }
            return tenant_url($this->subdomain, 'login');
        });
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithActiveSubscription($query)
    {
        return $query->whereHas('subscription', fn ($q) => $q->where('status', 'active'));
    }
}
