@extends('layouts.superadmin.app')
@section('title', $company->company_name)
@section('page-title', $company->company_name)

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Company Info Card --}}
    <div class="xl:col-span-2 space-y-6">
        <div class="card">
            <div class="card-header">
                <h3>Company Details</h3>
                <div class="flex gap-2">
                    <a href="{{ route('superadmin.companies.edit', $company) }}" class="btn btn-secondary text-xs">
                        <span class="material-icons text-sm">edit</span> Edit
                    </a>
                    <form method="POST" action="{{ route('superadmin.companies.impersonate', $company) }}">
                        @csrf
                        <button class="btn btn-primary text-xs">
                            <span class="material-icons text-sm">login</span> Login As Admin
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body grid grid-cols-2 gap-4">
                @php $fields = [
                    'Company Name'  => $company->company_name,
                    'App Name'      => $company->app_name,
                    'Email'         => $company->company_email,
                    'Phone'         => $company->company_phone ?? '—',
                    'Subdomain'     => $company->subdomain,
                    'Custom Domain' => $company->custom_domain ?? '—',
                    'Status'        => ucfirst($company->status),
                    'Joined'        => $company->created_at->format('M d, Y'),
                ]; @endphp
                @foreach($fields as $label => $value)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $label }}</p>
                    <p class="font-medium text-slate-800">{{ $value }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="card">
            <div class="card-header"><h3>Recent Payments</h3></div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Amount</th><th>Status</th><th>Transaction</th></tr></thead>
                    <tbody>
                        @forelse($recentPayments as $p)
                        <tr>
                            <td>{{ $p->created_at->format('M d, Y') }}</td>
                            <td class="font-semibold">${{ number_format($p->amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $p->status === 'complete' ? 'badge-green' : 'badge-red' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td class="font-mono text-xs text-slate-400">{{ Str::limit($p->transaction_id, 20) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-6 text-slate-400">No payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar Stats --}}
    <div class="space-y-4">
        <div class="stat-card">
            <div class="stat-label mb-1">Package</div>
            <div class="stat-number text-xl">{{ $company->package?->name ?? 'None' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label mb-1">Total Users</div>
            <div class="stat-number text-xl">{{ $userCount }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label mb-1">Total Paid</div>
            <div class="stat-number text-xl text-emerald-600">${{ number_format($totalPaid, 2) }}</div>
        </div>
        @if($company->subscription)
        <div class="card">
            <div class="card-header"><h3>Subscription</h3></div>
            <div class="card-body space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Status</span>
                    <span class="badge {{ $company->subscription->isActive() ? 'badge-green' : 'badge-red' }}">
                        {{ ucfirst($company->subscription->status) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Cycle</span>
                    <span class="font-medium capitalize">{{ $company->subscription->billing_cycle }}</span>
                </div>
                @if($company->subscription->current_period_end)
                <div class="flex justify-between">
                    <span class="text-slate-500">Renews</span>
                    <span class="font-medium">{{ $company->subscription->current_period_end->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Users List --}}
        <div class="card">
            <div class="card-header"><h3>Users ({{ $company->users->count() }})</h3></div>
            <div class="divide-y divide-slate-100">
                @foreach($company->users->take(5) as $u)
                <div class="px-4 py-3 flex items-center gap-3">
                    <img src="{{ $u->image_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $u->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $u->email }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
