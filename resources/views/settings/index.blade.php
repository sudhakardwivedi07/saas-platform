@extends('layouts.app')
@section('page-title', 'Settings')

@section('content')

@php
$_stabs = [
    'tab-payment'        => auth()->user()->can('view_settings_payment'),
    'tab-email'          => auth()->user()->can('view_settings_email'),
    'tab-sms'            => auth()->user()->can('view_settings_sms'),
    'tab-quickbooks'     => auth()->user()->can('view_settings_quickbooks'),
    'tab-plans'          => auth()->user()->can('view_settings_plans'),
    'tab-insight-limits' => auth()->user()->can('view_settings_insight_limits'),
    'tab-roles'          => auth()->user()->can('view_settings_roles'),
];
$_atabs = [
    'tab-profile'         => auth()->user()->can('view_account_profile'),
    'tab-branding'        => auth()->user()->can('view_account_branding'),
    'tab-login-page'      => auth()->user()->can('view_account_login_page'),
    'tab-forgot-password' => auth()->user()->can('view_account_forgot_password'),
    'tab-subscription'    => auth()->user()->can('view_account_subscription'),
    'tab-activity'        => auth()->user()->can('view_account_activity'),
];
// First visible panel in sidebar order
$_sidebarOrder = ['tab-profile','tab-subscription','tab-branding','tab-login-page','tab-forgot-password','tab-quickbooks','tab-email','tab-sms','tab-payment','tab-insight-limits','tab-plans','tab-roles','tab-activity'];
$_cfirst = 'tab-profile';
foreach ($_sidebarOrder as $_k) {
    if (!empty($_atabs[$_k] ?? $_stabs[$_k] ?? false)) { $_cfirst = $_k; break; }
}
$_sfirst = $_cfirst;
$_afirst = $_cfirst;
// Breadcrumb map for server-side initial render
$_bcMap = [
    'tab-profile'         => ['Profile & Account', 'Profile'],
    'tab-subscription'    => ['Profile & Account', 'Subscription & Plan'],
    'tab-branding'        => ['Brand & Portal',    'Branding'],
    'tab-login-page'      => ['Brand & Portal',    'Login Page'],
    'tab-forgot-password' => ['Brand & Portal',    'Forgot Password'],
    'tab-quickbooks'      => ['Integrations',      'QuickBooks'],
    'tab-email'           => ['Integrations',      'Email'],
    'tab-sms'             => ['Integrations',      'SMS'],
    'tab-payment'         => ['Insights & Defaults','Payment'],
    'tab-insight-limits'  => ['Insights & Defaults','Insight Limits'],
    'tab-plans'           => ['Insights & Defaults','Plan Management'],
    'tab-roles'           => ['Team & Governance', 'Roles & Permissions'],
    'tab-activity'        => ['Team & Governance', 'User Log'],
];
$_bcGroup = $_bcMap[$_cfirst][0] ?? '';
$_bcPage  = $_bcMap[$_cfirst][1] ?? '';
@endphp

{{-- Full-width page header (above sidebar + content columns) --}}
<div class="stg-page-header">
    <h1>Settings</h1>
    <p>Manage your profile, brand, integrations, and team configuration</p>
</div>

{{-- Settings Layout: Sidebar + Content --}}
<div class="stg-layout">

{{-- Left Sidebar --}}
<nav class="stg-sidebar" id="stgSidebar">
    <div class="stg-sidebar-head">
        <div class="stg-nav-section-label">Settings</div>
        <button class="stg-collapse-btn" onclick="toggleStgSidebar()" title="Collapse sidebar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
    </div>
    <div class="stg-search-row">
        <div class="stg-search-wrap">
            <svg class="stg-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="stg-search" id="stgSearch" placeholder="Search settings…" oninput="stgFilterSidebar(this.value)">
        </div>
    </div>
 
    {{-- GROUP: PROFILE & ACCOUNT --}}
    @if(!empty($_atabs['tab-profile']) || !empty($_atabs['tab-subscription']))
    <div class="stg-group">
        <div class="stg-group-label">Profile &amp; Account</div>
        @if(!empty($_atabs['tab-profile']))
        <button class="stg-item {{ $_cfirst === 'tab-profile' ? 'active' : '' }}" data-tab="tab-profile">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Profile</span>
        </button>
        @endif
        @if(!empty($_atabs['tab-subscription']))
        <button class="stg-item {{ $_cfirst === 'tab-subscription' ? 'active' : '' }}" data-tab="tab-subscription">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <span>Subscription &amp; Plan</span>
        </button>
        @endif
    </div>
    @endif

    {{-- GROUP: BRAND & PORTAL --}}
    @if(!empty($_atabs['tab-branding']) || !empty($_atabs['tab-login-page']) || !empty($_atabs['tab-forgot-password']))
    <div class="stg-group">
        <div class="stg-group-label">Brand &amp; Portal</div>
        @if(!empty($_atabs['tab-branding']))
        <button class="stg-item {{ $_cfirst === 'tab-branding' ? 'active' : '' }}" data-tab="tab-branding">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
            <span>Branding</span>
        </button>
        @endif
        @if(!empty($_atabs['tab-login-page']))
        <button class="stg-item {{ $_cfirst === 'tab-login-page' ? 'active' : '' }}" data-tab="tab-login-page">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>
            <span>Login Page</span>
        </button>
        @endif
        @if(!empty($_atabs['tab-forgot-password']))
        <button class="stg-item {{ $_cfirst === 'tab-forgot-password' ? 'active' : '' }}" data-tab="tab-forgot-password">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <span>Forgot Password</span>
        </button>
        @endif
    </div>
    @endif

    {{-- GROUP: INTEGRATIONS --}}
    @if(!empty($_stabs['tab-quickbooks']) || !empty($_stabs['tab-email']) || !empty($_stabs['tab-sms']))
    <div class="stg-group">
        <div class="stg-group-label">Integrations</div>
        @if(!empty($_stabs['tab-quickbooks']))
        <button class="stg-item {{ $_cfirst === 'tab-quickbooks' ? 'active' : '' }}" data-tab="tab-quickbooks">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            <span>QuickBooks</span>
        </button>
        @endif
        @if(!empty($_stabs['tab-email']))
        <button class="stg-item {{ $_cfirst === 'tab-email' ? 'active' : '' }}" data-tab="tab-email">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <span>Email</span>
        </button>
        @endif
        @if(!empty($_stabs['tab-sms']))
        <button class="stg-item {{ $_cfirst === 'tab-sms' ? 'active' : '' }}" data-tab="tab-sms">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            <span>SMS</span>
        </button>
        @endif
    </div>
    @endif

    {{-- GROUP: INSIGHTS & DEFAULTS --}}
    @if(!empty($_stabs['tab-insight-limits']) || !empty($_stabs['tab-plans']) || !empty($_stabs['tab-payment']))
    <div class="stg-group">
        <div class="stg-group-label">Insights &amp; Defaults</div>
        @if(!empty($_stabs['tab-insight-limits']))
        <button class="stg-item {{ $_cfirst === 'tab-insight-limits' ? 'active' : '' }}" data-tab="tab-insight-limits">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <span>Insight Limits</span>
        </button>
        @endif
        @if(!empty($_stabs['tab-plans']))
        <button class="stg-item {{ $_cfirst === 'tab-plans' ? 'active' : '' }}" data-tab="tab-plans">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <span>Plan Management</span>
        </button>
        @endif
        @if(!empty($_stabs['tab-payment']))
        <button class="stg-item {{ $_cfirst === 'tab-payment' ? 'active' : '' }}" data-tab="tab-payment">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Payment</span>
        </button>
        @endif
    </div>
    @endif

    {{-- GROUP: TEAM & GOVERNANCE --}}
    @if(!empty($_stabs['tab-roles']) || !empty($_atabs['tab-activity']))
    <div class="stg-group">
        <div class="stg-group-label">Team &amp; Governance</div>
        @if(!empty($_stabs['tab-roles']))
        <button class="stg-item {{ $_cfirst === 'tab-roles' ? 'active' : '' }}" data-tab="tab-roles">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            <span>Roles &amp; Permissions</span>
        </button>
        @endif
        @if(!empty($_atabs['tab-activity']))
        <button class="stg-item {{ $_cfirst === 'tab-activity' ? 'active' : '' }}" data-tab="tab-activity">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <span>User Log</span>
        </button>
        @endif
    </div>
    @endif
</nav>

{{-- Main Content --}}
<div class="stg-content">

{{-- Breadcrumb inside content area --}}
<div class="stg-content-head">
    <nav class="stg-breadcrumb" id="stgBreadcrumb" aria-label="breadcrumb">
        <span class="stg-bc-root">Settings</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span class="stg-bc-group" id="stgBcGroup">{{ $_bcGroup }}</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span class="stg-bc-page" id="stgBcPage">{{ $_bcPage }}</span>
    </nav>
</div>

{{-- Tab 1: Payment --}}
@if($_stabs['tab-payment'])
<div class="tab-panel {{ $_sfirst === 'tab-payment' ? 'active' : '' }}" id="tab-payment">
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Stripe Payment Gateway
        </div>

        <div class="info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="info-banner-txt">Configure your Stripe keys to enable payments for your sub-companies.</span>
        </div>

        <form method="POST" action="{{ route('settings.payment-gateway.update') }}" id="payment-form" onsubmit="submitSettingsForm(event,this,'Payment gateway settings updated.')">
            @csrf
            <input type="hidden" name="status" value="active">
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Mode</label>
                    <select name="mode" class="f-sel">
                        <option value="live" {{ ($paymentGateway->mode ?? 'live') === 'live' ? 'selected' : '' }}>Live</option>
                        <option value="test" {{ ($paymentGateway->mode ?? '') === 'test' ? 'selected' : '' }}>Test / Sandbox</option>
                    </select>
                </div>
                <div class="f-group">
                    <label class="f-label">Gateway</label>
                    <select name="gateway" class="f-sel">
                        <option value="stripe">Stripe</option>
                    </select>
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">Live Publishable Key</label>
                <input type="text" name="live_key" class="f-inp" placeholder="pk_live_…" value="{{ $paymentGateway->live_key ?? '' }}">
            </div>
            <div class="f-group">
                <label class="f-label">Live Secret Key</label>
                <input type="password" name="live_secret" class="f-inp" placeholder="sk_live_…" value="" autocomplete="new-password">
                <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Leave blank to keep current value.</div>
            </div>
            <div class="f-group">
                <label class="f-label">Live Webhook Secret</label>
                <input type="text" name="live_webhook_secret" class="f-inp" placeholder="whsec_…" value="{{ $paymentGateway->live_webhook_secret ?? '' }}">
            </div>
            <div class="f-group">
                <label class="f-label">Webhook URL</label>
                <div style="display:flex;gap:8px;align-items:center"> <input type="text" class="f-inp" value="{{ url('webhooks/stripe') }}" readonly style="background:var(--bg);color:var(--text-2);flex:1">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="navigator.clipboard.writeText('{{ url('webhooks/stripe') }}').then(()=>showToast('Copied!','success'))">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                        Copy
                    </button>
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Paste this URL in your Stripe dashboard under Webhooks.</div>
            </div>
            <div style="margin-top:20px">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endif {{-- /tab-payment --}}

{{-- Tab 2: Email --}}
@if($_stabs['tab-email'])
<div class="tab-panel {{ $_sfirst === 'tab-email' ? 'active' : '' }}" id="tab-email">

    {{-- Email sub-tab bar --}}
    <div style="display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:22px">
        <button class="email-stab" data-epanel="epanel-smtp"
            style="padding:9px 18px;font-size:13.5px;font-weight:600;background:none;border:none;cursor:pointer;border-bottom:2px solid var(--orange);margin-bottom:-2px;color:var(--orange);display:inline-flex;align-items:center;gap:6px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            SMTP Configuration
        </button>
        <button class="email-stab" data-epanel="epanel-templates"
            style="padding:9px 18px;font-size:13.5px;font-weight:500;background:none;border:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--text-3);display:inline-flex;align-items:center;gap:6px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Email Templates
        </button>
    </div>

    {{-- Sub-panel 1: SMTP Configuration --}}
    <div id="epanel-smtp" class="epanel">
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Email SMTP Settings
        </div>

        <form method="POST" action="{{ route('settings.email.update') }}" id="email-form" onsubmit="if(!validateSmtpForm()){return false;} submitSettingsForm(event,this,'Email settings updated.')">
            @csrf
            <input type="hidden" name="driver" value="smtp">
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">From Name <span style="color:var(--red)">*</span></label>
                    <input type="text" id="smtp-from-name" name="from_name" class="f-inp" placeholder="My Platform" value="{{ $smtpSetting->from_name ?? '' }}">
                    <div class="st-err" id="smtp-from-name-err"></div>
                </div>
                <div class="f-group">
                    <label class="f-label">From Email <span style="color:var(--red)">*</span></label>
                    <input type="email" id="smtp-from-email" name="from_email" class="f-inp" placeholder="no-reply@example.com" value="{{ $smtpSetting->from_email ?? '' }}">
                    <div class="st-err" id="smtp-from-email-err"></div>
                </div>
            </div>
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">SMTP Host <span style="color:var(--red)">*</span></label>
                    <input type="text" id="smtp-host" name="host" class="f-inp" placeholder="smtp.mailgun.org" value="{{ $smtpSetting->host ?? '' }}">
                    <div class="st-err" id="smtp-host-err"></div>
                </div>
                <div class="f-group">
                    <label class="f-label">SMTP Port <span style="color:var(--red)">*</span></label>
                    <input type="number" id="smtp-port" name="port" class="f-inp" placeholder="587" value="{{ $smtpSetting->port ?? '587' }}">
                    <div class="st-err" id="smtp-port-err"></div>
                </div>
            </div>
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Username <span style="color:var(--red)">*</span></label>
                    <input type="text" id="smtp-username" name="username" class="f-inp" placeholder="smtp_user" value="{{ $smtpSetting->username ?? '' }}">
                    <div class="st-err" id="smtp-username-err"></div>
                </div>
                <div class="f-group">
                    <label class="f-label">Password <span style="color:var(--red)">*</span></label>
                    <input type="password" id="smtp-password" name="password" class="f-inp" placeholder="••••••••" autocomplete="new-password">
                    <div class="st-err" id="smtp-password-err"></div>
                </div>
            </div>
            <div class="f-group" style="max-width:220px">
                <label class="f-label">Encryption <span style="color:var(--red)">*</span></label>
                <select name="encryption" class="f-sel">
                    <option value="tls" {{ ($smtpSetting->encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ ($smtpSetting->encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ ($smtpSetting->encryption ?? '') === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
                <button type="button" id="smtpTestTrigger" onclick="openSmtpTestModal()" disabled
                    style="background:#fff;border:1.5px solid #6c5ecf;color:#6c5ecf;font-weight:600;padding:9px 18px;border-radius:7px;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;opacity:0.45;pointer-events:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Test Email
                </button>
            </div>
        </form>
    </div>
    </div>{{-- /epanel-smtp --}}

    {{-- Sub-panel 2: Email Templates --}}
    <div id="epanel-templates" class="epanel" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text-1)">Email Templates</div>
                <div style="font-size:12.5px;color:var(--text-3);margin-top:2px">Customise the emails sent to your clients. Use placeholders like <code style="background:var(--bg);padding:1px 5px;border-radius:4px;font-size:11.5px">&#123;&#123;client_name&#125;&#125;</code> to personalise content.</div>
            </div>
            @can('add_email_templates')
            <button onclick="etOpenAdd()" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Email Template
            </button>
            @endcan
        </div>

        <div style="background:var(--bg-blue,#f0f7ff);border:1px solid var(--blue-light,#bfdbfe);border-radius:8px;padding:10px 14px;font-size:12.5px;color:var(--blue-dark,#1e40af);margin-bottom:18px;display:flex;align-items:center;gap:8px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Templates are sent using your configured SMTP settings. Each template can be customised or deleted. Disabled templates will not be used.
        </div>

        <div style="background:#fff;border:1px solid var(--border);border-radius:10px;overflow:visible">
        <table id="et-table" style="width:100%;border-collapse:collapse;table-layout:fixed">
            <colgroup>
                <col style="width:26%">
                <col style="width:22%">
                <col style="width:28%">
                <col style="width:12%">
                <col style="width:12%">
            </colgroup>
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:#fafafa">
                    <th style="padding:11px 20px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.6px;border-radius:10px 0 0 0">Template Name</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.6px">Trigger</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.6px">Subject Line</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.6px">Status</th>
                    <th style="padding:11px 16px;width:52px;border-radius:0 10px 0 0"></th>
                </tr>
            </thead>
            <tbody id="et-tbody">
                @forelse($firmEmailTemplates as $tpl)
                <tr id="et-row-{{ $tpl->id }}" style="border-bottom:1px solid var(--border);transition:background .12s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                    <td style="padding:14px 20px">
                        <div style="font-size:14px;font-weight:600;color:var(--text-1)">{{ $tpl->name }}</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;font-family:monospace">{{ $tpl->slug }}</div>
                    </td>
                    <td style="padding:14px 16px;font-size:13px;color:var(--text-2)">{{ $tpl->trigger_event ?: '—' }}</td>
                    <td style="padding:14px 16px;font-size:13px;color:var(--text-2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:0">{{ $tpl->subject }}</td>
                    <td style="padding:14px 16px">
                        @if($tpl->is_active)
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:#dcfce7;color:#166534">Active</span>
                        @else
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:#f3f4f6;color:#6b7280">Inactive</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;text-align:right">
                        <div class="et-action-wrap" style="position:relative;display:inline-block">
                            <button class="et-menu-btn" onclick="etToggleMenu(this)" style="background:none;border:none;cursor:pointer;color:var(--text-3);font-size:20px;line-height:1;padding:2px 6px;border-radius:5px" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='none'">&#8943;</button>
                            <div class="et-dropdown" style="display:none;position:absolute;right:0;top:110%;background:#fff;border:1px solid var(--border);border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.10);z-index:200;min-width:140px;overflow:hidden">
                                <button onclick="etOpenView({{ $tpl->id }})" class="et-dd-item" style="display:flex;align-items:center;gap:9px;padding:10px 15px;font-size:13px;color:var(--text-1);background:none;border:none;cursor:pointer;width:100%;text-align:left" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View
                                </button>
                                <button onclick="etOpenEdit({{ $tpl->id }})" class="et-dd-item" style="display:flex;align-items:center;gap:9px;padding:10px 15px;font-size:13px;color:var(--text-1);background:none;border:none;cursor:pointer;width:100%;text-align:left" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                <div style="height:1px;background:var(--border);margin:3px 0"></div>
                                <button onclick="etDeleteConfirm({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')" class="et-dd-item" style="display:flex;align-items:center;gap:9px;padding:10px 15px;font-size:13px;color:#ef4444;background:none;border:none;cursor:pointer;width:100%" onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background=''">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="et-empty-row">
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--text-3);font-size:13px">
                        No email templates yet. Click <strong>+ Add Email Template</strong> to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>{{-- /epanel-templates --}}

</div>

{{-- SMTP Test Email Modal --}}
<div id="smtpTestModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px 32px;width:100%;max-width:420px;box-shadow:0 8px 32px rgba(0,0,0,.18);position:relative;">
        <button onclick="closeSmtpTestModal()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#888;line-height:1;">&times;</button>
        <div style="font-size:17px;font-weight:700;color:#1a1a2e;margin-bottom:6px;">Send Test Email</div>
        <div style="font-size:13px;color:#666;margin-bottom:18px;">Enter an email address to receive a test message using your saved SMTP settings.</div>
        <label style="font-size:13px;font-weight:600;color:#444;display:block;margin-bottom:6px;">Recipient Email</label>
        <input type="email" id="smtpTestEmail" class="f-inp" placeholder="you@example.com" value="{{ auth()->user()->email }}" style="width:100%;margin-bottom:16px;">
        <div id="smtpTestResult" style="display:none;padding:10px 14px;border-radius:7px;font-size:13px;margin-bottom:14px;"></div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" onclick="closeSmtpTestModal()" style="padding:8px 18px;border-radius:7px;border:1.5px solid #ddd;background:#fff;color:#555;font-size:14px;cursor:pointer;font-weight:600;">Cancel</button>
            <button type="button" id="smtpTestBtn" onclick="sendSmtpTest()" style="padding:8px 20px;border-radius:7px;background:#6c5ecf;color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;">
                <svg id="smtpTestSpinner" style="display:none;animation:spin .8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" width="15" height="15"><circle cx="12" cy="12" r="10" stroke-opacity=".3"/><path d="M12 2a10 10 0 010 20"/></svg>
                Send
            </button>
        </div>
    </div>
</div>

<script>
function openSmtpTestModal() {
    document.getElementById('smtpTestModal').style.display = 'flex';
    document.getElementById('smtpTestResult').style.display = 'none';
    document.getElementById('smtpTestEmail').value = '{{ auth()->user()->email }}';
}
function closeSmtpTestModal() {
    document.getElementById('smtpTestModal').style.display = 'none';
}
function sendSmtpTest() {
    const email = document.getElementById('smtpTestEmail').value.trim();
    const result = document.getElementById('smtpTestResult');
    const btn = document.getElementById('smtpTestBtn');
    const spinner = document.getElementById('smtpTestSpinner');

    if (!email) { showSmtpResult('error', 'Please enter a recipient email address.'); return; }

    btn.disabled = true;
    spinner.style.display = 'inline-block';
    result.style.display = 'none';

    fetch('{{ route('settings.email.test') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ to_email: email })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            showSmtpResult('success', data.message || 'Test email sent successfully!');
        } else {
            showSmtpResult('error', data.message || 'SMTP test failed.');
        }
    })
    .catch(() => showSmtpResult('error', 'Network error. Please try again.'))
    .finally(() => { btn.disabled = false; spinner.style.display = 'none'; });
}
function showSmtpResult(type, msg) {
    const el = document.getElementById('smtpTestResult');
    el.style.display = 'block';
    if (type === 'success') {
        el.style.background = '#f0fdf4'; el.style.color = '#166534'; el.style.border = '1px solid #bbf7d0';
    } else {
        el.style.background = '#fef2f2'; el.style.color = '#991b1b'; el.style.border = '1px solid #fecaca';
    }
    el.textContent = msg;
}
document.getElementById('smtpTestModal').addEventListener('click', function(e) {
    if (e.target === this) closeSmtpTestModal();
});

