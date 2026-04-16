@extends('layouts.superadmin.app')
@section('title','Finance')
@section('page-title','Finance')

@section('content')
<div class="ph">
    <div>
        <h1>Finance</h1>
        <p>Revenue, invoices &amp; financial overview</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </button>
    </div>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:18px">
    <div class="kpi">
        <div class="kpi-accent" style="background:#F06B21"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">MRR</span>
            <div class="kpi-ico i-orange"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
        </div>
        <div class="kpi-val">${{ number_format($mrr, 0) }}</div>
        <div class="kpi-ctx">This month</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#0C84BC"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">ARR</span>
            <div class="kpi-ico i-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
        </div>
        <div class="kpi-val">${{ number_format($arr, 0) }}</div>
        <div class="kpi-ctx">Annualized</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#10b981"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Total Revenue</span>
            <div class="kpi-ico i-green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="20 6 9 17 4 12"/></svg></div>
        </div>
        <div class="kpi-val">${{ number_format($totalRevenue, 0) }}</div>
        <div class="kpi-ctx">All-time collected</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent" style="background:#ef4444"></div>
        <div class="kpi-top">
            <span class="kpi-lbl">Outstanding</span>
            <div class="kpi-ico i-red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
        </div>
        <div class="kpi-val">$0</div>
        <div class="kpi-ctx">Unpaid invoices</div>
    </div>
</div>

{{-- Recent Payments Table --}}
<div class="tc">
    <div class="tc-head">
        <div style="font-size:14px;font-weight:600">Payment History</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPayments as $p)
            <tr>
                <td>{{ $p->company?->company_name ?? '—' }}</td>
                <td style="font-weight:600;color:var(--green)">${{ number_format($p->amount, 2) }}</td>
                <td><span class="badge b-green">Paid</span></td>
                <td>{{ $p->paid_at?->format('M d, Y') }}</td>
                <td style="color:var(--text-3)">Stripe</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px">No payments yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
