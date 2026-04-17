@extends('layouts.superadmin.app')
@section('title', isset($package) ? 'Edit Plan' : 'New Plan')
@section('page-title', isset($package) ? 'Edit Plan' : 'New Plan')

@section('content')
<div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px;">
    <div>
        <h1 style="font-size:22px; font-weight:800; color:#111827; margin:0;">
            {{ isset($package) ? 'Edit Plan' : 'Add / Edit Plan' }}
        </h1>
        <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">Define a subscription plan for firms</p>
    </div>
    <a href="{{ route('superadmin.packages.index') }}"
       style="display:inline-flex; align-items:center; gap:6px; background:#f3f4f6; color:#374151; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;">
        ← Back to Plans
    </a>
</div>

<div style="max-width:680px; background:#fff; border-radius:14px; box-shadow:0 2px 16px rgba(0,0,0,.09); padding:32px;">

    {{-- Card header --}}
    <div style="margin-bottom:24px; padding-bottom:18px; border-bottom:1px solid #f0f0f0;">
        <h2 style="font-size:18px; font-weight:700; color:#111827; margin:0 0 2px;">
            {{ isset($package) ? 'Edit Plan' : 'Add / Edit Plan' }}
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
                    Plan Name
                </label>
                <input type="text" name="name"
                       placeholder="e.g. Enterprise"
                       value="{{ old('name', $package->name ?? '') }}"
                       required
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Price / month ($)
                </label>
                <input type="number" step="0.01" name="monthly_price"
                       placeholder="79"
                       value="{{ old('monthly_price', $package->monthly_price ?? '0') }}"
                       required min="0"
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
        </div>

        {{-- Row 2: Max Clients + Insights/Month --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Max Clients
                </label>
                <input type="number" name="max_users"
                       placeholder="100"
                       value="{{ old('max_users', $package->max_users ?? '') }}"
                       min="1"
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Insights / Month
                </label>
                <input type="number" name="max_insights_per_month"
                       placeholder="500"
                       value="{{ old('max_insights_per_month', $package->max_insights_per_month ?? '') }}"
                       min="1"
                       style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; transition:border-color .15s;"
                       onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
        </div>

        {{-- Row 3: Billing Cycle + Status --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Billing Cycle
                </label>
                <div style="position:relative;">
                    <select name="billing_cycle"
                            style="width:100%; padding:10px 36px 10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; appearance:none; background:#fff; cursor:pointer; transition:border-color .15s;"
                            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                        <option value="monthly"  {{ old('billing_cycle', $package->billing_cycle ?? 'monthly') === 'monthly'  ? 'selected' : '' }}>Monthly</option>
                        <option value="annual"   {{ old('billing_cycle', $package->billing_cycle ?? '') === 'annual'   ? 'selected' : '' }}>Annual</option>
                        <option value="both"     {{ old('billing_cycle', $package->billing_cycle ?? '') === 'both'     ? 'selected' : '' }}>Both</option>
                    </select>
                    <span style="position:absolute; right:11px; top:50%; transform:translateY(-50%); pointer-events:none; color:#6b7280;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </span>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                    Status
                </label>
                <div style="position:relative;">
                    <select name="is_active"
                            style="width:100%; padding:10px 36px 10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; appearance:none; background:#fff; cursor:pointer; transition:border-color .15s;"
                            onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">
                        <option value="1" {{ old('is_active', $package->is_active ?? true) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !old('is_active', $package->is_active ?? true) ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <span style="position:absolute; right:11px; top:50%; transform:translateY(-50%); pointer-events:none; color:#6b7280;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- Description (full width) --}}
        <div style="margin-bottom:18px;">
            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Description</label>
            <textarea name="description" rows="2"
                      placeholder="e.g. Perfect for small teams getting started."
                      style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; resize:vertical; font-family:inherit; transition:border-color .15s;"
                      onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">{{ old('description', $package->description ?? '') }}</textarea>
        </div>

        {{-- Features textarea --}}
        <div style="margin-bottom:22px;">
            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                Features <span style="font-weight:400; color:#9ca3af;">(one per line)</span>
            </label>
            <textarea name="features_lines" rows="5"
                      placeholder="QB integration&#10;Custom branding&#10;Priority support"
                      style="width:100%; padding:10px 13px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; resize:vertical; font-family:inherit; transition:border-color .15s; line-height:1.6;"
                      onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e5e7eb'">{{ old('features_lines', isset($package) ? implode("\n", $package->features ?? []) : '') }}</textarea>
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
document.getElementById('pkg-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);

    // Convert features textarea (one per line) to features[] array
    const lines = (data.get('features_lines') || '').split('\n').map(f => f.trim()).filter(Boolean);
    data.delete('features_lines');
    lines.forEach(f => data.append('features[]', f));

    // is_active comes from a select — ensure unchecked checkboxes send 0
    if (!data.has('is_trial_allowed')) data.append('is_trial_allowed', '0');
    if (!data.has('is_recommended'))   data.append('is_recommended', '0');
    if (!data.has('is_free'))          data.append('is_free', '0');

    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const r    = await fetch(form.action, { method: 'POST', body: data, headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'} });
    const json = await r.json();

    if (json.status === 'success') {
        showToast(json.message, 'success');
        setTimeout(() => window.location = '{{ route("superadmin.packages.index") }}', 1000);
    } else {
        showToast(json.message || 'Validation error', 'error');
        btn.disabled = false;
        btn.textContent = 'Save Plan';
    }
});

</script>
@endpush
