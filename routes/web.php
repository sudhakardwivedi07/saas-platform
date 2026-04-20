<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\ClientCompanyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPackageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SmtpSettingController;
use App\Http\Controllers\SmsSettingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TenantPaymentGatewayController;
use App\Http\Controllers\TenantUserController;
use Illuminate\Support\Facades\Route;

// ── Public (guest) ─────────────────────────────────────────────────────────────
Route::group([], function () {

    Route::get('login',          [AuthController::class, 'showLogin'])->name('login');
    Route::post('login',         [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout',        [AuthController::class, 'logout'])->name('logout');

    Route::get('register',       [AuthController::class, 'showRegister'])->name('register');
    Route::post('register',      [AuthController::class, 'register'])->name('register.submit');
    Route::get('register/payment-success',      [AuthController::class, 'registrationPaymentSuccess'])->name('register.payment.success');
    Route::get('register/payment-cancel/{id}',  [AuthController::class, 'registrationPaymentCancel'])->name('register.payment.cancel');

    Route::get('forgot-password',       [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password',      [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');

    Route::get('2fa',            [AuthController::class, 'showTwoFactor'])->name('2fa.challenge');
    Route::post('2fa',           [AuthController::class, 'verifyTwoFactor'])->name('2fa.verify');
});

// ── Authenticated Tenant Routes ────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/',         [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Profile
    Route::get('profile',               [ProfileController::class, 'index'])->name('profile');
    Route::post('profile',              [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/password',     [ProfileController::class, 'changePassword'])->name('profile.password');

    // Documents
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/',              [DocumentController::class, 'index'])->name('index');
        Route::post('/upload',       [DocumentController::class, 'upload'])->name('upload');
        Route::get('/{id}/download', [DocumentController::class, 'download'])->name('download');
        Route::post('/{id}/version', [DocumentController::class, 'uploadVersion'])->name('version');
        Route::delete('/{id}',       [DocumentController::class, 'destroy'])->name('destroy');
    });

    // QuickBooks
    Route::prefix('quickbooks')->name('quickbooks.')->group(function () {
        Route::get('/',                    [QuickBooksController::class, 'index'])->name('index');
        Route::get('/connect',             [QuickBooksController::class, 'connect'])->name('connect');
        Route::get('/callback',            [QuickBooksController::class, 'callback'])->name('callback');
        Route::post('/{id}/sync',          [QuickBooksController::class, 'sync'])->name('sync');
        Route::post('/{id}/disconnect',    [QuickBooksController::class, 'disconnect'])->name('disconnect');
    });

    // Email Templates
    Route::prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/',              [EmailTemplateController::class, 'index'])->name('index');
        Route::get('/create',        [EmailTemplateController::class, 'create'])->name('create');
        Route::post('/create',       [EmailTemplateController::class, 'store'])->name('store');
        Route::get('/{slug}/edit',   [EmailTemplateController::class, 'edit'])->name('edit');
        Route::post('/{slug}',       [EmailTemplateController::class, 'update'])->name('update');
        Route::post('/{slug}/test',  [EmailTemplateController::class, 'sendTest'])->name('test');
        Route::delete('/{slug}',     [EmailTemplateController::class, 'destroy'])->name('destroy');
    });

    // Plans (client_admin's own plans for sub-companies)
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/',              [ClientPackageController::class, 'index'])->name('index');
        Route::get('/create',        [ClientPackageController::class, 'create'])->name('create');
        Route::post('/create',       [ClientPackageController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [ClientPackageController::class, 'edit'])->name('edit');
        Route::post('/{id}',         [ClientPackageController::class, 'update'])->name('update');
        Route::delete('/{id}',       [ClientPackageController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle',  [ClientPackageController::class, 'toggleStatus'])->name('toggle');
    });

    // My Companies (multi-company management for client_admin)
    Route::prefix('my-companies')->name('my-companies.')->group(function () {
        Route::get('/',                          [ClientCompanyController::class, 'index'])->name('index');
        Route::get('/create',                    [ClientCompanyController::class, 'create'])->name('create');
        Route::post('/create',                   [ClientCompanyController::class, 'store'])->name('store');
        Route::get('/payment-success',           [ClientCompanyController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/payment-cancel/{id}',       [ClientCompanyController::class, 'paymentCancel'])->name('payment.cancel');
    });

    // Billing / Subscriptions
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/',              [SubscriptionController::class, 'index'])->name('index');
        Route::post('/checkout',     [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::get('/success',       [SubscriptionController::class, 'success'])->name('success');
        Route::get('/cancel',        [SubscriptionController::class, 'cancel'])->name('cancel');
    });

    // Settings — unified tabbed page + individual sub-routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',              [SettingsController::class, 'index'])->name('index');
        Route::get('/branding',      [BrandingController::class, 'index'])->name('branding');
        Route::post('/branding',     [BrandingController::class, 'update'])->name('branding.update');
        Route::get('/email',         [SmtpSettingController::class, 'index'])->name('email');
        Route::post('/email',        [SmtpSettingController::class, 'update'])->name('email.update');
        Route::get('/sms',           [SmsSettingController::class, 'index'])->name('sms');
        Route::post('/sms',          [SmsSettingController::class, 'update'])->name('sms.update');
        Route::get('/payment-gateway',  [TenantPaymentGatewayController::class, 'index'])->name('payment-gateway');
        Route::post('/payment-gateway', [TenantPaymentGatewayController::class, 'update'])->name('payment-gateway.update');

        // Roles & Permissions
        Route::post('/roles',       [RoleController::class, 'store'])->name('roles.store');
        Route::post('/roles/{id}',  [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}',[RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Clients
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/',        [ClientController::class, 'index'])->name('index');
        Route::get('/{id}',    [ClientController::class, 'show'])->name('show');
        Route::post('/',       [ClientController::class, 'store'])->name('store');
        Route::post('/{id}',   [ClientController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->name('destroy');
        // Insights sub-pages
        Route::get('/{id}/insights',        [ClientController::class, 'insights'])->name('insights');
        Route::get('/{clientId}/insights/{insightId}', [ClientController::class, 'insightDetail'])->name('insight.detail');
    });

    // Finance
    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');

    // Support
    Route::get('support',                [SupportController::class, 'index'])->name('support.index');
    Route::post('support',               [SupportController::class, 'store'])->name('support.store');
    Route::get('support/{ticket}',         [SupportController::class, 'show'])->name('support.show');
    Route::patch('support/{ticket}',       [SupportController::class, 'update'])->name('support.update');
    Route::patch('support/{ticket}/notes', [SupportController::class, 'updateNotes'])->name('support.notes');

    // Users (tenant-scoped)
    Route::get('users',  [TenantUserController::class, 'index'])->name('users.index');
    Route::post('users', [TenantUserController::class, 'store'])->name('users.store');
    Route::delete('users/{id}', [TenantUserController::class, 'destroy'])->name('users.destroy');

    // My Account
    Route::get('my-account',          [AccountController::class, 'index'])->name('account.index');
    Route::post('my-account/profile',  [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::post('my-account/password', [AccountController::class, 'updatePassword'])->name('account.password');

    // Activity Log
    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
});
