@extends('layouts.superadmin.app')
@section('title', isset($package) ? 'Edit Plan' : 'New Plan')
@section('page-title', isset($package) ? 'Edit Plan' : 'New Plan')

@section('content')
<div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px;">
    <div>
        <h1 style="font-size:22px; font-weight:800; color:#111827; margin:0;">
            {{ isset($package) ? 'Edit Plan' : 'Add Plan' }}
        </h1>
        <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">Define a subscription plan for firms</p>
    </div>
    <a href="{{ route('superadmin.packages.index') }}"
       style="display:inline-flex; align-items:center; gap:6px; background:#f3f4f6; color:#374151; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;">
        ← Back to Plans
    </a>
</div>

<div style="background:#fff; border-radius:14px; box-shadow:0 2px 16px rgba(0,0,0,.09); padding:32px;">

    {{-- Card header --}}
    <div style="margin-bottom:24px; padding-bottom:18px; border-bottom:1px solid #f0f0f0;">
        <h2 style="font-size:18px; font-weight:700; color:#111827; margin:0 0 2px;">
            {{ isset($package) ? 'Edit Plan' : 'Add Plan' }}
        </h2>
        <p style="font-size:13px; color:#9ca3af; margin:0;">Define a subscription plan for firms</p>
    </div>

    <form id="pkg-form"
          action="{{ isset($package) ? route('superadmin.packages.update', $package) : route('superadmin.packages.store') }}"
          method="POST">
        @csrf
        @if(isset($package)) @method('PUT') @endif

        {{-- Row 1: Plan Name + Price/month --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Plan Name <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" name="name" id="field-name"
                       placeholder="e.g. Enterprise"
                       value="{{ old('name', $package->name ?? '') }}"
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                <div id="err-name" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
            </div>
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Price / month ($) <span style="color:#ef4444;">*</span>
                </label>
                <input type="number" step="0.01" name="monthly_price" id="field-monthly_price"
                       placeholder="79"
                       value="{{ old('monthly_price', $package->monthly_price ?? '0') }}"
                       min="0"
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                <div id="err-monthly_price" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
            </div>
        </div>

        {{-- Row 2: Max Clients + Insights/Month --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Max Clients <span style="color:#ef4444;">*</span>
                </label>
                <input type="number" name="max_users" id="field-max_users"
                       placeholder="100"
                       value="{{ old('max_users', $package->max_users ?? '') }}"
                       min="1"
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                <div id="err-max_users" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
            </div>
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Insights / Month <span style="color:#ef4444;">*</span>
                </label>
                <input type="number" name="max_insights_per_month" id="field-max_insights_per_month"
                       placeholder="500"
                       value="{{ old('max_insights_per_month', $package->max_insights_per_month ?? '') }}"
                       min="1"
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                <div id="err-max_insights_per_month" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
            </div>
        </div>

        {{-- Row 3: Billing Cycle + Status --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Billing Cycle <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative;">
                    @php $billingVal = old('billing_cycle', $package->billing_cycle ?? ''); @endphp
                    <select name="billing_cycle" id="field-billing_cycle"
                            style="width:100%; padding:10px 36px 10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; appearance:none; background:#fff; cursor:pointer; transition:border-color .15s;"
                            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                        <option value="" {{ $billingVal === '' ? 'selected' : '' }}>-- Select Billing Cycle --</option>
                        <option value="monthly"  {{ $billingVal === 'monthly'  ? 'selected' : '' }}>Monthly</option>
                        <option value="annual"   {{ $billingVal === 'annual'   ? 'selected' : '' }}>Annual</option>
                        <option value="both"     {{ $billingVal === 'both'     ? 'selected' : '' }}>Both</option>
                    </select>
                    <span style="position:absolute; right:11px; top:50%; transform:translateY(-50%); pointer-events:none; color:#6b7280;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </span>
                </div>
                <div id="err-billing_cycle" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
            </div>
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Status <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative;">
                    @php $activeVal = old('is_active', isset($package) ? ($package->is_active ? '1' : '0') : ''); @endphp
                    <select name="is_active" id="field-is_active"
                            style="width:100%; padding:10px 36px 10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; appearance:none; background:#fff; cursor:pointer; transition:border-color .15s;"
                            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                        <option value="" {{ $activeVal === '' ? 'selected' : '' }}>-- Select Status --</option>
                        <option value="1" {{ $activeVal === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $activeVal === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <span style="position:absolute; right:11px; top:50%; transform:translateY(-50%); pointer-events:none; color:#6b7280;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </span>
                </div>
                <div id="err-is_active" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
            </div>
        </div>

        {{-- Description (full width) --}}
        <div style="margin-bottom:18px;">
            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                Description <span style="color:#ef4444;">*</span>
            </label>
            <textarea name="description" id="field-description" rows="2"
                      placeholder="e.g. Perfect for small teams getting started."
                      style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; resize:vertical; font-family:inherit; transition:border-color .15s;"
                      onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">{{ old('description', $package->description ?? '') }}</textarea>
            <div id="err-description" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
        </div>

        {{-- Features textarea --}}
        <div style="margin-bottom:22px;">
            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                Features <span style="font-weight:400; color:#9ca3af;">(one per line)</span> <span style="color:#ef4444;">*</span>
            </label>
            <textarea name="features_lines" id="field-features_lines" rows="5"
                      placeholder="QB integration&#10;Custom branding&#10;Priority support"
                      style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; resize:vertical; font-family:inherit; transition:border-color .15s; line-height:1.6;"
                      onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">{{ old('features_lines', isset($package) ? implode("\n", $package->features ?? []) : '') }}</textarea>
            <div id="err-features_lines" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
        </div>

        {{-- Options --}}
        <div style="display:flex; flex-wrap:wrap; gap:18px; margin-bottom:22px;">
            @foreach([
                ['is_trial_allowed', 'Allow Free Trial',    true],
                ['is_recommended',   'Mark as Recommended', false],
                ['is_free',          'Free Plan',           false],
            ] as [$field, $label, $default])
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                <input type="checkbox" name="{{ $field }}" value="1"
                       {{ old($field, ($package->{$field} ?? $default) ? '1' : '') ? 'checked' : '' }}
                       style="accent-color:#f97316; width:15px; height:15px;">
                {{ $label }}
            </label>
            @endforeach
        </div>

        {{-- Action buttons --}}
        <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:18px; border-top:1px solid #f0f0f0;">
            <a href="{{ route('superadmin.packages.index') }}"
               style="padding:10px 22px; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; color:#374151; background:#f3f4f6; border:1px solid #e5e7eb;">
                Cancel
            </a>
            <button type="submit"
                    style="padding:10px 28px; border-radius:8px; font-size:14px; font-weight:600; background:#f97316; color:#fff; border:none; cursor:pointer; box-shadow:0 2px 6px rgba(249,115,22,.35);">
                Save Plan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function pkgShowError(fieldId, message) {
    var el = document.getElementById('err-' + fieldId);
    var input = document.getElementById('field-' + fieldId);
    if (el) { el.textContent = message; el.style.display = 'block'; }
    if (input) input.style.borderColor = '#ef4444';
}

function pkgClearError(fieldId) {
    var el = document.getElementById('err-' + fieldId);
    var input = document.getElementById('field-' + fieldId);
    if (el) { el.textContent = ''; el.style.display = 'none'; }
    if (input) input.style.borderColor = '#e5e7eb';
}

function pkgClearAllErrors() {
    ['name','monthly_price','max_users','max_insights_per_month','billing_cycle','is_active','description','features_lines'].forEach(pkgClearError);
}

function pkgValidate() {
    pkgClearAllErrors();
    var valid = true;

    // Plan Name: required, min 2, max 100, allowed chars
    var name = document.getElementById('field-name').value.trim();
    if (!name) {
        pkgShowError('name', 'Plan Name is required.');
        valid = false;
    } else if (name.length < 2) {
        pkgShowError('name', 'Plan Name must be at least 2 characters.');
        valid = false;
    } else if (name.length > 100) {
        pkgShowError('name', 'Plan Name cannot exceed 100 characters.');
        valid = false;
    } else if (!/^[a-zA-Z0-9 .&\-_]+$/.test(name)) {
        pkgShowError('name', 'Plan Name contains invalid characters.');
        valid = false;
    }

    // Price / Month: required, numeric, not negative
    var priceStr = document.getElementById('field-monthly_price').value.trim();
    if (priceStr === '') {
        pkgShowError('monthly_price', 'Price per month is required.');
        valid = false;
    } else if (!/^\d+(\.\d+)?$/.test(priceStr)) {
        pkgShowError('monthly_price', 'Please enter a valid price.');
        valid = false;
    } else if (Number(priceStr) < 0) {
        pkgShowError('monthly_price', 'Price cannot be negative.');
        valid = false;
    }

    // Max Clients: required, whole number, > 0
    var maxUsersStr = document.getElementById('field-max_users').value.trim();
    if (maxUsersStr === '') {
        pkgShowError('max_users', 'Max Clients is required.');
        valid = false;
    } else if (!/^\d+$/.test(maxUsersStr)) {
        pkgShowError('max_users', 'Please enter a valid client limit.');
        valid = false;
    } else if (Number(maxUsersStr) <= 0) {
        pkgShowError('max_users', 'Max Clients must be greater than 0.');
        valid = false;
    }

    // Insights / Month: required, whole number, > 0
    var insightsStr = document.getElementById('field-max_insights_per_month').value.trim();
    if (insightsStr === '') {
        pkgShowError('max_insights_per_month', 'Insights per month is required.');
        valid = false;
    } else if (!/^\d+$/.test(insightsStr)) {
        pkgShowError('max_insights_per_month', 'Please enter a valid insights limit.');
        valid = false;
    } else if (Number(insightsStr) <= 0) {
        pkgShowError('max_insights_per_month', 'Insights per month must be greater than 0.');
        valid = false;
    }

    // Billing Cycle: required selection
    var billingCycleVal = document.getElementById('field-billing_cycle').value;
    if (!billingCycleVal) {
        pkgShowError('billing_cycle', 'Please select a billing cycle.');
        valid = false;
    }

    // Status: required selection
    var isActiveVal = document.getElementById('field-is_active').value;
    if (isActiveVal === '') {
        pkgShowError('is_active', 'Please select a status.');
        valid = false;
    }

    // Description: required, min 10, max 500
    var desc = document.getElementById('field-description').value.trim();
    if (!desc) {
        pkgShowError('description', 'Description is required.');
        valid = false;
    } else if (desc.length < 10) {
        pkgShowError('description', 'Description must be at least 10 characters.');
        valid = false;
    } else if (desc.length > 500) {
        pkgShowError('description', 'Description exceeds the allowed limit.');
        valid = false;
    }

    // Features: at least one non-empty line, total max 500 chars
    var featuresRaw = document.getElementById('field-features_lines').value.trim();
    if (!featuresRaw) {
        pkgShowError('features_lines', 'Please add at least one feature.');
        valid = false;
    } else {
        var featureLines = featuresRaw.split('\n').map(function(f) { return f.trim(); }).filter(Boolean);
        if (featureLines.length === 0) {
            pkgShowError('features_lines', 'Please add at least one feature.');
            valid = false;
        } else if (featureLines.some(function(f) { return f.length > 500; })) {
            pkgShowError('features_lines', 'Please enter valid feature details.');
            valid = false;
        }
    }

    if (!valid) {
        showToast('Please fill in all required fields.', 'error');
    }

    return valid;
}

// Clear error on input for text/number/textarea fields
['name','monthly_price','max_users','max_insights_per_month','description','features_lines'].forEach(function(id) {
    var el = document.getElementById('field-' + id);
    if (el) el.addEventListener('input', function() { pkgClearError(id); });
});

// Clear error on change for select fields
['billing_cycle','is_active'].forEach(function(id) {
    var el = document.getElementById('field-' + id);
    if (el) el.addEventListener('change', function() { pkgClearError(id); });
});

document.getElementById('pkg-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!pkgValidate()) return;

    const form = e.target;
    const data = new FormData(form);

    // Convert features textarea (one per line) to features[] array
    const lines = (data.get('features_lines') || '').split('\n').map(f => f.trim()).filter(Boolean);
    data.delete('features_lines');
    lines.forEach(f => data.append('features[]', f));

    // Ensure unchecked checkboxes send 0
    if (!data.has('is_trial_allowed')) data.append('is_trial_allowed', '0');
    if (!data.has('is_recommended'))   data.append('is_recommended', '0');
    if (!data.has('is_free'))          data.append('is_free', '0');

    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const r    = await fetch(form.action, { method: 'POST', body: data, headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'} });
    const json = await r.json();

    if (json.status === 'success') {
        showToast(json.message || 'Plan saved successfully.', 'success');
        setTimeout(() => window.location = '{{ route("superadmin.packages.index") }}', 1000);
    } else if (json.errors) {
        // Laravel validation errors (HTTP 422) — show each error inline on its field
        Object.keys(json.errors).forEach(function(field) {
            var displayField = field.startsWith('features') ? 'features_lines' : field;
            pkgShowError(displayField, json.errors[field][0]);
        });
        showToast('Please fill in all required fields.', 'error');
        btn.disabled = false;
        btn.textContent = 'Save Plan';
    } else {
        showToast(json.message || 'Validation error', 'error');
        btn.disabled = false;
        btn.textContent = 'Save Plan';
    }
});
</script>
@endpush
