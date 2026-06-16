@extends('layouts.app')
@section('page-title', 'Settings')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Settings</h1>
        <p>Configure payment, email, SMS, QuickBooks, and plan settings</p>
    </div>
</div>

@php
// Determine which settings tabs this user may see, and which is first/active.
$_stabs = [
    'tab-payment'        => auth()->user()->can('view_settings_payment'),
    'tab-email'          => auth()->user()->can('view_settings_email'),
    'tab-sms'            => auth()->user()->can('view_settings_sms'),
    'tab-quickbooks'     => auth()->user()->can('view_settings_quickbooks'),
    'tab-plans'          => auth()->user()->can('view_settings_plans'),
    'tab-insight-limits' => auth()->user()->can('view_settings_insight_limits'),
    'tab-roles'          => auth()->user()->can('view_settings_roles'),
];
$_sfirst = array_key_first(array_filter($_stabs)) ?? 'tab-payment';
@endphp

{{-- Tab Bar --}}
<div class="tab-bar">
    @if($_stabs['tab-payment'])
    <button class="tab-btn {{ $_sfirst === 'tab-payment' ? 'active' : '' }}" data-tab="tab-payment">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Payment
    </button>
    @endif
    @if($_stabs['tab-email'])
    <button class="tab-btn {{ $_sfirst === 'tab-email' ? 'active' : '' }}" data-tab="tab-email">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Email
    </button>
    @endif
    @if($_stabs['tab-sms'])
    <button class="tab-btn {{ $_sfirst === 'tab-sms' ? 'active' : '' }}" data-tab="tab-sms">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        SMS
    </button>
    @endif
    @if($_stabs['tab-quickbooks'])
    <button class="tab-btn {{ $_sfirst === 'tab-quickbooks' ? 'active' : '' }}" data-tab="tab-quickbooks">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        QuickBooks
    </button>
    @endif
    @if($_stabs['tab-plans'])
    <button class="tab-btn {{ $_sfirst === 'tab-plans' ? 'active' : '' }}" data-tab="tab-plans">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Plan Management
    </button>
    @endif
    @if($_stabs['tab-insight-limits'])
    <button class="tab-btn {{ $_sfirst === 'tab-insight-limits' ? 'active' : '' }}" data-tab="tab-insight-limits">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        Insight Limits
    </button>
    @endif
    @if($_stabs['tab-roles'])
    <button class="tab-btn {{ $_sfirst === 'tab-roles' ? 'active' : '' }}" data-tab="tab-roles">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Roles &amp; Permissions
    </button>
    @endif
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
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Email SMTP Settings 
        </div>

        <form method="POST" action="{{ route('settings.email.update') }}" id="email-form" onsubmit="submitSettingsForm(event,this,'Email settings updated.')">
            @csrf
            <input type="hidden" name="driver" value="smtp">
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">From Name</label>
                    <input type="text" name="from_name" class="f-inp" placeholder="My Platform" value="{{ $smtpSetting->from_name ?? '' }}">
                </div>
                <div class="f-group">
                    <label class="f-label">From Email</label>
                    <input type="email" name="from_email" class="f-inp" placeholder="no-reply@example.com" value="{{ $smtpSetting->from_email ?? '' }}">
                </div>
            </div>
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">SMTP Host</label>
                    <input type="text" name="host" class="f-inp" placeholder="smtp.mailgun.org" value="{{ $smtpSetting->host ?? '' }}">
                </div>
                <div class="f-group">
                    <label class="f-label">SMTP Port</label>
                    <input type="number" name="port" class="f-inp" placeholder="587" value="{{ $smtpSetting->port ?? '587' }}">
                </div>
            </div>
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Username</label>
                    <input type="text" name="username" class="f-inp" placeholder="smtp_user" value="{{ $smtpSetting->username ?? '' }}">
                </div>
                <div class="f-group">
                    <label class="f-label">Password</label>
                    <input type="password" name="password" class="f-inp" placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>
            <div class="f-group" style="max-width:220px">
                <label class="f-label">Encryption</label>
                <select name="encryption" class="f-sel">
                    <option value="tls" {{ ($smtpSetting->encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ ($smtpSetting->encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ ($smtpSetting->encryption ?? '') === 'none' ? 'selected' : '' }}>None</option>
                </select>
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

        <form method="POST" action="{{ route('settings.sms.update') }}" id="sms-form" onsubmit="submitSettingsForm(event,this,'SMS settings updated.')">
            @csrf
            <input type="hidden" name="provider" value="twilio">
            <div class="f-group">
                <label class="f-label">Account SID</label>
                <input type="text" name="account_sid" class="f-inp" placeholder="ACxxxxxxxxxxxxxxxx" value="{{ $smsSetting->account_sid ?? '' }}">
            </div>
            <div class="f-group">
                <label class="f-label">Auth Token</label>
                <input type="password" name="auth_token" class="f-inp" placeholder="••••••••••••••••••••" autocomplete="new-password" value="{{ $smsSetting->auth_token ?? '' }}">
            </div>
            <div class="f-group">
                <label class="f-label">From Number</label>
                <input type="text" name="from_number" class="f-inp" placeholder="+15551234567" value="{{ $smsSetting->from_number ?? '' }}">
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
            QuickBooks Integration Settings
        </div>

        <div class="info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="info-banner-txt">Configure your QuickBooks app credentials to enable OAuth integration for your sub-companies.</span>
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
                    <label class="f-label">QuickBooks Base URL <span style="color:var(--red)">*</span></label>
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

    {{-- Sub-tab bar --}}
    <div style="display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:20px">
        <button type="button" class="il-subtab active" data-subtab="subtab-limit-setup"
            style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-bottom:3px solid var(--orange);background:none;cursor:pointer;color:var(--orange);margin-bottom:-2px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Insight Limit Setup
        </button>
        <button type="button" class="il-subtab" data-subtab="subtab-company-overrides"
            style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-bottom:3px solid transparent;background:none;cursor:pointer;color:var(--text-3);margin-bottom:-2px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            Company-wise Limit Overrides
        </button>
    </div>

    {{-- Sub-tab 1: Insight Limit Setup --}}
    <div id="subtab-limit-setup">
        <div class="acc-card">
            <div class="acc-section-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                Insights Limit
            </div>

            <form id="insight-limit-form" onsubmit="submitInsightLimitForm(event)">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;align-items:start">
                    <div class="f-group" style="margin:0">
                        <label class="f-label">Default Limit Per Client</label>
                        <div style="display:flex;align-items:center;gap:8px">
                            <input type="number" name="default_limit" id="il-default-limit" class="f-inp"
                                value="{{ $insightSetting->default_limit ?? 50 }}"
                                min="1" max="99999" style="max-width:110px">
                            <span style="font-size:13px;color:var(--text-3);white-space:nowrap">insights</span>
                        </div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Applied to all companies unless overridden</div>
                    </div>
                    <div class="f-group" style="margin:0">
                        <label class="f-label">Billing Period</label>
                        <select name="billing_period" class="f-sel">
                            <option value="monthly" {{ ($insightSetting->billing_period ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly"  {{ ($insightSetting->billing_period ?? '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">When the limit resets for each company</div>
                    </div>
                    <div class="f-group" style="margin:0">
                        <label class="f-label">Overage Behaviour</label>
                        <select name="overage_behaviour" class="f-sel">
                            <option value="pause"         {{ ($insightSetting->overage_behaviour ?? 'pause') === 'pause' ? 'selected' : '' }}>Pause generation when limit is reached</option>
                            <option value="allow_overage" {{ ($insightSetting->overage_behaviour ?? '') === 'allow_overage' ? 'selected' : '' }}>Allow overage and notify when limit is reached</option>
                        </select>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">What happens when a company reaches its cap</div>
                    </div>
                </div>
                <div style="margin-top:24px">
                    <button type="submit" class="btn btn-primary" id="il-submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sub-tab 2: Company-wise Limit Overrides --}}
    <div id="subtab-company-overrides" style="display:none">
        <div class="acc-card">
            <div class="acc-section-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                Custom Limit
            </div>
            <div style="font-size:12.5px;color:var(--text-3);margin-bottom:18px">Set a custom monthly insight limit for specific companies. Overrides the default limit.</div>

            @php $ilDefault = $insightSetting->default_limit ?? 50; @endphp

            @if(($companiesWithUsage ?? collect())->isEmpty())
            <div style="text-align:center;padding:56px 20px">
                <div style="width:56px;height:56px;border-radius:14px;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.6"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:8px">Company-level overrides will appear here</div>
                <div style="font-size:13px;color:var(--text-3);line-height:1.6;max-width:480px;margin:0 auto">
                    Once you add clients and their companies, you can set custom insight limits for individual companies here. This is useful when specific clients need more or fewer insights than your default allocation.
                </div>
            </div>
            @else
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr>
                            <th style="text-align:left;padding:10px 12px;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Company</th>
                            <th style="text-align:left;padding:10px 12px;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Client</th>
                            <th style="text-align:center;padding:10px 12px;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Default Limit</th>
                            <th style="text-align:left;padding:10px 12px;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Used This Month</th>
                            <th style="text-align:right;padding:10px 12px;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border)">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companiesWithUsage as $co)
                        @php
                            $coOwner        = $co->users->first();
                            $coCustomLimit  = $co->custom_insight_limit;
                            $coEffective    = $coCustomLimit ?? $ilDefault;
                            $coUsed         = (int) ($co->insights_used ?? 0);
                            $coPct          = $coEffective > 0 ? min(100, round($coUsed / $coEffective * 100)) : 0;
                            $coBarColor     = $coPct >= 90 ? 'var(--red)' : ($coPct >= 70 ? '#f59e0b' : 'var(--blue)');
                        @endphp
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:12px;font-size:13px;font-weight:500;color:var(--text-1)">{{ $co->company_name }}</td>
                            <td style="padding:12px;font-size:13px;color:var(--text-2)">{{ $coOwner?->name ?? '—' }}</td>
                            <td style="padding:12px;font-size:13px;color:var(--text-2);text-align:center">{{ $ilDefault }}</td>
                            <td style="padding:12px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div style="flex:1;max-width:120px;height:6px;background:var(--border);border-radius:3px;overflow:hidden">
                                        <div style="width:{{ $coPct }}%;height:100%;background:{{ $coBarColor }};border-radius:3px;transition:width .3s"></div>
                                    </div>
                                    <span style="font-size:12.5px;color:var(--text-2);white-space:nowrap">{{ $coUsed }}/{{ $coEffective }}</span>
                                </div>
                            </td>
                            <td style="padding:12px;text-align:right">
                                <button type="button" class="btn btn-primary btn-sm"
                                    onclick="openCustomLimitModal({{ $co->id }}, '{{ addslashes($co->company_name) }}', {{ $coCustomLimit ?? $ilDefault }})">
                                    Set Custom Limit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Set Custom Limit Modal --}}
<div class="modal-overlay" id="modal-custom-limit" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:420px">
        <button class="modal-close" onclick="document.getElementById('modal-custom-limit').classList.remove('open')">&times;</button>
        <div class="modal-title">Set Custom Limit</div>
        <div class="modal-sub" id="custom-limit-subtitle"></div>
        <div class="mf-field" style="margin-top:16px">
            <label class="mf-label">Custom Monthly Insight Limit</label>
            <div style="display:flex;align-items:center;gap:10px">
                <input type="number" id="custom-limit-value" class="mf-inp" min="1" max="99999"
                    style="max-width:130px;text-align:center;font-size:16px;font-weight:600">
                <span style="font-size:13px;color:var(--text-3)">insights per month</span>
            </div>
            <div style="font-size:11.5px;color:var(--text-3);margin-top:6px;line-height:1.5">
                This overrides the default limit set in Insight Limit Setup. Set to the default value to remove the override.
            </div>
        </div>
        <input type="hidden" id="custom-limit-company-id">
        <div class="mf-foot">
            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-custom-limit').classList.remove('open')">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm" id="custom-limit-save-btn" onclick="saveCustomLimit()">Save Limit</button>
        </div>
    </div>
</div>

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
                <div class="dr-wrap" id="role-dr">
                    <div class="dr-field" onclick="drOpen('role-dr')">
                        <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="dr-text" id="role-dr-text">Start Date – End Date</span>
                        <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('role-dr')" title="Clear date filter">&#x2715;</button>
                    </div>
                    <div class="dr-menu" id="role-dr-menu">
                        <ul class="dr-list">
                            <li class="dr-opt" onclick="drPickPreset('role-dr','today',this)">Today</li>
                            <li class="dr-opt" onclick="drPickPreset('role-dr','last30',this)">Last 30 Days</li>
                            <li class="dr-opt" onclick="drPickPreset('role-dr','thisMonth',this)">This Month</li>
                            <li class="dr-opt" onclick="drPickPreset('role-dr','lastMonth',this)">Last Month</li>
                            <li class="dr-opt" onclick="drPickPreset('role-dr','last90',this)">Last 90 Days</li>
                            <li class="dr-opt" onclick="drPickPreset('role-dr','last6m',this)">Last 6 Months</li>
                            <li class="dr-opt" onclick="drPickPreset('role-dr','last1y',this)">Last 1 Year</li>
                            <li class="dr-opt" onclick="drPickPreset('role-dr','custom',this)">Custom Range</li>
                        </ul>
                        <div class="dr-custom-row" id="role-dr-custom">
                            <input type="date" class="dr-date-inp" id="role-dr-from">
                            <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                            <input type="date" class="dr-date-inp" id="role-dr-to">
                            <button class="dr-apply-btn" onclick="drApplyCustom('role-dr')">Apply</button>
                        </div>
                    </div>
                </div>
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
                        <th style="width:100px">Actions</th>
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
                                <span class="badge b-green" style="font-size:11.5px">{{ $permCount }} permissions</span>
                            @else
                                <span style="color:var(--text-3);font-size:13px">None</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:5px">
                                @if(!$isSystem)
                                <button class="btn btn-ghost btn-xs"
                                    onclick="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', '{{ addslashes($role->alias ?? '') }}', {{ json_encode($role->permissions->pluck('name')) }})">
                                    Edit
                                </button>
                                <button class="btn btn-xs" style="background:rgba(239,68,68,.07);color:var(--red);border:1px solid rgba(239,68,68,.2)"
                                    onclick="deleteRole({{ $role->id }}, '{{ addslashes($role->display_name) }}')">
                                    Delete
                                </button>
                                @else
                                <span style="font-size:11.5px;color:var(--text-3)">Protected</span>
                                @endif
                            </div>
                        </td>
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
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-top:14px">
            @foreach($allPermissions as $module => $perms)
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
        <form id="create-role-form" onsubmit="submitCreateRole(event)">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start">
                <div class="mf-field">
                    <label class="mf-label">Role Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="cr-name" class="mf-inp" placeholder="e.g. accountant" required autocomplete="off">
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Use lowercase with underscores (e.g. senior_manager)</div>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Alias <span style="font-size:11px;color:var(--text-3)">(display name)</span></label>
                    <input type="text" name="alias" id="cr-alias" class="mf-inp" placeholder="e.g. Account Manager" autocomplete="off">
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Shown to users instead of the role name</div>
                </div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Permissions</label>
                <div style="display:grid;gap:10px;margin-top:6px;max-height:340px;overflow-y:auto;padding-right:4px">
                    @foreach($allPermissions as $module => $perms)
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:11px 13px">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                            <input type="checkbox" class="mod-toggle" data-module="cr-mod-{{ Str::slug($module) }}"
                                id="cr-modall-{{ Str::slug($module) }}"
                                onchange="toggleModule(this)" style="accent-color:var(--orange);cursor:pointer;width:14px;height:14px">
                            <label for="cr-modall-{{ Str::slug($module) }}" style="font-size:11.5px;font-weight:700;color:var(--text-1);cursor:pointer;text-transform:uppercase;letter-spacing:.5px">{{ $module }}</label>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 8px" class="cr-mod-{{ Str::slug($module) }}">
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
                <button type="submit" class="btn btn-primary btn-sm" id="cr-submit">Create Role</button>
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
        <form id="edit-role-form" onsubmit="submitEditRole(event)">
            @csrf
            <input type="hidden" id="er-role-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start">
                <div class="mf-field">
                    <label class="mf-label">Role Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="er-name" class="mf-inp" required autocomplete="off">
                </div>
                <div class="mf-field">
                    <label class="mf-label">Alias <span style="font-size:11px;color:var(--text-3)">(display name)</span></label>
                    <input type="text" name="alias" id="er-alias" class="mf-inp" placeholder="e.g. Account Manager" autocomplete="off">
                </div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Permissions</label>
                <div style="display:grid;gap:10px;margin-top:6px;max-height:340px;overflow-y:auto;padding-right:4px">
                    @foreach($allPermissions as $module => $perms)
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:11px 13px">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                            <input type="checkbox" class="mod-toggle" data-module="er-mod-{{ Str::slug($module) }}"
                                id="er-modall-{{ Str::slug($module) }}"
                                onchange="toggleModule(this)" style="accent-color:var(--orange);cursor:pointer;width:14px;height:14px">
                            <label for="er-modall-{{ Str::slug($module) }}" style="font-size:11.5px;font-weight:700;color:var(--text-1);cursor:pointer;text-transform:uppercase;letter-spacing:.5px">{{ $module }}</label>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 8px" class="er-mod-{{ Str::slug($module) }}">
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
                <button type="submit" class="btn btn-primary btn-sm" id="er-submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
// ── Activate tab from URL hash (e.g. #tab-plans from "Back to Plans") ─────────
(function () {
    var hash = window.location.hash.replace('#', '');
    if (hash) {
        var targetBtn   = document.querySelector('.tab-btn[data-tab="' + hash + '"]');
        var targetPanel = document.getElementById(hash);
        if (targetBtn && targetPanel) {
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
            targetBtn.classList.add('active');
            targetPanel.classList.add('active');
        }
    }
})();

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
                return 'Showing ' + start + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'plans' : 'plan');
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

    // Roles DataTable
    document.getElementById('role-search').value       = '';
    document.getElementById('role-type-filter').value  = '';

    var roleTable = $('#roles-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [4] }],
        infoCallback: function(settings, start, end, max, total, pre) {
            return 'Showing ' + start + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'roles' : 'role');
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

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'roles-table') return true;
        var from = document.getElementById('role-dr-from') ? document.getElementById('role-dr-from').value : '';
        var to   = document.getElementById('role-dr-to')   ? document.getElementById('role-dr-to').value   : '';
        if (!from && !to) return true;
        var row = roleTable.row(dataIndex).node();
        if (!row) return true;
        var dateVal = row.querySelector('td[data-date]')?.getAttribute('data-date') || '';
        if (!dateVal) return true;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    });
    _drCbs['role-dr'] = function() { roleTable.draw(); };
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
function openCreateRoleModal() {
    document.getElementById('create-role-form').reset();
    document.querySelectorAll('#create-role-modal .mod-toggle').forEach(function(cb) { cb.checked = false; });
    document.getElementById('create-role-modal').classList.add('open');
}

