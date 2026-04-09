@extends('layouts.superadmin.app')
@section('title', 'Edit Role')
@section('page-title', 'Edit Role')

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header">
            <h3>Edit Role: {{ $role->name }}</h3>
            <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary text-xs">
                <span class="material-icons text-sm">arrow_back</span> Back
            </a>
        </div>
        <div class="card-body">
            <form id="editRoleForm">
                @csrf @method('PUT')
                <div class="form-group mb-5">
                    <label class="form-label">Role Name *</label>
                    <input name="name" value="{{ $role->name }}" class="form-control max-w-sm" required>
                </div>

                <p class="text-sm font-semibold text-slate-700 mb-3">Permissions</p>
                @php $assigned = $role->permissions->pluck('name')->toArray(); @endphp
                @foreach($permissions as $module => $perms)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase mb-2">{{ ucfirst(str_replace('_',' ',$module)) }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2 p-2 rounded border border-slate-200 cursor-pointer hover:bg-slate-50 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="rounded"
                                {{ in_array($perm->name, $assigned) ? 'checked' : '' }}>
                            {{ $perm->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editRoleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const fd = new FormData(this);
    const body = { name: fd.get('name'), permissions: fd.getAll('permissions[]') };
    const res = await apiCall('{{ route("superadmin.roles.update", $role->id) }}', 'PUT', body);
    if (res.status === 'success') { showToast(res.message); }
    else { showToast(res.message || 'Error', 'error'); }
    btn.disabled = false;
});
</script>
@endpush
