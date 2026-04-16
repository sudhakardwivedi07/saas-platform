@extends('layouts.superadmin.app')
@section('title', 'Create Company')
@section('page-title', 'Create Company')

@section('content')
<div class="ph">
    <div>
        <h1>New Company</h1>
        <p>Onboard a new firm to the platform</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:720px">
    <form id="createForm">
        @csrf

        <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px">Company Details</div>
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Company Name *</label>
                <input name="company_name" class="f-inp" placeholder="Acme Corp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Brand / App Name</label>
                <input name="app_name" class="f-inp" placeholder="Same as company name">
            </div>
            <div class="f-group">
                <label class="f-label">Company Email *</label>
                <input name="company_email" type="email" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Phone</label>
                <input name="company_phone" class="f-inp">
            </div>
            <div class="f-group">
                <label class="f-label">Subdomain *</label>
                <div style="display:flex;align-items:center;gap:0;border:1.5px solid var(--border);border-radius:8px;overflow:hidden">
                    <input name="subdomain" class="f-inp" placeholder="acme" required style="border:none;border-radius:0;flex:1">
                    <span style="padding:0 12px;color:var(--text-3);font-size:12px;background:var(--bg);border-left:1.5px solid var(--border);white-space:nowrap;height:100%;display:flex;align-items:center">.{{ env('ROOT_DOMAIN','yourdomain.com') }}</span>
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">Status</label>
                <select name="status" class="f-sel">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="f-group" style="grid-column:span 2">
                <label class="f-label">Package</label>
                <select name="package_id" class="f-sel">
                    <option value="">— No Package —</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}">{{ $package->name }} (${{ number_format($package->monthly_price,2) }}/mo)</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px">Admin User</div>
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Admin Name *</label>
                <input name="admin_name" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Admin Email *</label>
                <input name="admin_email" type="email" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Admin Password *</label>
                <input name="admin_password" type="password" class="f-inp" required minlength="8">
            </div>
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;padding-top:14px;border-top:1px solid var(--border)">
            <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Company</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true; btn.textContent = 'Creating…';
    const body = Object.fromEntries(new FormData(this).entries());
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
