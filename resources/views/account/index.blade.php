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
    @if(auth()->user()->can('edit_branding'))
    <button class="tab-btn" data-tab="tab-login-page">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
        Login Page
    </button>
    @endif
    @if(auth()->user()->hasAnyRole(['admin','manager']))
    <button class="tab-btn" data-tab="tab-activity">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Activity Log
    </button>
    @endif
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
</div>

{{-- Tab 3: Login Page --}}
@if(auth()->user()->can('edit_branding'))
<div class="tab-panel" id="tab-login-page">
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
@endif

{{-- Tab 4: Activity Log --}}
@if(auth()->user()->hasAnyRole(['admin','manager']))
<div class="tab-panel" id="tab-activity">
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
                <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">From</label>
                <input type="date" class="f-inp" id="actFilterFrom" style="min-width:150px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">To</label>
                <input type="date" class="f-inp" id="actFilterTo" style="min-width:150px;">
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
            <button class="btn btn-ghost btn-sm" onclick="actClearFilters()">Clear</button>
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
                            <td style="font-size:0.8125rem;color:var(--text-2);" data-order="{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->timestamp : 0 }}">
                                <div>{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('M j, Y') : '—' }}</div>
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
@endif

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
@if(auth()->user()->hasAnyRole(['admin','manager']))
$(document).ready(function () {
    if (!document.getElementById('activity-table')) return;

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
            var info = this.api().page.info();
            var badge = document.getElementById('activity-count-badge');
            if (badge) badge.textContent = info.recordsDisplay + ' event' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    document.getElementById('activitySearch').addEventListener('keyup', function () {
        actTable.search(this.value).draw();
    });

    document.getElementById('actFilterFrom').addEventListener('change', function () { actTable.draw(); });
    document.getElementById('actFilterTo').addEventListener('change', function () { actTable.draw(); });
    document.getElementById('actFilterEventType').addEventListener('change', function () {
        actTable.column(0).search(this.value ? this.value : '', false, false).draw();
    });
});

function actClearFilters() {
    document.getElementById('activitySearch').value    = '';
    document.getElementById('actFilterFrom').value     = '';
    document.getElementById('actFilterTo').value       = '';
    document.getElementById('actFilterEventType').value = '';
    if (window.$.fn && $('#activity-table').length) {
        $('#activity-table').DataTable().search('').columns().search('').draw();
    }
}
@endif

// ── Login Page ────────────────────────────────────────────────────────────────
@if(auth()->user()->can('edit_branding'))
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
    fetch('{{ route("settings.login-page.update") }}', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: data,
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        btn.disabled = false;
        showToast(res.status === 'success' ? (res.message || 'Login page saved.') : (res.message || 'Something went wrong.'), res.status === 'success' ? 'success' : 'error');
    })
    .catch(function () { btn.disabled = false; showToast('Request failed.', 'error'); });
}
@endif
</script>
@endpush

@push('styles')
<style>
.activity-dot-success,.activity-dot-danger,.activity-dot-info,.activity-dot-warning,.activity-dot-muted {
    width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block;
}
.activity-dot-success { background:#16a34a; }
.activity-dot-danger  { background:#dc2626; }
.activity-dot-info    { background:var(--blue); }
.activity-dot-warning { background:var(--orange); }
.activity-dot-muted   { background:var(--text-3); }
</style>
@endpush
