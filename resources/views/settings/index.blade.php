@extends('layouts.app')
@section('page-title', 'Settings')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Settings</h1>
        <p>Configure payment, email, SMS, and plan settings</p>
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
    <button class="tab-btn" data-tab="tab-plans">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Plan Management
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
            <div class="f-group">
                <label class="f-label">Live Publishable Key</label>
                <input type="text" name="stripe_key" class="f-inp" placeholder="pk_live_…" value="{{ $paymentGateway->stripe_key ?? '' }}">
            </div>
            <div class="f-group">
                <label class="f-label">Secret Key</label>
                <input type="password" name="stripe_secret" class="f-inp" placeholder="sk_live_…" value="{{ $paymentGateway->stripe_secret ?? '' }}" autocomplete="new-password">
            </div>
            <div class="f-group">
                <label class="f-label">Webhook Secret</label>
                <input type="text" name="stripe_webhook_secret" class="f-inp" placeholder="whsec_…" value="{{ $paymentGateway->stripe_webhook_secret ?? '' }}">
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

{{-- Tab 4: Plan Management --}}
<div class="tab-panel" id="tab-plans">
    <div class="ph" style="margin-bottom:16px">
        <div class="ph-left">
            <h1 style="font-size:16px">My Plans</h1>
            <p>Manage the plans you offer to your sub-companies.</p>
        </div>
        <div class="ph-actions">
            <a href="{{ route('plans.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Plan
            </a>
        </div>
    </div>

    @if($clientPackages->isNotEmpty())
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
        @foreach($clientPackages as $plan)
        <div class="card" style="margin-bottom:0">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px">
                <div class="card-title" style="margin-bottom:0">{{ $plan->name }}</div>
                @if(($plan->status ?? 'active') === 'active')
                    <span class="badge b-green" style="font-size:11px">Active</span>
                @else
                    <span class="badge b-gray" style="font-size:11px">Inactive</span>
                @endif
            </div>
            <div style="font-size:22px;font-weight:700;color:var(--text-1);margin-bottom:4px">
                ${{ number_format($plan->monthly_price ?? 0, 0) }}<span style="font-size:13px;font-weight:400;color:var(--text-3)">/mo</span>
            </div>
            @if($plan->description)
            <div style="font-size:12.5px;color:var(--text-2);margin-bottom:12px;line-height:1.45">{{ Str::limit($plan->description, 80) }}</div>
            @endif
            <div style="margin-bottom:14px">
                @if($plan->max_users)
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-2);margin-bottom:4px">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Up to {{ $plan->max_users }} users
                </div>
                @endif
                @if($plan->max_storage_gb)
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-2);margin-bottom:4px">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ $plan->max_storage_gb }} GB storage
                </div>
                @endif
                @if(!empty($plan->features))
                    @foreach(array_slice(is_array($plan->features) ? $plan->features : [], 0, 3) as $feat)
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-2);margin-bottom:4px">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ Str::title(str_replace('_', ' ', $feat)) }}
                    </div>
                    @endforeach
                @endif
            </div>
            <div style="display:flex;gap:7px;border-top:1px solid var(--border);padding-top:12px">
                <a href="{{ route('plans.edit', $plan) }}" class="btn btn-ghost btn-xs" style="flex:1;justify-content:center">Edit</a>
                <form method="POST" action="{{ route('plans.destroy', $plan) }}" style="flex:1" onsubmit="return confirm('Delete this plan?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs" style="width:100%;background:var(--red-light);color:var(--red);border:1px solid #fca5a5">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="tc">
        <div style="text-align:center;padding:56px 20px">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.3" style="margin:0 auto 14px;display:block"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <div style="font-size:14px;font-weight:500;color:var(--text-2)">No plans created yet</div>
            <div style="font-size:12.5px;color:var(--text-3);margin-top:5px">Create your first plan to offer it to your sub-companies.</div>
            <a href="{{ route('plans.create') }}" class="btn btn-primary btn-sm" style="margin-top:16px;display:inline-flex">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create First Plan
            </a>
        </div>
    </div>
    @endif
</div>

@endsection
