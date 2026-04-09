<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // ── global_settings ────────────────────────────────────────────────────
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('global_app_name')->default('SaaS Platform');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('login_background')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i');
            $table->string('locale')->default('en');
            $table->string('currency_code')->default('USD');
            $table->string('primary_color')->default('#2563eb');
            $table->boolean('allow_registration')->default(true);
            $table->boolean('email_verification')->default(false);
            $table->boolean('maintenance_mode')->default(false);
            $table->text('auth_css')->nullable();
            $table->timestamps();
        });

        // ── companies ──────────────────────────────────────────────────────────
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('app_name')->nullable();
            $table->string('company_email');
            $table->string('company_phone')->nullable();
            $table->string('subdomain')->unique()->nullable();
            $table->string('custom_domain')->unique()->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i');
            $table->string('locale')->default('en');
            $table->timestamps();
        });

        // ── company_brandings ──────────────────────────────────────────────────
        Schema::create('company_brandings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('logo')->nullable();
            $table->string('dark_logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('login_background')->nullable();
            $table->string('primary_color')->default('#2563eb');
            $table->string('secondary_color')->default('#64748b');
            $table->string('accent_color')->default('#0ea5e9');
            $table->string('font_family')->default('Inter');
            $table->text('custom_css')->nullable();
            $table->string('login_heading')->nullable();
            $table->string('login_subheading')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        // ── users ──────────────────────────────────────────────────────────────
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();  // NULL = SuperAdmin
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('mobile')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_superadmin')->default(false);
            $table->boolean('admin_approval')->default(true);
            $table->string('locale')->default('en');
            $table->boolean('dark_theme')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('two_factor_code')->nullable();
            $table->dateTime('two_factor_expires_at')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['email', 'company_id']);   // same email allowed across tenants
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        // ── password_reset_tokens ──────────────────────────────────────────────
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ── sessions ───────────────────────────────────────────────────────────
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('company_brandings');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('global_settings');
    }
};
