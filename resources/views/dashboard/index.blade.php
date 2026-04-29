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

@php
    $canAddClient = ($brandingDone ?? false) && ($settingsDone ?? false);
    $disabledTip  = 'The Add Client option will be enabled once Step 1: Branding Setup and Step 2: Settings Configuration are completed.';
@endphp

@if($isFreshUser)

{{-- ══════════════════════════════════════════════════════
     FRESH USER — ONBOARDING DASHBOARD
     ══════════════════════════════════════════════════════ --}}

{{-- Page Header --}}
<div class="ph" style="margin-bottom:20px">
    <div class="ph-left">
        <h1>Dashboard</h1>
        <p>Your partner portal is live — let's get you set up</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('account.index') . '#branding' }}" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            Branding Settings
        </a>
        @if($canAddClient)
        <button type="button" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px" onclick="openDashboardAddClientModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
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
        <div class="ob-title">Welcome, {{ auth()->user()->first_name }}.</div>
        <p class="ob-sub">Complete the setup below to configure your partner portal and start onboarding clients.</p>
    </div>
    <div class="ob-banner-btns">
        @if($canAddClient)
        <button type="button" class="btn btn-primary" style="display:flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap" onclick="openDashboardAddClientModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
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

{{-- 3 Setup Steps --}}
@php
    $activeStep = !($brandingDone ?? false) ? 1 : (!($settingsDone ?? false) ? 2 : 3);
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

    {{-- Step 2: Configure Settings — entire card is clickable --}}
    <a href="{{ route('settings.index') }}" class="ob-step {{ $activeStep === 2 ? 'active' : '' }}" style="text-decoration:none;display:flex;flex-direction:column">
        @if($activeStep === 2)
        <div class="ob-step-badge">START HERE</div>
        @elseif($settingsDone)
        <div class="ob-step-badge" style="background:var(--green)">✓ DONE</div>
        @endif

        <div class="ob-step-ico" style="background:#ecfdf5">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8" width="22" height="22"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        </div>
        <div class="ob-step-num" style="color:{{ $activeStep === 2 ? '#10b981' : 'var(--text-3)' }}">STEP 2</div>
        <div class="ob-step-title">Configure Settings</div>
        <div class="ob-step-desc">Set up your <strong>Payment Gateway</strong>, configure <strong>Email &amp; SMS</strong> for client communications, and create your <strong>Client Plans</strong> before onboarding anyone.</div>
        <span class="ob-step-link" style="color:#10b981">Go to Settings →</span>
    </a>

    {{-- Step 3: Add Your Clients — clickable only when steps 1 & 2 are done --}}
    @if($canAddClient)
    <a href="{{ route('clients.index') }}" class="ob-step {{ $activeStep === 3 ? 'active' : '' }}" style="text-decoration:none;display:flex;flex-direction:column">
        @if($activeStep === 3)
        <div class="ob-step-badge">START HERE</div>
        @endif

        <div class="ob-step-ico" style="background:#ecfdf5">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8" width="22" height="22"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </div>
        <div class="ob-step-num" style="color:#10b981">STEP 3</div>
        <div class="ob-step-title">Add Your Clients</div>
        <div class="ob-step-desc">Add the individuals your portal serves — one at a time or bulk-import via CSV. Once added, each client receives a personalised portal invitation automatically.</div>
        <span class="ob-step-link" style="color:#10b981">Add Client →</span>
    </a>
    @else
    <div class="ob-step cta-disabled-wrap" style="cursor:not-allowed;display:flex;flex-direction:column">
        <div class="ob-step-ico" style="background:#ecfdf5">
            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8" width="22" height="22"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </div>
        <div class="ob-step-num" style="color:var(--text-3)">STEP 3</div>
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
            Last updated: {{ now()->format('M j, Y · g:i A') }}
        </span>
        @if($canAddClient)
        <button type="button" class="btn btn-primary btn-sm" onclick="openDashboardAddClientModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
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
            foreach(($recentActivities ?? collect()) as $activity) {
                $eventType = $activity->event_type ?? 'unknown';
                $color = match(true) {
                    str_contains($eventType, 'login')  => 'var(--green)',
                    str_contains($eventType, 'delete') => 'var(--red)',
                    str_contains($eventType, 'create') => 'var(--blue)',
                    str_contains($eventType, 'update') => 'var(--orange)',
                    default                             => 'var(--text-3)',
                };

                $actItems->push([
                    'color' => $color,
                    'text'  => $activity->display_event,
                    'time'  => $activity->created_at?->diffForHumans() ?? '',
                ]);
            }
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

@endif {{-- end @if($isFreshUser) / @else --}}

