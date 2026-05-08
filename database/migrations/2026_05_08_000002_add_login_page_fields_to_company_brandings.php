<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_brandings', function (Blueprint $table) {
            $table->enum('login_bg_type', ['gradient', 'solid', 'image'])
                  ->default('gradient')->after('login_subheading');
            $table->string('login_bg_color', 25)->default('#0d3d66')->after('login_bg_type');
            $table->string('login_bg_color_to', 25)->default('#f06b21')->after('login_bg_color');
            $table->string('login_card_bg', 25)->default('#ffffff')->after('login_bg_color_to');
            $table->string('login_btn_color', 25)->default('#f06b21')->after('login_card_bg');
        });
    }

    public function down(): void
    {
        Schema::table('company_brandings', function (Blueprint $table) {
            $table->dropColumn([
                'login_bg_type',
                'login_bg_color',
                'login_bg_color_to',
                'login_card_bg',
                'login_btn_color',
            ]);
        });
    }
};
