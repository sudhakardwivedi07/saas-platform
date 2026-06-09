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

@php
// Determine which My Account tabs this user may see, and which is first/active.
$_atabs = [
    'tab-profile'          => auth()->user()->can('view_account_profile'),
    'tab-branding'         => auth()->user()->can('view_account_branding'),
    'tab-login-page'       => auth()->user()->can('view_account_login_page'),
    'tab-forgot-password'  => auth()->user()->can('view_account_forgot_password'),
    'tab-subscription'     => auth()->user()->can('view_account_subscription'),
    'tab-activity'         => auth()->user()->can('view_account_activity'),
];
$_afirst = array_key_first(array_filter($_atabs)) ?? 'tab-profile';
@endphp

{{-- Tab Bar --}}
<div class="tab-bar">
    @if($_atabs['tab-profile'])
    <button class="tab-btn {{ $_afirst === 'tab-profile' ? 'active' : '' }}" data-tab="tab-profile">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
    </button>
    @endif
    @if($_atabs['tab-branding'])
    <button class="tab-btn {{ $_afirst === 'tab-branding' ? 'active' : '' }}" data-tab="tab-branding">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><path d="M17 21H7a4 4 0 01-4-4 3 3 0 013-3h.5a2.5 2.5 0 002.5-2.5v-1A2.5 2.5 0 0111.5 8h1"/><path d="M14 21v-4a2 2 0 012-2h4"/></svg>
        Branding
    </button>
    @endif
    @if($_atabs['tab-login-page'])
    <button class="tab-btn {{ $_afirst === 'tab-login-page' ? 'active' : '' }}" data-tab="tab-login-page">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
        Login Page
    </button>
    @endif
    @if($_atabs['tab-forgot-password'])
    <button class="tab-btn {{ $_afirst === 'tab-forgot-password' ? 'active' : '' }}" data-tab="tab-forgot-password">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Forgot Password
    </button>
    @endif
    @if($_atabs['tab-subscription'])
    <button class="tab-btn {{ $_afirst === 'tab-subscription' ? 'active' : '' }}" data-tab="tab-subscription">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
        Subscription
    </button>
    @endif
    @if($_atabs['tab-activity'])
    <button class="tab-btn {{ $_afirst === 'tab-activity' ? 'active' : '' }}" data-tab="tab-activity">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Activity Log
    </button>
    @endif
</div>

{{-- Tab 1: Profile --}}
@if($_atabs['tab-profile'])
<div class="tab-panel {{ $_afirst === 'tab-profile' ? 'active' : '' }}" id="tab-profile">
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
                    <span class="badge b-orange" style="font-size:11px">{{ $authUser->roleDisplayName('Admin') }}</span>
                </div>
            </div>
        </div>

        {{-- Profile Form --}}
        <div style="margin-bottom:26px">
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:16px">Personal Information</div>
            <form method="POST" action="{{ route('account.profile') }}" id="profile-form" novalidate>
                @csrf
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Full Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="name" id="ac-profile-name" class="f-inp" value="{{ old('name', $authUser->name) }}" maxlength="100">
                        <div id="err-profile-name" class="ac-err" style="display:none"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Email Address</label>
                        <input type="email" class="f-inp" value="{{ $authUser->email }}" readonly
                               style="background:var(--bg);cursor:not-allowed;color:var(--text-2)">
                        <div style="font-size:11px;color:var(--text-3);margin-top:4px">Email address cannot be changed.</div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="validateAndSubmitProfile()">
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
            <form method="POST" action="{{ route('account.password') }}" id="password-form" novalidate>
                @csrf
                <div class="f-group" style="max-width:400px">
                    <label class="f-label">Current Password <span style="color:var(--red)">*</span></label>
                    <input type="password" name="current_password" id="ac-cur-pwd" class="f-inp" placeholder="Enter your current password" autocomplete="current-password">
                    <div id="err-cur-pwd" class="ac-err" style="display:none"></div>
                </div>
                <div class="form-2col" style="max-width:400px">
                    <div class="f-group">
                        <label class="f-label">New Password <span style="color:var(--red)">*</span></label>
                        <input type="password" name="password" id="ac-new-pwd" class="f-inp" placeholder="Min. 8 characters" autocomplete="new-password">
                        <div id="err-new-pwd" class="ac-err" style="display:none"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Confirm Password <span style="color:var(--red)">*</span></label>
                        <input type="password" name="password_confirmation" id="ac-confirm-pwd" class="f-inp" placeholder="Repeat new password" autocomplete="new-password">
                        <div id="err-confirm-pwd" class="ac-err" style="display:none"></div>
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="validateAndSubmitPassword()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Save Password
                </button>
            </form>
        </div>
    </div>
</div>
@endif {{-- /tab-profile --}}

{{-- Tab 2: Branding --}}
@if($_atabs['tab-branding'])
<div class="tab-panel {{ $_afirst === 'tab-branding' ? 'active' : '' }}" id="tab-branding">
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

                {{-- Favicon Upload --}}
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                        Favicon
                    </div>

                    <div style="display:flex;align-items:center;gap:14px">
                        <label for="favicon-upload" id="favicon-drop-zone" style="display:flex;align-items:center;justify-content:center;flex-direction:column;width:80px;height:80px;border:2px dashed #bfdbfe;border-radius:10px;background:var(--blue-pale);cursor:pointer;transition:border-color .15s,background .15s;flex-shrink:0" onmouseenter="this.style.borderColor='var(--blue)'" onmouseleave="this.style.borderColor='#bfdbfe'">
                            <div id="favicon-preview-wrap">
                                @if($branding?->favicon)
                                    <img id="favicon-preview-img" src="{{ asset('storage/' . $branding->favicon) }}" alt="Favicon" style="width:32px;height:32px;object-fit:contain;display:block">
                                @else
                                    <svg id="favicon-preview-img" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#93c5fd" stroke-width="1.6"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                                @endif
                            </div>
                        </label>
                        <input type="file" name="favicon" id="favicon-upload" accept="image/x-icon,image/png,image/jpeg" style="display:none" onchange="previewFavicon(this)">
                        <div>
                            <div style="font-size:13px;font-weight:500;color:var(--text-1);margin-bottom:3px">Browser Tab Icon</div>
                            <div style="font-size:12px;color:var(--text-3);margin-bottom:8px;line-height:1.5">Appears in the browser tab for both your portal and all client portals. Recommended: 32×32px ICO or PNG.</div>
                            <label for="favicon-upload" class="btn btn-ghost btn-xs" style="cursor:pointer">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Favicon
                            </label>
                        </div>
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

    {{-- ── Date Format (separate card, not part of branding form) ── --}}
    @php
    $companyDateFormat = company()?->date_format;
    $globalDateFormat  = global_setting()?->date_format ?? 'M j, Y';
    $effectiveFmt      = $companyDateFormat ?: $globalDateFormat;
    $today             = \Carbon\Carbon::now();
    @endphp
    <div class="acc-card" style="margin-top:18px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text-1)">Date Format</div>
                <div style="font-size:12.5px;color:var(--text-3);margin-top:2px">
                    Override the platform-wide date format for your account and your users.
                    Leave as default to use the Super Admin's setting
                    (<strong>{{ $today->format($globalDateFormat) }}</strong>).
                </div>
            </div>
            @if($companyDateFormat)
            <span style="background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.25);border-radius:20px;padding:3px 12px;font-size:11.5px;font-weight:600;flex-shrink:0;margin-left:16px">Custom</span>
            @else
            <span style="background:var(--bg);color:var(--text-3);border:1px solid var(--border);border-radius:20px;padding:3px 12px;font-size:11.5px;font-weight:600;flex-shrink:0;margin-left:16px">Using Default</span>
            @endif
        </div>

        <div style="height:1px;background:var(--border);margin:14px 0"></div>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:18px" id="adf-grid">
            @foreach(DATE_FORMAT_PRESETS as $fmt => $example)
            <label id="adf-opt-{{ str_replace(['/', '.', ' '], ['-', '-', '-'], $fmt) }}"
                   style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:2px solid {{ $effectiveFmt === $fmt ? 'var(--blue)' : 'var(--border)' }};border-radius:10px;cursor:pointer;background:{{ $effectiveFmt === $fmt ? 'var(--blue-pale)' : '#fff' }};transition:border-color .13s,background .13s"
                   onmouseenter="if(!this.querySelector('input').checked){this.style.borderColor='var(--blue)';this.style.background='var(--blue-pale)'}"
                   onmouseleave="if(!this.querySelector('input').checked){this.style.borderColor='var(--border)';this.style.background='#fff'}">
                <input type="radio" name="adf_pick" value="{{ $fmt }}"
                       style="accent-color:var(--blue);width:15px;height:15px;flex-shrink:0"
                       {{ $effectiveFmt === $fmt ? 'checked' : '' }}
                       onchange="adfPickFormat(this.value)">
                <div>
                    <div style="font-size:13.5px;font-weight:600;color:var(--text-1);font-family:monospace">{{ $today->format($fmt) }}</div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:1px"><code style="background:var(--bg);padding:1px 4px;border-radius:4px;font-size:10.5px">{{ $fmt }}</code></div>
                </div>
            </label>
            @endforeach
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div style="font-size:12.5px;color:var(--text-3)">
                Preview:
                <span id="adf-preview" style="font-weight:600;color:var(--text-1);margin-left:4px">{{ $today->format($effectiveFmt) }}</span>
            </div>
            <div style="display:flex;gap:8px">
                @if($companyDateFormat)
                <button class="btn btn-ghost btn-sm" onclick="adfClear()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                    Reset to Default
                </button>
                @endif
                <button class="btn btn-primary btn-sm" id="adf-save-btn" onclick="adfSave()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Date Format
                </button>
            </div>
        </div>
    </div>
