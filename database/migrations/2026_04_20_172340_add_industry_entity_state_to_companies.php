<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('industry_type')->nullable()->after('company_phone');
            $table->string('entity_type')->nullable()->after('industry_type');
            $table->string('state')->nullable()->after('entity_type');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['industry_type', 'entity_type', 'state']);
        });
    }
};
