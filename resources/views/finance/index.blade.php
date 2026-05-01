@extends('layouts.app')
@section('title', 'Finance')
@section('page-title', 'Finance')

@push('styles')
<style>
/* ── KPI cards ───────────────────────────────────────── */
.fin-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.fin-kpi{background:#fff;border:1px solid var(--border);border-radius:12px;padding:20px 22px;display:flex;flex-direction:column;gap:8px}
.fin-kpi-head{display:flex;align-items:center;justify-content:space-between}
.fin-kpi-lbl{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-3)}
.fin-kpi-ico{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.fin-kpi-val{font-size:26px;font-weight:700;color:var(--text-1);line-height:1.1}
.fin-kpi-val.dash{font-size:28px;color:#d1d5db;font-weight:400}
.fin-kpi-ctx{font-size:12px;color:var(--text-3)}

/* ── Info card (fresh user) ──────────────────────────── */
.fin-info-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:28px 32px;display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:20px}
.fin-info-divider{border-left:1px solid var(--border);padding-left:32px}
.fin-info-title{font-size:15px;font-weight:700;color:var(--text-1);margin-bottom:10px}
.fin-info-body{font-size:13px;color:var(--text-2);line-height:1.7;margin-bottom:0}
.fin-info-body a{color:var(--orange);text-decoration:none}
.fin-info-body a:hover{text-decoration:underline}
.fin-info-btns{display:flex;align-items:center;gap:10px;margin-top:20px;flex-wrap:wrap}

/* ── Payment history section ─────────────────────────── */
.fin-hist-card{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
.fin-hist-head{padding:20px 24px;border-bottom:1px solid var(--border)}
.fin-hist-title{font-size:14px;font-weight:700;color:var(--text-1)}
.fin-empty-state{text-align:center;padding:64px 24px 48px}
.fin-empty-ico{width:72px;height:72px;border-radius:16px;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
.fin-empty-title{font-size:16px;font-weight:700;color:var(--text-1);margin-bottom:10px}
.fin-empty-body{font-size:13.5px;color:#6b7280;line-height:1.7;max-width:440px;margin:0 auto 24px}
.fin-empty-btns{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap}

/* ── Table ───────────────────────────────────────────── */
.fin-table{width:100%;border-collapse:collapse}
.fin-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);padding:11px 20px;border-bottom:1px solid var(--border);white-space:nowrap;text-align:left}
.fin-table td{padding:13px 20px;border-bottom:1px solid var(--bg);font-size:13px;vertical-align:middle}
.fin-table tbody tr:last-child td{border-bottom:none}
.fin-table tbody tr:hover td{background:var(--bg)}

@media(max-width:900px){
    .fin-kpi-grid{grid-template-columns:repeat(2,1fr)}
    .fin-info-card{grid-template-columns:1fr}
    .fin-info-divider{border-left:none;border-top:1px solid var(--border);padding-left:0;padding-top:24px}
}
@media(max-width:600px){
    .fin-kpi-grid{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')

@if(session('error'))
<div class="info-banner" style="margin-bottom:1rem;background:rgba(239,68,68,.06);border-color:var(--red)">
    <span style="font-size:13px;color:var(--red)">{{ session('error') }}</span>
</div>
@endif

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Finance</h1>
        <p>Track client billing, revenue, and subscription activity</p>
    </div>
</div>

@if($isFreshFinance)

{{-- ── FRESH USER: Info card ───────────────────────────────────────────── --}}
<div class="fin-info-card">
    <div>
        <div class="fin-info-title">What you will see here</div>
        <p class="fin-info-body">
            Once clients are on active plans and billing is processing, this section displays your portal's revenue summary, individual client invoices, payment history, and active subscription counts.
            <a href="#">All data updates automatically.</a>
        </p>
        <div class="fin-info-btns">
            <a href="{{ route('settings.index') . '#payment' }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:7px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Set Up Payment Gateway
            </a>
            <a href="{{ route('settings.index') . '#plans' }}" class="btn btn-secondary">Create Client Plans</a>
        </div>
    </div>
    <div class="fin-info-divider">
        <div class="fin-info-title">To get started</div>
        <p class="fin-info-body">
            <a href="{{ route('settings.index') . '#payment' }}">Connect your payment gateway in Settings</a> to enable client billing. Then create your client plans and begin onboarding clients. Financial activity will populate here as soon as the first subscription payment is processed.
        </p>
    </div>
</div>

{{-- ── FRESH USER: Grayed-out KPI cards ───────────────────────────────── --}}
<div class="fin-kpi-grid">

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Monthly Revenue</span>
            <div class="fin-kpi-ico" style="background:#dbeafe">
                <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="18" height="18"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val dash">—</div>
        <div class="fin-kpi-ctx">Activates when billing begins</div>
    </div>

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Active Subscriptions</span>
            <div class="fin-kpi-ico" style="background:#d1fae5">
                <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" width="18" height="18"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val dash">—</div>
        <div class="fin-kpi-ctx">Assign plans to start tracking</div>
    </div>

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Outstanding</span>
            <div class="fin-kpi-ico" style="background:#ffedd5">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val dash">—</div>
        <div class="fin-kpi-ctx">No outstanding balances</div>
    </div>

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Total Collected</span>
            <div class="fin-kpi-ico" style="background:#ffedd5">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" width="18" height="18"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val dash">—</div>
        <div class="fin-kpi-ctx">Lifetime payments received</div>
    </div>

</div>

{{-- ── FRESH USER: Empty payment history ───────────────────────────────── --}}
<div class="fin-hist-card">
    <div class="fin-hist-head">
        <div class="fin-hist-title">Invoice &amp; Payment History</div>
    </div>
    <div class="fin-empty-state">
        <div class="fin-empty-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.6" width="32" height="32"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="fin-empty-title">No billing activity has been recorded yet</div>
        <div class="fin-empty-body">
            Your invoice history, client payments, and subscription records will appear here once clients are on active plans and payments begin processing. All billing is automated — nothing to configure on an ongoing basis.
        </div>
        <div class="fin-empty-btns">
            <a href="{{ route('settings.index') . '#payment' }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:7px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Set Up Payment Gateway
            </a>
            <a href="{{ route('settings.index') . '#plans' }}" class="btn btn-secondary">Create Client Plans</a>
        </div>
    </div>
</div>

@else

{{-- ── ACTIVE: KPI cards ────────────────────────────────────────────────── --}}
<div class="fin-kpi-grid">

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Monthly Revenue</span>
            <div class="fin-kpi-ico" style="background:#dbeafe">
                <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="18" height="18"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val" style="color:var(--green)">${{ number_format($mrr, 2) }}</div>
        <div class="fin-kpi-ctx">Current billing cycle</div>
    </div>

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Active Subscriptions</span>
            <div class="fin-kpi-ico" style="background:#d1fae5">
                <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" width="18" height="18"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val" style="color:#10b981">{{ $activeSubscriptions }}</div>
        <div class="fin-kpi-ctx">Clients on active plans</div>
    </div>

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Outstanding</span>
            <div class="fin-kpi-ico" style="background:#ffedd5">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val">$0.00</div>
        <div class="fin-kpi-ctx">No pending invoices</div>
    </div>

    <div class="fin-kpi">
        <div class="fin-kpi-head">
            <span class="fin-kpi-lbl">Total Collected</span>
            <div class="fin-kpi-ico" style="background:#ffedd5">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" width="18" height="18"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <div class="fin-kpi-val" style="color:var(--text-1)">${{ number_format($totalRevenue, 2) }}</div>
        <div class="fin-kpi-ctx">Lifetime payments received</div>
    </div>

</div>

{{-- ── Invoice & billing table (payments + plan/subscription fallback) ─── --}}
<div class="fin-hist-card">
    <div class="fin-hist-head">
        <div class="fin-hist-title">Invoice &amp; Payment History</div>
    </div>
    @if($recentPayments->isNotEmpty())
    <table class="fin-table">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Company Name</th>
                <th>Plan</th>
                <th>Price</th>
                <th>Status</th>
                <th>Date</th>
                <th style="width:48px;text-align:right"> </th>
            </tr>
        </thead>
        <tbody>
        @foreach($recentPayments as $payment)
        @php
            $co = $payment->company;
            $clientName = $co?->users->first()?->name ?? '—';
            $companyName = $co?->company_name ?? '—';
            $planName = $co?->clientPackage?->name
                ?? $payment->subscription?->clientPackage?->name
                ?? $payment->subscription?->package?->name
                ?? '—';
            $status = $payment->status ?? 'pending';
            $badgeClass = match(strtolower((string) $status)) {
                'complete', 'paid', 'succeeded' => 'b-green',
                'pending' => 'b-amber',
                'failed', 'refunded' => 'b-red',
                default => 'b-gray',
            };
            $amt = (float) $payment->amount;
        @endphp
        <tr>
            <td>{{ $clientName }}</td>
            <td style="font-weight:500;color:var(--text-1)">{{ $companyName }}</td>
            <td>{{ $planName }}</td>
            <td style="font-weight:600;color:var(--text-1)">${{ number_format($amt, 2) }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
            <td style="color:var(--text-2);font-size:12.5px">{{ $payment->paid_at?->format('M j, Y') ?? '—' }}</td>
            <td style="text-align:right">
                <div class="dm-wrap">
                    <button type="button" class="dot-menu" data-menu="fin-pay-{{ $payment->id }}" title="Actions">•••</button>
                    <div class="dm-popup" id="fin-pay-{{ $payment->id }}">
                        @if($payment->stripe_invoice_id)
                        <a href="{{ route('finance.invoice', $payment) }}" target="_blank" rel="noopener noreferrer" class="dm-item">View invoice</a>
                        @else
                        <span class="dm-item" style="opacity:.55;cursor:default">No Stripe invoice</span>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @elseif(isset($subscribedCompanies) && $subscribedCompanies->isNotEmpty())
    <p style="font-size:12.5px;color:var(--text-3);padding:0 24px 12px;margin:0;border-bottom:1px solid var(--border)">
        No card payments recorded yet. Below is each client company’s assigned plan and subscription status (invoices appear after Stripe charges).
    </p>
    <table class="fin-table">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Company Name</th>
                <th>Plan</th>
                <th>Price</th>
                <th>Status</th>
                <th>Date</th>
                <th style="width:48px;text-align:right"> </th>
            </tr>
        </thead>
        <tbody>
        @foreach($subscribedCompanies as $co)
        @php
            $clientName = $co->users->first()?->name ?? '—';
            $pkg = $co->clientPackage;
            $planName = $pkg?->name
                ?? $co->subscription?->clientPackage?->name
                ?? $co->subscription?->package?->name
                ?? '—';
            $monthly = $pkg?->monthly_price;
            $priceLabel = $planName === '—'
                ? '—'
                : (($monthly !== null && (float) $monthly == 0) || ($pkg?->is_free ?? false)
                    ? 'Free'
                    : '$' . number_format((float) $monthly, 2) . '/mo');
            $sub = $co->subscription;
            $subStatus = $sub?->status ?? 'unassigned';
            $badgeClass = match(strtolower((string) $subStatus)) {
                'active', 'trialing' => 'b-green',
                'past_due' => 'b-amber',
                'canceled', 'cancelled', 'unpaid' => 'b-red',
                'unassigned' => 'b-gray',
                default => 'b-gray',
            };
            $rowDate = $sub?->current_period_start ?? $sub?->updated_at ?? $co->updated_at;
        @endphp
        <tr>
            <td>{{ $clientName }}</td>
            <td style="font-weight:500;color:var(--text-1)">{{ $co->company_name }}</td>
            <td>{{ $planName }}</td>
            <td style="font-weight:600;color:var(--text-1)">{{ $priceLabel }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', ucfirst($subStatus)) }}</span></td>
            <td style="color:var(--text-2);font-size:12.5px">{{ $rowDate?->format('M j, Y') ?? '—' }}</td>
            <td style="text-align:right">
                <div class="dm-wrap">
                    <button type="button" class="dot-menu" data-menu="fin-sub-{{ $co->id }}" title="Actions">•••</button>
                    <div class="dm-popup" id="fin-sub-{{ $co->id }}">
                        <span class="dm-item" style="opacity:.55;cursor:default">Invoice after first payment</span>
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <div class="fin-empty-state" style="padding:40px 24px">
        <div class="fin-empty-title" style="font-size:15px">No billing rows yet</div>
        <div class="fin-empty-body">Connect Stripe and process a payment, or assign plans to client companies to see activity here.</div>
    </div>
    @endif
</div>

@endif

@endsection
