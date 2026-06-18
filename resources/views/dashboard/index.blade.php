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
    position: relative;    border-left: 4px solid var(--border);
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
    gap: 10px;
    flex-wrap: wrap;
}
.custom-range-row.visible { display: flex; }

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

@media(max-width:900px){
    .db-kpi-grid.cols-3 { grid-template-columns: repeat(2,1fr); }
    .db-bottom { grid-template-columns: 1fr; }
}
@media(max-width:560px){
    .db-kpi-grid.cols-3 { grid-template-columns: 1fr; }
}

/* ── Onboarding (fresh user) ─────────────────────────── */
.ob-banner {
    background: linear-gradient(135deg, #1a3a5c 0%, #2a5f8f 100%);
    border-radius: 14px;
    padding: 32px 36px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.ob-badge {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .15em;
    text-transform: uppercase;
    color: rgba(255,255,255,.5);
    margin-bottom: 8px;
}
.ob-title {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 10px;
    line-height: 1.3;
}
.ob-sub {
    font-size: 13.5px;
    color: rgba(255,255,255,.7);
    line-height: 1.65;
    max-width: 520px;
}
.ob-banner-btns {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex-shrink: 0;
    align-items: stretch;
    min-width: 160px;
}
.ob-banner-btns .btn-outline-white {
    background: rgba(255,255,255,.12);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,.25);
    border-radius: 8px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background .15s;
}
.ob-banner-btns .btn-outline-white:hover { background: rgba(255,255,255,.2); }

.ob-steps {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.ob-step {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    position: relative;
    transition: box-shadow .15s;
}
.ob-step:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
.ob-step.active {
    border: 2px solid var(--orange);
    box-shadow: 0 4px 20px rgba(240,107,33,.12);
}
.ob-step-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: var(--orange);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .06em;
    padding: 3px 9px;
    border-radius: 4px;
}
.ob-step-ico {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}
.ob-step-num {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 5px;
}
.ob-step-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-1);
    margin-bottom: 10px;
}
.ob-step-desc {
    font-size: 13px;
    color: var(--text-2);
    line-height: 1.65;
    margin-bottom: 18px;
}
.ob-step-link {
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ob-step-link:hover { opacity: .8; }

@media(max-width:900px) { .ob-steps { grid-template-columns: 1fr; } }

/* ── Disabled CTA tooltip ────────────────────────────── */
.cta-disabled-wrap {
    position: relative;
    display: inline-flex;
    cursor: not-allowed;
}
.cta-disabled-wrap .btn,
.cta-disabled-wrap .ob-step-link {
    pointer-events: none;
    opacity: .42;
}
.cta-tip {
    display: none;
    position: absolute;
    bottom: calc(100% + 9px);
    left: 50%;
    transform: translateX(-50%);
    background: #1a202c;
    color: #fff;
    font-size: 12px;
    line-height: 1.55;
    padding: 9px 13px;
    border-radius: 8px;
    width: 250px;
    z-index: 200;
    text-align: center;
    white-space: normal;
    box-shadow: 0 4px 14px rgba(0,0,0,.22);
    pointer-events: none;
}
.cta-tip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: #1a202c;
}
.cta-disabled-wrap:hover .cta-tip { display: block; }
</style>
@endpush

@section('content')

<div id="insights-refresh-target" data-refresh="1" style="display:none"></div>

{{-- Trial Banner: only show when trialing and not yet on an active paid plan --}}
@if($subscription && $subscription->isTrialing() && ($subscription->status ?? '') !== 'active')
<div class="info-banner" style="background:#fffbeb;border-color:#fde68a;margin-bottom:16px;display:none">
    <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" width="15" height="15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <div class="info-banner-txt" style="color:#92400e">
        Free trial active — ends {{ $subscription->trial_ends_at?->diffForHumans() }}.
        <a href="{{ route('billing.index') }}" style="color:var(--orange);font-weight:600">Upgrade now →</a>
    </div>
</div>
@endif

@php
    $canAddClient = ($brandingDone ?? false);
    $disabledTip  = 'Complete Step 1: Branding Setup to unlock Add Client.';
@endphp

@if($isFreshUser)

{{-- ══════════════════════════════════════════════════════
     FRESH USER — ONBOARDING DASHBOARD
     ══════════════════════════════════════════════════════ --}}

