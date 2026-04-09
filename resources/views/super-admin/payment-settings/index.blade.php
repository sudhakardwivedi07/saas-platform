@extends('layouts.superadmin.app')
@section('title','Payment Gateway')
@section('page-title','Payment Gateway Settings')

@section('content')
<div class="max-w-2xl">
<div class="card">
    <div class="card-header">
        <h3>Stripe Configuration</h3>
        <span class="badge {{ $credentials?->stripe_status === 'active' ? 'badge-green' : 'badge-gray' }}">
            {{ $credentials?->stripe_status === 'active' ? 'Active' : 'Inactive' }}
        </span>
    </div>
    <div class="card-body">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-800 flex gap-3">
            <span class="material-icons text-lg flex-shrink-0">info</span>
            <div>
                <p class="font-medium mb-1">Setup Instructions</p>
                <ol class="list-decimal list-inside space-y-1 text-blue-700">
                    <li>Add your Stripe API keys below</li>
                    <li>Set webhook URL in Stripe dashboard: <code class="bg-blue-100 px-1 rounded">{{ url('webhooks/stripe') }}</code></li>
                    <li>Copy the webhook signing secret into the field below</li>
                    <li>Set Mode to Live when ready for production</li>
                </ol>
            </div>
        </div>

        <form id="gateway-form" method="POST" action="{{ route('superadmin.payment_settings.update') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Mode</label>
                <div class="flex gap-4">
                    @foreach(['test','live'] as $mode)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="stripe_mode" value="{{ $mode }}"
                               {{ ($credentials?->stripe_mode ?? 'test') === $mode ? 'checked' : '' }}
                               class="accent-blue-600">
                        <span class="text-sm font-medium capitalize {{ $mode === 'live' ? 'text-red-600' : 'text-slate-700' }}">
                            {{ ucfirst($mode) }} Mode
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="stripe_status" class="form-control">
                    <option value="active"   {{ $credentials?->stripe_status === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $credentials?->stripe_status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <hr class="my-5 border-slate-100">
            <p class="text-sm font-semibold text-slate-700 mb-4">Test Keys</p>

            <div class="form-group">
                <label class="form-label">Test Publishable Key</label>
                <input type="text" name="test_stripe_key" class="form-control font-mono text-sm"
                       placeholder="pk_test_..." value="{{ $credentials?->test_stripe_key }}">
            </div>
            <div class="form-group">
                <label class="form-label">Test Secret Key</label>
                <input type="password" name="test_stripe_secret" class="form-control font-mono text-sm"
                       placeholder="sk_test_..." value="{{ $credentials?->test_stripe_secret ? '••••••••' : '' }}"
                       autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Test Webhook Signing Secret</label>
                <input type="password" name="test_stripe_webhook_secret" class="form-control font-mono text-sm"
                       placeholder="whsec_...">
            </div>

            <hr class="my-5 border-slate-100">
            <p class="text-sm font-semibold text-slate-700 mb-4">Live Keys</p>

            <div class="form-group">
                <label class="form-label">Live Publishable Key</label>
                <input type="text" name="live_stripe_key" class="form-control font-mono text-sm"
                       placeholder="pk_live_..." value="{{ $credentials?->live_stripe_key }}">
            </div>
            <div class="form-group">
                <label class="form-label">Live Secret Key</label>
                <input type="password" name="live_stripe_secret" class="form-control font-mono text-sm"
                       placeholder="sk_live_..." autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Live Webhook Signing Secret</label>
                <input type="password" name="live_stripe_webhook_secret" class="form-control font-mono text-sm"
                       placeholder="whsec_...">
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
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
    const r = await fetch(e.target.action, { method:'POST', body: new FormData(e.target), headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} });
    const j = await r.json();
    showToast(j.message, j.status === 'success' ? 'success' : 'error');
});
</script>
@endpush