function submitCreateRole(e) {
    e.preventDefault();
    var form = document.getElementById('create-role-form');
    var btn  = document.getElementById('cr-submit');
    var data = new FormData(form);
    btn.disabled = true;

    fetch('{{ route("settings.roles.store") }}', { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            if (res.status === 'success') {
                document.getElementById('create-role-modal').classList.remove('open');
                var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
                Swal.fire({ icon: 'success', text: res.message || 'Role created.', confirmButtonText: 'Ok', confirmButtonColor: btnColor })
                    .then(function() { location.reload(); });
            } else {
                showToast(res.message || 'Something went wrong.', 'error');
            }
        })
        .catch(function() { btn.disabled = false; showToast('Request failed.', 'error'); });
}

// ── Edit Role ──────────────────────────────────────────────────────────────────
function openEditRoleModal(id, name, alias, permissions) {
    document.getElementById('er-role-id').value = id;
    document.getElementById('er-name').value     = name;
    document.getElementById('er-alias').value    = alias || '';

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

    document.getElementById('edit-role-modal').classList.add('open');
}

function submitEditRole(e) {
    e.preventDefault();
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

// Auto-switch tab based on URL hash (#payment, #plans, #email, #sms, #roles, #quickbooks)
document.addEventListener('DOMContentLoaded', function () {
    var hash = window.location.hash.replace('#', '');
    if (!hash) return;
    var btn = document.querySelector('[data-tab="tab-' + hash + '"]');
    if (btn) btn.click();
});

// ── Insight Limits sub-tab switching ──────────────────────────────────────────
document.querySelectorAll('.il-subtab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var target = this.dataset.subtab;
        document.querySelectorAll('.il-subtab').forEach(function(b) {
            b.style.color = 'var(--text-3)';
            b.style.borderBottomColor = 'transparent';
            b.style.fontWeight = '500';
        });
        this.style.color = 'var(--orange)';
        this.style.borderBottomColor = 'var(--orange)';
        this.style.fontWeight = '600';

        document.querySelectorAll('#subtab-limit-setup, #subtab-company-overrides').forEach(function(p) {
            p.style.display = 'none';
        });
        var panel = document.getElementById(target);
        if (panel) panel.style.display = '';
    });
});