{{-- Page Header --}}
<div class="ph" style="margin-bottom:20px">
    <div class="ph-left">
        <h1>Dashboard</h1>
        <p>Your portal is live — let's get you set up</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('account.index') . '#branding' }}" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            Branding Settings
        </a>
        @if($canAddClient)
        <a href="{{ route('clients.index') }}?add=1" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </a>
        @else
        <span class="cta-disabled-wrap">
            <span class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Client
            </span>
            <div class="cta-tip">{{ $disabledTip }}</div>
        </span>
        @endif
    </div>
</div>

{{-- Welcome Banner --}}
<div class="ob-banner">
    <div>
        <div class="ob-badge">{{ strtoupper(company()?->company_name ?? 'YOUR PORTAL') }}</div>
        <div class="ob-title">Hi {{ explode(' ', auth()->user()->name)[0] }}, welcome to your Portal 👋</div>
        <p class="ob-sub">Two steps stand between you and delivering AI-powered CFO insights to your clients. Follow the setup guide below to get your portal fully configured.</p>
    </div>
    <div class="ob-banner-btns">
        @if($canAddClient)
        <a href="{{ route('clients.index') }}?add=1" class="btn btn-primary" style="display:flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </a>
        @else
        <span class="cta-disabled-wrap" style="width:100%">
            <span class="btn btn-primary" style="display:flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap;width:100%">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Client
            </span>
            <div class="cta-tip">{{ $disabledTip }}</div>
        </span>
        @endif
        <a href="{{ route('account.index') . '#branding' }}" class="btn-outline-white">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            Branding Settings
        </a>
    </div>
</div>

{{-- 2 Setup Steps --}}
@php
    $activeStep = !($brandingDone ?? false) ? 1 : 2;
@endphp
<div class="ob-steps">

    {{-- Step 1: Set Your Branding — entire card is clickable --}}
    <a href="{{ route('account.index') . '#branding' }}" class="ob-step {{ $activeStep === 1 ? 'active' : '' }}" style="text-decoration:none;display:flex;flex-direction:column">
        @if($activeStep === 1)
        <div class="ob-step-badge">START HERE</div>
        @elseif($brandingDone)
        <div class="ob-step-badge" style="background:var(--green)">✓ DONE</div>
        @endif

        <div class="ob-step-ico" style="background:#fff3ec">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="1.8" width="22" height="22"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
        </div>
        <div class="ob-step-num" style="color:{{ $activeStep === 1 ? 'var(--orange)' : 'var(--text-3)' }}">STEP 1</div>
        <div class="ob-step-title">Set Your Branding</div>
        <div class="ob-step-desc">Upload your firm logo and set your brand colours. Your clients experience your identity — not a generic interface. This is the first thing they will see.</div>
        <span class="ob-step-link" style="color:var(--orange)">My Account → Branding</span>
    </a>

    {{-- Step 2: Add Your Clients — clickable only when Step 1 is done --}}
    @if($canAddClient)
    <a href="{{ route('clients.index') }}?add=1" class="ob-step {{ $activeStep === 2 ? 'active' : '' }}" style="text-decoration:none;display:flex;flex-direction:column">
        @if($activeStep === 2)
        <div class="ob-step-badge">START HERE</div>
        @endif

        <div class="ob-step-ico" style="background:#ecfdf5">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8" width="22" height="22"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </div>
        <div class="ob-step-num" style="color:#10b981">STEP 2</div>
        <div class="ob-step-title">Add Your Clients</div>
        <div class="ob-step-desc">Add the individuals your portal serves — one at a time or bulk-import via CSV. Once added, each client receives a personalised portal invitation automatically.</div>
        <span class="ob-step-link" style="color:#10b981">Add Client →</span>
    </a>
    @else
    <div class="ob-step cta-disabled-wrap" style="cursor:not-allowed;display:flex;flex-direction:column">
        <div class="ob-step-ico" style="background:#ecfdf5">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8" width="22" height="22"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </div>
        <div class="ob-step-num" style="color:var(--text-3)">STEP 2</div>
        <div class="ob-step-title" style="opacity:.55">Add Your Clients</div>
        <div class="ob-step-desc" style="opacity:.55">Add the individuals your portal serves — one at a time or bulk-import via CSV. Once added, each client receives a personalised portal invitation automatically.</div>
        <span class="ob-step-link" style="color:#10b981;opacity:.42;pointer-events:none">Add Client →</span>
        <div class="cta-tip" style="bottom:calc(100% + 9px);left:50%;transform:translateX(-50%)">{{ $disabledTip }}</div>
    </div>
    @endif

