<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // ── email_smtp_settings ───────────────────────────────────────────────
        Schema::create('email_smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable(); // NULL = global default
            $table->enum('driver', ['smtp', 'mailgun', 'sendgrid', 'ses', 'log'])->default('smtp');
            $table->string('host')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->enum('encryption', ['tls', 'ssl', 'none'])->default('tls');
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->text('api_key')->nullable();         // Mailgun / Sendgrid
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        // ── email_templates ────────────────────────────────────────────────────
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable(); // NULL = global template
            $table->string('name');
            $table->string('slug');          // e.g. user_welcome, payment_success
            $table->string('subject');
            $table->longText('body');        // HTML with {{placeholders}}
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'slug']);
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        // ── email_logs ────────────────────────────────────────────────────────
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('to_email');
            $table->string('subject');
            $table->enum('status', ['queued', 'sent', 'failed', 'bounced'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('template_id')->references('id')->on('email_templates')->nullOnDelete();
        });

        // ── sms_settings ──────────────────────────────────────────────────────
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->enum('provider', ['twilio', 'vonage', 'aws_sns'])->default('twilio');
            $table->text('account_sid')->nullable();
            $table->text('auth_token')->nullable();
            $table->string('from_number')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        // ── sms_logs ──────────────────────────────────────────────────────────
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('to_number', 50);
            $table->text('message');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        // ── documents ─────────────────────────────────────────────────────────
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->enum('disk', ['local', 's3', 'gcs'])->default('local');
            $table->string('path', 1000);
            $table->unsignedBigInteger('size')->nullable();         // bytes
            $table->string('documentable_type')->nullable();        // polymorphic
            $table->unsignedBigInteger('documentable_id')->nullable();
            $table->boolean('is_private')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['documentable_type', 'documentable_id']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // ── document_versions ─────────────────────────────────────────────────
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedSmallInteger('version_number')->default(1);
            $table->string('path', 1000);
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── quickbooks_connections ────────────────────────────────────────────
        Schema::create('quickbooks_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');          // who connected it
            $table->string('realm_id');                     // QB Company ID
            $table->string('qb_company_name')->nullable();
            $table->longText('access_token');
            $table->longText('refresh_token');
            $table->dateTime('access_token_expires_at');
            $table->dateTime('refresh_token_expires_at');
            $table->string('country', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'realm_id']);     // multiple QB per tenant, no dups
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
        });

        // ── quickbooks_sync_logs ──────────────────────────────────────────────
        Schema::create('quickbooks_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connection_id');
            $table->string('sync_type', 100);   // customers, invoices, payments
            $table->enum('status', ['running', 'success', 'failed'])->default('running');
            $table->integer('records_synced')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->foreign('connection_id')->references('id')->on('quickbooks_connections')->cascadeOnDelete();
        });

        // ── event_logs (audit trail) ──────────────────────────────────────────
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event_type', 100);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['company_id', 'event_type']);
            $table->index('created_at');
        });

        // ── notifications ─────────────────────────────────────────────────────
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // ── cache ─────────────────────────────────────────────────────────────
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // ── jobs / failed_jobs ────────────────────────────────────────────────
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('event_logs');
        Schema::dropIfExists('quickbooks_sync_logs');
        Schema::dropIfExists('quickbooks_connections');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_settings');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_smtp_settings');
    }
};
