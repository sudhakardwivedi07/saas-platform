<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            // Settings
            'view_settings', 'edit_settings', 'edit_branding', 'manage_payment_gateway',
            // Billing
            'view_billing', 'manage_billing',
            // Reports
            'view_reports',
            // Companies (multi-company management for admin)
            'add_company', 'manage_company',
            // Plans (admin's own plans for sub-companies)
            'view_plan', 'add_plan', 'edit_plan', 'delete_plan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles (global — no company_id)
        $adminRole  = Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        $manager    = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $clientRole = Role::firstOrCreate(['name' => 'client',  'guard_name' => 'web']);

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
