<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('assigned_to')->nullable()->after('status');
        });

        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN status ENUM('open','in_progress','sla_breach','resolved','closed') DEFAULT 'open'");
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('assigned_to');
        });

        DB::statement("ALTER TABLE support_tickets MODIFY COLUMN status ENUM('open','in_progress','resolved','closed') DEFAULT 'open'");
    }
};