// Enable/disable Send Test Email trigger based on required fields
(function() {
    const triggerBtn = document.getElementById('smtpTestTrigger');
    const requiredIds = ['smtp-from-name', 'smtp-from-email', 'smtp-host', 'smtp-port', 'smtp-username'];

    function checkSmtpFields() {
        const allFilled = requiredIds.every(id => {
            const el = document.getElementById(id);
            return el && el.value.trim() !== '';
        });
        triggerBtn.disabled = !allFilled;
        triggerBtn.style.opacity = allFilled ? '1' : '0.45';
        triggerBtn.style.pointerEvents = allFilled ? 'auto' : 'none';
        triggerBtn.style.cursor = allFilled ? 'pointer' : 'default';
    }

    requiredIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', checkSmtpFields);
    });

    checkSmtpFields(); // run once on page load to handle pre-filled values
})();
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

{{-- ── Add / Edit / View Email Template Modal ──────────────────────────────── --}}
<div id="et-modal" style="display:none;position:fixed;inset:0;z-index:9990;background:rgba(0,0,0,.45);align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:1000px;max-height:90vh;overflow-y:auto;box-shadow:0 8px 40px rgba(0,0,0,.18);position:relative">
        {{-- Header --}}
        <div style="padding:22px 26px 0">
            <button onclick="etCloseModal()" style="position:absolute;top:16px;right:18px;background:none;border:none;font-size:20px;cursor:pointer;color:#aaa;line-height:1">&times;</button>
            <div id="et-modal-title" style="font-size:17px;font-weight:700;color:#1a1a2e"></div>
            <div id="et-modal-sub"   style="font-size:13px;color:#888;margin-top:3px"></div>
        </div>

        {{-- Form --}}
        <form id="et-modal-form" onsubmit="etModalSubmit(event)" style="padding:20px 26px 24px">
            <input type="hidden" id="et-modal-id">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#444;margin-bottom:6px">
                        Template Name <span style="color:var(--orange)">*</span>
                    </label>
                    <input type="text" id="et-m-name" name="name"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box"
                           placeholder="e.g. Client Invitation" required>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#444;margin-bottom:6px">
                        Trigger Condition
                    </label>
                    <input type="text" id="et-m-trigger" name="trigger_event"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box"
                           placeholder="e.g. Client completes registration">
                </div>
            </div>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:13px;font-weight:500;color:#444;margin-bottom:6px">
                    Subject Line <span style="color:var(--orange)">*</span>
                </label>
                <input type="text" id="et-m-subject" name="subject"
                       style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box"
                       placeholder="Email subject line" required>
            </div>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:13px;font-weight:500;color:#444;margin-bottom:6px">
                    Email Body <span style="color:var(--orange)">*</span>
                </label>
                <textarea id="et-m-body" name="body" rows="7"
                          style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;resize:vertical;box-sizing:border-box;font-family:inherit"
                          placeholder="Write your email body here…" required></textarea>
            </div>

            {{-- Status --}}
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:13px;font-weight:500;color:#444;margin-bottom:8px">Status</label>
                <div id="et-status-wrap" style="display:flex;gap:10px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:9px 16px;border:1.5px solid #e2e8f0;border-radius:8px;flex:1;transition:border-color .15s" id="et-status-active-wrap">
                        <input type="radio" id="et-status-active" name="is_active" value="1"
                               style="accent-color:#22c55e;width:15px;height:15px;cursor:pointer" checked
                               onchange="etStatusHighlight()">
                        <span style="font-size:13.5px;font-weight:500;color:#16a34a">Active</span>
                        <span style="margin-left:auto;width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block"></span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:9px 16px;border:1.5px solid #e2e8f0;border-radius:8px;flex:1;transition:border-color .15s" id="et-status-inactive-wrap">
                        <input type="radio" id="et-status-inactive" name="is_active" value="0"
                               style="accent-color:#9ca3af;width:15px;height:15px;cursor:pointer"
                               onchange="etStatusHighlight()">
                        <span style="font-size:13.5px;font-weight:500;color:#6b7280">Inactive</span>
                        <span style="margin-left:auto;width:8px;height:8px;border-radius:50%;background:#d1d5db;display:inline-block"></span>
                    </label>
                </div>
            </div>

            {{-- Available Variables --}}
            <div style="background:#f8f9fb;border:1px solid #e8eaf0;border-radius:9px;padding:14px;margin-bottom:14px">
                <div style="font-size:12.5px;font-weight:600;color:#555;margin-bottom:10px">Available Variables</div>
                @php
                    $etVars = ['{{client_name}}','{{partner_name}}','{{portal_name}}','{{portal_url}}','{{company_name}}'];
                @endphp
                <div style="display:flex;flex-wrap:wrap;gap:7px" id="et-var-btns">
                    @foreach($etVars as $ph)
                    <button type="button" onclick="etInsertVar('{{ $ph }}')"
                            style="padding:5px 13px;border:1.5px solid #bfdbfe;border-radius:20px;background:#eff6ff;font-size:12px;font-family:monospace;color:#2563eb;cursor:pointer;transition:background .15s"
                            onmouseover="this.style.background='#dbeafe';this.style.borderColor='#93c5fd'"
                            onmouseout="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'">{{ $ph }}</button>
                    @endforeach
                </div>
                <div style="font-size:11.5px;color:#9ca3af;margin-top:9px">Click a placeholder to insert it at the cursor position in the body.</div>
            </div>

            {{-- Warning --}}
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px;display:flex;gap:10px;align-items:flex-start;margin-bottom:20px">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span style="font-size:12.5px;color:#9a3412;line-height:1.5">Changes apply immediately for all new outbound emails. To discard changes, delete the template from the table.</span>
            </div>

            {{-- Footer --}}
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="etCloseModal()"
                        style="padding:9px 20px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;color:#555;font-size:14px;font-weight:500;cursor:pointer">
                    Cancel
                </button>
                <button type="submit" id="et-modal-submit"
                        style="padding:9px 22px;border-radius:8px;background:var(--orange,#f06b21);color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer">
                    Save Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Email sub-tab switching ────────────────────────────────────────────────────
(function() {
    var stabs = document.querySelectorAll('.email-stab');
    function activatePanel(target) {
        stabs.forEach(function(b) {
            var on = b.dataset.epanel === target;
            b.style.color             = on ? 'var(--orange)' : 'var(--text-3)';
            b.style.borderBottomColor = on ? 'var(--orange)' : 'transparent';
            b.style.fontWeight        = on ? '600' : '500';
        });
        document.querySelectorAll('.epanel').forEach(function(p) { p.style.display = 'none'; });
        var p = document.getElementById(target); if (p) p.style.display = '';
    }
    stabs.forEach(function(b) { b.addEventListener('click', function() { activatePanel(this.dataset.epanel); }); });
}());

// ── Close dropdown on outside click ──────────────────────────────────────────
document.addEventListener('click', function(e) {
    if (!e.target.closest('.et-action-wrap'))
        document.querySelectorAll('.et-dropdown').forEach(function(d) { d.style.display = 'none'; });
});
function etToggleMenu(btn) {
    var dd = btn.nextElementSibling;
    var open = dd.style.display === 'block';
    document.querySelectorAll('.et-dropdown').forEach(function(d) { d.style.display = 'none'; });
    if (!open) dd.style.display = 'block';
}

// ── Modal helpers ─────────────────────────────────────────────────────────────
var _etBodyEditor = null;
function etShowModal(title, sub, readonly) {
    document.getElementById('et-modal-title').textContent = title;
    document.getElementById('et-modal-sub').textContent   = sub;
    var fields = document.querySelectorAll('#et-modal-form input, #et-modal-form textarea');
    fields.forEach(function(f) { f.readOnly = !!readonly; f.style.background = readonly ? '#f9fafb' : '#fff'; });
    document.getElementById('et-modal-submit').style.display = readonly ? 'none' : '';
    document.getElementById('et-var-btns').style.pointerEvents = readonly ? 'none' : '';
    var sw = document.getElementById('et-status-wrap');
    sw.style.pointerEvents = readonly ? 'none' : '';
    sw.querySelectorAll('label').forEach(function(l) { l.style.cursor = readonly ? 'default' : 'pointer'; });
    document.getElementById('et-modal').style.display = 'flex';
    _etBodyEditor = document.getElementById('et-m-body');
}
function etCloseModal() {
    document.getElementById('et-modal').style.display = 'none';
    document.getElementById('et-modal-form').reset();
    document.getElementById('et-modal-id').value = '';
}
document.getElementById('et-modal').addEventListener('click', function(e) {
    if (e.target === this) etCloseModal();
});

function etInsertVar(ph) {
    var el = _etBodyEditor;
    if (!el || el.readOnly) return;
    var s = el.selectionStart, e = el.selectionEnd;
    el.value = el.value.substring(0,s) + ph + el.value.substring(e);
    el.selectionStart = el.selectionEnd = s + ph.length;
    el.focus();
}

// ── Add ───────────────────────────────────────────────────────────────────────
// ── Status radio highlight ────────────────────────────────────────────────────
function etStatusHighlight() {
    var isActive = document.getElementById('et-status-active').checked;
    document.getElementById('et-status-active-wrap').style.borderColor   = isActive ? '#22c55e' : '#e2e8f0';
    document.getElementById('et-status-inactive-wrap').style.borderColor = !isActive ? '#9ca3af' : '#e2e8f0';
}
function etSetStatus(val) {
    document.getElementById('et-status-active').checked   = !!val;
    document.getElementById('et-status-inactive').checked = !val;
    etStatusHighlight();
}

function etOpenAdd() {
    document.getElementById('et-modal-id').value = '';
    document.getElementById('et-m-name').value    = '';
    document.getElementById('et-m-trigger').value = '';
    document.getElementById('et-m-subject').value = '';
    document.getElementById('et-m-body').value    = '';
    etSetStatus(true);
    etShowModal('Add Email Template', 'Create a new custom email template', false);
    setTimeout(function() { document.getElementById('et-m-name').focus(); }, 80);
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function etOpenEdit(id) {
    document.querySelectorAll('.et-dropdown').forEach(function(d) { d.style.display='none'; });
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    fetch('{{ url("settings/firm-email-templates") }}/' + id, { headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf} })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.status !== 'success') { showToast(res.message || 'Could not load template.', 'error'); return; }
            var t = res.template;
            document.getElementById('et-modal-id').value  = t.id;
            document.getElementById('et-m-name').value    = t.name;
            document.getElementById('et-m-trigger').value = t.trigger_event;
            document.getElementById('et-m-subject').value = t.subject;
            document.getElementById('et-m-body').value    = t.body;
            etSetStatus(t.is_active);
            etShowModal('Edit Email Template', 'Update "' + t.name + '"', false);
        })
        .catch(function() { showToast('Request failed.', 'error'); });
}

// ── View (read-only) ──────────────────────────────────────────────────────────
function etOpenView(id) {
    document.querySelectorAll('.et-dropdown').forEach(function(d) { d.style.display='none'; });
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    fetch('{{ url("settings/firm-email-templates") }}/' + id, { headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf} })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.status !== 'success') { showToast(res.message || 'Could not load template.', 'error'); return; }
            var t = res.template;
            document.getElementById('et-modal-id').value  = t.id;
            document.getElementById('et-m-name').value    = t.name;
            document.getElementById('et-m-trigger').value = t.trigger_event;
            document.getElementById('et-m-subject').value = t.subject;
            document.getElementById('et-m-body').value    = t.body;
            etSetStatus(t.is_active);
            etShowModal('View Email Template', t.name, true);
        })
        .catch(function() { showToast('Request failed.', 'error'); });
}

// ── Save (add or edit) ────────────────────────────────────────────────────────
async function etModalSubmit(e) {
    e.preventDefault();
    var id   = document.getElementById('et-modal-id').value;
    var btn  = document.getElementById('et-modal-submit');
    var orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = 'Saving…';
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var fd = new FormData(document.getElementById('et-modal-form'));
    var url = id
        ? '{{ url("settings/firm-email-templates") }}/' + id
        : '{{ url("settings/firm-email-templates") }}';
    try {
        var res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        }).then(function(r) { return r.json(); });
        if (res.status === 'success') {
            etCloseModal();
            if (id) { etUpdateRow(res.template); }
            else    { etAppendRow(res.template); }
            showToast(res.message || 'Saved.', 'success');
        } else {
            showToast(res.message || 'Error saving template.', 'error');
        }
    } catch(err) { showToast('Request failed.', 'error'); }
    btn.disabled = false; btn.innerHTML = orig;
}

// ── Delete ────────────────────────────────────────────────────────────────────
function etDeleteConfirm(id, name) {
    document.querySelectorAll('.et-dropdown').forEach(function(d) { d.style.display='none'; });
    Swal.fire({
        title: 'Delete template?',
        text: 'Delete "' + name + '"? This cannot be undone.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', reverseButtons: true,
    }).then(function(result) {
        if (!result.isConfirmed) return;
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        fetch('{{ url("settings/firm-email-templates") }}/' + id, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.status === 'success') {
                var row = document.getElementById('et-row-' + id);
                if (row) row.remove();
                var tbody = document.getElementById('et-tbody');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    var tr = document.createElement('tr'); tr.id = 'et-empty-row';
                    tr.innerHTML = '<td colspan="5" style="text-align:center;padding:40px;color:var(--text-3);font-size:13px">No email templates yet. Click <strong>+ Add Email Template</strong> to create one.</td>';
                    tbody.appendChild(tr);
                }
                showToast(res.message || 'Template deleted.', 'success');
            } else { showToast(res.message || 'Could not delete.', 'error'); }
        }).catch(function() { showToast('Request failed.', 'error'); });
    });
}

