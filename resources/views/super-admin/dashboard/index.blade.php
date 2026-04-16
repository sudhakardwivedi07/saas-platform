@extends('layouts.superadmin.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="ph">
    <div>
        <h1>Dashboard</h1>
        <p>Platform overview &amp; key metrics</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.companies.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Firm
        </a>
    </div>
</div>

{{-- KPI Row --}}
<div class="kpi-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="kpi">
        <div class="kpi-accent" style="background:#0C84BC"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Total Firms</span>
            <div class="kpi-ico i-blue">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
        </div>
        <div class="kpi-val">{{ $stats['total_companies'] }}</div>
        <div class="kpi-ctx">All registered firms</div>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#10b981"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Active Firms</span>
            <div class="kpi-ico i-green">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>
        <div class="kpi-val">{{ $stats['active_companies'] }}</div>
        <div class="kpi-ctx">Currently active subscriptions</div>
        <span class="kpi-trend trend-up">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            Active
        </span>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#F06B21"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Monthly Revenue</span>
            <div class="kpi-ico i-orange">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
        </div>
        <div class="kpi-val">${{ number_format($stats['monthly_revenue'], 0) }}</div>
        <div class="kpi-ctx">This month</div>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#7c3aed"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Active Subscriptions</span>
            <div class="kpi-ico i-purple">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <div class="kpi-val">{{ $stats['active_subscriptions'] }}</div>
        <div class="kpi-ctx">Active &amp; trialing</div>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#0C84BC"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Total Users</span>
            <div class="kpi-ico i-blue">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="kpi-val">{{ $stats['total_users'] }}</div>
        <div class="kpi-ctx">Across all firms</div>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#10b981"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Total Revenue</span>
            <div class="kpi-ico i-green">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
        </div>
        <div class="kpi-val">${{ number_format($stats['total_revenue'], 0) }}</div>
        <div class="kpi-ctx">All-time collected</div>
    </div>
</div>

{{-- Charts row --}}
<div class="charts-row charts-2col">
    <div class="card">
        <div class="card-title">Revenue Overview</div>
        <div class="card-sub">Monthly revenue — last 6 months</div>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
    <div class="card">
        <div class="card-title">Recent Activity</div>
        <div class="card-sub">Latest platform events</div>
        @forelse($recentPayments->take(6) as $p)
        <div class="act-row">
            <div class="act-dot" style="background:#0C84BC"></div>
            <div class="act-txt">
                Payment from <strong>{{ $p->company?->company_name ?? 'Unknown' }}</strong>
                — ${{ number_format($p->amount, 2) }}
            </div>
            <div class="act-time">{{ $p->paid_at?->diffForHumans() }}</div>
        </div>
        @empty
        <div class="act-row"><div class="act-txt" style="color:var(--text-3)">No recent activity</div></div>
        @endforelse
    </div>
</div>

{{-- Recent Firms --}}
<div class="tc">
    <div class="tc-head">
        <div>
            <div style="font-size:14px;font-weight:600">Recent Firms</div>
            <div style="font-size:12px;color:var(--text-3)">Newly registered companies</div>
        </div>
        <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Firm</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Users</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentCompanies as $co)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="firm-av">{{ strtoupper(substr($co->company_name, 0, 2)) }}</div>
                        <div>
                            <div style="font-weight:500">{{ $co->company_name }}</div>
                            <div style="font-size:11.5px;color:var(--text-3)">{{ $co->subdomain ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $co->package?->name ?? '—' }}</td>
                <td>
                    @if($co->status === 'active')
                        <span class="badge b-green"><span class="bdot" style="background:#10b981"></span>Active</span>
                    @elseif($co->status === 'suspended')
                        <span class="badge b-red"><span class="bdot" style="background:#ef4444"></span>Suspended</span>
                    @elseif($co->status === 'trial')
                        <span class="badge b-amber"><span class="bdot" style="background:#f59e0b"></span>Trial</span>
                    @else
                        <span class="badge b-gray">{{ ucfirst($co->status ?? 'Inactive') }}</span>
                    @endif
                </td>
                <td>{{ $co->users()->count() }}</td>
                <td>{{ $co->created_at?->format('M d, Y') }}</td>
                <td>
                    <div class="dm-wrap">
                        <button class="dot-menu" data-menu="dm-{{ $co->id }}">⋯</button>
                        <div class="dm-popup" id="dm-{{ $co->id }}">
                            <a href="{{ route('superadmin.companies.show', $co->id) }}" class="dm-item dm-hi">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View Firm
                            </a>
                            <a href="{{ route('superadmin.companies.edit', $co->id) }}" class="dm-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </a>
                            <form method="POST" action="{{ route('superadmin.companies.impersonate', $co->id) }}" style="display:contents">
                                @csrf
                                <button type="submit" class="dm-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    Login As
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:28px">No firms yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const labels = @json($revenueChart->pluck('month'));
const data   = @json($revenueChart->pluck('total'));
const ctx    = document.getElementById('revenueChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue ($)',
                data,
                backgroundColor: 'rgba(12,132,188,.18)',
                borderColor: '#0C84BC',
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: v => '$' + v.toLocaleString() } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
