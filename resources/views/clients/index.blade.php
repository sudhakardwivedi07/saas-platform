@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
div.dataTables_wrapper div.dataTables_info{font-size:12.5px;color:var(--text-3);padding-top:14px}
div.dataTables_wrapper div.dataTables_paginate{padding-top:10px}
div.dataTables_wrapper div.dataTables_paginate .paginate_button{border-radius:7px!important;font-size:13px;border:1.5px solid var(--border)!important;background:#fff!important;color:var(--text-1)!important;padding:5px 11px!important;margin:0 2px}
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover{background:var(--orange)!important;color:#fff!important;border-color:var(--orange)!important}
div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover:not(.current){background:var(--bg)!important;border-color:var(--text-3)!important}
table.dataTable thead th{cursor:pointer}
</style>
@endpush

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>Client Management</h1>
        <p>Manage clients and their companies across your partner portfolio</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-primary" onclick="document.getElementById('modal-add-client').classList.add('open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
    </div>
</div>

{{-- Tab Bar --}}
<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-clients">
        Clients
        <span class="tab-badge">{{ $clients->count() }}</span>
    </button>
    <button class="tab-btn" data-tab="tab-companies">
        Companies
        <span class="tab-badge">{{ $companies->count() }}</span>
    </button>
</div>

{{-- ── Tab: Clients ──────────────────────────────────────── --}}
<div class="tab-panel active" id="tab-clients">
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="client-search" placeholder="Search clients…" autocomplete="off">
                </div>
                <select class="sel" id="client-status-filter" autocomplete="off">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <span class="badge b-gray" id="client-count-badge">{{ $clients->count() }} clients</span>
        </div>
        <div style="overflow-x:auto">
            <table id="clients-table" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>QB Status</th>
                        <th>Last Activity</th>
                        <th style="width:48px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    @php
                        $initials = strtoupper(substr($client->name, 0, 1)) . strtoupper(substr(strstr($client->name, ' ') ?: ' ', 1, 1));
                        $status = $client->status ?? 'active';
                        $co = $client->company;
                        $isQBConnected = $co && $co->quickbooksConnections->where('is_active', true)->isNotEmpty();
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:34px;height:34px;border-radius:9px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;letter-spacing:-.2px">{{ $initials }}</div>
                                <div>
                                    <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $client->name }}</div>
                                    <div style="font-size:11.5px;color:var(--text-3)">{{ $client->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-size:13px;color:var(--text-2)">{{ $co?->company_name ?? '—' }}</span>
                        </td>
                        <td data-search="{{ $status }}">
                            @if($status === 'active')
                                <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                            @elseif($status === 'suspended')
                                <span class="badge b-red"><span class="badge-dot" style="background:var(--red)"></span>Suspended</span>
                            @else
                                <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if($isQBConnected)
                                <span class="badge b-green" style="font-size:11.5px">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                                    Connected
                                </span>
                            @else
                                <span class="badge b-gray" style="font-size:11.5px">—</span>
                            @endif
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2)">{{ $client->created_at->diffForHumans() }}</td>
                        <td>
                            <div class="dm-wrap">
                                <button class="dot-menu" data-menu="dm-cl-{{ $client->id }}" title="Actions">•••</button>
                                <div class="dm-popup" id="dm-cl-{{ $client->id }}">
                                    <a href="{{ route('clients.show', $client) }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View Details
                                    </a>
                                    <a href="{{ route('clients.insights', $client) }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                        View Insights
                                    </a>
                                    <div class="dm-divider"></div>
                                    <button class="dm-item dm-danger" onclick="confirmDelete({{ $client->id }}, '{{ addslashes($client->name) }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                        Delete Client
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:56px 20px">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                            <div style="font-size:14px;font-weight:500;color:var(--text-2)">No clients yet</div>
                            <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Add your first client to get started.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Tab: Companies ────────────────────────────────────── --}}