</div>

@else

{{-- ══════════════════════════════════════════════════════
     RETURNING USER — REGULAR DASHBOARD
     ══════════════════════════════════════════════════════ --}}

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
            Last updated: {{ now()->format(active_date_format() . ' · g:i A') }}
        </span>
        @if($modalClients->isNotEmpty())
        <button class="btn btn-primary btn-sm" onclick="openViewClientModal()" style="display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            View as Client
        </button>
        @endif
        @if($canAddClient)
        <a href="{{ route('clients.index') }}?add=1" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </a>
        @else
        <span class="cta-disabled-wrap">
            <span class="btn btn-primary btn-sm" style="display:inline-flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Client
            </span>
            <div class="cta-tip" style="left:auto;right:0;transform:none">{{ $disabledTip }}</div>
        </span>
        @endif
    </div>
</div>

{{-- Custom date range picker (shown only when custom is active) --}}
<div class="custom-range-row {{ $period === 'custom' ? 'visible' : '' }}" id="customRangeRow" style="margin-bottom:12px;align-items:center;gap:10px">
    <div class="dr-wrap" id="db-dr">
        <div class="dr-field" onclick="drOpen('db-dr')">
            <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span class="dr-text" id="db-dr-text">Start Date – End Date</span>
            <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('db-dr')" title="Clear date filter">&#x2715;</button>
        </div>
        <div class="dr-menu" id="db-dr-menu">
            <ul class="dr-list">
                <li class="dr-opt" onclick="drPickPreset('db-dr','today',this)">Today</li>
                <li class="dr-opt" onclick="drPickPreset('db-dr','last30',this)">Last 30 Days</li>
                <li class="dr-opt" onclick="drPickPreset('db-dr','thisMonth',this)">This Month</li>
                <li class="dr-opt" onclick="drPickPreset('db-dr','lastMonth',this)">Last Month</li>
                <li class="dr-opt" onclick="drPickPreset('db-dr','last90',this)">Last 90 Days</li>
                <li class="dr-opt" onclick="drPickPreset('db-dr','last6m',this)">Last 6 Months</li>
                <li class="dr-opt" onclick="drPickPreset('db-dr','last1y',this)">Last 1 Year</li>
                <li class="dr-opt" onclick="drPickPreset('db-dr','custom',this)">Custom Range</li>
            </ul>
            <div class="dr-custom-row" id="db-dr-custom">
                <input type="date" class="dr-date-inp" id="db-dr-from" value="{{ $startDateInput }}">
                <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                <input type="date" class="dr-date-inp" id="db-dr-to" value="{{ $endDateInput }}">
                <button class="dr-apply-btn" onclick="drApplyCustom('db-dr')">Apply</button>
            </div>
        </div>
    </div>
    <span style="font-size:12px;color:var(--text-3)">
        Showing: <strong>{{ $periodLabel }}</strong>
    </span>
</div>

{{-- KPI Row 1: 3 cards --}}
<div class="db-kpi-grid cols-3">

    {{-- Total Clients --}}
    <div class="db-kpi bl">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Total Clients</span>
            <div class="db-kpi-ico ico-bl">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
        </div>
        <div class="db-kpi-val">{{ $clientCount }}</div>
        <div class="db-kpi-ctx">Active clients in portfolio</div>
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
        <div class="db-kpi-ctx">Client companies in portfolio</div>
    </div>

    {{-- Connected --}}
    <div class="db-kpi gr">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Connected</span>
            <div class="db-kpi-ico ico-gr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
            </div>
        </div>
        <div class="db-kpi-val {{ $qbConnectedCount > 0 ? 'green' : '' }}">
            {{ $qbConnectedCount }}<span class="sub-val"> / {{ $companyCount }}</span>
        </div>
        <div class="db-kpi-ctx">Companies with accounting tool connected</div>
    </div>

</div>

