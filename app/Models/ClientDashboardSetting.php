<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDashboardSetting extends Model
{
    protected $fillable = [
        'company_id',
        'hidden_kpis',
        'draft_hidden_kpis',
        'publish_status',
        'kpi_synopsis_overrides',
    ];

    protected $casts = [
        'hidden_kpis'             => 'array',
        'draft_hidden_kpis'       => 'array',
        'kpi_synopsis_overrides'  => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function forCompany(int $companyId): self
    {
        return self::firstOrCreate(
            ['company_id' => $companyId],
            ['hidden_kpis' => [], 'draft_hidden_kpis' => [], 'publish_status' => 'published']
        );
    }

    public function getEffectiveHiddenKpis(): array
    {
        return $this->hidden_kpis ?? [];
    }

    public function isDraft(): bool
    {
        return $this->publish_status === 'draft';
    }
}
