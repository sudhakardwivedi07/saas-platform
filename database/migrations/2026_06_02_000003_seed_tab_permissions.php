<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** Tab-level visibility permissions for Settings and My Account pages. */
    private array $tabPermissions = [
        // Settings page tabs
        'view_settings_payment',
        'view_settings_email',
        'view_settings_sms',
        'view_settings_quickbooks',
        'view_settings_plans',
        'view_settings_insight_limits',
        'view_settings_roles',
        // My Account page tabs
        'view_account_profile',
        'view_account_branding',
        'view_account_login_page',
        'view_account_subscription',
        'view_account_activity',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->tabPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Grant all new tab permissions to the admin role by default.
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($this->tabPermissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->tabPermissions as $perm) {
            Permission::where('name', $perm)->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
