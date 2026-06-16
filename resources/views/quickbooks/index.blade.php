@extends('layouts.app')
@section('title', 'Connections')
@section('page-title', 'Connections')

@section('content')

@php
    $managedCompanies  = $managedCompanies ?? collect();
    $connectedCount    = $connectedCount ?? 0;
    $notConnectedCount = $notConnectedCount ?? 0;
    $connectUrl        = route('quickbooks.connect');
    $showPartnerColumn = $showPartnerColumn ?? false;
    $adminId           = auth()->id();

    // Returns the real client user for a company:
    // 1) first direct user (company_id = company->id)
    // 2) first manager from user_companies who isn't the logged-in admin
    $clientOf = fn($company) =>
        $company->users->first()
        ?? $company->managers->first(fn($u) => $u->id !== $adminId);
@endphp

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Connections</h1>
        <p style="margin:4px 0 0;font-size:13.5px;color:var(--text-3)">Manage accounting tool connections across all your client companies</p>
    </div>
    <div class="ph-right">
        <button onclick="syncAllConnected(this)" id="sync-all-btn" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
            Sync All
        </button>
    </div>
</div>

{{-- Info Banner hidden --}}

{{-- Stats Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
    <div class="acc-card" style="padding:20px 24px">
        <div style="font-size:11px;font-weight:600;color:var(--text-3);letter-spacing:.06em;text-transform:uppercase;margin-bottom:8px">TOTAL COMPANIES</div>
        <div style="font-size:32px;font-weight:700;color:var(--text-1)">{{ $managedCompanies->count() }}</div>
        <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Across all clients</div>
    </div>
    <div class="acc-card" style="padding:20px 24px;border-color:#10b981">
        <div style="font-size:11px;font-weight:600;color:#10b981;letter-spacing:.06em;text-transform:uppercase;margin-bottom:8px">CONNECTED</div>
        <div style="font-size:32px;font-weight:700;color:#10b981">{{ $connectedCount }}</div>
        <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Ready for insight generation</div>
    </div>
    <div class="acc-card" style="padding:20px 24px;border-color:#ef4444">
        <div style="font-size:11px;font-weight:600;color:#ef4444;letter-spacing:.06em;text-transform:uppercase;margin-bottom:8px">NOT CONNECTED</div>
        <div style="font-size:32px;font-weight:700;color:#ef4444">{{ $notConnectedCount }}</div>
        <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Awaiting connection setup</div>
    </div>
</div>

{{-- Table --}}
<div class="tc">
    <div class="tc-head">
        <div class="tc-filters">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="qb-search" placeholder="Search companies…" autocomplete="off">
            </div>
            <select class="sel" id="qb-status-filter" autocomplete="off">
                <option value="">Select Status</option>
                <option value="connected">Connected</option>
                <option value="not connected">Not Connected</option>
                <option value="auth expired">Auth Expired</option>
            </select>
            <div class="dr-wrap" id="qb-dr">
                <div class="dr-field" onclick="drOpen('qb-dr')">
                    <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span class="dr-text" id="qb-dr-text">Start Date – End Date</span>
                    <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('qb-dr')" title="Clear date filter">&#x2715;</button>
                </div>
                <div class="dr-menu" id="qb-dr-menu">
                    <ul class="dr-list">
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','today',this)">Today</li>
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','last30',this)">Last 30 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','thisMonth',this)">This Month</li>
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','lastMonth',this)">Last Month</li>
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','last90',this)">Last 90 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','last6m',this)">Last 6 Months</li>
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','last1y',this)">Last 1 Year</li>
                        <li class="dr-opt" onclick="drPickPreset('qb-dr','custom',this)">Custom Range</li>
                    </ul>
                    <div class="dr-custom-row" id="qb-dr-custom">
                        <input type="date" class="dr-date-inp" id="qb-dr-from">
                        <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                        <input type="date" class="dr-date-inp" id="qb-dr-to">
                        <button class="dr-apply-btn" onclick="drApplyCustom('qb-dr')">Apply</button>
                    </div>
                </div>
            </div>
            <button onclick="resetFilters()" class="btn btn-danger">Reset</button>
        </div>
    </div>

    <table id="qb-table" style="width:100%;border-collapse:collapse">
        <thead>
            <tr>
                <th>COMPANY</th>
                @if($showPartnerColumn)
                <th>PARTNER</th>
                @endif
                <th>CLIENT</th>
                <th>TOOL</th>
                <th>STATUS</th>
                <th>LAST SYNC</th>
                <th style="width:180px">ACTION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($managedCompanies as $company)
                @if($company->quickbooksConnections->isNotEmpty())
                    {{-- One row per QB connection --}}
                    @foreach($company->quickbooksConnections as $conn)
                    @php
                        $expired    = $conn->isAccessTokenExpired();
                        $qbStatus   = $expired ? 'Auth Expired' : 'Connected';
                        $dotColor   = $expired ? 'var(--amber)' : 'var(--green)';
                        $badgeCls   = $expired ? 'b-amber' : 'b-green';
                        $clientUser = $clientOf($company);
                    @endphp
                    <tr>
                        <td data-order="{{ $conn->created_at?->timestamp ?? $company->created_at?->timestamp ?? 0 }}">
                            <div class="cell-trunc" style="font-weight:500;font-size:13px;color:var(--text-1)" title="{{ $company->company_name }}">{{ $company->company_name }}</div>
                        </td>
                        @if($showPartnerColumn)
                        <td>
                            <div class="cell-trunc" style="font-size:13px;color:var(--text-2)" title="{{ $company->creatorCompany?->company_name ?? '' }}">{{ $company->creatorCompany?->company_name ?? '—' }}</div>
                        </td>
                        @endif
                        <td>
                            <div class="cell-trunc" style="font-size:13px;color:var(--text-1)" title="{{ $clientUser?->name ?? '' }}">{{ $clientUser?->name ?? '—' }}</div>
                        </td>
                        <td>
                            <div style="font-size:13px;color:var(--text-2)">QuickBooks Online</div>
                        </td>
                        <td data-order="{{ $qbStatus }}" data-search="{{ strtolower($qbStatus) }}">
                            <span class="badge {{ $badgeCls }}">
                                <span class="badge-dot" style="background:{{ $dotColor }}"></span>
                                {{ $qbStatus }}
                            </span>
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2)" data-date="{{ $conn->last_synced_at?->format('Y-m-d') ?? '' }}">
                            {{ $conn->last_synced_at?->format(active_date_format()) ?? '—' }}
                        </td>
                        <td>
                            @if(!$expired)
                                <div style="display:flex;align-items:center;gap:8px">
                                    <button onclick="syncQB({{ $conn->id }}, this)"
                                        style="padding:5px 14px;font-size:12.5px;font-weight:500;cursor:pointer;border:1px solid #e5e7eb;background:#fff;color:var(--text-1);border-radius:6px;font-family:inherit;transition:border-color .14s,color .14s"
                                        onmouseover="this.style.borderColor='#9ca3af'" onmouseout="this.style.borderColor='#e5e7eb'">
                                        Sync
                                    </button>
                                    <button onclick="disconnectQB({{ $conn->id }}, this)"
                                        data-company="{{ $company->company_name }}"
                                        data-tool="QuickBooks Online"
                                        style="padding:5px 14px;font-size:12.5px;font-weight:500;cursor:pointer;border:1px solid var(--orange);background:#fff;color:var(--orange);border-radius:6px;font-family:inherit;transition:background .14s,color .14s"
                                        onmouseover="this.style.background='var(--orange)';this.style.color='#fff'"
                                        onmouseout="this.style.background='#fff';this.style.color='var(--orange)'">
                                        Disconnect
                                    </button>
                                </div>
                            @else
                                <a href="{{ $connectUrl }}?company_id={{ $company->id }}"
                                   style="display:inline-flex;align-items:center;gap:5px;padding:6px 16px;background:var(--orange);color:#fff;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;transition:background .14s"
                                   onmouseover="this.style.background='var(--orange-dark)'" onmouseout="this.style.background='var(--orange)'">
                                    Reconnect
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    {{-- No connection yet --}}
                    <tr>
                        <td data-order="{{ $company->created_at?->timestamp ?? 0 }}">
                            <div class="cell-trunc" style="font-weight:500;font-size:13px;color:var(--text-1)" title="{{ $company->company_name }}">{{ $company->company_name }}</div>
                        </td>
                        @if($showPartnerColumn)
                        <td>
                            <div class="cell-trunc" style="font-size:13px;color:var(--text-2)" title="{{ $company->creatorCompany?->company_name ?? '' }}">{{ $company->creatorCompany?->company_name ?? '—' }}</div>
                        </td>
                        @endif
                        <td>
                            <div style="font-size:13px;color:var(--text-1)">{{ $clientOf($company)?->name ?? '—' }}</div>
                        </td>
                        <td><div style="font-size:13px;color:var(--text-2)">—</div></td>
                        <td data-order="Not Connected" data-search="not connected">
                            <span class="badge b-red">
                                <span class="badge-dot" style="background:var(--red)"></span>
                                Not Connected
                            </span>
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2)" data-date="">—</td>
                        <td>
                            @if($qbConfigured ?? false)
                            <a href="{{ $connectUrl }}?company_id={{ $company->id }}"
                               style="display:inline-flex;align-items:center;gap:5px;padding:6px 16px;background:var(--orange);color:#fff;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;transition:background .14s"
                               onmouseover="this.style.background='var(--orange-dark)'" onmouseout="this.style.background='var(--orange)'">
                                Connect
                            </a>
                            @else
                            <span style="font-size:12.5px;color:var(--text-3)">Not configured</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @empty
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
const QB_CSRF = document.querySelector('meta[name=csrf-token]').content;

