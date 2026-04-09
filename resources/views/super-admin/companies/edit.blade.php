@extends('layouts.superadmin.app')
@section('title', 'Edit Company')
@section('page-title', 'Edit: ' . $company->company_name)

@section('content')
<div class="max-w-2xl">
<div class="card">
    <div class="card-header">
        <h3>Edit Company</h3>
        <a href="{{ route('superadmin.companies.show', $company) }}" class="btn btn-secondary text-xs">← Back</a>
    </div>
    <div class="card-body">
        <form id="company-form" method="POST" action="{{ route('superadmin.companies.update', $company) }}">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group col-span-2">
                    <label class="form-label">Company Name *</label>
                    <input type="text" name="company_name" class="form-control"
                           value="{{ old('company_name', $company->company_name) }}" required>
                </div>
                <div class="form-group col-span-2">
                    <label class="form-label">App Name <span class="text-gray-400 font-normal">(white-label display name)</span></label>
                    <input type="text" name="app_name" class="form-control"
                           value="{{ old('app_name', $company->app_name) }}"
                           placeholder="Leave blank to use company name">
                </div>
                <div class="form-group">
                    <label class="form-label">Company Email</label>
                    <input type="email" name="company_email" class="form-control"
                           value="{{ old('company_email', $company->company_email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="company_phone" class="form-control"
                           value="{{ old('company_phone', $company->company_phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control">
                        @foreach(['active','inactive','suspended'] as $s)
                        <option value="{{ $s }}" {{ $company->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Package</label>
                    <select name="package_id" class="form-control">
                        <option value="">— No Package —</option>
                        @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ $company->package_id == $pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }} (${{ number_format($pkg->monthly_price,0) }}/mo)
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons text-lg">save</span> Save Changes
                </button>
                <a href="{{ route('superadmin.companies.show', $company) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
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
