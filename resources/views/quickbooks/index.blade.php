@extends('layouts.app')
@section('title', 'QuickBooks')
@section('page-title', 'QuickBooks Integration')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>QuickBooks</h1>
    </div>
</div>

{{-- Tabs --}}
<div style="display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:24px">
    <button id="tab-client" onclick="switchQbTab('client')"
        style="padding:9px 22px;font-size:13.5px;font-weight:500;cursor:pointer;border:none;background:none;font-family:inherit;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--text-2);transition:color .15s,border-color .15s">
        Client QuickBook
    </button>
    <button id="tab-my" onclick="switchQbTab('my')"
        style="padding:9px 22px;font-size:13.5px;font-weight:500;cursor:pointer;border:none;background:none;font-family:inherit;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--text-2);transition:color .15s,border-color .15s">
        My QuickBook
    </button>
</div>

{{-- ═══════════════ CLIENT QUICKBOOK TAB ═══════════════ --}}
<div id="pane-client">

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
</div>{{-- /pane-client --}}

{{-- ═══════════════ MY QUICKBOOK TAB ═══════════════ --}}
<div id="pane-my" style="display:none">
@php
    $fConn        = $firmConnection ?? null;
    $fTokenActive = $fConn && !$fConn->isAccessTokenExpired();
    $fDaysLeft    = $fConn && $fConn->refresh_token_expires_at
                        ? max(0, (int) now()->diffInDays($fConn->refresh_token_expires_at, false))
                        : null;
@endphp

<div style="max-width:700px">
    @if($fConn && $fConn->is_active)
    <div class="acc-card">
        <div style="font-size:15px;font-weight:600;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--border)">
            Connection Status
        </div>
        <div style="margin-bottom:20px">
            <span style="display:inline-flex;align-items:center;gap:7px;background:#166534;color:#fff;padding:6px 16px;border-radius:8px;font-size:13.5px;font-weight:600">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                Connected
            </span>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:22px">
            @if($fConn->qb_company_name)
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:3px">Company Name</div>
                <div style="font-size:14.5px;font-weight:500;color:var(--text-1)">{{ $fConn->qb_company_name }}</div>
            </div>
            @endif
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:3px">Realm ID</div>
                <div style="font-size:14px;color:var(--text-1);font-family:monospace">{{ $fConn->realm_id }}</div>
            </div>
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:3px">Connected Since</div>
                <div style="font-size:14px;color:var(--text-1)">{{ $fConn->created_at?->format('M d, Y h:i A') ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:4px">Access Token Expires</div>
                <div style="display:flex;align-items:center;gap:9px;flex-wrap:wrap">
                    <span style="font-size:14px;color:var(--text-1)" id="fqb-token-expires-at">
                        {{ $fConn->access_token_expires_at?->format('M d, Y h:i A') ?? '—' }}
                    </span>
                    @if($fTokenActive)
                    <span style="background:#166534;color:#fff;border-radius:5px;padding:2px 9px;font-size:11.5px;font-weight:600">Active</span>
                    @else
                    <span style="background:#991b1b;color:#fff;border-radius:5px;padding:2px 9px;font-size:11.5px;font-weight:600">Expired</span>
                    @endif
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:3px">Auto-refreshes when needed</div>
            </div>
            @if($fConn->refresh_token_expires_at)
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:4px">Authorization Valid Until</div>
                <div style="display:flex;align-items:center;gap:9px;flex-wrap:wrap">
                    <span style="font-size:14px;color:var(--text-1)">{{ $fConn->refresh_token_expires_at->format('M d, Y h:i A') }}</span>
                    @if($fDaysLeft !== null)
                    <span style="background:{{ $fDaysLeft > 14 ? '#166534' : '#92400e' }};color:#fff;border-radius:5px;padding:2px 9px;font-size:11.5px;font-weight:600">
                        {{ $fDaysLeft }} {{ $fDaysLeft === 1 ? 'day' : 'days' }} remaining
                    </span>
                    @endif
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:3px">Reconnect required after this date</div>
            </div>
            @endif
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button onclick="fqbTestConnection(this)" class="btn btn-secondary" style="min-width:150px;justify-content:center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                Test Connection
            </button>
            <button onclick="fqbRefreshToken(this)" style="min-width:150px;justify-content:center;display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;background:#0e9f8e;color:#fff;font-family:inherit;white-space:nowrap;transition:background .14s" onmouseover="this.style.background='#0b867a'" onmouseout="this.style.background='#0e9f8e'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                Refresh Token
            </button>
            <button onclick="fqbDisconnect(this)" class="btn btn-danger" style="min-width:120px;justify-content:center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="2" y1="2" x2="22" y2="22"/><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                Disconnect
            </button>
        </div>
    </div>
    @else
    <div class="acc-card">
        <div style="font-size:15px;font-weight:600;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--border)">
            Connection Status
        </div>
        <div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding:28px 0 16px">
            <div style="width:64px;height:64px;border-radius:14px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
                <svg viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" width="32" height="32">
                    <line x1="2" y1="2" x2="22" y2="22"/>
                    <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                </svg>
            </div>
            <div style="font-size:14.5px;color:var(--text-2);margin-bottom:22px">QuickBooks is not connected yet.</div>
            @if($qbConfigured ?? false)
            <a href="{{ route('firm-quickbooks.connect') }}"
               style="display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 32px;background:var(--orange);color:#fff;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;transition:background .14s;min-width:240px"
               onmouseover="this.style.background='var(--orange-dark)'" onmouseout="this.style.background='var(--orange)'">
                Connect QuickBooks
            </a>
            @else
            <div style="background:var(--amber-light);border:1px solid var(--amber);border-radius:9px;padding:13px 18px;font-size:13px;color:var(--text-1);max-width:380px">
                QuickBooks integration is not configured. Please contact your administrator to set up the credentials.
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
</div>{{-- /pane-my --}}

@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
// Tab switching
function switchQbTab(tab) {
    document.getElementById('pane-client').style.display = tab === 'client' ? '' : 'none';
    document.getElementById('pane-my').style.display     = tab === 'my'     ? '' : 'none';
    document.getElementById('tab-client').style.borderBottomColor = tab === 'client' ? 'var(--orange)' : 'transparent';
    document.getElementById('tab-my').style.borderBottomColor     = tab === 'my'     ? 'var(--orange)' : 'transparent';
    document.getElementById('tab-client').style.color = tab === 'client' ? 'var(--orange)' : 'var(--text-2)';
    document.getElementById('tab-my').style.color     = tab === 'my'     ? 'var(--orange)' : 'var(--text-2)';
    history.replaceState(null, '', '?tab=' + tab);
}
// Activate tab from URL or default to client
(function() {
    const p = new URLSearchParams(location.search);
    switchQbTab(p.get('tab') === 'my' ? 'my' : 'client');
})();

// Firm QB actions
const FQB_ROUTES = {
    testConnection : '{{ route('firm-quickbooks.test-connection') }}',
    refreshToken   : '{{ route('firm-quickbooks.refresh-token') }}',
    disconnect     : '{{ route('firm-quickbooks.disconnect') }}',
};
const FQB_CSRF = document.querySelector('meta[name=csrf-token]').content;

async function fqbTestConnection(btn) {
    btn.disabled = true;
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:fqb-spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10"/></svg> Testing…';
    const res = await fetch(FQB_ROUTES.testConnection, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': FQB_CSRF }
    }).then(r => r.json()).catch(() => ({ status: 'fail', message: 'Request failed.' }));
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
    btn.innerHTML = orig;
}

async function fqbRefreshToken(btn) {
    btn.disabled = true;
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:fqb-spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10"/></svg> Refreshing…';
    const res = await fetch(FQB_ROUTES.refreshToken, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': FQB_CSRF }
    }).then(r => r.json()).catch(() => ({ status: 'fail', message: 'Request failed.' }));
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    if (res.status === 'success') {
        const el = document.getElementById('fqb-token-expires-at');
        if (el && res.access_token_expires_at) el.textContent = res.access_token_expires_at;
    }
    btn.disabled = false;
    btn.innerHTML = orig;
}

