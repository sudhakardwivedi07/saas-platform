<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // ── packages ───────────────────────────────────────────────────────────
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('annual_price', 10, 2)->default(0);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_annual_price_id')->nullable();
            $table->integer('max_users')->nullable();          // NULL = unlimited
            $table->integer('max_storage_gb')->nullable();
            $table->json('features')->nullable();              // feature flag array
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trial_allowed')->default(true);
            $table->integer('trial_days')->default(14);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        // Add FK now that packages table exists
        Schema::table('companies', function (Blueprint $table) {
            $table->foreign('package_id')->references('id')->on('packages')->nullOnDelete();
        });

        // ── payment_gateway_credentials ────────────────────────────────────────
        Schema::create('payment_gateway_credentials', function (Blueprint $table) {
            $table->id();
            $table->enum('stripe_mode', ['test', 'live'])->default('test');
            $table->text('live_stripe_key')->nullable();
            $table->text('live_stripe_secret')->nullable();
            $table->text('live_stripe_webhook_secret')->nullable();
            $table->text('test_stripe_key')->nullable();
            $table->text('test_stripe_secret')->nullable();
            $table->text('test_stripe_webhook_secret')->nullable();
            $table->enum('stripe_status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();
        });

        // ── subscriptions ─────────────────────────────────────────────────────
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('package_id');
            $table->string('stripe_subscription_id')->unique()->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->enum('status', ['trialing', 'active', 'past_due', 'canceled', 'unpaid'])->default('trialing');
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('package_id')->references('id')->on('packages');
        });

        // ── payments ──────────────────────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('stripe_invoice_id')->nullable();
            $table->string('transaction_id')->unique()->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('usd');
            $table->enum('status', ['pending', 'complete', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
        });

        // ── Spatie permission tables ───────────────────────────────────────────
        $tableNames = config('permission.table_names', [
            'roles'                 => 'roles',
            'permissions'           => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles'       => 'model_has_roles',
            'role_has_permissions'  => 'role_has_permissions',
        ]);

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable(); // NULL = SuperAdmin roles
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
            $table->unique(['company_id', 'name', 'guard_name']);
        });

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('module')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('role_id')->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('payment_gateway_credentials');
        Schema::dropIfExists('packages');
    }
};
