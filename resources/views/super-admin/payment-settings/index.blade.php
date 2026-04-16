@extends('layouts.superadmin.app')
@section('title','Payment Gateway')
@section('page-title','Payment Gateway Settings')

@section('content')
<div class="ph">
    <div>
        <h1>Payment Gateway</h1>
        <p>Configure Stripe API keys and webhook settings</p>
    </div>
    <div class="ph-actions">
        <span class="badge {{ $credentials?->stripe_status === 'active' ? 'b-green' : 'b-gray' }}">
            <span class="bdot" style="background:{{ $credentials?->stripe_status === 'active' ? 'var(--green)' : 'var(--text-3)' }}"></span>
            {{ $credentials?->stripe_status === 'active' ? 'Active' : 'Inactive' }}
        </span>
    </div>
</div>

<div class="info-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="info-banner-txt">
        <strong>Setup Instructions:</strong> Add your Stripe API keys below, then set the webhook URL in your Stripe dashboard: <code style="background:rgba(12,132,188,.1);padding:1px 5px;border-radius:4px">{{ url('webhooks/stripe') }}</code>. Copy the webhook signing secret into the field below and set Mode to Live when ready for production.
    </div>
</div>

<div class="acc-card" style="max-width:680px">
    <div class="acc-title">Stripe Configuration</div>
    <form id="gateway-form" method="POST" action="{{ route('superadmin.payment_settings.update') }}">
        @csrf

        <div class="f-group">
            <label class="f-label">Mode</label>
            <div style="display:flex;gap:16px">
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
            <select name="stripe_status" class="f-sel">
                <option value="active"   {{ $credentials?->stripe_status === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $credentials?->stripe_status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <div style="font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:12px">Test Keys</div>

        <div class="f-group">
            <label class="f-label">Test Publishable Key</label>
            <input type="text" name="test_stripe_key" class="f-inp" style="font-family:monospace"
                   placeholder="pk_test_..." value="{{ $credentials?->test_stripe_key }}">
        </div>
        <div class="f-group">
            <label class="f-label">Test Secret Key</label>
            <input type="password" name="test_stripe_secret" class="f-inp" style="font-family:monospace"
                   placeholder="sk_test_..." value="{{ $credentials?->test_stripe_secret ? '••••••••' : '' }}"
                   autocomplete="new-password">
        </div>
        <div class="f-group">
            <label class="f-label">Test Webhook Signing Secret</label>
            <input type="password" name="test_stripe_webhook_secret" class="f-inp" style="font-family:monospace"
                   placeholder="whsec_...">
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <div style="font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:12px">Live Keys</div>

        <div class="f-group">
            <label class="f-label">Live Publishable Key</label>
            <input type="text" name="live_stripe_key" class="f-inp" style="font-family:monospace"
                   placeholder="pk_live_..." value="{{ $credentials?->live_stripe_key }}">
        </div>
        <div class="f-group">
            <label class="f-label">Live Secret Key</label>
            <input type="password" name="live_stripe_secret" class="f-inp" style="font-family:monospace"
                   placeholder="sk_live_..." autocomplete="new-password">
        </div>
        <div class="f-group">
            <label class="f-label">Live Webhook Signing Secret</label>
            <input type="password" name="live_stripe_webhook_secret" class="f-inp" style="font-family:monospace"
                   placeholder="whsec_...">
        </div>

        <div style="display:flex;gap:8px;padding-top:14px;border-top:1px solid var(--border)">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('gateway-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const r = await fetch(e.target.action, { method:'POST', body: new FormData(e.target), headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} });
    const j = await r.json();
    showToast(j.message, j.status === 'success' ? 'success' : 'error');
});
</script>
@endpush
