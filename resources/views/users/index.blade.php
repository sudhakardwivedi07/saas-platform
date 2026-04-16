@extends('layouts.app')

@section('page-title', 'Users')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-1);margin:0 0 2px 0;">Users</h1>
        <p style="font-size:0.875rem;color:var(--text-2);margin:0;">Manage team members and their access levels.</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-primary" onclick="document.getElementById('inviteUserModal').style.display='flex'">
            + Invite User
        </button>
    </div>
</div>

{{-- Users Table --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <div>
            <div class="card-title">Team Members</div>
            <div class="card-sub">All users with access to this account.</div>
        </div>
        <div class="srch-wrap">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="2" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" class="srch-inp" placeholder="Search users..." id="userSearch"
                   oninput="filterUsers(this.value)">
        </div>
    </div>

    @if($users->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;">
            <div style="width:56px;height:56px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p style="font-size:0.9375rem;font-weight:600;color:var(--text-1);margin:0 0 4px 0;">No users yet</p>
            <p style="font-size:0.8125rem;color:var(--text-2);margin:0 0 1rem 0;">Invite your first team member to get started.</p>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('inviteUserModal').style.display='flex'">
                + Invite User
            </button>
        </div>
    @else
        <div class="tc">
            <table id="usersTable">
                <thead>
                    <tr class="tc-head">
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Joined</th>
                        <th style="width:60px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    @php
                        $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                        $role = $user->roles()->first();
                        $roleName = $role ? $role->name : 'viewer';
                        $isActive = ($user->status ?? 'active') === 'active';
                    @endphp
                    <tr class="user-row">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:6px;background:var(--orange);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <span style="font-size:0.6875rem;font-weight:700;color:#fff;letter-spacing:0.03em;">{{ $initials }}</span>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text-1);font-size:0.875rem;">{{ $user->name }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-2);">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge b-blue">{{ ucfirst($roleName) }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $isActive ? 'b-green' : 'b-red' }}">
                                {{ $isActive ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="font-size:0.8125rem;color:var(--text-2);">
                            {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}
                        </td>
                        <td style="font-size:0.8125rem;color:var(--text-2);">
                            {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('M j, Y') : '—' }}
                        </td>
                        <td style="text-align:center;">
                            <div class="dm-wrap">
                                <button class="dot-menu" onclick="toggleDotMenu(this)" aria-label="Actions">
                                    <span></span><span></span><span></span>
                                </button>
                                <div class="dm-popup">
                                    <button class="dm-item" onclick="editUser({{ $user->id }})">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <div class="dm-divider"></div>
                                    <button class="dm-item dm-danger" onclick="removeUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Invite User Modal --}}
<div id="inviteUserModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="width:100%;max-width:480px;">
        <button class="modal-close" onclick="document.getElementById('inviteUserModal').style.display='none'" aria-label="Close">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="modal-title">Invite Team Member</div>
        <div class="modal-sub">They will receive an email invitation to join your account.</div>

        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="mf-field">
                <label class="mf-label" for="invite-name">Full Name</label>
                <input type="text" id="invite-name" name="name" class="mf-inp"
                       placeholder="Jane Smith" required>
            </div>

            <div class="mf-field">
                <label class="mf-label" for="invite-email">Email Address</label>
                <input type="email" id="invite-email" name="email" class="mf-inp"
                       placeholder="jane@company.com" required>
            </div>

            <div class="mf-field">
                <label class="mf-label" for="invite-role">Role</label>
                <select id="invite-role" name="role" class="mf-sel">
                    <option value="admin">Admin — Full access</option>
                    <option value="manager" selected>Manager — Manage users &amp; data</option>
                    <option value="viewer">Viewer — Read only</option>
                </select>
            </div>

            <div class="mf-foot">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('inviteUserModal').style.display='none'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Send Invite</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDotMenu(btn) {
    var popup = btn.nextElementSibling;
    var isOpen = popup.classList.contains('open');
    document.querySelectorAll('.dm-popup.open').forEach(function(p) { p.classList.remove('open'); });
    if (!isOpen) { popup.classList.add('open'); }
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dm-wrap')) {
        document.querySelectorAll('.dm-popup.open').forEach(function(p) { p.classList.remove('open'); });
    }
});
function filterUsers(query) {
    var q = query.toLowerCase();
    document.querySelectorAll('#usersTable .user-row').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
function editUser(id) {
    // TODO: open edit modal or navigate to edit page
    console.log('Edit user', id);
}
function removeUser(id, name) {
    if (confirm('Remove ' + name + ' from your account? This cannot be undone.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/users/' + id;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endsection
