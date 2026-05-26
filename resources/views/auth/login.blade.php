@php
    // ── Resolve login-page branding ──────────────────────────────────────────
    $bgType     = $branding?->login_bg_type     ?? 'gradient';
    $bgColor    = $branding?->login_bg_color    ?? '#0d3d66';
    $bgColorTo  = $branding?->login_bg_color_to ?? '#f06b21';
    $cardBg     = $branding?->login_card_bg     ?? '#ffffff';
    $btnColor   = $branding?->login_btn_color   ?? ($branding?->accent_color ?? '#f06b21');
    $heading    = $branding?->login_heading     ?? 'Welcome back';
    $subheading = $branding?->login_subheading
        ?? 'Sign in to ' . ($branding?->app_name ?? $setting?->global_app_name ?? config('app.name'));
 
    if ($bgType === 'solid') {
        $bodyBg = "background:{$bgColor}";
    } elseif ($bgType === 'image' && $branding?->login_background) {
        $bodyBg = "background:url('" . asset('storage/' . $branding->login_background) . "') center/cover no-repeat";
    } else {
        $bodyBg = "background:linear-gradient(135deg,{$bgColor} 0%,{$bgColorTo} 130%)";
    }

    // ── Logo resolution ───────────────────────────────────────────────────────
    // On a tenant domain: only use the company's own uploaded logo.
    // On the root/superadmin domain ($branding is null): fall back to global setting logo.
    $logoSrc = null;
    if ($branding?->logo) {
        $logoSrc = asset('storage/' . $branding->logo);        // company logo
    } elseif (!$branding && $setting?->logo) {
        $logoSrc = asset('storage/' . $setting->logo);         // global logo (root domain only)
    }
    // $logoSrc === null  →  show "Your Logo" placeholder, hide heading

    // ── Favicon resolution ────────────────────────────────────────────────────
    // On a tenant domain: only use the company's own favicon, never the platform default.
    // On root domain: use global setting favicon if available.
    $faviconUrl = null;
    if ($branding?->favicon) {
        $faviconUrl = asset('storage/' . $branding->favicon);
    } elseif (!$branding && $setting?->favicon) {
        $faviconUrl = asset('storage/' . $setting->favicon);
    }
    // $faviconUrl === null  →  emit an empty data: URI to suppress the browser's default icon
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — {{ $branding ? ($branding->app_name ?? config('app.name')) : ($setting?->global_app_name ?? config('app.name')) }}</title>

    {{-- Favicon: explicit empty URI prevents browser showing its own default icon --}}
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" href="data:,">
    @endif
 
    <link href="{{ asset(config('assets.font_dm_sans')) }}" rel="stylesheet">
