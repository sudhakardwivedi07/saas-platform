<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Rename 'client_admin' role → 'admin' ────────────────────────────
        DB::table('roles')
            ->where('name', 'client_admin')
            ->where('guard_name', 'web')
            ->update(['name' => 'admin']);

        // Also update the pivot table that stores the string role value
        DB::table('user_companies')
            ->where('role', 'client_admin')
            ->update(['role' => 'admin']);

        // ── 2. Create 'client' role (copy of 'user' role) ──────────────────────
        $userRole = DB::table('roles')
            ->where('name', 'user')
            ->where('guard_name', 'web')
            ->first();

        if ($userRole) {
            $clientRoleExists = DB::table('roles')
                ->where('name', 'client')
                ->where('guard_name', 'web')
                ->exists();

            if (!$clientRoleExists) {
                $clientRoleId = DB::table('roles')->insertGetId([
                    'name'       => 'client',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Copy all permissions from 'user' role to 'client' role
                $userPermissions = DB::table('role_has_permissions')
                    ->where('role_id', $userRole->id)
                    ->pluck('permission_id');

                foreach ($userPermissions as $permId) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permId,
                        'role_id'       => $clientRoleId,
                    ]);
                }

                // ── 3. Migrate all users from 'user' role → 'client' role ──────
                DB::table('model_has_roles')
                    ->where('role_id', $userRole->id)
                    ->update(['role_id' => $clientRoleId]);
            }

            // ── 4. Delete the now-empty 'user' role ────────────────────────────
            DB::table('role_has_permissions')->where('role_id', $userRole->id)->delete();
            DB::table('model_has_roles')->where('role_id', $userRole->id)->delete();
            DB::table('roles')->where('id', $userRole->id)->delete();
        }

        // Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // ── Reverse: rename 'admin' back to 'client_admin' ────────────────────
        DB::table('roles')
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->update(['name' => 'client_admin']);

        DB::table('user_companies')
            ->where('role', 'admin')
            ->update(['role' => 'client_admin']);

        // ── Reverse: rename 'client' back to 'user' ───────────────────────────
        DB::table('roles')
            ->where('name', 'client')
            ->where('guard_name', 'web')
            ->update(['name' => 'user']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