// ── DOM row helpers ───────────────────────────────────────────────────────────
function etBadge(active) {
    return active
        ? '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:#dcfce7;color:#166534">Active</span>'
        : '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:#f3f4f6;color:#6b7280">Inactive</span>';
}
function etMenu(t) {
    return '<div class="et-action-wrap" style="position:relative;display:inline-block">'
        + '<button class="et-menu-btn" onclick="etToggleMenu(this)" style="background:none;border:none;cursor:pointer;color:var(--text-3);font-size:20px;line-height:1;padding:2px 6px;border-radius:5px">&#8943;</button>'
        + '<div class="et-dropdown" style="display:none;position:absolute;right:0;top:110%;background:#fff;border:1px solid var(--border);border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.10);z-index:200;min-width:140px;overflow:hidden">'
        + '<button onclick="etOpenView(' + t.id + ')" class="et-dd-item" style="display:flex;align-items:center;gap:9px;padding:10px 15px;font-size:13px;color:var(--text-1);background:none;border:none;cursor:pointer;width:100%">'
        + '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View</button>'
        + '<button onclick="etOpenEdit(' + t.id + ')" class="et-dd-item" style="display:flex;align-items:center;gap:9px;padding:10px 15px;font-size:13px;color:var(--text-1);background:none;border:none;cursor:pointer;width:100%">'
        + '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>'
        + '<div style="height:1px;background:var(--border);margin:3px 0"></div>'
        + '<button onclick="etDeleteConfirm(' + t.id + ',\'' + t.name.replace(/'/g,"\\'") + '\')" class="et-dd-item" style="display:flex;align-items:center;gap:9px;padding:10px 15px;font-size:13px;color:#ef4444;background:none;border:none;cursor:pointer;width:100%">'
        + '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>'
        + '</div></div>';
}
function etEsc(s) {
    return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : '';
}
function etAppendRow(t) {
    var empty = document.getElementById('et-empty-row'); if (empty) empty.remove();
    var tr = document.createElement('tr');
    tr.id = 'et-row-' + t.id;
    tr.style.borderBottom = '1px solid var(--border)';
    tr.setAttribute('onmouseover',"this.style.background='var(--bg)'");
    tr.setAttribute('onmouseout', "this.style.background=''");
    tr.innerHTML = '<td style="padding:14px 20px"><div style="font-size:14px;font-weight:600;color:var(--text-1)">' + etEsc(t.name) + '</div>'
        + '<div style="font-size:11.5px;color:var(--text-3);margin-top:2px;font-family:monospace">' + etEsc(t.slug) + '</div></td>'
        + '<td style="padding:14px 16px;font-size:13px;color:var(--text-2)">' + (etEsc(t.trigger_event) || '—') + '</td>'
        + '<td style="padding:14px 16px;font-size:13px;color:var(--text-2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:0">' + etEsc(t.subject) + '</td>'
        + '<td style="padding:14px 16px">' + etBadge(t.is_active) + '</td>'
        + '<td style="padding:14px 16px;text-align:right">' + etMenu(t) + '</td>';
    document.getElementById('et-tbody').appendChild(tr);
}
function etUpdateRow(t) {
    var row = document.getElementById('et-row-' + t.id); if (!row) return;
    row.cells[0].innerHTML = '<div style="font-size:14px;font-weight:600;color:var(--text-1)">' + etEsc(t.name) + '</div>'
        + '<div style="font-size:11.5px;color:var(--text-3);margin-top:2px;font-family:monospace">' + etEsc(t.slug) + '</div>';
    row.cells[1].textContent = t.trigger_event || '—';
    row.cells[2].textContent = t.subject;
    row.cells[3].innerHTML   = etBadge(t.is_active);
    row.cells[4].innerHTML   = etMenu(t);
}
</script>

@endif {{-- /tab-email --}}

{{-- Tab 3: SMS --}}
@if($_stabs['tab-sms'])
<div class="tab-panel {{ $_sfirst === 'tab-sms' ? 'active' : '' }}" id="tab-sms">
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            SMS Settings (Twilio)
        </div>

        <div class="info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="info-banner-txt">SMS notifications are powered by Twilio. Enter your credentials from the Twilio Console.</span>
        </div>

        <form method="POST" action="{{ route('settings.sms.update') }}" id="sms-form" onsubmit="if(!validateSmsForm()){return false;} submitSettingsForm(event,this,'SMS settings updated.')">
            @csrf
            <input type="hidden" name="provider" value="twilio">
            <div class="f-group">
                <label class="f-label">Account SID <span style="color:var(--red)">*</span></label>
                <input type="text" id="sms-sid" name="account_sid" class="f-inp" placeholder="ACxxxxxxxxxxxxxxxx" value="{{ $smsSetting->account_sid ?? '' }}">
                <div class="st-err" id="sms-sid-err"></div>
            </div>
            <div class="f-group">
                <label class="f-label">Auth Token <span style="color:var(--red)">*</span></label>
                <input type="password" id="sms-token" name="auth_token" class="f-inp" placeholder="••••••••••••••••••••" autocomplete="new-password" value="{{ $smsSetting->auth_token ?? '' }}">
                <div class="st-err" id="sms-token-err"></div>
            </div>
            <div class="f-group">
                <label class="f-label">From Number <span style="color:var(--red)">*</span></label>
                <input type="text" id="sms-from" name="from_number" class="f-inp" placeholder="+15551234567" value="{{ $smsSetting->from_number ?? '' }}">
                <div class="st-err" id="sms-from-err"></div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Must be a verified Twilio number in E.164 format.</div>
            </div>
            <div style="margin-top:20px">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endif {{-- /tab-sms --}}

{{-- Tab 4: QuickBooks --}}
@if($_stabs['tab-quickbooks'])
<div class="tab-panel {{ $_sfirst === 'tab-quickbooks' ? 'active' : '' }}" id="tab-quickbooks">
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            QuickBooks Integration Settings <span class="help-tip" data-tip="Connect your QuickBooks account.">i</span>
        </div>

        <form id="qb-settings-form" onsubmit="submitQbSettings(event)">
            @csrf
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">QuickBooks Client ID <span style="color:var(--red)">*</span></label>
                    <input type="text" name="client_id" class="f-inp" placeholder="Enter Client ID"
                        value="{{ $qbSetting->client_id ?? '' }}" required autocomplete="off">
                </div>
                <div class="f-group">
                    <label class="f-label">QuickBooks Environment <span style="color:var(--red)">*</span></label>
                    <select name="base_url" class="f-sel" required>
                        <option value="development" {{ ($qbSetting->base_url ?? 'development') === 'development' ? 'selected' : '' }}>Development</option>
                        <option value="production"  {{ ($qbSetting->base_url ?? '') === 'production'  ? 'selected' : '' }}>Production</option>
                    </select>
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">QuickBooks Client Secret <span style="color:var(--red)">*</span></label>
                <input type="password" name="client_secret" class="f-inp" placeholder="••••••••••••••••"
                    autocomplete="new-password">
                @if($qbSetting)
                <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Leave blank to keep current value.</div>
                @endif
            </div>
            <div class="f-group">
                <label class="f-label">QuickBooks Redirect URI <span style="color:var(--red)">*</span></label>
                <input type="text" name="redirect_uri" class="f-inp" placeholder="your domain "
                    value="{{ $qbSetting->redirect_uri ?? url('account/quickbooks-integration/callback') }}" required autocomplete="off">
                <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Must match the redirect URI registered in your QuickBooks app.</div>
            </div>
            <div style="margin-top:20px">
                <button type="submit" class="btn btn-primary" id="qb-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endif {{-- /tab-quickbooks --}}

{{-- Tab 5: Plan Management --}}
@if($_stabs['tab-plans'])
<div class="tab-panel {{ $_sfirst === 'tab-plans' ? 'active' : '' }}" id="tab-plans">
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="plan-search" placeholder="Search plans…" autocomplete="off">
                </div>
                <select class="sel" id="plan-status-filter" autocomplete="off">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <div class="dr-wrap" id="plan-dr">
                    <div class="dr-field" onclick="drOpen('plan-dr')">
                        <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="dr-text" id="plan-dr-text">Start Date – End Date</span>
                        <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('plan-dr')" title="Clear date filter">&#x2715;</button>
                    </div>
                    <div class="dr-menu" id="plan-dr-menu">
                        <ul class="dr-list">
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','today',this)">Today</li>
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','last30',this)">Last 30 Days</li>
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','thisMonth',this)">This Month</li>
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','lastMonth',this)">Last Month</li>
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','last90',this)">Last 90 Days</li>
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','last6m',this)">Last 6 Months</li>
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','last1y',this)">Last 1 Year</li>
                            <li class="dr-opt" onclick="drPickPreset('plan-dr','custom',this)">Custom Range</li>
                        </ul>
                        <div class="dr-custom-row" id="plan-dr-custom">
                            <input type="date" class="dr-date-inp" id="plan-dr-from">
                            <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                            <input type="date" class="dr-date-inp" id="plan-dr-to">
                            <button class="dr-apply-btn" onclick="drApplyCustom('plan-dr')">Apply</button>
                        </div>
                    </div>
                </div>
                <button class="btn btn-danger" onclick="resetPlanFilters()">Reset</button>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge b-gray" id="plan-count-badge">0 plans</span>
                <a href="{{ route('plans.create') }}" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Plan
                </a>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table id="plans-table" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Monthly Price</th>
                        <th>Annual Price</th>
                        <th>Insights / Month</th>
                        <th>Max Companies</th>
                        <th>Status</th>
                        <th style="width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $clientPackages = $clientPackages ?? collect(); @endphp
                    @forelse($clientPackages as $plan)
                    @php
                        $planStatus = ($plan->is_active ?? true) ? 'active' : 'inactive';
                    @endphp
                    <tr>
                        <td data-date="{{ $plan->created_at?->format('Y-m-d') ?? '' }}">
                            <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $plan->name }}</div>
                            @if($plan->description)
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:1px">{{ Str::limit($plan->description, 60) }}</div>
                            @endif
                            @if($plan->is_recommended ?? false)
                            <span class="badge b-orange" style="font-size:10px;margin-top:3px">Most Popular</span>
                            @endif
                        </td>
                        <td style="font-weight:600;color:var(--text-1)">
                            ${{ number_format($plan->monthly_price ?? 0, 2) }}<span style="font-size:11.5px;font-weight:400;color:var(--text-3)">/mo</span>
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2)">
                            @if($plan->annual_price)
                            ${{ number_format($plan->annual_price, 2) }}<span style="font-size:11px;color:var(--text-3)">/yr</span>
                            @else
                            <span style="color:var(--text-3)">—</span>
                            @endif
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2)">
                            {{ $plan->insights_per_month ?? '—' }}
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2)">
                            {{ $plan->max_companies ? $plan->max_companies . ' companies' : '∞' }}
                        </td>
                        <td data-search="{{ $planStatus }}">
                            @if($planStatus === 'active')
                                <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                            @else
                                <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:5px">
                                <a href="{{ route('plans.edit', $plan) }}" class="btn btn-ghost btn-xs">Edit</a>
                                <form method="POST" action="{{ route('plans.destroy', $plan) }}" class="js-plan-delete-form" data-plan-name="{{ $plan->name }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs" style="background:rgba(239,68,68,.07);color:var(--red);border:1px solid rgba(239,68,68,.2)">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:56px 20px">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <div style="font-size:14px;font-weight:500;color:var(--text-2)">No plans created yet</div>
                            <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Create your first plan to offer it to your sub-companies.</div>
                            <a href="{{ route('plans.create') }}" class="btn btn-primary btn-sm" style="margin-top:16px;display:inline-flex">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Create First Plan
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endif {{-- /tab-plans --}}

