<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@platform.com', 'company_id' => null],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('SuperAdmin@123'),
                'is_superadmin'     => true,
                'status'            => 'active',
                'admin_approval'    => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('SuperAdmin ready: superadmin@platform.com / SuperAdmin@123');
    }
}