</div>
@endif {{-- /tab-branding --}}

{{-- Tab 3: Login Page --}}
@if($_atabs['tab-login-page'])
<div class="tab-panel {{ $_afirst === 'tab-login-page' ? 'active' : '' }}" id="tab-login-page">
    <div class="acc-card" style="max-width:1200px">
        <div class="acc-section-title" style="margin-bottom:24px">
            <h3 style="font-size:16px;font-weight:700;color:#1a202c;margin:0 0 4px">Login Page</h3>
            <p style="font-size:13px;color:#6b7280;margin:0">Customise the login page your clients and team members see when signing in.</p>
        </div>

        <div style="display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap">

            {{-- ── LEFT: Form ── --}}
            <div style="flex:1;min-width:320px;max-width:520px">
                <form id="login-page-form" enctype="multipart/form-data" onsubmit="submitLoginPageForm(event)">
                    @csrf
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Heading</label>
                        <input type="text" name="login_heading" id="lp-heading"
                               value="{{ $branding?->login_heading ?? 'Welcome back' }}"
                               placeholder="Welcome back"
                               oninput="lpUpdatePreview()"
                               style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Subheading</label>
                        <input type="text" name="login_subheading" id="lp-subheading"
                               value="{{ $branding?->login_subheading ?? 'Sign in to ' . ($branding?->app_name ?? config('app.name')) }}"
                               placeholder="Sign in to your account"
                               oninput="lpUpdatePreview()"
                               style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:8px">Background Type</label>
                        <div style="display:flex;gap:12px;flex-wrap:wrap">
                            @foreach(['gradient' => 'Gradient', 'solid' => 'Solid Color', 'image' => 'Image'] as $val => $lbl)
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;cursor:pointer">
                                <input type="radio" name="login_bg_type" value="{{ $val }}"
                                       {{ ($branding?->login_bg_type ?? 'gradient') === $val ? 'checked' : '' }}
                                       onchange="lpUpdateBgTypeVisibility();lpUpdatePreview()">
                                {{ $lbl }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div id="lp-colors-wrap" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
                        <div id="lp-bg-color-wrap" style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px" id="lp-bg-color-label">Background Color</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_bg_color" id="lp-bg-color"
                                       value="{{ $branding?->login_bg_color ?? '#0d3d66' }}"
                                       oninput="lpUpdatePreview()"
                                       style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-bg-color-text"
                                       value="{{ $branding?->login_bg_color ?? '#0d3d66' }}"
                                       oninput="lpSyncColor('lp-bg-color','lp-bg-color-text');lpUpdatePreview()"
                                       maxlength="7"
                                       style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                        <div id="lp-bg-color-to-wrap" style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Gradient End Color</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_bg_color_to" id="lp-bg-color-to"
                                       value="{{ $branding?->login_bg_color_to ?? '#f06b21' }}"
                                       oninput="lpUpdatePreview()"
                                       style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-bg-color-to-text"
                                       value="{{ $branding?->login_bg_color_to ?? '#f06b21' }}"
                                       oninput="lpSyncColor('lp-bg-color-to','lp-bg-color-to-text');lpUpdatePreview()"
                                       maxlength="7"
                                       style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                    </div>
                    <div id="lp-bg-image-wrap" style="margin-bottom:16px;display:none">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Background Image</label>
                        <input type="file" name="login_background" id="lp-bg-image" accept="image/*"
                               onchange="lpPreviewBgImage(this)"
                               style="font-size:13px">
                        @if($branding?->login_background)
                            <p style="font-size:12px;color:#6b7280;margin-top:4px">Current: <a href="{{ asset('storage/'.$branding->login_background) }}" target="_blank" style="color:#1a6aa8">view image</a></p>
                        @endif
                    </div>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
                        <div style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Card Background</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_card_bg" id="lp-card-bg"
                                       value="{{ $branding?->login_card_bg ?? '#ffffff' }}"
                                       oninput="lpUpdatePreview()"
                                       style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-card-bg-text"
                                       value="{{ $branding?->login_card_bg ?? '#ffffff' }}"
                                       oninput="lpSyncColor('lp-card-bg','lp-card-bg-text');lpUpdatePreview()"
                                       maxlength="7"
                                       style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                        <div style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Button Color</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_btn_color" id="lp-btn-color"
                                       value="{{ $branding?->login_btn_color ?? ($branding?->accent_color ?? '#f06b21') }}"
                                       oninput="lpUpdatePreview()"
                                       style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-btn-color-text"
                                       value="{{ $branding?->login_btn_color ?? ($branding?->accent_color ?? '#f06b21') }}"
                                       oninput="lpSyncColor('lp-btn-color','lp-btn-color-text');lpUpdatePreview()"
                                       maxlength="7"
                                       style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom:24px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Logo (shown on login card)</label>
                        <input type="file" name="logo" id="lp-logo" accept="image/*"
                               onchange="lpPreviewLogoImage(this)"
                               style="font-size:13px">
                        @if($branding?->logo)
                            <p style="font-size:12px;color:#6b7280;margin-top:4px">Current: <a href="{{ asset('storage/'.$branding->logo) }}" target="_blank" style="color:#1a6aa8">view logo</a></p>
                        @endif
                    </div>
                    <button type="submit" id="lp-submit" class="btn btn-primary">Save Login Page</button>
                </form>
            </div>

            {{-- ── RIGHT: Live Preview ── --}}
            <div style="flex:1;min-width:280px;max-width:440px">
                <div style="position:sticky;top:80px">
                    <p style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">Live Preview</p>
                    <div style="border-radius:14px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.18);aspect-ratio:4/3;position:relative" id="lp-preview-outer">
                        <div id="lp-preview-bg" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;transition:background .25s">
                            <div id="lp-preview-card" style="border-radius:14px;padding:28px 28px 22px;width:100%;max-width:320px;box-shadow:0 12px 40px rgba(0,0,0,.2);transition:background .25s">
                                <div style="text-align:center;margin-bottom:16px">
                                    <div id="lp-preview-logo-wrap">
                                        @if($branding?->logo)
                                            <img id="lp-preview-logo-img" src="{{ asset('storage/'.$branding->logo) }}" alt="Logo" style="max-height:36px;max-width:140px;object-fit:contain;display:block;margin:0 auto">
                                        @else
                                            <div id="lp-preview-logo-placeholder" style="display:inline-flex;align-items:center;justify-content:center;height:32px;min-width:120px;border:2px dashed #e2e8f0;border-radius:8px;padding:0 12px;color:#9ca3af;font-size:11px;font-weight:500">
                                                {{ $branding?->app_name ?? config('app.name') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div id="lp-preview-heading" style="font-size:15px;font-weight:700;color:#1a202c;text-align:center;margin-bottom:3px">
                                    {{ $branding?->login_heading ?? 'Welcome back' }}
                                </div>
                                <div id="lp-preview-subheading" style="font-size:11px;color:#6b7280;text-align:center;margin-bottom:14px">
                                    {{ $branding?->login_subheading ?? 'Sign in to ' . ($branding?->app_name ?? config('app.name')) }}
                                </div>
                                <div style="height:30px;border:1.5px solid #e2e8f0;border-radius:7px;margin-bottom:8px;background:#fff"></div>
                                <div style="height:30px;border:1.5px solid #e2e8f0;border-radius:7px;margin-bottom:12px;background:#fff"></div>
                                <div id="lp-preview-btn" style="height:34px;border-radius:8px;transition:background .25s"></div>
                            </div>
                        </div>
                    </div>
                    <p style="font-size:11.5px;color:#9ca3af;margin-top:8px;text-align:center">Changes are reflected instantly in the preview above.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif {{-- /tab-login-page --}}

{{-- Tab 3b: Forgot Password Page --}}
@if($_atabs['tab-forgot-password'])
<div class="tab-panel {{ $_afirst === 'tab-forgot-password' ? 'active' : '' }}" id="tab-forgot-password">
    <div class="acc-card" style="max-width:1200px">
        <div class="acc-section-title" style="margin-bottom:24px">
            <h3 style="font-size:16px;font-weight:700;color:#1a202c;margin:0 0 4px">Forgot Password Page</h3>
            <p style="font-size:13px;color:#6b7280;margin:0">Customise the heading and subheading shown on the "Forgot Password" page. The background, logo, and colours are inherited from your Login Page settings.</p>
        </div>
        <div style="display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap">
            {{-- LEFT: Form --}}
            <div style="flex:1;min-width:320px;max-width:520px">
                <form id="fp-page-form" onsubmit="submitFpPageForm(event)">
                    @csrf
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Heading</label>
                        <input type="text" name="fp_heading" id="fp-heading"
                               value="{{ $branding?->fp_heading ?? 'Forgot your password?' }}"
                               placeholder="Forgot your password?"
                               oninput="fpUpdatePreview()"
                               style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="margin-bottom:24px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Subheading</label>
                        <input type="text" name="fp_subheading" id="fp-subheading"
                               value="{{ $branding?->fp_subheading ?? "Enter your email and we'll send a reset link." }}"
                               placeholder="Enter your email and we'll send a reset link."
                               oninput="fpUpdatePreview()"
                               style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="padding:12px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;margin-bottom:20px;display:flex;align-items:flex-start;gap:8px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="14" height="14" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span style="font-size:12px;color:#1e40af;line-height:1.5">The background style, logo, card colour, and button colour are shared with your <strong>Login Page</strong> settings.</span>
                    </div>
                    <button type="submit" id="fp-submit" class="btn btn-primary">Save Forgot Password Page</button>
                </form>
            </div>
            {{-- RIGHT: Live Preview --}}
            <div style="flex:1;min-width:280px;max-width:440px">
                <div style="position:sticky;top:80px">
                    <p style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">Live Preview</p>
                    <div style="border-radius:14px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.18);aspect-ratio:4/3;position:relative" id="fp-preview-outer">
                        <div id="fp-preview-bg"
                             data-bg-type="{{ $branding?->login_bg_type ?? 'gradient' }}"
                             data-bg-color="{{ $branding?->login_bg_color ?? '#0d3d66' }}"
                             data-bg-color-to="{{ $branding?->login_bg_color_to ?? '#f06b21' }}"
                             @if($branding?->login_bg_type === 'image' && $branding?->login_background)
                             data-bg-img-url="{{ asset('storage/'.$branding->login_background) }}"
                             @endif
                             style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:24px">
                            <div id="fp-preview-card"
                                 style="border-radius:14px;padding:28px 28px 22px;width:100%;max-width:320px;box-shadow:0 12px 40px rgba(0,0,0,.2);background:{{ $branding?->login_card_bg ?? '#ffffff' }}">
                                <div style="text-align:center;margin-bottom:12px">
                                    <div id="fp-preview-logo-wrap">
                                        @if($branding?->logo)
                                            <img src="{{ asset('storage/'.$branding->logo) }}" alt="Logo" style="max-height:36px;max-width:140px;object-fit:contain;display:block;margin:0 auto">
                                        @else
                                            <div style="display:inline-flex;align-items:center;justify-content:center;height:32px;min-width:120px;border:2px dashed #e2e8f0;border-radius:8px;padding:0 12px;color:#9ca3af;font-size:11px;font-weight:500">
                                                {{ $branding?->app_name ?? config('app.name') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div id="fp-preview-heading" style="font-size:14px;font-weight:700;color:#1a202c;text-align:center;margin-bottom:3px">
                                    {{ $branding?->fp_heading ?? 'Forgot your password?' }}
                                </div>
                                <div id="fp-preview-subheading" style="font-size:10px;color:#6b7280;text-align:center;margin-bottom:12px">
                                    {{ $branding?->fp_subheading ?? "Enter your email and we'll send a reset link." }}
                                </div>
                                <div style="height:28px;border:1.5px solid #e2e8f0;border-radius:7px;margin-bottom:10px;background:#fff"></div>
                                <div id="fp-preview-btn" style="height:32px;border-radius:8px;background:{{ $branding?->login_btn_color ?? ($branding?->accent_color ?? '#f06b21') }}"></div>
                                <div style="text-align:center;margin-top:10px">
                                    <span style="font-size:9px;color:#6b7280">← Back to login</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p style="font-size:11.5px;color:#9ca3af;margin-top:8px;text-align:center">Background &amp; colours reflect your Login Page settings.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif {{-- /tab-forgot-password --}}

{{-- Tab 4: Subscription --}}
@if($_atabs['tab-subscription'])
<div class="tab-panel {{ $_afirst === 'tab-subscription' ? 'active' : '' }}" id="tab-subscription">
@php
    $subPkg      = $subscription?->package;
    $subStatus   = $subscription?->status ?? 'none';
    $renewsAt    = $subscription?->current_period_end;
    $planName    = $subPkg?->name ?? 'No Plan';
    $maxClients  = $subPkg?->max_users ?? 0;
    $maxInsights = $subPkg?->max_documents ?? 0;
    $planPrice   = $subPkg ? ($subscription?->billing_cycle === 'annual'
                    ? ($subPkg->annual_price ?? $subPkg->monthly_price * 12)
                    : $subPkg->monthly_price) : 0;
    $priceLabel  = $subPkg ? ('$' . number_format($planPrice, 2) . '/' . ($subscription?->billing_cycle === 'annual' ? 'year' : 'month')) : '—';
    $billingLabel= ucfirst($subscription?->billing_cycle ?? 'monthly');
    $nextBilling = $renewsAt ? $renewsAt->format(active_date_format()) : '—';
    $planDesc    = $subPkg?->description ?? 'Your current subscription plan';
@endphp

{{-- ── Subscription Summary Card ──────────────────────────────── --}}
@if($subscription && $subPkg)
<div style="background:#fff;border:1.5px solid var(--border);border-left:4px solid var(--blue);border-radius:12px;padding:22px 24px 18px;margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;padding-bottom:16px;border-bottom:1px solid var(--border);margin-bottom:18px">
        <div>
            <div style="font-size:19px;font-weight:700;color:var(--text-1);margin-bottom:3px">{{ $planName }}</div>
            <div style="font-size:12.5px;color:var(--text-3)">Your current subscription plan</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
            @if($subStatus === 'active')
                <span style="background:rgba(16,185,129,.12);color:#059669;border:1px solid rgba(16,185,129,.3);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Active</span>
            @elseif($subStatus === 'trialing')
                <span style="background:rgba(245,158,11,.12);color:#d97706;border:1px solid rgba(245,158,11,.3);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Trial</span>
            @elseif($subStatus === 'past_due')
                <span style="background:rgba(239,68,68,.12);color:#dc2626;border:1px solid rgba(239,68,68,.3);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Past Due</span>
            @elseif($subStatus === 'canceled')
                <span style="background:rgba(239,68,68,.10);color:#dc2626;border:1px solid rgba(239,68,68,.25);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Canceled</span>
            @endif
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px">
        <div>
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Plan Price</div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $priceLabel }}</div>
        </div>
        <div>
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Billing Cycle</div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $billingLabel }}</div>
        </div>
        <div>
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Next Billing Date</div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $nextBilling }}</div>
        </div>
        <div>
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Plan Features</div>
            <div style="font-size:13.5px;color:var(--text-2)">{{ $planDesc }}</div>
        </div>
    </div>
    @if($subStatus === 'canceled' && $subscription?->ends_at && $subscription->ends_at->isFuture())
    <button class="btn btn-sm" style="border:1.5px solid var(--green);color:var(--green);background:transparent" onclick="subReactivate()">Reactivate Subscription</button>
    @elseif(in_array($subStatus, ['active','trialing','past_due']))
    <button class="btn btn-sm" style="border:1.5px solid var(--red);color:var(--red);background:transparent" onclick="subCancel()">Cancel Subscription</button>
    @endif