{{-- Tab 6: Insight Limits --}}
@if($_stabs['tab-insight-limits'])
<div class="tab-panel {{ $_sfirst === 'tab-insight-limits' ? 'active' : '' }}" id="tab-insight-limits">

    @php
        $ilAdminBudget    = (int) ($adminInsightBudget ?? 0);
        $ilTotalAllocated = (int) ($totalAllocated ?? 0);
        $ilBudgetLeft     = $ilAdminBudget > 0 ? max(0, $ilAdminBudget - $ilTotalAllocated) : null;
    @endphp

    {{-- Client Insight Limits Table --}}
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="il-search" placeholder="Search clients…" autocomplete="off">
                </div>
                <select class="sel" id="il-status-filter" autocomplete="off">
                    <option value="">All Statuses</option>
                    <option value="Not Set">Not Set</option>
                    <option value="Unused">Unused</option>
                    <option value="Available">Available</option>
                    <option value="Almost Full">Almost Full</option>
                    <option value="Full">Full</option>
                </select>
                <button class="btn btn-danger" onclick="resetIlFilters()">Reset</button>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
                @if($ilAdminBudget > 0)
                <div style="display:flex;align-items:center;gap:12px;border:1px solid var(--border);border-radius:8px;padding:6px 14px;background:var(--bg-card)">
                    <span style="font-size:12px;color:var(--text-3)">Total: <strong style="color:var(--text-1)">{{ number_format($ilAdminBudget) }}</strong></span>
                    <span style="width:1px;height:14px;background:var(--border)"></span>
                    <span style="font-size:12px;color:var(--text-3)">Allocated: <strong style="color:var(--text-1)">{{ number_format($ilTotalAllocated) }}</strong></span>
                    <span style="width:1px;height:14px;background:var(--border)"></span>
                    <span style="font-size:12px;color:var(--text-3)">Remaining: <strong style="color:{{ $ilBudgetLeft <= 0 ? '#dc2626' : '#16a34a' }}" id="il-budget-remaining-display">{{ number_format($ilBudgetLeft) }}</strong></span>
                </div>
                @endif
                <span class="badge b-gray" id="il-count-badge">{{ ($clientsForLimits ?? collect())->count() }} client{{ ($clientsForLimits ?? collect())->count() !== 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div style="overflow-x:auto;width:100%">
        <table id="il-table" style="width:100%;min-width:600px;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th style="text-align:center">Companies</th>
                    <th style="text-align:center">Insights Used</th>
                    <th style="text-align:center">Insights Remaining</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:right;width:120px">Action</th>
                    <th style="display:none">sort-name</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientsForLimits ?? collect() as $ilClient)
                @php
                    $_ilManaged    = $ilClient->managedCompanies ?? collect();
                    $_ilPrimary    = $ilClient->company;
                    $_ilAll        = collect($_ilPrimary ? [$_ilPrimary] : [])
                                         ->merge($_ilManaged)->filter()->unique('id')->values();
                    $ilTotalUsed   = (int) $_ilAll->sum('insights_used');
                    $ilSetCompanies = $_ilAll->filter(fn($c) => $c->custom_insight_limit !== null);
                    $ilTotalLimit  = (int) $ilSetCompanies->sum('custom_insight_limit');
                    $ilHasLimit    = $ilSetCompanies->isNotEmpty();
                    $ilRemaining   = $ilHasLimit ? max(0, $ilTotalLimit - $ilTotalUsed) : null;
                    $ilPct         = ($ilHasLimit && $ilTotalLimit > 0) ? min(100, round($ilTotalUsed / $ilTotalLimit * 100)) : null;
                    $ilStatus      = !$ilHasLimit ? 'Not Set'
                                   : ($ilPct >= 100 ? 'Full'
                                   : ($ilPct >= 70  ? 'Almost Full'
                                   : ($ilPct > 0    ? 'Available'
                                   :                  'Unused')));
                    $ilStatusClr   = !$ilHasLimit ? '#9ca3af'
                                   : ($ilPct >= 100 ? '#dc2626'
                                   : ($ilPct >= 70  ? '#d97706'
                                   : ($ilPct > 0    ? '#16a34a' : '#9ca3af')));
                    $ilCompaniesJson = $_ilAll->map(fn($c) => [
                        'id'     => $c->id,
                        'name'   => $c->company_name,
                        'limit'  => $c->custom_insight_limit,
                        'period' => $c->insight_limit_period ?? 'monthly',
                    ])->values()->toJson();
                @endphp
                <tr data-status="{{ $ilStatus }}">
                    <td>
                        <div style="font-size:13px;font-weight:600;color:var(--text-1)">{{ $ilClient->name }}</div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">{{ $ilClient->email }}</div>
                    </td>
                    <td style="text-align:center;font-size:13px;color:var(--text-2)">{{ $_ilAll->count() }}</td>
                    <td style="text-align:center;font-size:13px;color:var(--text-2)">{{ $ilTotalUsed }}</td>
                    <td style="text-align:center;font-size:13px;color:var(--text-2)" data-order="{{ $ilHasLimit ? $ilRemaining : -1 }}">
                        @if($ilHasLimit) {{ $ilRemaining }} @else <span style="color:#9ca3af">—</span> @endif
                    </td>
                    <td style="text-align:center" data-search="{{ $ilStatus }}">
                        <span style="font-size:13px;font-weight:500;color:{{ $ilStatusClr }}">{{ $ilStatus }}</span>
                    </td>
                    <td style="text-align:right">
                        <button type="button" class="btn btn-primary btn-sm"
                            onclick="openSetLimitModal('{{ addslashes($ilClient->name) }}', {{ $ilCompaniesJson }})">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            Set Limit
                        </button>
                    </td>
                    <td style="display:none">{{ strtolower($ilClient->name) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No clients yet. Add clients to set their insight limits.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- /overflow-x:auto --}}
    </div>

</div>

{{-- Set Limit Modal --}}
<div class="modal-overlay" id="modal-set-limit" onclick="if(event.target===this)closeSetLimitModal()">
    <div class="modal-box" style="width:460px;max-height:85vh;display:flex;flex-direction:column">
        <button class="modal-close" onclick="closeSetLimitModal()">&times;</button>
        <div class="modal-title" style="margin-bottom:4px">Set Insight Limit</div>
        <div style="font-size:13px;color:var(--text-3);margin-bottom:20px">Define company-wise insight limits for this client</div>

        <div style="margin-bottom:16px">
            <label style="display:block;font-size:12.5px;font-weight:600;color:var(--text-2);margin-bottom:6px">Client Name</label>
            <input type="text" id="sl-client-name" class="f-inp" readonly
                style="background:var(--bg-2,#f5f6f8);color:var(--text-2);cursor:default;width:100%">
        </div>

        {{-- Budget warning shown when admin budget is configured --}}
        @if($ilAdminBudget > 0)
        <div id="sl-budget-bar" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:8px;background:#f9fafb;border:1px solid var(--border);margin-bottom:14px;font-size:12.5px">
            <span style="color:var(--text-3)">Total: <strong style="color:var(--text-1)">{{ number_format($ilAdminBudget) }}</strong></span>
            <span id="sl-budget-info" style="color:var(--text-3)">Remaining after save: <strong id="sl-budget-after" style="color:var(--text-1)">—</strong></span>
        </div>
        @endif

        <div style="margin-bottom:10px">
            <label style="display:block;font-size:12.5px;font-weight:600;color:var(--text-2);margin-bottom:8px">Company-wise Limits</label>
            <div id="sl-companies-list" style="overflow-y:auto;max-height:280px;border:1px solid var(--border);border-radius:8px;overflow:hidden"></div>
        </div>

        <div id="sl-budget-warning" style="display:none;padding:8px 12px;border-radius:8px;background:#fef2f2;border:1px solid #fecaca;font-size:12.5px;color:#dc2626;margin-top:8px"></div>

        <div class="mf-foot" style="margin-top:16px">
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeSetLimitModal()">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm" id="sl-save-btn" onclick="saveSetLimits()">Save Limit</button>
        </div>
    </div>
</div>

<script>
(function () {
    var _slCompanies  = [];
    var _slPrevAlloc  = 0; // allocated to other clients (not this one)
    var _adminBudget  = {{ $ilAdminBudget > 0 ? $ilAdminBudget : 'null' }};
    var _totalAlloc   = {{ $ilTotalAllocated }};

    window.openSetLimitModal = function (clientName, companies) {
        _slCompanies = companies;
        document.getElementById('sl-client-name').value = clientName;

        // Sum of limits for THIS client's companies before editing
        var thisClientAlloc = companies.reduce(function (s, c) { return s + (c.limit !== null ? parseInt(c.limit, 10) || 0 : 0); }, 0);
        _slPrevAlloc = _totalAlloc - thisClientAlloc;

        var list = document.getElementById('sl-companies-list');
        list.innerHTML = '';
        companies.forEach(function (co, idx) {
            var row = document.createElement('div');
            row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:12px 14px' + (idx > 0 ? ';border-top:1px solid var(--border)' : '');
            row.innerHTML =
                '<div style="flex:1;font-size:13px;font-weight:500;color:var(--text-1);min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + escHtml(co.name) + '">' + escHtml(co.name) + '</div>' +
                '<input type="number" data-co-id="' + co.id + '" class="f-inp sl-limit-inp" value="' + (co.limit !== null ? co.limit : '') + '" min="1" max="99999" placeholder="Enter limit" style="width:90px;text-align:center;flex-shrink:0">' +
                '<select data-co-id="' + co.id + '" class="f-sel sl-period-sel" style="width:112px;flex-shrink:0">' +
                    '<option value="monthly"' + (co.period === 'monthly' ? ' selected' : '') + '>Monthly</option>' +
                    '<option value="weekly"'  + (co.period === 'weekly'  ? ' selected' : '') + '>Weekly</option>' +
                '</select>';
            list.appendChild(row);
        });

        // Live budget validation on any limit change
        list.querySelectorAll('.sl-limit-inp').forEach(function (inp) {
            inp.addEventListener('input', updateBudgetPreview);
        });
        updateBudgetPreview();

        document.getElementById('modal-set-limit').classList.add('open');
    };

    function updateBudgetPreview() {
        if (!_adminBudget) return;
        var inputs = document.querySelectorAll('#sl-companies-list .sl-limit-inp');
        var newAlloc = 0;
        inputs.forEach(function (inp) { newAlloc += parseInt(inp.value, 10) || 0; });
        var totalAfter = _slPrevAlloc + newAlloc;
        var remaining  = _adminBudget - totalAfter;
        var afterEl = document.getElementById('sl-budget-after');
        var warnEl  = document.getElementById('sl-budget-warning');
        if (afterEl) {
            afterEl.textContent = remaining >= 0 ? remaining.toLocaleString() : '—';
            afterEl.style.color = remaining < 0 ? '#dc2626' : 'var(--text-1)';
        }
        if (warnEl) {
            if (remaining < 0) {
                warnEl.style.display = 'block';
                warnEl.textContent = 'Total allocation (' + totalAfter.toLocaleString() + ') exceeds your plan budget of ' + _adminBudget.toLocaleString() + '. Please reduce the limits.';
            } else {
                warnEl.style.display = 'none';
            }
        }
    }

    window.closeSetLimitModal = function () {
        document.getElementById('modal-set-limit').classList.remove('open');
    };

    window.saveSetLimits = function () {
        var btn = document.getElementById('sl-save-btn');
        var inputs = document.querySelectorAll('#sl-companies-list .sl-limit-inp');

        // Validate: every company must have a limit set
        var missingLimit = false;
        inputs.forEach(function (inp) {
            var v = parseInt(inp.value, 10);
            if (!v || v < 1) { missingLimit = true; inp.style.borderColor = 'var(--red,#ef4444)'; }
            else inp.style.borderColor = '';
        });
        if (missingLimit) {
            Swal.fire({ icon: 'warning', title: 'Limit Required', text: 'Please enter a limit (minimum 1) for every company.' });
            return;
        }

        // Validate: total must not exceed admin budget
        if (_adminBudget) {
            var newAlloc = 0;
            inputs.forEach(function (inp) { newAlloc += parseInt(inp.value, 10) || 0; });
            if (_slPrevAlloc + newAlloc > _adminBudget) {
                Swal.fire({ icon: 'error', title: 'Budget Exceeded', text: 'Total allocation exceeds your plan budget of ' + _adminBudget.toLocaleString() + ' insights. Please reduce the limits.' });
                return;
            }
        }

        var payload = [];
        inputs.forEach(function (inp) {
            var coId = inp.dataset.coId;
            var sel  = document.querySelector('#sl-companies-list .sl-period-sel[data-co-id="' + coId + '"]');
            payload.push({ company_id: parseInt(coId, 10), limit: parseInt(inp.value, 10), period: sel ? sel.value : 'monthly' });
        });

        btn.disabled = true;
        btn.textContent = 'Saving…';

        fetch('{{ route("settings.insight-limits.bulk") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ companies: payload })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            btn.disabled = false;
            btn.textContent = 'Save Limit';
            if (d.error) {
                Swal.fire({ icon: 'error', title: 'Error', text: d.message || 'Something went wrong.' });
            } else {
                closeSetLimitModal();
                Swal.fire({ icon: 'success', title: 'Saved', text: d.message || 'Limits updated.', timer: 1800, showConfirmButton: false })
                    .then(function () { location.reload(); });
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'Save Limit';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' });
        });
    };

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

@endif {{-- /tab-insight-limits --}}

{{-- Tab 7: Roles & Permissions --}}
@if($_stabs['tab-roles'])
<div class="tab-panel {{ $_sfirst === 'tab-roles' ? 'active' : '' }}" id="tab-roles">

    {{-- Roles DataTable --}}
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="role-search" placeholder="Search roles…" autocomplete="off">
                </div>
                <select class="sel" id="role-type-filter" autocomplete="off">
                    <option value="">Select Type</option>
                    <option value="system">System</option>
                    <option value="custom">Custom</option>
                </select>
                <button class="btn btn-danger" onclick="resetRoleFilters()">Reset</button>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge b-gray" id="role-count-badge">{{ $roles->count() }} role{{ $roles->count() !== 1 ? 's' : '' }}</span>
                <button class="btn btn-primary btn-sm" onclick="openCreateRoleModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Role
                </button>
            </div>
        </div>

        <table id="roles-table" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Type</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th style="width:48px">Action</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @php $systemRoles = ['admin', 'super_admin']; @endphp
                    @foreach($roles as $role)
                    @if($role->name === 'super_admin') @continue @endif
                    @php
                        $isSystem  = in_array($role->name, $systemRoles);
                        $typeLabel = $isSystem ? 'System' : 'Custom';
                        $typeClass = $isSystem ? 'b-blue' : 'b-gray';
                        $permCount = $role->permissions->count();
                    @endphp
                    <tr>
                        <td data-date="{{ $role->created_at?->format('Y-m-d') ?? '' }}">
                            <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $role->display_name }}</div>
                            @if($role->alias)
                            <div style="font-size:11px;color:var(--text-3);margin-top:1px;font-family:monospace">{{ $role->name }}</div>
                            @endif
                        </td>
                        <td data-search="{{ strtolower($typeLabel) }}">
                            <span class="badge {{ $typeClass }}" style="font-size:11.5px">{{ $typeLabel }}</span>
                        </td>
                        <td style="font-size:13px;color:var(--text-2)">{{ $role->users_count ?? 0 }}</td>
                        <td>
                            @if($permCount > 0)
                                <span class="badge b-green" style="font-size:11.5px">{{ $permCount }} {{ $permCount === 1 ? 'permission' : 'permissions' }}</span>
                            @else
                                <span style="color:var(--text-3);font-size:13px">None</span>
                            @endif
                        </td>
                        <td>
                            @if(!$isSystem)
                            <div class="dm-wrap">
                                <button class="dot-menu" data-menu="dm-role-{{ $role->id }}" title="Actions">•••</button>
                                <div class="dm-popup" id="dm-role-{{ $role->id }}">
                                    <a href="javascript:void(0)" class="dm-item"
                                       onclick="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', '{{ addslashes($role->alias ?? '') }}', {{ json_encode($role->permissions->pluck('name')) }})">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                        Edit
                                    </a>
                                    <div class="dm-divider"></div>
                                    <button class="dm-item" style="color:var(--red);width:100%;text-align:left;border:0;background:transparent;cursor:pointer"
                                        onclick="deleteRole({{ $role->id }}, '{{ addslashes($role->display_name) }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2"/><path d="M19 6l-1 14a1 1 0 01-1 1H7a1 1 0 01-1-1L5 6"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                            @else
                            <span style="font-size:11.5px;color:var(--text-3)">Protected</span>
                            @endif
                        </td>
                        <td>{{ $role->created_at?->format('Y-m-d H:i:s') ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
    </div>

    {{-- Permission Reference Card --}}
    <div class="acc-card" style="margin-top:20px">
        <div class="acc-section-title">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            All Permissions by Module
        </div>
        @php $hiddenPermModules = ['Documents','Email Templates','Billing','Reports','Companies & Plans']; @endphp
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-top:14px">
            @foreach($allPermissions as $module => $perms)
            @if(in_array($module, $hiddenPermModules)) @continue @endif
            <div style="background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:12px 14px">
                <div style="font-size:11.5px;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">{{ $module }}</div>
                @foreach($perms as $perm)
                <div style="font-size:12px;color:var(--text-2);padding:3px 0;display:flex;align-items:center;gap:6px">
                    <span style="width:5px;height:5px;border-radius:50%;background:var(--blue);flex-shrink:0;display:inline-block"></span>
                    {{ $perm->name }}
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif {{-- /tab-roles --}}

{{-- Create Role Modal --}}
<div class="modal-overlay" id="create-role-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:560px">
        <button class="modal-close" onclick="document.getElementById('create-role-modal').classList.remove('open')">&times;</button>
        <div class="modal-title">Create New Role</div>
        <div class="modal-sub">Define a role name and assign permissions.</div>
        <form id="create-role-form" onsubmit="submitCreateRole(event)" novalidate data-no-loader>
            @csrf
            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Role Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="cr-name" class="mf-inp" placeholder="e.g. accountant" autocomplete="off">
                    <div id="err-cr-name" style="font-size:11.5px;color:var(--red);margin-top:2px;display:none"></div>
                    <div id="hint-cr-name" style="font-size:11.5px;color:var(--text-3);margin-top:4px">Use lowercase (e.g. manager)</div>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Alias <span style="color:var(--red)">*</span> <span style="font-size:11px;color:var(--text-3)">(display name)</span></label>
                    <input type="text" name="alias" id="cr-alias" class="mf-inp" placeholder="e.g. Account Manager" autocomplete="off">
                    <div id="err-cr-alias" style="font-size:11.5px;color:var(--red);margin-top:2px;display:none"></div>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Shown to users instead of the role name</div>
                </div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Permissions <span style="color:var(--red)">*</span></label>
                <div id="err-cr-perms" style="font-size:11.5px;color:var(--red);margin-top:2px;display:none">Please select at least one permission.</div>
                @php $crHiddenModules = ['Documents','Email Templates','Billing','Reports','Companies & Plans','Other']; @endphp
                <div style="display:grid;gap:10px;margin-top:6px;max-height:340px;overflow-y:auto;padding-right:4px">
                    @foreach($allPermissions as $module => $perms)
                    @if(in_array($module, $crHiddenModules)) @continue @endif
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:11px 13px">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                            <input type="checkbox" class="mod-toggle" data-module="cr-mod-{{ Str::slug($module) }}"
                                id="cr-modall-{{ Str::slug($module) }}"
                                onchange="toggleModule(this)" style="accent-color:var(--orange);cursor:pointer;width:14px;height:14px">
                            <label for="cr-modall-{{ Str::slug($module) }}" style="font-size:11.5px;font-weight:700;color:var(--text-1);cursor:pointer;text-transform:uppercase;letter-spacing:.5px">{{ $module }}</label>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 8px" class="cr-mod-{{ Str::slug($module) }} cr-perms-grid">
                            @foreach($perms as $perm)
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:var(--text-2)">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                    class="perm-cb perm-cr-mod-{{ Str::slug($module) }}"
                                    style="accent-color:var(--orange);cursor:pointer;width:13px;height:13px"
                                    onchange="syncModuleToggle('cr-mod-{{ Str::slug($module) }}','cr-modall-{{ Str::slug($module) }}')">
                                {{ $perm->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('create-role-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm" id="cr-submit" disabled style="opacity:.5;cursor:not-allowed">Create Role</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Role Modal --}}
<div class="modal-overlay" id="edit-role-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:560px">
        <button class="modal-close" onclick="document.getElementById('edit-role-modal').classList.remove('open')">&times;</button>
        <div class="modal-title">Edit Role</div>
        <div class="modal-sub">Update role name and permissions.</div>
        <form id="edit-role-form" onsubmit="submitEditRole(event)" novalidate data-no-loader>
            @csrf
            <input type="hidden" id="er-role-id">
            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Role Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="er-name" class="mf-inp" autocomplete="off">
                    <div id="err-er-name" style="font-size:11.5px;color:var(--red);margin-top:2px;display:none"></div>
                    <div id="hint-er-name" style="font-size:11.5px;color:var(--text-3);margin-top:4px">Use lowercase (e.g. manager)</div>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Alias <span style="color:var(--red)">*</span> <span style="font-size:11px;color:var(--text-3)">(display name)</span></label>
                    <input type="text" name="alias" id="er-alias" class="mf-inp" placeholder="e.g. Account Manager" autocomplete="off">
                    <div id="err-er-alias" style="font-size:11.5px;color:var(--red);margin-top:2px;display:none"></div>
                </div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Permissions <span style="color:var(--red)">*</span></label>
                <div id="err-er-perms" style="font-size:11.5px;color:var(--red);margin-top:2px;display:none">Please select at least one permission.</div>
                @php $erHiddenModules = ['Documents','Email Templates','Billing','Reports','Companies & Plans','Other']; @endphp
                <div style="display:grid;gap:10px;margin-top:6px;max-height:340px;overflow-y:auto;padding-right:4px">
                    @foreach($allPermissions as $module => $perms)
                    @if(in_array($module, $erHiddenModules)) @continue @endif
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:11px 13px">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                            <input type="checkbox" class="mod-toggle" data-module="er-mod-{{ Str::slug($module) }}"
                                id="er-modall-{{ Str::slug($module) }}"
                                onchange="toggleModule(this)" style="accent-color:var(--orange);cursor:pointer;width:14px;height:14px">
                            <label for="er-modall-{{ Str::slug($module) }}" style="font-size:11.5px;font-weight:700;color:var(--text-1);cursor:pointer;text-transform:uppercase;letter-spacing:.5px">{{ $module }}</label>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 8px" class="er-mod-{{ Str::slug($module) }} er-perms-grid">
                            @foreach($perms as $perm)
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:var(--text-2)">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                    class="perm-cb perm-er-mod-{{ Str::slug($module) }}"
                                    style="accent-color:var(--orange);cursor:pointer;width:13px;height:13px"
                                    onchange="syncModuleToggle('er-mod-{{ Str::slug($module) }}','er-modall-{{ Str::slug($module) }}')">
                                {{ $perm->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('edit-role-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm" id="er-submit" disabled style="opacity:.5;cursor:not-allowed">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ ACCOUNT PANELS ══════════════════════════════════════════════ --}}

{{-- Tab: Profile --}}
@if(!empty($_atabs['tab-profile']))
<div class="tab-panel {{ $_afirst === 'tab-profile' ? 'active' : '' }}" id="tab-profile">
    <div class="acc-card">
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
                        <input type="email" class="f-inp" value="{{ $authUser->email }}" readonly style="background:var(--bg);cursor:not-allowed;color:var(--text-2)">
                        <div style="font-size:11px;color:var(--text-3);margin-top:4px">Email address cannot be changed.</div>
                    </div>
                </div>
                <button type="button" id="profile-submit-btn" class="btn btn-primary btn-sm" onclick="validateAndSubmitProfile()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Profile
                </button>
            </form>
        </div>
        <div style="height:1px;background:var(--border);margin:0 -22px 22px"></div>
        <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:16px">Change Password</div>
            <form method="POST" action="{{ route('account.password') }}" id="password-form" novalidate>
                @csrf
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Current Password <span style="color:var(--red)">*</span></label>
                        <input type="password" name="current_password" id="ac-cur-pwd" class="f-inp" placeholder="Enter your current password" autocomplete="current-password">
                        <div id="err-cur-pwd" class="ac-err" style="display:none"></div>
                    </div>
                    <div></div>
                </div>
                <div class="form-2col">
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
                <button type="button" id="password-submit-btn" class="btn btn-primary btn-sm" onclick="validateAndSubmitPassword()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Save Password
                </button>
            </form>
        </div>
    </div>
</div>
@endif {{-- /tab-profile --}}

{{-- Tab: Branding --}}
@if(!empty($_atabs['tab-branding']))
<div class="tab-panel {{ $_afirst === 'tab-branding' ? 'active' : '' }}" id="tab-branding">
    <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data" id="branding-form">
        @csrf
        <div class="brand-layout">
            <div>
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                    <div>
                        <div style="font-size:16px;font-weight:700;color:var(--text-1)">Branding Settings</div>
                        <div style="font-size:13px;color:var(--text-3);margin-top:2px">Customize your client portal's look and feel</div>
                    </div>
                </div>
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
                            <div style="font-size:12px;color:var(--text-3);margin-bottom:8px;line-height:1.5">Appears in the browser tab. Recommended: 32×32px ICO or PNG.</div>
                            <label for="favicon-upload" class="btn btn-ghost btn-xs" style="cursor:pointer">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Favicon
                            </label>
                        </div>
                    </div>
                </div>
                <div class="brand-sec">
                    <div class="brand-sec-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="6.5" cy="11.5" r="2.5"/><circle cx="17.5" cy="15.5" r="2.5"/><circle cx="10" cy="19" r="2"/></svg>
                        Brand Colors
                    </div>
                    <div style="display:flex;flex-direction:column;gap:14px">
                        <div>
                            <div style="font-size:12.5px;font-weight:600;color:var(--text-1);margin-bottom:7px">Primary Color</div>
                            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;background:#fff">
                                <div id="primary-swatch" style="width:38px;height:38px;border-radius:8px;background:{{ $branding->primary_color ?? '#1a6aa8' }};flex-shrink:0;border:2px solid rgba(0,0,0,.08)"></div>
                                <input type="text" id="primary-hex-inp" value="{{ $branding->primary_color ?? '#1a6aa8' }}" maxlength="7" placeholder="#1a6aa8" style="flex:1;border:none;outline:none;font-size:13.5px;font-family:monospace;color:var(--text-1);background:transparent;font-weight:500" oninput="syncHexInput(this,'primary')">
                                <label for="primary-color-inp" style="cursor:pointer;flex-shrink:0">
                                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $branding->primary_color ?? '#1a6aa8' }};border:2px solid rgba(0,0,0,.08);cursor:pointer" id="primary-picker-btn"></div>
                                    <input type="color" id="primary-color-inp" name="primary_color" value="{{ $branding->primary_color ?? '#1a6aa8' }}" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none" oninput="syncColorPicker(this,'primary')">
                                </label>
                            </div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Used for sidebar, navigation, headings, and UI elements</div>
                        </div>
                        <div>
                            <div style="font-size:12.5px;font-weight:600;color:var(--text-1);margin-bottom:7px">Accent / CTA Color</div>
                            <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;background:#fff">
                                <div id="accent-swatch" style="width:38px;height:38px;border-radius:8px;background:{{ $branding->accent_color ?? '#f06b21' }};flex-shrink:0;border:2px solid rgba(0,0,0,.08)"></div>
                                <input type="text" id="accent-hex-inp" value="{{ $branding->accent_color ?? '#f06b21' }}" maxlength="7" placeholder="#f06b21" style="flex:1;border:none;outline:none;font-size:13.5px;font-family:monospace;color:var(--text-1);background:transparent;font-weight:500" oninput="syncHexInput(this,'accent')">
                                <label for="accent-color-inp" style="cursor:pointer;flex-shrink:0">
                                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $branding->accent_color ?? '#f06b21' }};border:2px solid rgba(0,0,0,.08);cursor:pointer" id="accent-picker-btn"></div>
                                    <input type="color" id="accent-color-inp" name="accent_color" value="{{ $branding->accent_color ?? '#f06b21' }}" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none" oninput="syncColorPicker(this,'accent')">
                                </label>
                            </div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Used for buttons, CTAs, active states, and highlights</div>
                        </div>
                        <div style="padding-top:4px;border-top:1px solid var(--border)">
                            <div style="font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:10px">Quick Presets</div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                @php
                                $presets = [['#1a6aa8','#f06b21'],['#b45309','#f59e0b'],['#7c3aed','#ec4899'],['#1d4ed8','#f06b21'],['#065f46','#f59e0b'],['#0a2540','#2563eb']];
                                @endphp
                                @foreach($presets as $preset)
                                <button type="button" onclick="applyPreset('{{ $preset[0] }}','{{ $preset[1] }}')" title="{{ $preset[0] }} / {{ $preset[1] }}" style="width:38px;height:38px;border-radius:10px;border:2px solid rgba(0,0,0,.1);cursor:pointer;overflow:hidden;padding:0;background:transparent;flex-shrink:0;transition:transform .12s,box-shadow .12s" onmouseenter="this.style.transform='scale(1.12)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.2)'" onmouseleave="this.style.transform='scale(1)';this.style.boxShadow='none'">
                                    <svg viewBox="0 0 38 38" width="38" height="38"><path d="M0 0 L38 0 L0 38 Z" fill="{{ $preset[1] }}"/><path d="M38 0 L38 38 L0 38 Z" fill="{{ $preset[0] }}"/></svg>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                        <div id="preview-sidebar" style="width:84px;background:#fff;border-right:1px solid var(--border);padding:10px 0;display:flex;flex-direction:column;align-items:center;gap:2px">
                            <div id="preview-logo-wrap" style="width:48px;height:32px;border-radius:6px;background:var(--bg);border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;margin-bottom:10px;overflow:hidden">
                                @if($branding?->logo)
                                <img id="preview-logo-img" src="{{ asset('storage/' . $branding->logo) }}" style="max-width:44px;max-height:28px;object-fit:contain;display:block">
                                @else
                                <svg id="preview-logo-img" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                @endif
                            </div>
                            <div id="preview-nav-active" style="width:68px;padding:5px 7px;border-radius:5px;border-left:2px solid var(--orange);background:var(--orange-light);display:flex;align-items:center;gap:4px;margin-bottom:1px">
                                <div style="width:8px;height:8px;background:var(--orange);border-radius:50%;flex-shrink:0"></div>
                                <div style="font-size:7.5px;color:var(--orange);font-weight:700;line-height:1.2">Dashboard</div>
                            </div>
                            @foreach(['Clients','Billing','Settings'] as $navLabel)
                            <div style="width:68px;padding:5px 7px;border-radius:5px;display:flex;align-items:center;gap:4px;margin-bottom:1px">
                                <div style="width:8px;height:8px;background:var(--border);border-radius:50%;flex-shrink:0"></div>
                                <div style="font-size:7.5px;color:var(--text-3);line-height:1.2">{{ $navLabel }}</div>
                            </div>
                            @endforeach
                        </div>
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
                <div style="font-size:12.5px;color:var(--text-3);margin-top:2px">Override the platform-wide date format for your account. Leave as default to use the Super Admin's setting (<strong>{{ $today->format($globalDateFormat) }}</strong>).</div>
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
                <input type="radio" name="adf_pick" value="{{ $fmt }}" style="accent-color:var(--blue);width:15px;height:15px;flex-shrink:0" {{ $effectiveFmt === $fmt ? 'checked' : '' }} onchange="adfPickFormat(this.value)">
                <div>
                    <div style="font-size:13.5px;font-weight:600;color:var(--text-1);font-family:monospace">{{ $today->format($fmt) }}</div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:1px"><code style="background:var(--bg);padding:1px 4px;border-radius:4px;font-size:10.5px">{{ $fmt }}</code></div>
                </div>
            </label>
            @endforeach
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div style="font-size:12.5px;color:var(--text-3)">Preview: <span id="adf-preview" style="font-weight:600;color:var(--text-1);margin-left:4px">{{ $today->format($effectiveFmt) }}</span></div>
            <div style="display:flex;gap:8px">
                <button type="button" class="btn btn-primary btn-sm" id="adf-save-btn" onclick="saveAll()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
@endif {{-- /tab-branding --}}

{{-- Tab: Login Page --}}
@if(!empty($_atabs['tab-login-page']))
<div class="tab-panel {{ $_afirst === 'tab-login-page' ? 'active' : '' }}" id="tab-login-page">
    <div class="acc-card" style="max-width:1200px">
        <div class="acc-section-title" style="margin-bottom:24px">
            <h3 style="font-size:16px;font-weight:700;color:#1a202c;margin:0 0 4px">Login Page</h3>
            <p style="font-size:13px;color:#6b7280;margin:0">Customise the login page your clients and team members see when signing in.</p>
        </div>
        <div style="display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap">
            <div style="flex:1;min-width:320px;max-width:520px">
                <form id="login-page-form" enctype="multipart/form-data" onsubmit="submitLoginPageForm(event)">
                    @csrf
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Heading</label>
                        <input type="text" name="login_heading" id="lp-heading" value="{{ $branding?->login_heading ?? 'Welcome back' }}" placeholder="Welcome back" oninput="lpUpdatePreview()" style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Subheading</label>
                        <input type="text" name="login_subheading" id="lp-subheading" value="{{ $branding?->login_subheading ?? 'Sign in to ' . ($branding?->app_name ?? config('app.name')) }}" placeholder="Sign in to your account" oninput="lpUpdatePreview()" style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:8px">Background Type</label>
                        <div style="display:flex;gap:12px;flex-wrap:wrap">
                            @foreach(['gradient' => 'Gradient', 'solid' => 'Solid Color', 'image' => 'Image'] as $val => $lbl)
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;cursor:pointer">
                                <input type="radio" name="login_bg_type" value="{{ $val }}" {{ ($branding?->login_bg_type ?? 'gradient') === $val ? 'checked' : '' }} onchange="lpUpdateBgTypeVisibility();lpUpdatePreview()">
                                {{ $lbl }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div id="lp-colors-wrap" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
                        <div id="lp-bg-color-wrap" style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px" id="lp-bg-color-label">Background Color</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_bg_color" id="lp-bg-color" value="{{ $branding?->login_bg_color ?? '#0d3d66' }}" oninput="lpUpdatePreview()" style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-bg-color-text" value="{{ $branding?->login_bg_color ?? '#0d3d66' }}" oninput="lpSyncColor('lp-bg-color','lp-bg-color-text');lpUpdatePreview()" maxlength="7" style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                        <div id="lp-bg-color-to-wrap" style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Gradient End Color</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_bg_color_to" id="lp-bg-color-to" value="{{ $branding?->login_bg_color_to ?? '#f06b21' }}" oninput="lpUpdatePreview()" style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-bg-color-to-text" value="{{ $branding?->login_bg_color_to ?? '#f06b21' }}" oninput="lpSyncColor('lp-bg-color-to','lp-bg-color-to-text');lpUpdatePreview()" maxlength="7" style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                    </div>
                    <div id="lp-bg-image-wrap" style="margin-bottom:16px;display:none">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Background Image</label>
                        <input type="file" name="login_background" id="lp-bg-image" accept="image/*" onchange="lpPreviewBgImage(this)" style="font-size:13px">
                        @if($branding?->login_background)
                            <p style="font-size:12px;color:#6b7280;margin-top:4px">Current: <a href="{{ asset('storage/'.$branding->login_background) }}" target="_blank" style="color:#1a6aa8">view image</a></p>
                        @endif
                    </div>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
                        <div style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Card Background</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_card_bg" id="lp-card-bg" value="{{ $branding?->login_card_bg ?? '#ffffff' }}" oninput="lpUpdatePreview()" style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-card-bg-text" value="{{ $branding?->login_card_bg ?? '#ffffff' }}" oninput="lpSyncColor('lp-card-bg','lp-card-bg-text');lpUpdatePreview()" maxlength="7" style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                        <div style="flex:1;min-width:120px">
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Button Color</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="color" name="login_btn_color" id="lp-btn-color" value="{{ $branding?->login_btn_color ?? ($branding?->accent_color ?? '#f06b21') }}" oninput="lpUpdatePreview()" style="width:40px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:2px;cursor:pointer">
                                <input type="text" id="lp-btn-color-text" value="{{ $branding?->login_btn_color ?? ($branding?->accent_color ?? '#f06b21') }}" oninput="lpSyncColor('lp-btn-color','lp-btn-color-text');lpUpdatePreview()" maxlength="7" style="width:80px;height:34px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 8px;font-size:13px;font-family:monospace">
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom:24px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Logo (shown on login card)</label>
                        <label id="lp-logo-drop-zone" for="lp-logo" style="display:block;border:2px dashed #bfdbfe;border-radius:10px;background:var(--blue-pale);padding:32px 20px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s" ondragover="event.preventDefault();this.style.borderColor='var(--blue)'" ondragleave="this.style.borderColor='#bfdbfe'" ondrop="lpHandleLogoDrop(event)">
                            <div id="lp-logo-drop-content">
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
                        <input type="file" name="logo" id="lp-logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml" style="display:none" onchange="lpPreviewLogoImage(this)">
                    </div>
                    <button type="submit" id="lp-submit" class="btn btn-primary">Save Login Page</button>
                </form>
            </div>
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
                                            <div id="lp-preview-logo-placeholder" style="display:inline-flex;align-items:center;justify-content:center;height:32px;min-width:120px;border:2px dashed #e2e8f0;border-radius:8px;padding:0 12px;color:#9ca3af;font-size:11px;font-weight:500">{{ $branding?->app_name ?? config('app.name') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div id="lp-preview-heading" style="font-size:15px;font-weight:700;color:#1a202c;text-align:center;margin-bottom:3px">{{ $branding?->login_heading ?? 'Welcome back' }}</div>
                                <div id="lp-preview-subheading" style="font-size:11px;color:#6b7280;text-align:center;margin-bottom:14px">{{ $branding?->login_subheading ?? 'Sign in to ' . ($branding?->app_name ?? config('app.name')) }}</div>
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

{{-- Tab: Forgot Password --}}
@if(!empty($_atabs['tab-forgot-password']))
<div class="tab-panel {{ $_afirst === 'tab-forgot-password' ? 'active' : '' }}" id="tab-forgot-password">
    <div class="acc-card" style="max-width:1200px">
        <div class="acc-section-title" style="margin-bottom:24px">
            <h3 style="font-size:16px;font-weight:700;color:#1a202c;margin:0 0 4px">Forgot Password Page</h3>
            <p style="font-size:13px;color:#6b7280;margin:0">Customise the heading and subheading shown on the "Forgot Password" page.</p>
        </div>
        <div style="display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap">
            <div style="flex:1;min-width:320px;max-width:520px">
                <form id="fp-page-form" onsubmit="submitFpPageForm(event)">
                    @csrf
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Heading</label>
                        <input type="text" name="fp_heading" id="fp-heading" value="{{ $branding?->fp_heading ?? 'Forgot your password?' }}" placeholder="Forgot your password?" oninput="fpUpdatePreview()" style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="margin-bottom:24px">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px">Subheading</label>
                        <input type="text" name="fp_subheading" id="fp-subheading" value="{{ $branding?->fp_subheading ?? 'Enter your email and we\'ll send a reset link.' }}" placeholder="Enter your email and we'll send a reset link." oninput="fpUpdatePreview()" style="width:100%;height:40px;border:1.5px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:14px;outline:none">
                    </div>
                    <div style="padding:12px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;margin-bottom:20px;display:flex;align-items:flex-start;gap:8px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="14" height="14" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span style="font-size:12px;color:#1e40af;line-height:1.5">The background style, logo, card colour, and button colour are shared with your <strong>Login Page</strong> settings.</span>
                    </div>
                    <button type="submit" id="fp-submit" class="btn btn-primary">Save Forgot Password Page</button>
                </form>
            </div>
            <div style="flex:1;min-width:280px;max-width:440px">
                <div style="position:sticky;top:80px">
                    <p style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">Live Preview</p>
                    <div style="border-radius:14px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.18);aspect-ratio:4/3;position:relative" id="fp-preview-outer">
                        <div id="fp-preview-bg"
                             data-bg-type="{{ $branding?->login_bg_type ?? 'gradient' }}"
                             data-bg-color="{{ $branding?->login_bg_color ?? '#0d3d66' }}"
                             data-bg-color-to="{{ $branding?->login_bg_color_to ?? '#f06b21' }}"
                             @if($branding?->login_bg_type === 'image' && $branding?->login_background) data-bg-img-url="{{ asset('storage/'.$branding->login_background) }}" @endif
                             style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:24px">
                            <div id="fp-preview-card" style="border-radius:14px;padding:28px 28px 22px;width:100%;max-width:320px;box-shadow:0 12px 40px rgba(0,0,0,.2);background:{{ $branding?->login_card_bg ?? '#ffffff' }}">
                                <div style="text-align:center;margin-bottom:12px">
                                    @if($branding?->logo)
                                        <img src="{{ asset('storage/'.$branding->logo) }}" alt="Logo" style="max-height:36px;max-width:140px;object-fit:contain;display:block;margin:0 auto">
                                    @else
                                        <div style="display:inline-flex;align-items:center;justify-content:center;height:32px;min-width:120px;border:2px dashed #e2e8f0;border-radius:8px;padding:0 12px;color:#9ca3af;font-size:11px;font-weight:500">{{ $branding?->app_name ?? config('app.name') }}</div>
                                    @endif
                                </div>
                                <div id="fp-preview-heading" style="font-size:14px;font-weight:700;color:#1a202c;text-align:center;margin-bottom:3px">{{ $branding?->fp_heading ?? 'Forgot your password?' }}</div>
                                <div id="fp-preview-subheading" style="font-size:10px;color:#6b7280;text-align:center;margin-bottom:12px">{{ $branding?->fp_subheading ?? 'Enter your email and we\'ll send a reset link.' }}</div>
                                <div style="height:28px;border:1.5px solid #e2e8f0;border-radius:7px;margin-bottom:10px;background:#fff"></div>
                                <div id="fp-preview-btn" style="height:32px;border-radius:8px;background:{{ $branding?->login_btn_color ?? ($branding?->accent_color ?? '#f06b21') }}"></div>
                                <div style="text-align:center;margin-top:10px"><span style="font-size:9px;color:#6b7280">← Back to login</span></div>
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

{{-- Tab: Subscription --}}
@if(!empty($_atabs['tab-subscription']))
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
    $priceLabel  = $subPkg ? ('$' . number_format($planPrice, 0) . '/' . ($subscription?->billing_cycle === 'annual' ? 'year' : 'month')) : '—';
    $billingLabel= ucfirst($subscription?->billing_cycle ?? 'monthly');
    $nextBilling = $renewsAt ? $renewsAt->format(active_date_format()) : '—';
    $planDesc    = $subPkg?->description ?? 'Your current subscription plan';
@endphp
@if($subscription && $subPkg)
<div style="background:#fff;border:1.5px solid var(--border);border-left:4px solid var(--blue);border-radius:12px;padding:22px 24px 18px;margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;padding-bottom:16px;border-bottom:1px solid var(--border);margin-bottom:18px">
        <div>
            <div style="font-size:19px;font-weight:700;color:var(--text-1);margin-bottom:3px">{{ $planName }}</div>
            <div style="font-size:12.5px;color:var(--text-3)">Your current subscription plan</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
            @if($subStatus === 'active') <span style="background:rgba(16,185,129,.12);color:#059669;border:1px solid rgba(16,185,129,.3);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Active</span>
            @elseif($subStatus === 'trialing') <span style="background:rgba(245,158,11,.12);color:#d97706;border:1px solid rgba(245,158,11,.3);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Trial</span>
            @elseif($subStatus === 'past_due') <span style="background:rgba(239,68,68,.12);color:#dc2626;border:1px solid rgba(239,68,68,.3);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Past Due</span>
            @elseif($subStatus === 'canceled') <span style="background:rgba(239,68,68,.10);color:#dc2626;border:1px solid rgba(239,68,68,.25);border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600">Canceled</span>
            @endif
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px">
        <div><div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Plan Price</div><div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $priceLabel }}</div></div>
        <div><div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Billing Cycle</div><div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $billingLabel }}</div></div>
        <div><div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Renews</div><div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $nextBilling }}</div></div>
        <div><div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:5px">Plan Features</div><div style="font-size:13.5px;color:var(--text-2)">{{ $planDesc }}</div></div>
    </div>
    @if($subStatus === 'canceled' && $subscription?->ends_at && $subscription->ends_at->isFuture())
    <button class="btn btn-sm" style="border:1.5px solid var(--green);color:var(--green);background:transparent" onclick="subReactivate()">Reactivate Subscription</button>
    @elseif(in_array($subStatus, ['active','trialing','past_due']))
    <button class="btn btn-sm" style="border:1.5px solid var(--red);color:var(--red);background:transparent" onclick="subCancel()">Cancel Subscription</button>
    @endif
</div>
@endif
@if($subscription?->pm_last4)
<div style="background:#fff;border:1.5px solid var(--border);border-left:4px solid var(--green);border-radius:12px;padding:18px 24px;margin-bottom:16px">
    <div style="font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:10px">Payment Method</div>
    <div style="display:flex;align-items:center;gap:14px">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-2)" stroke-width="1.8" width="32" height="32"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
        <div>
            <div style="font-size:15px;font-weight:600;color:var(--text-1);letter-spacing:.08em">•••• •••• •••• {{ $subscription->pm_last4 }} &nbsp;&nbsp;<span style="font-size:13px;font-weight:400;color:var(--text-3)">Expires: {{ str_pad($subscription->pm_exp_month, 2, '0', STR_PAD_LEFT) }}/{{ substr($subscription->pm_exp_year, -2) }}</span></div>
            @if($subscription->pm_brand)<div style="font-size:11.5px;color:var(--text-3);margin-top:2px;text-transform:capitalize">{{ $subscription->pm_brand }}</div>@endif
        </div>
    </div>
</div>
@endif
@if($subscription)
<div class="plan-banner" style="margin-bottom:20px">
    <div class="plan-banner-left">
        <div class="plan-label">Current Plan</div>
        <div class="plan-name">{{ $planName }}</div>
        <div class="plan-meta">
            @if($maxClients) {{ $maxClients }} clients · @endif
            @if($maxInsights) {{ number_format($maxInsights) }} insights / month · @endif
            @if($subPkg?->monthly_price) ${{ number_format($subPkg->monthly_price, 0) }} / month @endif
            @if($subStatus === 'canceled' && $subscription?->ends_at) · Access until {{ $subscription->ends_at->format(active_date_format()) }}
            @elseif($renewsAt) · Renews {{ $renewsAt->format(active_date_format()) }}
            @endif
        </div>
    </div>
    <div class="plan-banner-right">
        @if($subStatus === 'active') <span class="plan-status-badge">Active</span>
        @elseif($subStatus === 'trialing') <span class="plan-status-badge" style="background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.5)">Trial</span>
        @elseif($subStatus === 'past_due') <span class="plan-status-badge" style="background:rgba(239,68,68,.2);border-color:rgba(239,68,68,.5)">Past Due</span>
        @elseif($subStatus === 'canceled') <span class="plan-status-badge" style="background:rgba(239,68,68,.18);border-color:rgba(239,68,68,.5)">Canceled</span>
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
@if(!empty($packages) && $packages->isNotEmpty())
<div style="margin-bottom:16px">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:4px">Available Plans</div>
    @if($subPkg) <div style="font-size:13px;color:var(--text-3)">You are on the <strong>{{ $planName }}</strong> plan. You can switch to any plan below. A switch takes effect from the next billing cycle.</div>
    @else <div style="font-size:13px;color:var(--text-3)">Choose a plan to get started.</div>
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
            @if($p->max_users)<li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ $p->max_users }} clients</li>@endif
            @if($p->max_documents)<li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ number_format($p->max_documents) }} insights / month</li>@endif
            @if(!empty($p->features) && is_array($p->features))
                @foreach(array_slice($p->features, 0, 5) as $feat)<li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ Str::title(str_replace('_', ' ', $feat)) }}</li>@endforeach
            @else
                @if($p->max_storage_gb)<li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ $p->max_storage_gb }}GB storage</li>@endif
                @if($p->is_trial_allowed)<li><svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>{{ $p->trial_days }}-day free trial</li>@endif
            @endif
        </ul>
        @php $isFreePackage = ($p->is_free ?? false) || ((float)($p->monthly_price ?? 0) <= 0); @endphp
        @php $isCurrentPaid = $subscription && !(!empty($subPkg?->is_free) || ((float)($subPkg?->monthly_price ?? 0) <= 0)); @endphp
        <div style="margin-top:auto">
            @if($isCurrent)
            <button disabled class="btn btn-primary" style="width:100%;justify-content:center;cursor:not-allowed;opacity:.85">Current Plan</button>
            @elseif(!$isFreePackage || !$isCurrentPaid)
            <button class="btn {{ $subscription ? 'btn-ghost' : 'btn-primary' }}" style="width:100%;justify-content:center" onclick="subStartCheckout({{ $p->id }}, this)">
                {{ $subscription ? ($p->monthly_price > ($subPkg?->monthly_price ?? 0) ? 'Upgrade to ' : 'Switch to ') . $p->name : 'Get ' . $p->name }}
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif
@if(!empty($payments) && $payments->count())
<div style="margin-top:28px">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:14px">Payment History</div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin-bottom:14px">
        <div class="srch-wrap" style="flex:1;min-width:0;max-width:260px">
            <svg class="srch-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="phSearch" class="srch-inp" placeholder="Search payments…">
        </div>
        <select id="phStatusFilter" class="f-sel" style="width:auto;height:34px;min-width:130px;font-size:13px">
            <option value="">Select Status</option>
            <option value="complete">Complete</option>
            <option value="failed">Failed</option>
            <option value="pending">Pending</option>
            <option value="refunded">Refunded</option>
        </select>
        <div class="dr-wrap" id="ph-dr">
            <div class="dr-field" onclick="drOpen('ph-dr')">
                <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="dr-text" id="ph-dr-text">Start Date – End Date</span>
                <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('ph-dr')" title="Clear date filter">&#x2715;</button>
            </div>
            <div class="dr-menu" id="ph-dr-menu">
                <ul class="dr-list">
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','today',this)">Today</li>
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','last30',this)">Last 30 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','thisMonth',this)">This Month</li>
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','lastMonth',this)">Last Month</li>
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','last90',this)">Last 90 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','last6m',this)">Last 6 Months</li>
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','last1y',this)">Last 1 Year</li>
                    <li class="dr-opt" onclick="drPickPreset('ph-dr','custom',this)">Custom Range</li>
                </ul>
                <input type="hidden" id="ph-dr-from">
                <input type="hidden" id="ph-dr-to">
            </div>
        </div>
        <button class="btn btn-danger btn-sm" onclick="phReset()">Reset</button>
    </div>
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
                    $phStatus = strtolower($ph->status ?? 'pending');
                    $phBadge  = match($phStatus) { 'complete' => ['b-green','Complete'], 'failed' => ['b-red','Failed'], 'refunded' => ['b-orange','Refunded'], default => ['b-gray','Pending'] };
                    $phPlan   = $ph->subscription?->package?->name ?? '—';
                    $phCycle  = ucfirst($ph->subscription?->billing_cycle ?? '—');
                    $phDate   = $ph->paid_at ?? $ph->created_at;
                @endphp
                <tr style="border-bottom:1px solid var(--border)">
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-1);font-weight:500">{{ $phPlan }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-2)">{{ $phCycle }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-1);font-weight:600">${{ number_format((float)$ph->amount, 2) }} <span style="font-size:11px;color:var(--text-3);font-weight:400;text-transform:uppercase">{{ strtoupper($ph->currency ?? 'USD') }}</span></td>
                    <td style="padding:11px 16px" data-search="{{ $phStatus }}"><span class="badge {{ $phBadge[0] }}">{{ $phBadge[1] }}</span></td>
                    <td style="padding:11px 16px;font-size:13px;color:var(--text-2)" data-date="{{ $phDate?->format('Y-m-d') ?? '' }}" data-order="{{ $phDate?->format('Y-m-d') ?? '' }}">{{ $phDate ? $phDate->format(active_date_format()) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
</div>
@endif {{-- /tab-subscription --}}

{{-- Tab: User Log --}}
@if(!empty($_atabs['tab-activity']))
<div class="tab-panel {{ $_afirst === 'tab-activity' ? 'active' : '' }}" id="tab-activity">
    <div class="info-banner" style="margin-bottom:1.25rem;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--blue);"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="info-banner-txt">Activity log records all actions taken within your account. Logs are retained for 90 days.</span>
    </div>
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="tc-filters" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="activitySearch" placeholder="Search activity..." autocomplete="off">
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">Event Type</label>
                <select class="sel" id="actFilterEventType" style="min-width:190px;">
                    <option value="">Select Event Type</option>
                    @foreach(($eventTypes ?? collect()) as $eventType)
                        <option value="{{ $eventType }}">{{ ucwords(str_replace(['_', '-'], ' ', $eventType)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dr-wrap" id="act-log-dr">
                <div class="dr-field" onclick="drOpen('act-log-dr')">
                    <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span class="dr-text" id="act-log-dr-text">Start Date – End Date</span>
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
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p style="font-size:0.9375rem;font-weight:600;color:var(--text-1);margin:0 0 6px 0;">No activity yet</p>
                <p style="font-size:0.8125rem;color:var(--text-2);margin:0;max-width:340px;margin-inline:auto;">Once enabled, all actions taken within your account will appear here for auditing and compliance.</p>
            </div>
        @else
            <div class="tc">
                <table id="activity-table" style="width:100%;border-collapse:collapse">
                    <thead><tr><th>Event</th><th>User</th><th>Date &amp; Time</th></tr></thead>
                    <tbody>
                        @foreach(($activities ?? collect()) as $activity)
                        <tr data-event-type="{{ $activity->event_type ?? '' }}">
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @php
                                        $eventType = $activity->event_type ?? 'unknown';
                                        $icoClass = match(true) { str_contains($eventType, 'login') => 'activity-dot-success', str_contains($eventType, 'delete') => 'activity-dot-danger', str_contains($eventType, 'create') => 'activity-dot-info', str_contains($eventType, 'update') => 'activity-dot-warning', default => 'activity-dot-muted' };
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

</div>{{-- /stg-content --}}
</div>{{-- /stg-layout --}}

@endsection

@push('styles')
<style>
/* ── Settings sidebar layout ──────────────────────────────────────── */
.stg-layout{display:flex;align-items:flex-start;gap:0;min-height:calc(100vh - 60px)}
.stg-sidebar{width:240px;flex-shrink:0;background:#fff;border-right:1px solid var(--border);min-height:calc(100vh - 60px);padding:0 0 24px;position:sticky;top:60px;overflow-y:auto;transition:width .22s ease}
.stg-sidebar.collapsed{width:52px}
.stg-sidebar.collapsed .stg-group-label,
.stg-sidebar.collapsed .stg-item span,
.stg-sidebar.collapsed .stg-badge,
.stg-sidebar.collapsed .stg-search-wrap{display:none}
.stg-sidebar.collapsed .stg-item{justify-content:center;padding:9px 0}
.stg-sidebar-head{display:flex;align-items:center;gap:8px;padding:6px 12px 8px;border-bottom:none;margin-bottom:0}
.stg-search-wrap{position:relative;width:100%}
.stg-search-icon{position:absolute;left:8px;top:50%;transform:translateY(-50%);color:var(--text-3);pointer-events:none}
.stg-search{width:100%;height:30px;border:1.5px solid var(--border);border-radius:7px;padding:0 8px 0 26px;font-size:12.5px;color:var(--text-1);background:#fafbff;outline:none;transition:border-color .15s}
.stg-search:focus{border-color:var(--blue);background:#fff}
.stg-collapse-btn{flex-shrink:0;width:26px;height:26px;border:1.5px solid var(--border);border-radius:7px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-3);transition:background .15s,color .15s}
.stg-collapse-btn:hover{background:var(--bg);color:var(--text-1)}
.stg-nav-section-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-3);white-space:nowrap;flex:1}
.stg-sidebar.collapsed .stg-nav-section-label{display:none}
.stg-search-row{padding:0 12px 10px;border-bottom:1px solid var(--border);margin-bottom:8px}
.stg-sidebar.collapsed .stg-search-row{display:none}
.stg-group{margin-bottom:4px;padding:0 8px}
.stg-group-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-3);padding:8px 8px 4px;white-space:nowrap}
.stg-item{display:flex;align-items:center;gap:9px;width:100%;padding:8px 10px;border:none;background:transparent;border-radius:8px;cursor:pointer;font-size:13px;color:var(--text-2);text-align:left;transition:background .13s,color .13s;position:relative;white-space:nowrap}
.stg-item:hover{background:var(--bg);color:var(--text-1)}
.stg-item.active{background:var(--blue-pale);color:var(--blue);font-weight:600}
.stg-item svg{flex-shrink:0;opacity:.7}
.stg-item.active svg{opacity:1}
.stg-badge{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-left:auto}
.stg-badge-red{background:#ef4444}
.stg-page-header{padding:0 0 14px}
.stg-page-header h1{font-size:20px;font-weight:600;line-height:1.2;color:var(--text-1);margin:0 0 3px}
.stg-page-header p{font-size:13px;color:var(--text-2);margin:0}
.stg-content{flex:1;min-width:0;padding:0 0 40px}
.stg-content-head{padding:14px 28px 10px;border-bottom:1px solid var(--border)}
.stg-breadcrumb{display:flex;align-items:center;gap:5px;font-size:12.5px;color:var(--text-3)}
.stg-bc-root{color:var(--text-3)}
.stg-bc-group{color:var(--text-2)}
.stg-bc-page{color:var(--text-1);font-weight:600}
.stg-breadcrumb svg{opacity:.5;flex-shrink:0}
.stg-content .tab-panel{padding:20px 28px 0}
/* ── Account / existing styles ───────────────────────────────────── */
.st-err{display:none;color:var(--red);font-size:11.5px;margin-top:4px}
.ac-err{display:none;color:var(--red);font-size:11.5px;margin-top:4px}
.ac-invalid{border-color:var(--red)!important;outline:none}
.f-inp.st-invalid,.f-sel.st-invalid{border-color:var(--red)!important;outline:none}
.il-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;align-items:start}
/* Plan cards */
.plan-banner{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 22px;background:var(--blue-pale);border:1.5px solid var(--blue-light,#bfdbfe);border-radius:12px;flex-wrap:wrap}
.plan-banner-left{flex:1;min-width:0}
.plan-label{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--blue);margin-bottom:3px}
.plan-name{font-size:17px;font-weight:700;color:var(--text-1)}
.plan-meta{font-size:12.5px;color:var(--text-3);margin-top:2px}
.plan-banner-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.plan-status-badge{background:rgba(16,185,129,.18);color:#059669;border:1px solid rgba(16,185,129,.4);border-radius:20px;padding:3px 14px;font-size:12px;font-weight:600}
.plan-cancel-btn{border:1.5px solid var(--red);color:var(--red);background:transparent;border-radius:8px;padding:6px 14px;font-size:12.5px;font-weight:600;cursor:pointer;transition:background .13s}
.plan-cancel-btn:hover{background:rgba(239,68,68,.08)}
.plan-reactivate-btn{border:1.5px solid var(--green);color:var(--green);background:transparent;border-radius:8px;padding:6px 14px;font-size:12.5px;font-weight:600;cursor:pointer;transition:background .13s}
.plan-reactivate-btn:hover{background:rgba(16,185,129,.08)}
.plans-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.plan-card{border:1.5px solid var(--border);border-radius:12px;padding:20px 18px;background:#fff;display:flex;flex-direction:column;gap:8px;position:relative;transition:box-shadow .15s}
.plan-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.09)}
.plan-card.is-current{border-color:var(--blue);background:var(--blue-pale)}
.plan-card-badge{position:absolute;top:-1px;right:14px;background:var(--blue);color:#fff;font-size:10.5px;font-weight:700;padding:2px 10px;border-radius:0 0 8px 8px}
.plan-card-label{font-size:11px;font-weight:700;letter-spacing:.1em;color:var(--text-3)}
.plan-card-price{font-size:22px;font-weight:700;color:var(--text-1)}
.plan-card-price span{font-size:13px;font-weight:400;color:var(--text-3)}
.plan-card-sub{font-size:12px;color:var(--text-3)}
.plan-card-features{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:5px;flex:1}
.plan-card-features li{display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text-2)}
/* Activity dots */
.activity-dot-success,.activity-dot-danger,.activity-dot-info,.activity-dot-warning,.activity-dot-muted{display:inline-block;width:8px;height:8px;border-radius:50%;flex-shrink:0}
.activity-dot-success{background:#10b981}.activity-dot-danger{background:#ef4444}.activity-dot-info{background:#3b82f6}.activity-dot-warning{background:#f59e0b}.activity-dot-muted{background:#9ca3af}
/* Branding */
.brand-layout{display:grid;grid-template-columns:1fr 300px;gap:28px;align-items:start}
.brand-sec{margin-bottom:22px;padding-bottom:22px;border-bottom:1px solid var(--border)}
.brand-sec:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
.brand-sec-title{font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:12px;display:flex;align-items:center;gap:7px}
.preview-sticky{position:sticky;top:80px}
.preview-card{border:1.5px solid var(--border);border-radius:12px;overflow:hidden;background:#fff}
.preview-head{display:flex;align-items:center;gap:7px;padding:9px 14px;font-size:11px;font-weight:700;color:#fff;letter-spacing:.04em}
@media(max-width:700px){.il-grid{grid-template-columns:1fr 1fr}}
@media(max-width:480px){.il-grid{grid-template-columns:1fr}}
@media(max-width:640px){.cr-perms-grid,.er-perms-grid{grid-template-columns:1fr!important}}
@media(max-width:900px){.brand-layout{grid-template-columns:1fr}.preview-sticky{position:static}}
@media(max-width:768px){.stg-layout{flex-direction:column}.stg-sidebar{width:100%;min-height:auto;position:relative;top:0;flex-direction:row;display:flex;flex-wrap:wrap;padding:8px}.stg-group{margin-bottom:0}}
</style>
@endpush

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
// ── Breadcrumb map ─────────────────────────────────────────────────────────────
var _stgBreadcrumb = {
    'tab-profile':         ['Profile & Account',    'Profile'],
    'tab-subscription':    ['Profile & Account',    'Subscription & Plan'],
    'tab-branding':        ['Brand & Portal',       'Branding'],
    'tab-login-page':      ['Brand & Portal',       'Login Page'],
    'tab-forgot-password': ['Brand & Portal',       'Forgot Password'],
    'tab-quickbooks':      ['Integrations',         'QuickBooks'],
    'tab-email':           ['Integrations',         'Email'],
    'tab-sms':             ['Integrations',         'SMS'],
    'tab-payment':         ['Insights & Defaults',  'Payment'],
    'tab-insight-limits':  ['Insights & Defaults',  'Insight Limits'],
    'tab-plans':           ['Insights & Defaults',  'Plan Management'],
    'tab-roles':           ['Team & Governance',    'Roles & Permissions'],
    'tab-activity':        ['Team & Governance',    'User Log'],
};
function updateBreadcrumb(tabId) {
    var entry = _stgBreadcrumb[tabId];
    if (!entry) return;
    var grp  = document.getElementById('stgBcGroup');
    var page = document.getElementById('stgBcPage');
    if (grp)  grp.textContent  = entry[0];
    if (page) page.textContent = entry[1];
}

// ── Settings tab persistence + reset on switch ────────────────────────────────
(function () {
    var SK = 'settings_active_tab';

    function resetTabContent(tabId) {
        var panel = document.getElementById(tabId);
        if (!panel) return;
        // Only reset DataTable search/filters — do NOT clear form fields
        // (form values are server-rendered from DB and must stay intact on tab switch)
        panel.querySelectorAll('table[id]').forEach(function(tbl) {
            if (window.$ && $.fn.DataTable && $.fn.DataTable.isDataTable('#' + tbl.id)) {
                $('#' + tbl.id).DataTable().search('').columns().search('').draw();
            }
        });
    }

    function activateTab(tabId) {
        var btn   = document.querySelector('.stg-item[data-tab="' + tabId + '"]');
        var panel = document.getElementById(tabId);
        if (!btn || !panel) return false;
        document.querySelectorAll('.stg-item').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
        btn.classList.add('active');
        panel.classList.add('active');
        history.replaceState(null, '', location.pathname + '#' + tabId);
        window.scrollTo(0, 0);
        updateBreadcrumb(tabId);
        var ev = new CustomEvent('tabActivated', { detail: { tabId: tabId } });
        document.dispatchEvent(ev);
        return true;
    }

    // On load: honour URL hash; always set breadcrumb for the active tab
    var hash = window.location.hash.replace('#', '');
    if (hash) {
        activateTab(hash) || activateTab('tab-' + hash);
    } else {
        // Set breadcrumb for the server-rendered first tab
        var firstActive = document.querySelector('.stg-item.active');
        if (firstActive) updateBreadcrumb(firstActive.dataset.tab);
    }

    // On tab click: reset the target tab's content, then activate it
    document.querySelectorAll('.stg-item').forEach(function(btn) {
        btn.addEventListener('click', function () {
            var tabId = btn.dataset.tab;
            if (!tabId) return;
            resetTabContent(tabId);
            activateTab(tabId);
        });
    });
}());

$(document).ready(function() {
    // Plan delete: delegated so it works with DataTables; Swal must run (not native confirm)
    $(document).on('submit', '.js-plan-delete-form', function (e) {
        e.preventDefault();
        var form = this;
        var name = form.getAttribute('data-plan-name') || '';
        Swal.fire({
            title: 'Delete plan?',
            text: 'Delete plan "' + name + '"? This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            form.submit();
        });
    });

    // Only init DataTable if the plans tab has rows
    if ($('#plans-table tbody tr td').length && !$('#plans-table tbody tr td[colspan]').length) {
        document.getElementById('plan-search').value        = '';
        document.getElementById('plan-status-filter').value = '';

        var planTable = $('#plans-table').DataTable({
            paging: true, pageLength: 15, lengthChange: false,
            searching: true, ordering: true, info: true, dom: 'tip',
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [6] }],
            infoCallback: function(settings, start, end, max, total, pre) {
                return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'plans' : 'plan');
            },
            language: {
                infoEmpty: 'No plans', paginate: { previous: '‹', next: '›' },
                emptyTable: 'No plans yet',
            },
            drawCallback: function() {
                var info = this.api().page.info();
                document.getElementById('plan-count-badge').textContent = info.recordsDisplay + ' plan' + (info.recordsDisplay !== 1 ? 's' : '');
            }
        });

        document.getElementById('plan-search').addEventListener('keyup', function() { planTable.search(this.value).draw(); });
        document.getElementById('plan-status-filter').addEventListener('change', function() {
            planTable.column(5).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (!settings.nTable || settings.nTable.id !== 'plans-table') return true;
            var from = document.getElementById('plan-dr-from') ? document.getElementById('plan-dr-from').value : '';
            var to   = document.getElementById('plan-dr-to')   ? document.getElementById('plan-dr-to').value   : '';
            if (!from && !to) return true;
            var row = planTable.row(dataIndex).node();
            if (!row) return true;
            var dateVal = row.querySelector('td[data-date]')?.getAttribute('data-date') || '';
            if (!dateVal) return true;
            if (from && dateVal < from) return false;
            if (to   && dateVal > to)   return false;
            return true;
        });
        _drCbs['plan-dr'] = function() { planTable.draw(); };
    } else {
        // Update count badge for empty state
        var badge = document.getElementById('plan-count-badge');
        if (badge) badge.textContent = '0 plans';
    }

    // Insight Limits DataTable
    if ($('#il-table tbody tr td').length && !$('#il-table tbody tr td[colspan]').length) {
        document.getElementById('il-search').value        = '';
        document.getElementById('il-status-filter').value = '';

        var ilTable = $('#il-table').DataTable({
            paging: true, pageLength: 15, lengthChange: false,
            searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
            order: [[6, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5] },
                { visible: false,   targets: [6] },
            ],
            infoCallback: function(settings, start, end, max, total) {
                return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'clients' : 'client');
            },
            language: {
                infoEmpty: 'No clients found', paginate: { previous: '‹', next: '›' },
                emptyTable: '<div style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No clients found.</div>',
            },
            drawCallback: function() {
                var info = this.api().page.info();
                var badge = document.getElementById('il-count-badge');
                if (badge) badge.textContent = info.recordsDisplay + ' client' + (info.recordsDisplay !== 1 ? 's' : '');
            }
        });

        document.getElementById('il-search').addEventListener('keyup', function() { ilTable.search(this.value).draw(); });
        document.getElementById('il-status-filter').addEventListener('change', function() {
            ilTable.column(4).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
        });
    }

    // Roles DataTable
    document.getElementById('role-search').value       = '';
    document.getElementById('role-type-filter').value  = '';

    var roleTable = $('#roles-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [4] },
            { visible: false, targets: [5] },
        ],
        infoCallback: function(settings, start, end, max, total, pre) {
            return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'roles' : 'role');
        },
        language: {
            infoEmpty: 'No roles found', paginate: { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No roles found.</div>',
        },
        drawCallback: function() {
            var info = this.api().page.info();
            document.getElementById('role-count-badge').textContent = info.recordsDisplay + ' role' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    document.getElementById('role-search').addEventListener('keyup', function() { roleTable.search(this.value).draw(); });
    document.getElementById('role-type-filter').addEventListener('change', function() {
        roleTable.column(1).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

});

// ── Permission toggles ─────────────────────────────────────────────────────────
function toggleModule(checkbox) {
    var prefix = checkbox.dataset.module;
    document.querySelectorAll('.perm-' + prefix).forEach(function(cb) { cb.checked = checkbox.checked; });
}

function syncModuleToggle(modClass, toggleId) {
    var cbs = document.querySelectorAll('.perm-' + modClass);
    var allChecked = Array.from(cbs).every(function(cb) { return cb.checked; });
    var toggle = document.getElementById(toggleId);
    if (toggle) toggle.checked = allChecked;
}

// ── Create Role ────────────────────────────────────────────────────────────────
function crSetErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
}

function crValidateName(show) {
    var val = (document.getElementById('cr-name') || {}).value || '';
    var err = '';
    if (!val.trim()) {
        err = show ? 'Role name is required.' : '';
    } else {
        var v = val.trim();
        if (/\s/.test(v)) {
            err = 'Spaces are not allowed. Use underscores instead (e.g. account_manager).';
        } else if (/[A-Z]/.test(v)) {
            err = 'Uppercase letters are not allowed. Use all lowercase (e.g. manager).';
        } else if (!/^[a-z]/.test(v)) {
            err = 'Must start with a lowercase letter (e.g. manager).';
        } else if (!/^[a-z][a-z0-9_]*$/.test(v)) {
            err = 'Only lowercase letters, numbers, and underscores are allowed (e.g. account_manager).';
        }
    }
    crSetErr('err-cr-name', err);
    var hint = document.getElementById('hint-cr-name');
    if (hint) hint.style.display = err ? 'none' : '';
    return err === '' && val.trim() !== '';
}

function crValidateAlias(show) {
    var val = (document.getElementById('cr-alias') || {}).value || '';
    var err = (!val.trim() && show) ? 'Alias (display name) is required.' : '';
    crSetErr('err-cr-alias', err);
    return val.trim() !== '';
}

function crUpdateBtn() {
    var nameOk  = crValidateName(false);
    var aliasOk = crValidateAlias(false);
    var permsOk = document.querySelectorAll('#create-role-form input[name="permissions[]"]:checked').length > 0;
    var enabled = nameOk && aliasOk && permsOk;
    var btn = document.getElementById('cr-submit');
    if (!btn) return;
    btn.disabled = !enabled;
    btn.style.opacity = enabled ? '' : '.5';
    btn.style.cursor  = enabled ? '' : 'not-allowed';
}

function openCreateRoleModal() {
    var form = document.getElementById('create-role-form');
    if (form) form.reset();
    document.querySelectorAll('#create-role-modal .mod-toggle').forEach(function(cb) { cb.checked = false; });
    crSetErr('err-cr-name', '');
    crSetErr('err-cr-alias', '');
    crSetErr('err-cr-perms', '');
    var hintName = document.getElementById('hint-cr-name');
    if (hintName) hintName.style.display = '';
    crUpdateBtn();
    document.getElementById('create-role-modal').classList.add('open');
}

(function() {
    var nameEl  = document.getElementById('cr-name');
    var aliasEl = document.getElementById('cr-alias');
    var formEl  = document.getElementById('create-role-form');
    if (nameEl) {
        nameEl.addEventListener('input', crUpdateBtn);
        nameEl.addEventListener('blur',  function() { crValidateName(true); crUpdateBtn(); });
    }
    if (aliasEl) {
        aliasEl.addEventListener('input', crUpdateBtn);
        aliasEl.addEventListener('blur',  function() { crValidateAlias(true); crUpdateBtn(); });
    }
    if (formEl) {
        formEl.addEventListener('change', crUpdateBtn);
    }
}());

function submitCreateRole(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    var nameOk  = crValidateName(true);
    var aliasOk = crValidateAlias(true);
    var permsChecked = document.querySelectorAll('#create-role-form input[name="permissions[]"]:checked').length > 0;
    var permsErr = document.getElementById('err-cr-perms');
    if (permsErr) permsErr.style.display = permsChecked ? 'none' : 'block';
    if (!nameOk || !aliasOk || !permsChecked) return;

    var form = document.getElementById('create-role-form');
    var btn  = document.getElementById('cr-submit');
    var data = new FormData(form);
    btn.disabled = true;

    fetch('{{ route("settings.roles.store") }}', { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false; btn.style.opacity = ''; btn.style.cursor = '';
            if (res.status === 'success') {
                document.getElementById('create-role-modal').classList.remove('open');
                var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
                Swal.fire({ icon: 'success', text: res.message || 'Role created.', confirmButtonText: 'Ok', confirmButtonColor: btnColor })
                    .then(function() { location.href = location.pathname + '#tab-roles'; location.reload(); });
            } else {
                showToast(res.message || 'Something went wrong.', 'error');
            }
        })
        .catch(function() { btn.disabled = false; btn.style.opacity = ''; btn.style.cursor = ''; showToast('Request failed.', 'error'); });
}

// ── Edit Role ──────────────────────────────────────────────────────────────────
function erSetErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
}
function erValidateName(show) {
    var val = (document.getElementById('er-name') || {}).value || '';
    var err = '';
    if (!val.trim()) {
        err = show ? 'Role name is required.' : '';
    } else {
        var v = val.trim();
        if (/\s/.test(v)) {
            err = 'Spaces are not allowed. Use underscores instead (e.g. account_manager).';
        } else if (/[A-Z]/.test(v)) {
            err = 'Uppercase letters are not allowed. Use all lowercase (e.g. manager).';
        } else if (!/^[a-z]/.test(v)) {
            err = 'Must start with a lowercase letter (e.g. manager).';
        } else if (!/^[a-z][a-z0-9_]*$/.test(v)) {
            err = 'Only lowercase letters, numbers, and underscores are allowed (e.g. account_manager).';
        }
    }
    erSetErr('err-er-name', err);
    var hint = document.getElementById('hint-er-name');
    if (hint) hint.style.display = err ? 'none' : '';
    return err === '' && val.trim() !== '';
}
function erValidateAlias(show) {
    var val = (document.getElementById('er-alias') || {}).value || '';
    var err = (!val.trim() && show) ? 'Alias (display name) is required.' : '';
    erSetErr('err-er-alias', err);
    return val.trim() !== '';
}
function erUpdateBtn() {
    var nameOk  = erValidateName(false);
    var aliasOk = erValidateAlias(false);
    var permsOk = document.querySelectorAll('#edit-role-form input[name="permissions[]"]:checked').length > 0;
    var enabled = nameOk && aliasOk && permsOk;
    var btn = document.getElementById('er-submit');
    if (!btn) return;
    btn.disabled = !enabled;
    btn.style.opacity = enabled ? '' : '.5';
    btn.style.cursor  = enabled ? '' : 'not-allowed';
}
(function() {
    var nameEl  = document.getElementById('er-name');
    var aliasEl = document.getElementById('er-alias');
    var formEl  = document.getElementById('edit-role-form');
    if (nameEl) {
        nameEl.addEventListener('input', erUpdateBtn);
        nameEl.addEventListener('blur',  function() { erValidateName(true); erUpdateBtn(); });
    }
    if (aliasEl) {
        aliasEl.addEventListener('input', erUpdateBtn);
        aliasEl.addEventListener('blur',  function() { erValidateAlias(true); erUpdateBtn(); });
    }
    if (formEl) formEl.addEventListener('change', erUpdateBtn);
}());

function openEditRoleModal(id, name, alias, permissions) {
    document.getElementById('er-role-id').value = id;
    document.getElementById('er-name').value     = name;
    document.getElementById('er-alias').value    = alias || '';

    // Reset errors
    erSetErr('err-er-name', '');
    erSetErr('err-er-alias', '');
    erSetErr('err-er-perms', '');
    var hintName = document.getElementById('hint-er-name');
    if (hintName) hintName.style.display = '';

    // Reset all checkboxes
    document.querySelectorAll('#edit-role-modal input[type=checkbox]').forEach(function(cb) { cb.checked = false; });

    // Check existing permissions
    permissions.forEach(function(perm) {
        var cb = document.querySelector('#edit-role-modal input[value="' + perm + '"]');
        if (cb) cb.checked = true;
    });

    // Sync module toggles
    document.querySelectorAll('#edit-role-modal .mod-toggle').forEach(function(toggle) {
        var prefix = toggle.dataset.module;
        var cbs = document.querySelectorAll('#edit-role-modal .perm-' + prefix);
        toggle.checked = cbs.length > 0 && Array.from(cbs).every(function(c) { return c.checked; });
    });

    erUpdateBtn();
    document.getElementById('edit-role-modal').classList.add('open');
}

function submitEditRole(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    var nameOk  = erValidateName(true);
    var aliasOk = erValidateAlias(true);
    var permsChecked = document.querySelectorAll('#edit-role-form input[name="permissions[]"]:checked').length > 0;
    var permsErr = document.getElementById('err-er-perms');
    if (permsErr) permsErr.style.display = permsChecked ? 'none' : 'block';
    if (!nameOk || !aliasOk || !permsChecked) return;

    var roleId = document.getElementById('er-role-id').value;
    var form   = document.getElementById('edit-role-form');
    var btn    = document.getElementById('er-submit');
    var data   = new FormData(form);
    btn.disabled = true;

    fetch('{{ route("settings.roles.update", ":id") }}'.replace(':id', roleId), { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            if (res.status === 'success') {
                document.getElementById('edit-role-modal').classList.remove('open');
                var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
                Swal.fire({ icon: 'success', text: res.message || 'Role updated.', confirmButtonText: 'Ok', confirmButtonColor: btnColor })
                    .then(function() { location.reload(); });
            } else {
                showToast(res.message || 'Something went wrong.', 'error');
            }
        })
        .catch(function() { btn.disabled = false; showToast('Request failed.', 'error'); });
}

// ── Delete Role ────────────────────────────────────────────────────────────────
function deleteRole(id, name) {
    Swal.fire({
        title: 'Delete role?',
        text: 'Delete role "' + name + '"? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
        fetch('{{ route("settings.roles.destroy", ":id") }}'.replace(':id', id), {
            method: 'DELETE',
            headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type':'application/json'},
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.status === 'success') {
                var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
                Swal.fire({ icon: 'success', text: res.message || 'Role deleted.', confirmButtonText: 'Ok', confirmButtonColor: btnColor })
                    .then(function() { location.reload(); });
            } else {
                showToast(res.message || 'Cannot delete role.', 'error');
            }
        })
        .catch(function() { showToast('Request failed.', 'error'); });
    });
}

// ── QuickBooks Settings ────────────────────────────────────────────────────────
function submitQbSettings(e) {
    e.preventDefault();
    var form = document.getElementById('qb-settings-form');
    var btn  = document.getElementById('qb-submit');
    var data = new FormData(form);
    btn.disabled = true;

    fetch('{{ route("settings.quickbooks-settings.update") }}', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: data
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        if (res.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: res.message || 'QuickBooks settings saved.', confirmButtonText: 'Ok', confirmButtonColor: btnColor });
        } else {
            showToast(res.message || 'Something went wrong.', 'error');
        }
    })
    .catch(function() { btn.disabled = false; showToast('Request failed.', 'error'); });
}

