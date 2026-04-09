<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope — automatically filters all Eloquent queries by company_id.
 *
 * Based on the reference project's CompanyScope, improved to:
 * - Use the global company() helper for resolution
 * - Skip scoping when running console commands (migrations, seeders)
 * - Skip when no company context exists (SuperAdmin panel)
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Skip during console commands (migrations, seeders)
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        // Model must declare a company() relationship via HasTenant trait
        if (!method_exists($model, 'company')) {
            return;
        }

        // Only apply when a company context exists
        $company = company();

        if ($company) {
            $builder->where($model->getTable() . '.company_id', '=', $company->id);
        }
    }
}
