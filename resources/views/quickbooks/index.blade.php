@extends('layouts.app')
@section('title','QuickBooks')
@section('page-title','QuickBooks Integration')

@section('content')

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">Connect your QuickBooks accounts to sync customers and invoices.</p>
    @can('connect_quickbooks')
    <a href="{{ route('quickbooks.connect') }}" class="btn btn-primary">
        <span class="material-icons text-lg">add_link</span> Connect QuickBooks
    </a>
    @endcan
</div>

@if(session('success'))
<div class="alert alert-success mb-5">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error mb-5">{{ session('error') }}</div>
@endif

@forelse($connections as $conn)
<div class="card mb-4">
    <div class="card-body">
        <div class="flex flex-wrap items-start justify-between gap-4">
            {{-- QB account info --}}
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:#2ca01c">
                    <svg viewBox="0 0 40 40" style="width:28px;fill:#fff">
                        <path d="M20 5C11.73 5 5 11.73 5 20s6.73 15 15 15 15-6.73 15-15S28.27 5 20 5zm-2 22v-3h-3v-4h3v-3l5 5-5 5zm4-10v3h3v4h-3v3l-5-5 5-5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $conn->qb_company_name ?? 'QuickBooks Account' }}</h3>
                    <p class="text-sm text-gray-500">Realm ID: <code class="text-xs">{{ $conn->realm_id }}</code></p>
                    <p class="text-sm text-gray-500">Connected by {{ $conn->connectedBy?->name }} ·
                        {{ $conn->created_at->format('M d, Y') }}</p>

                    <div class="flex items-center gap-2 mt-2">
                        <span class="badge {{ $conn->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $conn->is_active ? 'Active' : 'Disconnected' }}
                        </span>
                        @if($conn->isAccessTokenExpired())
                        <span class="badge badge-yellow">Token Expired</span>
                        @endif
                        @if($conn->last_synced_at)
                        <span class="text-xs text-gray-400">Last sync: {{ $conn->last_synced_at->diffForHumans() }}</span>
                        @else
                        <span class="text-xs text-gray-400">Never synced</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            @can('sync_quickbooks')
            <div class="flex flex-wrap gap-2">
                <button onclick="syncQB({{ $conn->id }}, 'customers')" class="btn btn-secondary text-sm">
                    <span class="material-icons text-base">people</span> Sync Customers
                </button>
                <button onclick="syncQB({{ $conn->id }}, 'invoices')" class="btn btn-secondary text-sm">
                    <span class="material-icons text-base">receipt_long</span> Sync Invoices
                </button>
                <button onclick="disconnectQB({{ $conn->id }})" class="btn btn-danger text-sm">
                    <span class="material-icons text-base">link_off</span> Disconnect
                </button>
            </div>
            @endcan
        </div>

        {{-- Sync log --}}
        @if($conn->syncLogs->count() > 0)
        <div class="mt-5 border-t border-gray-100 pt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Recent Sync Activity</h4>
            <div class="overflow-x-auto">
                <table class="data-table text-xs">
                    <thead>
                        <tr>
                            <th>Type</th><th>Status</th><th>Records</th><th>Duration</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conn->syncLogs->take(5) as $log)
                        <tr>
                            <td class="capitalize font-medium">{{ $log->sync_type }}</td>
                            <td>
                                <span class="badge text-xs {{ $log->status === 'success' ? 'badge-green' : ($log->status === 'running' ? 'badge-yellow' : 'badge-red') }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>{{ number_format($log->records_synced) }}</td>
                            <td>
                                @if($log->started_at && $log->completed_at)
                                    {{ $log->started_at->diffInSeconds($log->completed_at) }}s
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $log->created_at?->format('M d, H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-16">
        <span class="material-icons text-5xl text-gray-300 block mb-3">sync_disabled</span>
        <h3 class="text-gray-600 font-semibold">No QuickBooks Accounts Connected</h3>
        <p class="text-sm text-gray-400 mt-1 mb-5">Connect your QuickBooks account to start syncing data automatically.</p>
        @can('connect_quickbooks')
        <a href="{{ route('quickbooks.connect') }}" class="btn btn-primary">
            <span class="material-icons text-lg">add_link</span> Connect QuickBooks
        </a>
        @endcan
    </div>
</div>
@endforelse

@endsection

@push('scripts')
<script>
async function syncQB(id, type) {
    const btn = event.currentTarget;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons spin text-base">refresh</span> Syncing…';

    const json = await apiPost(`/quickbooks/${id}/sync`, { sync_type: type });
    handleReply(json);
    btn.disabled = false;
    btn.innerHTML = btn.innerHTML.replace('<span class="material-icons spin text-base">refresh</span> Syncing…',
        type === 'customers' ? '<span class="material-icons text-base">people</span> Sync Customers'
                             : '<span class="material-icons text-base">receipt_long</span> Sync Invoices');
}

async function disconnectQB(id) {
    if (!confirm('Disconnect this QuickBooks account?')) return;
    const json = await apiPost(`/quickbooks/${id}/disconnect`);
    handleReply(json);
    if (json.status === 'success') setTimeout(() => location.reload(), 800);
}

function apiPost(url, data={}) {
    return fetch(url, {
        method:'POST',
        headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json','Content-Type':'application/json'},
        body: JSON.stringify(data)
    }).then(r=>r.json());
}
</script>
@endpush