// ── Settings Form Inline Validation ───────────────────────────────────────────
function _stErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
}
function _stMark(id, invalid) {
    var el = document.getElementById(id);
    if (!el) return;
    if (invalid) el.classList.add('st-invalid');
    else el.classList.remove('st-invalid');
}
function validateSmtpForm() {
    var ok = true;
    var fields = [
        { id: 'smtp-from-name',  errId: 'smtp-from-name-err',  label: 'From Name' },
        { id: 'smtp-from-email', errId: 'smtp-from-email-err', label: 'From Email', email: true },
        { id: 'smtp-host',       errId: 'smtp-host-err',       label: 'SMTP Host' },
        { id: 'smtp-port',       errId: 'smtp-port-err',       label: 'SMTP Port', numeric: true },
        { id: 'smtp-username',   errId: 'smtp-username-err',   label: 'Username' },
        { id: 'smtp-password',   errId: 'smtp-password-err',   label: 'Password' },
    ];
    fields.forEach(function(f) {
        var el  = document.getElementById(f.id);
        var val = el ? el.value.trim() : '';
        if (!val) {
            _stErr(f.errId, f.label + ' is required.');
            _stMark(f.id, true);
            ok = false;
        } else if (f.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            _stErr(f.errId, 'Enter a valid email address.');
            _stMark(f.id, true);
            ok = false;
        } else if (f.numeric && (isNaN(Number(val)) || parseInt(val) <= 0)) {
            _stErr(f.errId, 'Enter a valid port number.');
            _stMark(f.id, true);
            ok = false;
        } else {
            _stErr(f.errId, '');
            _stMark(f.id, false);
        }
    });
    return ok;
}
function validateSmsForm() {
    var ok = true;
    var fields = [
        { id: 'sms-sid',   errId: 'sms-sid-err',   label: 'Account SID' },
        { id: 'sms-token', errId: 'sms-token-err',  label: 'Auth Token' },
        { id: 'sms-from',  errId: 'sms-from-err',   label: 'From Number', e164: true },
    ];
    fields.forEach(function(f) {
        var el  = document.getElementById(f.id);
        var val = el ? el.value.trim() : '';
        if (!val) {
            _stErr(f.errId, f.label + ' is required.');
            _stMark(f.id, true);
            ok = false;
        } else if (f.e164 && !/^\+[1-9]\d{7,14}$/.test(val)) {
            _stErr(f.errId, 'Enter a valid E.164 number (e.g. +15551234567).');
            _stMark(f.id, true);
            ok = false;
        } else {
            _stErr(f.errId, '');
            _stMark(f.id, false);
        }
    });
    return ok;
}
(function() {
    var emailForm = document.getElementById('email-form');
    var smsForm   = document.getElementById('sms-form');
    if (emailForm) emailForm.addEventListener('input', function(e) {
        _stErr(e.target.id + '-err', '');
        _stMark(e.target.id, false);
    });
    if (smsForm) smsForm.addEventListener('input', function(e) {
        _stErr(e.target.id + '-err', '');
        _stMark(e.target.id, false);
    });
})();

