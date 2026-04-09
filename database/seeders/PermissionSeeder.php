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
            // Companies (multi-company management for client_admin)
            'add_company', 'manage_company',
            // Plans (client_admin's own plans for sub-companies)
            'view_plan', 'add_plan', 'edit_plan', 'delete_plan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles (global — no company_id)
        $clientAdmin = Role::firstOrCreate(['name' => 'client_admin', 'guard_name' => 'web']);
        $manager     = Role::firstOrCreate(['name' => 'manager',      'guard_name' => 'web']);
        $userRole    = Role::firstOrCreate(['name' => 'user',         'guard_name' => 'web']);

        // Client Admin gets all permissions
        $clientAdmin->syncPermissions($permissions);

        // Manager gets most permissions except billing and branding
        $manager->syncPermissions([
            'view_users', 'create_users',
            'view_documents', 'upload_documents', 'download_documents',
            'connect_quickbooks', 'sync_quickbooks', 'view_quickbooks',
            'view_email_templates',
            'view_settings',
            'view_reports',
        ]);

        // User gets read-only access
        $userRole->syncPermissions([
            'view_documents', 'download_documents',
            'view_quickbooks',
            'view_reports',
        ]);
    }
}
