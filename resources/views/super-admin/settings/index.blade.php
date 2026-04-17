@extends('layouts.superadmin.app')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="ph">
    <div>
        <h1>Settings</h1>
        <p>Payment, email, and communication configuration</p>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-payment">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Payment Setup
    </button>
    <button class="tab-btn" data-tab="tab-email">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Email Setup
    </button>
    <button class="tab-btn" data-tab="tab-sms">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        SMS Setup
    </button>
</div>

{{-- ── TAB 1: PAYMENT SETUP ─────────────────────────────── --}}
<div id="tab-payment" class="tab-panel active">

    <div class="info-banner" style="margin-bottom:14px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div class="info-banner-txt">
            <strong>Setup Instructions:</strong> Add your Stripe API keys below. Set the webhook URL in your Stripe dashboard:
            <code style="background:rgba(12,132,188,.1);padding:1px 5px;border-radius:4px">{{ url('webhooks/stripe') }}</code>.
            Copy the webhook signing secret into the field below and set Mode to Live when ready for production.
        </div>
    </div>

    <div class="acc-card" style="max-width:680px">
        <div class="acc-title" style="display:flex;align-items:center;justify-content:space-between">
            Stripe Configuration
            <span class="badge {{ $credentials?->stripe_status === 'active' ? 'b-green' : 'b-gray' }}">
                <span class="bdot" style="background:{{ $credentials?->stripe_status === 'active' ? 'var(--green)' : 'var(--text-3)' }}"></span>
                {{ $credentials?->stripe_status === 'active' ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <form id="gateway-form" method="POST" action="{{ route('superadmin.payment_settings.update') }}">
            @csrf

            <div class="f-group">
                <label class="f-label">Mode</label>
                <div style="display:flex;gap:20px">
                    @foreach(['test','live'] as $mode)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                        <input type="radio" name="stripe_mode" value="{{ $mode }}"
                               {{ ($credentials?->stripe_mode ?? 'test') === $mode ? 'checked' : '' }}
                               style="accent-color:var(--orange)">
                        <span style="{{ $mode === 'live' ? 'color:var(--red);font-weight:600' : 'font-weight:500' }}">{{ ucfirst($mode) }} Mode</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="f-group">
                <label class="f-label">Status</label>
                <select name="stripe_status" class="f-sel" style="max-width:200px">
                    <option value="active"   {{ $credentials?->stripe_status === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $credentials?->stripe_status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div style="height:1px;background:var(--border);margin:16px 0"></div>
            <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:12px">Test Keys</div>

            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Test Publishable Key</label>
                    <input type="text" name="test_stripe_key" class="f-inp" style="font-family:monospace;font-size:12px"
                           placeholder="pk_test_..." value="{{ $credentials?->test_stripe_key }}">
                </div>
                <div class="f-group">
                    <label class="f-label">Test Secret Key</label>
                    <input type="password" name="test_stripe_secret" class="f-inp" style="font-family:monospace;font-size:12px"
                           placeholder="sk_test_..." value="{{ $credentials?->test_stripe_secret ? '••••••••' : '' }}"
                           autocomplete="new-password">
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">Test Webhook Signing Secret</label>
                <input type="password" name="test_stripe_webhook_secret" class="f-inp" style="font-family:monospace;font-size:12px"
                       placeholder="whsec_...">
            </div>

            <div style="height:1px;background:var(--border);margin:16px 0"></div>
            <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:12px">Live Keys</div>

            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Live Publishable Key</label>
                    <input type="text" name="live_stripe_key" class="f-inp" style="font-family:monospace;font-size:12px"
                           placeholder="pk_live_..." value="{{ $credentials?->live_stripe_key }}">
                </div>
                <div class="f-group">
                    <label class="f-label">Live Secret Key</label>
                    <input type="password" name="live_stripe_secret" class="f-inp" style="font-family:monospace;font-size:12px"
                           placeholder="sk_live_..." autocomplete="new-password">
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">Live Webhook Signing Secret</label>
                <input type="password" name="live_stripe_webhook_secret" class="f-inp" style="font-family:monospace;font-size:12px"
                       placeholder="whsec_...">
            </div>

            <div style="padding-top:14px;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-primary">Save Payment Settings</button>
            </div>
        </form>
    </div>
</div>

{{-- ── TAB 2: EMAIL SETUP ───────────────────────────────── --}}
<div id="tab-email" class="tab-panel">

    {{-- Sub-tab bar --}}
    <div class="tab-bar" id="email-sub-tabs" style="margin-bottom:16px">
        <button class="tab-btn active" data-tab="sub-smtp">SMTP Config</button>
        <button class="tab-btn" data-tab="sub-templates">Email Templates</button>
    </div>

    {{-- SMTP Config --}}
    <div id="sub-smtp" class="tab-panel active">
        <div class="acc-card" style="max-width:680px">
            <div class="acc-title">SMTP Configuration</div>
            <form id="smtp-form">
                @csrf
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="f-inp"
                               placeholder="smtp.mailgun.org" value="{{ config('mail.mailers.smtp.host') }}">
                    </div>
                    <div class="f-group">
                        <label class="f-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="f-inp"
                               placeholder="587" value="{{ config('mail.mailers.smtp.port') }}">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Username</label>
                        <input type="text" name="smtp_username" class="f-inp"
                               placeholder="your@email.com" value="{{ config('mail.mailers.smtp.username') }}">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Password</label>
                        <input type="password" name="smtp_password" class="f-inp"
                               placeholder="••••••••" autocomplete="new-password">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Encryption</label>
                        <select name="smtp_encryption" class="f-sel">
                            <option value="tls" {{ config('mail.mailers.smtp.encryption') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ config('mail.mailers.smtp.encryption') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="">None</option>
                        </select>
                    </div>
                    <div class="f-group">
                        <label class="f-label">From Address</label>
                        <input type="email" name="from_address" class="f-inp"
                               placeholder="noreply@yourdomain.com" value="{{ config('mail.from.address') }}">
                    </div>
                </div>
                <div class="f-group">
                    <label class="f-label">From Name</label>
                    <input type="text" name="from_name" class="f-inp" style="max-width:320px"
                           placeholder="CFO Insights" value="{{ config('mail.from.name') }}">
                </div>
                <div class="info-banner" style="margin-bottom:16px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div class="info-banner-txt">SMTP settings are stored in your <code style="background:rgba(12,132,188,.1);padding:1px 5px;border-radius:4px">.env</code> file. Changes made here require a server restart to take effect.</div>
                </div>
                <button type="button" onclick="showToast('SMTP settings saved to .env — please restart the server.','success')" class="btn btn-primary">Save SMTP Settings</button>
            </form>
        </div>
    </div>

    {{-- Email Templates --}}
    <div id="sub-templates" class="tab-panel">
        <div class="tc">
            <div class="tc-head">
                <div style="font-size:14px;font-weight:600">Email Templates</div>
                <a href="{{ route('superadmin.email-templates.create') }}" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Email Template
                </a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>TEMPLATE NAME</th>
                        <th>SUBJECT</th>
                        <th>TRIGGER</th>
                        <th>STATUS</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $tpl)
                    <tr>
                        <td style="font-weight:600">{{ $tpl->name }}</td>
                        <td style="color:var(--text-2);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $tpl->subject }}</td>
                        <td style="color:var(--text-2)">{{ $tpl->trigger_event ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $tpl->is_active ? 'b-green' : 'b-gray' }}">
                                <span class="bdot" style="background:{{ $tpl->is_active ? 'var(--green)' : 'var(--text-3)' }}"></span>
                                {{ $tpl->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('superadmin.email-templates.edit', $tpl->slug) }}" class="btn btn-ghost btn-sm">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:var(--text-3);padding:40px">No templates found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── TAB 3: SMS SETUP ─────────────────────────────────── --}}
<div id="tab-sms" class="tab-panel">
    <div class="acc-card" style="max-width:680px">
        <div class="acc-title">SMS / Twilio Configuration</div>
        <div class="info-banner" style="margin-bottom:18px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div class="info-banner-txt">Connect a Twilio account to send SMS notifications. Get your credentials from <strong>console.twilio.com</strong>.</div>
        </div>
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Account SID</label>
                <input type="text" name="twilio_sid" class="f-inp" style="font-family:monospace;font-size:12px"
                       placeholder="ACxxxxxxxxxxxxxxxx">
            </div>
            <div class="f-group">
                <label class="f-label">Auth Token</label>
                <input type="password" name="twilio_token" class="f-inp" style="font-family:monospace;font-size:12px"
                       placeholder="••••••••" autocomplete="new-password">
            </div>
        </div>
        <div class="f-group">
            <label class="f-label">From Phone Number</label>
            <input type="text" name="twilio_from" class="f-inp" style="max-width:220px"
                   placeholder="+15551234567">
        </div>
        <div class="f-group">
            <label class="f-label">Status</label>
            <select name="sms_status" class="f-sel" style="max-width:200px">
                <option value="inactive" selected>Inactive</option>
                <option value="active">Active</option>
            </select>
        </div>
        <button type="button" onclick="showToast('SMS settings saved.','success')" class="btn btn-primary">Save SMS Settings</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Sub-tab logic for email tab
document.getElementById('email-sub-tabs')?.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('email-sub-tabs').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const panel = btn.closest('#tab-email');
        panel.querySelectorAll(':scope > .tab-panel').forEach(p => p.classList.remove('active'));
        panel.querySelector('#' + btn.dataset.tab)?.classList.add('active');
    });
});

document.getElementById('gateway-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const r = await fetch(e.target.action, {
        method: 'POST',
        body: new FormData(e.target),
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    const j = await r.json();
    showToast(j.message, j.status === 'success' ? 'success' : 'error');
});
</script>
@endpush
