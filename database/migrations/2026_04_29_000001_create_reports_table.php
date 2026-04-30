<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('report_name');
            $table->date('upload_date');
            $table->unsignedBigInteger('total_file_size')->default(0);
            $table->string('status')->default('Uploading'); // Uploading, Analyzing, Analyzed, Failed
            $table->string('one_page_insight')->nullable();
            $table->string('seven_page_insight')->nullable();
            $table->json('insight_json_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
