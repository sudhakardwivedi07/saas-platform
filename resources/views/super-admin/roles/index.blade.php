@extends('layouts.superadmin.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@push('styles')
<link rel="stylesheet" href="{{ asset(config('assets.datatables_bs5_css')) }}">
<style>
div.dataTables_wrapper div.dataTables_length select,
div.dataTables_wrapper div.dataTables_filter input {
    border: 1.5px solid var(--border); border-radius: 8px;
    padding: 5px 10px; font-size: 13px; outline: none;
    font-family: inherit; color: var(--text-1); background: #fff;
}
div.dataTables_wrapper div.dataTables_filter input:focus { border-color: var(--blue); }
div.dataTables_wrapper div.dataTables_info { font-size: 12.5px; color: var(--text-3); padding-top: 14px; }
div.dataTables_wrapper div.dataTables_paginate { padding-top: 10px; }
div.dataTables_wrapper div.dataTables_paginate .paginate_button {
    border-radius: 7px !important; font-size: 13px; border: 1.5px solid var(--border) !important;
    background: #fff !important; color: var(--text-1) !important; padding: 5px 11px !important;
    margin: 0 2px; line-height: 1.4;
}
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover {
    background: #F06B21 !important; color: #fff !important; border-color: #F06B21 !important;
}
div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover:not(.current) {
    background: var(--bg) !important; border-color: var(--text-3) !important;
}
div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled,
div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled:hover {
    color: var(--text-3) !important;
}
table.dataTable thead th { cursor: pointer; }
table.dataTable thead th:after { font-size: 10px; }
.dt-scroll { overflow-x: auto; }
.dt-footer { padding: 10px 18px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.dt-footer .dataTables_info { padding-top: 0 !important; }
.dt-footer .dataTables_paginate { padding-top: 0 !important; }
.dt-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
</style>
@endpush

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
    <div class="tc-head" id="dt-custom-toolbar">
        <div class="dt-toolbar">
            <div class="srch-wrap" style="min-width:220px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="role-search" class="srch-inp" placeholder="Search roles…">
            </div>
        </div>
        <div style="font-size:13px;color:var(--text-3);flex-shrink:0">
            <span id="role-count">{{ $roles->count() }}</span> roles
        </div>
    </div>

    <div>
        <table id="roles-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Role Name</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Permissions</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Users</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;width:50px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        <div style="font-weight:600;font-size:13.5px">{{ $role->name }}</div>
                    </td>
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#f3f4f6;color:#374151">
                            {{ $role->permissions_count }} permissions
                        </span>
                    </td>
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:var(--blue3);color:var(--blue)">
                            {{ $role->users_count }} users
                        </span>
                    </td>
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        <div class="dm-wrap">
                            <button class="dot-menu" type="button" data-menu="role-{{ $role->id }}"
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
                    <td colspan="4" style="text-align:center;padding:48px 24px;color:var(--text-3)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="display:block;margin:0 auto 12px;stroke:var(--text-3)"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <div style="font-size:14px;font-weight:500;margin-bottom:4px">No roles yet</div>
                        <div style="font-size:13px">Create your first role to get started</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
$(document).ready(function () {
    var table = $('#roles-table').DataTable({
        paging:       true,
        pageLength:   15,
        lengthChange: false,
        searching:    true,
        ordering:     true,
        info:         true,
        dom:          '<"dt-scroll"t><"dt-footer"ip>',
        order:        [[0, 'asc']],
        columnDefs:   [{ orderable: false, targets: [3] }],
        language: {
            info:       'Showing _START_–_END_ of _TOTAL_ roles',
            infoEmpty:  'No roles found',
            paginate:   { previous: '‹', next: '›' },
            emptyTable: 'No roles registered yet',
            zeroRecords: 'No roles match your search',
        },
        drawCallback: function () {
            var info = this.api().page.info();
            document.getElementById('role-count').textContent = info.recordsDisplay;
        },
    });

    document.getElementById('role-search').addEventListener('keyup', function () {
        table.search(this.value).draw();
    });
});

// Fixed-position action menu — prevents clipping inside overflow container
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-menu]');
    if (!btn || !btn.closest('#roles-table_wrapper')) return;

    e.stopImmediatePropagation();

    var id    = btn.dataset.menu;
    var popup = document.getElementById(id);
    if (!popup) return;

    var isOpen = popup.classList.contains('open');

    document.querySelectorAll('.dm-popup.open').forEach(function (p) {
        p.classList.remove('open');
        p.style.cssText = '';
    });

    if (!isOpen) {
        var rect = btn.getBoundingClientRect();
        popup.style.position = 'fixed';
        popup.style.top      = (rect.bottom + 4) + 'px';
        popup.style.right    = (window.innerWidth - rect.right) + 'px';
        popup.style.left     = 'auto';
        popup.classList.add('open');
    }
}, true);

window.addEventListener('scroll', function () {
    document.querySelectorAll('.dm-popup.open').forEach(function (p) {
        p.classList.remove('open');
        p.style.cssText = '';
    });
}, true);
</script>
@endpush
