@php
    // ── Resolve forgot-password page branding (inherits from login page settings) ──
    $bgType     = $branding?->login_bg_type     ?? 'gradient';
    $bgColor    = $branding?->login_bg_color    ?? '#0d3d66';
    $bgColorTo  = $branding?->login_bg_color_to ?? '#f06b21';
    $cardBg     = $branding?->login_card_bg     ?? '#ffffff';
    $btnColor   = $branding?->login_btn_color   ?? ($branding?->accent_color ?? '#f06b21');
    $heading    = $branding?->fp_heading        ?? 'Forgot your password?';
    $subheading = $branding?->fp_subheading     ?? 'Enter your email and we\'ll send a reset link.';

    if ($bgType === 'solid') {
        $bodyBg = "background:{$bgColor}";
    } elseif ($bgType === 'image' && $branding?->login_background) {
        $bodyBg = "background:url('" . asset('storage/' . $branding->login_background) . "') center/cover no-repeat";
    } else {
        $bodyBg = "background:linear-gradient(135deg,{$bgColor} 0%,{$bgColorTo} 130%)";
    }

    // ── Logo resolution ───────────────────────────────────────────────────────
    $logoSrc = null;
    if ($branding?->logo) {
        $logoSrc = asset('storage/' . $branding->logo);
    } elseif (!$branding && $setting?->logo) {
        $logoSrc = asset('storage/' . $setting->logo);
    }

    // ── Favicon resolution ────────────────────────────────────────────────────
    $faviconUrl = null;
    if ($branding?->favicon) {
        $faviconUrl = asset('storage/' . $branding->favicon);
    } elseif (!$branding && $setting?->favicon) {
        $faviconUrl = asset('storage/' . $setting->favicon);
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — {{ $branding ? ($branding->app_name ?? config('app.name')) : ($setting?->global_app_name ?? config('app.name')) }}</title>

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
.help-tip{display:inline-flex;align-items:center;justify-content:center;width:14px;height:14px;border-radius:50%;background:#e2e8f0;color:#64748b;font-size:8.5px;font-weight:700;font-style:italic;cursor:default;margin-left:4px;vertical-align:middle;position:relative;flex-shrink:0;line-height:1;font-family:Georgia,serif}
.help-tip::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;font-size:11px;font-weight:400;line-height:1.45;padding:5px 9px;border-radius:6px;width:max-content;max-width:220px;white-space:normal;pointer-events:none;opacity:0;transition:opacity .15s;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.help-tip:hover::after{opacity:1}
.login-logo-zone{text-align:center;margin-bottom:28px}
.login-logo-img{max-height:52px;max-width:200px;object-fit:contain;display:block;margin:0 auto}
.login-logo-placeholder{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:44px;min-width:180px;border:2px dashed #e2e8f0;border-radius:11px;padding:0 20px;color:#9ca3af;font-size:13px;font-weight:500;background:#fafbff}
.login-title{font-size:22px;font-weight:700;color:#1a202c;text-align:center;margin-bottom:4px;letter-spacing:-.3px}
.login-sub{font-size:13.5px;color:#6b7280;text-align:center;margin-bottom:28px}
.login-error{background:#fff5f5;border:1px solid #fecaca;border-radius:8px;padding:9px 12px;font-size:12.5px;color:#ef4444;margin-bottom:16px}
.login-success{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:9px 12px;font-size:12.5px;color:#16a34a;margin-bottom:16px}
.login-field{margin-bottom:16px}
.login-field label{display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px;letter-spacing:.02em}
.login-field input{width:100%;height:44px;border:1.5px solid #e2e8f0;border-radius:10px;padding:0 14px;font-size:14px;font-family:inherit;color:#1a202c;outline:none;transition:border-color .14s,box-shadow .14s;background:#fff}
.login-field input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,106,168,.12)}
.login-field input.is-error{border-color:#ef4444}
.login-field .err-msg{color:#ef4444;font-size:12px;margin-top:4px;display:block}
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

    {{-- ── Heading ─────────────────────────────────────────────────────────── --}}
    <div class="login-title">{{ $heading }} <span class="help-tip" data-tip="Title shown on reset password page.">i</span></div>
    <div class="login-sub">{{ $subheading }}</div>

    @if(session('error'))
        <div class="login-error">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="login-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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

        <button type="submit" class="login-btn" id="reset-btn">Send Reset Link</button>
    </form>
    <script>
    document.querySelector('form').addEventListener('submit', function() {
        var btn = document.getElementById('reset-btn');
        btn.disabled = true;
        btn.textContent = 'Loading...';
    });
    </script>

    <div class="login-divider">
        <a href="{{ route('login') }}">← Back to login</a>
    </div>
</div>
</body>
</html>
