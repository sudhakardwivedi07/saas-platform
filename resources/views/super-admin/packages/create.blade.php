@extends('layouts.superadmin.app')
@section('title', isset($package) ? 'Edit Package' : 'New Package')
@section('page-title', isset($package) ? 'Edit Package' : 'New Package')

@section('content')
<div class="max-w-2xl">
<div class="card">
    <div class="card-header">
        <h3>{{ isset($package) ? 'Edit: ' . $package->name : 'Create New Package' }}</h3>
    </div>
    <div class="card-body">
        <form method="POST"
              action="{{ isset($package) ? route('superadmin.packages.update', $package) : route('superadmin.packages.store') }}"
              id="pkg-form">
            @csrf
            @if(isset($package)) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group col-span-2">
                    <label class="form-label">Package Name *</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $package->name ?? '') }}" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="form-group col-span-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $package->description ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Monthly Price ($) *</label>
                    <input type="number" step="0.01" name="monthly_price" class="form-control"
                           value="{{ old('monthly_price', $package->monthly_price ?? '0') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Annual Price ($) *</label>
                    <input type="number" step="0.01" name="annual_price" class="form-control"
                           value="{{ old('annual_price', $package->annual_price ?? '0') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Stripe Monthly Price ID</label>
                    <input type="text" name="stripe_monthly_price_id" class="form-control font-mono text-sm"
                           placeholder="price_..." value="{{ old('stripe_monthly_price_id', $package->stripe_monthly_price_id ?? '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Stripe Annual Price ID</label>
                    <input type="text" name="stripe_annual_price_id" class="form-control font-mono text-sm"
                           placeholder="price_..." value="{{ old('stripe_annual_price_id', $package->stripe_annual_price_id ?? '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Max Users (leave blank = unlimited)</label>
                    <input type="number" name="max_users" class="form-control"
                           value="{{ old('max_users', $package->max_users ?? '') }}" min="1">
                </div>

                <div class="form-group">
                    <label class="form-label">Max Storage GB (blank = unlimited)</label>
                    <input type="number" name="max_storage_gb" class="form-control"
                           value="{{ old('max_storage_gb', $package->max_storage_gb ?? '') }}" min="1">
                </div>

                <div class="form-group">
                    <label class="form-label">Trial Days</label>
                    <input type="number" name="trial_days" class="form-control"
                           value="{{ old('trial_days', $package->trial_days ?? 14) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort" class="form-control"
                           value="{{ old('sort', $package->sort ?? 0) }}" min="0">
                </div>
            </div>

            {{-- Feature flags --}}
            <div class="form-group">
                <label class="form-label">Features (comma-separated keys)</label>
                <input type="text" name="features_raw" class="form-control"
                       placeholder="email,sms,documents,quickbooks,api_access"
                       value="{{ old('features_raw', isset($package) ? implode(',', $package->features ?? []) : '') }}">
                <p class="text-xs text-slate-400 mt-1">These keys control feature access in the tenant panel.</p>
            </div>

            {{-- Toggles --}}
            <div class="grid grid-cols-2 gap-3 mb-5">
                @foreach([
                    ['is_active','Active (visible to clients)', true],
                    ['is_trial_allowed','Allow Free Trial', true],
                    ['is_recommended','Mark as Recommended', false],
                    ['is_free','Free Plan', false],
                ] as [$field, $label, $default])
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="{{ $field }}" value="1"
                           {{ old($field, ($package->{$field} ?? $default) ? '1' : '') ? 'checked' : '' }}
                           class="w-4 h-4 accent-blue-600">
                    <span class="text-sm text-slate-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>

            <div class="flex gap-3 pt-2 border-t border-slate-100">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons text-lg">save</span>
                    {{ isset($package) ? 'Update Package' : 'Create Package' }}
                </button>
                <a href="{{ route('superadmin.packages.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('pkg-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);

    // Convert comma-separated features to array notation
    const raw = data.get('features_raw') || '';
    data.delete('features_raw');
    raw.split(',').map(f => f.trim()).filter(Boolean).forEach(f => data.append('features[]', f));

    const r = await fetch(form.action, { method: form._method?.value === 'PUT' ? 'POST' : 'POST', body: data, headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'} });
    const json = await r.json();
    if (json.status === 'success') {
        showToast(json.message, 'success');
        setTimeout(() => window.location = '{{ route("superadmin.packages.index") }}', 1000);
    } else {
        showToast(json.message || 'Validation error', 'error');
    }
});
</script>
@endpush