<div class="tab-panel" id="tab-companies">
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="company-search" placeholder="Search companies…" autocomplete="off">
                </div>
                <select class="sel" id="company-status-filter" autocomplete="off">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="trial">Trial</option>
                </select>
                <select class="sel" id="company-qb-filter" autocomplete="off">
                    <option value="">QB Status</option>
                    <option value="connected">Connected</option>
                    <option value="not connected">Not Connected</option>
                </select>
            </div>
            <a href="{{ route('my-companies.create') }}" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Company
            </a>
        </div>
        <div style="overflow-x:auto">
            <table id="companies-table" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>QB</th>
                        <th>Plan</th>
                        <th>Last Insight</th>
                        <th style="width:48px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    @php
                        $initials = strtoupper(substr($company->company_name, 0, 1)) . strtoupper(substr(strstr($company->company_name, ' ') ?: '  ', 1, 1));
                        $compStatus = $company->status ?? 'active';
                        $planName = $company->clientPackage?->name ?? $company->package?->name ?? '—';
                        $owner = $company->users()->orderBy('id')->first();
                        $isQBConnected = $company->quickbooksConnections->where('is_active', true)->isNotEmpty() ?? false;
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:34px;height:34px;border-radius:9px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">{{ $initials }}</div>
                                <div>
                                    <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $company->company_name }}</div>
                                    @if($company->subdomain)
                                    <div style="font-size:11.5px;color:var(--text-3)">{{ $company->subdomain }}.{{ config('app.domain', 'saas.app') }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px;color:var(--text-2)">{{ $owner?->name ?? '—' }}</td>
                        <td data-search="{{ $compStatus }}">
                            @if($compStatus === 'active')
                                <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                            @elseif($compStatus === 'trial')
                                <span class="badge b-amber"><span class="badge-dot" style="background:var(--amber)"></span>Trial</span>
                            @elseif($compStatus === 'suspended')
                                <span class="badge b-red"><span class="badge-dot" style="background:var(--red)"></span>Suspended</span>
                            @else
                                <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>Inactive</span>
                            @endif
                        </td>
                        <td data-search="{{ $isQBConnected ? 'connected' : 'not connected' }}">
                            @if($isQBConnected)
                                <span class="badge b-green" style="font-size:11.5px">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="10" height="10"><polyline points="20 6 9 17 4 12"/></svg>
                                    Connected
                                </span>
                            @else
                                <span style="color:var(--text-3);font-size:13px">—</span>
                            @endif
                        </td>
                        <td><span style="font-size:12.5px;color:var(--text-2)">{{ $planName }}</span></td>
                        <td style="font-size:12.5px;color:var(--text-2)">{{ $company->last_active_at?->diffForHumans() ?? '—' }}</td>
                        <td>
                            <div class="dm-wrap">
                                <button class="dot-menu" data-menu="dm-co-{{ $company->id }}" title="Actions">•••</button>
                                <div class="dm-popup" id="dm-co-{{ $company->id }}">
                                    <a href="{{ route('clients.show', ['id' => $owner?->id ?? 0]) }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View Dashboard
                                    </a>
                                    <a href="{{ route('quickbooks.index') }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                                        QB Status
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:56px 20px">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                            <div style="font-size:14px;font-weight:500;color:var(--text-2)">No companies yet</div>
                            <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Companies will appear here once added.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Add Client Modal ─────────────────────────────────── --}}
<div class="modal-overlay" id="modal-add-client" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('modal-add-client').classList.remove('open')">&times;</button>
        <div class="modal-title">Add New Client</div>
        <div class="modal-sub">Create a new client account under your portfolio.</div>
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            <div class="mf-field">
                <label class="mf-label">Full Name *</label>
                <input type="text" name="name" class="mf-inp" placeholder="Jane Smith" required>
            </div>
            <div class="mf-field">
                <label class="mf-label">Email Address *</label>
                <input type="email" name="email" class="mf-inp" placeholder="jane@example.com" required>
            </div>
            <div class="mf-field">
                <label class="mf-label">Phone</label>
                <input type="tel" name="phone" class="mf-inp" placeholder="+1 555 000 0000">
            </div>
            <div class="mf-field">
                <label class="mf-label">Password *</label>
                <input type="password" name="password" class="mf-inp" placeholder="Min. 8 characters" required>
            </div>
            <div class="mf-field">
                <label class="mf-label">Company Name</label>
                <input type="text" name="company_name" class="mf-inp" placeholder="Acme Inc.">
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-add-client').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Add Client</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Delete Confirmation Modal ────────────────────────── --}}
<div class="modal-overlay" id="modal-delete-client" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="max-width:400px">
        <button class="modal-close" onclick="document.getElementById('modal-delete-client').classList.remove('open')">&times;</button>
        <div class="modal-title">Delete Client</div>
        <div class="modal-sub" id="delete-msg">Are you sure?</div>
        <form method="POST" id="delete-form">
            @csrf @method('DELETE')
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-delete-client').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Reset filters on load (bfcache fix)
    document.getElementById('client-search').value   = '';
    document.getElementById('client-status-filter').value = '';
    document.getElementById('company-search').value  = '';
    document.getElementById('company-status-filter').value = '';
    document.getElementById('company-qb-filter').value = '';

    var clientTable = $('#clients-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: 'tip',
        columnDefs: [{ orderable: false, targets: [5] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ clients',
            infoEmpty: 'No clients found',
            paginate: { previous: '‹', next: '›' },
            emptyTable: 'No clients yet',
        },
        drawCallback: function() {
            var info = this.api().page.info();
            document.getElementById('client-count-badge').textContent = info.recordsDisplay + ' client' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    document.getElementById('client-search').addEventListener('keyup', function() {
        clientTable.search(this.value).draw();
    });
    document.getElementById('client-status-filter').addEventListener('change', function() {
        clientTable.column(2).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

    var coTable = $('#companies-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: 'tip',
        columnDefs: [{ orderable: false, targets: [6] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ companies',
            infoEmpty: 'No companies found',
            paginate: { previous: '‹', next: '›' },
            emptyTable: 'No companies yet',
        }
    });

    document.getElementById('company-search').addEventListener('keyup', function() {
        coTable.search(this.value).draw();
    });
    document.getElementById('company-status-filter').addEventListener('change', function() {
        coTable.column(2).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
    document.getElementById('company-qb-filter').addEventListener('change', function() {
        coTable.column(3).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
});

function confirmDelete(id, name) {
    document.getElementById('delete-msg').textContent = 'Delete "' + name + '"? This cannot be undone.';
    document.getElementById('delete-form').action = '/clients/' + id;
    document.getElementById('modal-delete-client').classList.add('open');
}
</script>
@endpush