{{-- KPI Row 2: 3 cards --}}
<div class="db-kpi-grid cols-3" style="margin-bottom:18px">

    {{-- Insights Used --}}
    <div class="db-kpi gr">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Insights Used</span>
            <div class="db-kpi-ico ico-gr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
        </div>
        <div class="db-kpi-val green">{{ $insightsGenerated ?? 0 }}</div>
        <div class="db-kpi-ctx">Insights generated this month</div>
    </div>

    {{-- Insights Remaining --}}
    <div class="db-kpi or">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Insights Remaining</span>
            <div class="db-kpi-ico ico-or">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
        </div>
        @php
            $insAvail = $insightsAvailable ?? 0;
            $insPkg   = $insightPlanLimit ?? ($subscription?->clientPackage?->insights_per_month
                ?? $subscription?->package?->max_insights_per_month
                ?? 0);
        @endphp
        <div class="db-kpi-val orange">{{ $insAvail }}</div>
        <div class="db-kpi-ctx">Of {{ $insPkg }} total · resets {{ now()->endOfMonth()->format(active_date_format()) }}</div>
    </div>

    {{-- Pending Review --}}
    <div class="db-kpi or">
        <div class="db-kpi-head">
            <span class="db-kpi-lbl">Pending Review</span>
            <div class="db-kpi-ico ico-or">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="db-kpi-val orange">{{ $pendingReview ?? 0 }}</div>
        <div class="db-kpi-ctx">Drafts pending review</div>
    </div>

</div>

{{-- Bottom row: Chart + Activity --}}
<div class="db-bottom">

    {{-- Usage Overview chart --}}
    <div class="db-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px">
            <div>
                <div class="db-card-title">Usage Overview</div>
                <div class="db-card-sub">Insights generated · last 6 months</div>
            </div>
        </div>
        <canvas id="usageChart" height="160"></canvas>
    </div>

    {{-- Recent Activity --}}
    <div class="db-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div class="db-card-title">Recent Activity</div>
                <div class="db-card-sub" style="margin-bottom:0">Your 5 most recent actions</div>
            </div>
            <a href="{{ route('activity.index') }}" style="font-size:12.5px;color:var(--blue);font-weight:500;white-space:nowrap">View all →</a>
        </div>

        @forelse($recentActivities as $log)
        @php
            $dotColor = match(true) {
                str_contains($log->event_type, 'quickbooks') => 'var(--green)',
                str_contains($log->event_type, 'insight')    => 'var(--teal, #0d9488)',
                str_contains($log->event_type, 'login')      => 'var(--blue)',
                str_contains($log->event_type, 'ticket')     => 'var(--orange)',
                str_contains($log->event_type, 'subscription') => '#8b5cf6',
                default                                      => 'var(--blue)',
            };
        @endphp
        <div class="act-item">
            <div class="act-dot" style="background:{{ $dotColor }}"></div>
            <div class="act-txt">{{ $log->display_description }}</div>
            <div class="act-time">{{ $log->created_at->diffForHumans() }}</div>
        </div>
        @empty
        <div style="text-align:center;padding:32px 0;color:var(--text-3)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" style="display:block;margin:0 auto 8px;stroke:var(--text-3)"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <div style="font-size:13px">No recent activity yet</div>
        </div>
        @endforelse
    </div>

</div>

@endif {{-- end @if($isFreshUser) / @else --}}

{{-- View Client Portal Modal --}}

