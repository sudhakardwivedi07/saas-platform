<?php

namespace App\Models;

use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuickBooksConnection extends BaseModel
{
    use HasTenant;

    protected $table = 'quickbooks_connections';

    protected $fillable = [
        'company_id', 'user_id', 'realm_id', 'qb_company_name',
        'access_token', 'refresh_token',
        'access_token_expires_at', 'refresh_token_expires_at',
        'country', 'is_active', 'last_synced_at',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'access_token_expires_at'  => 'datetime',
        'refresh_token_expires_at' => 'datetime',
        'last_synced_at'           => 'datetime',
        'is_active'                => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function connectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(QuickBooksSyncLog::class, 'connection_id');
    }

    public function isAccessTokenExpired(): bool
    {
        return $this->access_token_expires_at->subMinutes(10)->isPast();
    }

    public function isRefreshTokenExpired(): bool
    {
        return $this->refresh_token_expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
