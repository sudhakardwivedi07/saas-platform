@extends('layouts.app')
@section('title','QuickBooks')
@section('page-title','QuickBooks Integration')

@section('content')
<div class="ph">
    <div class="ph-left">
        <h1>QuickBooks</h1>
        <p>Connect your QuickBooks accounts to sync customers and invoices</p>
    </div>
    <div class="ph-actions">
        @can('connect_quickbooks')
        <a href="{{ route('quickbooks.connect') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
            Connect QuickBooks
        </a>
        @endcan
    </div>
</div>

@if(session('success'))
<div class="ca-alert ca-alert-success">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="ca-alert ca-alert-error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

@forelse($connections as $conn)
<div class="card">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="width:44px;height:44px;border-radius:11px;background:#2ca01c;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg viewBox="0 0 40 40" style="width:26px;fill:#fff"><path d="M20 5C11.73 5 5 11.73 5 20s6.73 15 15 15 15-6.73 15-15S28.27 5 20 5zm-2 22v-3h-3v-4h3v-3l5 5-5 5zm4-10v3h3v4h-3v3l-5-5 5-5z"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:600;margin-bottom:2px">{{ $conn->qb_company_name ?? 'QuickBooks Account' }}</div>
                <div style="font-size:12px;color:var(--text-2)">Realm ID: <code style="font-size:11px;background:var(--bg);padding:1px 5px;border-radius:4px">{{ $conn->realm_id }}</code></div>
                <div style="font-size:12px;color:var(--text-3);margin-top:2px">Connected by {{ $conn->connectedBy?->name }} · {{ $conn->created_at->format('M d, Y') }}</div>
                <div style="display:flex;align-items:center;gap:8px;margin-top:8px;flex-wrap:wrap">
                    <span class="badge {{ $conn->is_active ? 'b-green' : 'b-red' }}">
                        <span class="badge-dot" style="background:{{ $conn->is_active ? 'var(--green)' : 'var(--red)' }}"></span>
                        {{ $conn->is_active ? 'Active' : 'Disconnected' }}
                    </span>
                    @if($conn->isAccessTokenExpired())
                    <span class="badge b-amber">Token Expired</span>
                    @endif
                    <span style="font-size:12px;color:var(--text-3)">
                        {{ $conn->last_synced_at ? 'Last sync: '.$conn->last_synced_at->diffForHumans() : 'Never synced' }}
                    </span>
                </div>
            </div>
        </div>

        @can('sync_quickbooks')
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button onclick="syncQB({{ $conn->id }}, 'customers')" class="btn btn-ghost btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Sync Customers
            </button>
            <button onclick="syncQB({{ $conn->id }}, 'invoices')" class="btn btn-ghost btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Sync Invoices
            </button>
            <button onclick="disconnectQB({{ $conn->id }})" class="btn btn-danger btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                Disconnect
            </button>
        </div>
        @endcan
    </div>

    @if($conn->syncLogs->count() > 0)
    <div style="margin-top:16px;border-top:1px solid var(--border);padding-top:14px">
        <div style="font-size:12.5px;font-weight:600;margin-bottom:10px">Recent Sync Activity</div>
        <div class="tc" style="margin-bottom:0">
            <table>
                <thead>
                    <tr>
                        <th>Type</th><th>Status</th><th>Records</th><th>Duration</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conn->syncLogs->take(5) as $log)
                    <tr>
                        <td style="font-weight:500;text-transform:capitalize">{{ $log->sync_type }}</td>
                        <td>
                            <span class="badge {{ $log->status === 'success' ? 'b-green' : ($log->status === 'running' ? 'b-amber' : 'b-red') }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td>{{ number_format($log->records_synced) }}</td>
                        <td style="color:var(--text-3)">
                            @if($log->started_at && $log->completed_at)
                                {{ $log->started_at->diffInSeconds($log->completed_at) }}s
                            @else —
                            @endif
                        </td>
                        <td style="color:var(--text-3)">{{ $log->created_at?->format('M d, H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@empty
<div class="card" style="text-align:center;padding:60px 24px">
    <div style="width:56px;height:56px;border-radius:14px;background:#f0fdf4;margin:0 auto 14px;display:flex;align-items:center;justify-content:center">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" width="24" height="24"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
    </div>
    <div style="font-size:15px;font-weight:600;margin-bottom:6px">No QuickBooks Accounts Connected</div>
    <div style="font-size:13px;color:var(--text-3);margin-bottom:20px">Connect your QuickBooks account to start syncing data automatically.</div>
    @can('connect_quickbooks')
    <a href="{{ route('quickbooks.connect') }}" class="btn btn-primary">Connect QuickBooks</a>
    @endcan
</div>
@endforelse

@endsection

@push('scripts')
<script>
async function syncQB(id, type) {
    const json = await fetch(`/quickbooks/${id}/sync`, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({sync_type:type})
    }).then(r=>r.json());
    showToast(json.message, json.status === 'success' ? 'success' : 'error');
    if (json.status === 'success') setTimeout(() => location.reload(), 1500);
}

async function disconnectQB(id) {
    if (!confirm('Disconnect this QuickBooks account?')) return;
    const json = await fetch(`/quickbooks/${id}/disconnect`, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}
    }).then(r=>r.json());
    showToast(json.message, json.status === 'success' ? 'success' : 'error');
    if (json.status === 'success') setTimeout(() => location.reload(), 800);
}
</script>
@endpush
