<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old incorrectly-named table if it was accidentally run
        Schema::dropIfExists('nav_items_and_roles_tables');

        // ── Nav Items ─────────────────────────────────────────────────────────
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->string('nav_key', 50)->unique();     // machine key, e.g. 'dashboard'
            $table->string('label', 100);                // display name
            $table->text('icon_svg');                    // full <svg>…</svg> string
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Nav Item × Role assignments ───────────────────────────────────────
        // One row per (nav_item, role) pair. Controls route, active-route-pattern,
        // optional Spatie permission gate, and the super-admin visibility toggle.
        Schema::create('nav_item_roles', function (Blueprint $table) {
            $table->id();
            $table->string('nav_key', 50);
            $table->string('role_name', 50);             // 'admin' | 'manager' | 'client'
            $table->string('route_name', 150);           // Laravel route name
            // Comma-separated patterns passed to request()->routeIs()
            $table->string('active_route_pattern', 250)->default('');
            // Optional Spatie permission; if set, user must have it to see the item
            $table->string('required_permission', 100)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['nav_key', 'role_name']);
            $table->foreign('nav_key')
                  ->references('nav_key')
                  ->on('nav_items')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_item_roles');
        Schema::dropIfExists('nav_items');
    }
};
