<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Track which superadmin created each company
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('id');
        });

        // Allow users to manage multiple companies (beyond their primary company_id)
        Schema::create('user_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member'); // admin, member, viewer
            $table->timestamps();
            $table->unique(['user_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_companies');
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
