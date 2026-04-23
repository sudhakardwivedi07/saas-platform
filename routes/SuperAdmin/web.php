<?php

use App\Http\Controllers\SuperAdmin\AccountController;
use App\Http\Controllers\SuperAdmin\ActivityController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\EmailTemplateController;
use App\Http\Controllers\SuperAdmin\FinanceController;
use App\Http\Controllers\SuperAdmin\GlobalSettingController;
use App\Http\Controllers\SuperAdmin\PackageController;
use App\Http\Controllers\SuperAdmin\PaymentGatewayController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\StripeWebhookController;
use App\Http\Controllers\SuperAdmin\SupportController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

// ── SuperAdmin Panel ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'super-admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Companies (Tenants)
        Route::get('companies/create',  [CompanyController::class, 'create'])->name('companies.create');
        Route::post('companies',        [CompanyController::class, 'store'])->name('companies.store');
        Route::post('companies/bulk',   [CompanyController::class, 'bulkStore'])->name('companies.bulk-store');
        Route::resource('companies', CompanyController::class)->except(['create', 'store']);
        Route::post('companies/{id}/impersonate', [CompanyController::class, 'impersonate'])->name('companies.impersonate');

        // Users
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Roles & Permissions
        Route::resource('roles', RoleController::class)->except(['show']);

        // Packages
        Route::resource('packages', PackageController::class);
        Route::post('packages/{id}/toggle', [PackageController::class, 'toggleStatus'])->name('packages.toggle');

        // Email Templates
        Route::get('email-templates',             [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('email-templates/create',      [EmailTemplateController::class, 'create'])->name('email-templates.create');
        Route::post('email-templates',            [EmailTemplateController::class, 'store'])->name('email-templates.store');
        Route::get('email-templates/{slug}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
        Route::post('email-templates/{slug}',     [EmailTemplateController::class, 'update'])->name('email-templates.update');
        Route::delete('email-templates/{slug}',   [EmailTemplateController::class, 'destroy'])->name('email-templates.destroy');

        // Payment gateway credentials
        Route::get('payment-settings',  [PaymentGatewayController::class, 'index'])->name('payment_settings');
        Route::post('payment-settings', [PaymentGatewayController::class, 'update'])->name('payment_settings.update');

        // Global Settings
        Route::get('settings',       [GlobalSettingController::class, 'index'])->name('settings');
        Route::post('settings',      [GlobalSettingController::class, 'update'])->name('settings.update');
        Route::post('smtp-settings', [GlobalSettingController::class, 'updateSmtp'])->name('smtp_settings.update');
        Route::post('sms-settings',  [GlobalSettingController::class, 'updateSms'])->name('sms_settings.update');

        // Finance
        Route::get('finance', [FinanceController::class, 'index'])->name('finance');
        Route::get('finance/data', [FinanceController::class, 'data'])->name('finance.data');
        Route::get('finance/export', [FinanceController::class, 'export'])->name('finance.export');

        // Support
        Route::get('support',                           [SupportController::class, 'index'])->name('support');
        Route::get('support/{ticket}',                  [SupportController::class, 'show'])->name('support.show');
        Route::patch('support/{ticket}/status',         [SupportController::class, 'updateStatus'])->name('support.update-status');
        Route::patch('support/{ticket}/assign',         [SupportController::class, 'assign'])->name('support.assign');
        Route::patch('support/{ticket}/notes',          [SupportController::class, 'saveNotes'])->name('support.save-notes');

        // My Account
        Route::get('my-account',  [AccountController::class, 'index'])->name('account');
        Route::post('my-account', [AccountController::class, 'update'])->name('account.update');

        // Activity Log
        Route::get('activity', [ActivityController::class, 'index'])->name('activity');
    });

// ── Stop Impersonation (auth only — accessible while impersonating) ───────────
Route::middleware(['auth'])
    ->get('superadmin/stop-impersonation', [CompanyController::class, 'stopImpersonation'])
    ->name('superadmin.stop_impersonation');

// ── Stripe Webhooks (no auth, no CSRF) ────────────────────────────────────────
Route::post('webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->name('webhooks.stripe')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
