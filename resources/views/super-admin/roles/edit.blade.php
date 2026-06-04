@extends('layouts.superadmin.app')
@section('title', 'Edit Role')
@section('page-title', 'Edit Role')

@section('content')
<div class="ph">
    <div>
        <h1>Edit Role: {{ $role->display_name }}</h1>
        <p>Update permissions for this role</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:720px">
    <div class="acc-title">Role Details</div>
    <form id="editRoleForm">
        @csrf @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:620px;margin-bottom:22px">
            <div class="f-group">
                <label class="f-label">Role Name *</label>
                <input name="name" value="{{ $role->name }}" class="f-inp" required>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Internal identifier — used for permission logic.</div>
            </div>
            <div class="f-group">
                <label class="f-label">Alias <span style="font-weight:400;color:var(--text-3)">(Display Name)</span></label>
                <input name="alias" value="{{ $role->alias }}" class="f-inp" placeholder="e.g. Account Manager">
                <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Shown in the UI instead of the role name. Leave blank to use the role name.</div>
            </div>
        </div>

        <div style="font-size:13px;font-weight:600;margin-bottom:12px">Permissions</div>
        @php $assigned = $role->permissions->pluck('name')->toArray(); @endphp
        @foreach($permissions as $module => $perms)
        <div style="margin-bottom:18px">
            <div style="font-size:10.5px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:8px">{{ ucfirst(str_replace('_',' ',$module)) }}</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px">
                @foreach($perms as $perm)
                <label style="display:flex;align-items:center;gap:8px;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;font-size:12.5px;transition:border .14s" onmouseover="this.style.borderColor='var(--blue)'" onmouseout="this.style.borderColor='var(--border)'">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" style="accent-color:var(--blue)"
                        {{ in_array($perm->name, $assigned) ? 'checked' : '' }}>
                    {{ $perm->name }}
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;padding-top:16px;border-top:1px solid var(--border)">
            <a href="{{ route('superadmin.roles.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editRoleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const fd = new FormData(this);
    const body = { name: fd.get('name'), alias: fd.get('alias') || null, permissions: fd.getAll('permissions[]') };
    const res = await apiCall('{{ route("superadmin.roles.update", $role->id) }}', 'PUT', body);
    if (res.status === 'success') { showToast(res.message); }
    else { showToast(res.message || 'Error', 'error'); }
    btn.disabled = false;
});
</script>
@endpush
