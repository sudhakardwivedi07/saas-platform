@extends('layouts.superadmin.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="ph">
    <div>
        <h1>Edit: {{ $user->name }}</h1>
        <p>Update user details and permissions</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:640px">
    <div class="acc-title">User Details</div>
    <form id="editUserForm">
        @csrf @method('PUT')
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Full Name *</label>
                <input name="name" value="{{ $user->name }}" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Email *</label>
                <input name="email" type="email" value="{{ $user->email }}" class="f-inp" required>
            </div>
        </div>
        <div class="f-group">
            <label class="f-label">New Password <span style="color:var(--text-3);font-weight:400">(leave blank to keep current)</span></label>
            <input name="password" type="password" class="f-inp" minlength="8">
        </div>
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Status</label>
                <select name="status" class="f-sel">
                    <option value="active"   {{ $user->status==='active'   ? 'selected':'' }}>Active</option>
                    <option value="inactive" {{ $user->status==='inactive' ? 'selected':'' }}>Inactive</option>
                </select>
            </div>
            <div class="f-group">
                <label class="f-label">Company</label>
                <select name="company_id" class="f-sel">
                    <option value="">— SuperAdmin —</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ $user->company_id == $c->id ? 'selected':'' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="f-group">
                <label class="f-label">Role *</label>
                <select name="role" class="f-sel" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected':'' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
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
