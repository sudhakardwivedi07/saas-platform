<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client_packages', function (Blueprint $table) {
            $table->unsignedInteger('max_documents')->nullable()->after('max_storage_gb');
        });
    }

    public function down(): void
    {
        Schema::table('client_packages', function (Blueprint $table) {
            $table->dropColumn('max_documents');
        });
    }
};
