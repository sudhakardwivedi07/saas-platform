@extends('layouts.superadmin.app')
@section('title', isset($package) ? 'Edit Package' : 'New Package')
@section('page-title', isset($package) ? 'Edit Package' : 'New Package')

@section('content')
<div class="ph">
    <div>
        <h1>{{ isset($package) ? 'Edit: '.$package->name : 'Create New Package' }}</h1>
        <p>{{ isset($package) ? 'Update plan details and pricing' : 'Define a new subscription plan' }}</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.packages.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:700px">
    <div class="acc-title">{{ isset($package) ? 'Edit Package' : 'Package Details' }}</div>
    <form id="pkg-form">
        @csrf
        @if(isset($package)) @method('PUT') @endif

        <div class="form-2col">
            <div class="f-group" style="grid-column:span 2">
                <label class="f-label">Package Name *</label>
                <input type="text" name="name" class="f-inp"
                       value="{{ old('name', $package->name ?? '') }}" required>
                @error('name') <div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
            </div>

            <div class="f-group" style="grid-column:span 2">
                <label class="f-label">Description</label>
                <textarea name="description" rows="2" class="f-textarea">{{ old('description', $package->description ?? '') }}</textarea>
            </div>

            <div class="f-group">
                <label class="f-label">Monthly Price ($) *</label>
                <input type="number" step="0.01" name="monthly_price" class="f-inp"
                       value="{{ old('monthly_price', $package->monthly_price ?? '0') }}" required>
            </div>

            <div class="f-group">
                <label class="f-label">Annual Price ($) *</label>
                <input type="number" step="0.01" name="annual_price" class="f-inp"
                       value="{{ old('annual_price', $package->annual_price ?? '0') }}" required>
            </div>

            <div class="f-group">
                <label class="f-label">Stripe Monthly Price ID</label>
                <input type="text" name="stripe_monthly_price_id" class="f-inp" style="font-family:monospace"
                       placeholder="price_..." value="{{ old('stripe_monthly_price_id', $package->stripe_monthly_price_id ?? '') }}">
            </div>

            <div class="f-group">
                <label class="f-label">Stripe Annual Price ID</label>
                <input type="text" name="stripe_annual_price_id" class="f-inp" style="font-family:monospace"
                       placeholder="price_..." value="{{ old('stripe_annual_price_id', $package->stripe_annual_price_id ?? '') }}">
            </div>

            <div class="f-group">
                <label class="f-label">Max Users <span style="color:var(--text-3);font-weight:400">(blank = unlimited)</span></label>
                <input type="number" name="max_users" class="f-inp"
                       value="{{ old('max_users', $package->max_users ?? '') }}" min="1">
            </div>

            <div class="f-group">
                <label class="f-label">Max Storage GB <span style="color:var(--text-3);font-weight:400">(blank = unlimited)</span></label>
                <input type="number" name="max_storage_gb" class="f-inp"
                       value="{{ old('max_storage_gb', $package->max_storage_gb ?? '') }}" min="1">
            </div>

            <div class="f-group">
                <label class="f-label">Trial Days</label>
                <input type="number" name="trial_days" class="f-inp"
                       value="{{ old('trial_days', $package->trial_days ?? 14) }}" min="0">
            </div>

            <div class="f-group">
                <label class="f-label">Sort Order</label>
                <input type="number" name="sort" class="f-inp"
                       value="{{ old('sort', $package->sort ?? 0) }}" min="0">
            </div>
        </div>

        <div class="f-group">
            <label class="f-label">Features <span style="color:var(--text-3);font-weight:400">(comma-separated keys)</span></label>
            <input type="text" name="features_raw" class="f-inp"
                   placeholder="email,sms,documents,quickbooks,api_access"
                   value="{{ old('features_raw', isset($package) ? implode(',', $package->features ?? []) : '') }}">
            <div style="font-size:12px;color:var(--text-3);margin-top:4px">These keys control feature access in the tenant panel.</div>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px">
            @foreach([
                ['is_active','Active (visible to clients)', true],
                ['is_trial_allowed','Allow Free Trial', true],
                ['is_recommended','Mark as Recommended', false],
                ['is_free','Free Plan', false],
            ] as [$field, $label, $default])
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                <input type="checkbox" name="{{ $field }}" value="1"
                       {{ old($field, ($package->{$field} ?? $default) ? '1' : '') ? 'checked' : '' }}
                       style="accent-color:var(--orange);width:15px;height:15px">
                {{ $label }}
            </label>
            @endforeach
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:14px;border-top:1px solid var(--border)">
            <a href="{{ route('superadmin.packages.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($package) ? 'Update Package' : 'Create Package' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('pkg-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);

    const raw = data.get('features_raw') || '';
    data.delete('features_raw');
    raw.split(',').map(f => f.trim()).filter(Boolean).forEach(f => data.append('features[]', f));

    const r = await fetch(form.action, { method: 'POST', body: data, headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'} });
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
