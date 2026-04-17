@extends('layouts.superadmin.app')
@section('title','Finance')
@section('page-title','Finance')

@section('content')
<div class="ph">
    <div>
        <h1>Finance</h1>
        <p>Revenue, subscriptions &amp; billing overview</p>
    </div>
    <div class="ph-actions">
        <select class="sel" id="period-filter">
            <option value="this_month"    {{ $period === 'this_month'    ? 'selected' : '' }}>This Month</option>
            <option value="last_3_months" {{ $period === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            <option value="this_year"     {{ $period === 'this_year'     ? 'selected' : '' }}>This Year</option>
            <option value="all_time"      {{ $period === 'all_time'      ? 'selected' : '' }}>All Time</option>
        </select>
        <a id="export-btn" href="{{ route('superadmin.finance.export', ['period' => $period]) }}" class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid kpi-4" style="margin-bottom:18px">
    <div class="kpi">
        <div class="kpi-accent" style="background:#10b981"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Total Revenue</span>
            <div class="kpi-ico i-green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
        </div>
        <div class="kpi-val" id="kpi-revenue">${{ number_format($totalRevenue, 0) }}</div>
        <div class="kpi-ctx">All-time collected</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#0C84BC"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Active Subscriptions</span>
            <div class="kpi-ico i-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
        </div>
        <div class="kpi-val" id="kpi-firms">{{ $revenueByCompany->count() }}</div>
        <div class="kpi-ctx">Firms with payments</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#F06B21"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">New Subscriptions</span>
            <div class="kpi-ico i-orange"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        </div>
        <div class="kpi-val" id="kpi-mrr">${{ number_format($mrr, 0) }}</div>
        <div class="kpi-ctx">MRR this month</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#7c3aed"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Avg / Firm</span>
            <div class="kpi-ico i-purple"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
        </div>
        <div class="kpi-val" id="kpi-avg">
            ${{ $revenueByCompany->count() > 0 ? number_format($totalRevenue / $revenueByCompany->count(), 0) : 0 }}
        </div>
        <div class="kpi-ctx">Average per firm</div>
    </div>
</div>

{{-- Revenue by Firm Table --}}
<div class="tc" style="margin-bottom:18px">
    <div class="tc-head">
        <div>
            <div style="font-size:14px;font-weight:600">Revenue by Firm</div>
            <div style="font-size:12px;color:var(--text-3)">Subscription &amp; billing details per firm</div>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>Firm</th>
                    <th>Contact</th>
                    <th>Plan</th>
                    <th>Fee</th>
                    <th>Billing</th>
                    <th>Next Billing</th>
                    <th>Joined</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody id="revenue-tbody">
                @forelse($revenueByCompany as $co)
                @php
                    $total = $co->payments->sum('amount');
                    $lastPayment = $co->payments->sortByDesc('paid_at')->first();
                    $payStatus = $total > 0 ? 'Paid' : 'Pending';
                    $payBadge  = $total > 0 ? 'b-green' : 'b-amber';
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="firm-av">{{ strtoupper(substr($co->company_name, 0, 2)) }}</div>
                            <div>
                                <div style="font-weight:500">{{ $co->company_name }}</div>
                                <div style="font-size:11.5px;color:var(--text-3)">{{ $co->subdomain }}.{{ config('tenancy.root_domain') }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text-2)">{{ $co->company_email ?? '—' }}</td>
                    <td>{{ $co->package?->name ?? '—' }}</td>
                    <td style="font-weight:600">${{ number_format($total, 0) }}</td>
                    <td style="color:var(--text-3)">Monthly</td>
                    <td style="color:var(--text-2)">
                        {{ $lastPayment ? $lastPayment->paid_at?->addMonth()->format('M d, Y') : '—' }}
                    </td>
                    <td style="color:var(--text-3)">{{ $co->created_at?->format('M d, Y') }}</td>
                    <td><span class="badge {{ $payBadge }}">{{ $payStatus }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px">No firms with payments yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Upcoming Renewals --}}
<div class="tc">
    <div class="tc-head">
        <div>
            <div style="font-size:14px;font-weight:600">Upcoming Renewals</div>
            <div style="font-size:12px;color:var(--text-3)" id="renewal-subtitle">Next 30 days &middot; {{ $revenueByCompany->take(8)->count() }} firms renewing</div>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>Firm</th>
                    <th>Plan</th>
                    <th>Renewal Date</th>
                    <th>Amount</th>
                    <th>Last Login</th>
                    <th>Insight Usage</th>
                    <th>Risk</th>
                </tr>
            </thead>
            <tbody id="renewals-tbody">
                @forelse($revenueByCompany->take(8) as $co)
                @php
                    $last      = $co->payments->sortByDesc('paid_at')->first();
                    $renewal   = $last ? $last->paid_at?->addMonth() : null;
                    $daysLeft  = $renewal ? (int) now()->diffInDays($renewal, false) : null;
                    $maxInsights  = max(1, $co->package?->max_users ?? 50);
                    $usedInsights = $co->users_count ?? 0;
                    $usagePct  = min(100, round(($usedInsights / $maxInsights) * 100));
                    $barColor  = $usagePct >= 80 ? 'var(--orange)' : 'var(--blue)';
                    $riskLabel = $usagePct >= 80 ? 'High' : ($usagePct >= 60 ? 'Medium' : 'Low');
                    $riskBadge = $usagePct >= 80 ? 'b-red' : ($usagePct >= 60 ? 'b-amber' : 'b-green');
                    $planBadge = match(strtolower($co->package?->name ?? '')) {
                        'enterprise' => 'b-blue',
                        'starter'    => 'b-amber',
                        'pro'        => 'b-purple',
                        default      => 'b-gray',
                    };
                    $lastLogin = $co->last_active_at;
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="firm-av" style="width:30px;height:30px;font-size:11px">{{ strtoupper(substr($co->company_name, 0, 2)) }}</div>
                            <div style="font-weight:500">{{ $co->company_name }}</div>
                        </div>
                    </td>
                    <td>
                        @if($co->package)
                            <span class="badge {{ $planBadge }}">{{ $co->package->name }}</span>
                        @else
                            <span style="color:var(--text-3)">—</span>
                        @endif
                    </td>
                    <td style="color:{{ $daysLeft !== null && $daysLeft <= 15 ? 'var(--orange)' : 'var(--text-2)' }};white-space:nowrap">
                        @if($renewal)
                            {{ $renewal->format('M j, Y') }}
                            @if($daysLeft !== null)
                                &middot; {{ $daysLeft }} days
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-weight:600;color:var(--green)">${{ number_format($co->package?->monthly_price ?? 0, 2) }}</td>
                    <td style="color:var(--text-2);white-space:nowrap">
                        {{ $lastLogin ? $lastLogin->diffForHumans() : '—' }}
                    </td>
                    <td style="min-width:140px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:6px;background:var(--border);border-radius:999px;overflow:hidden">
                                <div style="width:{{ $usagePct }}%;height:6px;background:{{ $barColor }};border-radius:999px"></div>
                            </div>
                            <span style="font-size:11px;color:var(--text-3);flex-shrink:0">{{ $usedInsights }}/{{ $maxInsights }}</span>
                        </div>
                    </td>
                    <td><span class="badge {{ $riskBadge }}">{{ $riskLabel }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px">No upcoming renewals</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const dataUrl   = '{{ route('superadmin.finance.data') }}';
    const exportBtn = document.getElementById('export-btn');
    const exportBase = '{{ route('superadmin.finance.export') }}';
    const select    = document.getElementById('period-filter');

    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderRevenueTbody(rows) {
        if (!rows.length) {
            return '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px">No firms with payments yet</td></tr>';
        }
        return rows.map(r => `
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="firm-av">${esc(r.initials)}</div>
                        <div>
                            <div style="font-weight:500">${esc(r.name)}</div>
                            <div style="font-size:11.5px;color:var(--text-3)">${esc(r.domain)}</div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--text-2)">${esc(r.email)}</td>
                <td>${esc(r.plan)}</td>
                <td style="font-weight:600">${esc(r.total)}</td>
                <td style="color:var(--text-3)">Monthly</td>
                <td style="color:var(--text-2)">${esc(r.nextBilling)}</td>
                <td style="color:var(--text-3)">${esc(r.joined)}</td>
                <td><span class="badge ${esc(r.payBadge)}">${esc(r.payStatus)}</span></td>
            </tr>`).join('');
    }

    function renderRenewalsTbody(rows) {
        if (!rows.length) {
            return '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px">No upcoming renewals</td></tr>';
        }
        return rows.map(r => {
            const dateColor = r.daysLeft !== null && r.daysLeft <= 15 ? 'var(--orange)' : 'var(--text-2)';
            const renewalText = r.renewalDate
                ? esc(r.renewalDate) + (r.daysLeft !== null ? ' &middot; ' + r.daysLeft + ' days' : '')
                : '—';
            const planCell = r.plan
                ? `<span class="badge ${esc(r.planBadge)}">${esc(r.plan)}</span>`
                : `<span style="color:var(--text-3)">—</span>`;

            return `
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="firm-av" style="width:30px;height:30px;font-size:11px">${esc(r.initials)}</div>
                        <div style="font-weight:500">${esc(r.name)}</div>
                    </div>
                </td>
                <td>${planCell}</td>
                <td style="color:${dateColor};white-space:nowrap">${renewalText}</td>
                <td style="font-weight:600;color:var(--green)">${esc(r.amount)}</td>
                <td style="color:var(--text-2);white-space:nowrap">${r.lastLogin ? esc(r.lastLogin) : '—'}</td>
                <td style="min-width:140px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;height:6px;background:var(--border);border-radius:999px;overflow:hidden">
                            <div style="width:${r.usagePct}%;height:6px;background:${esc(r.barColor)};border-radius:999px"></div>
                        </div>
                        <span style="font-size:11px;color:var(--text-3);flex-shrink:0">${r.usedInsights}/${r.maxInsights}</span>
                    </div>
                </td>
                <td><span class="badge ${esc(r.riskBadge)}">${esc(r.riskLabel)}</span></td>
            </tr>`;
        }).join('');
    }

    function applyData(data) {
        document.getElementById('kpi-revenue').textContent = data.totalRevenue;
        document.getElementById('kpi-firms').textContent   = data.firmCount;
        document.getElementById('kpi-mrr').textContent     = data.mrr;
        document.getElementById('kpi-avg').textContent     = data.avgPerFirm;
        document.getElementById('renewal-subtitle').innerHTML =
            'Next 30 days &middot; ' + data.renewalCount + ' firms renewing';
        document.getElementById('revenue-tbody').innerHTML  = renderRevenueTbody(data.revenueByFirm);
        document.getElementById('renewals-tbody').innerHTML = renderRenewalsTbody(data.upcomingRenewals);
    }

    function loadPeriod(period) {
        select.disabled = true;
        fetch(dataUrl + '?period=' + encodeURIComponent(period), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            applyData(data);
            exportBtn.href = exportBase + '?period=' + encodeURIComponent(period);
        })
        .finally(() => { select.disabled = false; });
    }

    select.addEventListener('change', function () {
        loadPeriod(this.value);
    });
})();
</script>
@endsection
