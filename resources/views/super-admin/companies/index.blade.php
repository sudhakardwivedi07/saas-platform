@extends('layouts.superadmin.app')
@section('title', 'Firms')
@section('page-title', 'Firms')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
/* Override DataTables to match our design system */
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
.firm-color-0  { background: #0C84BC }
.firm-color-1  { background: #10b981 }
.firm-color-2  { background: #F06B21 }
.firm-color-3  { background: #7c3aed }
.firm-color-4  { background: #0d9488 }
.firm-color-5  { background: #e11d48 }
.firm-color-6  { background: #b45309 }
.firm-color-7  { background: #6366f1 }
</style>
@endpush

@section('content')
<div class="ph">
    <div>
        <h1>Firms</h1>
        <p>Manage all partner firms on the platform</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.companies.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Firm
        </a>
    </div>
</div>

<div class="tc">
    {{-- Custom toolbar rendered before DataTables inits --}}
    <div class="tc-head" id="dt-custom-toolbar">
        <div class="dt-toolbar">
            <div class="srch-wrap" style="min-width:220px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="firm-search" class="srch-inp" placeholder="Search firms…">
            </div>
            <select class="sel" id="status-filter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="trial">Trial</option>
                <option value="suspended">Suspended</option>
                <option value="inactive">Inactive</option>
            </select>
            <select class="sel" id="plan-filter">
                <option value="">All Plans</option>
                @foreach($packages as $pkg)
                <option value="{{ $pkg->name }}">{{ $pkg->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="font-size:13px;color:var(--text-3);flex-shrink:0">
            <span id="firm-count">{{ $companies->count() }}</span> firms
        </div>
    </div>

    <div>
        <table id="firms-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Firm Name</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Name &amp; Contact</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Plan</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Plan Price</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Total Clients</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Status</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Last Active</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Onboarded By</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left">Onboarded On</th>
                    <th style="padding:10px 14px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap;text-align:left"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $co)
                @php
                    $colorIdx  = $loop->index % 8;
                    $initials  = strtoupper(substr($co->company_name, 0, 1)) . strtoupper(substr(explode(' ', $co->company_name)[1] ?? $co->company_name, 0, 1));
                    $admin     = $co->adminUser;

                    // Status
                    $statusBadge = match($co->status) {
                        'active'    => ['Active',    'b-green',  '#10b981'],
                        'trial'     => ['Trial',     'b-amber',  '#f59e0b'],
                        'suspended' => ['Suspended', 'b-red',    '#ef4444'],
                        default     => ['Inactive',  'b-gray',   '#94a3b8'],
                    };

                    // Last active
                    $lastActive = $co->last_active_at ?? $co->updated_at;
                    $lastActiveText = $lastActive ? $lastActive->diffForHumans() : '—';
                    $lastActiveDays = $lastActive ? $lastActive->diffInDays() : 999;
                    $lastActiveColor = $lastActiveDays > 7 ? '#ef4444' : ($lastActiveDays > 1 ? '#f59e0b' : 'var(--text-2)');
                @endphp
                <tr class="firm-row" data-status="{{ $co->status }}" data-plan="{{ $co->package?->name }}">
                    {{-- Firm Name --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        <div style="display:flex;align-items:center;gap:11px">
                            <div class="firm-color-{{ $colorIdx }}" style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0">
                                {{ $initials }}
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:13.5px">{{ $co->company_name }}</div>
                                @if($co->subdomain)
                                <div style="font-size:11.5px;color:var(--text-3)">{{ $co->subdomain }}.{{ config('tenancy.root_domain') }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Name & Contact --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        @if($admin)
                        <div style="font-size:13px;font-weight:500">{{ $admin->name }}</div>
                        <div style="font-size:11.5px;color:var(--text-3)">{{ $admin->email }}</div>
                        @if($co->company_phone)
                        <div style="font-size:11.5px;color:var(--text-3)">{{ $co->company_phone }}</div>
                        @endif
                        @else
                        <div style="font-size:13px;font-weight:500">{{ $co->company_email }}</div>
                        @if($co->company_phone)
                        <div style="font-size:11.5px;color:var(--text-3)">{{ $co->company_phone }}</div>
                        @endif
                        @endif
                    </td>

                    {{-- Plan --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        @if($co->package)
                        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:var(--blue3);color:var(--blue)">
                            {{ $co->package->name }}
                        </span>
                        @else
                        <span style="color:var(--text-3);font-size:13px">—</span>
                        @endif
                    </td>

                    {{-- Plan Price --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;font-weight:600;color:#F06B21;font-size:13px">
                        @if($co->package?->monthly_price)
                            ${{ number_format($co->package->monthly_price, 0) }}/mo
                        @else
                            <span style="color:var(--text-3)">—</span>
                        @endif
                    </td>

                    {{-- Total Clients --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:13px;font-weight:600;color:var(--text-1)">
                        {{ $co->users_count }}
                    </td>

                    {{-- Status --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:{{ $statusBadge[2] }}18;color:{{ $statusBadge[2] }}">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusBadge[2] }};flex-shrink:0"></span>
                            {{ $statusBadge[0] }}
                        </span>
                    </td>

                    {{-- Last Active --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:12.5px;color:{{ $lastActiveColor }}">
                        {{ $lastActiveText }}
                    </td>

                    {{-- Onboarded By --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:12.5px;color:var(--text-2)">
                        {{ $co->onboardedBy?->name ?? '—' }}
                    </td>

                    {{-- Onboarded On --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:12.5px;color:var(--text-2)">
                        {{ $co->created_at ? $co->created_at->format('M d, Y') : '—' }}
                    </td>

                    {{-- Actions --}}
                    <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                        <div class="dm-wrap">
                            <button class="dot-menu" type="button" data-menu="dm-{{ $co->id }}"
                                    style="font-size:20px;letter-spacing:1px;font-weight:700;color:var(--text-3)">···</button>
                            <div class="dm-popup" id="dm-{{ $co->id }}">
                                <form method="POST" action="{{ route('superadmin.companies.impersonate', $co) }}" style="display:contents">
                                    @csrf
                                    <button type="submit" class="dm-item dm-hi">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                        Login as Firm
                                    </button>
                                </form>
                                <a href="{{ route('superadmin.companies.show', $co) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Firm
                                </a>
                                <a href="{{ route('superadmin.companies.edit', $co) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit Firm
                                </a>
                                <button class="dm-item" type="button" onclick="openChangePlan({{ $co->id }}, '{{ addslashes($co->company_name) }}', {{ $co->package_id ?? 'null' }})">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                    Change Plan
                                </button>
                                <div class="dm-div"></div>
                                <form method="POST" action="{{ route('superadmin.companies.update', $co) }}" style="display:contents"
                                      onsubmit="return confirm('{{ $co->status === 'suspended' ? 'Re-activate' : 'Disable' }} {{ addslashes($co->company_name) }}?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="company_name" value="{{ $co->company_name }}">
                                    <input type="hidden" name="status" value="{{ $co->status === 'suspended' ? 'active' : 'suspended' }}">
                                    <button type="submit" class="dm-item {{ $co->status === 'suspended' ? '' : 'dm-danger' }}">
                                        @if($co->status === 'suspended')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                        Activate Firm
                                        @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                        Disable Firm
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:48px 24px;color:var(--text-3)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="display:block;margin:0 auto 12px;stroke:var(--text-3)"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <div style="font-size:14px;font-weight:500;margin-bottom:4px">No firms yet</div>
                        <div style="font-size:13px">Add your first partner firm to get started</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Change Plan Modal --}}
<div id="change-plan-modal" class="modal-overlay">
    <div class="modal-box" style="width:420px">
        <button class="modal-close" onclick="document.getElementById('change-plan-modal').classList.remove('open')">×</button>
        <div class="modal-title">Change Plan</div>
        <div id="change-plan-firm" class="modal-sub"></div>
        <form id="change-plan-form" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="company_name" id="cp-company-name">
            <div class="mf-field">
                <label class="mf-label">Select Plan</label>
                <select name="package_id" id="cp-package" class="mf-inp">
                    <option value="">— No Plan —</option>
                    @foreach($packages as $pkg)
                    <option value="{{ $pkg->id }}" data-price="{{ $pkg->monthly_price }}">
                        {{ $pkg->name }} — ${{ number_format($pkg->monthly_price, 0) }}/mo
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="mf-foot">
                <button type="button" onclick="document.getElementById('change-plan-modal').classList.remove('open')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Plan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
@if($companies->count() > 0)
$(document).ready(function() {
    const table = $('#firms-table').DataTable({
        paging:   true,
        pageLength: 15,
        lengthChange: false,
        searching: true,
        ordering: true,
        info:     true,
        dom: '<"dt-scroll"t><"dt-footer"ip>',
        language: {
            info:      'Showing _START_–_END_ of _TOTAL_ firms',
            infoEmpty: 'No firms found',
            paginate:  { previous: '‹', next: '›' },
            emptyTable: 'No firms registered yet',
            zeroRecords: 'No firms match your filters',
        },
        columnDefs: [
            { orderable: false, targets: [1, 6, 7, 8, 9] },  // Name&Contact, LastActive, OnboardedBy, OnboardedOn, Actions
        ],
        drawCallback: function() {
            // Re-count visible rows after DataTables paging
            const info = this.api().page.info();
            document.getElementById('firm-count').textContent = info.recordsDisplay;
        }
    });

    // Custom search box
    document.getElementById('firm-search').addEventListener('keyup', function() {
        table.search(this.value).draw();
    });

    // Status filter
    document.getElementById('status-filter').addEventListener('change', function() {
        table.column(5).search(this.value).draw();
    });

    // Plan filter
    document.getElementById('plan-filter').addEventListener('change', function() {
        table.column(2).search(this.value).draw();
    });
});
@endif

// Fix action-menu clipping inside overflow container — use fixed positioning
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-menu]');
    if (!btn || !btn.closest('#firms-table_wrapper')) return;

    e.stopImmediatePropagation();

    const id = btn.dataset.menu;
    const popup = document.getElementById(id);
    if (!popup) return;

    const isOpen = popup.classList.contains('open');

    document.querySelectorAll('.dm-popup.open').forEach(p => {
        p.classList.remove('open');
        p.style.cssText = '';
    });

    if (!isOpen) {
        const rect = btn.getBoundingClientRect();
        popup.style.position = 'fixed';
        popup.style.top  = (rect.bottom + 4) + 'px';
        popup.style.right = (window.innerWidth - rect.right) + 'px';
        popup.style.left = 'auto';
        popup.classList.add('open');
    }
}, true); // capture phase — fires before the global layout handler

// Close fixed popup on scroll
window.addEventListener('scroll', function() {
    document.querySelectorAll('.dm-popup.open').forEach(p => {
        p.classList.remove('open');
        p.style.cssText = '';
    });
}, true);

function openChangePlan(id, name, currentPlanId) {
    document.getElementById('change-plan-form').action = `/superadmin/companies/${id}`;
    document.getElementById('cp-company-name').value = name;
    document.getElementById('change-plan-firm').textContent = `Firm: ${name}`;
    const sel = document.getElementById('cp-package');
    sel.value = currentPlanId || '';
    document.getElementById('change-plan-modal').classList.add('open');
}
</script>
@endpush
