@extends('layouts.app')

@section('title', 'Edit Plan — ' . $plan->name)
@section('page-title', 'Edit Plan')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <a href="{{ route('plans.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <span class="material-icons text-base">arrow_back</span> Back to Plans
    </a>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('plans.update', $plan->id) }}">
        @csrf

        <div class="card">
            <div class="card-body space-y-5">

                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 text-lg">Edit: {{ $plan->name }}</h3>
                    <span class="text-xs {{ $plan->is_active ? 'text-green-600 bg-green-100' : 'text-gray-500 bg-gray-100' }} px-2 py-1 rounded-full">
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                {{-- Name --}}
                <div class="form-group">
                    <label class="form-label">Plan Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $plan->name) }}" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description) }}</textarea>
                </div>

                {{-- Pricing --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Monthly Price ($) <span class="text-red-500">*</span></label>
                        <input type="number" name="monthly_price" step="0.01" min="0"
                               class="form-control" value="{{ old('monthly_price', $plan->monthly_price) }}" required
                               id="monthly_price_input">
                        @error('monthly_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Annual Price ($) <span class="text-xs text-gray-400">(optional)</span></label>
                        <input type="number" name="annual_price" step="0.01" min="0"
                               class="form-control" value="{{ old('annual_price', $plan->annual_price) }}">
                    </div>
                </div>

                {{-- Limits --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Max Users <span class="text-xs text-gray-400">(blank = unlimited)</span></label>
                        <input type="number" name="max_users" min="1"
                               class="form-control" value="{{ old('max_users', $plan->max_users) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Storage (GB) <span class="text-xs text-gray-400">(blank = unlimited)</span></label>
                        <input type="number" name="max_storage_gb" min="1"
                               class="form-control" value="{{ old('max_storage_gb', $plan->max_storage_gb) }}">
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Toggles --}}
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_free" value="0">
                        <input type="checkbox" name="is_free" value="1" class="w-4 h-4 rounded accent-blue-600"
                               {{ old('is_free', $plan->is_free) ? 'checked' : '' }} id="chk_free"
                               onchange="toggleFree(this)">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Free Plan</p>
                            <p class="text-xs text-gray-400">Set price to $0 and mark as free</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_recommended" value="0">
                        <input type="checkbox" name="is_recommended" value="1" class="w-4 h-4 rounded accent-blue-600"
                               {{ old('is_recommended', $plan->is_recommended) ? 'checked' : '' }}>
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Recommended</p>
                            <p class="text-xs text-gray-400">Highlight this plan to users</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_trial_allowed" value="0">
                        <input type="checkbox" name="is_trial_allowed" value="1" class="w-4 h-4 rounded accent-blue-600"
                               {{ old('is_trial_allowed', $plan->is_trial_allowed) ? 'checked' : '' }} id="chk_trial"
                               onchange="document.getElementById('trial_days_group').style.display=this.checked?'block':'none'">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Free Trial</p>
                            <p class="text-xs text-gray-400">Allow trial before payment</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded accent-blue-600"
                               {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Active</p>
                            <p class="text-xs text-gray-400">Visible when adding companies</p>
                        </div>
                    </label>
                </div>

                {{-- Trial days --}}
                <div id="trial_days_group" style="{{ old('is_trial_allowed', $plan->is_trial_allowed) ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label class="form-label">Trial Period (days)</label>
                        <input type="number" name="trial_days" min="1" max="365"
                               class="form-control max-w-xs" value="{{ old('trial_days', $plan->trial_days ?: 14) }}">
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Features --}}
                <div class="form-group">
                    <label class="form-label">Features <span class="text-xs text-gray-400">(one per line)</span></label>
                    @php
                        $featuresText = old('features',
                            $plan->features ? implode("\n", $plan->features) : ''
                        );
                    @endphp
                    <textarea name="features" class="form-control font-mono text-sm" rows="5"
                              placeholder="Up to 10 users&#10;50GB storage&#10;Priority support">{{ $featuresText }}</textarea>
                </div>

                <hr class="border-gray-100">

                {{-- Stripe --}}
                <div>
                    <h4 class="font-medium text-gray-700 mb-3 flex items-center gap-2">
                        <span class="material-icons text-indigo-500">credit_card</span>
                        Stripe Price IDs
                        <span class="text-xs text-gray-400 font-normal">(optional)</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label text-xs">Monthly Price ID</label>
                            <input type="text" name="stripe_monthly_price_id" class="form-control font-mono text-sm"
                                   value="{{ old('stripe_monthly_price_id', $plan->stripe_monthly_price_id) }}"
                                   placeholder="price_xxxxxxxx">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-xs">Annual Price ID</label>
                            <input type="text" name="stripe_annual_price_id" class="form-control font-mono text-sm"
                                   value="{{ old('stripe_annual_price_id', $plan->stripe_annual_price_id) }}"
                                   placeholder="price_xxxxxxxx">
                        </div>
                    </div>
                </div>

                {{-- Sort --}}
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort" min="0" class="form-control max-w-xs"
                           value="{{ old('sort', $plan->sort) }}">
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('plans.index') }}" class="btn btn-secondary">Cancel</a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleFree(chk) {
    const priceInput = document.getElementById('monthly_price_input');
    if (chk.checked) { priceInput.value = '0'; priceInput.setAttribute('readonly', true); }
    else             { priceInput.removeAttribute('readonly'); }
}
// Init free plan toggle on load
if (document.getElementById('chk_free')?.checked) {
    document.getElementById('monthly_price_input')?.setAttribute('readonly', true);
}
</script>
@endpush
