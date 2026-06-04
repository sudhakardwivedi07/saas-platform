<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('pm_brand', 20)->nullable()->after('stripe_customer_id');
            $table->string('pm_last4', 4)->nullable()->after('pm_brand');
            $table->unsignedSmallInteger('pm_exp_month')->nullable()->after('pm_last4');
            $table->unsignedSmallInteger('pm_exp_year')->nullable()->after('pm_exp_month');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['pm_brand', 'pm_last4', 'pm_exp_month', 'pm_exp_year']);
        });
    }
};
