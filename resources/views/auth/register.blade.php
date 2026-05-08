<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — {{ $setting?->global_app_name ?? config('app.name') }}</title>
    <link href="{{ asset(config('assets.font_dm_sans')) }}" rel="stylesheet">
    <style>
:root {
  --primary:   {{ $setting?->primary_color ?? '#f06b21' }};
  --orange:    {{ $setting?->primary_color ?? '#f06b21' }};
  --orange-l:  #fff9f6;
  --blue:      #1a6aa8;
  --blue3:     #eff6ff;
  --green:     #10b981;
  --red:       #ef4444;
  --purple:    #7c3aed;
  --bg:        #f0f4f8;
  --border:    #e2e8f0;
  --text-1:    #1a202c;
  --text-2:    #6b7280;
  --text-3:    #9ca3af;
  --r-lg:      12px;
  --sh:        0 1px 4px rgba(0,0,0,.07);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
body {
    font-family: 'DM Sans', -apple-system, sans-serif;
    background: linear-gradient(160deg, #0d3d66 0%, #1a6aa8 50%, var(--primary) 100%);
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 32px 16px 56px;
}
.wrap { width: 100%; max-width: 740px }

/* Brand header */
.brand { text-align: center; margin-bottom: 24px }
.brand-logo { max-height: 44px; filter: brightness(0) invert(1); display: block; margin: 0 auto 10px }
.brand-name { font-size: 26px; font-weight: 700; color: #fff; letter-spacing: -.3px }
.brand-sub  { font-size: 13.5px; color: rgba(255,255,255,.72); margin-top: 5px }

/* Cards */
.card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid var(--border);
    padding: 22px 24px;
    margin-bottom: 14px;
    box-shadow: var(--sh);
}
.card-head {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 18px; padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
}
.card-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.card-head-text { font-size: 14px; font-weight: 600; color: var(--text-1) }
.card-head-sub  { font-size: 12px; color: var(--text-3); margin-top: 1px }

/* Grid */
.form-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px }
.f-group { margin-bottom: 14px }
.f-label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-1); margin-bottom: 5px }
.f-inp {
    width: 100%; height: 40px; border: 1.5px solid var(--border);
    border-radius: 8px; padding: 0 12px; font-size: 14px;
    font-family: inherit; color: var(--text-1); outline: none;
    transition: border-color .14s; background: #fff;
}
.f-inp:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(26,106,168,.07) }
.f-inp.is-invalid { border-color: var(--red) !important }
.f-err { font-size: 11.5px; color: var(--red); margin-top: 4px }
.f-hint { font-size: 11.5px; color: var(--text-3); margin-top: 3px }

/* Domain type toggle */
.domain-type-opt {
    flex: 1; display: flex; align-items: flex-start; gap: 10px;
    border: 2px solid var(--border); border-radius: 10px;
    padding: 12px 14px; cursor: pointer; transition: all .13s; background: #fff;
}
.domain-type-opt:hover { border-color: var(--orange) }
.domain-type-opt.selected { border-color: var(--orange); background: var(--orange-l) }

/* Plan cards */
.pkg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(175px, 1fr)); gap: 10px }
.pkg-option {
    display: flex; align-items: flex-start; gap: 10px;
    border: 2px solid var(--border); border-radius: 10px;
    padding: 12px 14px; cursor: pointer; transition: all .13s;
}
.pkg-option:hover { border-color: var(--orange) }
.pkg-option.selected { border-color: var(--orange); background: var(--orange-l) }
.pkg-option input[type=radio] { accent-color: var(--orange); flex-shrink: 0; margin-top: 2px }
.pkg-option-body { flex: 1 }

/* Subdomain input */
.subdomain-wrap {
    display: flex; align-items: stretch; border: 1.5px solid var(--border);
    border-radius: 8px; overflow: hidden; background: #fff;
}
.subdomain-wrap input {
    flex: 1; height: 40px; border: none; padding: 0 12px;
    font-size: 14px; font-family: inherit; color: var(--text-1); outline: none;
}
.subdomain-sfx {
    padding: 0 12px; display: flex; align-items: center;
    background: var(--bg); border-left: 1.5px solid var(--border);
    font-size: 12px; color: var(--text-3); white-space: nowrap; flex-shrink: 0;
}
.subdomain-wrap.is-invalid { border-color: var(--red) }

