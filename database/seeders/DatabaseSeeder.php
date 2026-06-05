<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ordered list of all application seeders.
     *
     * AutoSeed middleware reads this array to discover which seeders have not
     * yet been recorded in the `seeder_runs` table and runs them automatically.
     *
     * ── How to add a new seeder ────────────────────────────────────────────────
     * 1. Create your seeder class in database/seeders/
     * 2. Make it idempotent (use firstOrCreate / updateOrInsert / insertOrIgnore)
     * 3. Append it to this array — AutoSeed will pick it up on the next request
     *
     * Do NOT remove or reorder existing entries; only append new ones.
     */
    public static array $seeders = [
        GlobalSettingSeeder::class,   // 1. Platform-level settings singleton
        PackageSeeder::class,          // 2. Subscription plans
        PermissionSeeder::class,       // 3. Spatie permissions + roles
        SuperAdminSeeder::class,       // 4. Initial SuperAdmin account
        EmailTemplateSeeder::class,    // 5. Global email templates
        PaymentGatewaySeeder::class,   // 6. Stripe credentials placeholder
        NavItemSeeder::class,          // 7. Sidebar nav items + role assignments
    ];

    /**
     * Run all application seeders in dependency order.
     */
    public function run(): void
    {
        $this->call(static::$seeders);
    }
}
