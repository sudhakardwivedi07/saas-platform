<?php

namespace Database\Seeders;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        GlobalPaymentGatewayCredentials::firstOrCreate([], [
            'stripe_mode'   => 'test',
            'stripe_status' => 'inactive',
        ]);

        $this->command->info('Payment gateway credentials initialised (inactive — configure in SuperAdmin panel).');
    }
}
