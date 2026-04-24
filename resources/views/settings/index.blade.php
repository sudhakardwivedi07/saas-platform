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

{{-- Tab Bar --}}
<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-payment">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Payment
    </button>
    <button class="tab-btn" data-tab="tab-email">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Email
    </button>
    <button class="tab-btn" data-tab="tab-sms">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        SMS
    </button>
    <button class="tab-btn" data-tab="tab-quickbooks">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        QuickBooks
    </button>
    <button class="tab-btn" data-tab="tab-plans">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Plan Management
    </button>
    <button class="tab-btn" data-tab="tab-roles">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Roles &amp; Permissions
    </button>
</div>

{{-- Tab 1: Payment --}}
<div class="tab-panel active" id="tab-payment">
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Stripe Payment Gateway
        </div>

        <div class="info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="info-banner-txt">Configure your Stripe keys to enable payments for your sub-companies.</span>
        </div>

        <form method="POST" action="{{ route('settings.payment-gateway.update') }}">
            @csrf
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
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" class="f-inp" value="{{ url('webhooks/stripe') }}" readonly style="background:var(--bg);color:var(--text-2);flex:1">
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

{{-- Tab 2: Email --}}
<div class="tab-panel" id="tab-email">
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Email SMTP Settings
        </div>

        <form method="POST" action="{{ route('settings.email.update') }}">
            @csrf
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

{{-- Tab 3: SMS --}}
<div class="tab-panel" id="tab-sms">
    <div class="acc-card">
        <div class="acc-section-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            SMS Settings (Twilio)
        </div>

        <div class="info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="info-banner-txt">SMS notifications are powered by Twilio. Enter your credentials from the Twilio Console.</span>
        </div>

        <form method="POST" action="{{ route('settings.sms.update') }}">
            @csrf
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

{{-- Tab 4: QuickBooks --}}
<div class="tab-panel" id="tab-quickbooks">
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

{{-- Tab 5: Plan Management --}}
<div class="tab-panel" id="tab-plans">
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
                        <th>Max Users</th>
                        <th>Storage</th>
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
                        <td>
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
                            {{ $plan->max_users ? $plan->max_users . ' users' : '∞' }}
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2)">
                            {{ $plan->max_storage_gb ? $plan->max_storage_gb . ' GB' : '∞' }}
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
                                <form method="POST" action="{{ route('plans.destroy', $plan) }}" onsubmit="return confirm('Delete plan &quot;{{ addslashes($plan->name) }}&quot;? This cannot be undone.')">
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

{{-- Tab 5: Roles & Permissions --}}
<div class="tab-panel" id="tab-roles">

    {{-- Roles DataTable --}}
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="role-search" placeholder="Search roles…" autocomplete="off">
                </div>
                <select class="sel" id="role-type-filter" autocomplete="off">
                    <option value="">All Types</option>
                    <option value="system">System</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge b-gray" id="role-count-badge">{{ $roles->count() }} roles</span>
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
                    @php $systemRoles = ['client_admin', 'super_admin']; @endphp
                    @foreach($roles as $role)
                    @php
                        $isSystem  = in_array($role->name, $systemRoles);
                        $typeLabel = $isSystem ? 'System' : 'Custom';
                        $typeClass = $isSystem ? 'b-blue' : 'b-gray';
                        $permCount = $role->permissions->count();
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ ucwords(str_replace('_', ' ', $role->name)) }}</div>
                            <div style="font-size:11px;color:var(--text-3);margin-top:1px">{{ $role->name }}</div>
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
                                    onclick="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', {{ json_encode($role->permissions->pluck('name')) }})">
                                    Edit
                                </button>
                                <button class="btn btn-xs" style="background:rgba(239,68,68,.07);color:var(--red);border:1px solid rgba(239,68,68,.2)"
                                    onclick="deleteRole({{ $role->id }}, '{{ addslashes($role->name) }}')">
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

{{-- Create Role Modal --}}
<div class="modal-overlay" id="create-role-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:560px">
        <button class="modal-close" onclick="document.getElementById('create-role-modal').classList.remove('open')">&times;</button>
        <div class="modal-title">Create New Role</div>
        <div class="modal-sub">Define a role name and assign permissions.</div>
        <form id="create-role-form" onsubmit="submitCreateRole(event)">
            @csrf
            <div class="mf-field">
                <label class="mf-label">Role Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" id="cr-name" class="mf-inp" placeholder="e.g. accountant" required autocomplete="off">
                <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Use lowercase with underscores (e.g. senior_manager)</div>
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
            <div class="mf-field">
                <label class="mf-label">Role Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" id="er-name" class="mf-inp" required autocomplete="off">
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
$(document).ready(function() {
    // Only init DataTable if the plans tab has rows
    if ($('#plans-table tbody tr td').length && !$('#plans-table tbody tr td[colspan]').length) {
        document.getElementById('plan-search').value        = '';
        document.getElementById('plan-status-filter').value = '';

        var planTable = $('#plans-table').DataTable({
            paging: true, pageLength: 15, lengthChange: false,
            searching: true, ordering: true, info: true, dom: 'tip',
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [6] }],
            language: {
                info: 'Showing _START_–_END_ of _TOTAL_ plans',
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
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ roles',
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
                showToast(res.message || 'Role created.', 'success');
                setTimeout(function() { location.reload(); }, 900);
            } else {
                showToast(res.message || 'Something went wrong.', 'error');
            }
        })
        .catch(function() { btn.disabled = false; showToast('Request failed.', 'error'); });
}

// ── Edit Role ──────────────────────────────────────────────────────────────────
function openEditRoleModal(id, name, permissions) {
    document.getElementById('er-role-id').value = id;
    document.getElementById('er-name').value     = name;

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
                showToast(res.message || 'Role updated.', 'success');
                setTimeout(function() { location.reload(); }, 900);
            } else {
                showToast(res.message || 'Something went wrong.', 'error');
            }
        })
        .catch(function() { btn.disabled = false; showToast('Request failed.', 'error'); });
}

// ── Delete Role ────────────────────────────────────────────────────────────────
function deleteRole(id, name) {
    if (!confirm('Delete role "' + name + '"? This cannot be undone.')) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
    fetch('{{ route("settings.roles.destroy", ":id") }}'.replace(':id', id), {
        method: 'DELETE',
        headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type':'application/json'},
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status === 'success') {
            showToast(res.message || 'Role deleted.', 'success');
            setTimeout(function() { location.reload(); }, 900);
        } else {
            showToast(res.message || 'Cannot delete role.', 'error');
        }
    })
    .catch(function() { showToast('Request failed.', 'error'); });
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
            showToast(res.message || 'QuickBooks settings saved.', 'success');
        } else {
            showToast(res.message || 'Something went wrong.', 'error');
        }
    })
    .catch(function() { btn.disabled = false; showToast('Request failed.', 'error'); });
}

// Auto-switch tab based on URL hash (#payment, #plans, #email, #sms, #roles, #quickbooks)
// Must be DOMContentLoaded so it runs AFTER the layout registers tab click handlers
document.addEventListener('DOMContentLoaded', function () {
    var hash = window.location.hash.replace('#', '');
    if (!hash) return;
    var btn = document.querySelector('[data-tab="tab-' + hash + '"]');
    if (btn) btn.click();
});
</script>
@endpush
