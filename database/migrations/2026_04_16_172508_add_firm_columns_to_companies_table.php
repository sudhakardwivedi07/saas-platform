<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->after('trial_ends_at');
        });

        // Extend status enum to include 'trial'
        DB::statement("ALTER TABLE companies MODIFY COLUMN status ENUM('active','inactive','suspended','trial') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('last_active_at');
        });

        DB::statement("ALTER TABLE companies MODIFY COLUMN status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active'");
    }
};