</div>
@endif

{{-- ── Payment Method Card ─────────────────────────────────────── --}}
@if($subscription?->pm_last4)
<div style="background:#fff;border:1.5px solid var(--border);border-left:4px solid var(--green);border-radius:12px;padding:18px 24px;margin-bottom:16px">
    <div style="font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:10px">Payment Method</div>
    <div style="display:flex;align-items:center;gap:14px">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-2)" stroke-width="1.8" width="32" height="32"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
        <div>
            <div style="font-size:15px;font-weight:600;color:var(--text-1);letter-spacing:.08em">
                •••• •••• •••• {{ $subscription->pm_last4 }}
                &nbsp;&nbsp;
                <span style="font-size:13px;font-weight:400;color:var(--text-3)">Expire:
                    {{ str_pad($subscription->pm_exp_month, 2, '0', STR_PAD_LEFT) }}/{{ substr($subscription->pm_exp_year, -2) }}
                </span>
            </div>
            @if($subscription->pm_brand)
            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;text-transform:capitalize">{{ $subscription->pm_brand }}</div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Current Plan Banner ─────────────────────────────────────── --}}
@if($subscription)
<div class="plan-banner" style="margin-bottom:20px">
    <div class="plan-banner-left">
        <div class="plan-label">Current Plan</div>
        <div class="plan-name">{{ $planName }}</div>
        <div class="plan-meta">
            @if($maxClients) {{ $maxClients }} clients · @endif
            @if($maxInsights) {{ number_format($maxInsights) }} insights / month · @endif
            @if($subPkg?->monthly_price) ${{ number_format($subPkg->monthly_price, 0) }} / month @endif
            @if($subStatus === 'canceled' && $subscription?->ends_at)
                · Access until {{ $subscription->ends_at->format(active_date_format()) }}
            @elseif($renewsAt)
                · Renews {{ $renewsAt->format(active_date_format()) }}
            @endif
        </div>
    </div>
    <div class="plan-banner-right">
        @if($subStatus === 'active')
            <span class="plan-status-badge">Active</span>
        @elseif($subStatus === 'trialing')
            <span class="plan-status-badge" style="background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.5)">Trial</span>
        @elseif($subStatus === 'past_due')
            <span class="plan-status-badge" style="background:rgba(239,68,68,.2);border-color:rgba(239,68,68,.5)">Past Due</span>
        @elseif($subStatus === 'canceled')
            <span class="plan-status-badge" style="background:rgba(239,68,68,.18);border-color:rgba(239,68,68,.5)">Canceled</span>
        @endif
        @if($subStatus === 'canceled' && $subscription?->ends_at && $subscription->ends_at->isFuture())
            <button class="plan-reactivate-btn" onclick="subReactivate()">Reactivate Subscription</button>
        @elseif(in_array($subStatus, ['active','trialing','past_due']))
            <button class="plan-cancel-btn" onclick="subCancel()">Cancel Subscription</button>
        @endif
    </div>
