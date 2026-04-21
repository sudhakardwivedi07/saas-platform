@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
/* ── Dashboard KPI Cards ─────────────────────────────── */
.db-kpi-grid {
    display: grid;
    gap: 14px;
    margin-bottom: 14px;
}
.db-kpi-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
.db-kpi-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }

.db-kpi {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    border-left: 4px solid var(--border);
}
.db-kpi.bl { border-left-color: var(--blue); }
.db-kpi.gr { border-left-color: var(--green); }
.db-kpi.or { border-left-color: var(--orange); }
.db-kpi.rd { border-left-color: var(--red); }
.db-kpi.pu { border-left-color: #7c3aed; }

.db-kpi-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.db-kpi-lbl {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--text-3);
}
.db-kpi-ico {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ico-bl { background: rgba(26,106,168,.1); color: var(--blue); }
.ico-gr { background: rgba(16,185,129,.1); color: var(--green); }
.ico-or { background: rgba(240,107,33,.1); color: var(--orange); }
.ico-rd { background: rgba(239,68,68,.1); color: var(--red); }
.ico-pu { background: rgba(124,58,237,.1); color: #7c3aed; }

.db-kpi-val {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-1);
    line-height: 1.1;
}
.db-kpi-val .sub-val {
    font-size: 16px;
    font-weight: 400;
    color: var(--text-3);
}
.db-kpi-val.green  { color: var(--green); }
.db-kpi-val.orange { color: var(--orange); }
.db-kpi-val.red    { color: var(--red); }

.db-kpi-ctx {
    font-size: 12px;
    color: var(--text-3);
    line-height: 1.4;
}
.db-kpi-arrow {
    position: absolute;
    bottom: 16px;
    right: 16px;
    color: var(--red);
}

/* ── Time pills ──────────────────────────────────────── */
.tp-bar {
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 9px;
    padding: 3px;
}
.tp {
    padding: 5px 12px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 500;
    border: none;
    background: transparent;
    color: var(--text-2);
    cursor: pointer;
    transition: all .12s;
    display: flex;
    align-items: center;
    gap: 5px;
}
.tp.active {
    background: var(--orange);
    color: #fff;
}
.tp:hover:not(.active) { background: #fff; }

/* ── Custom date range ───────────────────────────────── */
.custom-range-row {
    display: none;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.custom-range-row.visible { display: flex; }
.custom-range-row input[type="date"] {
    padding: 5px 10px;
    border: 1px solid var(--border);
    border-radius: 7px;
    font-size: 13px;
    color: var(--text-1);
    background: #fff;
    outline: none;
}
.custom-range-row input[type="date"]:focus {
    border-color: var(--orange);
}
.custom-range-row .btn-apply {
    padding: 5px 14px;
    background: var(--orange);
    color: #fff;
    border: none;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}

/* ── Bottom row ──────────────────────────────────────── */
.db-bottom {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 14px;
}
.db-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.db-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-1);
    margin-bottom: 2px;
}
.db-card-sub {
    font-size: 12px;
    color: var(--text-3);
    margin-bottom: 16px;
}

/* ── Activity feed ───────────────────────────────────── */
.act-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid var(--bg);
}
.act-item:last-child { border-bottom: none; padding-bottom: 0; }
.act-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
}
.act-txt { flex: 1; font-size: 13px; color: var(--text-1); line-height: 1.4; }
.act-time { font-size: 11.5px; color: var(--text-3); white-space: nowrap; flex-shrink: 0; }

@media(max-width:1100px){
    .db-kpi-grid.cols-4 { grid-template-columns: repeat(2,1fr); }
    .db-kpi-grid.cols-3 { grid-template-columns: repeat(2,1fr); }
    .db-bottom { grid-template-columns: 1fr; }
}
@media(max-width:640px){
    .db-kpi-grid.cols-4,.db-kpi-grid.cols-3 { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- Trial Banner --}}
@if($subscription && $subscription->isTrialing())
<div class="info-banner" style="background:#fffbeb;border-color:#fde68a;margin-bottom:16px">
    <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" width="15" height="15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <div class="info-banner-txt" style="color:#92400e">
        Free trial active — ends {{ $subscription->trial_ends_at?->diffForHumans() }}.
        <a href="{{ route('billing.index') }}" style="color:var(--orange);font-weight:600">Upgrade now →</a>
    </div>
</div>
@endif

