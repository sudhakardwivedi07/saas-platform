@extends('layouts.superadmin.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3>Edit: {{ $user->name }}</h3>
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary text-xs">
                <span class="material-icons text-sm">arrow_back</span> Back
            </a>
        </div>
        <div class="card-body">
            <form id="editUserForm">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input name="name" value="{{ $user->name }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input name="email" type="email" value="{{ $user->email }}" class="form-control" required>
                    </div>
                    <div class="form-group col-span-2">
                        <label class="form-label">New Password <span class="text-slate-400 font-normal">(leave blank to keep current)</span></label>
                        <input name="password" type="password" class="form-control" minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active"   {{ $user->status==='active'   ? 'selected':'' }}>Active</option>
                            <option value="inactive" {{ $user->status==='inactive' ? 'selected':'' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-control">
                            <option value="">— SuperAdmin —</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ $user->company_id == $c->id ? 'selected':'' }}>{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-control" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected':'' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const body = Object.fromEntries(new FormData(this).entries());
    const res = await apiCall('{{ route("superadmin.users.update", $user->id) }}', 'PUT', body);
    if (res.status === 'success') { showToast(res.message); }
    else { showToast(res.message || 'Error', 'error'); }
    btn.disabled = false;
});
</script>
@endpush
