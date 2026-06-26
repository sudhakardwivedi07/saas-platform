@extends('layouts.app')
@section('page-title', 'Settings')

@section('content')

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

// Build dynamic subtitle from visible tabs
$_sTabLabels = [
    'tab-payment'        => 'Payment',
    'tab-email'          => 'Email',
    'tab-sms'            => 'SMS',
    'tab-quickbooks'     => 'QuickBooks',
    'tab-plans'          => 'Plan Management',
    'tab-insight-limits' => 'Insight Limits',
    'tab-roles'          => 'Roles & Permissions',
];
$_sVisible = [];
foreach ($_sTabLabels as $_k => $_lbl) {
    if (!empty($_stabs[$_k])) $_sVisible[] = $_lbl;
}
$_n = count($_sVisible);
if ($_n === 0) {
    $_sSubtitle = 'Configure your settings';
} elseif ($_n === 1) {
    $_sSubtitle = 'Configure your ' . strtolower($_sVisible[0]) . ' settings';
} elseif ($_n === 2) {
    $_sSubtitle = 'Configure your ' . strtolower($_sVisible[0]) . ' and ' . strtolower($_sVisible[1]) . ' settings';
} else {
    $_sLast = array_pop($_sVisible);
    $_sSubtitle = 'Configure your ' . implode(', ', array_map('strtolower', $_sVisible)) . ', and ' . strtolower($_sLast) . ' settings';
}
@endphp

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Settings</h1>
        <p>{{ $_sSubtitle }}</p>
    </div>
</div>

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

        <table id="il-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th style="text-align:center">Companies</th>
                    <th style="text-align:center">Insights Used</th>
                    <th style="text-align:center">Insights Remaining</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:right;width:120px">Action <span class="help-tip" data-tip="Set company-wise insight limits.">i</span></th>
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

@endsection

@push('styles')
<style>
.st-err{display:none;color:var(--red);font-size:11.5px;margin-top:4px}
.f-inp.st-invalid,.f-sel.st-invalid{border-color:var(--red)!important;outline:none}
.il-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;align-items:start}
@media(max-width:700px){.il-grid{grid-template-columns:1fr 1fr}}
@media(max-width:480px){.il-grid{grid-template-columns:1fr}}
@media(max-width:640px){
  .cr-perms-grid,.er-perms-grid{grid-template-columns:1fr!important}
}
</style>
@endpush

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
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
        var btn   = document.querySelector('.tab-btn[data-tab="' + tabId + '"]');
        var panel = document.getElementById(tabId);
        if (!btn || !panel) return false;
        document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
        btn.classList.add('active');
        panel.classList.add('active');
        history.replaceState(null, '', location.pathname + '#' + tabId);
        window.scrollTo(0, 0);
        return true;
    }

    // On load: honour URL hash only (no localStorage restore — default is server-rendered first tab)
    var hash = window.location.hash.replace('#', '');
    if (hash) {
        activateTab(hash) || activateTab('tab-' + hash);
    }

    // On tab click: reset the target tab's content, then activate it
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
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
</script>
@endpush
