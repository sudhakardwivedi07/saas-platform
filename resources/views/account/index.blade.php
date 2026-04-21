@extends('layouts.app')
@section('page-title', 'My Account')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>My Account</h1>
        <p>Manage your profile, password, and branding settings</p>
    </div>
</div>

{{-- Tab Bar --}}
<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-profile">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
    </button>
    <button class="tab-btn" data-tab="tab-branding">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><path d="M17 21H7a4 4 0 01-4-4 3 3 0 013-3h.5a2.5 2.5 0 002.5-2.5v-1A2.5 2.5 0 0111.5 8h1"/><path d="M14 21v-4a2 2 0 012-2h4"/></svg>
        Branding
    </button>
</div>

{{-- Tab 1: Profile --}}
<div class="tab-panel active" id="tab-profile">
    <div class="acc-card">
        {{-- Avatar + Name --}}
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border)">
            <div style="width:52px;height:52px;border-radius:12px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff;letter-spacing:-.5px;flex-shrink:0">
                {{ strtoupper(substr($authUser->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(strstr($authUser->name ?? '', ' ') ?: '  ', 1, 1)) }}
            </div>
            <div>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">{{ $authUser->name }}</div>
                <div style="font-size:13px;color:var(--text-3);margin-top:2px">{{ $authUser->email }}</div>
                <div style="margin-top:5px">
                    <span class="badge b-orange" style="font-size:11px">{{ $authUser->getRoleNames()->first() ?? 'Admin' }}</span>
                </div>
            </div>
        </div>

        {{-- Profile Form --}}
        <div style="margin-bottom:26px">
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:16px">Personal Information</div>
            <form method="POST" action="{{ route('account.profile') }}">
                @csrf
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Full Name</label>
                        <input type="text" name="name" class="f-inp" value="{{ old('name', $authUser->name) }}" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Email Address</label>
                        <input type="email" name="email" class="f-inp" value="{{ old('email', $authUser->email) }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Profile
                </button>
            </form>
        </div>

        {{-- Divider --}}
        <div style="height:1px;background:var(--border);margin:0 -22px 22px"></div>

        {{-- Password Form --}}
        <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:4px">Change Password</div>
            <div style="font-size:12.5px;color:var(--text-3);margin-bottom:16px">Use a strong password that you don't use elsewhere.</div>
            <form method="POST" action="{{ route('account.password') }}">
                @csrf
                <div class="f-group">
                    <label class="f-label">Current Password</label>
                    <input type="password" name="current_password" class="f-inp" placeholder="Enter your current password" autocomplete="current-password" required style="max-width:400px">
                </div>
                <div class="form-2col" style="max-width:400px">
                    <div class="f-group">
                        <label class="f-label">New Password</label>
                        <input type="password" name="password" class="f-inp" placeholder="Min. 8 characters" autocomplete="new-password" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="f-inp" placeholder="Repeat new password" autocomplete="new-password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-ghost btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Save Password
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Tab 2: Branding --}}
<div class="tab-panel" id="tab-branding">
    <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data" id="branding-form">
        @csrf
        <div class="brand-layout">

            {{-- Left Column --}}
            <div>

                {{-- Branding Header --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                    <div>
                        <div style="font-size:16px;font-weight:700;color:var(--text-1)">Branding Settings</div>
                        <div style="font-size:13px;color:var(--text-3);margin-top:2px">Customise the look and feel of your client portal</div>
                    </div>
                    <div style="display:flex;gap:8px">
                        <button type="button" class="btn btn-ghost btn-sm" id="reset-branding-btn" onclick="resetBranding()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            Reset
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Branding
                        </button>
                    </div>
                </div>

                {{-- Logo Upload --}}
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Logo
                    </div>

                    <label id="logo-drop-zone" for="logo-upload" style="display:block;border:2px dashed #bfdbfe;border-radius:10px;background:var(--blue-pale);padding:32px 20px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s" ondragover="event.preventDefault();this.style.borderColor='var(--blue)'" ondragleave="this.style.borderColor='#bfdbfe'" ondrop="handleLogoDrop(event)">
                        <div id="logo-drop-content">
                            @if($branding?->logo)
                            <img src="{{ asset('storage/' . $branding->logo) }}" alt="Logo" style="max-height:52px;max-width:200px;object-fit:contain;display:block;margin:0 auto 10px">
                            <p style="font-size:12.5px;color:var(--text-2)">Drag &amp; drop a new logo, or <span style="color:var(--blue);font-weight:600;text-decoration:underline">click to upload</span></p>
                            @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="#93c5fd" stroke-width="1.6" width="28" height="28" style="display:block;margin:0 auto 10px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p style="font-size:13px;color:var(--text-2)">Drag &amp; drop your logo, or <span style="color:var(--blue);font-weight:600;text-decoration:underline">click to upload</span></p>
                            @endif
                            <p style="font-size:12px;color:var(--text-3);margin-top:5px">PNG, SVG, JPG &middot; up to 2 MB &middot; Recommended: 200&times;60px</p>
                        </div>
                    </label>
                    <input type="file" name="logo" id="logo-upload" accept="image/png,image/jpeg,image/jpg,image/svg+xml" style="display:none" onchange="previewLogo(this)">

                    <div style="display:flex;align-items:flex-start;gap:7px;margin-top:10px;padding:9px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="14" height="14" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span style="font-size:12px;color:#1e40af;line-height:1.5">Changes apply to the sidebar, login page, and client portal header in real-time.</span>
                    </div>
                </div>

                {{-- Brand Colors --}}
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="6.5" cy="11.5" r="2.5"/><circle cx="17.5" cy="15.5" r="2.5"/><circle cx="10" cy="19" r="2"/></svg>
                        Brand Colors
                    </div>

                    <div style="display:flex;flex-direction:column;gap:14px">
                        {{-- Primary Color --}}
                        <div>
                            <div style="font-size:12.5px;font-weight:600;color:var(--text-1);margin-bottom:7px">Primary Color</div>
                            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;background:#fff">
                                <div id="primary-swatch" style="width:38px;height:38px;border-radius:8px;background:{{ $branding->primary_color ?? '#1a6aa8' }};flex-shrink:0;border:2px solid rgba(0,0,0,.08)"></div>
                                <input type="text" id="primary-hex-inp" value="{{ $branding->primary_color ?? '#1a6aa8' }}" maxlength="7" placeholder="#1a6aa8"
                                    style="flex:1;border:none;outline:none;font-size:13.5px;font-family:monospace;color:var(--text-1);background:transparent;font-weight:500"
                                    oninput="syncHexInput(this,'primary')">
                                <label for="primary-color-inp" style="cursor:pointer;flex-shrink:0">
                                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $branding->primary_color ?? '#1a6aa8' }};border:2px solid rgba(0,0,0,.08);cursor:pointer" id="primary-picker-btn"></div>
                                    <input type="color" id="primary-color-inp" name="primary_color" value="{{ $branding->primary_color ?? '#1a6aa8' }}" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none"
                                        oninput="syncColorPicker(this,'primary')">
                                </label>
                            </div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Used for sidebar, navigation, headings, and UI elements</div>
                        </div>

                        {{-- Accent / CTA Color --}}
                        <div>
                            <div style="font-size:12.5px;font-weight:600;color:var(--text-1);margin-bottom:7px">Accent / CTA Color</div>
                            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;background:#fff">
                                <div id="accent-swatch" style="width:38px;height:38px;border-radius:8px;background:{{ $branding->accent_color ?? '#f06b21' }};flex-shrink:0;border:2px solid rgba(0,0,0,.08)"></div>
                                <input type="text" id="accent-hex-inp" value="{{ $branding->accent_color ?? '#f06b21' }}" maxlength="7" placeholder="#f06b21"
                                    style="flex:1;border:none;outline:none;font-size:13.5px;font-family:monospace;color:var(--text-1);background:transparent;font-weight:500"
                                    oninput="syncHexInput(this,'accent')">
                                <label for="accent-color-inp" style="cursor:pointer;flex-shrink:0">
                                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $branding->accent_color ?? '#f06b21' }};border:2px solid rgba(0,0,0,.08);cursor:pointer" id="accent-picker-btn"></div>
                                    <input type="color" id="accent-color-inp" name="accent_color" value="{{ $branding->accent_color ?? '#f06b21' }}" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none"
                                        oninput="syncColorPicker(this,'accent')">
                                </label>
                            </div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Used for buttons, CTAs, active states, and highlights</div>
                        </div>

                        {{-- Quick Presets --}}
                        <div style="padding-top:4px;border-top:1px solid var(--border)">
                            <div style="font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:10px">Quick Presets</div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                @php
                                $presets = [
                                    ['#1a6aa8','#f06b21'],
                                    ['#b45309','#f59e0b'],
                                    ['#7c3aed','#ec4899'],
                                    ['#1d4ed8','#f06b21'],
                                    ['#065f46','#f59e0b'],
                                    ['#0a2540','#2563eb'],
                                ];
                                @endphp
                                @foreach($presets as $preset)
                                <button type="button"
                                    onclick="applyPreset('{{ $preset[0] }}','{{ $preset[1] }}')"
                                    title="{{ $preset[0] }} / {{ $preset[1] }}"
                                    style="width:38px;height:38px;border-radius:10px;border:2px solid rgba(0,0,0,.1);cursor:pointer;overflow:hidden;padding:0;background:transparent;flex-shrink:0;transition:transform .12s,box-shadow .12s"
                                    onmouseenter="this.style.transform='scale(1.12)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.2)'"
                                    onmouseleave="this.style.transform='scale(1)';this.style.boxShadow='none'">
                                    <svg viewBox="0 0 38 38" width="38" height="38">
                                        <path d="M0 0 L38 0 L0 38 Z" fill="{{ $preset[1] }}"/>
                                        <path d="M38 0 L38 38 L0 38 Z" fill="{{ $preset[0] }}"/>
                                    </svg>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Custom Domain --}}
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                        Domain Settings
                    </div>
                    <div style="font-size:12px;color:var(--text-3);margin-bottom:10px">Your sub-companies will be hosted on a custom subdomain</div>
                    <div style="display:flex;align-items:stretch;border:1.5px solid var(--border);border-radius:9px;overflow:hidden;background:#fff">
                        <input type="text" name="custom_domain" class="f-inp" placeholder="yourcompany" value="{{ $branding->custom_domain ?? '' }}" style="border:none;border-radius:0;flex:1;background:#fff">
                        <div style="display:flex;align-items:center;padding:0 13px;background:var(--bg);border-left:1px solid var(--border);font-size:13px;font-weight:500;color:var(--text-2);white-space:nowrap;flex-shrink:0">.saas.app</div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Sticky Live Preview --}}
            <div class="preview-sticky">
                <div class="preview-card">
                    <div class="preview-head" id="preview-head" style="background:var(--blue)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Live Preview
                    </div>
                    <div style="padding:10px 12px;border-bottom:1px solid var(--border);background:#fafbff">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3)">Client Portal Appearance</div>
                    </div>
                    <div style="display:flex;height:240px">
                        {{-- Mini Sidebar --}}
                        <div id="preview-sidebar" style="width:84px;background:#fff;border-right:1px solid var(--border);padding:10px 0;display:flex;flex-direction:column;align-items:center;gap:2px">
                            <div id="preview-logo-wrap" style="width:48px;height:32px;border-radius:6px;background:var(--bg);border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;margin-bottom:10px;overflow:hidden">
                                @if($branding?->logo)
                                <img id="preview-logo-img" src="{{ asset('storage/' . $branding->logo) }}" style="max-width:44px;max-height:28px;object-fit:contain;display:block">
                                @else
                                <svg id="preview-logo-img" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                @endif
                            </div>
                            {{-- Nav Items --}}
                            <div id="preview-nav-active" style="width:68px;padding:5px 7px;border-radius:5px;border-left:2px solid var(--orange);background:var(--orange-light);display:flex;align-items:center;gap:4px;margin-bottom:1px">
                                <div style="width:8px;height:8px;background:var(--orange);border-radius:50%;flex-shrink:0"></div>
                                <div style="font-size:7.5px;color:var(--orange);font-weight:700;line-height:1.2">Dashboard</div>
                            </div>
                            @foreach(['Clients','Billing','Settings','Account'] as $navLabel)
                            <div style="width:68px;padding:5px 7px;border-radius:5px;display:flex;align-items:center;gap:4px;margin-bottom:1px">
                                <div style="width:8px;height:8px;background:var(--border);border-radius:50%;flex-shrink:0"></div>
                                <div style="font-size:7.5px;color:var(--text-3);line-height:1.2">{{ $navLabel }}</div>
                            </div>
                            @endforeach
                        </div>
                        {{-- Mini Content --}}
                        <div style="flex:1;background:var(--bg);padding:10px 9px;display:flex;flex-direction:column;gap:6px">
                            <div style="height:7px;background:var(--border);border-radius:4px;width:60%"></div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px">
                                <div style="height:28px;background:#fff;border-radius:5px;border:1px solid var(--border)"></div>
                                <div style="height:28px;background:#fff;border-radius:5px;border:1px solid var(--border)"></div>
                            </div>
                            <div style="height:50px;background:#fff;border-radius:5px;border:1px solid var(--border)"></div>
                            <div style="height:5px;background:var(--border);border-radius:3px;width:80%"></div>
                            <div style="height:5px;background:var(--border);border-radius:3px;width:55%"></div>
                            <div style="margin-top:auto">
                                <div id="preview-btn" style="height:26px;background:var(--orange);border-radius:6px;width:80px;display:flex;align-items:center;justify-content:center">
                                    <div style="height:4px;background:rgba(255,255,255,.7);border-radius:2px;width:44px"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="font-size:11.5px;color:var(--text-3);text-align:center;margin-top:8px">Branding preview (approximate)</div>
            </div>

        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
