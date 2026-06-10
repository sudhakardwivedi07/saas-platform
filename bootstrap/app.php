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
            \App\Http\Middleware\AutoMigrate::class,          // 1. Migrations first
            \App\Http\Middleware\AutoSeed::class,             // 2. Seeders after
            \App\Http\Middleware\ImpersonateClientViaToken::class, // 3. Per-request client view (no session change)
            LogClientAdminActivity::class,
        ]);

        // ── Named middleware aliases ───────────────────────────────────────────
        $middleware->alias([
            'super-admin'      => SuperAdmin::class,
            'identify-tenant'  => IdentifyTenant::class,
        ]);

        // ── CSRF exclusions ───────────────────────────────────────────────────
        // logout: logging someone out is harmless, so we exempt it to prevent
        // a 419 when the session expires while the page is still open.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Redirect to login instead of showing the blank 419 page when a CSRF
        // token has expired (e.g. session timed out while page was open).
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }
            return redirect()->route('login')
                ->withErrors(['session' => 'Your session expired. Please log in again.']);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $status = $e->getStatusCode();

            if (in_array($status, [403, 404, 500]) && !$request->expectsJson()) {
                return response()->view("errors.{$status}", ['exception' => $e], $status);
            }
        });
    })
    ->create();
