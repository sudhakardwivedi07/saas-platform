<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seeders that were already part of the project before AutoSeed was introduced.
     * On an existing deployment these are pre-marked as "ran" so they are never
     * executed again.  On a brand-new install the table is left empty and AutoSeed
     * will run every seeder on the first request.
     */
    private array $existingSeeders = [
        'Database\\Seeders\\GlobalSettingSeeder',
        'Database\\Seeders\\PackageSeeder',
        'Database\\Seeders\\PermissionSeeder',
        'Database\\Seeders\\SuperAdminSeeder',
        'Database\\Seeders\\EmailTemplateSeeder',
        'Database\\Seeders\\PaymentGatewaySeeder',
        'Database\\Seeders\\NavItemSeeder',
    ];

    public function up(): void
    {
        Schema::create('seeder_runs', function (Blueprint $table) {
            $table->id();
            $table->string('seeder', 255)->unique()->comment('Fully-qualified seeder class name');
            $table->timestamp('ran_at')->comment('When this seeder was last executed');
        });

        // ── Existing deployment detection ──────────────────────────────────────
        // If global_settings already has rows the app was running before AutoSeed
        // was added.  Pre-populate the table so those seeders are not re-run.
        $isExistingDeployment = Schema::hasTable('global_settings')
            && DB::table('global_settings')->exists();

        if ($isExistingDeployment) {
            $now = now();
            foreach ($this->existingSeeders as $seeder) {
                DB::table('seeder_runs')->insertOrIgnore([
                    'seeder' => $seeder,
                    'ran_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seeder_runs');
    }
};
