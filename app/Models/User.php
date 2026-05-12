<?php

namespace App\Models;

use App\Models\Company;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Scopes\TenantScope;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User model.
 *
 * @property int    $id
 * @property int    $company_id         NULL for SuperAdmins
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $mobile
 * @property string $image
 * @property string $status             active|inactive
 * @property bool   $is_superadmin
 * @property bool   $admin_approval
 * @property string $locale
 * @property bool   $dark_theme
 * @property string $two_factor_code
 * @property \Carbon\Carbon $two_factor_expires_at
 * @property \Carbon\Carbon $last_login
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    // NOTE: User does NOT use HasTenant because SuperAdmins have company_id = NULL.
    // We apply TenantScope manually via the scopeTenant() method for queries that need it.

    protected $table = 'users';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'mobile',
        'image',
        'status',
        'is_superadmin',
        'admin_approval',
        'locale',
        'dark_theme',
        'email_verified_at',
        'two_factor_code',
        'two_factor_expires_at',
        'last_login',
        'business_context',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'last_login'           => 'datetime',
        'is_superadmin'        => 'boolean',
        'admin_approval'       => 'boolean',
        'dark_theme'           => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function quickbooksConnections(): HasMany
    {
        return $this->hasMany(QuickBooksConnection::class);
    }

    public function managedCompanies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_companies')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Same company list as My Companies (owned + linked + primary when in ownership chain).
     * Keeps Insights / KPI company pickers aligned with my-companies.
     */
    public function portfolioCompanies(): \Illuminate\Support\Collection
    {
        $authUser = $this;
        $ownerKey = $authUser->hasAnyRole(['admin', 'manager'])
            ? (int) $authUser->company_id
            : (int) $authUser->id;

        $ownedCompanies = Company::withoutGlobalScopes()
            ->where(function ($query) use ($ownerKey, $authUser) {
                $query->where('created_by', $ownerKey)
                    ->orWhere('created_by', $authUser->id);
            })
            ->get();

        $linkedCompanies = $authUser->managedCompanies()
            ->withoutGlobalScopes()
            ->get()
            ->filter(fn ($c) => ! $ownedCompanies->contains(fn ($row) => (int) $row->id === (int) $c->id));

        $companies = $ownedCompanies->merge($linkedCompanies);

        $primaryCompany = $authUser->company;
        if ($primaryCompany && ! $companies->contains(fn ($row) => (int) $row->id === (int) $primaryCompany->id)) {
            $companies->prepend($primaryCompany);
        }

        return $companies->unique('id')->values();
    }

    public function companyHasFinancialKpiSource(int $companyId): bool
    {
        if (QuickBooksConnection::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists()) {
            return true;
        }

        if (Report::query()
            ->where('user_id', $this->id)
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
            ->exists()) {
            return true;
        }

        // Legacy reports with no company_id: treat as belonging to the user's home tenant.
        if ($this->company_id && (int) $companyId === (int) $this->company_id) {
            return Report::query()
                ->where('user_id', $this->id)
                ->whereNull('company_id')
                ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
                ->exists();
        }

        return false;
    }

    /**
     * True when this user has at least one non-deleted Analyzed report for the tenant company
     * (including legacy null company_id rows only when $tenantCompanyId is the user’s home company).
     */
    public function hasAnalyzedInsightsForTenant(int $tenantCompanyId): bool
    {
        return Report::query()
            ->where('user_id', $this->id)
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
            ->where(function ($q) use ($tenantCompanyId) {
                $q->where('company_id', $tenantCompanyId);
                if ($this->company_id && (int) $tenantCompanyId === (int) $this->company_id) {
                    $q->orWhereNull('company_id');
                }
            })
            ->exists();
    }

    /**
     * Companies that should appear on the KPI dashboard (QB and/or analyzed insights for this user).
     */
    public function kpiEligiblePortfolioCompanies(): \Illuminate\Support\Collection
    {
        $portfolio = $this->portfolioCompanies();
        $withSource = $portfolio->filter(fn ($c) => $this->companyHasFinancialKpiSource((int) $c->id));

        if ($withSource->isNotEmpty()) {
            return $withSource->values();
        }

        $hasLooseAnalyzed = Report::query()
            ->where('user_id', $this->id)
            ->whereNull('company_id')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
            ->exists();

        if ($hasLooseAnalyzed && $this->company_id) {
            $home = Company::withoutGlobalScopes()->find($this->company_id);
            if ($home) {
                return collect([$home]);
            }
        }

        return collect();
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Scope to current tenant. Use explicitly when needed.
     * SuperAdmin queries should NOT use this scope.
     */
    public function scopeForTenant($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image
            ? asset('storage/' . $this->image)
            : asset('images/default-avatar.png')
        );
    }

    protected function firstName(): Attribute
    {
        return Attribute::get(fn () => explode(' ', trim($this->name))[0]);
    }

    // ─── Business Logic ────────────────────────────────────────────────────────

    /**
     * Check if 2FA code is valid.
     */
    public function isValidTwoFactorCode(string $code): bool
    {
        return $this->two_factor_code === $code
            && $this->two_factor_expires_at
            && $this->two_factor_expires_at->isFuture();
    }

    /**
     * Generate and store a 6-digit 2FA code.
     */
    public function generateTwoFactorCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->forceFill([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();
        return $code;
    }

    /**
     * Check a named permission string (mirrors reference project pattern).
     * Returns: 'all' | 'none'
     */
    public function hasPermissionLevel(string $permission): string
    {
        return $this->can($permission) ? 'all' : 'none';
    }
}