</div>
@else
<div style="background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:22px 24px;margin-bottom:20px;text-align:center">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:6px">No Active Subscription</div>
    <div style="font-size:13px;color:var(--text-3)">Choose a plan below to get started.</div>
</div>
@endif

{{-- ── Available Plans ─────────────────────────────────────────── --}}
@if($packages->isNotEmpty())
<div style="margin-bottom:16px">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:4px">Available Plans</div>
    @if($subPkg)
    <div style="font-size:13px;color:var(--text-3)">You are on the <strong>{{ $planName }}</strong> plan. You can switch to any plan below. A switch takes effect from the next billing cycle.</div>
    @else
    <div style="font-size:13px;color:var(--text-3)">Choose a plan to get started.</div>
    @endif
</div>
<div class="plans-grid" style="margin-bottom:0">
    @foreach($packages as $p)
    @php $isCurrent = $subscription?->package_id === $p->id; @endphp
    <div class="plan-card {{ $isCurrent ? 'is-current' : '' }}">
        @if($isCurrent)<div class="plan-card-badge">Current Plan</div>@endif
        <div class="plan-card-label">{{ strtoupper($p->name) }}</div>
        <div class="plan-card-price">${{ number_format($p->monthly_price ?? 0, 0) }}<span>/month</span></div>
        <div class="plan-card-sub">@if($p->max_users) Up to {{ $p->max_users }} clients @else Unlimited clients @endif</div>
        <ul class="plan-card-features">
            @if($p->max_users)
            <li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ $p->max_users }} clients</li>
            @endif
            @if($p->max_documents)
            <li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ number_format($p->max_documents) }} insights / month</li>
            @endif
            @if(!empty($p->features) && is_array($p->features))
                @foreach(array_slice($p->features, 0, 5) as $feat)
                <li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ Str::title(str_replace('_', ' ', $feat)) }}</li>
                @endforeach
            @else
                @if($p->max_storage_gb)
                <li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ $p->max_storage_gb }}GB storage</li>
                @endif
                @if($p->is_trial_allowed)
                <li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ $p->trial_days }}-day free trial</li>
                @endif
            @endif
        </ul>
        @php $isFreePackage = ($p->is_free ?? false) || ((float)($p->monthly_price ?? 0) <= 0); @endphp
        @php $isCurrentPaid = $subscription && !(!empty($subPkg?->is_free) || ((float)($subPkg?->monthly_price ?? 0) <= 0)); @endphp
        <div style="margin-top:auto">
            @if($isCurrent)
            <button disabled class="btn btn-primary" style="width:100%;justify-content:center;cursor:not-allowed;opacity:.85">Current Plan</button>
            @elseif(!$isFreePackage || !$isCurrentPaid)
            <button class="btn {{ $subscription ? 'btn-ghost' : 'btn-primary' }}" style="width:100%;justify-content:center"
                    onclick="subStartCheckout({{ $p->id }}, this)">
                {{ $subscription ? ($p->monthly_price > ($subPkg?->monthly_price ?? 0) ? 'Upgrade to ' : 'Switch to ') . $p->name : 'Get ' . $p->name }}
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── Payment History ──────────────────────────────────────────── --}}
@if(!empty($payments) && $payments->count())
<div style="margin-top:28px">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:14px">Payment History</div>

    {{-- Filters --}}
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin-bottom:14px">
        <div class="srch-wrap" style="flex:1;min-width:0;max-width:260px">
            <svg class="srch-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="phSearch" class="srch-inp" placeholder="Search payments…">
        </div>

        <select id="phStatusFilter" class="f-sel" style="width:auto;height:34px;min-width:130px;font-size:13px">
            <option value="">All Status</option>
            <option value="complete">Complete</option>
            <option value="failed">Failed</option>
            <option value="pending">Pending</option>
            <option value="refunded">Refunded</option>
        </select>

        <div class="dr-wrap" id="ph-dr">
            <div class="dr-field" onclick="drOpen('ph-dr')">
                <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="dr-text" id="ph-dr-text">Start Date To End Date</span>
                <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('ph-dr')" title="Clear date filter">&#x2715;</button>
            </div>
            <div class="dr-menu" id="ph-dr-menu">
                <div class="dr-list">
                    <div class="dr-item" onclick="drPickPreset('ph-dr','today')">Today</div>
                    <div class="dr-item" onclick="drPickPreset('ph-dr','yesterday')">Yesterday</div>
                    <div class="dr-item" onclick="drPickPreset('ph-dr','last7')">Last 7 days</div>
                    <div class="dr-item" onclick="drPickPreset('ph-dr','last30')">Last 30 days</div>
                    <div class="dr-item" onclick="drPickPreset('ph-dr','thisMonth')">This month</div>
                    <div class="dr-item" onclick="drPickPreset('ph-dr','lastMonth')">Last month</div>
                    <div class="dr-item" onclick="drPickPreset('ph-dr','thisYear')">This year</div>
                </div>
                <div class="dr-custom-row">
                    <input type="date" id="ph-dr-from" class="dr-inp">
                    <span style="color:var(--text-3);font-size:12px">–</span>
                    <input type="date" id="ph-dr-to" class="dr-inp">
                    <button class="dr-apply-btn" onclick="drApplyCustom('ph-dr')">Apply</button>
                </div>
            </div>
        </div>

        <button class="btn btn-danger btn-sm" onclick="phReset()">Reset</button>
    </div>

    {{-- Table --}}
    <div style="background:#fff;border:1.5px solid var(--border);border-radius:12px;overflow:hidden">
        <table id="ph-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--bg-2);border-bottom:1.5px solid var(--border)">
                    <th style="text-align:left;padding:10px 16px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3)">Plan</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3)">Billing Cycle</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3)">Amount</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3)">Status</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3)">Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $ph)
                @php
                    $phStatus   = strtolower($ph->status ?? 'pending');
                    $phBadge    = match($phStatus) {
                        'complete'  => ['b-green',  'Complete'],
                        'failed'    => ['b-red',    'Failed'],
                        'refunded'  => ['b-orange', 'Refunded'],
                        default     => ['b-gray',   'Pending'],
                    };
                    $phPlan     = $ph->subscription?->package?->name ?? '—';
                    $phCycle    = ucfirst($ph->subscription?->billing_cycle ?? '—');
                    $phDate     = $ph->paid_at ?? $ph->created_at;
                @endphp
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-1);font-weight:500">{{ $phPlan }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-2)">{{ $phCycle }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-1);font-weight:600">${{ number_format((float)$ph->amount, 2) }} <span style="font-size:11px;color:var(--text-3);font-weight:400;text-transform:uppercase">{{ strtoupper($ph->currency ?? 'USD') }}</span></td>
                    <td style="padding:11px 16px" data-search="{{ $phStatus }}">
                        <span class="badge {{ $phBadge[0] }}">{{ $phBadge[1] }}</span>
                    </td>
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-2)" data-date="{{ $phDate?->format('Y-m-d') ?? '' }}" data-order="{{ $phDate?->format('Y-m-d') ?? '' }}">
                        {{ $phDate ? $phDate->format(active_date_format()) : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>
@endif {{-- /tab-subscription --}}

{{-- Tab 5: Activity Log --}}
@if($_atabs['tab-activity'])
<div class="tab-panel {{ $_afirst === 'tab-activity' ? 'active' : '' }}" id="tab-activity">
    <div class="info-banner" style="margin-bottom:1.25rem;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--blue);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="info-banner-txt">Activity log records all actions taken within your account. Logs are retained for 90 days.</span>
    </div>

    {{-- Filters --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="tc-filters" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="activitySearch" placeholder="Search activity..." autocomplete="off">
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">Event Type</label>
                <select class="sel" id="actFilterEventType" style="min-width:190px;">
                    <option value="">All Events</option>
                    @foreach(($eventTypes ?? collect()) as $eventType)
                        <option value="{{ $eventType }}">{{ ucwords(str_replace(['_', '-'], ' ', $eventType)) }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Date range filter --}}
            <div class="dr-wrap" id="act-log-dr">
                <div class="dr-field" onclick="drOpen('act-log-dr')">
                    <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span class="dr-text" id="act-log-dr-text">Start Date To End Date</span>
                    <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('act-log-dr')" title="Clear date filter">&#x2715;</button>
                </div>
                <div class="dr-menu" id="act-log-dr-menu">
                    <ul class="dr-list">
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','today',this)">Today</li>
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','last30',this)">Last 30 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','thisMonth',this)">This Month</li>
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','lastMonth',this)">Last Month</li>
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','last90',this)">Last 90 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','last6m',this)">Last 6 Months</li>
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','last1y',this)">Last 1 Year</li>
                        <li class="dr-opt" onclick="drPickPreset('act-log-dr','custom',this)">Custom Range</li>
                    </ul>
                    <div class="dr-custom-row" id="act-log-dr-custom">
                        <input type="date" class="dr-date-inp" id="act-log-dr-from">
                        <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                        <input type="date" class="dr-date-inp" id="act-log-dr-to">
                        <button class="dr-apply-btn" onclick="drApplyCustom('act-log-dr')">Apply</button>
                    </div>
                </div>
            </div>
            <button class="btn btn-danger" onclick="actClearFilters()">Reset</button>
        </div>
    </div>

    {{-- Activity Table --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <div>
                <div class="card-title">Recent Activity</div>
                <div class="card-sub">Showing the latest events across your account.</div>
            </div>
            <span class="badge b-gray" id="activity-count-badge">{{ ($activities ?? collect())->count() }} event{{ ($activities ?? collect())->count() !== 1 ? 's' : '' }}</span>
        </div>

        @if(($activities ?? collect())->isEmpty())
            <div style="text-align:center;padding:3.5rem 1rem;">
                <div style="width:64px;height:64px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p style="font-size:0.9375rem;font-weight:600;color:var(--text-1);margin:0 0 6px 0;">No activity yet</p>
                <p style="font-size:0.8125rem;color:var(--text-2);margin:0;max-width:340px;margin-inline:auto;">
                    Once enabled, all actions taken within your account will appear here for auditing and compliance.
                </p>
            </div>
        @else
            <div class="tc">
                <table id="activity-table" style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>User</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($activities ?? collect()) as $activity)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @php
                                        $eventType = $activity->event_type ?? 'unknown';
                                        $icoClass = match(true) {
                                            str_contains($eventType, 'login')   => 'activity-dot-success',
                                            str_contains($eventType, 'delete')  => 'activity-dot-danger',
                                            str_contains($eventType, 'create')  => 'activity-dot-info',
                                            str_contains($eventType, 'update')  => 'activity-dot-warning',
                                            default                             => 'activity-dot-muted',
                                        };
                                    @endphp
                                    <span class="{{ $icoClass }}"></span>
                                    <span style="font-weight:500;color:var(--text-1);font-size:0.875rem;">{{ $activity->display_event }}</span>
                                </div>
                                @if(!empty($activity->display_description) && $activity->display_description !== $activity->display_event)
                                    <div style="font-size:0.75rem;color:var(--text-3);margin-top:2px;padding-left:16px;">{{ $activity->display_description }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size:0.875rem;font-weight:500;color:var(--text-1);">{{ optional($activity->user)->name ?? 'System' }}</div>
                                <div style="font-size:0.75rem;color:var(--text-2);">{{ optional($activity->user)->email ?? '' }}</div>
                            </td>
                            <td style="font-size:0.8125rem;color:var(--text-2);" data-order="{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->timestamp : 0 }}" data-date="{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('Y-m-d') : '' }}">
                                <div>{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format(active_date_format()) : '—' }}</div>
                                <div style="color:var(--text-3);">{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('g:i A') : '' }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif {{-- /tab-activity --}}

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

function previewFavicon(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.getElementById('favicon-preview-wrap');
            if (wrap) {
                wrap.innerHTML = '<img id="favicon-preview-img" src="' + e.target.result + '" style="width:32px;height:32px;object-fit:contain;display:block">';
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

            // Update browser tab favicon live if a new one was saved
            if (json.favicon_url) {
                let link = document.querySelector("link[rel='icon']");
                if (!link) {
                    link = document.createElement('link');
                    link.rel = 'icon';
                    document.head.appendChild(link);
                }
                link.href = json.favicon_url;
                // Update preview in the upload zone
                const wrap = document.getElementById('favicon-preview-wrap');
                if (wrap) {
                    wrap.innerHTML = '<img id="favicon-preview-img" src="' + json.favicon_url + '" style="width:32px;height:32px;object-fit:contain;display:block">';
                }
            }

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

            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: json.message || 'Branding updated successfully.', confirmButtonText: 'Ok', confirmButtonColor: btnColor });
        } else {
            showToast(json.message || 'Failed to save branding.', 'error');
        }
    } catch (err) {
        showToast('An error occurred. Please try again.', 'error');
    }

    btn.disabled  = false;
    btn.innerHTML = origText;
});

