<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Version number within a report series (1, 2, 3, ...)
            $table->unsignedInteger('version')->default(1)->after('publish_status');
            // Points to the original (v1) report; null means this IS the original
            $table->unsignedBigInteger('parent_report_id')->nullable()->after('version');
            $table->foreign('parent_report_id')->references('id')->on('reports')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['parent_report_id']);
            $table->dropColumn(['version', 'parent_report_id']);
        });
    }
};
