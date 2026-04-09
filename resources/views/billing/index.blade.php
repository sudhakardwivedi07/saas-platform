@extends('layouts.app')
@section('title','Billing')
@section('page-title','Billing & Subscription')

@section('content')

{{-- Current Plan --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <h3>Current Plan</h3>
            @if($subscription)
            <span class="badge {{ $subscription->status === 'active' ? 'badge-green' :
                ($subscription->status === 'trialing' ? 'badge-yellow' : 'badge-red') }}">
                {{ ucfirst($subscription->status) }}
            </span>
            @endif
        </div>
        <div class="card-body">
            @if($subscription)
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $subscription->package?->name ?? 'Unknown Plan' }}</h2>
                    <p class="text-gray-500 mt-1">{{ $subscription->billing_cycle === 'annual' ? 'Annual' : 'Monthly' }} billing</p>

                    @if($subscription->isTrialing())
                    <div class="mt-3 flex items-center gap-2 text-amber-700">
                        <span class="material-icons text-base">hourglass_empty</span>
                        <span class="text-sm font-medium">
                            Free trial — ends {{ $subscription->trial_ends_at->format('F j, Y') }}
                            ({{ $subscription->trial_ends_at->diffForHumans() }})
                        </span>
                    </div>
                    @elseif($subscription->current_period_end)
                    <p class="text-sm text-gray-400 mt-2">
                        Next billing date: {{ $subscription->current_period_end->format('F j, Y') }}
                    </p>
                    @endif
                </div>

                <div class="text-right">
                    <p class="text-3xl font-bold text-gray-800">
                        ${{ number_format($subscription->billing_cycle === 'annual'
                            ? ($subscription->package?->annual_price ?? 0)
                            : ($subscription->package?->monthly_price ?? 0), 2) }}
                    </p>
                    <p class="text-gray-400 text-sm">per {{ $subscription->billing_cycle === 'annual' ? 'year' : 'month' }}</p>
                </div>
            </div>
            @else
            <div class="text-center py-6">
                <span class="material-icons text-5xl text-gray-300 block mb-3">credit_card_off</span>
                <p class="text-gray-600 font-medium">No active subscription</p>
                <p class="text-sm text-gray-400 mt-1">Choose a plan below to get started.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Plan limits --}}
    @if($subscription?->package)
    <div class="card">
        <div class="card-header"><h3>Plan Limits</h3></div>
        <div class="card-body space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Users</span>
                    <span class="font-medium">{{ $subscription->package->max_users ?? '∞' }}</span>
                </div>
                <div class="progress"><div class="progress-bar" style="width:30%"></div></div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Storage</span>
                    <span class="font-medium">{{ $subscription->package->max_storage_gb ? $subscription->package->max_storage_gb . ' GB' : '∞' }}</span>
                </div>
                <div class="progress"><div class="progress-bar" style="width:15%"></div></div>
            </div>
            @if($subscription->package->features)
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Included Features</p>
                @foreach($subscription->package->features as $feature)
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-1">
                    <span class="material-icons text-green-500 text-base">check</span>
                    {{ Str::title(str_replace('_', ' ', $feature)) }}
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Plan upgrade --}}
<div class="card mb-6">
    <div class="card-header">
        <h3>{{ $subscription ? 'Upgrade Plan' : 'Choose a Plan' }}</h3>
    </div>
    <div class="card-body">
        <div class="flex gap-3 mb-5">
            <button onclick="setBilling('monthly')" id="btn-monthly"
                    class="btn btn-primary btn-sm">Monthly</button>
            <button onclick="setBilling('annual')" id="btn-annual"
                    class="btn btn-secondary btn-sm">Annual <span class="badge badge-green ml-1 text-xs">Save 20%</span></button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($packages as $pkg)
            <div class="border-2 border-gray-200 rounded-xl p-5 hover:border-blue-400 transition cursor-pointer relative
                        {{ $subscription?->package_id === $pkg->id ? 'border-blue-500 bg-blue-50' : '' }}"
                 onclick="selectPlan({{ $pkg->id }})">
                @if($pkg->is_recommended)
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="badge badge-blue px-3">Most Popular</span>
                </div>
                @endif
                @if($subscription?->package_id === $pkg->id)
                <div class="absolute top-3 right-3">
                    <span class="badge badge-green text-xs">Current Plan</span>
                </div>
                @endif

                <h3 class="font-bold text-gray-800 mb-1">{{ $pkg->name }}</h3>
                <p class="text-xs text-gray-400 mb-3">{{ $pkg->description }}</p>

                <div class="mb-4">
                    <span class="text-2xl font-bold text-gray-800 monthly-price"
                          data-monthly="{{ $pkg->monthly_price }}" data-annual="{{ $pkg->annual_price }}">
                        ${{ number_format($pkg->monthly_price, 0) }}
                    </span>
                    <span class="text-gray-400 text-sm billing-label">/month</span>
                </div>

                <ul class="space-y-1 mb-4 text-sm text-gray-600">
                    <li class="flex items-center gap-1">
                        <span class="material-icons text-green-500 text-sm">check</span>
                        {{ $pkg->max_users ?? 'Unlimited' }} users
                    </li>
                    <li class="flex items-center gap-1">
                        <span class="material-icons text-green-500 text-sm">check</span>
                        {{ $pkg->max_storage_gb ? $pkg->max_storage_gb . ' GB' : 'Unlimited' }} storage
                    </li>
                    @if($pkg->is_trial_allowed)
                    <li class="flex items-center gap-1">
                        <span class="material-icons text-amber-500 text-sm">hourglass_empty</span>
                        {{ $pkg->trial_days }}-day free trial
                    </li>
                    @endif
                </ul>

                @if($subscription?->package_id !== $pkg->id)
                <button onclick="checkout({{ $pkg->id }})" class="btn btn-primary btn-block btn-sm">
                    {{ $subscription ? 'Switch to This Plan' : 'Get Started' }}
                </button>
                @else
                <button disabled class="btn btn-secondary btn-block btn-sm" style="opacity:.5">Current Plan</button>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Payment history --}}