async function fqbDisconnect(btn) {
    let confirmed;
    if (window.Swal) {
        const c = await Swal.fire({
            title: 'Disconnect QuickBooks?', text: 'You can reconnect at any time.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Yes, disconnect', cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', reverseButtons: true,
        });
        confirmed = c.isConfirmed;
    } else {
        confirmed = confirm('Disconnect QuickBooks? You can reconnect at any time.');
    }
    if (!confirmed) return;
    btn.disabled = true;
    btn.textContent = 'Disconnecting…';
    const res = await fetch(FQB_ROUTES.disconnect, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': FQB_CSRF }
    }).then(r => r.json()).catch(() => ({ status: 'fail', message: 'Request failed.' }));
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    if (res.status === 'success') setTimeout(() => location.reload(), 900);
    else { btn.disabled = false; btn.textContent = 'Disconnect'; }
}
</script>
<style>@keyframes fqb-spin { to { transform: rotate(360deg); } }</style>
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
    const json = await fetch('{{ route("quickbooks.sync", ":id") }}'.replace(':id', id), {
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
    let confirmed;
    if (window.Swal) {
        const c = await Swal.fire({
            title: 'Disconnect QuickBooks?',
            text: 'Synced data will remain but no new syncs will occur.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, disconnect',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        });
        confirmed = c.isConfirmed;
    } else {
        confirmed = confirm('Disconnect QuickBooks? Synced data will remain but no new syncs will occur.');
    }
    if (!confirmed) return;
    const json = await fetch('{{ route("quickbooks.disconnect", ":id") }}'.replace(':id', id), {
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
