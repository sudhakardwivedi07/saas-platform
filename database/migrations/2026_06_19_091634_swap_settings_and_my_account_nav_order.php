<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Swap sort_order of settings (12) and my_account (11)
        DB::table('nav_items')->where('nav_key', 'settings')->update(['sort_order' => 11]);
        DB::table('nav_items')->where('nav_key', 'my_account')->update(['sort_order' => 12]);
    }

    public function down(): void
    {
        DB::table('nav_items')->where('nav_key', 'settings')->update(['sort_order' => 12]);
        DB::table('nav_items')->where('nav_key', 'my_account')->update(['sort_order' => 11]);
    }
};