// ── Insight Limit Setup form ──────────────────────────────────────────────────
function submitInsightLimitForm(e) {
    e.preventDefault();
    var form = document.getElementById('insight-limit-form');
    var btn  = document.getElementById('il-submit');
    var origHtml = btn.innerHTML;

    var limitVal = parseInt(document.getElementById('il-default-limit').value, 10);
    if (!limitVal || limitVal < 1) {
        Swal.fire({ icon: 'error', title: 'Invalid Limit', text: 'Please enter a valid limit (minimum 1).', confirmButtonColor: '#f06b21' });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0" stroke-dasharray="40" stroke-dashoffset="10"/></svg> Saving…';

    fetch('{{ route("settings.insight-limits.update") }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false; btn.innerHTML = origHtml;
        if (res.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: res.message || 'Insight limit settings saved.', confirmButtonText: 'Ok', confirmButtonColor: btnColor });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Something went wrong.', confirmButtonColor: '#f06b21' });
        }
    })
    .catch(function() {
        btn.disabled = false; btn.innerHTML = origHtml;
        Swal.fire({ icon: 'error', title: 'Request Failed', text: 'Could not save settings. Please try again.', confirmButtonColor: '#f06b21' });
    });
}

// ── Custom Limit Modal ────────────────────────────────────────────────────────
function openCustomLimitModal(companyId, companyName, currentLimit) {
    document.getElementById('custom-limit-company-id').value = companyId;
    document.getElementById('custom-limit-subtitle').textContent = 'Set a custom monthly insight limit for: ' + companyName;
    document.getElementById('custom-limit-value').value = currentLimit || 50;
    document.getElementById('modal-custom-limit').classList.add('open');
    setTimeout(function() { document.getElementById('custom-limit-value').focus(); }, 80);
}

