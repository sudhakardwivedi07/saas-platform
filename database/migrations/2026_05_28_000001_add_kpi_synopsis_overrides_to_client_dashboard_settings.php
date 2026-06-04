<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_dashboard_settings', function (Blueprint $table) {
            $table->json('kpi_synopsis_overrides')->nullable()->after('draft_hidden_kpis');
        });
    }

    public function down(): void
    {
        Schema::table('client_dashboard_settings', function (Blueprint $table) {
            $table->dropColumn('kpi_synopsis_overrides');
        });
    }
};
