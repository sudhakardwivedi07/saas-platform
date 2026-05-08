<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Tracks whether this company uses a subdomain or a fully custom domain.
            $table->enum('domain_type', ['subdomain', 'domain'])
                  ->default('subdomain')
                  ->after('subdomain');

            // Random 40-char token placed as a DNS TXT record to prove ownership
            // of the custom domain before traffic is routed to it.
            $table->string('domain_verification_token', 64)
                  ->nullable()
                  ->after('custom_domain');

            // Set when the DNS TXT verification check passes. NULL = unverified.
            $table->timestamp('domain_verified_at')
                  ->nullable()
                  ->after('domain_verification_token');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['domain_type', 'domain_verification_token', 'domain_verified_at']);
        });
    }
};
