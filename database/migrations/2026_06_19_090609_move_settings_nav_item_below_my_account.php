<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Shift items after settings (sort_order > 5) down by 1 to close the gap.
        DB::table('nav_items')
            ->where('sort_order', '>', 5)
            ->where('nav_key', '!=', 'settings')
            ->decrement('sort_order');

        // Place settings directly after my_account.
        $myAccountSort = DB::table('nav_items')->where('nav_key', 'my_account')->value('sort_order');

        DB::table('nav_items')
            ->where('nav_key', 'settings')
            ->update(['sort_order' => $myAccountSort + 1]);
    }

    public function down(): void
    {
        // Restore settings to position 5 and shift everything from 5 upward.
        DB::table('nav_items')
            ->where('sort_order', '>=', 5)
            ->where('nav_key', '!=', 'settings')
            ->increment('sort_order');

        DB::table('nav_items')
            ->where('nav_key', 'settings')
            ->update(['sort_order' => 5]);
    }
};
