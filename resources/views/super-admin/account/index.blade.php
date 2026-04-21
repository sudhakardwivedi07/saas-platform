@extends('layouts.superadmin.app')
@section('title','My Account')
@section('page-title','My Account')

@section('content')
<div class="ph">
    <div>
        <h1>My Account</h1>
        <p>Manage your profile &amp; global platform settings</p>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-profile">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
    </button>
    <button class="tab-btn" data-tab="tab-global">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        Global Settings
    </button>
</div>

{{-- ── TAB 1: PROFILE ───────────────────────────────────── --}}
<div id="tab-profile" class="tab-panel active">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start">

        {{-- Organisation Profile Card --}}
        <div class="acc-card">
            <div class="acc-title">Organisation Profile</div>

            <div style="display:flex;align-items:center;gap:16px;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--border)">
                <div style="width:54px;height:54px;border-radius:12px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;flex-shrink:0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                </div>
                <div>
                    <div style="font-size:15px;font-weight:600">{{ auth()->user()->name }}</div>
                    <div style="font-size:12px;color:var(--text-3)">{{ auth()->user()->email }}</div>
                    <span class="badge b-orange" style="margin-top:4px;font-size:10.5px">Super Admin</span>
                </div>
            </div>

            <form method="POST" action="{{ route('superadmin.account.update') }}" id="profile-form">
                @csrf
                <div class="f-group">
                    <label class="f-label">Organisation Name <span class="req-star">*</span></label>
                    <input type="text" name="name" class="f-inp"
                           value="{{ old('name', auth()->user()->name) }}">
                </div>
                <div class="f-group">
                    <label class="f-label">Admin Email <span class="req-star">*</span></label>
                    <input type="email" name="email" class="f-inp"
                           value="{{ old('email', auth()->user()->email) }}">
                </div>
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Phone</label>
                        <input type="tel" name="phone" class="f-inp" placeholder="+1 (555) 000-0000">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Website</label>
                        <input type="url" name="website" class="f-inp" placeholder="https://yoursite.com">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>
        </div>

        {{-- Password & Security Card --}}
        <div class="acc-card">
            <div class="acc-title">Password &amp; Security</div>

            <form method="POST" action="{{ route('superadmin.account.update') }}" id="password-form">
                @csrf
                <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                <input type="hidden" name="email" value="{{ auth()->user()->email }}">

                <div class="f-group">
                    <label class="f-label">Current Password <span class="req-star">*</span></label>
                    <input type="password" name="current_password" class="f-inp" placeholder="••••••••">
                </div>
                <div class="f-group">
                    <label class="f-label">New Password <span class="req-star">*</span></label>
                    <input type="password" name="password" class="f-inp" placeholder="Min. 8 characters">
                </div>
                <div class="f-group">
                    <label class="f-label">Confirm New Password <span class="req-star">*</span></label>
                    <input type="password" name="password_confirmation" class="f-inp" placeholder="••••••••">
                </div>

                <div style="padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;font-size:12.5px;color:#92400e;margin-bottom:16px">
                    <strong>Security tip:</strong> Use at least 12 characters with a mix of letters, numbers, and symbols.
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>

    </div>
</div>

