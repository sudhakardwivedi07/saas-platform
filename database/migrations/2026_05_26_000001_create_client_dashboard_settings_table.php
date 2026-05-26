<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique()->index();
            $table->json('hidden_kpis')->nullable();
            $table->json('draft_hidden_kpis')->nullable();
            $table->string('publish_status', 20)->default('published');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_dashboard_settings');
    }
};
