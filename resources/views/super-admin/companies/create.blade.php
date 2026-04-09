@extends('layouts.superadmin.app')
@section('title', 'Create Company')
@section('page-title', 'Create Company')

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header">
            <h3>New Company</h3>
            <a href="{{ route('superadmin.companies.index') }}" class="btn btn-secondary text-xs">
                <span class="material-icons text-sm">arrow_back</span> Back
            </a>
        </div>
        <div class="card-body space-y-6">
            <form id="createForm">
                @csrf
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Company Details</p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Company Name *</label>
                        <input name="company_name" class="form-control" placeholder="Acme Corp" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Brand / App Name</label>
                        <input name="app_name" class="form-control" placeholder="Same as company name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company Email *</label>
                        <input name="company_email" type="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input name="company_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subdomain *</label>
                        <div class="flex items-center gap-1">
                            <input name="subdomain" class="form-control" placeholder="acme" required>
                            <span class="text-slate-400 text-sm whitespace-nowrap">.{{ env('ROOT_DOMAIN','yourdomain.com') }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group col-span-2">
                        <label class="form-label">Package</label>
                        <select name="package_id" class="form-control">
                            <option value="">— No Package —</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }} (${{ number_format($package->monthly_price,2) }}/mo)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4 border-slate-100">
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Admin User</p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Admin Name *</label>
                        <input name="admin_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Email *</label>
                        <input name="admin_email" type="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Password *</label>
                        <input name="admin_password" type="password" class="form-control" required minlength="8">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <a href="{{ route('superadmin.companies.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons text-sm">add_business</span> Create Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true; btn.textContent = 'Creating…';
    const fd = new FormData(this);
    const body = Object.fromEntries(fd.entries());
    const res = await apiCall('{{ route("superadmin.companies.store") }}', 'POST', body);
    if (res.status === 'success') {
        showToast(res.message);
        setTimeout(() => window.location = res.redirect, 1000);
    } else {
        showToast(res.message || 'Error creating company', 'error');
        btn.disabled = false; btn.textContent = 'Create Company';
    }
});
</script>
@endpush
