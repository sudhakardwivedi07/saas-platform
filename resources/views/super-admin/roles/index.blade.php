@extends('layouts.superadmin.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="ph">
    <div>
        <h1>Roles &amp; Permissions</h1>
        <p>Manage access control roles</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Role
        </a>
    </div>
</div>

<div class="tc">
    <div class="tc-head">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <div style="font-size:14px;font-weight:600">
                Roles <span id="role-count-badge" style="color:var(--text-3);font-weight:400">({{ $roles->count() }})</span>
            </div>
            <input type="text" id="role-search" placeholder="Search roles…"
                   style="height:32px;border:1.5px solid var(--border);border-radius:7px;padding:0 10px;font-size:13px;outline:none;min-width:200px">
        </div>
    </div>

    <table id="roles-table" style="width:100%;border-collapse:collapse">
        <thead>
            <tr>
                <th style="padding:10px 16px;text-align:left;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border)">Role Name</th>
                <th style="padding:10px 16px;text-align:left;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border)">Permissions</th>
                <th style="padding:10px 16px;text-align:left;font-size:11.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border)">Users</th>
                <th style="padding:10px 16px;border-bottom:1px solid var(--border);width:50px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
            <tr>
                <td style="padding:13px 16px;font-weight:600;font-size:13.5px;border-bottom:1px solid var(--border)">{{ $role->name }}</td>
                <td style="padding:13px 16px;border-bottom:1px solid var(--border)">
                    <span class="badge b-gray">{{ $role->permissions_count }} permissions</span>
                </td>
                <td style="padding:13px 16px;border-bottom:1px solid var(--border)">
                    <span class="badge b-blue">{{ $role->users_count }} users</span>
                </td>
                <td style="padding:13px 16px;border-bottom:1px solid var(--border);text-align:right">
                    <div class="dm-wrap">
                        <button class="dot-menu" type="button"
                                data-menu="role-{{ $role->id }}"
                                style="font-size:20px;letter-spacing:1px;font-weight:700;color:var(--text-3)">···</button>
                        <div class="dm-popup" id="role-{{ $role->id }}">
                            <a href="{{ route('superadmin.roles.edit', $role) }}" class="dm-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Role
                            </a>
                            @unless(in_array($role->name, ['super_admin', 'admin']))
                            <div class="dm-div"></div>
                            <form method="POST" action="{{ route('superadmin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role?')" style="display:contents">
                                @csrf @method('DELETE')
                                <button type="submit" class="dm-item dm-danger">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                    Delete Role
                                </button>
                            </form>
                            @endunless
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;color:var(--text-3);padding:48px 20px;font-size:13px">No roles found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<link rel="stylesheet" href="{{ asset(config('assets.datatables_css')) }}">
<script>
$(document).ready(function () {
    var table = $('#roles-table').DataTable({
        paging:     true,
        pageLength: 15,
        lengthChange: false,
        searching:  true,
        ordering:   true,
        info:       true,
        dom:        '<"dt-tscroll"t><"dt-footer"ip>',
        order:      [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [3] }],
        language: {
            info:      'Showing _START_–_END_ of _TOTAL_ roles',
            infoEmpty: 'No roles found',
            paginate:  { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No roles found.</div>',
        },
        drawCallback: function () {
            var info = this.api().page.info();
            document.getElementById('role-count-badge').textContent =
                '(' + info.recordsDisplay + ')';
        },
    });

    document.getElementById('role-search').addEventListener('keyup', function () {
        table.search(this.value).draw();
    });
});
</script>
@endpush
