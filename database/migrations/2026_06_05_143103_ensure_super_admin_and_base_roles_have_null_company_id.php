<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ensure all platform-level (super-admin) roles exist with company_id = NULL.
 *
 * The SuperAdmin UserController filters roles with whereNull('company_id') so that
 * tenant-scoped roles are never shown in the super-admin user creation form.
 * This migration guarantees those global roles are present regardless of seeder order.
 */
return new class extends Migration
{
    /** Roles that must always exist at the platform level (company_id = NULL). */
    private array $platformRoles = [
        'super_admin',
        'admin',
        'manager',
        'client',
    ];

    public function up(): void
    {
        foreach ($this->platformRoles as $name) {
            $exists = DB::table('roles')
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->whereNull('company_id')
                ->exists();

            if (!$exists) {
                DB::table('roles')->insert([
                    'company_id'  => null,
                    'name'        => $name,
                    'guard_name'  => 'web',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Clear Spatie permission cache so the new roles are visible immediately.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Only remove roles that have no assigned users, to avoid orphaning data.
        foreach ($this->platformRoles as $name) {
            $role = DB::table('roles')
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->whereNull('company_id')
                ->first();

            if (!$role) {
                continue;
            }

            $hasUsers = DB::table('model_has_roles')->where('role_id', $role->id)->exists();
            if (!$hasUsers) {
                DB::table('role_has_permissions')->where('role_id', $role->id)->delete();
                DB::table('roles')->where('id', $role->id)->delete();
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
