<?php

namespace App\Jobs;

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

        $service = new QuickBooksService($connection);

        match ($this->syncType) {
            'customers' => $service->syncCustomers(),
            'invoices'  => $service->syncInvoices(),
            default     => throw new \InvalidArgumentException("Unknown sync type: {$this->syncType}"),
        };
    }
}
