<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. Make companies.date_format nullable so NULL means "inherit from global_settings".
 *    (Previously defaulted to 'Y-m-d', making it impossible to distinguish
 *    "admin set this explicitly" from "never touched".)
 * 2. Update global_settings.date_format default to 'M j, Y' (e.g. Jun 2, 2026)
 *    and seed any existing row that still has the old 'Y-m-d' placeholder.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── companies: make date_format nullable, clear old forced default ───────
        Schema::table('companies', function (Blueprint $table) {
            $table->string('date_format', 30)->nullable()->default(null)->change();
        });

        // Rows with the old 'Y-m-d' placeholder → treat as "not set" (inherit global)
        DB::table('companies')->where('date_format', 'Y-m-d')->update(['date_format' => null]);

        // ── global_settings: upgrade default from ISO to human-readable ──────────
        DB::table('global_settings')
            ->where('date_format', 'Y-m-d')
            ->update(['date_format' => 'M j, Y']);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('date_format', 30)->nullable(false)->default('Y-m-d')->change();
        });

        DB::table('companies')->whereNull('date_format')->update(['date_format' => 'Y-m-d']);

        DB::table('global_settings')
            ->where('date_format', 'M j, Y')
            ->update(['date_format' => 'Y-m-d']);
    }
};
