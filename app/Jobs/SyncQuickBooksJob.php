<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\QuickBooksConnection;
use App\Services\QuickBooksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncQuickBooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        private readonly int    $connectionId,
        private readonly string $syncType = 'customers', // customers|invoices
    ) {}

    public function handle(): void
    {
        $connection = QuickBooksConnection::find($this->connectionId);

        if (!$connection || !$connection->is_active) {
            Log::warning("SyncQuickBooksJob: connection {$this->connectionId} not found or inactive.");
            return;
        }

        $credentials = $this->resolveCredentials($connection->company_id);
        $service     = new QuickBooksService($connection, $credentials ?? []);

        match ($this->syncType) {
            'customers' => $service->syncCustomers(),
            'invoices'  => $service->syncInvoices(),
            default     => throw new \InvalidArgumentException("Unknown sync type: {$this->syncType}"),
        };

        $connection->update(['last_synced_at' => now()]);
    }

    private function resolveCredentials(int $companyId): ?array
    {
        $setting = CompanyQuickbooksSetting::where('company_id', $companyId)->first();

        if (!$setting) {
            $parentId = Company::withoutGlobalScopes()
                ->where('id', $companyId)
                ->value('created_by');

            if ($parentId) {
                $setting = CompanyQuickbooksSetting::where('company_id', $parentId)->first();
            }
        }

        if (!$setting) return null;

        return [
            'client_id'     => $setting->client_id,
            'client_secret' => $setting->client_secret,
            'redirect_uri'  => $setting->redirect_uri,
            'base_url'      => $setting->base_url,
        ];
    }
}
