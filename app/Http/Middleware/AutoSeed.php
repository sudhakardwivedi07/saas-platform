<?php

namespace App\Http\Middleware;

use Closure;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AutoSeed
{
    /**
     * How long (seconds) to cache the "no pending seeders" result.
     * Keeps every request from hitting the seeder_runs table.
     */
    private const CACHE_TTL = 60;
    private const CACHE_KEY = 'auto_seed_no_pending';
    private const TABLE     = 'seeder_runs';

    public function handle(Request $request, Closure $next): Response
    {
        // Run at most once per cache window — same strategy as AutoMigrate
        if (!Cache::has(self::CACHE_KEY)) {
            $this->runIfPending();
        }

        return $next($request);
    }

    private function runIfPending(): void
    {
        try {
            // If the tracking table does not exist yet, AutoMigrate has not
            // finished (or failed).  Skip silently — we will retry next request.
            if (!Schema::hasTable(self::TABLE)) {
                return;
            }

            $allSeeders = DatabaseSeeder::$seeders;

            // Resolve short class names (e.g. "GlobalSettingSeeder") to their
            // fully-qualified equivalents so comparisons are consistent.
            $allSeeders = array_map(
                fn($class) => ltrim($class, '\\'),
                $allSeeders
            );

            $ran     = DB::table(self::TABLE)->pluck('seeder')->map(fn($s) => ltrim($s, '\\'))->toArray();
            $pending = array_values(array_diff($allSeeders, $ran));

            if (empty($pending)) {
                Cache::put(self::CACHE_KEY, true, self::CACHE_TTL);
                return;
            }

            Log::info('AutoSeed: ' . count($pending) . ' pending seeder(s) detected. Running…', [
                'pending' => $pending,
            ]);

            foreach ($pending as $seederClass) {
                try {
                    Artisan::call('db:seed', [
                        '--class' => $seederClass,
                        '--force' => true,
                    ]);

                    DB::table(self::TABLE)->insertOrIgnore([
                        'seeder' => $seederClass,
                        'ran_at' => now(),
                    ]);

                    Log::info('AutoSeed: ✓ ' . class_basename($seederClass));
                } catch (\Throwable $e) {
                    // One seeder failing must not block others or the request
                    Log::error('AutoSeed: ✗ ' . class_basename($seederClass) . ' — ' . $e->getMessage());
                }
            }

            // Invalidate Spatie permission cache in case any seeder touched roles/permissions
            try {
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            } catch (\Throwable) {
                // Non-critical — ignore if Spatie is not installed / cache is unavailable
            }

            Cache::put(self::CACHE_KEY, true, self::CACHE_TTL);

            Log::info('AutoSeed: completed.');

        } catch (\Throwable $e) {
            // Never block the request — just log the failure
            Log::error('AutoSeed: unexpected error — ' . $e->getMessage());
        }
    }
}
