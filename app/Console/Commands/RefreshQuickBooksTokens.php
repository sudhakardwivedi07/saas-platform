<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\Company;
use App\Models\EventLog;
use App\Models\QuickBooksConnection;
use App\Models\SuperAdmin\Subscription;
use App\Services\QuickBooksService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// ── RefreshQuickBooksTokens ────────────────────────────────────────────────────
class RefreshQuickBooksTokens extends Command
{
    protected $signature   = 'quickbooks:refresh-tokens';
    protected $description = 'Refresh QuickBooks access tokens that are about to expire.';

    public function handle(): void
    {
        $connections = QuickBooksConnection::where('is_active', true)
            ->where('access_token_expires_at', '<=', now()->addMinutes(30))
            ->get();

        $this->info("Found {$connections->count()} connections needing refresh.");

        foreach ($connections as $connection) {
            try {
                $service = new QuickBooksService($connection);
                $service->refreshTokenIfNeeded();
                $this->line("  ✓ Refreshed: {$connection->qb_company_name} (company #{$connection->company_id})");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed #{$connection->id}: " . $e->getMessage());
                Log::error("QB token refresh failed for connection {$connection->id}: " . $e->getMessage());
            }
        }

        $this->info('Done.');
    }
}
