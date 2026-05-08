<?php

namespace App\Http\Controllers\Auth;

use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\GlobalSetting;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    // ── Login ─────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        $branding = company()?->branding;
        $setting  = global_setting();

        return view('auth.login', compact('branding', 'setting'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $company   = company();
        $companyId = $company?->id;

        // Find user scoped to this tenant when a subdomain/domain is identified.
        // When accessing via the root URL (no tenant context), allow any user to
        // login — SuperAdmin OR any company admin — useful for localhost dev and
        // single-domain deployments.
        $user = User::where('email', $request->email)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'These credentials do not match our records.']);
        }

        if ($user->status === 'inactive') {
            return back()->withErrors(['email' => 'Your account has been deactivated. Contact support.']);
        }

        if (!$user->admin_approval) {
            return back()->withErrors(['email' => 'Your account is pending admin approval.']);
        }

        // 2FA check
        if ($user->two_factor_code) {
            session(['2fa_user_id' => $user->id]);
            return redirect()->route('2fa.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login' => now()]);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath($user));
    }

    // ── Two-Factor Auth ───────────────────────────────────────────────────────

    public function showTwoFactor()
    {
        abort_unless(session()->has('2fa_user_id'), 403);
        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $userId = session('2fa_user_id');
        $user   = User::findOrFail($userId);

        if (!$user->isValidTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        $user->forceFill(['two_factor_code' => null, 'two_factor_expires_at' => null])->save();
        Auth::login($user);
        session()->forget('2fa_user_id');
        $user->update(['last_login' => now()]);

        return redirect()->intended($this->redirectPath($user));
    }

    // ── Registration ──────────────────────────────────────────────────────────

    public function showRegister()
    {
        $setting = global_setting();
        abort_unless($setting?->allow_registration, 403, 'Registration is currently disabled.');

        $packages = Package::active()->orderBy('sort')->get();
        return view('auth.register', compact('packages', 'setting'));
    }

    public function register(Request $request)
    {
        $setting = global_setting();
        abort_unless($setting?->allow_registration, 403);

        $domainType = $request->input('domain_type', 'subdomain');

        $request->validate([
            'company_name'  => ['required','string','min:2','max:255'],
            'company_phone' => 'required|digits:10',
            'domain_type'   => ['required','in:subdomain,domain'],
            'subdomain'     => $domainType === 'subdomain'
                                ? ['required','string','min:3','max:63','regex:/^[a-z0-9-]+$/','unique:companies,subdomain']
                                : ['nullable'],
            'custom_domain' => $domainType === 'domain'
                                ? ['required','string','max:253','regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/','unique:companies,custom_domain']
                                : ['nullable'],
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'package_id'    => 'nullable|exists:packages,id',
        ], [
            'subdomain.required' => 'Subdomain is required.',
            'subdomain.regex'    => 'Only lowercase letters, numbers, and hyphens are allowed.',
            'subdomain.unique'   => 'This subdomain is already taken.',
            'custom_domain.required' => 'Custom domain is required.',
            'custom_domain.regex'    => 'Please enter a valid domain (e.g. app.acmecorp.com).',
            'custom_domain.unique'   => 'This domain is already in use.',
            'company_phone.required' => 'Phone number is required.',
            'company_phone.digits'   => 'Phone number must be 10 digits.',
        ]);

        $package = $request->package_id
            ? Package::find($request->package_id)
            : Package::active()->orderBy('sort')->first();

        // Determine if Stripe payment is required
        // Free plans or plans with no price go straight through.
        // Paid plans go through Stripe checkout (card required even for trials).
        $stripeReady     = $this->stripeIsConfigured();
        $requiresPayment = $package
            && !$package->is_free
            && $package->monthly_price > 0
            && $stripeReady;

        \Illuminate\Support\Facades\Log::info('Registration payment check', [
            'package'          => $package?->name,
            'is_free'          => $package?->is_free,
            'monthly_price'    => $package?->monthly_price,
            'stripe_ready'     => $stripeReady,
            'requires_payment' => $requiresPayment,
        ]);

        $verificationToken = $domainType === 'domain' ? \Illuminate\Support\Str::random(40) : null;

        // Create company — inactive until payment confirmed
        $company = Company::create([
            'company_name'              => $request->company_name,
            'app_name'                  => $request->company_name,
            'company_email'             => $request->email,
            'company_phone'             => $request->company_phone,
            'domain_type'               => $domainType,
            'subdomain'                 => $domainType === 'subdomain' ? Str::slug($request->subdomain) : null,
            'custom_domain'             => $domainType === 'domain' ? strtolower(trim($request->custom_domain)) : null,
            'domain_verification_token' => $verificationToken,
            'status'                    => $requiresPayment ? 'inactive' : 'active',
            'package_id'                => $package?->id,
            'trial_ends_at' => (!$requiresPayment && $package?->is_trial_allowed)
                ? now()->addDays($package->trial_days)
                : null,
        ]);

        CompanyBranding::create(['company_id' => $company->id]);

        // Create subscription record (trialing if no payment required)
        if ($package && !$requiresPayment) {
            Subscription::create([
                'company_id'           => $company->id,
                'package_id'           => $package->id,
                'status'               => 'trialing',
                'billing_cycle'        => 'monthly',
                'trial_ends_at'        => $company->trial_ends_at,
                'current_period_start' => now(),
                'current_period_end'   => $company->trial_ends_at ?? now()->addDays(14),
            ]);
        }

        // Create admin user — inactive until payment confirmed
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::create([
            'company_id'        => $company->id,
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'status'            => $requiresPayment ? 'inactive' : 'active',
            'admin_approval'    => true,
            'email_verified_at' => ($setting?->email_verification || $requiresPayment) ? null : now(),
        ]);
        $user->assignRole($role);

        // ── Path A: Redirect to Stripe checkout ──────────────────────────────────
        if ($requiresPayment) {
            try {
                $stripe  = new \App\Services\StripeService();
                $session = $stripe->createCheckoutSession(
                    company: $company,
                    package: $package,
                    billingCycle: 'monthly',
                    successUrl: route('register.payment.success') . '?session_id={CHECKOUT_SESSION_ID}&company_id=' . $company->id,
                    cancelUrl:  route('register.payment.cancel', $company->id),
                );

                \Illuminate\Support\Facades\Log::info('Registration: redirecting to Stripe checkout', [
                    'company_id' => $company->id,
                    'session_id' => $session->id,
                    'url'        => $session->url,
                ]);

                // Store credentials so we can log the user in after payment
                session(['pending_reg_user_id' => $user->id, 'pending_reg_company_id' => $company->id]);

                return redirect($session->url);

            } catch (\Throwable $e) {
                // Stripe not reachable — log the error and fall back to trial activation
                \Illuminate\Support\Facades\Log::warning('Stripe checkout failed during registration: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // ── Path B: Activate immediately (free / trial / Stripe unavailable) ─────
        $this->activateRegistration($company, $user, $package, $setting);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account has been created.');
    }

    /** Activate a just-registered company and user. */
    private function activateRegistration(
        \App\Models\Company $company,
        \App\Models\User $user,
        ?\App\Models\SuperAdmin\Package $package,
        ?\App\Models\GlobalSetting $setting
    ): void {
        $company->update([
            'status'        => 'active',
            'trial_ends_at' => $package?->is_trial_allowed ? now()->addDays($package->trial_days) : null,
        ]);

        $user->update([
            'status'            => 'active',
            'email_verified_at' => $setting?->email_verification ? null : now(),
        ]);

        if ($package && !Subscription::where('company_id', $company->id)->exists()) {
            Subscription::create([
                'company_id'           => $company->id,
                'package_id'           => $package->id,
                'status'               => 'trialing',
                'billing_cycle'        => 'monthly',
                'trial_ends_at'        => $company->fresh()->trial_ends_at,
                'current_period_start' => now(),
                'current_period_end'   => $company->fresh()->trial_ends_at ?? now()->addDays(14),
            ]);
        }

        // Welcome email
        try {
            \App\Jobs\SendEmailJob::dispatch(
                toEmail:      $user->email,
                templateSlug: 'user_welcome',
                placeholders: [
                    '{{name}}'      => $user->name,
                    '{{email}}'     => $user->email,
                    '{{app_name}}' => $company->app_name,
                    '{{login_url}}' => tenant_url($company->subdomain, 'login'),
                ],
                companyId: $company->id,
            )->onQueue('emails');
        } catch (\Throwable) {}
    }

    /** Called by Stripe after successful checkout. Activates company + logs user in. */
    public function registrationPaymentSuccess(\Illuminate\Http\Request $request)
    {
        $companyId = $request->input('company_id') ?? session('pending_reg_company_id');
        $userId    = session('pending_reg_user_id');

        $company = $companyId ? \App\Models\Company::withoutGlobalScopes()->find($companyId) : null;

        if (!$company) {
            return redirect()->route('login')->with('error', 'Could not find your account. Please contact support.');
        }

        // Activate company + user
        $company->update(['status' => 'active']);
        $user = \App\Models\User::withoutGlobalScopes()->where('company_id', $company->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->first();

        if ($user) {
            $user->update(['status' => 'active', 'email_verified_at' => $user->email_verified_at ?? now()]);
            session()->forget(['pending_reg_user_id', 'pending_reg_company_id']);
            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect()->route('dashboard')->with('success', 'Payment successful! Welcome to ' . $company->app_name . '!');
    }

    /** Called when user cancels Stripe checkout — clean up the inactive account. */
    public function registrationPaymentCancel(\Illuminate\Http\Request $request, int $id)
    {
        // Delete incomplete registration to avoid orphaned records
        $company = \App\Models\Company::withoutGlobalScopes()
            ->where('id', $id)
            ->where('status', 'inactive')
            ->first();

        if ($company) {
            // Delete users, branding, subscriptions (cascade should handle it, but be explicit)
            \App\Models\User::withoutGlobalScopes()->where('company_id', $company->id)->delete();
            \App\Models\CompanyBranding::where('company_id', $company->id)->delete();
            \App\Models\SuperAdmin\Subscription::where('company_id', $company->id)->delete();
            $company->delete();
        }

        session()->forget(['pending_reg_user_id', 'pending_reg_company_id']);

        return redirect()->route('register')
            ->with('error', 'Payment was cancelled. Please try again.');
    }

    /** Check whether Stripe is configured with a valid secret key. */
    private function stripeIsConfigured(): bool
    {
        try {
            $creds = \App\Models\SuperAdmin\GlobalPaymentGatewayCredentials::first();
            return (bool) $creds?->getActiveSecret();
        } catch (\Throwable) {
            return false;
        }
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ── Password Reset ────────────────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.passwords.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Password reset link sent to your email.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.passwords.reset', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successfully.')
            : back()->withErrors(['email' => __($status)]);
    }

    private function redirectPath(User $user): string
    {
        return $user->is_superadmin
            ? route('superadmin.dashboard')
            : route('dashboard');
    }
}
