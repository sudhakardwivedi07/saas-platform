<?php

namespace App\Models;

class SmsSetting extends BaseModel
{
    protected $table    = 'sms_settings';
    protected $fillable = ['company_id', 'provider', 'account_sid', 'auth_token', 'from_number', 'is_active'];
    protected $hidden   = ['account_sid', 'auth_token'];
    protected $casts    = ['is_active' => 'boolean'];

    public static function resolveFor(?int $companyId): ?self
    {
        return self::where(function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhereNull('company_id');
        })
        ->where('is_active', true)
        ->orderByRaw('company_id IS NULL ASC')
        ->first();
    }
}