function updatePreview(primary, accent) {
    const head = document.getElementById('preview-head');
    const btn  = document.getElementById('preview-btn');
    const nav  = document.getElementById('preview-nav-active');
    if (head) head.style.background = primary;
    if (btn)  btn.style.background  = accent;
    if (nav) {
        nav.style.borderLeftColor = accent;
        nav.style.background      = accent + '22';
        const dot  = nav.querySelector('div:first-child');
        const lbl  = nav.querySelector('div:last-child');
        if (dot) dot.style.background = accent;
        if (lbl) { lbl.style.color = accent; }
    }
}

function syncHexInput(el, type) {
    const val = el.value.trim();
    if (!/^#[0-9a-fA-F]{6}$/.test(val)) return;
    document.getElementById(type + '-swatch').style.background      = val;
    document.getElementById(type + '-picker-btn').style.background  = val;
    document.getElementById(type + '-color-inp').value              = val;
    const p = document.getElementById('primary-hex-inp').value.trim();
    const a = document.getElementById('accent-hex-inp').value.trim();
    if (/^#[0-9a-fA-F]{6}$/.test(p) && /^#[0-9a-fA-F]{6}$/.test(a)) updatePreview(p, a);
}

function syncColorPicker(el, type) {
    const val = el.value;
    document.getElementById(type + '-swatch').style.background     = val;
    document.getElementById(type + '-picker-btn').style.background = val;
    document.getElementById(type + '-hex-inp').value               = val;
    const p = document.getElementById('primary-color-inp').value;
    const a = document.getElementById('accent-color-inp').value;
    updatePreview(p, a);
}

function applyPreset(primary, accent) {
    ['primary','accent'].forEach(function(type) {
        const val = type === 'primary' ? primary : accent;
        document.getElementById(type + '-color-inp').value          = val;
        document.getElementById(type + '-hex-inp').value            = val;
        document.getElementById(type + '-swatch').style.background  = val;
        document.getElementById(type + '-picker-btn').style.background = val;
    });
    updatePreview(primary, accent);
}

function resetBranding() {
    applyPreset('#1a6aa8', '#f06b21');
}

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.getElementById('preview-logo-wrap');
            wrap.innerHTML = '<img src="' + e.target.result + '" style="max-width:44px;max-height:28px;object-fit:contain;display:block">';
            const content = document.getElementById('logo-drop-content');
            if (content) {
                content.innerHTML = '<img src="' + e.target.result + '" style="max-height:52px;max-width:200px;object-fit:contain;display:block;margin:0 auto 10px"><p style="font-size:12.5px;color:var(--text-2)">Drag &amp; drop a new logo, or <span style="color:var(--blue);font-weight:600;text-decoration:underline">click to upload</span></p>';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleLogoDrop(event) {
    event.preventDefault();
    const zone = document.getElementById('logo-drop-zone');
    zone.style.borderColor = '#bfdbfe';
    const files = event.dataTransfer.files;
    if (!files || !files[0]) return;
    const allowed = ['image/png','image/jpeg','image/jpg','image/svg+xml'];
    if (!allowed.includes(files[0].type)) return;
    const dt = new DataTransfer();
    dt.items.add(files[0]);
    const inp = document.getElementById('logo-upload');
    inp.files = dt.files;
    previewLogo(inp);
}

// Initialise preview with server-side colors on page load
(function() {
    const p = document.getElementById('primary-color-inp');
    const a = document.getElementById('accent-color-inp');
    if (p && a) updatePreview(p.value, a.value);
})();

// Submit branding form via AJAX and show toast
document.getElementById('branding-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Saving…';

    try {
        const fd = new FormData(this);
        const res  = await fetch(this.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (json.status === 'success') {
            // Update CSS variables immediately
            const primary = document.getElementById('primary-color-inp').value;
            const accent  = document.getElementById('accent-color-inp').value;
            document.documentElement.style.setProperty('--blue',   primary);
            document.documentElement.style.setProperty('--orange', accent);

            // Update sidebar logo and drop zone if a new logo was saved
            if (json.logo_url) {
                // Sidebar logo
                const sidebarLogoZone = document.querySelector('.ca-logo-zone');
                if (sidebarLogoZone) {
                    sidebarLogoZone.innerHTML = '<img src="' + json.logo_url + '" alt="Logo" class="ca-logo-img">';
                }
                // Drop zone
                const content = document.getElementById('logo-drop-content');
                if (content) {
                    content.innerHTML = '<img src="' + json.logo_url + '" style="max-height:52px;max-width:200px;object-fit:contain;display:block;margin:0 auto 10px"><p style="font-size:12.5px;color:var(--text-2)">Drag &amp; drop a new logo, or <span style="color:var(--blue);font-weight:600;text-decoration:underline">click to upload</span></p>';
                }
                // Preview panel
                const wrap = document.getElementById('preview-logo-wrap');
                if (wrap) {
                    wrap.innerHTML = '<img src="' + json.logo_url + '" style="max-width:44px;max-height:28px;object-fit:contain;display:block">';
                }
            }

            showToast(json.message || 'Branding updated successfully.', 'success');
        } else {
            showToast(json.message || 'Failed to save branding.', 'error');
        }
    } catch (err) {
        showToast('An error occurred. Please try again.', 'error');
    }

    btn.disabled  = false;
    btn.innerHTML = origText;
});

// Auto-switch to Branding tab when URL hash is #branding
// Must be DOMContentLoaded so it runs AFTER the layout registers tab click handlers
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#branding') {
        var btn = document.querySelector('[data-tab="tab-branding"]');
        if (btn) btn.click();
    }
});
</script>
@endpush
