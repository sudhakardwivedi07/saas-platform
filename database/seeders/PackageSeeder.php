<?php

namespace Database\Seeders;

use App\Models\SuperAdmin\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name'             => 'Starter',
                'description'      => 'Perfect for small teams getting started.',
                'monthly_price'    => 29.00,
                'annual_price'     => 290.00,
                'max_users'        => 5,
                'max_storage_gb'   => 10,
                'features'         => ['email', 'documents', 'basic_reports'],
                'is_active'        => true,
                'is_trial_allowed' => true,
                'trial_days'       => 14,
                'is_free'          => false,
                'is_recommended'   => false,
                'sort'             => 1,
            ],
            [
                'name'             => 'Professional',
                'description'      => 'For growing businesses that need more power.',
                'monthly_price'    => 79.00,
                'annual_price'     => 790.00,
                'max_users'        => 25,
                'max_storage_gb'   => 50,
                'features'         => ['email', 'sms', 'documents', 'quickbooks', 'reports', 'api_access'],
                'is_active'        => true,
                'is_trial_allowed' => true,
                'trial_days'       => 14,
                'is_free'          => false,
                'is_recommended'   => true,
                'sort'             => 2,
            ],
            [
                'name'             => 'Enterprise',
                'description'      => 'Unlimited power for large organizations.',
                'monthly_price'    => 199.00,
                'annual_price'     => 1990.00,
                'max_users'        => null,
                'max_storage_gb'   => null,
                'features'         => ['email', 'sms', 'documents', 'quickbooks', 'reports', 'api_access', 'white_label', 'priority_support'],
                'is_active'        => true,
                'is_trial_allowed' => true,
                'trial_days'       => 30,
                'is_free'          => false,
                'is_recommended'   => false,
                'sort'             => 3,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::firstOrCreate(['name' => $pkg['name']], $pkg);
        }
    }
}
