@extends('layouts.superadmin.app')
@section('title', 'Edit Company')
@section('page-title', 'Edit: ' . $company->company_name)

@section('content')
<div class="ph">
    <div>
        <h1>Edit Company</h1>
        <p>{{ $company->company_name }}</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.companies.show', $company) }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:640px">
    <div class="acc-title">Company Details</div>
    <form id="company-form" method="POST" action="{{ route('superadmin.companies.update', $company) }}">
        @csrf @method('PUT')

        <div class="f-group">
            <label class="f-label">Company Name *</label>
            <input type="text" name="company_name" class="f-inp"
                   value="{{ old('company_name', $company->company_name) }}" required>
        </div>
        <div class="f-group">
            <label class="f-label">App Name <span style="color:var(--text-3);font-weight:400">(white-label display name)</span></label>
            <input type="text" name="app_name" class="f-inp"
                   value="{{ old('app_name', $company->app_name) }}"
                   placeholder="Leave blank to use company name">
        </div>
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Company Email</label>
                <input type="email" name="company_email" class="f-inp"
                       value="{{ old('company_email', $company->company_email) }}">
            </div>
            <div class="f-group">
                <label class="f-label">Phone</label>
                <input type="text" name="company_phone" class="f-inp"
                       value="{{ old('company_phone', $company->company_phone) }}">
            </div>
            <div class="f-group">
                <label class="f-label">Status *</label>
                <select name="status" class="f-sel">
                    @foreach(['active','inactive','suspended'] as $s)
                    <option value="{{ $s }}" {{ $company->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="f-group">
                <label class="f-label">Package</label>
                <select name="package_id" class="f-sel">
                    <option value="">— No Package —</option>
                    @foreach($packages as $pkg)
                    <option value="{{ $pkg->id }}" {{ $company->package_id == $pkg->id ? 'selected' : '' }}>
                        {{ $pkg->name }} (${{ number_format($pkg->monthly_price,0) }}/mo)
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:flex;gap:8px;padding-top:14px;border-top:1px solid var(--border)">
            <a href="{{ route('superadmin.companies.show', $company) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('company-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const r = await fetch(e.target.action, {method:'POST', body:new FormData(e.target), headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
    const j = await r.json();
    showToast(j.message, j.status==='success'?'success':'error');
    if (j.status==='success') setTimeout(()=>window.history.back(), 1000);
});
</script>
@endpush
