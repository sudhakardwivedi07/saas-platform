@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Trial Banner --}}
@if($subscription && $subscription->isTrialing())
<div class="info-banner" style="background:#fffbeb;border-color:#fde68a">
    <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" width="15" height="15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <div class="info-banner-txt" style="color:#92400e">
        Free trial active — ends {{ $subscription->trial_ends_at?->diffForHumans() }}.
        <a href="{{ route('billing.index') }}" style="color:var(--orange);font-weight:600">Upgrade now →</a>
    </div>
</div>
@endif

{{-- Time filter --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
    <div>
        <h1 style="font-size:19px;font-weight:600;margin-bottom:2px">Dashboard</h1>
        <p style="font-size:13px;color:var(--text-2)">Welcome back, {{ $authUser->name }}</p>
    </div>
    <div class="time-pills">
        <button class="tp active">Today</button>
        <button class="tp">7D</button>
        <button class="tp">30D</button>
        <button class="tp">90D</button>
    </div>
</div>

{{-- KPI Row 1 --}}
<div class="kpi-grid kpi-4" style="margin-bottom:12px">
    <div class="kpi kpi-bl">
        <div class="kpi-top">
            <span class="kpi-lbl">Total Clients</span>
            <div class="kpi-ico ico-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        </div>
        <div class="kpi-val">0</div>
        <div class="kpi-trend neutral">— No change</div>
    </div>
    <div class="kpi kpi-gr">
        <div class="kpi-top">
            <span class="kpi-lbl">Active Companies</span>
            <div class="kpi-ico ico-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
        </div>
        <div class="kpi-val">{{ $qbConnections->count() }}</div>
        <div class="kpi-trend up">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="10" height="10"><polyline points="18 15 12 9 6 15"/></svg>
            Active
        </div>
    </div>
    <div class="kpi kpi-or">
        <div class="kpi-top">
            <span class="kpi-lbl">MRR</span>
            <div class="kpi-ico ico-orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
        </div>
        <div class="kpi-val kpi-val" style="color:var(--orange)">$0</div>
        <div class="kpi-trend neutral">— Billing active</div>
    </div>
    <div class="kpi kpi-bl">
        <div class="kpi-top">
            <span class="kpi-lbl">QB Connected</span>
            <div class="kpi-ico ico-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg></div>
        </div>
        <div class="kpi-val">{{ $qbConnections->count() }}</div>
        <div class="kpi-ctx" style="margin-top:4px">QuickBooks accounts</div>
    </div>
</div>

{{-- KPI Row 2 --}}
<div class="kpi-grid kpi-4" style="margin-bottom:18px">
    <div class="kpi">
        <div class="kpi-top">
            <span class="kpi-lbl">Documents</span>
            <div class="kpi-ico ico-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        </div>
        <div class="kpi-val">{{ $documentCount }}</div>
        <div class="kpi-ctx" style="margin-top:4px">Uploaded files</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <span class="kpi-lbl">Subscription</span>
            <div class="kpi-ico ico-orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
        </div>
        <div class="kpi-val" style="font-size:16px;padding-top:5px">{{ $subscription?->package?->name ?? 'No Plan' }}</div>
        <div class="kpi-ctx" style="margin-top:4px">{{ ucfirst($subscription?->status ?? 'N/A') }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <span class="kpi-lbl">Email Templates</span>
            <div class="kpi-ico ico-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        </div>
        <div class="kpi-val">—</div>
        <div class="kpi-ctx" style="margin-top:4px"><a href="{{ route('email-templates.index') }}" style="color:var(--blue)">Manage →</a></div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <span class="kpi-lbl">Support Tickets</span>
            <div class="kpi-ico ico-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></div>
        </div>
        <div class="kpi-val">0</div>
        <div class="kpi-ctx" style="margin-top:4px">Open tickets</div>
    </div>
</div>

{{-- Charts row --}}
<div class="charts-row">
    <div class="card">
        <div class="card-title">Revenue Overview</div>
        <div class="card-sub">Monthly revenue trend</div>
        <canvas id="dashChart" height="180"></canvas>
    </div>
    <div class="card">
        <div class="card-title">Recent Activity</div>
        <div class="card-sub">Latest events</div>
        @forelse($recentDocs->take(5) as $doc)
        <div class="act-row">
            <div class="act-dot" style="background:var(--blue)"></div>
            <div class="act-txt">
                <strong>{{ $doc->name }}</strong> uploaded
                <div style="font-size:11.5px;color:var(--text-3)">by {{ $doc->uploader?->name }}</div>
            </div>
            <div class="act-time">{{ $doc->created_at->diffForHumans() }}</div>
        </div>
        @empty
        <div style="text-align:center;padding:28px 0;color:var(--text-3);font-size:13px">No recent activity</div>
        @endforelse
        @if($qbConnections->count())
        @foreach($qbConnections->take(3) as $conn)
        <div class="act-row">
            <div class="act-dot" style="background:var(--green)"></div>
            <div class="act-txt">QuickBooks <strong>{{ $conn->qb_company_name ?? 'Account' }}</strong> connected</div>
            <div class="act-time">{{ $conn->created_at?->diffForHumans() }}</div>
        </div>
        @endforeach
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('dashChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun'],
            datasets: [{
                label: 'Revenue',
                data: [0,0,0,0,0,0],
                borderColor: '#f06b21',
                backgroundColor: 'rgba(240,107,33,.08)',
                borderWidth: 2,
                fill: true,
                tension: .4,
                pointBackgroundColor: '#f06b21',
                pointRadius: 4
            }]
        },
        options: {
            responsive:true,
            plugins:{ legend:{ display:false } },
            scales:{
                y:{ ticks:{ callback: v => '$'+v } },
                x:{ grid:{ display:false } }
            }
        }
    });
}
// Time pills
document.querySelectorAll('.tp').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tp').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});
</script>
@endpush
