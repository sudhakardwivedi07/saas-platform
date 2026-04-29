<?php

use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\LogClientAdminActivity;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:     __DIR__ . '/../routes/web.php',
        api:     __DIR__ . '/../routes/api.php',
        then: function () {
            // Load SuperAdmin routes
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/SuperAdmin/web.php'));
        },
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ── Global middleware ──────────────────────────────────────────────────
        $middleware->web(append: [
            IdentifyTenant::class,
            \App\Http\Middleware\AutoMigrate::class,
            LogClientAdminActivity::class,
        ]);

        // ── Named middleware aliases ───────────────────────────────────────────
        $middleware->alias([
            'super-admin'      => SuperAdmin::class,
            'identify-tenant'  => IdentifyTenant::class,
        ]);

        // ── CSRF exclusions (webhooks) ─────────────────────────────────────────
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $status = $e->getStatusCode();

            if (in_array($status, [403, 404, 500]) && !$request->expectsJson()) {
                return response()->view("errors.{$status}", ['exception' => $e], $status);
            }
        });
    })
    ->create();
