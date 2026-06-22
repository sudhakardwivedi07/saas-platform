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

    <div class="acc-card">
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
        <div class="acc-card">
            <div class="acc-title">SMTP Configuration</div>
            <form id="smtp-form" action="{{ route('superadmin.smtp_settings.update') }}">
                @csrf
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">SMTP Host <span style="color:red;">*</span></label>
                        <input type="text" name="smtp_host" id="smtp-host" class="f-inp"
                               placeholder="smtp.mailgun.org" value="{{ $smtpSetting?->host }}">
                        <div id="error-smtp_host" style="color:red;font-size:0.75rem;margin-top:4px;"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">SMTP Port <span style="color:red;">*</span></label>
                        <input type="number" name="smtp_port" id="smtp-port" class="f-inp"
                               placeholder="587" value="{{ $smtpSetting?->port }}">
                        <div id="error-smtp_port" style="color:red;font-size:0.75rem;margin-top:4px;"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Username <span style="color:red;">*</span></label>
                        <input type="text" name="smtp_username" id="smtp-username" class="f-inp"
                               placeholder="your@email.com" value="{{ $smtpSetting?->username }}">
                        <div id="error-smtp_username" style="color:red;font-size:0.75rem;margin-top:4px;"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Password</label>
                        <input type="password" name="smtp_password" class="f-inp"
                               placeholder="••••••••" autocomplete="new-password">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Encryption</label>
                        <select name="smtp_encryption" class="f-sel">
                            <option value="tls" {{ ($smtpSetting?->encryption ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($smtpSetting?->encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="" {{ ($smtpSetting?->encryption ?? '') === '' || $smtpSetting?->encryption === null ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                    <div class="f-group">
                        <label class="f-label">From Address <span style="color:red;">*</span></label>
                        <input type="email" name="from_address" id="smtp-from_address" class="f-inp"
                               placeholder="noreply@yourdomain.com" value="{{ $smtpSetting?->from_email }}">
                        <div id="error-from_address" style="color:red;font-size:0.75rem;margin-top:4px;"></div>
                    </div>
                </div>
                <div class="f-group">
                    <label class="f-label">From Name <span style="color:red;">*</span></label>
                    <input type="text" name="from_name" id="smtp-from_name" class="f-inp" style="max-width:320px"
                           placeholder="CFO Insights" value="{{ $smtpSetting?->from_name }}">
                    <div id="error-from_name" style="color:red;font-size:0.75rem;margin-top:4px;"></div>
                </div>
                <div class="info-banner" style="margin-bottom:16px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div class="info-banner-txt">SMTP settings are stored in the database and applied at runtime when sending emails.</div>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <button type="button" onclick="saveSmtpSettings()" class="btn btn-primary">Save SMTP Settings</button>
                    <button type="button" id="saSmtpTestTrigger" onclick="openSaSmtpTestModal()" disabled
                        style="padding:9px 18px;border-radius:7px;background:#fff;border:1.5px solid #6c5ecf;color:#6c5ecf;font-weight:600;font-size:14px;cursor:default;display:flex;align-items:center;gap:6px;opacity:0.45;pointer-events:none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Send Test Email
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SA SMTP Test Modal --}}
    <div id="saSmtpTestModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:28px 32px;width:100%;max-width:420px;box-shadow:0 8px 32px rgba(0,0,0,.18);position:relative;">
            <button onclick="closeSaSmtpTestModal()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#888;line-height:1;">&times;</button>
            <div style="font-size:17px;font-weight:700;color:#1a1a2e;margin-bottom:6px;">Send Test Email</div>
            <div style="font-size:13px;color:#666;margin-bottom:18px;">Enter an email address to receive a test message using your saved SMTP settings.</div>
            <label style="font-size:13px;font-weight:600;color:#444;display:block;margin-bottom:6px;">Recipient Email</label>
            <input type="email" id="saSmtpTestEmail" class="f-inp" placeholder="you@example.com" value="{{ auth()->user()->email }}" style="width:100%;margin-bottom:16px;">
            <div id="saSmtpTestResult" style="display:none;padding:10px 14px;border-radius:7px;font-size:13px;margin-bottom:14px;"></div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeSaSmtpTestModal()" style="padding:8px 18px;border-radius:7px;border:1.5px solid #ddd;background:#fff;color:#555;font-size:14px;cursor:pointer;font-weight:600;">Cancel</button>
                <button type="button" id="saSmtpTestBtn" onclick="sendSaSmtpTest()" style="padding:8px 20px;border-radius:7px;background:#6c5ecf;color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;">
                    <svg id="saSmtpTestSpinner" style="display:none;animation:spin .8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" width="15" height="15"><circle cx="12" cy="12" r="10" stroke-opacity=".3"/><path d="M12 2a10 10 0 010 20"/></svg>
                    Send
                </button>
            </div>
        </div>
    </div>
    <script>
    function openSaSmtpTestModal() {
        document.getElementById('saSmtpTestModal').style.display = 'flex';
        document.getElementById('saSmtpTestResult').style.display = 'none';
    }
    function closeSaSmtpTestModal() {
        document.getElementById('saSmtpTestModal').style.display = 'none';
    }
    function sendSaSmtpTest() {
        const email = document.getElementById('saSmtpTestEmail').value.trim();
        const result = document.getElementById('saSmtpTestResult');
        const btn = document.getElementById('saSmtpTestBtn');
        const spinner = document.getElementById('saSmtpTestSpinner');

        if (!email) { showSaSmtpResult('error', 'Please enter a recipient email address.'); return; }

        btn.disabled = true;
        spinner.style.display = 'inline-block';
        result.style.display = 'none';

        fetch('{{ route('superadmin.smtp_settings.test') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ to_email: email })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                showSaSmtpResult('success', data.message || 'Test email sent successfully!');
            } else {
                showSaSmtpResult('error', data.message || 'SMTP test failed.');
            }
        })
        .catch(() => showSaSmtpResult('error', 'Network error. Please try again.'))
        .finally(() => { btn.disabled = false; spinner.style.display = 'none'; });
    }
    function showSaSmtpResult(type, msg) {
        const el = document.getElementById('saSmtpTestResult');
        el.style.display = 'block';
        if (type === 'success') {
            el.style.background = '#f0fdf4'; el.style.color = '#166534'; el.style.border = '1px solid #bbf7d0';
        } else {
            el.style.background = '#fef2f2'; el.style.color = '#991b1b'; el.style.border = '1px solid #fecaca';
        }
        el.textContent = msg;
    }
    document.getElementById('saSmtpTestModal').addEventListener('click', function(e) {
        if (e.target === this) closeSaSmtpTestModal();
    });
    </script>

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
    <div class="acc-card">
        <div class="acc-title">SMS / Twilio Configuration</div>
        <form id="sms-form" action="{{ route('superadmin.sms_settings.update') }}">
            @csrf
            <div class="info-banner" style="margin-bottom:18px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div class="info-banner-txt">Connect a Twilio account to send SMS notifications. Get your credentials from <strong>console.twilio.com</strong>.</div>
            </div>
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Account SID <span style="color:red;">*</span></label>
                    <input type="text" name="twilio_sid" id="sms-account_sid" class="f-inp" style="font-family:monospace;font-size:12px"
                           placeholder="ACxxxxxxxxxxxxxxxx" value="{{ $smsSetting?->account_sid }}">
                    <div id="error-twilio_sid" style="color:red;font-size:0.75rem;margin-top:4px;"></div>
                </div>
                <div class="f-group">
                    <label class="f-label">Auth Token</label>
                    <input type="password" name="twilio_token" class="f-inp" style="font-family:monospace;font-size:12px"
                           placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">From Phone Number <span style="color:red;">*</span></label>
                <input type="text" name="twilio_from" id="sms-from_number" class="f-inp" style="max-width:220px"
                       placeholder="+15551234567" value="{{ $smsSetting?->from_number }}">
                <div id="error-twilio_from" style="color:red;font-size:0.75rem;margin-top:4px;"></div>
            </div>
            <div class="f-group">
                <label class="f-label">Status</label>
                <select name="sms_status" class="f-sel" style="max-width:200px">
                    <option value="inactive" {{ ($smsSetting?->is_active ?? false) ? '' : 'selected' }}>Inactive</option>
                    <option value="active"   {{ ($smsSetting?->is_active ?? false) ? 'selected' : '' }}>Active</option>
                </select>
            </div>
            <button type="button" onclick="saveSmsSettings()" class="btn btn-primary">Save SMS Settings</button>
        </form>
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

async function saveSmtpSettings() {
    var valid = true;
    ['smtp_host','smtp_port','smtp_username','from_address','from_name'].forEach(function(id) {
        var el = document.getElementById('error-' + id);
        if (el) el.textContent = '';
    });

    var host = document.getElementById('smtp-host');
    if (!host || !host.value.trim()) {
        document.getElementById('error-smtp_host').textContent = 'SMTP Host is required.';
        valid = false;
    }

    var port = document.getElementById('smtp-port');
    if (!port || !port.value.toString().trim()) {
        document.getElementById('error-smtp_port').textContent = 'SMTP Port is required.';
        valid = false;
    }

    var username = document.getElementById('smtp-username');
    if (!username || !username.value.trim()) {
        document.getElementById('error-smtp_username').textContent = 'Username is required.';
        valid = false;
    }

    var fromAddress = document.getElementById('smtp-from_address');
    if (!fromAddress || !fromAddress.value.trim()) {
        document.getElementById('error-from_address').textContent = 'From Address is required.';
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fromAddress.value.trim())) {
        document.getElementById('error-from_address').textContent = 'Please enter a valid email address.';
        valid = false;
    }

    var fromName = document.getElementById('smtp-from_name');
    if (!fromName || !fromName.value.trim()) {
        document.getElementById('error-from_name').textContent = 'From Name is required.';
        valid = false;
    }

    if (!valid) return;

    var form = document.getElementById('smtp-form');
    try {
        var r = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        var j = await r.json();
        showToast(j.message, j.status === 'success' ? 'success' : 'error');
    } catch (e) {
        showToast('Failed to save SMTP settings.', 'error');
    }
}

async function saveSmsSettings() {
    var valid = true;

    ['twilio_sid', 'twilio_from'].forEach(function(id) {
        var el = document.getElementById('error-' + id);
        if (el) el.textContent = '';
    });

    var accountSid = document.getElementById('sms-account_sid');
    if (!accountSid || !accountSid.value.trim()) {
        document.getElementById('error-twilio_sid').textContent = 'Account SID is required.';
        valid = false;
    }

    var fromNumber = document.getElementById('sms-from_number');
    if (!fromNumber || !fromNumber.value.trim()) {
        document.getElementById('error-twilio_from').textContent = 'From Phone Number is required.';
        valid = false;
    } else if (!/^\+?[0-9\s\-()]{6,20}$/.test(fromNumber.value.trim())) {
        document.getElementById('error-twilio_from').textContent = 'Please enter a valid phone number (e.g. +15551234567).';
        valid = false;
    }

    if (!valid) return;

    var form = document.getElementById('sms-form');
    try {
        var r = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        var j = await r.json();
        showToast(j.message, j.status === 'success' ? 'success' : 'error');
    } catch (e) {
        showToast('Failed to save SMS settings.', 'error');
    }
}

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