// Auto-switch tab based on URL hash or ?tab= query param
document.addEventListener('DOMContentLoaded', function () {
    var hash    = window.location.hash.replace('#', '');
    var tabParam = new URLSearchParams(window.location.search).get('tab');
    var target  = hash || tabParam;
    if (target) {
        var btn = document.querySelector('[data-tab="tab-' + target + '"]');
        if (btn) { btn.click(); return; }
    }
    // Legacy: plain "#branding" without "tab-" prefix
    if (hash === 'branding') {
        var b = document.querySelector('[data-tab="tab-branding"]');
        if (b) b.click();
    }
});

// ── Activity Log ──────────────────────────────────────────────────────────────
@if($_atabs['tab-activity'])
var _actDtSearchFn = null;

function initActivityTable() {
    if (!window.$ || !document.getElementById('activity-table')) return;

    // Remove any previously registered date-range search fn to avoid duplicates
    if (_actDtSearchFn) {
        var idx = $.fn.dataTable.ext.search.indexOf(_actDtSearchFn);
        if (idx > -1) $.fn.dataTable.ext.search.splice(idx, 1);
        _actDtSearchFn = null;
    }
    // Destroy existing DataTable instance if present
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#activity-table')) {
        try { $('#activity-table').DataTable().destroy(); } catch (ex) {}
    }

    var actTable = $('#activity-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true,
        dom: '<"dt-tscroll"t><"dt-footer"ip>',
        order: [[2, 'desc']],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ events',
            infoEmpty: 'No activity found',
            paginate: { previous: '&lsaquo;', next: '&rsaquo;' },
            emptyTable: '<div style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No activity yet</div>',
        },
        drawCallback: function () {
            var info  = this.api().page.info();
            var badge = document.getElementById('activity-count-badge');
            if (badge) badge.textContent = info.recordsDisplay + ' event' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    document.getElementById('activitySearch').addEventListener('keyup', function () {
        actTable.search(this.value).draw();
    });

    document.getElementById('actFilterEventType').addEventListener('change', function () {
        actTable.column(0).search(this.value ? this.value : '', false, false).draw();
    });

    // Date range filter — stored so it can be removed before the next re-init
    _actDtSearchFn = function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'activity-table') return true;
        var from = document.getElementById('act-log-dr-from') ? document.getElementById('act-log-dr-from').value : '';
        var to   = document.getElementById('act-log-dr-to')   ? document.getElementById('act-log-dr-to').value   : '';
        if (!from && !to) return true;
        var row = actTable.row(dataIndex).node();
        if (!row) return true;
        var dateTd  = row.querySelector('td[data-date]');
        var dateVal = dateTd ? dateTd.getAttribute('data-date') : '';
        if (!dateVal) return true;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    };
    $.fn.dataTable.ext.search.push(_actDtSearchFn);
    _drCbs['act-log-dr'] = function (from, to) { actTable.draw(); };
}

