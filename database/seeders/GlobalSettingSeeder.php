<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    public function run(): void
    {
        GlobalSetting::firstOrCreate([], [
            'global_app_name'    => 'SaaS Platform',
            'company_email'      => 'admin@platform.com',
            'timezone'           => 'UTC',
            'date_format'        => 'Y-m-d',
            'time_format'        => 'H:i',
            'locale'             => 'en',
            'currency_code'      => 'USD',
            'primary_color'      => '#2563eb',
            'allow_registration' => true,
            'email_verification' => false,
            'maintenance_mode'   => false,
        ]);
    }
}