/* Error alert */
.err-alert {
    background: #fff5f5; border: 1px solid #fca5a5; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 14px; font-size: 13px; color: #991b1b;
}
.err-alert ul { padding-left: 18px; margin-top: 5px }

/* Submit / sign-in */
.submit-btn {
    width: 100%; height: 46px; background: var(--orange); color: #fff;
    border: none; border-radius: 10px; font-size: 15px; font-weight: 600;
    font-family: inherit; cursor: pointer; transition: opacity .14s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.submit-btn:hover { opacity: .88 }
.submit-btn:active { opacity: .78 }
.sign-in-link { text-align: center; font-size: 13px; color: var(--text-2); margin-top: 12px }
.sign-in-link a { color: var(--orange); font-weight: 600; text-decoration: none }

/* DNS panel */
#dns-panel { display: none }
</style>
</head>
<body>
<div class="wrap">

    {{-- Brand --}}
    <div class="brand">
        @if($setting?->logo)
            <img src="{{ asset('storage/'.$setting->logo) }}" alt="{{ $setting->global_app_name }}" class="brand-logo">
        @else
            <div class="brand-name">{{ $setting?->global_app_name ?? config('app.name') }}</div>
        @endif
        <div class="brand-sub">Create your account and start your free trial</div>
    </div>

    @if($errors->any())
    <div class="err-alert">
        <strong>Please fix the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}" id="reg-form" novalidate>
        @csrf
        <input type="hidden" name="domain_type" id="domain_type" value="{{ old('domain_type', 'subdomain') }}">

        {{-- ── SECTION 1: Company Details ──────────────────────────────────── --}}
        <div class="card">
            <div class="card-head">
                <div class="card-icon" style="background:#eff6ff">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#1a6aa8" stroke-width="1.8" width="16" height="16"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <div class="card-head-text">Company Details</div>
                    <div class="card-head-sub">Basic information about your firm</div>
                </div>
            </div>

            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Company Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="company_name" id="company_name"
                           class="f-inp {{ $errors->has('company_name') ? 'is-invalid' : '' }}"
                           value="{{ old('company_name') }}" placeholder="Acme Corp"
                           oninput="autoFillSubdomain(this.value)" required>
                    @error('company_name') <div class="f-err">{{ $message }}</div> @enderror
                </div>
                <div class="f-group">
                    <label class="f-label">Company Email <span style="color:var(--red)">*</span></label>
                    <input type="email" name="company_email" id="company_email"
                           class="f-inp {{ $errors->has('company_email') ? 'is-invalid' : '' }}"
                           value="{{ old('company_email') }}" placeholder="billing@acme.com"
                           autocomplete="off" required>
                    @error('company_email') <div class="f-err">{{ $message }}</div> @enderror
                </div>
                <div class="f-group" style="grid-column:span 2">
                    <label class="f-label">Phone Number <span style="color:var(--red)">*</span></label>
                    <input type="text" name="company_phone" id="company_phone"
                           class="f-inp {{ $errors->has('company_phone') ? 'is-invalid' : '' }}"
                           value="{{ old('company_phone') }}" placeholder="10-digit phone number"
                           maxlength="10" inputmode="numeric"
                           oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
                    @error('company_phone') <div class="f-err">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Access Method --}}
            <div style="margin-bottom:14px">
                <label class="f-label" style="margin-bottom:8px;display:block">
                    Access Method <span style="color:var(--red)">*</span>
                </label>
                <div style="display:flex;gap:10px">
                    <label class="domain-type-opt {{ old('domain_type','subdomain') === 'subdomain' ? 'selected' : '' }}"
                           id="dt-subdomain-label" onclick="domainTypeChanged('subdomain')" style="cursor:pointer">
                        <input type="radio" name="_domain_type_radio" value="subdomain"
                               {{ old('domain_type','subdomain') === 'subdomain' ? 'checked' : '' }}
                               style="accent-color:var(--orange);flex-shrink:0">
                        <div>
                            <div style="font-size:13px;font-weight:600;margin-bottom:2px">Subdomain</div>
                            <div style="font-size:11.5px;color:var(--text-3)">acme.{{ config('tenancy.root_domain','yourdomain.com') }}</div>
                        </div>
                    </label>
                    <label class="domain-type-opt {{ old('domain_type') === 'domain' ? 'selected' : '' }}"
                           id="dt-domain-label" onclick="domainTypeChanged('domain')" style="cursor:pointer">
                        <input type="radio" name="_domain_type_radio" value="domain"
                               {{ old('domain_type') === 'domain' ? 'checked' : '' }}
                               style="accent-color:var(--orange);flex-shrink:0">
                        <div>
                            <div style="font-size:13px;font-weight:600;margin-bottom:2px">Custom Domain</div>
                            <div style="font-size:11.5px;color:var(--text-3)">app.acmecorp.com</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-2col">
                {{-- Subdomain field --}}
                <div class="f-group" id="subdomain-group" {{ old('domain_type') === 'domain' ? 'style=display:none' : '' }}>
                    <label class="f-label">Subdomain <span style="color:var(--red)">*</span></label>
                    <div class="subdomain-wrap {{ $errors->has('subdomain') ? 'is-invalid' : '' }}" id="subdomain-wrap">
                        <input type="text" name="subdomain" id="subdomain"
                               value="{{ old('subdomain') }}" placeholder="acme"
                               maxlength="50"
                               oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'');this.dataset.manual='1'">
                        <span class="subdomain-sfx">.{{ config('tenancy.root_domain','yourdomain.com') }}</span>
                    </div>
                    @error('subdomain') <div class="f-err">{{ $message }}</div> @enderror
                    <div class="f-hint">Only letters, numbers, and hyphens. Cannot be changed later.</div>
                </div>

                {{-- Custom domain field --}}
                <div class="f-group" id="custom-domain-group" {{ old('domain_type') !== 'domain' ? 'style=display:none' : '' }}>
                    <label class="f-label">Custom Domain <span style="color:var(--red)">*</span></label>
                    <input type="text" name="custom_domain" id="custom_domain"
                           class="f-inp {{ $errors->has('custom_domain') ? 'is-invalid' : '' }}"
                           value="{{ old('custom_domain') }}" placeholder="app.acmecorp.com"
                           maxlength="253"
                           oninput="this.value=this.value.toLowerCase().replace(/\s/g,'')">
                    @error('custom_domain') <div class="f-err">{{ $message }}</div> @enderror
                    <div class="f-hint">Enter the exact domain visitors will use (no https).</div>
                </div>
            </div>

            {{-- DNS Configuration panel --}}
            <div id="dns-panel" {{ old('domain_type') === 'domain' ? '' : 'style=display:none' }}>
                <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:16px 18px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span style="font-size:13px;font-weight:600;color:#92400e">DNS Configuration Required</span>
                    </div>
                    <p style="font-size:12.5px;color:#78350f;margin-bottom:12px;line-height:1.6">
                        After creating your account, you must add these DNS records at your domain registrar to point your domain to this server. The domain will only work once both records are in place.
                    </p>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        {{-- A Record --}}
                        <div style="background:#fff;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;background:#f59e0b;color:#fff;padding:2px 7px;border-radius:4px">A Record</span>
                                <span style="font-size:12.5px;font-weight:500;color:#1e293b">Point domain to this server</span>
                            </div>
                            <div style="display:grid;grid-template-columns:70px 1fr;gap:4px 10px;font-size:12px">
                                <span style="color:#64748b;font-weight:600">Name:</span>
                                <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;color:#0f172a">@</code>
                                <span style="color:#64748b;font-weight:600">Type:</span>
                                <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;color:#0f172a">A</code>
                                <span style="color:#64748b;font-weight:600">Value:</span>
                                <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;color:#0f172a">{{ env('SERVER_IP', request()->server('SERVER_ADDR', 'YOUR_SERVER_IP')) }}</code>
                            </div>
                        </div>
                        {{-- TXT Record --}}
                        <div style="background:#fff;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;background:#8b5cf6;color:#fff;padding:2px 7px;border-radius:4px">TXT Record</span>
                                <span style="font-size:12.5px;font-weight:500;color:#1e293b">Domain ownership verification</span>
                            </div>
                            <div style="display:grid;grid-template-columns:70px 1fr;gap:4px 10px;font-size:12px">
                                <span style="color:#64748b;font-weight:600">Name:</span>
                                <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;color:#0f172a">_saas-verify</code>
                                <span style="color:#64748b;font-weight:600">Type:</span>
                                <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;color:#0f172a">TXT</code>
                                <span style="color:#64748b;font-weight:600">Value:</span>
                                <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;color:#0f172a">Generated after account is created</code>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:10px;font-size:11.5px;color:#92400e;line-height:1.5">
                        <strong>Note:</strong> DNS changes can take up to 48 hours to propagate. You can trigger domain verification from your dashboard once the records are live. SSL/HTTPS must be configured separately on your web server (e.g. Let's Encrypt via Certbot).
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Plan Selection ────────────────────────────────────── --}}
        @if($packages->count() > 0)
        <div class="card">
            <div class="card-head">
                <div class="card-icon" style="background:var(--orange-l)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="1.8" width="16" height="16"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <div class="card-head-text">Plan Selection</div>
                    <div class="card-head-sub">All plans include a free trial. Cancel anytime.</div>
                </div>
            </div>

            <div class="pkg-grid">
                @foreach($packages as $pkg)
                <label class="pkg-option {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'selected' : '' }}">
                    <input type="radio" name="package_id" value="{{ $pkg->id }}"
                           data-price="{{ $pkg->monthly_price }}"
                           {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'checked' : '' }}>
                    <div class="pkg-option-body">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:6px;flex-wrap:wrap">
                            <div style="font-size:13px;font-weight:600">{{ $pkg->name }}</div>
                            <div style="font-size:14px;font-weight:700;color:var(--orange)">${{ number_format($pkg->monthly_price,0) }}<span style="font-size:10px;font-weight:400;color:var(--text-3)">/mo</span></div>
                        </div>
                        @if($pkg->is_recommended)
                        <div style="margin-top:3px"><span style="font-size:10px;font-weight:600;background:#fff3e8;color:var(--orange);padding:1px 7px;border-radius:999px">Recommended</span></div>
                        @endif
                        @if($pkg->is_trial_allowed)
                        <div style="font-size:11px;color:var(--green);margin-top:4px">✓ {{ $pkg->trial_days }}-day free trial</div>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── SECTION 3: Admin Account ─────────────────────────────────────── --}}
        <div class="card">
            <div class="card-head">
                <div class="card-icon" style="background:#f5f3ff">
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="1.8" width="16" height="16"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <div class="card-head-text">Admin Account</div>
                    <div class="card-head-sub">Your personal login credentials</div>
                </div>
            </div>

            <div class="form-2col">
                <div class="f-group" style="grid-column:span 2">
                    <label class="f-label">Full Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="reg-name"
                           class="f-inp {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name') }}" placeholder="John Smith"
                           autocomplete="off" required>
                    @error('name') <div class="f-err">{{ $message }}</div> @enderror
                </div>
                <div class="f-group" style="grid-column:span 2">
                    <label class="f-label">Email Address <span style="color:var(--red)">*</span></label>
                    <input type="email" name="email" id="reg-email"
                           class="f-inp {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}" placeholder="john@acme.com"
                           autocomplete="off" required>
                    @error('email') <div class="f-err">{{ $message }}</div> @enderror
                </div>
                <div class="f-group">
                    <label class="f-label">Password <span style="color:var(--red)">*</span></label>
                    <div style="position:relative">
                        <input type="password" name="password" id="reg-password"
                               class="f-inp {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 characters" style="padding-right:40px"
                               autocomplete="new-password" minlength="8" required>
                        <button type="button" onclick="togglePw('reg-password','eye-pw')"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-3);cursor:pointer;padding:4px">
                            <svg id="eye-pw" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password') <div class="f-err">{{ $message }}</div> @enderror
                </div>
                <div class="f-group">
                    <label class="f-label">Confirm Password <span style="color:var(--red)">*</span></label>
                    <input type="password" name="password_confirmation" id="reg-pw-confirm"
                           class="f-inp" placeholder="Re-enter password"
                           autocomplete="new-password" minlength="8" required>
                    <div id="pw-match-msg" style="font-size:11.5px;margin-top:4px;display:none"></div>
                </div>
            </div>
        </div>

        {{-- ── SUBMIT ──────────────────────────────────────────────────────────── --}}
        <button type="submit" id="submit-btn" class="submit-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="20 6 9 17 4 12"/></svg>
            Create Account
        </button>
        <div class="sign-in-link">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </form>