$(document).ready(function () { initActivityTable(); });

function actClearFilters() {
    document.getElementById('activitySearch').value    = '';
    drClear('act-log-dr');
    document.getElementById('actFilterEventType').value = '';
    if (window.$.fn && $('#activity-table').length) {
        $('#activity-table').DataTable().search('').columns().search('').draw();
    }
}
@endif

// ── Tab auto-refresh (Activity Log & Subscription tabs) ───────────────────────
// Fires whenever the global tab-switcher dispatches 'tabActivated'.
// Only the two live-data tabs are refreshed; edit-form tabs (Profile, Branding,
// Login Page) are intentionally skipped so unsaved changes are not lost.
document.addEventListener('tabActivated', function (e) {
    var tabId = e.detail.tabId;
    // Guard: only run on the My Account page
    if (!document.getElementById('tab-profile')) return;
    if (['tab-activity', 'tab-subscription'].indexOf(tabId) === -1) return;
    _refreshAccountTab(tabId);
});

function _refreshAccountTab(tabId) {
    var panel = document.getElementById(tabId);
    if (!panel) return;

    // Dim the panel while loading
    panel.style.opacity       = '0.45';
    panel.style.pointerEvents = 'none';

    fetch(window.location.pathname, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.text(); })
        .then(function (html) {
            var doc   = new DOMParser().parseFromString(html, 'text/html');
            var fresh = doc.getElementById(tabId);
            if (fresh) {
                // Tear down Activity Log DataTable before swapping HTML
                if (tabId === 'tab-activity' && typeof _actDtSearchFn !== 'undefined') {
                    if (_actDtSearchFn) {
                        var idx = $.fn.dataTable.ext.search.indexOf(_actDtSearchFn);
                        if (idx > -1) $.fn.dataTable.ext.search.splice(idx, 1);
                        _actDtSearchFn = null;
                    }
                    if (window.$ && $.fn.DataTable && $.fn.DataTable.isDataTable('#activity-table')) {
                        try { $('#activity-table').DataTable().destroy(); } catch (ex) {}
                    }
                }

                // Tear down Payment History DataTable before swapping HTML
                if (tabId === 'tab-subscription') {
                    if (window.$ && $.fn.DataTable && $.fn.DataTable.isDataTable('#ph-table')) {
                        try { $('#ph-table').DataTable().destroy(); phTable = null; } catch (ex) {}
                    }
                }

                panel.innerHTML = fresh.innerHTML;

                // Re-initialise tab-specific JS after new HTML is in the DOM
                if (tabId === 'tab-activity' && typeof initActivityTable === 'function') {
                    initActivityTable();
                }

                // Re-initialise Payment History DataTable
                if (tabId === 'tab-subscription' && document.getElementById('ph-table') && window.$) {
                    phTable = $('#ph-table').DataTable({
                        paging: true, pageLength: 5, lengthChange: false,
                        searching: true, ordering: true, info: true,
                        dom: '<"dt-tscroll"t><"dt-footer"ip>',
                        order: [[4, 'desc']],
                        columnDefs: [{ orderable: false, targets: [3] }],
                        language: { info: 'Showing _START_ to _END_ of _TOTAL_ payments', infoEmpty: 'No payment records found', paginate: { previous: 'Previous', next: 'Next' }, emptyTable: 'No payment records found.' },
                    });
                    _drCbs['ph-dr'] = function () { if (phTable) phTable.draw(); };
                    var phS = document.getElementById('phSearch');
                    if (phS) phS.addEventListener('input', function () { if (phTable) phTable.search(this.value).draw(); });
                    var phSt = document.getElementById('phStatusFilter');
                    if (phSt) phSt.addEventListener('change', function () { if (phTable) phTable.column(3).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw(); });
                }
            }
            panel.style.opacity       = '';
            panel.style.pointerEvents = '';
        })
        .catch(function () {
            panel.style.opacity       = '';
            panel.style.pointerEvents = '';
        });
}

// ── Login Page ────────────────────────────────────────────────────────────────
@if($_atabs['tab-login-page'])
document.addEventListener('DOMContentLoaded', function () {
    var lpBtn = document.querySelector('[data-tab="tab-login-page"]');
    if (lpBtn) {
        lpBtn.addEventListener('click', function () {
            setTimeout(function () { lpUpdateBgTypeVisibility(); lpUpdatePreview(); }, 50);
        });
    }
    lpUpdateBgTypeVisibility();
    lpUpdatePreview();
});

function lpSyncColor(colorInputId, textInputId) {
    var ci = document.getElementById(colorInputId);
    var ti = document.getElementById(textInputId);
    if (!ci || !ti) return;
    if (/^#[0-9a-fA-F]{6}$/.test(ti.value)) ci.value = ti.value;
}

function lpUpdateBgTypeVisibility() {
    var type = document.querySelector('input[name="login_bg_type"]:checked');
    if (!type) return;
    var isGradient = type.value === 'gradient';
    var isImage    = type.value === 'image';

    var colorWrap   = document.getElementById('lp-colors-wrap');
    var colorToWrap = document.getElementById('lp-bg-color-to-wrap');
    var imageWrap   = document.getElementById('lp-bg-image-wrap');
    var bgLabel     = document.getElementById('lp-bg-color-label');

    if (colorWrap)   colorWrap.style.display   = isImage ? 'none' : 'flex';
    if (colorToWrap) colorToWrap.style.display  = isGradient ? '' : 'none';
    if (imageWrap)   imageWrap.style.display    = isImage ? 'block' : 'none';
    if (bgLabel)     bgLabel.textContent        = isGradient ? 'Gradient Start Color' : 'Background Color';
}

function lpUpdatePreview() {
    var type      = document.querySelector('input[name="login_bg_type"]:checked');
    var bgColor   = document.getElementById('lp-bg-color')?.value    || '#0d3d66';
    var bgColorTo = document.getElementById('lp-bg-color-to')?.value || '#f06b21';
    var cardBg    = document.getElementById('lp-card-bg')?.value     || '#ffffff';
    var btnColor  = document.getElementById('lp-btn-color')?.value   || '#f06b21';
    var heading   = document.getElementById('lp-heading')?.value     || 'Welcome back';
    var subheading= document.getElementById('lp-subheading')?.value  || '';

    var bg   = document.getElementById('lp-preview-bg');
    var card = document.getElementById('lp-preview-card');
    var btn  = document.getElementById('lp-preview-btn');
    var h    = document.getElementById('lp-preview-heading');
    var sub  = document.getElementById('lp-preview-subheading');
    if (!bg || !card) return;

    var bgStyle = '';
    if (type) {
        if (type.value === 'solid') {
            bgStyle = 'background:' + bgColor;
        } else if (type.value === 'image') {
            bgStyle = bg.dataset.bgImgUrl
                ? "background:url('" + bg.dataset.bgImgUrl + "') center/cover no-repeat"
                : 'background:linear-gradient(135deg,' + bgColor + ' 0%,' + bgColorTo + ' 130%)';
        } else {
            bgStyle = 'background:linear-gradient(135deg,' + bgColor + ' 0%,' + bgColorTo + ' 130%)';
        }
    } else {
        bgStyle = 'background:linear-gradient(135deg,' + bgColor + ' 0%,' + bgColorTo + ' 130%)';
    }

    bg.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;transition:background .25s;' + bgStyle;
    card.style.background = cardBg;
    if (btn) btn.style.background = btnColor;
    if (h)   h.textContent        = heading    || 'Welcome back';
    if (sub) sub.textContent      = subheading || '';
}

function lpPreviewBgImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var bg = document.getElementById('lp-preview-bg');
            if (bg) { bg.dataset.bgImgUrl = e.target.result; lpUpdatePreview(); }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function lpPreviewLogoImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var wrap = document.getElementById('lp-preview-logo-wrap');
            if (wrap) wrap.innerHTML = '<img src="' + e.target.result + '" alt="Logo" style="max-height:36px;max-width:140px;object-fit:contain;display:block;margin:0 auto">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function submitLoginPageForm(e) {
    e.preventDefault();
    var form = document.getElementById('login-page-form');
    var btn  = document.getElementById('lp-submit');
    var data = new FormData(form);
    btn.disabled = true;
    fetch('{{ parse_url(route("settings.login-page.update"), PHP_URL_PATH) }}', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: data,
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        btn.disabled = false;
        if (res.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: res.message || 'Login page saved.', confirmButtonText: 'Ok', confirmButtonColor: btnColor });
        } else {
            showToast(res.message || 'Something went wrong.', 'error');
        }
    })
    .catch(function () { btn.disabled = false; showToast('Request failed.', 'error'); });
}
@endif

