@extends('layouts.app')
@section('title', 'Payment Gateway')
@section('page-title', 'Payment Gateway Settings')

@section('content')
<div class="max-w-3xl">
<div class="card">
    <div class="card-header">
        <h3>Stripe Configuration</h3>
        <span class="badge {{ $gateway?->status === 'active' ? 'badge-green' : 'badge-gray' }}">
            {{ $gateway?->status === 'active' ? '● Active' : '○ Inactive' }}
        </span>
    </div>
    <div class="card-body">

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-800 flex gap-3">
            <span class="material-icons text-lg flex-shrink-0">info</span>
            <div>
                <p class="font-medium mb-1">Setup Instructions</p>
                <ol class="list-decimal list-inside space-y-1 text-blue-700">
                    <li>Get your API keys from <a href="{{ ($gateway?->mode ?? 'test') === 'live' ? config('services.stripe.dashboard_live_url') : config('services.stripe.dashboard_test_url') }}" target="_blank" rel="noopener noreferrer" class="underline">Stripe Dashboard</a></li>
                    <li>Add your webhook endpoint in Stripe: <code class="bg-blue-100 px-1 rounded text-xs">{{ url('webhooks/stripe') }}</code></li>
                    <li>Copy the webhook signing secret and paste it below</li>
                    <li>Switch to Live mode only when ready for production</li>
                </ol>
            </div>
        </div>

        <form id="gateway-form" method="POST" action="{{ route('settings.payment-gateway.update') }}">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div class="form-group">
                    <label class="form-label">Mode</label>
                    <div class="flex gap-4 mt-1">
                        @foreach(['test' => 'Test Mode', 'live' => 'Live Mode'] as $val => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="mode" value="{{ $val }}"
                                   {{ ($gateway?->mode ?? 'test') === $val ? 'checked' : '' }}
                                   class="accent-blue-600">
                            <span class="text-sm font-medium {{ $val === 'live' ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $label }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="inactive" {{ ($gateway?->status ?? 'inactive') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="active"   {{ ($gateway?->status) === 'active' ? 'selected' : '' }}>Active</option>
                    </select>
                </div>
            </div>

            <hr class="my-5 border-gray-100">
            <p class="text-sm font-semibold text-gray-700 mb-4">Test Keys <span class="text-xs font-normal text-gray-400">(safe to use in development)</span></p>

            <div class="form-group">
                <label class="form-label">Test Publishable Key</label>
                <input type="text" name="test_key" class="form-control font-mono text-sm"
                       placeholder="pk_test_..." value="{{ $gateway?->test_key }}">
            </div>
            <div class="form-group">
                <label class="form-label">Test Secret Key</label>
                <input type="password" name="test_secret" class="form-control font-mono text-sm"
                       placeholder="sk_test_..."
                       value="{{ $gateway?->test_secret ? '••••••••' : '' }}"
                       autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Test Webhook Secret</label>
                <input type="password" name="test_webhook_secret" class="form-control font-mono text-sm"
                       placeholder="whsec_..."
                       value="{{ $gateway?->test_webhook_secret ? '••••••••' : '' }}">
            </div>

            <hr class="my-5 border-gray-100">
            <p class="text-sm font-semibold text-gray-700 mb-4">Live Keys <span class="text-xs font-normal text-red-400">(use only in production)</span></p>

            <div class="form-group">
                <label class="form-label">Live Publishable Key</label>
                <input type="text" name="live_key" class="form-control font-mono text-sm"
                       placeholder="pk_live_..." value="{{ $gateway?->live_key }}">
            </div>
            <div class="form-group">
                <label class="form-label">Live Secret Key</label>
                <input type="password" name="live_secret" class="form-control font-mono text-sm"
                       placeholder="sk_live_..." autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Live Webhook Secret</label>
                <input type="password" name="live_webhook_secret" class="form-control font-mono text-sm"
                       placeholder="whsec_...">
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons text-lg">save</span> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('gateway-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    const res = await fetch(e.target.action, {
        method: 'POST',
        body: new FormData(e.target),
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
    }).then(r => r.json());
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
});
</script>
@endpush
