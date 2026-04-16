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

{{-- Row 1: Firm KPIs --}}
<div class="kpi-grid kpi-3" style="margin-bottom:13px">
    <div class="kpi">
        <div class="kpi-accent" style="background:#0C84BC"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Total Firms</span>
            <div class="kpi-ico i-blue">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
        </div>
        <div class="kpi-val" style="font-size:32px">{{ $stats['total_companies'] }}</div>
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
        <div class="kpi-val" style="font-size:32px">{{ $stats['active_companies'] }}</div>
        <div class="kpi-ctx">Currently active subscriptions</div>
        <span class="kpi-trend trend-up">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            Active
        </span>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#7c3aed"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Total End Clients</span>
            <div class="kpi-ico i-purple">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
        </div>
        <div class="kpi-val" style="font-size:32px">{{ $stats['total_users'] }}</div>
        <div class="kpi-ctx">Users across all firms</div>
    </div>
</div>

{{-- Row 2: Revenue KPIs --}}
<div class="kpi-grid kpi-3" style="margin-bottom:18px">
    <div class="kpi">
        <div class="kpi-accent" style="background:#10b981"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Revenue This Month</span>
            <div class="kpi-ico i-green">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
        </div>
        <div class="kpi-val" style="font-size:32px">${{ number_format($stats['monthly_revenue'], 0) }}</div>
        <div class="kpi-ctx">MRR this month</div>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#F06B21"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Revenue This Year</span>
            <div class="kpi-ico i-orange">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
        </div>
        <div class="kpi-val" style="font-size:32px">${{ number_format($stats['total_revenue'], 0) }}</div>
        <div class="kpi-ctx">All-time collected</div>
    </div>

    <div class="kpi">
        <div class="kpi-accent" style="background:#0C84BC"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Avg Revenue / Firm</span>
            <div class="kpi-ico i-blue">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
        </div>
        <div class="kpi-val" style="font-size:32px">
            ${{ $stats['active_companies'] > 0 ? number_format($stats['monthly_revenue'] / $stats['active_companies'], 0) : 0 }}
        </div>
        <div class="kpi-ctx">Per active firm / month</div>
    </div>
</div>

{{-- Revenue Chart (full width) --}}
<div class="card" style="margin-bottom:14px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div>
            <div class="card-title">Revenue Overview</div>
            <div class="card-sub">Monthly revenue — last 6 months</div>
        </div>
    </div>
    <canvas id="revenueChart" height="90"></canvas>
</div>

{{-- Recent Activity (full width) --}}
<div class="card">
    <div class="card-title" style="margin-bottom:14px">Recent Activity</div>
    @forelse($recentPayments->take(8) as $p)
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
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: v => '$' + v.toLocaleString() }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
