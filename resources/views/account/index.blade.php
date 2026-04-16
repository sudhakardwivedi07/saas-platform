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
    <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="brand-layout">

            {{-- Left Column --}}
            <div>
                {{-- Logo Upload --}}
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Logo
                    </div>
                    <label class="upload-zone" for="logo-upload">
                        @if($branding?->logo)
                        <img src="{{ asset('storage/' . $branding->logo) }}" alt="Current Logo" style="max-height:48px;max-width:180px;object-fit:contain;display:block;margin:0 auto 10px">
                        <p style="font-size:12px;color:var(--text-3)">Click to replace</p>
                        @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p>Click to upload logo</p>
                        <p style="font-size:12px;color:var(--text-3);margin-top:4px">PNG, JPG up to 2MB</p>
                        @endif
                    </label>
                    <input type="file" name="logo" id="logo-upload" accept="image/png,image/jpeg,image/jpg,image/svg+xml" style="display:none" onchange="previewLogo(this)">
                </div>

                {{-- Colors --}}
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="6.5" cy="11.5" r="2.5"/><circle cx="17.5" cy="15.5" r="2.5"/><circle cx="10" cy="19" r="2"/></svg>
                        Brand Colors
                    </div>

                    <div style="display:flex;flex-direction:column;gap:10px">
                        {{-- Primary Color --}}
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;border:1px solid var(--border);border-radius:9px;background:var(--bg)">
                            <label for="primary-color-inp" style="cursor:pointer;display:flex;align-items:center;gap:0">
                                <div id="primary-swatch" style="width:28px;height:28px;border-radius:7px;background:{{ $branding->primary_color ?? 'var(--blue)' }};cursor:pointer;border:2px solid rgba(0,0,0,.08);flex-shrink:0"></div>
                                <input type="color" id="primary-color-inp" name="primary_color" value="{{ $branding->primary_color ?? '#1a6aa8' }}" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none" onchange="document.getElementById('primary-swatch').style.background=this.value;document.getElementById('primary-hex').textContent=this.value">
                            </label>
                            <div style="flex:1">
                                <div style="font-size:13px;font-weight:500;color:var(--text-1)">Primary</div>
                                <div id="primary-hex" style="font-size:11.5px;color:var(--text-3);font-family:monospace">{{ $branding->primary_color ?? '#1a6aa8' }}</div>
                            </div>
                            <button type="button" class="btn btn-ghost btn-xs" onclick="document.getElementById('primary-color-inp').click()">Change</button>
                        </div>

                        {{-- Accent Color --}}
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;border:1px solid var(--border);border-radius:9px;background:var(--bg)">
                            <label for="accent-color-inp" style="cursor:pointer;display:flex;align-items:center;gap:0">
                                <div id="accent-swatch" style="width:28px;height:28px;border-radius:7px;background:{{ $branding->accent_color ?? 'var(--orange)' }};cursor:pointer;border:2px solid rgba(0,0,0,.08);flex-shrink:0"></div>
                                <input type="color" id="accent-color-inp" name="accent_color" value="{{ $branding->accent_color ?? '#f06b21' }}" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none" onchange="document.getElementById('accent-swatch').style.background=this.value;document.getElementById('accent-hex').textContent=this.value">
                            </label>
                            <div style="flex:1">
                                <div style="font-size:13px;font-weight:500;color:var(--text-1)">Accent</div>
                                <div id="accent-hex" style="font-size:11.5px;color:var(--text-3);font-family:monospace">{{ $branding->accent_color ?? '#f06b21' }}</div>
                            </div>
                            <button type="button" class="btn btn-ghost btn-xs" onclick="document.getElementById('accent-color-inp').click()">Change</button>
                        </div>
                    </div>
                </div>

                {{-- Custom Domain --}}
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                        Custom Domain
                    </div>
                    <div style="display:flex;align-items:stretch;border:1px solid var(--border);border-radius:9px;overflow:hidden;background:#fff">
                        <input type="text" name="custom_domain" class="f-inp" placeholder="yourcompany" value="{{ $branding->custom_domain ?? '' }}" style="border:none;border-radius:0;flex:1;background:#fff">
                        <div style="display:flex;align-items:center;padding:0 13px;background:var(--bg);border-left:1px solid var(--border);font-size:13px;font-weight:500;color:var(--text-2);white-space:nowrap;flex-shrink:0">.saas.app</div>
                    </div>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:6px">Your sub-companies will be hosted at yourcompany.saas.app</div>
                </div>

                {{-- Save Button --}}
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Branding
                </button>
            </div>

            {{-- Right Column: Sticky Preview --}}
            <div class="preview-sticky">
                <div class="preview-card">
                    <div class="preview-head">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Preview
                    </div>
                    <div style="display:flex;height:260px">
                        {{-- Mini Sidebar --}}
                        <div style="width:80px;background:#fff;border-right:1px solid var(--border);padding:10px 0;display:flex;flex-direction:column;align-items:center;gap:2px">
                            {{-- Logo Placeholder --}}
                            <div id="preview-logo-wrap" style="width:42px;height:36px;border-radius:7px;background:var(--bg);border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;margin-bottom:8px">
                                @if($branding?->logo)
                                <img id="preview-logo-img" src="{{ asset('storage/' . $branding->logo) }}" style="max-width:38px;max-height:32px;object-fit:contain;display:block">
                                @else
                                <svg id="preview-logo-img" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                @endif
                            </div>
                            {{-- Nav Items --}}
                            @foreach(['Dashboard','Clients','Billing','Settings','Account'] as $i => $navLabel)
                            <div style="width:62px;padding:6px 8px;border-radius:6px;{{ $i === 0 ? 'background:var(--orange-light);' : '' }}display:flex;flex-direction:column;align-items:center;gap:2px">
                                <div style="width:14px;height:2px;background:{{ $i === 0 ? 'var(--orange)' : 'var(--border)' }};border-radius:1px"></div>
                                <div style="font-size:7.5px;color:{{ $i === 0 ? 'var(--orange)' : 'var(--text-3)' }};font-weight:{{ $i === 0 ? '600' : '400' }};text-align:center;line-height:1.2">{{ $navLabel }}</div>
                            </div>
                            @endforeach
                        </div>
                        {{-- Mini Content --}}
                        <div style="flex:1;background:var(--bg);padding:10px 9px">
                            <div style="height:8px;background:var(--border);border-radius:4px;width:70%;margin-bottom:8px"></div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:8px">
                                <div style="height:32px;background:#fff;border-radius:6px;border:1px solid var(--border)"></div>
                                <div style="height:32px;background:#fff;border-radius:6px;border:1px solid var(--border)"></div>
                            </div>
                            <div style="height:60px;background:#fff;border-radius:6px;border:1px solid var(--border);margin-bottom:6px"></div>
                            <div style="height:6px;background:var(--border);border-radius:3px;width:85%;margin-bottom:4px"></div>
                            <div style="height:6px;background:var(--border);border-radius:3px;width:60%"></div>
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
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.getElementById('preview-logo-wrap');
            wrap.innerHTML = '<img src="' + e.target.result + '" style="max-width:38px;max-height:32px;object-fit:contain;display:block">';
            // Also update the upload zone
            const zone = document.querySelector('.upload-zone');
            if (zone) {
                zone.innerHTML = '<img src="' + e.target.result + '" style="max-height:48px;max-width:180px;object-fit:contain;display:block;margin:0 auto 10px"><p style="font-size:12px;color:var(--text-3)">Click to replace</p>';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
