<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run all application seeders in dependency order.
     */
    public function run(): void
    {
        $this->call([
            GlobalSettingSeeder::class,   // 1. Platform-level settings singleton
            PackageSeeder::class,          // 2. Subscription plans
            PermissionSeeder::class,       // 3. Spatie permissions + roles
            SuperAdminSeeder::class,       // 4. Initial SuperAdmin account
            EmailTemplateSeeder::class,    // 5. Global email templates
            PaymentGatewaySeeder::class,   // 6. Stripe credentials placeholder
            NavItemSeeder::class,          // 7. Sidebar nav items + role assignments
        ]);
    }
}
