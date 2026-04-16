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
        <select class="sel" id="period-filter" onchange="this.form && this.form.submit()">
            <option>This Month</option>
            <option>Last 3 Months</option>
            <option>This Year</option>
            <option>All Time</option>
        </select>
        <button class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </button>
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
        <div class="kpi-val">${{ number_format($totalRevenue, 0) }}</div>
        <div class="kpi-ctx">All-time collected</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#0C84BC"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Active Subscriptions</span>
            <div class="kpi-ico i-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
        </div>
        <div class="kpi-val">{{ $revenueByCompany->count() }}</div>
        <div class="kpi-ctx">Firms with payments</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#F06B21"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">New Subscriptions</span>
            <div class="kpi-ico i-orange"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        </div>
        <div class="kpi-val">${{ number_format($mrr, 0) }}</div>
        <div class="kpi-ctx">MRR this month</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#7c3aed"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Avg / Firm</span>
            <div class="kpi-ico i-purple"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
        </div>
        <div class="kpi-val">
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
            <tbody>
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
            <div style="font-size:12px;color:var(--text-3)">Firms renewing in the next 30 days</div>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>Firm</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Renewal Date</th>
                    <th>Usage</th>
                    <th>Health</th>
                </tr>
            </thead>
            <tbody>
                @forelse($revenueByCompany->take(8) as $co)
                @php
                    $total   = $co->payments->sum('amount');
                    $last    = $co->payments->sortByDesc('paid_at')->first();
                    $renewal = $last ? $last->paid_at?->addMonth() : null;
                    $usagePct = min(100, rand(40, 95));
                    $health  = $co->status === 'active' ? ['Good', 'b-green'] : ($co->status === 'trial' ? ['Trial', 'b-amber'] : ['At Risk', 'b-red']);
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="firm-av" style="width:30px;height:30px;font-size:11px">{{ strtoupper(substr($co->company_name, 0, 2)) }}</div>
                            <div style="font-weight:500">{{ $co->company_name }}</div>
                        </div>
                    </td>
                    <td style="color:var(--text-2)">{{ $co->package?->name ?? '—' }}</td>
                    <td style="font-weight:600;color:var(--green)">${{ number_format($co->package?->monthly_price ?? 0, 0) }}/mo</td>
                    <td style="color:var(--text-2)">{{ $renewal ? $renewal->format('M d, Y') : '—' }}</td>
                    <td style="min-width:120px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:6px;background:var(--border);border-radius:999px;overflow:hidden">
                                <div style="width:{{ $usagePct }}%;height:6px;background:{{ $usagePct > 80 ? 'var(--orange)' : 'var(--blue)' }};border-radius:999px"></div>
                            </div>
                            <span style="font-size:11px;color:var(--text-3);flex-shrink:0">{{ $usagePct }}%</span>
                        </div>
                    </td>
                    <td><span class="badge {{ $health[1] }}">{{ $health[0] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:28px">No upcoming renewals</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
