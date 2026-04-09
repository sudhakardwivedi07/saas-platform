<?php

namespace App\Traits;

use App\Models\Company;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * HasTenant trait — apply to every model that belongs to a tenant.
 *
 * Usage:
 *   class Document extends Model {
 *       use HasTenant;
 *   }
 *
 * This automatically:
 * 1. Adds TenantScope to all queries (filters by company_id)
 * 2. Auto-fills company_id on create from the current company context
 * 3. Adds company() relationship
 */
trait HasTenant
{
    public static function bootHasTenant(): void
    {
        // Auto-apply tenant scope on all queries
        static::addGlobalScope(new TenantScope());

        // Auto-fill company_id when creating if not set
        static::creating(function (self $model) {
            if (empty($model->company_id)) {
                $company = company();
                if ($company) {
                    $model->company_id = $company->id;
                }
            }
        });
    }

    /**
     * Get the company this record belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