// Connected connection IDs for Sync All (all non-expired connections across all companies)
const connectedIds = @json(
    $managedCompanies
        ->flatMap(fn($co) => $co->quickbooksConnections->filter(fn($c) => !$c->isAccessTokenExpired())->pluck('id'))
        ->values()
);

$(document).ready(function () {
    document.getElementById('qb-search').value        = '';
    document.getElementById('qb-status-filter').value = '';

    var table = $('#qb-table').DataTable({
        paging: true, pageLength: 10, lengthChange: false,
        searching: true, ordering: true, info: true,
        dom: '<"dt-tscroll"t><"dt-footer"ip>',
        order: [[0, 'desc']],
        columns: @json(array_fill(0, $showPartnerColumn ? 7 : 6, null)),
        columnDefs: [{ orderable: false, targets: [-1] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ companies',
            infoEmpty: 'No companies',
            paginate: { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:48px 20px;color:var(--text-3)">No companies found.</div>',
        },
    });

    document.getElementById('qb-search').addEventListener('keyup', function () {
        table.search(this.value).draw();
    });
    const statusColIndex = {{ $showPartnerColumn ? 4 : 3 }};
    document.getElementById('qb-status-filter').addEventListener('change', function () {
        const val = this.value;
        table.column(statusColIndex).search(val ? '^' + val + '$' : '', true, false, true).draw();
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'qb-table') return true;
        var from = document.getElementById('qb-dr-from') ? document.getElementById('qb-dr-from').value : '';
        var to   = document.getElementById('qb-dr-to')   ? document.getElementById('qb-dr-to').value   : '';
        if (!from && !to) return true;
        var row = table.row(dataIndex).node();
        if (!row) return true;
        var dateVal = row.querySelector('td[data-date]')?.getAttribute('data-date') || '';
        if (!dateVal) return false;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    });
    _drCbs['qb-dr'] = function() { table.draw(); };
});

function resetFilters() {
    document.getElementById('qb-search').value        = '';
    document.getElementById('qb-status-filter').value = '';
    drClear('qb-dr');
    if (window.$.fn && $('#qb-table').DataTable) {
        var t = $('#qb-table').DataTable();
        t.search('').columns().search('').draw();
    }
}

async function syncQB(id, btn) {
    const origText = btn ? btn.textContent : '';
    const companyName = btn?.closest('tr')?.cells[0]?.querySelector('div')?.textContent?.trim() || '';
    if (btn) { btn.disabled = true; btn.textContent = 'Syncing…'; }

    const json = await fetch('{{ route("quickbooks.sync", ":id") }}'.replace(':id', id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': QB_CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ sync_type: 'customers' }),
    }).then(r => r.json()).catch(() => ({ status: 'error', message: 'Request failed.' }));

    if (btn) { btn.disabled = false; btn.textContent = origText; }

    if (json.status === 'success') {
        // Update Last Sync cell with the date returned from the server
        if (btn && json.last_synced_at) {
            const row = btn.closest('tr');
            if (row) {
                const lastSyncCell = row.cells[4];
                if (lastSyncCell) {
                    lastSyncCell.textContent = json.last_synced_at;
                    lastSyncCell.style.color = 'var(--text-2)';
                }
            }
        }

        if (window.Swal) {
            Swal.fire({
                html: `
                    <div style="display:flex;flex-direction:column;align-items:center;padding:8px 0 4px">
                        <div style="width:64px;height:64px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" width="30" height="30"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:10px">Data Synced Successfully</div>
                        ${companyName ? `<div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px">${companyName}</div>` : ''}
                        <div style="font-size:14px;color:#6b7280;line-height:1.6;text-align:center">
                            Financial data has been refreshed from the accounting tool. Dashboard and insights will reflect the latest data.
                        </div>
                    </div>`,
                showConfirmButton: true,
                confirmButtonText: 'Got it',
                confirmButtonColor: 'var(--orange, #f06b25)',
                width: 380,
                padding: '32px 28px 28px',
            });
        } else {
            showToast(json.message, 'success');
        }
    } else {
        showToast(json.message, 'error');
    }
}

async function syncAllConnected(btn) {
    if (!connectedIds.length) { showToast('No connected companies to sync.', 'error'); return; }
    btn.disabled = true;
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:qb-spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10"/></svg> Syncing…';
    for (const id of connectedIds) {
        await fetch('{{ route("quickbooks.sync", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': QB_CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ sync_type: 'customers' }),
        }).catch(() => {});
    }
    btn.disabled = false;
    btn.innerHTML = orig;
    const syncMsg = 'Sync dispatched for ' + connectedIds.length + ' ' + (connectedIds.length !== 1 ? 'companies' : 'company') + '.';
    if (window.Swal) {
        Swal.fire({
            html: `
                <div style="display:flex;flex-direction:column;align-items:center;padding:8px 0 4px">
                    <div style="width:64px;height:64px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" width="30" height="30"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:10px">Data Synced Successfully</div>
                    <div style="font-size:14px;color:#6b7280;line-height:1.6;text-align:center">${syncMsg}</div>
                </div>`,
            showConfirmButton: true,
            confirmButtonText: 'Got it',
            confirmButtonColor: 'var(--orange, #f06b25)',
            width: 380,
            padding: '32px 28px 28px',
        });
    } else {
        showToast(syncMsg, 'success');
    }
}

async function disconnectQB(id, btn) {
    const companyName = btn?.dataset.company || '';

    if (!window.Swal) {
        if (!confirm('Disconnect? You will no longer be able to pull financial data until reconnected.')) return;
    } else {
        const result = await Swal.fire({
            html: `
                <div style="display:flex;flex-direction:column;align-items:center;padding:8px 0 4px">
                    <div style="width:64px;height:64px;border-radius:50%;background:#fff0f0;display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                        <div style="width:36px;height:36px;border-radius:50%;border:2.5px solid #ef4444;display:flex;align-items:center;justify-content:center">
                            <div style="width:4px;height:14px;background:#ef4444;border-radius:2px;margin-top:2px"></div>
                        </div>
                    </div>
                    <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:10px">Disconnect Company</div>
                    ${companyName ? `<div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:10px">${companyName}</div>` : ''}
                    <div style="font-size:14px;color:#6b7280;line-height:1.6;text-align:center">
                        Are you sure you want to disconnect this company?
                        You will no longer be able to pull financial data
                        or generate insights until reconnected.
                    </div>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Disconnect',
            cancelButtonText: 'Cancel',
            confirmButtonColor: 'var(--orange, #f06b25)',
            cancelButtonColor: '#6b7280',
            width: 380,
            padding: '32px 28px 28px',
            reverseButtons: false,
        });
        if (!result.isConfirmed) return;
    }

    const json = await fetch('{{ route("quickbooks.disconnect", ":id") }}'.replace(':id', id), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': QB_CSRF, 'Accept': 'application/json' },
    }).then(r => r.json()).catch(() => ({ status: 'error', message: 'Request failed.' }));

    if (json.status === 'success') {
        if (window.Swal) {
            await Swal.fire({
                html: `
                    <div style="display:flex;flex-direction:column;align-items:center;padding:8px 0 4px">
                        <div style="width:64px;height:64px;border-radius:50%;background:#fff4ee;display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#f06b25" stroke-width="2.5" width="30" height="30"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:10px">Disconnected</div>
                        ${companyName ? `<div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px">${companyName}</div>` : ''}
                        <div style="font-size:14px;color:#6b7280;line-height:1.6;text-align:center">
                            The accounting tool connection has been removed.<br>
                            Reconnect to resume data sync and insight generation.
                        </div>
                    </div>`,
                showConfirmButton: true,
                confirmButtonText: 'Got it',
                confirmButtonColor: 'var(--orange, #f06b25)',
                width: 380,
                padding: '32px 28px 28px',
            });
        }
        location.reload();
    } else {
        showToast(json.message, 'error');
    }
}
</script>
<style>
@keyframes qb-spin { to { transform: rotate(360deg); } }
.swal-confirm-btn { border-radius:10px !important; font-size:14px !important; font-weight:600 !important; padding:12px 32px !important; }
.swal-cancel-btn  { border-radius:10px !important; font-size:14px !important; font-weight:500 !important; padding:12px 32px !important; border:1px solid #e5e7eb !important; color:#374151 !important; }
.swal-cancel-btn:hover { background:#f9fafb !important; }
.swal2-actions { gap:12px !important; }
</style>
@endpush
