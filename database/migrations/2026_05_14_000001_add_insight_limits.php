<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('custom_insight_limit')->nullable()->after('insights_used');
        });

        Schema::create('company_insight_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->unsignedInteger('default_limit')->default(50);
            $table->string('billing_period', 20)->default('monthly');
            $table->string('overage_behaviour', 30)->default('pause');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_insight_settings');
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('custom_insight_limit');
        });
    }
};
