<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update the Insights nav item for admin/manager to point to partner-insights
        DB::table('nav_item_roles')
            ->where('nav_key', 'insights')
            ->whereIn('role_name', ['admin', 'manager'])
            ->update([
                'route_name'           => 'partner-insights.index',
                'active_route_pattern' => 'partner-insights.*',
            ]);
    }

    public function down(): void
    {
        DB::table('nav_item_roles')
            ->where('nav_key', 'insights')
            ->whereIn('role_name', ['admin', 'manager'])
            ->update([
                'route_name'           => 'firm-insights.index',
                'active_route_pattern' => 'firm-insights.*',
            ]);
    }
};
