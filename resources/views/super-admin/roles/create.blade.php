@extends('layouts.superadmin.app')
@section('title', 'Create Role')
@section('page-title', 'Create Role')

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header">
            <h3>New Role</h3>
            <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary text-xs">
                <span class="material-icons text-sm">arrow_back</span> Back
            </a>
        </div>
        <div class="card-body">
            <form id="createRoleForm">
                @csrf
                <div class="form-group mb-5">
                    <label class="form-label">Role Name *</label>
                    <input name="name" class="form-control max-w-sm" placeholder="e.g. manager, accountant" required>
                </div>

                <p class="text-sm font-semibold text-slate-700 mb-3">Assign Permissions</p>
                @foreach($permissions as $module => $perms)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase mb-2">{{ ucfirst(str_replace('_',' ',$module)) }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2 p-2 rounded border border-slate-200 cursor-pointer hover:bg-slate-50 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="rounded">
                            {{ $perm->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
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
