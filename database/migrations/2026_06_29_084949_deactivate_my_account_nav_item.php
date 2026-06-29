<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('nav_items')
            ->where('nav_key', 'my_account')
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('nav_items')
            ->where('nav_key', 'my_account')
            ->update(['is_active' => true]);
    }
};
