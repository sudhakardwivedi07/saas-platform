@extends('layouts.app')

@section('page-title', 'Finance')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-1);margin:0 0 2px 0;">Finance</h1>
        <p style="font-size:0.875rem;color:var(--text-2);margin:0;">Overview of your billing, revenue, and payment history.</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid kpi-4" style="margin-bottom:1.75rem;">

    {{-- MRR --}}
    <div class="kpi kpi-bl">
        <div class="kpi-top">
            <span class="kpi-lbl">Monthly Recurring Revenue</span>
            <span class="kpi-ico ico-blue">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <div class="kpi-val">${{ number_format($mrr, 2) }}</div>
        <div class="kpi-ctx">Current billing cycle</div>
    </div>

    {{-- ARR --}}
    <div class="kpi kpi-gr">
        <div class="kpi-top">
            <span class="kpi-lbl">Annual Recurring Revenue</span>
            <span class="kpi-ico ico-green">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </span>
        </div>
        <div class="kpi-val">${{ number_format($mrr * 12, 2) }}</div>
        <div class="kpi-ctx">Projected annual value</div>
    </div>

    {{-- Total Revenue --}}
    <div class="kpi kpi-or">
        <div class="kpi-top">
            <span class="kpi-lbl">Total Revenue</span>
            <span class="kpi-ico ico-orange">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </span>
        </div>
        <div class="kpi-val">${{ number_format($totalRevenue, 2) }}</div>
        <div class="kpi-ctx">All time collected</div>
    </div>

    {{-- Outstanding --}}
    <div class="kpi kpi-rd">
        <div class="kpi-top">
            <span class="kpi-lbl">Outstanding</span>
            <span class="kpi-ico ico-red">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </span>
        </div>
        <div class="kpi-val">$0.00</div>
        <div class="kpi-ctx">No pending invoices</div>
    </div>

</div>

{{-- Payment History Table --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <div>
            <div class="card-title">Payment History</div>
            <div class="card-sub">All transactions processed through your account.</div>
        </div>
    </div>

    @if($recentPayments->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;">
            <div style="width:56px;height:56px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p style="font-size:0.9375rem;font-weight:600;color:var(--text-1);margin:0 0 4px 0;">No payments yet</p>
            <p style="font-size:0.8125rem;color:var(--text-2);margin:0;">Payment records will appear here once transactions are processed.</p>
        </div>
    @else
        <div class="tc">
            <table>
                <thead>
                    <tr class="tc-head">
                        <th>Company</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $payment)
                    <tr>
                        <td>
                            <span style="font-weight:500;color:var(--text-1);">
                                {{ $payment->company_name ?? $payment->description ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-weight:700;color:#16a34a;">
                                ${{ number_format($payment->amount / 100, 2) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $status = $payment->status ?? 'paid';
                                $badgeClass = match(strtolower($status)) {
                                    'paid', 'succeeded' => 'b-green',
                                    'pending'           => 'b-amber',
                                    'failed', 'overdue' => 'b-red',
                                    default             => 'b-gray',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td style="color:var(--text-2);font-size:0.8125rem;">
                            {{ isset($payment->created_at) ? \Carbon\Carbon::parse($payment->created_at)->format('M j, Y') : '—' }}
                        </td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:0.8125rem;color:var(--text-2);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                                </svg>
                                Stripe
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
