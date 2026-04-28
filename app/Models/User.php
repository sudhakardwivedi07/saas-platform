<?php

namespace App\Models;

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
