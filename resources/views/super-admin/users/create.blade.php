@extends('layouts.superadmin.app')
@section('title', 'Add User')
@section('page-title', 'Add User')

@section('content')
<div class="ph">
    <div>
        <h1>Add User</h1>
        <p>Add a new internal team member</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:640px">
    <div class="acc-title">New Team Member</div>
    <form id="createUserForm">
        @csrf
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Full Name *</label>
                <input name="name" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Email *</label>
                <input name="email" type="email" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Password *</label>
                <input name="password" type="password" class="f-inp" required minlength="8">
            </div>
            <div class="f-group">
                <label class="f-label">Status</label>
                <select name="status" class="f-sel">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="f-group">
                <label class="f-label">Role *</label>
                <select name="role" class="f-sel" required>
                    <option value="">— Select Role —</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create User</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const body = Object.fromEntries(new FormData(this).entries());
    const res = await apiCall('{{ route("superadmin.users.store") }}', 'POST', body);
    if (res.status === 'success') {
        showToast(res.message);
        setTimeout(() => window.location = res.redirect, 1000);
    } else {
        showToast(res.message || 'Error', 'error');
        btn.disabled = false;
    }
});
</script>
@endpush