function saveCustomLimit() {
    var companyId = document.getElementById('custom-limit-company-id').value;
    var limitVal  = parseInt(document.getElementById('custom-limit-value').value, 10);
    var btn       = document.getElementById('custom-limit-save-btn');

    if (!limitVal || limitVal < 1) {
        Swal.fire({ icon: 'error', title: 'Invalid Limit', text: 'Please enter a valid limit (minimum 1).', confirmButtonColor: '#f06b21' });
        return;
    }

    btn.disabled = true;
    var origHtml = btn.innerHTML;
    btn.innerHTML = 'Saving…';

    var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
    var url  = '{{ route("settings.insight-limits.custom", ":id") }}'.replace(':id', companyId);

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ custom_limit: limitVal }),
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false; btn.innerHTML = origHtml;
        document.getElementById('modal-custom-limit').classList.remove('open');
        if (res.status === 'success') {
            var btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
            Swal.fire({ icon: 'success', text: res.message || 'Custom limit saved.', confirmButtonText: 'Ok', confirmButtonColor: btnColor })
                .then(function() { location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Could not save limit.', confirmButtonColor: '#f06b21' });
        }
    })
    .catch(function() {
        btn.disabled = false; btn.innerHTML = origHtml;
        Swal.fire({ icon: 'error', title: 'Request Failed', text: 'Could not save limit. Please try again.', confirmButtonColor: '#f06b21' });
    });
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
    drClear('role-dr');
    if (window.$ && $('#roles-table').length) {
        $('#roles-table').DataTable().search('').columns().search('').draw();
    }
}
</script>
@endpush
