<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_brandings', function (Blueprint $table) {
            $table->string('fp_heading', 255)->nullable()->after('login_btn_color');
            $table->string('fp_subheading', 255)->nullable()->after('fp_heading');
        });

        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name'       => 'view_account_forgot_password',
            'guard_name' => 'web',
        ]);
    }

    public function down(): void
    {
        Schema::table('company_brandings', function (Blueprint $table) {
            $table->dropColumn(['fp_heading', 'fp_subheading']);
        });

        \Spatie\Permission\Models\Permission::where('name', 'view_account_forgot_password')
            ->where('guard_name', 'web')
            ->delete();
    }
};
