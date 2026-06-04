@extends('layouts.superadmin.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="ph">
    <div>
        <h1>Edit: {{ $user->name }}</h1>
        <p>Update team member details and role</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card">
    <div class="acc-title">User Details</div>
    <form id="editUserForm">
        @csrf @method('PUT')
        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Full Name <span style="color:red">*</span></label>
                <input name="name" value="{{ $user->name }}" class="f-inp">
                <div class="f-error-msg" style="color:red;font-size:0.82em;margin-top:4px"></div>
            </div>
            <div class="f-group">
                <label class="f-label">Email <span style="color:red">*</span></label>
                <input name="email" type="email" value="{{ $user->email }}" class="f-inp">
                <div class="f-error-msg" style="color:red;font-size:0.82em;margin-top:4px"></div>
            </div>
        </div>
        <div class="f-group">
            <label class="f-label">New Password <span style="color:var(--text-3);font-weight:400">(leave blank to keep current)</span></label>
            <input name="password" type="password" class="f-inp" minlength="8">
            <div class="f-error-msg" style="color:red;font-size:0.82em;margin-top:4px"></div>
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
                <label class="f-label">Role <span style="color:red">*</span></label>
                <select name="role" class="f-sel">
                    <option value="">— Select Role —</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected':'' }}>{{ $role->display_name }}</option>
                    @endforeach
                </select>
                <div class="f-error-msg" style="color:red;font-size:0.82em;margin-top:4px"></div>
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
function clearFieldError(field) {
    const grp = field.closest('.f-group');
    if (!grp) return;
    const err = grp.querySelector('.f-error-msg');
    if (err) err.textContent = '';
    field.style.borderColor = '';
}

function showFieldError(field, msg) {
    const grp = field.closest('.f-group');
    if (!grp) return;
    const err = grp.querySelector('.f-error-msg');
    if (err) err.textContent = msg;
    field.style.borderColor = 'red';
}

document.getElementById('editUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Clear previous errors
    this.querySelectorAll('.f-error-msg').forEach(el => el.textContent = '');
    this.querySelectorAll('.f-inp, .f-sel').forEach(el => el.style.borderColor = '');

    const name     = this.querySelector('[name=name]');
    const email    = this.querySelector('[name=email]');
    const password = this.querySelector('[name=password]');
    const role     = this.querySelector('[name=role]');
    let valid = true;

    if (!name.value.trim()) {
        showFieldError(name, 'Full name is required.');
        valid = false;
    }
    if (!email.value.trim()) {
        showFieldError(email, 'Email address is required.');
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        showFieldError(email, 'Please enter a valid email address.');
        valid = false;
    }
    if (password.value && password.value.length < 8) {
        showFieldError(password, 'Password must be at least 8 characters.');
        valid = false;
    }
    if (!role.value) {
        showFieldError(role, 'Please select a role.');
        valid = false;
    }

    if (!valid) return;

    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const body = Object.fromEntries(new FormData(this).entries());
    const res = await apiCall('{{ route("superadmin.users.update", $user->id) }}', 'PUT', body);
    if (res.status === 'success') { showToast(res.message); }
    else { showToast(res.message || 'Error', 'error'); }
    btn.disabled = false;
});

// Clear error on user interaction
['name', 'email', 'password', 'role'].forEach(function(n) {
    const f = document.querySelector('[name=' + n + ']');
    if (f) {
        f.addEventListener('input',  function() { clearFieldError(this); });
        f.addEventListener('change', function() { clearFieldError(this); });
    }
});
</script>
@endpush