{{-- ── TAB 2: GLOBAL SETTINGS ───────────────────────────── --}}
<div id="tab-global" class="tab-panel">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:14px;align-items:start">

        {{-- Settings Form --}}
        <div class="acc-card">
            <div class="acc-title">Platform Appearance</div>

            <form id="global-settings-form">
                @csrf

                {{-- Logo Upload --}}
                <div class="f-group">
                    <label class="f-label">Platform Logo</label>
                    <div style="display:flex;align-items:center;gap:14px;padding:14px;border:2px dashed var(--border);border-radius:10px;cursor:pointer" onclick="document.getElementById('logo-upload').click()">
                        @if($setting?->logo)
                        <img src="{{ asset('storage/'.$setting->logo) }}" alt="Logo" style="max-height:40px;max-width:120px">
                        @else
                        <div style="width:44px;height:44px;border-radius:10px;background:var(--blue);display:flex;align-items:center;justify-content:center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        @endif
                        <div>
                            <div style="font-size:13px;font-weight:500">Click to upload logo</div>
                            <div style="font-size:12px;color:var(--text-3)">PNG, SVG or JPG · Max 2MB · Recommended 200×50px</div>
                        </div>
                    </div>
                    <input type="file" id="logo-upload" name="logo" accept="image/*,.svg" style="display:none"
                           onchange="previewLogo(this)">
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Platform Name <span class="req-star">*</span></label>
                        <input type="text" name="global_app_name" id="prev-name" class="f-inp"
                               value="{{ $setting?->global_app_name ?? config('app.name') }}"
                               oninput="updatePreview()">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Tagline</label>
                        <input type="text" name="tagline" id="prev-tagline" class="f-inp"
                               value="{{ $setting?->tagline ?? 'SuperAdmin Console' }}"
                               placeholder="SuperAdmin Console" oninput="updatePreview()">
                    </div>
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Primary Color (Sidebar)</label>
                        <div style="display:flex;align-items:center;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:#fff">
                            <input type="color" name="primary_color" id="primary-color"
                                   value="{{ $setting?->primary_color ?? '#0a2540' }}"
                                   oninput="document.getElementById('primary-hex').value=this.value;updatePreview()"
                                   style="width:44px;height:42px;border:none;border-right:1.5px solid var(--border);padding:4px 6px;cursor:pointer;background:transparent;flex-shrink:0">
                            <input type="text" id="primary-hex" class="f-inp"
                                   value="{{ $setting?->primary_color ?? '#0a2540' }}"
                                   oninput="document.getElementById('primary-color').value=this.value;updatePreview()"
                                   style="border:none;border-radius:0;font-family:monospace;font-size:13px;flex:1;height:42px">
                        </div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Accent Color (CTA / Buttons)</label>
                        <div style="display:flex;align-items:center;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:#fff">
                            <input type="color" name="accent_color" id="accent-color"
                                   value="{{ $setting?->accent_color ?? '#F06B21' }}"
                                   oninput="document.getElementById('accent-hex').value=this.value;updatePreview()"
                                   style="width:44px;height:42px;border:none;border-right:1.5px solid var(--border);padding:4px 6px;cursor:pointer;background:transparent;flex-shrink:0">
                            <input type="text" id="accent-hex" class="f-inp"
                                   value="{{ $setting?->accent_color ?? '#F06B21' }}"
                                   oninput="document.getElementById('accent-color').value=this.value;updatePreview()"
                                   style="border:none;border-radius:0;font-family:monospace;font-size:13px;flex:1;height:42px">
                        </div>
                    </div>
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Favicon</label>
                        <input type="file" name="favicon" accept="image/*" class="f-inp" style="padding:7px">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Default Firm Domain</label>
                        <input type="text" name="root_domain" class="f-inp"
                               value="{{ config('tenancy.root_domain') }}"
                               placeholder="app.yourdomain.com">
                    </div>
                </div>

                <div style="height:1px;background:var(--border);margin:18px 0"></div>
                <div style="font-size:13px;font-weight:600;margin-bottom:12px">Platform Configuration</div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Contact Email <span class="req-star">*</span></label>
                        <input type="email" name="company_email" class="f-inp"
                               value="{{ $setting?->company_email }}" placeholder="support@yourapp.com">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Timezone</label>
                        <select name="timezone" class="f-sel">
                            @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ ($setting?->timezone ?? 'UTC') === $tz ? 'selected':'' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Currency</label>
                        <select name="currency_code" class="f-sel">
                            @foreach(['USD'=>'USD — US Dollar','EUR'=>'EUR — Euro','GBP'=>'GBP — British Pound','INR'=>'INR — Indian Rupee'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($setting?->currency_code ?? 'USD') === $v ? 'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Date Format</label>
                        <select name="date_format" class="f-sel">
                            @foreach(['Y-m-d'=>'YYYY-MM-DD','d/m/Y'=>'DD/MM/YYYY','m/d/Y'=>'MM/DD/YYYY'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($setting?->date_format ?? 'Y-m-d') === $v ? 'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input type="hidden" name="locale" value="{{ $setting?->locale ?? 'en' }}">

                <div style="height:1px;background:var(--border);margin:18px 0"></div>
                <div style="font-size:13px;font-weight:600;margin-bottom:12px">Feature Toggles</div>

                @foreach([
                    ['allow_registration','Allow Public Registration','Users can self-register without an invite'],
                    ['email_verification','Require Email Verification','New users must verify email before access'],
                    ['maintenance_mode','Maintenance Mode','Show maintenance page to all tenants'],
                ] as [$field, $label, $hint])
                <label style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;margin-bottom:8px">
                    <div>
                        <div style="font-size:13px;font-weight:500">{{ $label }}</div>
                        <div style="font-size:12px;color:var(--text-3)">{{ $hint }}</div>
                    </div>
                    <input type="checkbox" name="{{ $field }}" value="1" style="accent-color:var(--orange);width:16px;height:16px"
                        {{ $setting?->$field ? 'checked':'' }}>
                </label>
                @endforeach

                <div style="padding-top:16px;border-top:1px solid var(--border);margin-top:8px">
                    <button type="submit" class="btn btn-primary">Save Global Settings</button>
                </div>
            </form>
        </div>

        {{-- Live Preview Panel --}}
        <div>
            <div class="acc-card" style="padding:0;overflow:hidden">
                <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:13px;font-weight:600">Live Preview</div>

                {{-- Mini sidebar preview --}}
                <div style="display:flex;height:340px;overflow:hidden">
                    {{-- Sidebar --}}
                    <div id="preview-sidebar" style="width:130px;background:var(--preview-primary);display:flex;flex-direction:column;flex-shrink:0">
                        <div style="padding:12px 10px;border-bottom:1px solid rgba(255,255,255,.08)">
                            <div id="preview-appname" style="color:#fff;font-size:11px;font-weight:700;line-height:1.2">{{ $setting?->global_app_name ?? config('app.name') }}</div>
                            <div id="preview-tagline" style="font-size:9px;color:rgba(255,255,255,.4);margin-top:3px">{{ $setting?->tagline ?? 'SuperAdmin Console' }}</div>
                        </div>
                        <div style="padding:8px 6px;display:flex;flex-direction:column;gap:1px;flex:1">
                            @foreach(['Dashboard','Firms','Finance','Settings','My Account'] as $item)
                            <div class="{{ $loop->first ? 'preview-nav-active' : '' }}" style="padding:7px 8px;border-radius:6px;font-size:10px;color:rgba(255,255,255,.{{ $loop->first ? '9' : '5' }})">{{ $item }}</div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Main area --}}
                    <div style="flex:1;background:#f1f5f9;padding:10px;overflow:hidden">
                        {{-- Topbar --}}
                        <div id="preview-topbar" style="background:#fff;border-radius:7px;padding:7px 10px;display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
                            <div style="font-size:10px;color:#94a3b8">Dashboard</div>
                            <div id="preview-btn" style="width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--preview-accent)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" width="11" height="11"><path d="M18.36 6.64a9 9 0 11-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                            </div>
                        </div>
                        {{-- KPI cards --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:8px">
                            @foreach(['Total Firms','Active Firms','Revenue','Users'] as $lbl)
                            <div style="background:#fff;border-radius:7px;padding:8px 10px;box-shadow:0 1px 2px rgba(0,0,0,.05)">
                                <div style="font-size:8.5px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">{{ $lbl }}</div>
                                <div style="font-size:15px;font-weight:700;color:#0f172a;margin-top:3px;border-left:3px solid var(--preview-accent);padding-left:6px">—</div>
                            </div>
                            @endforeach
                        </div>
                        {{-- Chart placeholder --}}
                        <div style="background:#fff;border-radius:7px;padding:8px;box-shadow:0 1px 2px rgba(0,0,0,.05)">
                            <div style="font-size:8.5px;color:#94a3b8;margin-bottom:6px">Revenue Overview</div>
                            <div style="display:flex;align-items:flex-end;gap:4px;height:38px">
                                @foreach([40, 65, 50, 80, 70, 90] as $h)
                                <div style="flex:1;background:var(--preview-accent);opacity:.7;border-radius:3px 3px 0 0;height:{{ $h }}%"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding:10px 16px;border-top:1px solid var(--border);font-size:11px;color:var(--text-3)">
                    Preview updates as you type
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<style>
:root {
  --preview-primary: {{ $setting?->primary_color ?? '#0a2540' }};
  --preview-accent:  {{ $setting?->accent_color  ?? '#F06B21' }};
}
.req-star { color: #e53e3e; margin-left: 2px; }
.f-error  { color: #e53e3e; font-size: 12px; margin-top: 4px; display: block; }
.f-inp.inp-error { border-color: #e53e3e !important; }
.preview-nav-active {
  background: color-mix(in srgb, var(--preview-accent) 22%, transparent) !important;
  color: var(--preview-accent) !important;
}
</style>
<script>
// ── Validation helpers ───────────────────────────────────────
function _showErr(input, msg) {
    input.classList.add('inp-error');
    let err = input.parentElement.querySelector('.f-error');
    if (!err) { err = document.createElement('span'); err.className = 'f-error'; input.parentElement.appendChild(err); }
    err.textContent = msg;
}
function _clearErr(input) {
    input.classList.remove('inp-error');
    const err = input.parentElement.querySelector('.f-error');
    if (err) err.textContent = '';
}

// ── Profile form validation ─────────────────────────────────
function validateProfileForm() {
    const name  = document.querySelector('#profile-form [name="name"]');
    const email = document.querySelector('#profile-form [name="email"]');
    [name, email].forEach(_clearErr);
    let ok = true;
    if (!name.value.trim()) { _showErr(name, 'Organisation name is required.'); ok = false; }
    if (!email.value.trim()) { _showErr(email, 'Admin email is required.'); ok = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { _showErr(email, 'Please enter a valid email address.'); ok = false; }
    return ok;
}

// ── Password form validation ────────────────────────────────
function validatePasswordForm() {
    const cur  = document.querySelector('#password-form [name="current_password"]');
    const pwd  = document.querySelector('#password-form [name="password"]');
    const conf = document.querySelector('#password-form [name="password_confirmation"]');
    [cur, pwd, conf].forEach(_clearErr);
    let ok = true;
    if (!cur.value.trim())  { _showErr(cur,  'Current password is required.'); ok = false; }
    if (!pwd.value.trim())  { _showErr(pwd,  'New password is required.'); ok = false; }
    else if (pwd.value.length < 8) { _showErr(pwd, 'Password must be at least 8 characters.'); ok = false; }
    if (!conf.value.trim()) { _showErr(conf, 'Please confirm your new password.'); ok = false; }
    else if (pwd.value && pwd.value !== conf.value) { _showErr(conf, 'Passwords do not match.'); ok = false; }
    return ok;
}

// ── Global settings validation ──────────────────────────────
function validateGlobalForm() {
    const pname = document.querySelector('#global-settings-form [name="global_app_name"]');
    const email = document.querySelector('#global-settings-form [name="company_email"]');
    [pname, email].forEach(_clearErr);
    let ok = true;
    if (!pname.value.trim()) { _showErr(pname, 'Platform name is required.'); ok = false; }
    if (!email.value.trim()) { _showErr(email, 'Contact email is required.'); ok = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { _showErr(email, 'Please enter a valid email address.'); ok = false; }
    return ok;
}

// ── Bind profile & password submit ─────────────────────────
document.getElementById('profile-form')?.addEventListener('submit', function(e) {
    if (!validateProfileForm()) e.preventDefault();
});
document.getElementById('password-form')?.addEventListener('submit', function(e) {
    if (!validatePasswordForm()) e.preventDefault();
});
// Clear error on input
document.querySelectorAll('#profile-form .f-inp, #password-form .f-inp, #global-settings-form .f-inp').forEach(inp => {
    inp.addEventListener('input', () => _clearErr(inp));
});
</script>
<script>
function updatePreview() {
    const name    = document.getElementById('prev-name')?.value    || 'CFO Insights';
    const tagline = document.getElementById('prev-tagline')?.value || 'SuperAdmin Console';
    const primary = document.getElementById('primary-color')?.value || '#0a2540';
    const accent  = document.getElementById('accent-color')?.value  || '#F06B21';

    document.getElementById('preview-appname').textContent = name;
    document.getElementById('preview-tagline').textContent = tagline;

    // Preview panel: primary = sidebar bg, accent = CTAs/buttons
    document.documentElement.style.setProperty('--preview-primary', primary);
    document.documentElement.style.setProperty('--preview-accent',  accent);

    // Live page: primary drives sidebar bg, accent drives all CTAs/buttons
    document.documentElement.style.setProperty('--sidebar-bg', primary);
    document.documentElement.style.setProperty('--orange',     accent);
}

function previewLogo(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const div = input.previousElementSibling;
        if (div) div.querySelector('div:first-child').innerHTML = `<img src="${e.target.result}" style="max-height:40px;max-width:120px">`;
    };
    reader.readAsDataURL(input.files[0]);
}

document.getElementById('global-settings-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!validateGlobalForm()) return;
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const fd = new FormData(this);
    ['allow_registration','email_verification','maintenance_mode'].forEach(f => {
        fd.set(f, fd.has(f) ? '1' : '0');
    });
    const r = await fetch('{{ route("superadmin.settings.update") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: fd,
    });
    const res = await r.json();
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
});
</script>
@endpush