{{-- Page header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:10px">
    <div>
        <h1 style="font-size:20px;font-weight:700;margin-bottom:3px;color:var(--text-1)">Dashboard</h1>
        <p style="font-size:13px;color:var(--text-3)">Overview of your portfolio performance and activity</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div class="tp-bar">
            <button class="tp {{ $period === '1W' ? 'active' : '' }}" onclick="setTP(this,'1W')">1W</button>
            <button class="tp {{ $period === '1M' ? 'active' : '' }}" onclick="setTP(this,'1M')">1M</button>
            <button class="tp {{ $period === '1Y' ? 'active' : '' }}" onclick="setTP(this,'1Y')">1Y</button>
            <button class="tp {{ $period === 'custom' ? 'active' : '' }}" onclick="setTP(this,'custom')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Custom Range
            </button>
        </div>
        <span style="font-size:12px;color:var(--text-3)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" style="display:inline-block;vertical-align:middle;margin-right:3px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Last updated: {{ now()->format('M j, Y · g:i A') }}
        </span>
        <a href="{{ route('clients.index') }}" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </a>
    </div>
</div>

{{-- Custom date range picker (shown only when custom is active) --}}
<div class="custom-range-row {{ $period === 'custom' ? 'visible' : '' }}" id="customRangeRow" style="margin-bottom:12px">
    <span style="font-size:12.5px;color:var(--text-2);font-weight:500">From</span>
    <input type="date" id="startDate" value="{{ $startDateInput }}">
    <span style="font-size:12.5px;color:var(--text-2);font-weight:500">To</span>
    <input type="date" id="endDate" value="{{ $endDateInput }}">
    <button class="btn-apply" onclick="applyCustomRange()">Apply</button>
    <span style="font-size:12px;color:var(--text-3)">
        Showing: <strong>{{ $periodLabel }}</strong>
    </span>
</div>

{{-- KPI Row 1: 4 cards --}}
<div class="db-kpi-grid cols-4">

    {{-- Total Clients --}}
    <div class="db-kpi bl">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Total Clients</span>
            <div class="db-kpi-ico ico-bl">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
        </div>
        <div class="db-kpi-val">{{ $clientCount }}</div>
        <div class="db-kpi-ctx">New clients added · {{ $periodLabel }}</div>
    </div>

    {{-- Active Clients --}}
    <div class="db-kpi gr">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Active Clients</span>
            <div class="db-kpi-ico ico-gr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="db-kpi-val green">
            {{ $activeClientCount }}<span class="sub-val"> / {{ $clientCount }}</span>
        </div>
        <div class="db-kpi-ctx">Active of {{ $clientCount }} new clients · {{ $periodLabel }}</div>
    </div>

    {{-- Total Companies --}}
    <div class="db-kpi bl">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Total Companies</span>
            <div class="db-kpi-ico ico-bl">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
            </div>
        </div>
        <div class="db-kpi-val">{{ $companyCount }}</div>
        <div class="db-kpi-ctx">New companies added · {{ $periodLabel }}</div>
    </div>

    {{-- Data Connected --}}
    <div class="db-kpi gr">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Data Connected</span>
            <div class="db-kpi-ico ico-gr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
            </div>
        </div>
        <div class="db-kpi-val {{ $qbConnectedCount > 0 ? 'green' : '' }}">
            {{ $qbConnectedCount }}<span class="sub-val"> / {{ $companyCount }}</span>
        </div>
        <div class="db-kpi-ctx">Of {{ $companyCount }} new companies with QuickBooks · {{ $periodLabel }}</div>
    </div>

</div>

{{-- KPI Row 2: 4 cards --}}
<div class="db-kpi-grid cols-4">

    {{-- Active Companies --}}
    <div class="db-kpi bl">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Active Companies</span>
            <div class="db-kpi-ico ico-bl">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
        </div>
        <div class="db-kpi-val">
            {{ $activeCompanyCount }}<span class="sub-val"> / {{ $companyCount }}</span>
        </div>
        <div class="db-kpi-ctx">Active of {{ $companyCount }} new companies · {{ $periodLabel }}</div>
    </div>

    {{-- Insights Generated --}}
    <div class="db-kpi bl">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Insights Generated</span>
            <div class="db-kpi-ico ico-bl">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
        </div>
        <div class="db-kpi-val">{{ $insightsGenerated ?? 0 }}</div>
        <div class="db-kpi-ctx">CFO financial insights generated · {{ $periodLabel }}</div>
    </div>

    {{-- Insights Available --}}
    <div class="db-kpi or">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Insights Available</span>
            <div class="db-kpi-ico ico-or">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
        </div>
        @php
            $insAvail = $insightsAvailable ?? 0;
            $insPkg   = $subscription?->package?->max_documents ?? 50;
        @endphp
        <div class="db-kpi-val orange">
            {{ $insAvail }}<span class="sub-val"> / {{ $insPkg }}</span>
        </div>
        <div class="db-kpi-ctx">Insight credits left · resets {{ now()->endOfMonth()->format('M j') }}</div>
    </div>

    {{-- Companies at Risk --}}
    <div class="db-kpi rd">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Companies at Risk</span>
            <div class="db-kpi-ico ico-rd">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
        </div>
        <div class="db-kpi-val red">{{ $companiesAtRisk ?? 0 }}</div>
        <div class="db-kpi-ctx">Companies with inactive status</div>
        @if(($companiesAtRisk ?? 0) > 0)
        <a href="{{ route('clients.index') }}" class="db-kpi-arrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        @endif
    </div>

</div>

{{-- KPI Row 3: 3 cards --}}
<div class="db-kpi-grid cols-3" style="margin-bottom:18px">

    {{-- Total Revenue --}}
    <div class="db-kpi gr">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Total Revenue</span>
            <div class="db-kpi-ico ico-gr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
        </div>
        <div class="db-kpi-val green">${{ number_format($totalRevenue, 0) }}</div>
        <div class="db-kpi-ctx">Collected from clients · {{ $periodLabel }}</div>
    </div>

    {{-- Payments Received --}}
    <div class="db-kpi bl">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Payments Received</span>
            <div class="db-kpi-ico ico-bl">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <div class="db-kpi-val">{{ $paymentsReceived }}</div>
        <div class="db-kpi-ctx">Payments received · {{ $periodLabel }}</div>
    </div>

    {{-- Active Subscriptions --}}
    <div class="db-kpi pu">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Active Subscriptions</span>
            <div class="db-kpi-ico ico-pu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="db-kpi-val" style="color:#7c3aed">
            {{ $activeSubscriptions }}<span class="sub-val"> / {{ $companyCount }}</span>
        </div>
        <div class="db-kpi-ctx">Active or trial of {{ $companyCount }} new companies · {{ $periodLabel }}</div>
    </div>

</div>

{{-- Bottom row: Chart + Activity --}}
<div class="db-bottom">

    {{-- Usage Overview chart --}}
    <div class="db-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px">
            <div>
                <div class="db-card-title">Usage Overview</div>
                <div class="db-card-sub">Companies &amp; payments · last 6 months</div>
            </div>
        </div>
        <canvas id="usageChart" height="160"></canvas>
    </div>

    {{-- Recent Activity --}}
    <div class="db-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div class="db-card-title">Recent Activity</div>
                <div class="db-card-sub" style="margin-bottom:0">Latest events across your clients</div>
            </div>
            <a href="{{ route('activity.index') }}" style="font-size:12.5px;color:var(--blue);font-weight:500;white-space:nowrap">View all →</a>
        </div>

        @php
            $actItems = collect();
            foreach($recentDocs->take(4) as $doc) {
                $actItems->push([
                    'color' => 'var(--blue)',
                    'text'  => ($doc->name ?? 'Document') . ' uploaded',
                    'time'  => $doc->created_at->diffForHumans(),
                ]);
            }
            foreach($qbConnections->take(2) as $conn) {
                $actItems->push([
                    'color' => 'var(--green)',
                    'text'  => ($conn->company?->company_name ?? $conn->qb_company_name ?? 'Account') . ' connected QuickBooks',
                    'time'  => $conn->created_at?->diffForHumans() ?? '',
                ]);
            }
            $actItems = $actItems->sortByDesc('time')->values();
        @endphp

        @forelse($actItems as $item)
        <div class="act-item">
            <div class="act-dot" style="background:{{ $item['color'] }}"></div>
            <div class="act-txt">{{ $item['text'] }}</div>
            <div class="act-time">{{ $item['time'] }}</div>
        </div>
        @empty
        <div style="text-align:center;padding:32px 0;color:var(--text-3)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" style="display:block;margin:0 auto 8px;stroke:var(--text-3)"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <div style="font-size:13px">No recent activity yet</div>
        </div>
        @endforelse
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const CURRENT_PERIOD = '{{ $period }}';

function setTP(el, period) {
    if (period === 'custom') {
        document.querySelectorAll('.tp').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('customRangeRow').classList.add('visible');
        return;
    }
    window.location.href = '{{ url()->current() }}?period=' + period;
}

function applyCustomRange() {
    const start = document.getElementById('startDate').value;
    const end   = document.getElementById('endDate').value;
    if (!start || !end) {
        alert('Please select both start and end dates.');
        return;
    }
    if (start > end) {
        alert('Start date must be before end date.');
        return;
    }
    window.location.href = '{{ url()->current() }}?period=custom&start_date=' + start + '&end_date=' + end;
}

// Show custom range row if currently active
if (CURRENT_PERIOD === 'custom') {
    document.getElementById('customRangeRow').classList.add('visible');
}

// Chart
const ctx = document.getElementById('usageChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Companies',
                data: {!! json_encode($chartCompanies) !!},
                backgroundColor: 'rgba(240,107,33,.15)',
                borderColor: '#f06b21',
                borderWidth: 1.5,
                borderRadius: 5,
                borderSkipped: false,
            }, {
                label: 'Payments',
                data: {!! json_encode($chartPayments) !!},
                backgroundColor: 'rgba(26,106,168,.18)',
                borderColor: '#1a6aa8',
                borderWidth: 1.5,
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 16 } }
            },
            scales: {
                y: {
                    ticks: { stepSize: 1, callback: v => Number.isInteger(v) ? v : '' },
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.04)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
