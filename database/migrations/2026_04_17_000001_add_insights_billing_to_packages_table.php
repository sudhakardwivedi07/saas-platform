<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('max_insights_per_month')->nullable()->after('max_storage_gb');
            $table->enum('billing_cycle', ['monthly', 'annual', 'both'])->default('monthly')->after('annual_price');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['max_insights_per_month', 'billing_cycle']);
        });
    }
};