<link href="{{ asset(config('assets.font_inter')) }}" rel="stylesheet">
 
    <style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --primary:{{ $branding?->primary_color ?? '#1a6aa8' }};
  --btn-color:{{ $btnColor }};
  --card-bg:{{ $cardBg }};
  --font:'{{ $branding?->font_family ?? 'DM Sans' }}', -apple-system, sans-serif;
}
body{font-family:var(--font);min-height:100vh;display:flex;align-items:center;justify-content:center;{{ $bodyBg }}}
.login-card{background:var(--card-bg);border-radius:20px;padding:44px 44px 38px;width:420px;max-width:calc(100vw - 32px);box-shadow:0 24px 80px rgba(0,0,0,.22)}
.login-logo-zone{text-align:center;margin-bottom:28px}
.login-logo-img{max-height:52px;max-width:200px;object-fit:contain;display:block;margin:0 auto}
.login-logo-placeholder{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:44px;min-width:180px;border:2px dashed #e2e8f0;border-radius:11px;padding:0 20px;color:#9ca3af;font-size:13px;font-weight:500;background:#fafbff}
.login-title{font-size:22px;font-weight:700;color:#1a202c;text-align:center;margin-bottom:4px;letter-spacing:-.3px}
.login-sub{font-size:13.5px;color:#6b7280;text-align:center;margin-bottom:28px}
.login-error{background:#fff5f5;border:1px solid #fecaca;border-radius:8px;padding:9px 12px;font-size:12.5px;color:#ef4444;margin-bottom:16px}
.login-field{margin-bottom:16px}
.login-field label{display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px;letter-spacing:.02em}
.login-field input{width:100%;height:44px;border:1.5px solid #e2e8f0;border-radius:10px;padding:0 14px;font-size:14px;font-family:inherit;color:#1a202c;outline:none;transition:border-color .14s,box-shadow .14s;background:#fff}
.login-field input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,106,168,.12)}
.login-field input.is-error{border-color:#ef4444}
.login-field .err-msg{color:#ef4444;font-size:12px;margin-top:4px;display:block}
/* Password wrapper */
.pw-wrap{position:relative}
.pw-wrap input{padding-right:44px}
.pw-eye{position:absolute;right:0;top:0;width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:none;border:none;cursor:pointer;color:#9ca3af;transition:color .14s;padding:0}
.pw-eye:hover{color:#6b7280}
.pw-eye svg{width:17px;height:17px;stroke:currentColor;stroke-width:2;fill:none;pointer-events:none}
.login-extras{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.login-remember{display:flex;align-items:center;gap:7px;font-size:13px;color:#6b7280;cursor:pointer}
.login-remember input{accent-color:var(--btn-color);width:14px;height:14px}
.login-forgot a{font-size:12.5px;color:var(--primary);text-decoration:none;font-weight:500}
.login-forgot a:hover{text-decoration:underline}
.login-btn{width:100%;height:46px;background:var(--btn-color);color:#fff;border:none;border-radius:11px;font-size:15px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .14s,box-shadow .14s,opacity .14s;letter-spacing:.02em}
.login-btn:hover{opacity:.88;box-shadow:0 6px 20px rgba(0,0,0,.2)}
.login-btn:active{transform:translateY(1px)}
.login-divider{text-align:center;margin-top:22px;font-size:12.5px;color:#9ca3af}
.login-divider a{color:var(--primary);font-weight:600;text-decoration:none}
.login-divider a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="login-card">

    {{-- ── Logo zone ──────────────────────────────────────────────────────── --}}
    <div class="login-logo-zone">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Logo" class="login-logo-img">
        @else
            <div class="login-logo-placeholder">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Your Logo
            </div>
        @endif
    </div>

    {{-- ── Heading: only shown when a logo has been uploaded ─────────────── --}}
    @if($logoSrc)
        <div class="login-title">{{ $heading }}</div>
        @if($branding?->login_subheading)
            <div class="login-sub">{{ $branding->login_subheading }}</div>
        @endif
    @endif
 
    @if($errors->any())
<div class="login-error">{{ $errors->first() }}</div>
    @endif
    @if(session('error'))
<div class="login-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}" style="{{ $logoSrc ? 'margin-top:0' : 'margin-top:4px' }}">
        @csrf
 
        <div class="login-field">
<label for="email">Email Address</label>
<input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="you@yourfirm.com"
                   class="{{ $errors->has('email') ? 'is-error' : '' }}"
                   autocomplete="email" autofocus required>
            @error('email')<span class="err-msg">{{ $message }}</span>@enderror
</div>
 
        <div class="login-field">
            <label for="password">Password</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password"
                       placeholder="Enter your password"
                       class="{{ $errors->has('password') ? 'is-error' : '' }}"
                       autocomplete="current-password" required>
                <button type="button" class="pw-eye" id="pw-toggle" aria-label="Show password" tabindex="-1">
                    {{-- Eye open (shown when password is hidden) --}}
                    <svg id="eye-show" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    {{-- Eye crossed (shown when password is visible) --}}
                    <svg id="eye-hide" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
            @error('password')<span class="err-msg">{{ $message }}</span>@enderror
</div>
 
        <div class="login-extras">
<label class="login-remember">
<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember me
</label>
<div class="login-forgot">
<a href="{{ route('password.request') }}">Forgot password?</a>
</div>
</div>
 
        <button type="submit" class="login-btn">Sign In</button>
</form>
 
    @if($setting?->allow_registration)
<div class="login-divider">
            Don't have an account? <a href="{{ route('register') }}">Create one free</a>
</div>
    @endif
</div>

<script>
(function () {
    var toggle   = document.getElementById('pw-toggle');
    var input    = document.getElementById('password');
    var eyeShow  = document.getElementById('eye-show');
    var eyeHide  = document.getElementById('eye-hide');

    toggle.addEventListener('click', function () {
        var isPassword = input.type === 'password';
        input.type      = isPassword ? 'text' : 'password';
        eyeShow.style.display = isPassword ? 'none'  : '';
        eyeHide.style.display = isPassword ? ''      : 'none';
        toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
}());
</script>
</body>
</html> 