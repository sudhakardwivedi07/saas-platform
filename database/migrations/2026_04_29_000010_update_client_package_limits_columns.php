<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_packages', function (Blueprint $table) {
            $table->unsignedInteger('insights_per_month')->nullable()->after('annual_price');
            $table->unsignedInteger('max_companies')->nullable()->after('insights_per_month');
        });

        DB::table('client_packages')
            ->whereNull('max_companies')
            ->update([
                'max_companies' => DB::raw('max_users'),
            ]);

        DB::table('client_packages')
            ->whereNull('insights_per_month')
            ->update([
                'insights_per_month' => 50,
            ]);

        Schema::table('client_packages', function (Blueprint $table) {
            $table->dropColumn([
                'max_users',
                'max_storage_gb',
                'stripe_monthly_price_id',
                'stripe_annual_price_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('client_packages', function (Blueprint $table) {
            $table->integer('max_users')->nullable()->after('annual_price');
            $table->integer('max_storage_gb')->nullable()->after('max_users');
            $table->string('stripe_monthly_price_id')->nullable()->after('annual_price');
            $table->string('stripe_annual_price_id')->nullable()->after('stripe_monthly_price_id');
        });

        DB::table('client_packages')
            ->whereNull('max_users')
            ->update([
                'max_users' => DB::raw('max_companies'),
            ]);

        Schema::table('client_packages', function (Blueprint $table) {
            $table->dropColumn([
                'insights_per_month',
                'max_companies',
            ]);
        });
    }
};
