<?php

namespace App\Models;

class EmailTemplate extends BaseModel
{
    protected $table = 'email_templates';

    // EmailTemplates can be global (company_id = NULL) or tenant-specific.
    // HasTenant is intentionally NOT used — resolution uses resolveFor() fallback logic.

    protected $fillable = [
        'company_id', 'name', 'slug', 'trigger_event', 'subject', 'body',
        'from_name', 'from_email', 'cc', 'bcc', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Find the best template for a tenant: tenant-specific first, then global fallback.
     */
    public static function resolveFor(string $slug, ?int $companyId = null): ?self
    {
        return self::where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            })
            ->orderByRaw('company_id IS NULL ASC') // tenant-specific wins
            ->first();
    }
}
