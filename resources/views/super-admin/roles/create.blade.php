@extends('layouts.superadmin.app')
@section('title', 'Create Role')
@section('page-title', 'Create Role')

@section('content')
<div class="ph">
    <div>
        <h1>New Role</h1>
        <p>Define a new access control role</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:720px">
    <div class="acc-title">Role Details</div>
    <form id="createRoleForm">
        @csrf
        <div class="f-group" style="max-width:300px;margin-bottom:22px">
            <label class="f-label">Role Name *</label>
            <input name="name" class="f-inp" placeholder="e.g. manager, accountant" required>
        </div>

        <div style="font-size:13px;font-weight:600;margin-bottom:12px">Assign Permissions</div>
        @foreach($permissions as $module => $perms)
        <div style="margin-bottom:18px">
            <div style="font-size:10.5px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:8px">{{ ucfirst(str_replace('_',' ',$module)) }}</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px">
                @foreach($perms as $perm)
                <label style="display:flex;align-items:center;gap:8px;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;font-size:12.5px;transition:border .14s" onmouseover="this.style.borderColor='var(--blue)'" onmouseout="this.style.borderColor='var(--border)'">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" style="accent-color:var(--blue)">
                    {{ $perm->name }}
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;padding-top:16px;border-top:1px solid var(--border)">
            <a href="{{ route('superadmin.roles.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Role</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createRoleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const fd = new FormData(this);
    const body = { name: fd.get('name'), permissions: fd.getAll('permissions[]') };
    const res = await apiCall('{{ route("superadmin.roles.store") }}', 'POST', body);
    if (res.status === 'success') {
        showToast(res.message);
        setTimeout(() => window.location = res.redirect, 1000);
    } else { showToast(res.message || 'Error', 'error'); btn.disabled = false; }
});
</script>
@endpush
