<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AutoMigrate
{
    /**
     * How long (seconds) to cache the "no pending migrations" result.
     * Keeps every request from hitting the DB migration table.
     */
    private const CACHE_TTL = 60;
    private const CACHE_KEY = 'auto_migrate_no_pending';

    public function handle(Request $request, Closure $next): Response
    {
        // Run at most once per cache window
        if (!Cache::has(self::CACHE_KEY)) {
            $this->runIfPending();
        }

        return $next($request);
    }

    private function runIfPending(): void
    {
        try {
            /** @var \Illuminate\Database\Migrations\Migrator $migrator */
            $migrator = app('migrator');

            // Ensure the migrations table exists before querying it
            if (!$migrator->repositoryExists()) {
                Artisan::call('migrate', ['--force' => true]);
                Log::info('AutoMigrate: migrations table created and initial migrations run.');
                Cache::put(self::CACHE_KEY, true, self::CACHE_TTL);
                return;
            }

            $files   = $migrator->getMigrationFiles(database_path('migrations'));
            $ran     = $migrator->getRepository()->getRan();
            $pending = array_diff(array_keys($files), $ran);

            if (empty($pending)) {
                // No pending migrations — cache this so we skip DB check next time
                Cache::put(self::CACHE_KEY, true, self::CACHE_TTL);
                return;
            }

            Log::info('AutoMigrate: ' . count($pending) . ' pending migration(s) detected. Running migrate…', [
                'pending' => array_values($pending),
            ]);

            Artisan::call('migrate', ['--force' => true]);

            Log::info('AutoMigrate: migrations completed successfully.');

            // Cache after successful run
            Cache::put(self::CACHE_KEY, true, self::CACHE_TTL);

        } catch (\Throwable $e) {
            // Never block the request — just log the failure
            Log::error('AutoMigrate: failed to run migrations — ' . $e->getMessage());
        }
    }
}
