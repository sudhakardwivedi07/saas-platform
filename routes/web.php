<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\FirmInsightsController;
use App\Http\Controllers\PartnerInsightsController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\ClientCompanyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientKPIController;
use App\Http\Controllers\ClientPackageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardKPIController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FirmEmailTemplateController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FirmQuickBooksController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\InsightLimitController;
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
    Route::get('my-financials', [DashboardController::class, 'myFinancials'])->name('my-financials.index');
    Route::post('dashboard/assign-plan', [DashboardController::class, 'assignPlan'])->name('dashboard.assign-plan');
    Route::get('dashboard/kpi-data', [DashboardController::class, 'kpiData'])->name('dashboard.kpi-data');
    Route::get('dashboard/kpi-detail/{kpiType}', [DashboardController::class, 'kpiDetail'])->name('dashboard.kpi-detail');
    Route::prefix('dashboard-kpi')->name('dashboard-kpi.')->group(function () {
        Route::get('/runway-burn-details', [DashboardKPIController::class, 'runwayBurnDetails'])->name('runway-burn-details');
        Route::get('/working-capital-details', [DashboardKPIController::class, 'workingCapitalDetails'])->name('working-capital-details');
        Route::get('/operating-cash-flow-details', [DashboardKPIController::class, 'operatingCashFlowDetails'])->name('operating-cash-flow-details');
        Route::get('/revenue-details', [DashboardKPIController::class, 'revenueDetails'])->name('revenue-details');
        Route::get('/ebitda-details', [DashboardKPIController::class, 'ebitdaDetails'])->name('ebitda-details');
        Route::get('/margin-trends-details', [DashboardKPIController::class, 'marginTrendsDetails'])->name('margin-trends-details');
        Route::get('/ar-aging-details', [DashboardKPIController::class, 'arAgingDetails'])->name('ar-aging-details');
        Route::get('/ap-aging-details', [DashboardKPIController::class, 'apAgingDetails'])->name('ap-aging-details');
        Route::get('/revenue-concentration-details', [DashboardKPIController::class, 'revenueConcentrationDetails'])->name('revenue-concentration-details');

        Route::get('/cash-burn-runway', [DashboardKPIController::class, 'cashBurnRunway'])->name('cash-burn-runway');
        Route::get('/working-capital', [DashboardKPIController::class, 'workingCapital'])->name('working-capital');
        Route::get('/operating-cash-flow', [DashboardKPIController::class, 'operatingCashFlow'])->name('operating-cash-flow');
        Route::get('/revenue-trend', [DashboardKPIController::class, 'revenueTrend'])->name('revenue-trend');
        Route::get('/ebitda', [DashboardKPIController::class, 'ebitda'])->name('ebitda');
        Route::get('/margin-trends', [DashboardKPIController::class, 'marginTrends'])->name('margin-trends');
        Route::get('/ar-aging', [DashboardKPIController::class, 'arAging'])->name('ar-aging');
        Route::get('/ap-aging', [DashboardKPIController::class, 'apAging'])->name('ap-aging');
        Route::get('/revenue-concentration', [DashboardKPIController::class, 'revenueConcentration'])->name('revenue-concentration');

        Route::get('/kpi-synopsis-files/{kpiId}', [DashboardKPIController::class, 'getKpiSynopsisFiles'])->name('kpi-synopsis-files');
        Route::post('/kpi-synopsis-api', [DashboardKPIController::class, 'callKpiSynopsisApi'])->name('kpi-synopsis-api');
        Route::post('/kpi-synopsis-save', [DashboardKPIController::class, 'saveKpiSynopsis'])->name('kpi-synopsis-save');
        Route::get('/kpi-synopsis-get', [DashboardKPIController::class, 'getKpiSynopsisOverride'])->name('kpi-synopsis-get');
    });

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
    Route::get('account/quickbooks-integration/callback', [QuickBooksController::class, 'callback'])->name('quickbooks.callback');
    Route::prefix('quickbooks')->name('quickbooks.')->group(function () {
        Route::get('/',                    [QuickBooksController::class, 'index'])->name('index');
        Route::get('/connect',             [QuickBooksController::class, 'connect'])->name('connect');
        Route::get('/user-connect',         [QuickBooksController::class, 'userConnect'])->name('user-connect');
        Route::post('/user-disconnect',     [QuickBooksController::class, 'userDisconnect'])->name('user-disconnect');
        Route::get('/user-test-connection', [QuickBooksController::class, 'userTestConnection'])->name('user-test-connection');
        Route::post('/user-refresh-token',  [QuickBooksController::class, 'userRefreshToken'])->name('user-refresh-token');
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

    // Plans (admin's own plans for sub-companies)
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/',              [ClientPackageController::class, 'index'])->name('index');
        Route::get('/create',        [ClientPackageController::class, 'create'])->name('create');
        Route::post('/create',       [ClientPackageController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [ClientPackageController::class, 'edit'])->name('edit');
        Route::post('/{id}',         [ClientPackageController::class, 'update'])->name('update');
        Route::delete('/{id}',       [ClientPackageController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle',  [ClientPackageController::class, 'toggleStatus'])->name('toggle');
    });

    // Firm QuickBooks (firm's own QB connect/disconnect — separate from sub-company QB)
    Route::prefix('firm-quickbooks')->name('firm-quickbooks.')->group(function () {
        Route::get('/',                   [FirmQuickBooksController::class, 'index'])->name('index');
        Route::get('/connect',            [FirmQuickBooksController::class, 'connect'])->name('connect');
        Route::post('/disconnect',        [FirmQuickBooksController::class, 'disconnect'])->name('disconnect');
        Route::get('/test-connection',    [FirmQuickBooksController::class, 'testConnection'])->name('test-connection');
        Route::post('/refresh-token',     [FirmQuickBooksController::class, 'refreshToken'])->name('refresh-token');
    });

    // My Companies (multi-company management for admin)
    Route::prefix('my-companies')->name('my-companies.')->group(function () {
        Route::get('/',                          [ClientCompanyController::class, 'index'])->name('index');
        Route::get('/create',                    [ClientCompanyController::class, 'create'])->name('create');
        Route::post('/create',                   [ClientCompanyController::class, 'store'])->name('store');
        Route::get('/{id}/edit',                 [ClientCompanyController::class, 'edit'])->name('edit');
        Route::post('/{id}',                     [ClientCompanyController::class, 'update'])->name('update');
        Route::delete('/{id}',                   [ClientCompanyController::class, 'destroy'])->name('destroy');
        Route::get('/payment-success',           [ClientCompanyController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/payment-cancel/{id}',       [ClientCompanyController::class, 'paymentCancel'])->name('payment.cancel');
        Route::post('/{id}/assign-plan',         [ClientCompanyController::class, 'assignPlan'])->name('assign-plan');
        Route::post('/quick-store',              [ClientCompanyController::class, 'quickStore'])->name('quick-store');
    });

    // Billing / Subscriptions
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/',                      [SubscriptionController::class, 'index'])->name('index');
        Route::post('/checkout',             [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::get('/success',               [SubscriptionController::class, 'success'])->name('success');
        Route::get('/cancel',                [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/cancel-subscription',     [SubscriptionController::class, 'cancelSubscription'])->name('cancel-subscription');
        Route::post('/reactivate-subscription', [SubscriptionController::class, 'reactivateSubscription'])->name('reactivate-subscription');
    });

    // Settings — unified tabbed page + individual sub-routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',              [SettingsController::class, 'index'])->name('index');
        Route::get('/branding',      [BrandingController::class, 'index'])->name('branding');
        Route::post('/branding',     [BrandingController::class, 'update'])->name('branding.update');
        Route::get('/email',         [SmtpSettingController::class, 'index'])->name('email');
        Route::post('/email',        [SmtpSettingController::class, 'update'])->name('email.update');
        Route::post('/email/test',   [SmtpSettingController::class, 'testSmtp'])->name('email.test');
        Route::get('/sms',           [SmsSettingController::class, 'index'])->name('sms');
        Route::post('/sms',          [SmsSettingController::class, 'update'])->name('sms.update');
        Route::get('/payment-gateway',  [TenantPaymentGatewayController::class, 'index'])->name('payment-gateway');
        Route::post('/payment-gateway', [TenantPaymentGatewayController::class, 'update'])->name('payment-gateway.update');

        // Login Page customisation
        Route::post('/login-page', [SettingsController::class, 'updateLoginPage'])->name('login-page.update');
        Route::post('/forgot-password-page', [SettingsController::class, 'updateForgotPasswordPage'])->name('forgot-password-page.update');

        // QuickBooks Settings
        Route::post('/quickbooks-settings', [SettingsController::class, 'updateQuickbooksSettings'])->name('quickbooks-settings.update');

        // Roles & Permissions
        Route::post('/roles',       [RoleController::class, 'store'])->name('roles.store');
        Route::post('/roles/{id}',  [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}',[RoleController::class, 'destroy'])->name('roles.destroy');

        // Insight Limits
        Route::post('/insight-limits',                      [InsightLimitController::class, 'updateLimitSetup'])->name('insight-limits.update');
        Route::post('/insight-limits/{companyId}/custom',   [InsightLimitController::class, 'setCustomLimit'])->name('insight-limits.custom');

        // Firm Email Templates (partner portal settings — AJAX)
        Route::prefix('firm-email-templates')->name('firm-email-templates.')->group(function () {
            Route::post('/',        [FirmEmailTemplateController::class, 'store'])->name('store');
            Route::get('/{id}',     [FirmEmailTemplateController::class, 'show'])->name('show');
            Route::post('/{id}',    [FirmEmailTemplateController::class, 'update'])->name('update');
            Route::delete('/{id}',  [FirmEmailTemplateController::class, 'destroy'])->name('destroy');
        });
    });

    // Clients
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/',        [ClientController::class, 'index'])->name('index');
        // ── Literal POST routes must come BEFORE /{id} wildcard ──
        Route::get('/stop-impersonating',    [ClientController::class, 'stopImpersonating'])->name('stop-impersonating');
        Route::post('/bulk-upload',          [ClientController::class, 'bulkUpload'])->name('bulk-upload');
        Route::post('/check-email',          [ClientController::class, 'checkEmailExists'])->name('check-email');
        Route::post('/',       [ClientController::class, 'store'])->name('store');
        // ── Wildcard routes ───────────────────────────────────────
        Route::get('/{id}',    [ClientController::class, 'show'])->name('show');
        Route::post('/{id}',   [ClientController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status',   [ClientController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/assign-plan',     [ClientController::class, 'assignPlan'])->name('assign-plan');
        Route::post('/{id}/login-as',        [ClientController::class, 'loginAs'])->name('login-as');
        Route::get('/{id}/dashboard',      [ClientController::class, 'dashboard'])->name('dashboard');
        Route::post('/{id}/dashboard/save-draft', [ClientController::class, 'saveDashboardDraft'])->name('dashboard.save-draft');
        Route::post('/{id}/dashboard/publish',    [ClientController::class, 'publishDashboard'])->name('dashboard.publish');
        // KPI Dashboard
        Route::get('/{id}/kpi-dashboard', [ClientKPIController::class, 'index'])->name('kpi-dashboard');
        Route::get('/{id}/kpi-detail/{kpiType}', [ClientKPIController::class, 'showKpiDetail'])->name('kpi-detail');
        Route::get('/{id}/kpi-data',      [ClientKPIController::class, 'getKpiData'])->name('kpi-data');
        // Insights sub-pages
        Route::get('/{id}/insights',        [ClientController::class, 'insights'])->name('insights');
        Route::get('/{clientId}/insights/{insightId}', [ClientController::class, 'insightDetail'])->name('insight.detail');
    });

    // Finance
    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('finance/payments/{payment}/invoice', [FinanceController::class, 'invoice'])->name('finance.invoice');

    // Support
    Route::get('support',                [SupportController::class, 'index'])->name('support.index');
    Route::post('support',               [SupportController::class, 'store'])->name('support.store');
    Route::get('support/{ticket}',         [SupportController::class, 'show'])->name('support.show');
    Route::patch('support/{ticket}',       [SupportController::class, 'update'])->name('support.update');
    Route::patch('support/{ticket}/notes', [SupportController::class, 'updateNotes'])->name('support.notes');

    // Users (tenant-scoped)
    Route::get('users',              [TenantUserController::class, 'index'])->name('users.index');
    Route::post('users',             [TenantUserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update', [TenantUserController::class, 'update'])->name('users.update');
    Route::post('users/{id}/toggle', [TenantUserController::class, 'toggleStatus'])->name('users.toggle');
    Route::delete('users/{id}',      [TenantUserController::class, 'destroy'])->name('users.destroy');

    // My Account
    Route::get('my-account',          [AccountController::class, 'index'])->name('account.index');
    Route::post('my-account/profile',     [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::post('my-account/password',    [AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('my-account/date-format', [AccountController::class, 'updateDateFormat'])->name('account.date-format');

    // Activity Log
    Route::get('activity', fn() => redirect()->route('account.index', ['tab' => 'tab-activity']))->name('activity.index');

    // Insights (user role)
    Route::prefix('insights')->name('insights.')->group(function () {
        Route::get('/',                            [InsightsController::class, 'index'])->name('index');
        Route::post('/generate',                   [InsightsController::class, 'generate'])->name('generate');
        Route::post('/save-business-context',      [InsightsController::class, 'saveBusinessContext'])->name('save-business-context');
        Route::get('/status/{id}',                 [InsightsController::class, 'checkStatus'])->name('status');
        Route::get('/{encryptedId}/view',          [InsightsController::class, 'view'])->name('view');
        Route::get('/{id}/data',                   [InsightsController::class, 'getData'])->name('data');
        Route::post('/{id}/update-content',        [InsightsController::class, 'updateContent'])->name('update-content');
        Route::get('/{id}/preview',                [InsightsController::class, 'previewPdf'])->name('preview');
        Route::post('/{id}/share',                 [InsightsController::class, 'shareInsight'])->name('share');
        Route::get('/{id}/export-pdf',             [InsightsController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}/versions',               [InsightsController::class, 'getVersions'])->name('versions');
        Route::get('/{id}/download',               [InsightsController::class, 'download'])->name('download');
        Route::delete('/{id}',                     [InsightsController::class, 'destroy'])->name('destroy');
        Route::get('/delete-all',                  [InsightsController::class, 'deleteAllReports'])->name('delete-all');
    });

    // Partner Insights (admin / manager — all client insights, with generate/edit/publish)
    Route::prefix('partner-insights')->name('partner-insights.')->group(function () {
        Route::get('/',                            [PartnerInsightsController::class, 'index'])->name('index');
        Route::post('/generate',                   [PartnerInsightsController::class, 'generate'])->name('generate');
        Route::get('/clients',                     [PartnerInsightsController::class, 'getClients'])->name('clients');
        Route::get('/clients/{clientId}/companies',[PartnerInsightsController::class, 'getCompanies'])->name('companies');
        Route::get('/status/{id}',                 [PartnerInsightsController::class, 'checkStatus'])->name('status');
        Route::get('/{encryptedId}/view',          [PartnerInsightsController::class, 'view'])->name('view');
        Route::get('/{id}/data',                   [PartnerInsightsController::class, 'getData'])->name('data');
        Route::post('/{id}/update-content',        [PartnerInsightsController::class, 'updateContent'])->name('update-content');
        Route::post('/{id}/publish',               [PartnerInsightsController::class, 'publish'])->name('publish');
        Route::post('/{id}/share',                 [PartnerInsightsController::class, 'shareInsight'])->name('share');
        Route::get('/{id}/preview',                [PartnerInsightsController::class, 'previewPdf'])->name('preview');
        Route::get('/{id}/export-pdf',             [PartnerInsightsController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}/versions',               [PartnerInsightsController::class, 'getVersions'])->name('versions');
        Route::get('/{id}/download',               [PartnerInsightsController::class, 'download'])->name('download');
        Route::delete('/{id}',                     [PartnerInsightsController::class, 'destroy'])->name('destroy');
    });

    // Firm Insights (admin / manager — no monthly limit, scoped to firm)
    Route::prefix('firm-insights')->name('firm-insights.')->group(function () {
        Route::get('/',                            [FirmInsightsController::class, 'index'])->name('index');
        Route::post('/generate',                   [FirmInsightsController::class, 'generate'])->name('generate');
        Route::post('/save-business-context',      [FirmInsightsController::class, 'saveBusinessContext'])->name('save-business-context');
        Route::get('/status/{id}',                 [FirmInsightsController::class, 'checkStatus'])->name('status');
        Route::get('/{encryptedId}/view',          [FirmInsightsController::class, 'view'])->name('view');
        Route::get('/{id}/data',                   [FirmInsightsController::class, 'getData'])->name('data');
        Route::get('/{id}/export-pdf',             [FirmInsightsController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}/download',               [FirmInsightsController::class, 'download'])->name('download');
        Route::delete('/{id}',                     [FirmInsightsController::class, 'destroy'])->name('destroy');
    });
});