// ── Forgot Password Page ──────────────────────────────────────────────────────
@if($_atabs['tab-forgot-password'])
document.addEventListener('DOMContentLoaded', function () {
    var fpBtn = document.querySelector('[data-tab="tab-forgot-password"]');
    if (fpBtn) {
        fpBtn.addEventListener('click', function () {
            setTimeout(function () { fpUpdatePreview(); }, 50);
        });
    }
    fpUpdatePreview();
});

function fpUpdatePreview() {
    var bg     = document.getElementById('fp-preview-bg');
    var card   = document.getElementById('fp-preview-card');
    var btn    = document.getElementById('fp-preview-btn');
    var h      = document.getElementById('fp-preview-heading');
    var sub    = document.getElementById('fp-preview-subheading');
    if (!bg || !card) return;

    var heading    = document.getElementById('fp-heading')?.value    || 'Forgot your password?';
    var subheading = document.getElementById('fp-subheading')?.value || '';

    // Background: read from data-* attributes (set server-side from login page branding)
    var bgType    = bg.dataset.bgType    || 'gradient';
    var bgColor   = bg.dataset.bgColor   || '#0d3d66';
    var bgColorTo = bg.dataset.bgColorTo || '#f06b21';
    var bgImgUrl  = bg.dataset.bgImgUrl  || '';

    var bgStyle = '';
    if (bgType === 'solid') {
        bgStyle = 'background:' + bgColor;
    } else if (bgType === 'image' && bgImgUrl) {
        bgStyle = "background:url('" + bgImgUrl + "') center/cover no-repeat";
    } else {
        bgStyle = 'background:linear-gradient(135deg,' + bgColor + ' 0%,' + bgColorTo + ' 130%)';
    }

    bg.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;' + bgStyle;
    if (h)   h.textContent   = heading    || 'Forgot your password?';
    if (sub) sub.textContent = subheading || '';
}

function submitFpPageForm(e) {
    e.preventDefault();
    var form = document.getElementById('fp-page-form');
    var btn  = document.getElementById('fp-submit');
    var data = new FormData(form);
    btn.disabled = true;
    fetch('{{ parse_url(route("settings.forgot-password-page.update"), PHP_URL_PATH) }}', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: data,
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        btn.disabled = false;
        if (res.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: res.message || 'Forgot password page saved.', confirmButtonText: 'Ok', confirmButtonColor: btnColor });
        } else {
            showToast(res.message || 'Something went wrong.', 'error');
        }
    })
    .catch(function () { btn.disabled = false; showToast('Request failed.', 'error'); });
}
@endif

// ── Subscription Tab ──────────────────────────────────────────────────────────
@if($_atabs['tab-subscription'])
async function subStartCheckout(packageId, btn) {
    const orig = btn.textContent.trim();
    btn.disabled = true;
    btn.textContent = 'Redirecting…';
    try {
        const res = await fetch('{{ parse_url(route("billing.checkout"), PHP_URL_PATH) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ package_id: packageId, billing_cycle: 'monthly' }),
        });
        const json = await res.json();
        if (json.checkout_url) {
            window.location.href = json.checkout_url;
        } else {
            showToast(json.message || 'Could not start checkout.', 'error');
            btn.disabled = false; btn.textContent = orig;
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false; btn.textContent = orig;
    }
}

