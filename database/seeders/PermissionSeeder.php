<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User management
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Documents
            'view_documents', 'upload_documents', 'delete_documents', 'download_documents',
            // QuickBooks
            'connect_quickbooks', 'sync_quickbooks', 'view_quickbooks',
            // Email templates
            'view_email_templates', 'edit_email_templates', 'add_email_templates',
            // Settings (page-level)
            'view_settings', 'edit_settings', 'edit_branding', 'manage_payment_gateway',
            // Settings — tab visibility (controlled by SuperAdmin per role)
            'view_settings_payment', 'view_settings_email', 'view_settings_sms',
            'view_settings_quickbooks', 'view_settings_plans', 'view_settings_insight_limits',
            'view_settings_roles',
            // Billing
            'view_billing', 'manage_billing',
            // Reports
            'view_reports',
            // Companies (multi-company management for admin)
            'add_company', 'manage_company',
            // Plans (admin's own plans for sub-companies)
            'view_plan', 'add_plan', 'edit_plan', 'delete_plan',
            // My Account — tab visibility (controlled by SuperAdmin per role)
            'view_account_profile', 'view_account_branding', 'view_account_login_page',
            'view_account_forgot_password',
            'view_account_subscription', 'view_account_activity',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles (global — company_id = NULL so they are never confused with tenant-scoped roles)
        $adminRole  = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web', 'company_id' => null]);
        $manager    = Role::firstOrCreate(['name' => 'manager',     'guard_name' => 'web', 'company_id' => null]);
        $clientRole = Role::firstOrCreate(['name' => 'client',      'guard_name' => 'web', 'company_id' => null]);
        Role::firstOrCreate(            ['name' => 'super_admin', 'guard_name' => 'web', 'company_id' => null]);

        // Admin gets all permissions
        $adminRole->syncPermissions($permissions);

        // Manager gets most permissions except billing and branding
        $manager->syncPermissions([
            'view_users', 'create_users',
            'view_documents', 'upload_documents', 'download_documents',
            'connect_quickbooks', 'sync_quickbooks', 'view_quickbooks',
            'view_email_templates',
            'view_settings',
            'view_reports',
        ]);

        // Client gets read-only access
        $clientRole->syncPermissions([
            'view_documents', 'download_documents',
            'view_quickbooks',
            'view_reports',
        ]);
    }
}
