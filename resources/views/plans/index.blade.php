@extends('layouts.app')

@section('title', 'My Plans')
@section('page-title', 'My Plans')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Plans</h2>
            <p class="text-sm text-gray-500 mt-1">
                Define custom subscription plans for the companies you create.
                These are separate from the SuperAdmin's global plans.
            </p>
        </div>
        @can('add_plan')
        <a href="{{ route('plans.create') }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:.4rem">
            <span class="material-icons text-lg">add</span>
            New Plan
        </a>
        @endcan
    </div>

    @if($plans->isEmpty())
    <div class="card">
        <div class="card-body text-center py-16">
            <span class="material-icons text-6xl text-gray-300">loyalty</span>
            <h3 class="mt-4 text-lg font-semibold text-gray-600">No plans created yet</h3>
            <p class="text-gray-400 mt-1 text-sm">
                Create plans to offer when you add new companies.
            </p>
            @can('add_plan')
            <a href="{{ route('plans.create') }}" class="btn btn-primary mt-5">
                <span class="material-icons text-lg mr-1">add</span> Create First Plan
            </a>
            @endcan
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($plans as $plan)
        @php
            $badgeClass = $plan->is_active
                ? 'bg-green-100 text-green-700'
                : 'bg-gray-100 text-gray-500';
        @endphp
        <div class="card hover:shadow-md transition-shadow">
            <div class="card-body space-y-3">

                {{-- Plan header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-gray-800">{{ $plan->name }}</h3>
                            @if($plan->is_recommended)
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">★ Recommended</span>
                            @endif
                            @if($plan->is_free)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Free</span>
                            @endif
                        </div>
                        @if($plan->description)
                        <p class="text-sm text-gray-500 mt-0.5">{{ Str::limit($plan->description, 80) }}</p>
                        @endif
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $badgeClass }} flex-shrink-0">
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                {{-- Pricing --}}
                <div class="flex items-end gap-1 border-t border-gray-100 pt-3">
                    @if($plan->is_free || $plan->monthly_price == 0)
                    <span class="text-2xl font-bold text-green-600">Free</span>
                    @else
                    <span class="text-3xl font-bold text-gray-800">${{ number_format($plan->monthly_price, 0) }}</span>
                    <span class="text-sm text-gray-400 mb-1">/mo</span>
                    @if($plan->annual_price)
                    <span class="text-xs text-gray-400 ml-2 mb-1">(or ${{ number_format($plan->annual_price, 0) }}/yr)</span>
                    @endif
                    @endif
                </div>

                {{-- Details --}}
                <div class="text-sm text-gray-600 space-y-1">
                    @if($plan->max_users)
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-sm text-gray-400">people</span>
                        Up to {{ $plan->max_users }} users
                    </div>
                    @endif
                    @if($plan->max_storage_gb)
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-sm text-gray-400">storage</span>
                        {{ $plan->max_storage_gb }}GB storage
                    </div>
                    @endif
                    @if($plan->is_trial_allowed && $plan->trial_days)
                    <div class="flex items-center gap-2 text-green-600">
                        <span class="material-icons text-sm">verified</span>
                        {{ $plan->trial_days }}-day free trial
                    </div>
                    @endif
                    @if($plan->stripe_monthly_price_id)
                    <div class="flex items-center gap-2 text-indigo-600">
                        <span class="material-icons text-sm">link</span>
                        Stripe Price ID linked
                    </div>
                    @endif
                </div>

                {{-- Features list --}}
                @if($plan->features && count($plan->features))
                <div class="border-t border-gray-100 pt-2">
                    <ul class="text-xs text-gray-600 space-y-1">
                        @foreach(array_slice($plan->features, 0, 4) as $feature)
                        <li class="flex items-center gap-1">
                            <span class="material-icons text-green-500 text-xs">check</span>
                            {{ $feature }}
                        </li>
                        @endforeach
                        @if(count($plan->features) > 4)
                        <li class="text-gray-400">+{{ count($plan->features) - 4 }} more…</li>
                        @endif
                    </ul>
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                    @can('edit_plan')
                    <a href="{{ route('plans.edit', $plan->id) }}"
                       class="btn btn-outline btn-sm flex items-center gap-1 text-xs">
                        <span class="material-icons text-sm">edit</span> Edit
                    </a>
                    <button onclick="togglePlan({{ $plan->id }}, this)"
                            data-active="{{ $plan->is_active ? '1' : '0' }}"
                            class="btn btn-outline btn-sm text-xs {{ $plan->is_active ? 'text-amber-600' : 'text-green-600' }}">
                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    @endcan
                    @can('delete_plan')
                    <button onclick="deletePlan({{ $plan->id }}, '{{ addslashes($plan->name) }}')"
                            class="btn btn-outline btn-sm text-xs text-red-600 ml-auto">
                        <span class="material-icons text-sm">delete</span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- Delete confirmation form (hidden) --}}
<form id="delete-plan-form" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
async function togglePlan(id, btn) {
    btn.disabled = true;
    const res = await fetch('{{ route("plans.toggle", ":id") }}'.replace(':id', id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
        }
    }).then(r => r.json());

    if (res.status === 'success') {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(res.message || 'Error', 'error');
        btn.disabled = false;
    }
}

function deletePlan(id, name) {
    if (!confirm(`Delete plan "${name}"? This cannot be undone.`)) return;
    const form = document.getElementById('delete-plan-form');
    form.action = '{{ route("plans.destroy", ":id") }}'.replace(':id', id);
    form.submit();
}
</script>
@endpush
