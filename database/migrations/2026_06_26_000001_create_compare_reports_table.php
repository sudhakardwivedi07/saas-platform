<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compare_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('report_id_a')->index();
            $table->unsignedBigInteger('report_id_b')->index();
            $table->string('period_a');
            $table->string('period_b');
            $table->json('kpi_data_a')->nullable();
            $table->json('kpi_data_b')->nullable();
            $table->json('comparison_result')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compare_reports');
    }
};
