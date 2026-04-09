@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Trial Banner --}}
@if($subscription && $subscription->isTrialing())
<div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 mb-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="material-icons text-amber-500 text-2xl">hourglass_empty</span>
        <div>
            <p class="font-semibold text-amber-800">Free trial active</p>
            <p class="text-sm text-amber-700">
                Your trial ends {{ $subscription->trial_ends_at?->diffForHumans() }}.
                Upgrade to continue using {{ $appName }}.
            </p>
        </div>
    </div>
    <a href="{{ route('billing.index') }}" class="btn btn-primary text-sm whitespace-nowrap">
        Upgrade Now
    </a>
</div>
@endif

{{-- Stats Row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php $stats = [
        ['label' => 'Documents',    'value' => $documentCount,            'icon' => 'folder',          'color' => 'text-blue-500'],
        ['label' => 'QB Accounts',  'value' => $qbConnections->count(),   'icon' => 'sync',            'color' => 'text-indigo-500'],
        ['label' => 'Plan',         'value' => $subscription?->package?->name ?? 'No Plan', 'icon' => 'inventory_2', 'color' => 'text-purple-500'],
        ['label' => 'Status',       'value' => ucfirst($subscription?->status ?? 'N/A'),    'icon' => 'verified',    'color' => 'text-emerald-500'],
    ]; @endphp
    @foreach($stats as $s)
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
        <span class="material-icons text-2xl {{ $s['color'] }}">{{ $s['icon'] }}</span>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $s['value'] }}</p>
            <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $s['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Documents --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Recent Documents</h3>
            <a href="{{ route('documents.index') }}" class="text-sm text-blue-600 hover:underline">View all →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentDocs as $doc)
            <div class="px-5 py-3.5 flex items-center gap-4">
                <span class="material-icons text-2xl text-blue-400">
                    {{ str_contains($doc->mime_type, 'pdf') ? 'picture_as_pdf' :
                       (str_contains($doc->mime_type, 'image') ? 'image' : 'description') }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ $doc->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $doc->uploader?->name ?? 'Unknown' }} ·
                        {{ $doc->created_at->diffForHumans() }} ·
                        {{ format_bytes($doc->size ?? 0) }}
                    </p>
                </div>
                <a href="{{ route('documents.download', $doc) }}"
                   class="text-gray-400 hover:text-blue-600 transition" title="Download">
                    <span class="material-icons">download</span>
                </a>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-gray-400">
                <span class="material-icons text-4xl mb-2 block">folder_open</span>
                No documents yet.
                <a href="{{ route('documents.index') }}" class="text-blue-600 block mt-1 text-sm">Upload your first document →</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions + QB Status --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('documents.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                    <span class="material-icons text-blue-500">upload_file</span> Upload Document
                </a>
                <a href="{{ route('quickbooks.connect') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                    <span class="material-icons text-indigo-500">link</span> Connect QuickBooks
                </a>
                <a href="{{ route('email-templates.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                    <span class="material-icons text-purple-500">email</span> Manage Email Templates
                </a>
                <a href="{{ route('settings.branding') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                    <span class="material-icons text-pink-500">palette</span> Customize Branding
                </a>
            </div>
        </div>

        {{-- QuickBooks connections --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-4 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">QuickBooks</h3>
                <a href="{{ route('quickbooks.index') }}" class="text-xs text-blue-600 hover:underline">Manage</a>
            </div>
            @forelse($qbConnections as $conn)
            <div class="px-4 py-3 border-b border-gray-50 last:border-0">
                <p class="text-sm font-medium text-gray-800">{{ $conn->qb_company_name ?? 'Connected Account' }}</p>
                <p class="text-xs text-gray-400">
                    Last synced: {{ $conn->last_synced_at?->diffForHumans() ?? 'Never' }}
                </p>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-xs text-gray-400">
                No QuickBooks connected.
                <a href="{{ route('quickbooks.connect') }}" class="text-blue-600 block mt-1">Connect now →</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