// ── Generic Settings Form AJAX Submit ─────────────────────────────────────────
function submitSettingsForm(e, form, defaultMsg) {
    e.preventDefault();
    var btn      = form.querySelector('[type=submit]');
    var origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0" stroke-dasharray="40" stroke-dashoffset="10"/></svg> Saving…';

    var action = form.action.split('#')[0];
    fetch(action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
    })
    .then(function(r) {
        if (r.status === 422) return r.json().then(function(d) { throw { validation: true, data: d }; });
        return r.json();
    })
    .then(function(res) {
        btn.disabled = false; btn.innerHTML = origHtml;
        if (res.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: res.message || defaultMsg || 'Settings saved.', confirmButtonText: 'Ok', confirmButtonColor: btnColor });
        } else {
            showToast(res.message || 'Something went wrong.', 'error');
        }
    })
    .catch(function(err) {
        btn.disabled = false; btn.innerHTML = origHtml;
        if (err && err.validation && err.data && err.data.errors) {
            var firstError = Object.values(err.data.errors)[0];
            showToast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
        } else {
            showToast('Request failed. Please try again.', 'error');
        }
    });
}


function resetIlFilters() {
    document.getElementById('il-search').value        = '';
    document.getElementById('il-status-filter').value = '';
    if (window.$ && $('#il-table').length) {
        $('#il-table').DataTable().search('').columns().search('').draw();
    }
}

