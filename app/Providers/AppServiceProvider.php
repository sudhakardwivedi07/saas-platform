<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Document;
use App\Models\User;
use App\Services\EmailService;
use App\Services\SmsService;
use App\Services\StripeService;
use App\Services\DocumentService;
use App\Services\QuickBooksService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services as singletons in the container
        $this->app->singleton(EmailService::class);
        $this->app->singleton(SmsService::class);
        $this->app->singleton(DocumentService::class);

        // StripeService is NOT singleton — it reads fresh credentials each time
        $this->app->bind(StripeService::class);
    }

    public function boot(): void
    {
        // Use Tailwind-compatible pagination views
        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination-simple');

        // ── Custom Blade directives ────────────────────────────────────────────

        // @tenant — only renders content when inside a tenant context
        Blade::if('tenant', fn () => company() !== null);

        // @superadmin — only renders for super admins
        Blade::if('superadmin', fn () => is_superadmin());

        // @can wrapper that also checks tenant context
        // Usage: @tenantCan('upload_documents')
        Blade::directive('tenantCan', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->can({$permission})): ?>";
        });
        Blade::directive('endTenantCan', fn () => '<?php endif; ?>');

        // @money — format a number as currency
        Blade::directive('money', function ($amount) {
            return "<?php echo '$' . number_format({$amount}, 2); ?>";
        });

        // @fileSize — format bytes
        Blade::directive('fileSize', function ($bytes) {
            return "<?php echo format_bytes({$bytes}); ?>";
        });

        // @timeAgo — human readable time
        Blade::directive('timeAgo', function ($date) {
            return "<?php echo \Carbon\Carbon::parse({$date})->diffForHumans(); ?>";
        });
    }
}