</div>

<script>
// Clear credential fields on load to prevent browser auto-fill
(function() {
    ['reg-email','reg-password','reg-pw-confirm'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
})();

// Auto-fill subdomain from company name
function autoFillSubdomain(val) {
    var sub = document.getElementById('subdomain');
    if (sub && !sub.dataset.manual) {
        sub.value = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
}

// Domain type toggle
function domainTypeChanged(type) {
    document.getElementById('domain_type').value = type;
    var isSubdomain = type === 'subdomain';

    document.getElementById('subdomain-group').style.display      = isSubdomain ? '' : 'none';
    document.getElementById('custom-domain-group').style.display  = isSubdomain ? 'none' : '';
    document.getElementById('dns-panel').style.display            = isSubdomain ? 'none' : '';

    document.getElementById('dt-subdomain-label').classList.toggle('selected', isSubdomain);
    document.getElementById('dt-domain-label').classList.toggle('selected', !isSubdomain);

    document.querySelectorAll('[name=_domain_type_radio]').forEach(function(r) {
        r.checked = r.value === type;
    });
}

// Plan card selection + submit button text
document.querySelectorAll('.pkg-option input[type=radio]').forEach(function(r) {
    r.addEventListener('change', function() {
        document.querySelectorAll('.pkg-option').forEach(function(c) { c.classList.remove('selected'); });
        this.closest('.pkg-option').classList.add('selected');
        updateSubmitBtn();
    });
});

function updateSubmitBtn() {
    var sel = document.querySelector('.pkg-option input[type=radio]:checked');
    var price = sel ? parseFloat(sel.dataset.price || 0) : 0;
    var btn = document.getElementById('submit-btn');
    if (!btn) return;
    btn.innerHTML = (price > 0)
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Continue to Payment'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="20 6 9 17 4 12"/></svg> Create Account';
}
updateSubmitBtn();

// Password match indicator
document.getElementById('reg-pw-confirm').addEventListener('input', function() {
    var pw  = document.getElementById('reg-password').value;
    var msg = document.getElementById('pw-match-msg');
    if (!this.value) { msg.style.display = 'none'; return; }
    msg.style.display = 'block';
    if (this.value === pw) {
        msg.textContent = '✓ Passwords match';
        msg.style.color = 'var(--green)';
    } else {
        msg.textContent = '✗ Passwords do not match';
        msg.style.color = 'var(--red)';
    }
});

// Password visibility toggle
function togglePw(inputId, iconId) {
    var inp = document.getElementById(inputId);
    if (inp) inp.type = inp.type === 'password' ? 'text' : 'password';
}

// Init state if returning with validation errors
(function() {
    var dt = document.getElementById('domain_type').value;
    if (dt === 'domain') domainTypeChanged('domain');
})();
</script>
</body>
</html>
