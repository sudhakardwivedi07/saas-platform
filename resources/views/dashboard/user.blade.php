@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')

<style>
.user-db-header { margin-bottom: 24px; }
.user-db-header h1 { font-size: 22px; font-weight: 700; color: var(--text-1); margin: 0 0 6px; }
.user-db-header h1 span { color: var(--orange); }
.user-db-header p { font-size: 13.5px; color: var(--text-3); margin: 0; }
.user-db-header p a { color: var(--orange); text-decoration: none; font-weight: 500; }
.user-db-header p a:hover { text-decoration: underline; }

.plan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: start; }
@media (max-width: 900px) { .plan-grid { grid-template-columns: 1fr; } }

.plan-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.plan-card-title { font-size: 16px; font-weight: 700; color: var(--text-1); margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
.plan-card-title .plan-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    background: var(--text-1);
    color: #fff;
    letter-spacing: .3px;
}
.plan-card-title .plan-star { color: #F59E0B; font-size: 16px; }

.plan-card-subtitle { font-size: 13px; color: var(--text-3); margin: 0 0 6px; font-weight: 500; }
.plan-card-desc { font-size: 13px; color: var(--text-2); line-height: 1.6; margin: 0 0 16px; }
.plan-card-desc strong { font-weight: 600; color: var(--text-1); }

.plan-feature-list { list-style: none; margin: 0 0 20px; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.plan-feature-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: var(--text-2); }
.plan-feature-list li .feat-check {
    width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px;
    color: var(--green);
}
.plan-feature-list li .feat-title { font-weight: 600; color: var(--text-1); }
.plan-feature-list li .feat-body { color: var(--text-3); font-size: 12.5px; margin-top: 2px; line-height: 1.5; }

.entitlement-box {
    background: #FFF8F3;
    border: 1px solid #FBDCC8;
    border-radius: 8px;
    padding: 14px;
    margin: 0 0 18px;
}
.entitlement-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: var(--orange);
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 8px;
}
.entitlement-title { font-size: 13px; font-weight: 600; color: var(--text-1); margin-bottom: 3px; }
.entitlement-sub { font-size: 12px; color: var(--text-3); }

.plan-price-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: var(--text-3);
    margin-bottom: 14px;
    margin-top: auto;
}

.plan-btn {
    display: flex; align-items: center; justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 11px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: opacity .15s, transform .1s;
}
.plan-btn:hover { opacity: .88; transform: translateY(-1px); }
.plan-btn.btn-orange { background: var(--orange); color: #fff; }
.plan-btn.btn-orange-outline { background: #fff; color: var(--orange); border: 2px solid var(--orange); }

.mt-auto { margin-top: auto; }
</style>

<div class="user-db-header">
    <h1>Hi <span>{{ auth()->user()->first_name }}</span></h1>
    <p>
        You're one step away from unlocking your first CFO-level financial insights.
        @if(auth()->user()->can('view_quickbooks'))
        <a href="{{ route('quickbooks.index') }}">Connect QuickBooks</a> to proceed.
        @else
        Connect QuickBooks to proceed.
        @endif
    </p>
</div>

<div class="plan-grid">

    {{-- Card 1: What this platform does --}}
    <div class="plan-card">
        <div class="plan-card-title">What {{ $clientAdminName }} does</div>
        <p class="plan-card-desc">
            {{ $clientAdminName }} helps founders and finance leaders turn raw financial data into clear, CFO-level intelligence—so you can understand performance, monitor cash flow, and identify risks without manual spreadsheets.
        </p>
        <p class="plan-card-desc"><strong>It provides a structured platform for:</strong></p>
        <ul class="plan-feature-list">
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Flexible data ingestion and visibility</div>
                    <div class="feat-body">Bring in data via QuickBooks to create a single source of financial truth.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Live financial dashboards</div>
                    <div class="feat-body">Automatically generate and track key financial metrics as your data is connected.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Deeper KPI analysis</div>
                    <div class="feat-body">Go beyond surface metrics with detailed KPI views that explain what's driving performance.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Secure financial data management</div>
                    <div class="feat-body">Your data is handled through a secure, read-only system designed for financial accuracy and trust.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Ongoing guidance and support</div>
                    <div class="feat-body">Built to support informed decision-making as your business grows.</div>
                </div>
            </li>
        </ul>
    </div>

    {{-- Cards 2+: Dynamic plan cards from client_admin --}}
    @forelse($plans ?? collect() as $plan)
        @php
            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
            $features  = is_array($plan->features) ? array_filter($plan->features) : [];
            $isFree    = (bool)($plan->is_free ?? false);
            $price     = $isFree ? null : '$' . number_format((float)($plan->monthly_price ?? 0), 2);
        @endphp
        <div class="plan-card">
            <div class="plan-card-title">
                {{ $plan->name }}
                @if($isCurrent)
                    <span class="plan-badge">CURRENT</span>
                @elseif($plan->is_recommended ?? false)
                    <span class="plan-star">&#9733;</span>
                @endif
            </div>
            @if($plan->description)
                <p class="plan-card-desc">{{ $plan->description }}</p>
            @endif

            @if(count($features))
                <ul class="plan-feature-list" style="margin-bottom:auto">
                    @foreach($features as $feature)
                        <li>
                            <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($isCurrent && $isFree)
                <div class="entitlement-box">
                    <div class="entitlement-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        Entitlement
                    </div>
                    <div class="entitlement-title">Complimentary access</div>
                    <div class="entitlement-sub">Get started with your financial dashboard at no cost.</div>
                </div>
            @elseif($price)
                <div class="plan-price-label" style="margin-top:24px">
                    {{ $price }}/MONTH
                    @if($plan->max_insights_per_month ?? null)
                        &bull; INCLUDES {{ $plan->max_insights_per_month }} ANALYSIS RUNS
                    @endif
                </div>
            @endif

            @if($isCurrent)
                @if(!empty($isQuickbooksConnected))
                    <button type="button" class="plan-btn btn-orange" style="margin-top:auto">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                        Generate Insights
                    </button>
                @else
                    <a href="{{ route('quickbooks.index') }}" class="plan-btn btn-orange-outline" style="margin-top:auto">
                        Connect QuickBooks
                    </a>
                @endif
            @else
                <form method="POST" action="{{ route('dashboard.assign-plan') }}" style="margin-top:auto">
                    @csrf
                    <input type="hidden" name="client_package_id" value="{{ $plan->id }}">
                    <button type="submit" class="plan-btn btn-orange-outline">
                        Upgrade to {{ $plan->name }} &rarr;
                    </button>
                </form>
            @endif
        </div>
    @empty
        {{-- No plans configured by client_admin yet --}}
        <div class="plan-card" style="grid-column:span 2;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px 32px;min-height:220px">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin-bottom:14px"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
            <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:6px">No plans available yet</div>
            <div style="font-size:13px;color:var(--text-3);max-width:320px;line-height:1.6">
                Your account administrator hasn't set up any subscription plans yet. Please contact them for more information.
            </div>
        </div>
    @endforelse

</div>

@endsection