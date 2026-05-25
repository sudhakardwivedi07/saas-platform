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
        Schema::create('nav_menu_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nav_key', 50);      // e.g. 'dashboard', 'clients'
            $table->string('role_name', 50);    // 'admin', 'manager', 'client'
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['nav_key', 'role_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav_menu_settings');
    }
};
