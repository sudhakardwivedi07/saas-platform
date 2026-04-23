@extends('layouts.app')
@section('title', 'QuickBooks')
@section('page-title', 'QuickBooks Integration')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>QuickBooks</h1>
        <p>Manage QuickBooks connections for your client companies</p>
    </div>
    <div class="ph-actions">
        @can('connect_quickbooks')
        @if($qbConfigured ?? false)
        <a href="{{ route('quickbooks.connect') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Connect QuickBooks
        </a>
        @else
        <a href="{{ route('settings.index') }}#quickbooks" class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Configure in Settings
        </a>
        @endif
        @endcan
    </div>
</div>

@if(!($qbConfigured ?? false))
<div class="info-banner" style="margin-bottom:16px;background:var(--amber-light);border-color:var(--amber)">
    <svg viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="info-banner-txt" style="color:var(--text-1)">
        QuickBooks credentials are not configured yet.
        <a href="{{ route('settings.index') }}#quickbooks" style="color:var(--orange);font-weight:500;text-decoration:underline">Go to Settings → QuickBooks</a>
        to enter your Client ID, Client Secret, and Redirect URI before connecting.
    </div>
</div>
@else
<div class="info-banner" style="margin-bottom:16px">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="info-banner-txt">Connect your client companies' QuickBooks accounts to sync customers and invoices automatically.</div>
</div>
@endif

<div class="tc">
    <div class="tc-head">
        <div class="tc-filters">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="qb-search" placeholder="Search connections…" autocomplete="off">
            </div>
            <select class="sel" id="qb-status-filter" autocomplete="off">
                <option value="">All Status</option>
                <option value="connected">Connected</option>
                <option value="disconnected">Disconnected</option>
                <option value="token expired">Token Expired</option>
            </select>
        </div>
        <span class="badge b-gray" id="qb-count-badge">0 connections</span>
    </div>
    <table id="qb-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Connected By</th>
                    <th>QB Status</th>
                    <th>Connected On</th>
                    <th>Last Sync</th>
                    <th style="width:180px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php $connections = $connections ?? collect(); @endphp
                @foreach($connections as $conn)
                @php
                    $qbStatus = $conn->is_active
                        ? ($conn->isAccessTokenExpired() ? 'token expired' : 'connected')
                        : 'disconnected';
                    $statusClass = match($qbStatus) {
                        'connected'     => 'b-green',
                        'token expired' => 'b-amber',
                        default         => 'b-red',
                    };
                    $statusDot = match($qbStatus) {
                        'connected'     => 'var(--green)',
                        'token expired' => 'var(--amber)',
                        default         => 'var(--red)',
                    };
                    $companyName = $conn->company?->company_name ?? $conn->qb_company_name ?? 'Unknown';
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            <div style="width:32px;height:32px;border-radius:8px;background:#2ca01c;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <svg viewBox="0 0 40 40" style="width:18px;fill:#fff"><path d="M20 5C11.73 5 5 11.73 5 20s6.73 15 15 15 15-6.73 15-15S28.27 5 20 5zm-2 22v-3h-3v-4h3v-3l5 5-5 5zm4-10v3h3v4h-3v3l-5-5 5-5z"/></svg>
                            </div>
                            <div>
                                <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $companyName }}</div>
                                @if($conn->realm_id)
                                <div style="font-size:11.5px;color:var(--text-3)">Realm: {{ $conn->realm_id }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:500;color:var(--text-1)">{{ $conn->connectedBy?->name ?? 'Unknown' }}</div>
                        <div style="font-size:11.5px;color:var(--text-3)">{{ $conn->connectedBy?->email ?? '' }}</div>
                    </td>
                    <td data-search="{{ $qbStatus }}">
                        <span class="badge {{ $statusClass }}">
                            <span class="badge-dot" style="background:{{ $statusDot }}"></span>
                            {{ ucwords($qbStatus) }}
                        </span>
                    </td>
                    <td style="font-size:12.5px;color:var(--text-2)">
                        {{ $conn->created_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td style="font-size:12.5px;color:var(--text-2)">
                        {{ $conn->last_synced_at ? $conn->last_synced_at->diffForHumans() : 'Never' }}
                    </td>
                    <td>
                        @can('sync_quickbooks')
                        <div style="display:flex;gap:5px;flex-wrap:wrap">
                            @if($conn->is_active && !$conn->isAccessTokenExpired())
                            <button onclick="syncQB({{ $conn->id }}, 'customers')" class="btn btn-ghost btn-xs" title="Sync Customers">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                Customers
                            </button>
                            <button onclick="syncQB({{ $conn->id }}, 'invoices')" class="btn btn-ghost btn-xs" title="Sync Invoices">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Invoices
                            </button>
                            @endif
                            <button onclick="disconnectQB({{ $conn->id }})" class="btn btn-xs" style="background:rgba(239,68,68,.07);color:var(--red);border:1px solid rgba(239,68,68,.2)" title="Disconnect">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><line x1="2" y1="2" x2="22" y2="22"/><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/></svg>
                                Disconnect
                            </button>
                        </div>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    document.getElementById('qb-search').value       = '';
    document.getElementById('qb-status-filter').value = '';

    var table = $('#qb-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
        order: [[3, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ connections',
            infoEmpty: 'No connections', paginate: { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:48px 20px"><div style="width:52px;height:52px;border-radius:13px;background:#f0fdf4;margin:0 auto 14px;display:flex;align-items:center;justify-content:center"><svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8" width="24" height="24"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg></div><div style="font-size:14px;font-weight:500;color:#6b7280">No QuickBooks connections yet</div><div style="font-size:12.5px;color:#9ca3af;margin-top:4px">Connect your client companies\' QuickBooks accounts to start syncing data.</div></div>',
        },
        drawCallback: function() {
            var info = this.api().page.info();
            document.getElementById('qb-count-badge').textContent = info.recordsDisplay + ' connection' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    document.getElementById('qb-search').addEventListener('keyup', function() { table.search(this.value).draw(); });
    document.getElementById('qb-status-filter').addEventListener('change', function() {
        table.column(2).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
});

async function syncQB(id, type) {
    const json = await fetch(`/quickbooks/${id}/sync`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ sync_type: type })
    }).then(r => r.json());
    showToast(json.message, json.status === 'success' ? 'success' : 'error');
    if (json.status === 'success') setTimeout(() => location.reload(), 1500);
}

async function disconnectQB(id) {
    if (!confirm('Disconnect this QuickBooks account? Synced data will remain but no new syncs will occur.')) return;
    const json = await fetch(`/quickbooks/${id}/disconnect`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
        }
    }).then(r => r.json());
    showToast(json.message, json.status === 'success' ? 'success' : 'error');
    if (json.status === 'success') setTimeout(() => location.reload(), 800);
}
</script>
@endpush