{{-- Add Client Modal (Dashboard) --}}
<div class="modal-overlay {{ $errors->any() ? 'open' : '' }}" id="modal-dashboard-add-client" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:560px">
        <button class="modal-close" onclick="document.getElementById('modal-dashboard-add-client').classList.remove('open')">&times;</button>
        <div class="modal-title">Add New Client</div>
        <div class="modal-sub">Invite a new client to your partner portal</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;background:var(--bg);border-radius:10px;padding:4px;margin-bottom:20px;gap:0">
            <button type="button" id="dash-tab-manual-btn" onclick="switchDashboardAddClientTab('manual')"
                style="border:none;border-radius:8px;padding:9px 0;font-size:13px;font-weight:600;cursor:pointer;background:var(--orange);color:#fff;transition:all .15s">
                Manual Entry
            </button>
            <button type="button" id="dash-tab-bulk-btn" onclick="switchDashboardAddClientTab('bulk')"
                style="border:none;border-radius:8px;padding:9px 0;font-size:13px;font-weight:600;cursor:pointer;background:transparent;color:var(--text-2);transition:all .15s">
                Bulk Upload
            </button>
        </div>

        @if($errors->any())
        <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 13px;margin-bottom:14px">
            @foreach($errors->all() as $error)
            <div style="font-size:12.5px;color:var(--red);display:flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ $error }}
            </div>
            @endforeach
        </div>
        @endif

        <div id="dash-panel-manual">
            <form method="POST" action="{{ route('clients.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Full Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="name" class="mf-inp" placeholder="e.g. Sarah Chen" value="{{ old('name') }}" required>
                    </div>
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Email ID <span style="color:var(--red)">*</span></label>
                        <input type="email" name="email" class="mf-inp" placeholder="sarah@chen.com" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Phone Number</label>
                        <input type="tel" name="phone" class="mf-inp" placeholder="+1 (555) 000-0000" value="{{ old('phone') }}">
                    </div>
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Company Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="company_name" class="mf-inp" placeholder="e.g. TechCorp Inc." value="{{ old('company_name') }}" required>
                    </div>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Industry Type <span style="color:var(--red)">*</span></label>
                    <select name="industry_type" class="mf-sel" style="height:40px" required>
                        <option value="" disabled {{ old('industry_type') ? '' : 'selected' }}>Select Industry Type</option>
                        @foreach(['Technology','Healthcare','Finance','Manufacturing','Retail','Real Estate','Construction','Transportation','Energy','Agriculture','Education','Consulting','Legal Services','Entertainment','Automotive','Other'] as $ind)
                        <option value="{{ $ind }}" {{ old('industry_type') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Plan Tier</label>
                    <select name="client_package_id" class="mf-sel" style="height:40px">
                        <option value="">— No Plan —</option>
                        @foreach($clientPackages ?? [] as $pkg)
                        <option value="{{ $pkg->id }}" {{ old('client_package_id') == $pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }}@if($pkg->max_documents) — {{ $pkg->max_documents }} insights / company / month @endif
                        </option>
                        @endforeach
                    </select>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Plan tiers are managed by the CFO Insights platform.</div>
                </div>
                <div class="mf-field">
                    <label class="mf-label" style="display:flex;align-items:center;justify-content:space-between">
                        <span>Password <span style="color:var(--red)">*</span></span>
                    </label>
                    <div style="position:relative;display:flex;align-items:center">
                        <input type="password" name="password" id="dash-ac-password" class="mf-inp"
                            placeholder="Min. 8 characters" style="padding-right:88px" required>
                        <button type="button" title="Show/hide password"
                            onclick="toggleDashboardAcPassword()"
                            style="position:absolute;right:44px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--text-3);display:flex;align-items:center">
                            <svg id="dash-ac-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button type="button" title="Auto-generate password"
                            onclick="generateDashboardAcPassword()"
                            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--blue);display:flex;align-items:center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                        </button>
                    </div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px">Click <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11" style="vertical-align:middle"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg> to auto-generate a secure password.</div>
                </div>
                <div style="background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:12.5px;color:#3b82f6;line-height:1.5">
                    An email invite will be sent to the client automatically after submission.
                </div>
                <div class="mf-foot">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-dashboard-add-client').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Client</button>
                </div>
            </form>
        </div>

        <div id="dash-panel-bulk" style="display:none">
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:10px">Upload CSV File</div>
            <div id="dash-csv-drop-zone"
                style="border:2px dashed #c7d9f0;border-radius:12px;background:rgba(59,130,246,.04);padding:36px 20px;text-align:center;cursor:pointer;transition:border-color .15s"
                onclick="document.getElementById('dash-csv-file-input').click()"
                ondragover="event.preventDefault();this.style.borderColor='var(--blue)'"
                ondragleave="this.style.borderColor='#c7d9f0'"
                ondrop="handleDashboardCsvDrop(event)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.8" width="32" height="32" style="margin:0 auto 10px;display:block">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <div style="font-size:13.5px;color:var(--text-2)">
                    Drag &amp; drop your CSV here, or <span style="color:var(--blue);font-weight:500;text-decoration:underline">click to browse</span>
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:6px">.csv files only · Max 5 MB · Up to 200 clients per upload</div>
                <input type="file" id="dash-csv-file-input" accept=".csv" style="display:none" onchange="handleDashboardCsvFile(this.files[0])">
            </div>
            <div id="dash-csv-file-name" style="font-size:12px;color:var(--green);margin-top:8px;display:none"></div>

            <div style="margin-top:18px">
                <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:10px">CSV Format</div>
                <div style="border:1px solid var(--border);border-radius:10px;padding:14px 16px">
                    <div style="font-size:12.5px;color:var(--text-2);margin-bottom:10px">Your CSV must include the following columns in order:</div>
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:10px 14px;font-family:monospace;font-size:13px;color:var(--text-1)">
                        client_name, email, plan_tier
                    </div>
                    <div style="font-size:12px;color:var(--text-3);margin-top:10px">
                        Example: <em>TechCorp Inc., info@techcorp.com, Professional</em>
                    </div>
                    <a href="javascript:void(0)" onclick="downloadDashboardSampleCsv()" style="display:inline-flex;align-items:center;gap:5px;font-size:12.5px;color:var(--blue);margin-top:10px;text-decoration:none;font-weight:500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download sample CSV
                    </a>
                </div>
            </div>

            <div style="background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:11px 14px;margin-top:16px;margin-bottom:16px;font-size:12.5px;color:#3b82f6;line-height:1.5">
                All clients in the CSV will receive an email invite automatically after upload.
            </div>

            <div id="dash-bulk-error" style="display:none;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 13px;margin-bottom:14px;font-size:12.5px;color:var(--red)"></div>

            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-dashboard-add-client').classList.remove('open')">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitDashboardBulkUpload()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload &amp; Invite
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.chartjs')) }}"></script>
<script>
const CURRENT_PERIOD = '{{ $period }}';
const HAS_DASHBOARD_CLIENT_FORM_ERRORS = {{ $errors->any() ? 'true' : 'false' }};

function openDashboardAddClientModal() {
    const modal = document.getElementById('modal-dashboard-add-client');
    if (modal) modal.classList.add('open');
}

function switchDashboardAddClientTab(tab) {
    var isManual = tab === 'manual';
    document.getElementById('dash-panel-manual').style.display = isManual ? '' : 'none';
    document.getElementById('dash-panel-bulk').style.display   = isManual ? 'none' : '';
    var manualBtn = document.getElementById('dash-tab-manual-btn');
    var bulkBtn   = document.getElementById('dash-tab-bulk-btn');
    manualBtn.style.background = isManual ? 'var(--orange)' : 'transparent';
    manualBtn.style.color      = isManual ? '#fff' : 'var(--text-2)';
    bulkBtn.style.background   = isManual ? 'transparent' : 'var(--orange)';
    bulkBtn.style.color        = isManual ? 'var(--text-2)' : '#fff';
}

function toggleDashboardAcPassword() {
    var inp  = document.getElementById('dash-ac-password');
    var icon = document.getElementById('dash-ac-eye-icon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        inp.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

function generateDashboardAcPassword() {
    var chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    var pwd = '';
    for (var i = 0; i < 12; i++) pwd += chars[Math.floor(Math.random() * chars.length)];
    var inp = document.getElementById('dash-ac-password');
    inp.type  = 'text';
    inp.value = pwd;
    document.getElementById('dash-ac-eye-icon').innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
}

var _dashboardCsvFile = null;
function handleDashboardCsvFile(file) {
    if (!file) return;
    _dashboardCsvFile = file;
    var el = document.getElementById('dash-csv-file-name');
    el.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    el.style.display = 'block';
    document.getElementById('dash-csv-drop-zone').style.borderColor = 'var(--green)';
}

function handleDashboardCsvDrop(e) {
    e.preventDefault();
    document.getElementById('dash-csv-drop-zone').style.borderColor = '#c7d9f0';
    var file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) handleDashboardCsvFile(file);
}

function downloadDashboardSampleCsv() {
    var csv = 'client_name,email,plan_tier\nTechCorp Inc.,info@techcorp.com,Professional\nAcme Corp,hello@acme.com,Starter\n';
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'sample_clients.csv';
    a.click();
}

function submitDashboardBulkUpload() {
    var errBox = document.getElementById('dash-bulk-error');
    errBox.style.display = 'none';
    if (!_dashboardCsvFile) {
        errBox.textContent = 'Please select a CSV file first.';
        errBox.style.display = 'block';
        return;
    }
    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('csv_file', _dashboardCsvFile);
    fetch('{{ route("clients.bulk-upload") }}', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(function(data) {
            if (data.status === 'success') {
                window.location.href = '{{ route("clients.index") }}';
            } else {
                errBox.textContent = data.message || 'Upload failed.';
                errBox.style.display = 'block';
            }
        })
        .catch(function() {
            errBox.textContent = 'Network error. Please try again.';
            errBox.style.display = 'block';
        });
}

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

if (HAS_DASHBOARD_CLIENT_FORM_ERRORS) {
    openDashboardAddClientModal();
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