<div id="viewClientModal" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:520px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.18);overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px 16px;border-bottom:1px solid var(--border)">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text-1)">View as Client</div>
                <div style="font-size:12.5px;color:var(--text-3);margin-top:2px">Select a client to preview their portal experience</div>
            </div>
            <button onclick="closeViewClientModal()" style="border:none;background:none;cursor:pointer;color:var(--text-3);padding:4px;line-height:1;border-radius:6px" title="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="padding:0 22px;max-height:380px;overflow-y:auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:1px solid var(--border)">
                        <th style="text-align:left;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);padding:12px 0 10px">Client Name</th>
                        <th style="text-align:left;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);padding:12px 8px 10px">Company</th>
                        <th style="text-align:left;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);padding:12px 8px 10px">Status</th>
                        <th style="text-align:center;font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);padding:12px 0 10px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modalClients as $mc)
                    @php
                        $cid    = $mc->display_company_id ?? $mc->company_id;
                        $status = $companyPublishStatus[$cid] ?? null;
                    @endphp
                    <tr style="border-bottom:1px solid var(--bg)">
                        <td style="padding:10px 0;font-size:13px;color:var(--text-1);font-weight:500">{{ $mc->name }}</td>
                        <td style="padding:10px 8px;font-size:13px;color:var(--text-2);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $mc->display_company_name ?? $mc->company?->company_name ?? '' }}">{{ $mc->display_company_name ?? $mc->company?->company_name ?? '—' }}</td>
                        <td style="padding:10px 8px">
                            @if($status === 'Published')
                                <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:rgba(16,185,129,.12);color:#059669">Published</span>
                            @elseif($status === 'Draft')
                                <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:rgba(240,107,33,.12);color:var(--orange)">Draft</span>
                            @else
                                <span style="font-size:13px;color:var(--text-3)">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 0;text-align:center">
                            <form method="POST" action="{{ route('clients.login-as', $mc->id) }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="redirect_to" value="/dashboard">
                                <button type="submit" style="display:inline-flex;align-items:center;gap:5px;padding:5px 14px;background:var(--orange);color:#fff;border-radius:7px;font-size:12px;font-weight:600;border:none;cursor:pointer;white-space:nowrap">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    View
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 22px 16px;border-top:1px solid var(--border);margin-top:2px;text-align:right">
            <button onclick="closeViewClientModal()" class="btn btn-secondary btn-sm">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.chartjs')) }}"></script>
<script>
const CURRENT_PERIOD = '{{ $period }}';

function setTP(el, period) {
    if (period === 'custom') {
        document.querySelectorAll('.tp').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        var row = document.getElementById('customRangeRow');
        row.classList.add('visible');
        // Auto-open the date picker
        setTimeout(function() { drOpen('db-dr'); }, 50);
        return;
    }
    window.location.href = '{{ url()->current() }}?period=' + period;
}

function applyCustomRange(from, to) {
    var start = from !== undefined ? from : (document.getElementById('db-dr-from') ? document.getElementById('db-dr-from').value : '');
    var end   = to   !== undefined ? to   : (document.getElementById('db-dr-to')   ? document.getElementById('db-dr-to').value   : '');
    // Cleared (× button) — reset to default period silently
    if (!start && !end) {
        window.location.href = '{{ url()->current() }}?period=1M';
        return;
    }
    if (!start || !end) {
        Swal.fire({ icon: 'warning', title: 'Select both dates', text: 'Please select both a start and end date.', confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21' });
        return;
    }
    if (start > end) {
        Swal.fire({ icon: 'warning', title: 'Invalid range', text: 'Start date must be before end date.', confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21' });
        return;
    }
    window.location.href = '{{ url()->current() }}?period=custom&start_date=' + start + '&end_date=' + end;
}

// Register DR picker callback — navigates with custom period params
_drCbs['db-dr'] = function(from, to) { applyCustomRange(from, to); };

// Show custom range row if currently active and pre-populate label
if (CURRENT_PERIOD === 'custom') {
    document.getElementById('customRangeRow').classList.add('visible');
    var _dbFrom = '{{ $startDateInput }}';
    var _dbTo   = '{{ $endDateInput }}';
    if (_dbFrom || _dbTo) {
        document.addEventListener('DOMContentLoaded', function() {
            drSetLabel('db-dr', _dbFrom, _dbTo);
            // Also mark Custom Range option as active
            var customOpt = document.querySelector('#db-dr .dr-opt:last-child');
            if (customOpt) customOpt.classList.add('active');
            var customRow = document.getElementById('db-dr-custom');
            if (customRow) customRow.style.display = 'flex';
        });
    }
}

// View Client Portal Modal
function openViewClientModal() {
    document.getElementById('viewClientModal').style.display = 'flex';
}

function closeViewClientModal() {
    document.getElementById('viewClientModal').style.display = 'none';
}

document.getElementById('viewClientModal')?.addEventListener('click', function (e) {
    if (e.target === this) closeViewClientModal();
});

// Chart
const ctx = document.getElementById('usageChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Insights',
                data: {!! json_encode($chartInsights) !!},
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
                legend: { display: false }
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