<div class="card">
    <div class="card-header"><h3>Payment History</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th><th>Amount</th><th>Status</th><th>Description</th><th>Invoice ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at?->format('M d, Y') ?? $payment->created_at->format('M d, Y') }}</td>
                    <td class="font-semibold">${{ number_format($payment->amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $payment->status === 'complete' ? 'badge-green' :
                            ($payment->status === 'pending' ? 'badge-yellow' : 'badge-red') }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="text-gray-500">{{ $payment->description ?? 'Subscription payment' }}</td>
                    <td class="font-mono text-xs text-gray-400">{{ $payment->stripe_invoice_id ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-400">No payment history yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $payments->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
let billingCycle = 'monthly';

function setBilling(cycle) {
    billingCycle = cycle;
    document.getElementById('btn-monthly').className = `btn btn-sm ${cycle==='monthly'?'btn-primary':'btn-secondary'}`;
    document.getElementById('btn-annual').className  = `btn btn-sm ${cycle==='annual' ?'btn-primary':'btn-secondary'}`;
    document.querySelectorAll('.billing-label').forEach(el => el.textContent = cycle === 'annual' ? '/year' : '/month');
    document.querySelectorAll('.monthly-price').forEach(el => {
        const price = cycle === 'annual' ? el.dataset.annual : el.dataset.monthly;
        el.textContent = '$' + parseFloat(price).toLocaleString('en-US', {maximumFractionDigits:0});
    });
}

async function checkout(packageId) {
    const btn = event.currentTarget;
    btn.disabled = true; btn.textContent = 'Redirecting…';
    const json = await fetch('{{ route("billing.checkout") }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
        body: JSON.stringify({package_id: packageId, billing_cycle: billingCycle})
    }).then(r=>r.json());

    if (json.checkout_url) {
        window.location.href = json.checkout_url;
    } else {
        showToast(json.message || 'Could not start checkout.', 'error');
        btn.disabled = false; btn.textContent = 'Get Started';
    }
}
</script>
@endpush