function subCancel() {
    Swal.fire({
        title: 'Cancel Subscription?',
        text: 'Your plan will remain active until the end of the current billing period. You can re-subscribe at any time.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel Subscription',
        cancelButtonText: 'Keep Subscription',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(async function(result) {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Canceling…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const resp = await fetch('{{ parse_url(route("billing.cancel-subscription"), PHP_URL_PATH) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const res = await resp.json();
            if (res.status === 'success') {
                await Swal.fire({ icon: 'success', title: 'Subscription Canceled', text: res.message || 'You will retain access until the end of the current billing period.', confirmButtonText: 'OK' });
                window.location.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Cancellation Failed', text: res.message || 'Something went wrong.' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not reach the server. Please try again.' });
        }
    });
}

async function subReactivate() {
    const result = await Swal.fire({
        title: 'Reactivate Subscription?',
        text: 'Your subscription will be restored.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reactivate',
        cancelButtonText: 'Not Now',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });
    if (!result.isConfirmed) return;
    Swal.fire({ title: 'Reactivating…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const resp = await fetch('{{ parse_url(route("billing.reactivate-subscription"), PHP_URL_PATH) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        const res = await resp.json();
        if (res.status === 'success') {
            await Swal.fire({ icon: 'success', title: 'Subscription Reactivated', text: res.message || 'Welcome back!', confirmButtonText: 'Go to Dashboard' });
            window.location.reload();
        } else {
            Swal.fire({ icon: 'error', title: 'Reactivation Failed', text: res.message || 'Something went wrong.' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not reach the server. Please try again.' });
    }
}

// ── Payment History DataTable ──────────────────────────────────────────────────
var phTable = null;

$(document).ready(function () {
    if (!document.getElementById('ph-table')) return;

    phTable = $('#ph-table').DataTable({
        paging:       true,
        pageLength:   5,
        lengthChange: false,
        searching:    true,
        ordering:     true,
        info:         true,
        dom:          '<"dt-tscroll"t><"dt-footer"ip>',
        order:        [[4, 'desc']],
        columnDefs:   [{ orderable: false, targets: [3] }],
        language: {
            info:       'Showing _START_ to _END_ of _TOTAL_ payments',
            infoEmpty:  'No payment records found',
            paginate:   { previous: 'Previous', next: 'Next' },
            emptyTable: 'No payment records found.',
        },
    });

    // Date-range filter on Payment Date column (index 4)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'ph-table') return true;
        var from = document.getElementById('ph-dr-from') ? document.getElementById('ph-dr-from').value : '';
        var to   = document.getElementById('ph-dr-to')   ? document.getElementById('ph-dr-to').value   : '';
        if (!from && !to) return true;
        var row     = phTable.row(dataIndex).node();
        var dateVal = row ? (row.querySelector('td[data-date]')?.getAttribute('data-date') || '') : '';
        if (!dateVal) return false;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    });

    _drCbs['ph-dr'] = function () { if (phTable) phTable.draw(); };

    // Search input
    var phSearchEl = document.getElementById('phSearch');
    if (phSearchEl) phSearchEl.addEventListener('input', function () {
        if (phTable) phTable.search(this.value).draw();
    });

    // Status filter
    var phStatusEl = document.getElementById('phStatusFilter');
    if (phStatusEl) phStatusEl.addEventListener('change', function () {
        if (phTable) phTable.column(3).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
});

window.phReset = function () {
    document.getElementById('phSearch').value       = '';
    document.getElementById('phStatusFilter').value = '';
    drClear('ph-dr');
    if (phTable) phTable.search('').columns().search('').draw();
};
@endif

// ── Admin Date Format (Branding tab) ─────────────────────────────────────────
// Use var (not let/const) so there is no temporal dead zone if a script error
// above this line prevents initialization from completing.
var adfSamples = {!! json_encode(array_map(fn($fmt) => \Carbon\Carbon::now()->format($fmt), array_keys(DATE_FORMAT_PRESETS))) !!};
var adfKeys    = {!! json_encode(array_keys(DATE_FORMAT_PRESETS)) !!};
var adfCurrent = '{{ $effectiveFmt ?? active_date_format() }}';

function adfPickFormat(fmt) {
    adfCurrent = fmt;
    const idx = adfKeys.indexOf(fmt);
    document.getElementById('adf-preview').textContent = idx >= 0 ? adfSamples[idx] : fmt;

    document.querySelectorAll('#adf-grid label').forEach(function(lbl) {
        const inp = lbl.querySelector('input');
        if (inp.value === fmt) {
            lbl.style.borderColor = 'var(--blue)';
            lbl.style.background  = 'var(--blue-pale)';
        } else {
            lbl.style.borderColor = 'var(--border)';
            lbl.style.background  = '#fff';
        }
    });
}

async function adfSave() {
    const btn = document.getElementById('adf-save-btn');
    if (!btn) return;
    const orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = 'Saving…';
    try {
        const fd = new FormData();
        fd.append('date_format', adfCurrent);
        const r = await fetch('{{ parse_url(route("account.date-format"), PHP_URL_PATH) }}', {
            method: 'POST', body: fd,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        let j;
        try { j = await r.json(); } catch(_) { j = { status: 'fail', message: 'Server error (HTTP ' + r.status + ').' }; }
        if (j.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: j.message, confirmButtonText: 'OK', confirmButtonColor: btnColor })
                .then(() => window.location.reload());
        } else { showToast(j.message || 'Error saving.', 'error'); }
    } catch(e) { showToast('Request failed. Please try again.', 'error'); }
    btn.disabled = false; btn.innerHTML = orig;
}

async function adfClear() {
    try {
        const fd = new FormData();
        fd.append('date_format', '');   // empty = clear override
        const r = await fetch('{{ parse_url(route("account.date-format"), PHP_URL_PATH) }}', {
            method: 'POST', body: fd,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        let j;
        try { j = await r.json(); } catch(_) { j = { status: 'fail', message: 'Server error (HTTP ' + r.status + ').' }; }
        if (j.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: 'Reset to platform default.', confirmButtonText: 'OK', confirmButtonColor: btnColor })
                .then(() => window.location.reload());
        } else { showToast(j.message || 'Error.', 'error'); }
    } catch(e) { showToast('Request failed. Please try again.', 'error'); }
}

// ── Profile & Password Validation ─────────────────────────────────────────────
function acErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'flex' : 'none';
}
function acMark(id, invalid) {
    var el = document.getElementById(id);
    if (!el) return;
    if (invalid) el.classList.add('ac-invalid');
    else el.classList.remove('ac-invalid');
}

function validateAndSubmitProfile() {
    var ok = true;
    var nameEl  = document.getElementById('ac-profile-name');
    var nameVal = nameEl ? nameEl.value.trim() : '';
    if (!nameVal) {
        acErr('err-profile-name', 'Full name is required.'); acMark('ac-profile-name', true); ok = false;
    } else if (nameVal.length < 2) {
        acErr('err-profile-name', 'Minimum 2 characters required.'); acMark('ac-profile-name', true); ok = false;
    } else if (!/^[a-zA-Z\s]+$/.test(nameVal)) {
        acErr('err-profile-name', 'Name must contain only letters and spaces.'); acMark('ac-profile-name', true); ok = false;
    } else {
        acErr('err-profile-name', ''); acMark('ac-profile-name', false);
        if (nameEl) nameEl.value = nameVal;
    }
    if (ok) document.getElementById('profile-form').submit();
}

// Real-time: restrict name to letters + spaces
(function() {
    var el = document.getElementById('ac-profile-name');
    if (!el) return;
    el.addEventListener('input', function() {
        var pos = this.selectionStart;
        var cleaned = this.value.replace(/[^a-zA-Z\s]/g, '');
        if (cleaned !== this.value) {
            this.value = cleaned;
            this.setSelectionRange(Math.max(0, pos - 1), Math.max(0, pos - 1));
        }
    });
})();

function validateAndSubmitPassword() {
    var ok = true;
    var curEl  = document.getElementById('ac-cur-pwd');
    var newEl  = document.getElementById('ac-new-pwd');
    var confEl = document.getElementById('ac-confirm-pwd');

    var curVal = curEl  ? curEl.value  : '';
    var newVal = newEl  ? newEl.value  : '';
    var confVal = confEl ? confEl.value : '';

    if (!curVal) {
        acErr('err-cur-pwd', 'Current password is required.'); acMark('ac-cur-pwd', true); ok = false;
    } else {
        acErr('err-cur-pwd', ''); acMark('ac-cur-pwd', false);
    }

    if (!newVal) {
        acErr('err-new-pwd', 'New password is required.'); acMark('ac-new-pwd', true); ok = false;
    } else if (newVal.length < 8) {
        acErr('err-new-pwd', 'Password must be at least 8 characters.'); acMark('ac-new-pwd', true); ok = false;
    } else {
        acErr('err-new-pwd', ''); acMark('ac-new-pwd', false);
    }

    if (!confVal) {
        acErr('err-confirm-pwd', 'Please confirm your new password.'); acMark('ac-confirm-pwd', true); ok = false;
    } else if (confVal !== newVal) {
        acErr('err-confirm-pwd', 'Passwords do not match.'); acMark('ac-confirm-pwd', true); ok = false;
    } else {
        acErr('err-confirm-pwd', ''); acMark('ac-confirm-pwd', false);
    }

    if (ok) document.getElementById('password-form').submit();
}
</script>
@endpush

@push('styles')
<style>
/* ── Plan banner & plan cards (Subscription tab) ── */
.plan-banner {
    background: linear-gradient(135deg, #1a3a5c 0%, #1a6aa8 100%);
    border-radius: 14px;
    padding: 24px 26px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}
.plan-banner-left .plan-label {
    font-size: 10.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .12em; color: rgba(255,255,255,.65); margin-bottom: 5px;
}
.plan-banner-left .plan-name  { font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 5px; }
.plan-banner-left .plan-meta  { font-size: 13px; color: rgba(255,255,255,.75); }
.plan-banner-right            { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.plan-status-badge {
    background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.35);
    color: #fff; border-radius: 8px; padding: 5px 14px; font-size: 13px; font-weight: 600;
}
.plan-cancel-btn {
    background: rgba(255,255,255,.12); border: 1.5px solid rgba(255,255,255,.4);
    color: #fff; border-radius: 8px; padding: 7px 16px; font-size: 13px; font-weight: 500;
    cursor: pointer; transition: background .13s;
}
.plan-cancel-btn:hover { background: rgba(255,255,255,.22); }
.plan-reactivate-btn {
    background: rgba(22,163,74,.2); border: 1.5px solid rgba(22,163,74,.6);
    color: #fff; border-radius: 8px; padding: 7px 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: background .13s;
}
.plan-reactivate-btn:hover { background: rgba(22,163,74,.35); }
.plans-grid {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 24px;
}
.plan-card {
    background: #fff; border: 1.5px solid var(--border); border-radius: 12px;
    padding: 22px 20px 18px; position: relative; display: flex; flex-direction: column;
}
.plan-card.is-current { border-color: var(--orange); box-shadow: 0 0 0 1px var(--orange); }
.plan-card-badge {
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    background: var(--orange); color: #fff; font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em; padding: 3px 12px;
    border-radius: 20px; white-space: nowrap;
}
.plan-card-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--text-3); margin-bottom: 8px; }
.plan-card.is-current .plan-card-label { color: var(--orange); }
.plan-card-price { font-size: 32px; font-weight: 700; color: var(--text-1); margin-bottom: 4px; line-height: 1; }
.plan-card-price span { font-size: 14px; font-weight: 400; color: var(--text-3); }
.plan-card-sub { font-size: 12.5px; color: var(--text-3); margin-bottom: 16px; }
.plan-card-features { list-style: none; margin-bottom: 20px; flex: 1; }
.plan-card-features li { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-2); margin-bottom: 8px; }
.plan-card-features li svg { flex-shrink: 0; }
@media(max-width:960px) { .plans-grid { grid-template-columns: 1fr; } }

.activity-dot-success,.activity-dot-danger,.activity-dot-info,.activity-dot-warning,.activity-dot-muted {
    width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block;
}
.activity-dot-success { background:#16a34a; }
.activity-dot-danger  { background:#dc2626; }
.activity-dot-info    { background:var(--blue); }
.activity-dot-warning { background:var(--orange); }
.activity-dot-muted   { background:var(--text-3); }
.ac-err {
    font-size: 11.5px; color: var(--red); margin-top: 4px;
    display: flex; align-items: center; gap: 4px;
}
.ac-err::before {
    content: '';
    display: inline-block; width: 12px; height: 12px; flex-shrink: 0;
    background: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='2'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cline x1='12' y1='8' x2='12' y2='12'/%3E%3Cline x1='12' y1='16' x2='12.01' y2='16'/%3E%3C/svg%3E") center/contain no-repeat;
}
.f-inp.ac-invalid { border-color: var(--red) !important; }
</style>
@endpush