function resetPlanFilters() {
    document.getElementById('plan-search').value        = '';
    document.getElementById('plan-status-filter').value = '';
    drClear('plan-dr');
    if (window.$ && $('#plans-table').length) {
        $('#plans-table').DataTable().search('').columns().search('').draw();
    }
}

function resetRoleFilters() {
    document.getElementById('role-search').value        = '';
    document.getElementById('role-type-filter').value   = '';
    if (window.$ && $('#roles-table').length) {
        $('#roles-table').DataTable().search('').columns().search('').draw();
    }
}

// ── Sidebar: collapse + search ─────────────────────────────────────
function toggleStgSidebar() {
    var sb  = document.getElementById('stgSidebar');
    var btn = sb ? sb.querySelector('.stg-collapse-btn svg') : null;
    if (!sb) return;
    sb.classList.toggle('collapsed');
    if (btn) {
        btn.innerHTML = sb.classList.contains('collapsed')
            ? '<polyline points="9 18 15 12 9 6"/>'
            : '<polyline points="15 18 9 12 15 6"/>';
    }
}

function stgFilterSidebar(q) {
    var items  = document.querySelectorAll('.stg-item');
    var groups = document.querySelectorAll('.stg-group');
    var needle = q.trim().toLowerCase();
    items.forEach(function(item) {
        var label = (item.querySelector('span') || item).textContent.trim().toLowerCase();
        item.style.display = (!needle || label.includes(needle)) ? '' : 'none';
    });
    groups.forEach(function(grp) {
        var visible = Array.from(grp.querySelectorAll('.stg-item')).some(function(i){ return i.style.display !== 'none'; });
        grp.style.display = visible ? '' : 'none';
    });
}

