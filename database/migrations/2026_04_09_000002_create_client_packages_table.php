<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── client_packages ──────────────────────────────────────────────────────
        // Plans that a client_admin defines for use when they create sub-companies.
        // Mirrors the SuperAdmin 'packages' table but scoped to a company.
        Schema::create('client_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('annual_price',  10, 2)->nullable();
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_annual_price_id')->nullable();
            $table->integer('max_users')->nullable();
            $table->integer('max_storage_gb')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trial_allowed')->default(false);
            $table->integer('trial_days')->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        // ── Add client_package_id to companies ───────────────────────────────────
        // Allows a company to reference a client plan instead of a SuperAdmin plan.
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('client_package_id')->nullable()->after('package_id');
        });

        // ── Add client_package_id to subscriptions ───────────────────────────────
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('client_package_id')->nullable()->after('package_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', fn (Blueprint $t) => $t->dropColumn('client_package_id'));
        Schema::table('companies',     fn (Blueprint $t) => $t->dropColumn('client_package_id'));
        Schema::dropIfExists('client_packages');
    }
};
