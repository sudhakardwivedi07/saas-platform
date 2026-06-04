<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Custom Role model extending Spatie's Role.
 *
 * Adds an `alias` field — a human-friendly display name shown across the UI
 * instead of the technical role name (e.g. "Account Manager" instead of "manager").
 * The technical name is unchanged so all backend/permission logic remains intact.
 *
 * @property string|null $alias
 * @property-read string $display_name  Alias if set, otherwise the role name.
 */
class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     * Extend Spatie's fillable to include alias.
     */
    protected $fillable = [
        'name',
        'alias',
        'guard_name',
    ];

    /**
     * Returns the alias if one has been set, otherwise falls back to the
     * technical role name.  Use this everywhere the role is displayed in the UI.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->alias ?: $this->name;
    }
}
