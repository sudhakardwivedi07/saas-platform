<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate any existing firm_id data into company_id before dropping
        DB::table('email_templates')
            ->whereNotNull('firm_id')
            ->whereNull('company_id')
            ->update(['company_id' => DB::raw('firm_id')]);

        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropForeign(['firm_id']);
            $table->dropColumn('firm_id');
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('firm_id')->nullable()->after('company_id');
            $table->foreign('firm_id')->references('id')->on('companies')->nullOnDelete();
        });
    }
};
