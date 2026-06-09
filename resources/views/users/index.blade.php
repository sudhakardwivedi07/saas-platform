@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'Users')

@push('styles')
<style>
.role-badge-admin   { background:rgba(26,106,168,.1);  color:#1a6aa8; }
.role-badge-manager { background:rgba(240,107,33,.1);  color:#f06b21; }
.role-badge-analyst { background:rgba(124,58,237,.1);  color:#7c3aed; }
.role-badge-viewer  { background:rgba(156,163,175,.15);color:#6b7280; }
.role-badge-client  { background:rgba(156,163,175,.15);color:#6b7280; }
.role-badge              { display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:600; }
.u-perm-text             { font-size:12.5px;color:var(--text-2); }
.u-last-login            { font-size:12.5px;color:var(--text-2); }
.u-last-login.stale      { color:var(--red); }
.au-err {
    font-size: 11.5px; color: var(--red); margin-top: 4px;
    display: flex; align-items: center; gap: 4px;
}
.au-err::before {
    content: '';
    display: inline-block; width: 12px; height: 12px; flex-shrink: 0;
    background: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='2'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cline x1='12' y1='8' x2='12' y2='12'/%3E%3Cline x1='12' y1='16' x2='12.01' y2='16'/%3E%3C/svg%3E") center/contain no-repeat;
}
.mf-inp.au-invalid, .mf-sel.au-invalid { border-color: var(--red) !important; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Users</h1>
        <p>Manage team members in your organisation — assign roles and control access</p>
    </div>
    @if(auth()->user()->can('create_users'))
    <div class="ph-actions">
        <button class="btn btn-primary" onclick="document.getElementById('modal-add-user').classList.add('open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add User
        </button>
    </div>
    @endif
</div>

{{-- Info banner --}}
<div class="info-banner" style="margin-bottom:18px">
    <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" width="15" height="15" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div style="font-size:12.5px;color:var(--blue)">
        Users added here have access to the {{ $appName ?? config('app.name') }}. Assign roles carefully — Admin users have full access. Viewer users have read-only access.
    </div>
</div>


<div class="tc">
    {{-- Filters --}}
    <div class="tc-head">
        <div class="tc-filters">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="user-search" placeholder="Search users…" autocomplete="off">
            </div>
            <select class="sel" id="user-role-filter" autocomplete="off">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                @endforeach
            </select>
            <select class="sel" id="user-status-filter" autocomplete="off">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <div class="dr-wrap" id="user-dr">
                <div class="dr-field" onclick="drOpen('user-dr')">
                    <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span class="dr-text" id="user-dr-text">Start Date To End Date</span>
                    <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('user-dr')" title="Clear date filter">&#x2715;</button>
                </div>
                <div class="dr-menu" id="user-dr-menu">
                    <ul class="dr-list">
                        <li class="dr-opt" onclick="drPickPreset('user-dr','today',this)">Today</li>
                        <li class="dr-opt" onclick="drPickPreset('user-dr','last30',this)">Last 30 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('user-dr','thisMonth',this)">This Month</li>
                        <li class="dr-opt" onclick="drPickPreset('user-dr','lastMonth',this)">Last Month</li>
                        <li class="dr-opt" onclick="drPickPreset('user-dr','last90',this)">Last 90 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('user-dr','last6m',this)">Last 6 Months</li>
                        <li class="dr-opt" onclick="drPickPreset('user-dr','last1y',this)">Last 1 Year</li>
                        <li class="dr-opt" onclick="drPickPreset('user-dr','custom',this)">Custom Range</li>
                    </ul>
                    <div class="dr-custom-row" id="user-dr-custom">
                        <input type="date" class="dr-date-inp" id="user-dr-from">
                        <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                        <input type="date" class="dr-date-inp" id="user-dr-to">
                        <button class="dr-apply-btn" onclick="drApplyCustom('user-dr')">Apply</button>
                    </div>
                </div>
            </div>
            <button class="btn btn-danger" onclick="resetUsersFilters()">Reset</button>
        </div>
        <span class="badge b-gray" id="user-count-badge">{{ $users->count() }} user{{ $users->count() !== 1 ? 's' : '' }}</span>
    </div>

    <table id="users-table" style="width:100%;border-collapse:collapse">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Permissions</th>
                <th>Last Login</th>
                <th>Status</th>
                <th style="width:48px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            @php
                $initials  = strtoupper(substr($user->name,0,1)) . strtoupper(substr(strstr($user->name,' ') ?: ' ',1,1));
                $roleName  = $user->roles->first()?->name ?? 'client';
                $roleLabel = ucwords(str_replace('_',' ',$roleName));
                $status    = $user->status ?? 'active';
                $isActive  = $status === 'active';
                $lastLogin = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at) : null;
                $stale     = $lastLogin && $lastLogin->diffInDays(now()) > 7;

                // Permission summary
                $perms = $user->roles->first()?->permissions->pluck('name') ?? collect();
                $permSummary = match($roleName) {
                    'admin'   => 'Full access',
                    'manager' => 'Clients, Insights, Support',
                    'analyst' => 'Insights, Reports only',
                    'viewer'  => 'Read-only access',
                    'client'  => 'Read-only access',
                    default   => $perms->isEmpty() ? '—' : ucwords(str_replace('_',' ',$perms->first())) . ($perms->count() > 1 ? ', +'.($perms->count()-1).' more' : ''),
                };

                // Avatar colour per role
                $avatarColor = match($roleName) {
                    'admin'   => 'var(--blue)',
                    'manager' => 'var(--orange)',
                    'analyst' => 'var(--purple)',
                    default   => '#64748b',
                };
            @endphp
            <tr data-user-id="{{ $user->id }}">
                <td data-date="{{ $user->created_at?->format('Y-m-d') ?? '' }}">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:34px;height:34px;border-radius:9px;background:{{ $avatarColor }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">{{ $initials }}</div>
                        <div>
                            <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $user->name }}</div>
                            <div style="font-size:11.5px;color:var(--text-3)">
                                {{ $user->job_title ?? ($user->id === auth()->id() ? 'Owner' : '') }}
                            </div>
                        </div>
                    </div>
                </td>
                <td style="font-size:13px;color:var(--text-2)">{{ $user->email }}</td>
                <td>
                    <span class="role-badge role-badge-{{ $roleName }}">{{ $roleLabel }}</span>
                </td>
                <td class="u-perm-text">{{ $permSummary }}</td>
                <td>
                    @if($lastLogin)
                        <span class="u-last-login {{ $stale ? 'stale' : '' }}">{{ $lastLogin->diffForHumans() }}</span>
                    @else
                        <span class="u-last-login" style="color:var(--text-3)">Never</span>
                    @endif
                </td>
                <td>
                    <span class="user-status-badge" style="font-size:13px;font-weight:500;color:{{ $isActive ? 'var(--green)' : 'var(--red)' }}">
                        {{ $isActive ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="dm-wrap">
                        <button class="dot-menu" data-menu="dm-u-{{ $user->id }}" title="Actions">•••</button>
                        <div class="dm-popup" id="dm-u-{{ $user->id }}">
                            @if(auth()->user()->can('edit_users'))
                            <a href="javascript:void(0)" class="dm-item" onclick="openEditUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $roleName }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit User
                            </a>
                            <div class="dm-divider"></div>
                            @if($isActive)
                            <a href="javascript:void(0)" class="dm-item dm-danger toggle-status-link" onclick="toggleUserStatus({{ $user->id }}, '{{ $status }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                Disable User
                            </a>
                            @else
                            <a href="javascript:void(0)" class="dm-item toggle-status-link" style="color:var(--green)" onclick="toggleUserStatus({{ $user->id }}, '{{ $status }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
                                Enable User
                            </a>
                            @endif
                            @endif
                            @if(auth()->user()->can('delete_users') && $user->id !== auth()->id())
                            <div class="dm-divider"></div>
                            <a href="javascript:void(0)" class="dm-item dm-danger" onclick="removeUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                Remove User
                            </a>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ── Add User Modal ────────────────────────────────────── --}}
<div class="modal-overlay {{ $errors->any() ? 'open' : '' }}" id="modal-add-user" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:500px">
        <button class="modal-close" onclick="document.getElementById('modal-add-user').classList.remove('open')">&times;</button>
        <div class="modal-title">Add User</div>
        <div class="modal-sub">Invite a team member to access the {{ $appName ?? config('app.name') }}</div>

        @if($errors->any())
        <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 13px;margin-bottom:14px">
            @foreach($errors->all() as $error)
            <div style="font-size:12.5px;color:var(--red)">{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}" id="add-user-form" novalidate>
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="mf-field" style="margin:0 0 4px">
                    <label class="mf-label">Full Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="au-name" class="mf-inp" placeholder="e.g. Priya Sharma" value="{{ old('name') }}" maxlength="100">
                    <div id="err-au-name" class="au-err" style="display:none"></div>
                </div>
                <div class="mf-field" style="margin:0 0 4px">
                    <label class="mf-label">Email Address <span style="color:var(--red)">*</span></label>
                    <input type="text" name="email" id="au-email" class="mf-inp" placeholder="priya@rightfocuscfo.com" value="{{ old('email') }}" maxlength="100">
                    <div id="err-au-email" class="au-err" style="display:none"></div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="mf-field" style="margin:0 0 4px">
                    <label class="mf-label">Role <span style="color:var(--red)">*</span></label>
                    <select name="role" id="au-role" class="mf-sel" style="height:40px">
                        <option value="">e.g. Manager, Analyst, Viewer</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                            {{ $role->display_name }}
                        </option>
                        @endforeach
                    </select>
                    <div id="err-au-role" class="au-err" style="display:none"></div>
                </div>
                <div class="mf-field" style="margin:0 0 4px">
                    <label class="mf-label">Job Title</label>
                    <input type="text" name="job_title" class="mf-inp" placeholder="e.g. Financial Analyst" value="{{ old('job_title') }}">
                </div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Password <span style="color:var(--red)">*</span></label>
                <div style="position:relative;display:flex;align-items:center">
                    <input type="password" name="password" id="add-pwd" class="mf-inp" placeholder="Min. 8 characters" style="padding-right:88px" maxlength="20">
                    <button type="button" title="Show/hide" onclick="togglePwd('add-pwd','add-eye')" style="position:absolute;right:44px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--text-3);display:flex">
                        <svg id="add-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button type="button" title="Generate password" onclick="genPwd('add-pwd','add-eye')" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--blue);display:flex">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                    </button>
                </div>
                <div id="err-au-pwd" class="au-err" style="display:none"></div>
                <div style="font-size:11px;color:var(--text-3);margin-top:4px">Click the refresh icon to auto-generate a secure password.</div>
            </div>
            <div style="background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:12.5px;color:#3b82f6;line-height:1.5">
                An invitation email will be sent to the user's email address with their login credentials.
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-add-user').classList.remove('open')">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="validateAndSubmitAddUser()">Send Invite</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Edit User Modal ───────────────────────────────────── --}}
<div class="modal-overlay" id="modal-edit-user" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:480px">
        <button class="modal-close" onclick="document.getElementById('modal-edit-user').classList.remove('open')">&times;</button>
        <div class="modal-title">Edit User</div>
        <div class="modal-sub">Update name, email, role or reset password</div>

        <form method="POST" id="form-edit-user">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="mf-field" style="margin:0 0 14px">
                    <label class="mf-label">Full Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="edit-name" class="mf-inp" required>
                </div>
                <div class="mf-field" style="margin:0 0 14px">
                    <label class="mf-label">Email Address <span style="color:var(--red)">*</span></label>
                    <input type="email" name="email" id="edit-email" class="mf-inp" required>
                </div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Role <span style="color:var(--red)">*</span></label>
                <select name="role" id="edit-role" class="mf-sel" style="height:40px" required>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mf-field">
                <label class="mf-label">New Password <span style="font-size:11px;color:var(--text-3)">(leave blank to keep current)</span></label>
                <div style="position:relative;display:flex;align-items:center">
                    <input type="password" name="password" id="edit-pwd" class="mf-inp" placeholder="Leave blank to keep current" style="padding-right:88px">
                    <button type="button" title="Show/hide" onclick="togglePwd('edit-pwd','edit-eye')" style="position:absolute;right:44px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--text-3);display:flex">
                        <svg id="edit-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button type="button" title="Generate password" onclick="genPwd('edit-pwd','edit-eye')" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--blue);display:flex">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                    </button>
                </div>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-edit-user').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete hidden form --}}
<form id="form-remove-user" method="POST" style="display:none">@csrf @method('DELETE')</form>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
$(document).ready(function() {
    window.usersTable = $('#users-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true,
        dom: '<"dt-tscroll"t><"dt-footer"ip>',
        columnDefs: [{ orderable: false, targets: [3, 6] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ users',
            infoEmpty: 'No users found',
            paginate: { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No users yet</div>',
        },
        drawCallback: function() {
            var info = this.api().page.info();
            document.getElementById('user-count-badge').textContent = info.recordsDisplay + ' user' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    document.getElementById('user-search').addEventListener('keyup', function() {
        usersTable.search(this.value).draw();
    });
    document.getElementById('user-role-filter').addEventListener('change', function() {
        usersTable.column(2).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
    document.getElementById('user-status-filter').addEventListener('change', function() {
        usersTable.column(5).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'users-table') return true;
        var from = document.getElementById('user-dr-from') ? document.getElementById('user-dr-from').value : '';
        var to   = document.getElementById('user-dr-to')   ? document.getElementById('user-dr-to').value   : '';
        if (!from && !to) return true;
        var row = usersTable.row(dataIndex).node();
        if (!row) return true;
        var dateVal = row.querySelector('td[data-date]')?.getAttribute('data-date') || '';
        if (!dateVal) return true;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    });
    _drCbs['user-dr'] = function() { usersTable.draw(); };
});

function togglePwd(inputId, eyeId) {
    var inp = document.getElementById(inputId);
    var eye = document.getElementById(eyeId);
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        inp.type = 'password';
        eye.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
function genPwd(inputId, eyeId) {
    var upper   = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    var lower   = 'abcdefghjkmnpqrstuvwxyz';
    var digits  = '23456789';
    var special = '!@#$%';
    var all     = upper + lower + digits + special;
    var arr = [
        upper[Math.floor(Math.random() * upper.length)],
        lower[Math.floor(Math.random() * lower.length)],
        digits[Math.floor(Math.random() * digits.length)],
        special[Math.floor(Math.random() * special.length)],
    ];
    for (var i = arr.length; i < 12; i++) arr.push(all[Math.floor(Math.random() * all.length)]);
    for (var j = arr.length - 1; j > 0; j--) {
        var k = Math.floor(Math.random() * (j + 1));
        var tmp = arr[j]; arr[j] = arr[k]; arr[k] = tmp;
    }
    var inp = document.getElementById(inputId);
    inp.type = 'text';
    inp.value = arr.join('');
    var eye = document.getElementById(eyeId);
    if (eye) eye.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
}

function validateAndSubmitAddUser() {
    var ok = true;
    var emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function auErr(id, msg) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = msg || '';
        el.style.display = msg ? 'flex' : 'none';
    }
    function auMark(id, invalid) {
        var el = document.getElementById(id);
        if (!el) return;
        if (invalid) el.classList.add('au-invalid');
        else el.classList.remove('au-invalid');
    }

    // Full Name
    var nameEl = document.getElementById('au-name');
    var nameVal = nameEl ? nameEl.value.trim() : '';
    if (!nameVal) {
        auErr('err-au-name', 'Full name is required.'); auMark('au-name', true); ok = false;
    } else if (nameVal.length < 2) {
        auErr('err-au-name', 'Minimum 2 characters required.'); auMark('au-name', true); ok = false;
    } else if (!/^[a-zA-Z\s]+$/.test(nameVal)) {
        auErr('err-au-name', 'Name must contain only letters and spaces.'); auMark('au-name', true); ok = false;
    } else {
        auErr('err-au-name', ''); auMark('au-name', false);
    }

    // Email
    var emailEl = document.getElementById('au-email');
    var emailVal = emailEl ? emailEl.value.trim() : '';
    if (!emailVal) {
        auErr('err-au-email', 'Email address is required.'); auMark('au-email', true); ok = false;
    } else if (!emailRx.test(emailVal)) {
        auErr('err-au-email', 'Enter a valid email address.'); auMark('au-email', true); ok = false;
    } else {
        auErr('err-au-email', ''); auMark('au-email', false);
    }

    // Role
    var roleEl = document.getElementById('au-role');
    if (!roleEl || !roleEl.value) {
        auErr('err-au-role', 'Please select a role.'); auMark('au-role', true); ok = false;
    } else {
        auErr('err-au-role', ''); auMark('au-role', false);
    }

    // Password
    var pwdEl = document.getElementById('add-pwd');
    var pwdVal = pwdEl ? pwdEl.value : '';
    if (!pwdVal) {
        auErr('err-au-pwd', 'Password is required.'); auMark('add-pwd', true); ok = false;
    } else if (pwdVal.length < 8) {
        auErr('err-au-pwd', 'Password must be at least 8 characters.'); auMark('add-pwd', true); ok = false;
    } else {
        auErr('err-au-pwd', ''); auMark('add-pwd', false);
    }

    if (ok) {
        document.getElementById('add-user-form').submit();
    }
}

// Real-time: restrict full name to letters + spaces
(function() {
    var el = document.getElementById('au-name');
    if (!el) return;
    el.addEventListener('input', function() {
        var pos = this.selectionStart;
        var cleaned = this.value.replace(/[^a-zA-Z\s]/g, '');
        if (cleaned !== this.value) {
            this.value = cleaned;
            this.setSelectionRange(Math.max(0, pos - 1), Math.max(0, pos - 1));
        }
    });
})();

function openEditUser(id, name, email, role) {
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-role').value  = role;
    document.getElementById('edit-pwd').value   = '';
    document.getElementById('form-edit-user').action = '{{ route("users.update", ":id") }}'.replace(':id', id);
    document.getElementById('modal-edit-user').classList.add('open');
}

function toggleUserStatus(id, currentStatus) {
    var isActive = currentStatus === 'active';
    Swal.fire({
        title: isActive ? 'Disable User?' : 'Enable User?',
        text: isActive ? 'This user will lose access to the platform.' : 'This user will regain access to the platform.',
        icon: isActive ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: isActive ? 'Yes, Disable' : 'Yes, Enable',
        cancelButtonText: 'Cancel',
        confirmButtonColor: isActive ? '#ef4444' : '#10b981',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch('{{ route("users.toggle", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ _method: 'POST' }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // Show toast
            SwalToast.fire({
                icon: data.status === 'success' ? 'success' : 'warning',
                title: data.message,
            });

            if (data.status !== 'success') return;

            // Update the DataTable row without reload
            var row = document.querySelector('tr[data-user-id="' + id + '"]');
            if (!row) return;

            var newStatus   = data.newStatus;
            var isNowActive = newStatus === 'active';

            // Update status badge
            var statusCell = row.querySelector('.user-status-badge');
            if (statusCell) {
                statusCell.textContent = isNowActive ? 'Active' : 'Inactive';
                statusCell.style.color = isNowActive ? 'var(--green)' : 'var(--red)';
            }

            // Swap the dot-menu action link
            var dmPopup = row.querySelector('.dm-popup');
            if (dmPopup) {
                var toggleLink = dmPopup.querySelector('.toggle-status-link');
                if (toggleLink) {
                    toggleLink.setAttribute('onclick', 'toggleUserStatus(' + id + ', \'' + newStatus + '\')');
                    if (isNowActive) {
                        toggleLink.classList.add('dm-danger');
                        toggleLink.removeAttribute('style');
                        toggleLink.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg> Disable User';
                    } else {
                        toggleLink.classList.remove('dm-danger');
                        toggleLink.setAttribute('style', 'color:var(--green)');
                        toggleLink.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg> Enable User';
                    }
                }
            }

            // Redraw DataTable to apply filter state
            if (typeof usersTable !== 'undefined') {
                usersTable.draw(false);
            }
        })
        .catch(function() {
            SwalToast.fire({ icon: 'error', title: 'Something went wrong. Please try again.' });
        });
    });
}

function removeUser(id, name) {
    Swal.fire({
        title: 'Remove User?',
        text: '"' + name + '" will be permanently removed. This cannot be undone.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(function(result) {
        if (!result.isConfirmed) return;
        var form = document.getElementById('form-remove-user');
        form.action = '{{ route("users.destroy", ":id") }}'.replace(':id', id);
        form.submit();
    });
}

function resetUsersFilters() {
    document.getElementById('user-search').value        = '';
    document.getElementById('user-role-filter').value   = '';
    document.getElementById('user-status-filter').value = '';
    drClear('user-dr');
    if (window.$ && $('#users-table').length) {
        $('#users-table').DataTable().search('').columns().search('').draw();
    }
}
</script>
@endpush