// ── Account: branding ─────────────────────────────────────────────
function updatePreview(primary, accent) {
    var head = document.getElementById('preview-head');
    var btn  = document.getElementById('preview-btn');
    var nav  = document.getElementById('preview-nav-active');
    if (head) head.style.background = primary;
    if (btn)  btn.style.background  = accent;
    if (nav) {
        nav.style.borderLeftColor = accent;
        nav.style.background      = accent + '22';
        var dot = nav.querySelector('div:first-child');
        var lbl = nav.querySelector('div:last-child');
        if (dot) dot.style.background = accent;
        if (lbl) lbl.style.color = accent;
    }
}
function syncHexInput(el, type) {
    var val = el.value.trim();
    if (!/^#[0-9a-fA-F]{6}$/.test(val)) return;
    document.getElementById(type + '-swatch').style.background     = val;
    document.getElementById(type + '-picker-btn').style.background = val;
    document.getElementById(type + '-color-inp').value             = val;
    var p = document.getElementById('primary-hex-inp').value.trim();
    var a = document.getElementById('accent-hex-inp').value.trim();
    if (/^#[0-9a-fA-F]{6}$/.test(p) && /^#[0-9a-fA-F]{6}$/.test(a)) updatePreview(p, a);
}
function syncColorPicker(el, type) {
    var val = el.value;
    document.getElementById(type + '-swatch').style.background     = val;
    document.getElementById(type + '-picker-btn').style.background = val;
    document.getElementById(type + '-hex-inp').value               = val;
    var p = document.getElementById('primary-color-inp').value;
    var a = document.getElementById('accent-color-inp').value;
    updatePreview(p, a);
}
function applyPreset(primary, accent) {
    ['primary','accent'].forEach(function(type) {
        var val = type === 'primary' ? primary : accent;
        document.getElementById(type + '-color-inp').value             = val;
        document.getElementById(type + '-hex-inp').value               = val;
        document.getElementById(type + '-swatch').style.background     = val;
        document.getElementById(type + '-picker-btn').style.background = val;
    });
    updatePreview(primary, accent);
}
function previewLogo(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        if (file.size > 2 * 1024 * 1024) { input.value = ''; return; }
        var reader = new FileReader();
        reader.onload = function(e) {
            var wrap = document.getElementById('preview-logo-wrap');
            if (wrap) wrap.innerHTML = '<img src="' + e.target.result + '" style="max-width:44px;max-height:28px;object-fit:contain;display:block">';
            var content = document.getElementById('logo-drop-content');
            if (content) content.innerHTML = '<img src="' + e.target.result + '" style="max-height:52px;max-width:200px;object-fit:contain;display:block;margin:0 auto 10px"><p style="font-size:12.5px;color:var(--text-2)">Drag &amp; drop a new logo, or <span style="color:var(--blue);font-weight:600;text-decoration:underline">click to upload</span></p>';
        };
        reader.readAsDataURL(file);
    }
}
function previewFavicon(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var wrap = document.getElementById('favicon-preview-wrap');
            if (wrap) wrap.innerHTML = '<img id="favicon-preview-img" src="' + e.target.result + '" style="width:32px;height:32px;object-fit:contain;display:block">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function handleLogoDrop(event) {
    event.preventDefault();
    var zone = document.getElementById('logo-drop-zone');
    if (zone) zone.style.borderColor = '#bfdbfe';
    var files = event.dataTransfer && event.dataTransfer.files;
    if (!files || !files[0]) return;
    var dt = new DataTransfer();
    dt.items.add(files[0]);
    var inp = document.getElementById('logo-upload');
    inp.files = dt.files;
    previewLogo(inp);
}
(function() {
    var p = document.getElementById('primary-color-inp');
    var a = document.getElementById('accent-color-inp');
    if (p && a) updatePreview(p.value, a.value);
})();

// ── Account: login page ───────────────────────────────────────────
@if(!empty($_atabs['tab-login-page']))
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('lp-heading')) { lpUpdateBgTypeVisibility(); lpUpdatePreview(); }
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
    var isGradient  = type.value === 'gradient';
    var isImage     = type.value === 'image';
    var colorWrap   = document.getElementById('lp-colors-wrap');
    var colorToWrap = document.getElementById('lp-bg-color-to-wrap');
    var imageWrap   = document.getElementById('lp-bg-image-wrap');
    var bgLabel     = document.getElementById('lp-bg-color-label');
    if (colorWrap)   colorWrap.style.display  = isImage ? 'none' : 'flex';
    if (colorToWrap) colorToWrap.style.display = isGradient ? '' : 'none';
    if (imageWrap)   imageWrap.style.display   = isImage ? 'block' : 'none';
    if (bgLabel)     bgLabel.textContent       = isGradient ? 'Gradient Start Color' : 'Background Color';
}
function lpUpdatePreview() {
    var type      = document.querySelector('input[name="login_bg_type"]:checked');
    var bgColor   = (document.getElementById('lp-bg-color')    || {}).value || '#0d3d66';
    var bgColorTo = (document.getElementById('lp-bg-color-to') || {}).value || '#f06b21';
    var cardBg    = (document.getElementById('lp-card-bg')     || {}).value || '#ffffff';
    var btnColor  = (document.getElementById('lp-btn-color')   || {}).value || '#f06b21';
    var heading   = (document.getElementById('lp-heading')     || {}).value || 'Welcome back';
    var subheading= (document.getElementById('lp-subheading')  || {}).value || '';
    var bg   = document.getElementById('lp-preview-bg');
    var card = document.getElementById('lp-preview-card');
    var btn  = document.getElementById('lp-preview-btn');
    var h    = document.getElementById('lp-preview-heading');
    var sub  = document.getElementById('lp-preview-subheading');
    if (!bg || !card) return;
    var bgStyle = 'background:linear-gradient(135deg,' + bgColor + ' 0%,' + bgColorTo + ' 130%)';
    if (type) {
        if (type.value === 'solid') { bgStyle = 'background:' + bgColor; }
        else if (type.value === 'image' && bg.dataset.bgImgUrl) { bgStyle = "background:url('" + bg.dataset.bgImgUrl + "') center/cover no-repeat"; }
    }
    bg.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;transition:background .25s;' + bgStyle;
    card.style.background = cardBg;
    if (btn) btn.style.background = btnColor;
    if (h)   h.textContent = heading || 'Welcome back';
    if (sub) sub.textContent = subheading || '';
}
function lpPreviewBgImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) { var bg = document.getElementById('lp-preview-bg'); if (bg) { bg.dataset.bgImgUrl = e.target.result; lpUpdatePreview(); } };
        reader.readAsDataURL(input.files[0]);
    }
}
function lpPreviewLogoImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var wrap = document.getElementById('lp-preview-logo-wrap');
            if (wrap) wrap.innerHTML = '<img src="' + e.target.result + '" alt="Logo" style="max-height:36px;max-width:140px;object-fit:contain;display:block;margin:0 auto">';
            var content = document.getElementById('lp-logo-drop-content');
            if (content) content.innerHTML = '<img src="' + e.target.result + '" style="max-height:52px;max-width:200px;object-fit:contain;display:block;margin:0 auto 10px"><p style="font-size:12.5px;color:var(--text-2)">Drag &amp; drop a new logo, or <span style="color:var(--blue);font-weight:600;text-decoration:underline">click to upload</span></p>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function lpHandleLogoDrop(event) {
    event.preventDefault();
    var zone = document.getElementById('lp-logo-drop-zone');
    if (zone) zone.style.borderColor = '#bfdbfe';
    var files = event.dataTransfer && event.dataTransfer.files;
    if (!files || !files[0]) return;
    var input = document.getElementById('lp-logo');
    if (!input) return;
    var dt = new DataTransfer();
    dt.items.add(files[0]);
    input.files = dt.files;
    lpPreviewLogoImage(input);
}
function submitLoginPageForm(e) {
    e.preventDefault();
    var form = document.getElementById('login-page-form');
    var btn  = document.getElementById('lp-submit');
    var data = new FormData(form);
    btn.disabled = true; btn.textContent = 'Saving...';
    fetch('{{ parse_url(route("settings.login-page.update"), PHP_URL_PATH) }}', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: data,
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        btn.disabled = false; btn.textContent = 'Save Login Page';
        if (res.status === 'success') { Swal.fire({ icon: 'success', text: res.message || 'Login page saved.', confirmButtonText: 'Ok', confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21' }); }
        else { showToast(res.message || 'Something went wrong.', 'error'); }
    })
    .catch(function () { btn.disabled = false; btn.textContent = 'Save Login Page'; showToast('Request failed.', 'error'); });
}
@endif

// ── Account: forgot password ──────────────────────────────────────
@if(!empty($_atabs['tab-forgot-password']))
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('fp-heading')) fpUpdatePreview();
});
function fpUpdatePreview() {
    var bg  = document.getElementById('fp-preview-bg');
    var h   = document.getElementById('fp-preview-heading');
    var sub = document.getElementById('fp-preview-subheading');
    if (!bg) return;
    var heading    = (document.getElementById('fp-heading')    || {}).value || 'Forgot your password?';
    var subheading = (document.getElementById('fp-subheading') || {}).value || '';
    var bgType   = bg.dataset.bgType    || 'gradient';
    var bgColor  = bg.dataset.bgColor   || '#0d3d66';
    var bgColorTo= bg.dataset.bgColorTo || '#f06b21';
    var bgImgUrl = bg.dataset.bgImgUrl  || '';
    var bgStyle  = bgType === 'solid' ? ('background:' + bgColor) : (bgType === 'image' && bgImgUrl ? "background:url('" + bgImgUrl + "') center/cover no-repeat" : ('background:linear-gradient(135deg,' + bgColor + ' 0%,' + bgColorTo + ' 130%)'));
    bg.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;' + bgStyle;
    if (h)   h.textContent   = heading    || 'Forgot your password?';
    if (sub) sub.textContent = subheading || '';
}
function submitFpPageForm(e) {
    e.preventDefault();
    var form = document.getElementById('fp-page-form');
    var btn  = document.getElementById('fp-submit');
    var data = new FormData(form);
    btn.disabled = true; btn.textContent = 'Saving...';
    fetch('{{ parse_url(route("settings.forgot-password-page.update"), PHP_URL_PATH) }}', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: data,
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        btn.disabled = false; btn.textContent = 'Save Forgot Password Page';
        if (res.status === 'success') { Swal.fire({ icon: 'success', text: res.message || 'Forgot password page saved.', confirmButtonText: 'Ok', confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21' }); }
        else { showToast(res.message || 'Something went wrong.', 'error'); }
    })
    .catch(function () { btn.disabled = false; btn.textContent = 'Save Forgot Password Page'; showToast('Request failed.', 'error'); });
}
@endif

// ── Account: subscription ─────────────────────────────────────────
@if(!empty($_atabs['tab-subscription']))
async function subStartCheckout(packageId, btn) {
    var orig = btn.textContent.trim();
    btn.disabled = true; btn.textContent = 'Redirecting…';
    try {
        var res = await fetch('{{ parse_url(route("billing.checkout"), PHP_URL_PATH) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ package_id: packageId, billing_cycle: 'monthly' }),
        });
        var json = await res.json();
        if (json.checkout_url) { window.location.href = json.checkout_url; }
        else { showToast(json.message || 'Could not start checkout.', 'error'); btn.disabled = false; btn.textContent = orig; }
    } catch (e) { showToast('Network error. Please try again.', 'error'); btn.disabled = false; btn.textContent = orig; }
}
function subCancel() {
    var planName  = @json($planName ?? 'your current');
    var periodEnd = @json(isset($renewsAt) && $renewsAt ? $renewsAt->format(active_date_format()) : null);
    Swal.fire({
        title: 'Cancel Subscription?',
        text: 'You are about to cancel your ' + planName + ' plan. Your plan will remain active until the end of the current billing period' + (periodEnd ? ' on ' + periodEnd : '') + '.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Yes, Cancel', cancelButtonText: 'Keep',
        confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', reverseButtons: true,
    }).then(async function(result) {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Canceling…', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
        try {
            var resp = await fetch('{{ parse_url(route("billing.cancel-subscription"), PHP_URL_PATH) }}', {
                method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            var res = await resp.json();
            if (res.status === 'success') { await Swal.fire({ icon: 'success', title: 'Subscription Canceled', text: res.message || 'You will retain access until the end of the billing period.', confirmButtonText: 'OK' }); window.location.reload(); }
            else { Swal.fire({ icon: 'error', title: 'Cancellation Failed', text: res.message || 'Something went wrong.' }); }
        } catch (e) { Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not reach the server. Please try again.' }); }
    });
}
async function subReactivate() {
    var result = await Swal.fire({ title: 'Reactivate Subscription?', text: 'Your subscription will be restored.', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, Reactivate', cancelButtonText: 'Not Now', confirmButtonColor: '#16a34a', cancelButtonColor: '#6b7280', reverseButtons: true });
    if (!result.isConfirmed) return;
    Swal.fire({ title: 'Reactivating…', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
    try {
        var resp = await fetch('{{ parse_url(route("billing.reactivate-subscription"), PHP_URL_PATH) }}', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        var res = await resp.json();
        if (res.status === 'success') { await Swal.fire({ icon: 'success', title: 'Subscription Reactivated', text: res.message || 'Welcome back!', confirmButtonText: 'Go to Dashboard' }); window.location.reload(); }
        else { Swal.fire({ icon: 'error', title: 'Reactivation Failed', text: res.message || 'Something went wrong.' }); }
    } catch (e) { Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not reach the server. Please try again.' }); }
}
var phTable = null;
$(document).ready(function () {
    if (!document.getElementById('ph-table')) return;
    phTable = $('#ph-table').DataTable({
        paging: true, pageLength: 5, lengthChange: false, searching: true, ordering: true, info: true,
        dom: '<"dt-tscroll"t><"dt-footer"ip>', order: [[4, 'desc']], columnDefs: [{ orderable: false, targets: [3] }],
        infoCallback: function(settings, start, end, max, total) { return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'payments' : 'payment'); },
        language: { infoEmpty: 'No payment records found', paginate: { previous: 'Previous', next: 'Next' }, emptyTable: 'No payment records found.' },
    });
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'ph-table') return true;
        var from = (document.getElementById('ph-dr-from') || {}).value || '';
        var to   = (document.getElementById('ph-dr-to')   || {}).value || '';
        if (!from && !to) return true;
        var row     = phTable.row(dataIndex).node();
        var dateVal = row ? ((row.querySelector('td[data-date]') || {}).getAttribute ? (row.querySelector('td[data-date]').getAttribute('data-date') || '') : '') : '';
        if (!dateVal) return false;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    });
    _drCbs['ph-dr'] = function () { if (phTable) phTable.draw(); };
    var phS = document.getElementById('phSearch');
    if (phS) phS.addEventListener('input', function () { if (phTable) phTable.search(this.value).draw(); });
    var phSt = document.getElementById('phStatusFilter');
    if (phSt) phSt.addEventListener('change', function () { if (phTable) phTable.column(3).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw(); });
});
window.phReset = function () {
    document.getElementById('phSearch').value       = '';
    document.getElementById('phStatusFilter').value = '';
    drClear('ph-dr');
    if (phTable) phTable.search('').columns().search('').draw();
};
@endif

// ── Account: activity log ─────────────────────────────────────────
@if(!empty($_atabs['tab-activity']))
var _actDtSearchFn = null;
var _actTypeFn     = null;
function initActivityTable() {
    if (!window.$ || !document.getElementById('activity-table')) return;
    if (_actDtSearchFn) { var idx = $.fn.dataTable.ext.search.indexOf(_actDtSearchFn); if (idx > -1) $.fn.dataTable.ext.search.splice(idx, 1); _actDtSearchFn = null; }
    if (_actTypeFn)     { var idx2 = $.fn.dataTable.ext.search.indexOf(_actTypeFn);    if (idx2 > -1) $.fn.dataTable.ext.search.splice(idx2, 1);  _actTypeFn = null; }
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#activity-table')) { try { $('#activity-table').DataTable().destroy(); } catch (ex) {} }
    var actTable = $('#activity-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false, searching: true, ordering: true, info: true,
        dom: '<"dt-tscroll"t><"dt-footer"ip>', order: [[2, 'desc']],
        infoCallback: function(settings, start, end, max, total) { return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'events' : 'event'); },
        language: { infoEmpty: 'No activity found', paginate: { previous: '&lsaquo;', next: '&rsaquo;' }, emptyTable: '<div style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No activity yet</div>' },
        drawCallback: function () { var info = this.api().page.info(); var badge = document.getElementById('activity-count-badge'); if (badge) badge.textContent = info.recordsDisplay + ' event' + (info.recordsDisplay !== 1 ? 's' : ''); }
    });
    document.getElementById('activitySearch').addEventListener('keyup', function () { actTable.search(this.value).draw(); });
    _actTypeFn = function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'activity-table') return true;
        var selected = document.getElementById('actFilterEventType').value;
        if (!selected) return true;
        var row = actTable.row(dataIndex).node();
        return row ? row.getAttribute('data-event-type') === selected : true;
    };
    $.fn.dataTable.ext.search.push(_actTypeFn);
    document.getElementById('actFilterEventType').addEventListener('change', function () { actTable.draw(); });
    _actDtSearchFn = function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'activity-table') return true;
        var from = (document.getElementById('act-log-dr-from') || {}).value || '';
        var to   = (document.getElementById('act-log-dr-to')   || {}).value || '';
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
    document.getElementById('activitySearch').value     = '';
    drClear('act-log-dr');
    document.getElementById('actFilterEventType').value = '';
    if (window.$ && $('#activity-table').length) { $('#activity-table').DataTable().search('').columns().search('').draw(); }
}
@endif

// ── Account: tab refresh on subscription ──────────────────────────
document.addEventListener('tabActivated', function (e) {
    var tabId = e.detail.tabId;
    if (tabId !== 'tab-subscription') return;
    _refreshAccountTab(tabId);
});
function _refreshAccountTab(tabId) {
    var panel = document.getElementById(tabId);
    if (!panel) return;
    panel.style.pointerEvents = 'none';
    fetch(window.location.pathname, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.text(); })
        .then(function (html) {
            var doc   = new DOMParser().parseFromString(html, 'text/html');
            var fresh = doc.getElementById(tabId);
            if (fresh) {
                if (tabId === 'tab-subscription' && window.$ && $.fn.DataTable && $.fn.DataTable.isDataTable('#ph-table')) { try { $('#ph-table').DataTable().destroy(); phTable = null; } catch(ex) {} }
                panel.innerHTML = fresh.innerHTML;
                if (tabId === 'tab-subscription' && document.getElementById('ph-table') && window.$) {
                    phTable = $('#ph-table').DataTable({ paging: true, pageLength: 5, lengthChange: false, searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>', order: [[4, 'desc']], columnDefs: [{ orderable: false, targets: [3] }], language: { infoEmpty: 'No payment records found', paginate: { previous: 'Previous', next: 'Next' }, emptyTable: 'No payment records found.' } });
                    _drCbs['ph-dr'] = function () { if (phTable) phTable.draw(); };
                }
            }
            panel.style.pointerEvents = '';
        })
        .catch(function () { panel.style.pointerEvents = ''; });
}

// ── Account: date format ──────────────────────────────────────────
var adfSamples = {!! json_encode(array_map(fn($fmt) => \Carbon\Carbon::now()->format($fmt), array_keys(DATE_FORMAT_PRESETS))) !!};
var adfKeys    = {!! json_encode(array_keys(DATE_FORMAT_PRESETS)) !!};
var adfCurrent = '{{ $effectiveFmt ?? active_date_format() }}';
function adfPickFormat(fmt) {
    adfCurrent = fmt;
    var idx = adfKeys.indexOf(fmt);
    document.getElementById('adf-preview').textContent = idx >= 0 ? adfSamples[idx] : fmt;
    document.querySelectorAll('#adf-grid label').forEach(function(lbl) {
        var inp = lbl.querySelector('input');
        if (inp.value === fmt) { lbl.style.borderColor = 'var(--blue)'; lbl.style.background = 'var(--blue-pale)'; }
        else { lbl.style.borderColor = 'var(--border)'; lbl.style.background = '#fff'; }
    });
}
async function saveAll() {
    var btn = document.getElementById('adf-save-btn');
    if (!btn) return;
    var orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = 'Saving…';
    try {
        var fd1 = new FormData();
        fd1.append('date_format', adfCurrent);
        var r1 = await fetch('{{ parse_url(route("account.date-format"), PHP_URL_PATH) }}', { method: 'POST', body: fd1, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        var j1; try { j1 = await r1.json(); } catch(_) { j1 = { status: 'fail' }; }
        if (j1.status !== 'success') { showToast(j1.message || 'Error saving date format.', 'error'); btn.disabled = false; btn.innerHTML = orig; return; }
        var form = document.getElementById('branding-form');
        var fd2  = new FormData(form);
        var res  = await fetch(form.action, { method: 'POST', body: fd2, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        var json = await res.json();
        if (json.status === 'success') {
            var primary = document.getElementById('primary-color-inp').value;
            var accent  = document.getElementById('accent-color-inp').value;
            document.documentElement.style.setProperty('--blue',   primary);
            document.documentElement.style.setProperty('--orange', accent);
            Swal.fire({ icon: 'success', text: json.message || 'Branding updated successfully.', confirmButtonText: 'OK', confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21' });
        } else { showToast(json.message || 'Failed to save branding.', 'error'); }
    } catch(e) { showToast('An error occurred. Please try again.', 'error'); }
    btn.disabled = false; btn.innerHTML = orig;
}

// ── Account: profile & password validation ────────────────────────
function acErr(id, msg) { var el = document.getElementById(id); if (!el) return; el.textContent = msg || ''; el.style.display = msg ? 'block' : 'none'; }
function acMark(id, invalid) { var el = document.getElementById(id); if (!el) return; if (invalid) el.classList.add('ac-invalid'); else el.classList.remove('ac-invalid'); }
function validateAndSubmitProfile() {
    var ok = true;
    var nameEl  = document.getElementById('ac-profile-name');
    var nameVal = nameEl ? nameEl.value.trim() : '';
    if (!nameVal) { acErr('err-profile-name', 'Full name is required.'); acMark('ac-profile-name', true); ok = false; }
    else if (nameVal.length < 2) { acErr('err-profile-name', 'Minimum 2 characters required.'); acMark('ac-profile-name', true); ok = false; }
    else if (!/^[a-zA-Z\s]+$/.test(nameVal)) { acErr('err-profile-name', 'Name must contain only letters and spaces.'); acMark('ac-profile-name', true); ok = false; }
    else { acErr('err-profile-name', ''); acMark('ac-profile-name', false); if (nameEl) nameEl.value = nameVal; }
    if (ok) { var btn = document.getElementById('profile-submit-btn'); if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; } document.getElementById('profile-form').submit(); }
}
(function() {
    var el = document.getElementById('ac-profile-name');
    if (!el) return;
    el.addEventListener('input', function() { var pos = this.selectionStart; var cleaned = this.value.replace(/[^a-zA-Z\s]/g, ''); if (cleaned !== this.value) { this.value = cleaned; this.setSelectionRange(Math.max(0, pos-1), Math.max(0, pos-1)); } });
})();
function validateAndSubmitPassword() {
    var ok = true;
    var curEl  = document.getElementById('ac-cur-pwd');
    var newEl  = document.getElementById('ac-new-pwd');
    var confEl = document.getElementById('ac-confirm-pwd');
    var curVal  = curEl  ? curEl.value  : '';
    var newVal  = newEl  ? newEl.value  : '';
    var confVal = confEl ? confEl.value : '';
    if (!curVal) { acErr('err-cur-pwd', 'Current password is required.'); acMark('ac-cur-pwd', true); ok = false; } else { acErr('err-cur-pwd', ''); acMark('ac-cur-pwd', false); }
    if (!newVal) { acErr('err-new-pwd', 'New password is required.'); acMark('ac-new-pwd', true); ok = false; } else if (newVal.length < 8) { acErr('err-new-pwd', 'Password must be at least 8 characters.'); acMark('ac-new-pwd', true); ok = false; } else { acErr('err-new-pwd', ''); acMark('ac-new-pwd', false); }
    if (!confVal) { acErr('err-confirm-pwd', 'Please confirm your new password.'); acMark('ac-confirm-pwd', true); ok = false; } else if (confVal !== newVal) { acErr('err-confirm-pwd', 'Passwords do not match.'); acMark('ac-confirm-pwd', true); ok = false; } else { acErr('err-confirm-pwd', ''); acMark('ac-confirm-pwd', false); }
    if (ok) { var btn = document.getElementById('password-submit-btn'); if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; } document.getElementById('password-form').submit(); }
}
</script>
@endpush
