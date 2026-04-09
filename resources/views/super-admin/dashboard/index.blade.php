@extends('layouts.superadmin.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stats Row --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @php
    $statCards = [
        ['label'=>'Total Companies',  'value'=> number_format($stats['total_companies']),  'icon'=>'business',     'color'=>'text-blue-600'],
        ['label'=>'Active Companies', 'value'=> number_format($stats['active_companies']),  'icon'=>'check_circle', 'color'=>'text-green-600'],
        ['label'=>'Active Subs',      'value'=> number_format($stats['active_subscriptions']), 'icon'=>'subscriptions','color'=>'text-indigo-600'],
        ['label'=>'Total Users',      'value'=> number_format($stats['total_users']),       'icon'=>'group',        'color'=>'text-purple-600'],
        ['label'=>'Monthly Revenue',  'value'=>'$'.number_format($stats['monthly_revenue'],2), 'icon'=>'trending_up','color'=>'text-emerald-600'],
        ['label'=>'Total Revenue',    'value'=>'$'.number_format($stats['total_revenue'],2),'icon'=>'paid',         'color'=>'text-amber-600'],
    ];
    @endphp
    @foreach($statCards as $card)
    <div class="stat-card flex items-start gap-3">
        <span class="material-icons text-2xl mt-0.5 {{ $card['color'] }}">{{ $card['icon'] }}</span>
        <div>
            <div class="stat-number text-xl">{{ $card['value'] }}</div>
            <div class="stat-label">{{ $card['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Revenue Chart --}}
    <div class="xl:col-span-2 card">
        <div class="card-header">
            <h3>Revenue (Last 6 Months)</h3>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="220"></canvas>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="card">
        <div class="card-header"><h3>Recent Payments</h3></div>
        <div class="overflow-hidden">
            @forelse($recentPayments as $payment)
            <div class="px-5 py-3 border-b border-slate-100 last:border-0 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $payment->company?->company_name }}</p>
                    <p class="text-xs text-slate-400">{{ $payment->paid_at?->format('M d, Y') }}</p>
                </div>
                <span class="font-semibold text-sm text-emerald-600">${{ number_format($payment->amount, 2) }}</span>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-slate-400 text-sm">No payments yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Recent Companies --}}
<div class="card mt-6">
    <div class="card-header">
        <h3>Recent Companies</h3>
        <a href="{{ route('superadmin.companies.index') }}" class="btn btn-secondary text-xs">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Subdomain</th>
                    <th>Package</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentCompanies as $company)
                <tr>
                    <td>
                        <div class="font-medium text-slate-800">{{ $company->company_name }}</div>
                        <div class="text-xs text-slate-400">{{ $company->company_email }}</div>
                    </td>
                    <td class="text-slate-500 font-mono text-xs">{{ $company->subdomain }}</td>
                    <td>{{ $company->package?->name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $company->status === 'active' ? 'badge-green' : ($company->status === 'suspended' ? 'badge-red' : 'badge-yellow') }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </td>
                    <td class="text-slate-400 text-xs">{{ $company->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('superadmin.companies.show', $company->id) }}" class="btn btn-secondary text-xs py-1">View</a>
                            <form method="POST" action="{{ route('superadmin.companies.impersonate', $company->id) }}" class="inline">
                                @csrf
                                <button class="btn btn-primary text-xs py-1">Login As</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-slate-400">No companies yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const chartData = @json($revenueChart);
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.month),
        datasets: [{
            label: 'Revenue ($)',
            data:  chartData.map(d => parseFloat(d.total)),
            backgroundColor: 'rgba(59,130,246,.7)',
            borderColor: 'rgba(59,130,246,1)',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero:true, grid:{ color:'#f1f5f9' }, ticks:{ callback: v => '$'+v } },
            x: { grid:{ display:false } }
        }
    }
});
</script>
@endpush
