<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The "Subscription & Usage" billing item is now surfaced as a tab
        // inside "My Account", so it should no longer appear in the sidebar.
        \Illuminate\Support\Facades\DB::table('nav_item_roles')
            ->where('nav_key', 'billing')
            ->update(['is_visible' => false]);
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('nav_item_roles')
            ->where('nav_key', 'billing')
            ->update(['is_visible' => true]);
    }
};
