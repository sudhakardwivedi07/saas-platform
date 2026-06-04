@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')

@if(!empty($isAdminView))
@php
    $adminViewUser = $viewingAsUser ?? auth()->user();

    // Preload per-company dashboard settings FIRST so admin bar can reference them
    $kpiCoSettingsMap = [];
    foreach(($kpiCompanies ?? collect()) as $__co) {
        $__s = \App\Models\ClientDashboardSetting::forCompany($__co->id);
        $kpiCoSettingsMap[$__co->id] = [
            'name'           => $__co->company_name,
            'hidden_kpis'    => array_values($__s->draft_hidden_kpis ?? []),
            'publish_status' => $__s->publish_status ?? 'published',
            'has_insights'   => \App\Models\Report::where('company_id', $__co->id)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
                ->exists(),
        ];
    }

    // Admin bar should reflect the SELECTED entity, not the client's primary company
    $_selCoId     = $selectedKpiCompanyId ?? null;
    $adminCompany = $_selCoId
        ? (($kpiCompanies ?? collect())->firstWhere('id', $_selCoId) ?? $adminViewUser->company)
        : $adminViewUser->company;
    $adminPubStatus = ($_selCoId && isset($kpiCoSettingsMap[$_selCoId]))
        ? $kpiCoSettingsMap[$_selCoId]['publish_status']
        : ($dashboardPublishStatus ?? 'published');
@endphp
<style>
/* ── Admin action toast ───────────────────────────────────────────── */
.cdb-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    box-shadow: 0 8px 28px rgba(0,0,0,.18);
    transform: translateY(16px);
    opacity: 0;
    transition: opacity .2s ease, transform .2s ease;
    pointer-events: none;
}
.cdb-toast.show { opacity: 1; transform: translateY(0); }
.cdb-toast.success { background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; }
.cdb-toast.error   { background: #fef2f2; color: #991b1b; border: 1.5px solid #fca5a5; }

.cdb-admin-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    background: #1a2b45;
    border-radius: 10px;
    padding: 10px 18px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.cdb-exit-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: rgba(255,255,255,.7);
    font-size: 12.5px;
    font-weight: 500;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,.15);
    transition: background .12s;
    flex-shrink: 0;
}
.cdb-exit-btn:hover { background: rgba(255,255,255,.08); color: #fff; }
.cdb-client-info {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.cdb-client-name {
    font-size: 13.5px;
    font-weight: 600;
    color: #fff;
}
.cdb-company-name {
    font-size: 13px;
    color: rgba(255,255,255,.6);
}
.cdb-sep { color: rgba(255,255,255,.3); font-size: 12px; }
.cdb-status-badge {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    padding: 2px 9px;
    border-radius: 20px;
}
.cdb-status-draft      { background: rgba(240,107,33,.25); color: #f97316; }
.cdb-status-published  { background: rgba(16,185,129,.2);  color: #10b981; }
.cdb-bar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.cdb-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: opacity .12s, background .12s;
    text-decoration: none;
}
.cdb-btn:hover { opacity: .88; }
.cdb-btn-edit   { background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.2); }
.cdb-btn-edit.active { background: var(--orange); border-color: var(--orange); }
.cdb-btn-draft  { background: rgba(255,255,255,.1); color: rgba(255,255,255,.8); border: 1px solid rgba(255,255,255,.15); }
.cdb-btn-publish { background: var(--orange); color: #fff; }

/* ── KPI Eye Icon ─────────────────────────────────────── */
.kpi-eye-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0,0,0,.06);
    border: none;
    cursor: pointer;
    padding: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    line-height: 1;
    z-index: 5;
    display: none;
    align-items: center;
    justify-content: center;
    transition: background .15s;
}
/* Only visible in edit mode */
.kpi-edit-active .kpi-eye-btn { display: flex; }
.kpi-edit-active .kpi-eye-btn:hover { background: rgba(0,0,0,.12); }
.kpi-card { position: relative; }
/* Hidden cards are completely invisible outside edit mode */
.kpi-card.kpi-hidden-draft { display: none; }
/* Hide entire section block when all its KPI cards are draft-hidden */
.kpi-tier-block.kpi-section-all-hidden { display: none; }
/* In edit mode: keep section visible so admin can still toggle cards */
.kpi-edit-active .kpi-tier-block.kpi-section-all-hidden { display: block; }
/* In edit mode: show greyed out so admin can toggle visibility */
.kpi-edit-active .kpi-card.kpi-hidden-draft {
    display: block;
    opacity: .42;
    outline: 2px dashed rgba(240,107,33,.5);
    outline-offset: -2px;
}
.kpi-edit-active .kpi-card.kpi-hidden-draft::after {
    content: 'Hidden from client';
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--orange);
    white-space: nowrap;
    pointer-events: none;
}
</style>
<div id="cdbToast" class="cdb-toast" role="status" aria-live="polite"></div>
<div class="cdb-admin-bar" id="cdbAdminBar">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <a href="{{ $backUrl ?? route('dashboard') }}" class="cdb-exit-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="15 18 9 12 15 6"/></svg>
            Exit
        </a>
        <div class="cdb-client-info">
            <span class="cdb-client-name">{{ $adminViewUser->name }}</span>
            <span class="cdb-sep">·</span>
            <span class="cdb-company-name">{{ $adminCompany?->company_name ?? '' }}</span>
            <span id="cdbStatusBadge" class="cdb-status-badge cdb-status-{{ $adminPubStatus }}">{{ ucfirst($adminPubStatus) }}</span>
        </div>
    </div>
    @if(!empty($showKpiDashboard))
    <div class="cdb-bar-right">
        <button class="cdb-btn cdb-btn-edit" id="cdbEditModeBtn" onclick="cdbToggleEditMode()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Mode
        </button>
        <button class="cdb-btn cdb-btn-draft" id="cdbSaveDraftBtn" onclick="cdbSaveDraft()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Draft
        </button>
        <button class="cdb-btn cdb-btn-publish" id="cdbPublishBtn" onclick="cdbPublish()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Publish
        </button>
    </div>
    @endif
</div>

{{-- Edit Mode confirmation modal --}}
<div id="cdbEditConfirmModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:400px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);padding:28px 28px 22px;text-align:center">
        <div style="width:48px;height:48px;border-radius:50%;background:#fff3ec;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2" width="22" height="22"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </div>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:8px">Enable Edit Mode?</div>
        <div style="font-size:13px;color:#6b7280;line-height:1.55;margin-bottom:22px">You are about to enter edit mode. You can toggle KPI visibility and save or publish changes to the client dashboard.</div>
        <div style="display:flex;gap:10px;justify-content:center">
            <button onclick="cdbCancelEditMode()" style="flex:1;padding:9px 16px;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;cursor:pointer">Cancel</button>
            <button onclick="cdbConfirmEditMode()" style="flex:1;padding:9px 16px;border:none;border-radius:8px;background:var(--orange);color:#fff;font-size:13px;font-weight:600;cursor:pointer">Enable Edit Mode</button>
        </div>
    </div>
</div>
@endif

<div id="insights-refresh-target" data-refresh="1" style="display:none"></div>

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
.plan-card--current {
    border-color: rgba(240, 107, 33, .45);
    box-shadow: 0 10px 28px rgba(240, 107, 33, .08);
}
.plan-card--available {
    border-color: #e5e7eb;
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
.plan-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

.mt-auto { margin-top: auto; }

/* ── Generating Loader ──────────────────────────────────────────── */
.dash-gen-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 11px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    color: #1a6aa8;
    background: #e8f2fb;
    border: 1.5px solid #b3d4f0;
    cursor: not-allowed;
    user-select: none;
}
.dash-gen-spinner {
    width: 16px; height: 16px;
    border: 2.5px solid #b3d4f0;
    border-top-color: #1a6aa8;
    border-radius: 50%;
    animation: dashSpin 0.75s linear infinite;
    flex-shrink: 0;
}
@keyframes dashSpin { to { transform: rotate(360deg); } }

/* ── Industry Modal ─────────────────────────────────────────────── */
.ind-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 9999; display: none; align-items: center; justify-content: center;
}
.ind-modal-overlay.open { display: flex; }
.ind-modal {
    background: white; border-radius: 20px;
    padding: 36px 36px 28px; width: 480px; max-width: 95vw;
    text-align: center; position: relative;
    animation: indSlide .25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
@keyframes indSlide { from { opacity: 0; transform: translateY(-16px); } to { opacity: 1; transform: translateY(0); } }
.ind-modal h3 { font-size: 22px; font-weight: 700; color: #1a202c; margin: 0 0 10px; }
.ind-modal p  { font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0 0 20px; }
.ind-modal select {
    width: 100%; padding: 11px 14px; border: 1.5px solid #e2e8f0;
    border-radius: 8px; font-size: 14px; font-family: inherit;
    color: #1a202c; background: #fff; outline: none; cursor: pointer;
    margin-bottom: 20px; transition: border-color .2s;
}
.ind-modal select:focus { border-color: var(--orange); }
.ind-modal-actions { display: flex; gap: 12px; }
.ind-btn-confirm {
    flex: 1; background: var(--orange); color: white; border: none;
    padding: 13px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: background .2s;
}
.ind-btn-confirm:hover { background: var(--orange-dark); }
.ind-btn-confirm:disabled { opacity: .6; cursor: not-allowed; }
.ind-btn-cancel {
    flex: 1; background: transparent; color: var(--orange);
    border: 1.5px solid var(--orange); padding: 12px 20px; border-radius: 10px;
    font-size: 14px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.ind-btn-cancel:hover { background: #fff4ee; }

/* ── Free Report Limit Reached Modal ───────────────────────────── */
.limit-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 10002; display: none; align-items: center; justify-content: center;
}
.limit-modal-overlay.open { display: flex; }
.limit-modal {
    background: white; border-radius: 20px;
    padding: 40px 40px 32px; width: 460px; max-width: 95vw;
    text-align: center;
    animation: limitSlide .25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
@keyframes limitSlide { from { opacity: 0; transform: translateY(-16px); } to { opacity: 1; transform: translateY(0); } }
.limit-modal-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: #e0f2fe; display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
}
.limit-modal h3 { font-size: 22px; font-weight: 700; color: #1a202c; margin: 0 0 12px; }
.limit-modal p  { font-size: 14.5px; color: #6b7280; line-height: 1.65; margin: 0 0 28px; }
.limit-modal-actions { display: flex; gap: 12px; }
.limit-btn-subscribe {
    flex: 1; background: var(--orange); color: white; border: none;
    padding: 14px 20px; border-radius: 10px; font-size: 14.5px; font-weight: 600;
    cursor: pointer; transition: background .2s; text-decoration: none;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 5px 10px rgba(240,107,37,.3);
}
.limit-btn-subscribe:hover { background: var(--orange-dark); color: white; text-decoration: none; }
.limit-btn-later {
    flex: 1; background: transparent; color: var(--orange);
    border: 1.5px solid var(--orange); padding: 13px 20px; border-radius: 10px;
    font-size: 14.5px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.limit-btn-later:hover { background: #fff4ee; }
/* ── QB Confirm Modal ───────────────────────────────────────────── */
.qb-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 9998; display: none; align-items: center; justify-content: center;
}
.qb-modal-overlay.open { display: flex; }
.qb-modal {
    background: white; border-radius: 20px;
    padding: 40px 40px 32px; width: 520px; max-width: 95vw;
    text-align: center; position: relative;
    animation: qbSlide .25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
@keyframes qbSlide { from { opacity: 0; transform: translateY(-18px); } to { opacity: 1; transform: translateY(0); } }
.qb-modal h3 { font-size: 24px; font-weight: 700; color: #1a202c; margin: 0 0 12px; }
.qb-modal .qb-modal-sub { font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0 0 4px; }
.qb-modal ul { list-style: none; padding: 0; margin: 12px auto 10px; text-align: left; display: inline-block; }
.qb-modal ul li { font-size: 15px; color: #374151; padding: 3px 0; display: flex; align-items: center; gap: 10px; }
.qb-modal ul li::before { content: '○'; color: #6b7280; flex-shrink: 0; }
.qb-modal .qb-modal-timing { font-size: 15px; font-weight: 700; color: var(--orange); margin: 10px 0 24px; }
.qb-modal .qb-modal-actions { display: flex; flex-direction: row; gap: 12px; }
.qb-modal .btn-modal-confirm {
    flex: 1; background: var(--orange); color: white; border: none;
    padding: 16px 20px; border-radius: 10px; font-size: 15px; font-weight: 600;
    cursor: pointer; transition: background .2s;
    box-shadow: 0 5px 10px rgba(240,107,37,.3);
}
.qb-modal .btn-modal-confirm:hover { background: var(--orange-dark); }
.qb-modal .btn-modal-cancel {
    flex: 1; background: transparent; color: var(--orange);
    border: 1.5px solid var(--orange); padding: 15px 20px; border-radius: 10px;
    font-size: 15px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.qb-modal .btn-modal-cancel:hover { background: #fff4ee; }

/* ── Executive KPI header (replaces plan grid) ─────────────────────────── */
.kpi-exec-bar {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;
    margin-bottom: 20px; padding: 16px 18px; background: #fff; border: 1px solid var(--border); border-radius: 10px;
}
.kpi-exec-bar .kpi-exec-meta h2 { font-size: 18px; font-weight: 700; color: var(--text-1); margin: 0 0 4px; }
.kpi-exec-bar .kpi-exec-meta p { font-size: 12.5px; color: var(--text-3); margin: 0; }
.kpi-empty-card {
    background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 32px; text-align: center; max-width: 560px;
}
.kpi-empty-card h3 { font-size: 16px; font-weight: 600; margin: 0 0 8px; color: var(--text-1); }
.kpi-empty-card p { font-size: 13px; color: var(--text-3); line-height: 1.6; margin: 0 0 16px; }

/* ── Classic three-column dashboard (no KPI data yet) ───────────────────── */
.classic-dash-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    align-items: stretch;
}
@media (max-width: 1100px) {
    .classic-dash-grid { grid-template-columns: 1fr; }
}
.classic-dash-grid--empty-plans {
    grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
}
.classic-dash-grid--single-plan {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
}
@media (max-width: 1100px) {
    .classic-dash-grid--empty-plans,
    .classic-dash-grid--single-plan { grid-template-columns: 1fr; }
}
.classic-dash-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    min-height: 100%;
}
.classic-dash-card .classic-lead {
    font-size: 13px;
    color: var(--text-2);
    line-height: 1.65;
    margin: 0 0 18px;
}
.classic-stat-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: auto;
    padding-top: 16px;
}
.classic-stat-box {
    background: #f4f5f7;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
}
.classic-stat-box .lbl {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .08em;
    color: var(--text-3);
    margin-bottom: 4px;
}
.classic-stat-box .val {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-1);
}
.classic-price-strip {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    color: var(--text-2);
    margin: 14px 0 0;
    line-height: 1.45;
}
.classic-dash-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-top: 12px;
}
</style>

@if(!empty($showKpiDashboard))
<div class="user-kpi-fullbleed">
@php
    $__kpiCoList     = $kpiCompanies ?? collect();
    $__selectedKpiCo = $__kpiCoList->firstWhere('id', $selectedKpiCompanyId ?? null);
@endphp
<div class="cfo-kpi-exec-bar">

    {{-- Left: entity / company selector ---------------------------------------- --}}
    <div class="cfo-kpi-exec-entity">
        <span class="cfo-kpi-exec-entity-label">Selected Entity</span>
        @if($__kpiCoList->count() > 1)
        <select class="cfo-kpi-exec-entity-select"
                onchange="if(window.__kpiEmbedChangeCompany)window.__kpiEmbedChangeCompany(this.value)">
            @foreach($__kpiCoList as $__co)
            <option value="{{ $__co->id }}" {{ $__co->id == $selectedKpiCompanyId ? 'selected' : '' }}>
                {{ $__co->company_name }}
            </option>
            @endforeach
        </select>
        @else
        <span class="cfo-kpi-exec-entity-name">{{ $__selectedKpiCo?->company_name ?? 'Company' }}</span>
        @endif
    </div>

    {{-- Center: title + sub-label ---------------------------------------------- --}}
    <div class="cfo-kpi-exec-center">
        <h2 class="cfo-kpi-exec-title">Executive Financial Summary</h2>
        <p class="cfo-kpi-exec-sub">AI-powered financial intelligence</p>
    </div>

    {{-- Right: last updated + action buttons ------------------------------------ --}}
    <div class="cfo-kpi-exec-right">
        <p class="cfo-kpi-exec-updated">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"
                 style="display:inline-block;vertical-align:middle;margin-right:4px;stroke:#9ca3af">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            Last updated {{ now()->format('M d, Y g:i A') }}
        </p>
        <div class="cfo-kpi-exec-actions">
            @if(!empty($isQuickbooksConnected))
                @if(empty($isAdminView))
                <div class="cfo-kpi-gen-wrap">
                    <button type="button" id="dashGenerateBtn" class="cfo-btn-get-insights"
                            onclick="dashTriggerGenerate()"
                            style="{{ !empty($insightInProgress) ? 'display:none;' : '' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                        @if($monthlyLimit !== null)
                            Get Insights {{ (int) ($monthlyUsed ?? 0) }}/{{ (int) $monthlyLimit }} Left
                        @else
                            Generate Insights
                        @endif
                    </button>
                    <div id="dashGeneratingLoader" class="dash-gen-loader cfo-dash-gen-loader"
                         style="{{ empty($insightInProgress) ? 'display:none;' : '' }}">
                        <span class="dash-gen-spinner"></span>
                        <span>Generating insights…</span>
                    </div>
                </div>
                @endif
            @else
                @if(empty($isAdminView) && ($viewingAsUser ?? auth()->user())->can('view_quickbooks'))
                <a href="{{ route('quickbooks.index') }}" class="cfo-btn-get-insights" style="text-decoration:none">Connect QuickBooks</a>
                @endif
            @endif
            @if(empty($isAdminView))
            <a href="{{ route('insights.index') }}" class="cfo-btn-view-insights">View Insights</a>
            @endif
        </div>
    </div>

</div>

@if(($kpiCompanies ?? collect())->isEmpty() || empty($selectedKpiCompanyId))
    <div class="kpi-empty-card">
        <h3>No company on file</h3>
        <p>We could not resolve a company for KPI metrics. Ask your administrator to link your account to a company.</p>
    </div>
@else
@php
    $_initHasInsights = \App\Models\Report::where('company_id', $selectedKpiCompanyId)
        ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed'])
        ->exists();
@endphp
    <div id="kpiNoInsightsPlaceholder" style="{{ $_initHasInsights ? 'display:none;' : '' }}padding:40px 24px;text-align:center;background:#fff;border:1px solid #e5e7eb;border-radius:10px;margin-top:10px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" width="40" height="40" style="margin-bottom:12px"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <h3 style="margin:0 0 6px;font-size:17px;font-weight:700;color:#111827">No Insights Yet</h3>
        <p style="margin:0;font-size:13px;color:#6b7280">No financial insights have been generated for this company. Upload reports or connect QuickBooks to get started.</p>
    </div>
    <div id="kpiEmbedWrap" style="{{ $_initHasInsights ? '' : 'display:none;' }}">
    @include('dashboard.partials.kpi-dashboard-embed', [
        'kpiDataUrl'        => $kpiDataUrl,
        'kpiDetailBaseUrl'  => $kpiDetailBaseUrl,
        'companies'         => $kpiCompanies,
        'selectedCompanyId' => $selectedKpiCompanyId,
        'clientHiddenKpis'  => !empty($isAdminView) ? [] : ($hiddenKpis ?? []),
    ])
    </div>
@endif
</div>{{-- .user-kpi-fullbleed --}}

@else
{{-- Classic three-column dashboard when this tenant has no analyzed insights / KPI source yet --}}
<div class="user-db-header">
    @php $dashUser = $viewingAsUser ?? auth()->user(); @endphp
    <h1>Hi <span>{{ $dashUser->first_name }}</span></h1>
    <p>
        @if(empty($isAdminView))
        You're one step away from unlocking your first financial insights.
        @if($dashUser->can('view_quickbooks'))
            <a href="{{ route('quickbooks.index') }}">Connect QuickBooks</a> to proceed.
        @else
            Ask your administrator to connect QuickBooks to proceed.
        @endif
        @else
        This client hasn't generated any financial insights yet. Connect QuickBooks to get started.
        @endif
    </p>
</div>

@include('dashboard.partials.classic-user-dashboard', [
    'plans' => $plans ?? collect(),
    'currentPlan' => $currentPlan ?? null,
    'isQuickbooksConnected' => !empty($isQuickbooksConnected),
    'insightInProgress' => $insightInProgress ?? null,
])
@endif

{{-- ── Admin Edit Mode Script ─────────────────────────────────────────────── --}}
@if(!empty($isAdminView))
@push('scripts')
<script>
(function () {
    const CSRF             = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const SAVE_DRAFT_URL   = '{{ route("clients.dashboard.save-draft", $viewingAsUser->id) }}';
    const PUBLISH_URL      = '{{ route("clients.dashboard.publish",    $viewingAsUser->id) }}';
    const KPI_CO_SETTINGS  = {!! json_encode($kpiCoSettingsMap ?? []) !!};
    let editModeActive     = false;
    let currentCompanyId   = {{ $selectedKpiCompanyId ?? 'null' }};
    let draftHiddenKpis    = {!! json_encode(array_values($draftHiddenKpis ?? [])) !!};

    // Called by the embed after cards are in the DOM; also called immediately below
    // in case the embed's script already ran before this one (script ordering race).
    window.__kpiReadyCallback = function () {
        injectEyeIcons();
    };

    function injectEyeIcons() {
        document.querySelectorAll('[data-kpi-card]').forEach(function (card) {
            if (card.querySelector('.kpi-eye-btn')) return;
            const kpiType = card.getAttribute('data-kpi-card');
            const btn = document.createElement('button');
            btn.className = 'kpi-eye-btn';
            btn.setAttribute('type', 'button');
            btn.setAttribute('title', 'Toggle visibility');
            btn.setAttribute('data-kpi-eye', kpiType);
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (!editModeActive) return;
                toggleKpiHidden(kpiType);
            });
            card.appendChild(btn);
            refreshEyeIcon(kpiType);
        });
        refreshSectionTitleVisibility();
    }

    function refreshEyeIcon(kpiType) {
        const btn = document.querySelector('[data-kpi-eye="' + kpiType + '"]');
        if (!btn) return;
        const hidden = draftHiddenKpis.includes(kpiType);
        btn.innerHTML = hidden
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" width="16" height="16"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        const card = document.querySelector('[data-kpi-card="' + kpiType + '"]');
        if (card) {
            card.classList.toggle('kpi-hidden-draft', hidden);
        }
    }

    function refreshSectionTitleVisibility() {
        document.querySelectorAll('.kpi-tier-block').forEach(function (block) {
            var cards = block.querySelectorAll('[data-kpi-card]');
            var allHidden = cards.length > 0 && Array.from(cards).every(function (c) {
                return c.classList.contains('kpi-hidden-draft');
            });
            block.classList.toggle('kpi-section-all-hidden', allHidden);
        });
    }

    function toggleKpiHidden(kpiType) {
        const idx = draftHiddenKpis.indexOf(kpiType);
        if (idx === -1) draftHiddenKpis.push(kpiType);
        else draftHiddenKpis.splice(idx, 1);
        refreshEyeIcon(kpiType);
        refreshSectionTitleVisibility();
    }

    window.cdbToggleEditMode = function () {
        if (!editModeActive) {
            // Show confirmation before enabling
            var modal = document.getElementById('cdbEditConfirmModal');
            if (modal) { modal.style.display = 'flex'; return; }
        }
        // Disabling — no confirmation needed
        editModeActive = false;
        var btn   = document.getElementById('cdbEditModeBtn');
        var dash  = document.getElementById('kpiDashboard');
        if (btn)  btn.classList.remove('active');
        if (dash) dash.classList.remove('kpi-edit-active');
    };

    window.cdbConfirmEditMode = function () {
        var modal = document.getElementById('cdbEditConfirmModal');
        if (modal) modal.style.display = 'none';
        editModeActive = true;
        var btn   = document.getElementById('cdbEditModeBtn');
        var dash  = document.getElementById('kpiDashboard');
        if (btn)  btn.classList.add('active');
        if (dash) dash.classList.add('kpi-edit-active');
    };

    window.cdbCancelEditMode = function () {
        var modal = document.getElementById('cdbEditConfirmModal');
        if (modal) modal.style.display = 'none';
    };

    function showCdbToast(msg, type) {
        var t = document.getElementById('cdbToast');
        if (!t) return;
        t.textContent = msg;
        t.className = 'cdb-toast ' + (type || 'success');
        requestAnimationFrame(function () { t.classList.add('show'); });
        clearTimeout(t._timer);
        t._timer = setTimeout(function () { t.classList.remove('show'); }, 3200);
    }

    window.cdbSaveDraft = function () {
        var btn = document.getElementById('cdbSaveDraftBtn');
        var draftSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>';
        if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
        fetch(SAVE_DRAFT_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ hidden_kpis: draftHiddenKpis, company_id: currentCompanyId }),
        })
        .then(function (r) { return r.json(); })
        .then(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = draftSvg + ' Save Draft'; }
            var badge = document.getElementById('cdbStatusBadge');
            if (badge) { badge.textContent = 'Draft'; badge.className = 'cdb-status-badge cdb-status-draft'; }
            if (KPI_CO_SETTINGS[currentCompanyId]) KPI_CO_SETTINGS[currentCompanyId].publish_status = 'draft';
            // Exit edit mode so hidden cards are completely hidden (not just greyed out)
            editModeActive = false;
            var _eb = document.getElementById('cdbEditModeBtn'), _db = document.getElementById('kpiDashboard');
            if (_eb) _eb.classList.remove('active');
            if (_db) _db.classList.remove('kpi-edit-active');
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Draft Saved', text: 'Changes have been saved as a draft.', confirmButtonText: 'View Dashboard', confirmButtonColor: '#0788be', showCancelButton: false}).then(function(r) { if (r.isConfirmed) window.location.reload(); });
            }
        })
        .catch(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = draftSvg + ' Save Draft'; }
            showCdbToast('Failed to save draft. Please try again.', 'error');
        });
    };

    window.cdbPublish = function () {
        var btn = document.getElementById('cdbPublishBtn');
        var pubSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>';
        if (btn) { btn.disabled = true; btn.textContent = 'Publishing…'; }
        fetch(PUBLISH_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ hidden_kpis: draftHiddenKpis, company_id: currentCompanyId }),
        })
        .then(function (r) { return r.json(); })
        .then(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = pubSvg + ' Publish'; }
            var badge = document.getElementById('cdbStatusBadge');
            if (badge) { badge.textContent = 'Published'; badge.className = 'cdb-status-badge cdb-status-published'; }
            if (KPI_CO_SETTINGS[currentCompanyId]) KPI_CO_SETTINGS[currentCompanyId].publish_status = 'published';
            // Exit edit mode so hidden cards are completely hidden (not just greyed out)
            editModeActive = false;
            var _eb2 = document.getElementById('cdbEditModeBtn'), _db2 = document.getElementById('kpiDashboard');
            if (_eb2) _eb2.classList.remove('active');
            if (_db2) _db2.classList.remove('kpi-edit-active');
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Published!', text: 'Successfully published! The client can now see all reviewed content.', confirmButtonText: 'View Dashboard', confirmButtonColor: '#0788be', showCancelButton: false, }).then(function(r) { if (r.isConfirmed) window.location.reload(); });
            }
        })
        .catch(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = pubSvg + ' Publish'; }
            showCdbToast('Failed to publish. Please try again.', 'error');
        });
    };

    // Wrap company change to keep admin bar in sync with embed's company selector
    (function () {
        var _orig = window.__kpiEmbedChangeCompany;
        window.__kpiEmbedChangeCompany = function (id) {
            id = parseInt(String(id), 10);
            currentCompanyId = id;
            var s = KPI_CO_SETTINGS[id];
            if (s) {
                var nameEl = document.querySelector('.cdb-company-name');
                if (nameEl) nameEl.textContent = s.name;
                draftHiddenKpis = (s.hidden_kpis || []).slice();
                var badge = document.getElementById('cdbStatusBadge');
                if (badge) {
                    var st = s.publish_status || 'published';
                    badge.textContent = st.charAt(0).toUpperCase() + st.slice(1);
                    badge.className = 'cdb-status-badge cdb-status-' + st;
                }
            }
            // Exit edit mode on company switch
            editModeActive = false;
            var editBtn   = document.getElementById('cdbEditModeBtn');
            var dash      = document.getElementById('kpiDashboard');
            if (editBtn) editBtn.classList.remove('active');
            if (dash)    dash.classList.remove('kpi-edit-active');

            // Show/hide KPI embed based on whether the selected company has insights
            var hasInsights = s ? (s.has_insights !== false) : true;
            var embedWrap   = document.getElementById('kpiEmbedWrap');
            var noInsights  = document.getElementById('kpiNoInsightsPlaceholder');
            if (embedWrap)  embedWrap.style.display  = hasInsights ? '' : 'none';
            if (noInsights) noInsights.style.display = hasInsights ? 'none' : '';

            if (hasInsights && _orig) _orig(id);
        };
    }());

    // Embed's script runs before this one, so KPI cards are already in the DOM.
    // Apply eye icons and draft-hidden state immediately without waiting for callback.
    injectEyeIcons();
}());
</script>
@endpush
@endif

{{-- ── Industry Type Selection Modal ─────────────────────────────────────── --}}
<div id="industryModal" class="ind-modal-overlay">
    <div class="ind-modal">
        <h3>What's your industry?</h3>
        <p>We use industry benchmarks to put your financials in context — from margins to cash flow trends. This helps our AI provide more relevant insights tailored to your business.</p>
        <select id="industrySelect">
            <option value="">— Select your industry —</option>
            <option value="Automotive">Automotive</option>
            <option value="Technology">Technology</option>
            <option value="Healthcare">Healthcare</option>
            <option value="Manufacturing">Manufacturing</option>
            <option value="Finance">Finance</option>
            <option value="Retail">Retail</option>
            <option value="Construction">Construction</option>
            <option value="Transportation">Transportation</option>
            <option value="Energy">Energy</option>
            <option value="Agriculture">Agriculture</option>
            <option value="Education">Education</option>
            <option value="Real Estate">Real Estate</option>
            <option value="Entertainment">Entertainment</option>
            <option value="Consulting">Consulting</option>
            <option value="Legal Services">Legal Services</option>
            <option value="Other">Other</option>
        </select>
        <div class="ind-modal-actions">
            <button class="ind-btn-confirm" id="indBtnConfirm" onclick="saveIndustryAndProceed()">Continue</button>
            <button class="ind-btn-cancel" onclick="closeIndustryModal()">Cancel</button>
        </div>
    </div>
</div>

{{-- ── Free Report Limit Reached Modal ───────────────────────────────────── --}}
<div id="dashLimitModal" class="limit-modal-overlay">
    <div class="limit-modal">
        <div class="limit-modal-icon">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="#0ea5e9" stroke-width="2"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke="#0ea5e9" stroke-width="2.5" stroke-linecap="round"/>
                <circle cx="12" cy="16" r="1.2" fill="#0ea5e9"/>
            </svg>
        </div>
        <h3>Free Report Limit Reached</h3>
        <p>
            @php
                $limitPlanName   = $currentPlan?->name ?? null;
                $limitMonthlyMax = $monthlyLimit ?? null;
            @endphp
            @if($limitPlanName && $limitMonthlyMax)
                You're on the <strong>{{ $limitPlanName }}</strong> plan, which includes <strong>{{ $limitMonthlyMax }}</strong> insight{{ $limitMonthlyMax == 1 ? '' : 's' }} per month.
                You've used all your insights for this month.
            @elseif($limitMonthlyMax)
                You've used all <strong>{{ $limitMonthlyMax }}</strong> insights included in your plan this month.
            @else
                You've reached your monthly insight limit.
            @endif
            Upgrade your plan to generate more insights.
        </p>
        <div class="limit-modal-actions">
            <a href="{{ route('billing.index') }}" class="limit-btn-subscribe">Subscribe Now</a>
            <button class="limit-btn-later" onclick="closeDashLimitModal()">Maybe Later</button>
        </div>
    </div>
</div>

{{-- ── QB Sync Confirm Modal ──────────────────────────────────────────────── --}}
<div id="dashQbModal" class="qb-modal-overlay">
    <div class="qb-modal">
        <div style="margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
            <svg viewBox="0 0 70 70" width="70" height="70">
                <circle cx="35" cy="35" r="35" fill="#2CA01C"/>
                <circle cx="25" cy="36" r="11" fill="none" stroke="white" stroke-width="5.5"/>
                <line x1="32.5" y1="43.5" x2="38" y2="52.5" stroke="white" stroke-width="5.5" stroke-linecap="round"/>
                <line x1="44" y1="20" x2="44" y2="43" stroke="white" stroke-width="5.5" stroke-linecap="round"/>
                <circle cx="45.5" cy="45" r="10.5" fill="none" stroke="white" stroke-width="5.5"/>
            </svg>
        </div>
        <h3>Sync your QuickBooks account?</h3>
        <p class="qb-modal-sub">This will securely import your latest financial data (read-only) to generate:</p>
        <ul>
            <li>Cash flow insights</li>
            <li>Profitability trends</li>
            <li>Key financial KPIs</li>
        </ul>
        <p class="qb-modal-timing">Usually takes 5–10 minutes.</p>
        <div class="qb-modal-actions">
            <button class="btn-modal-confirm" onclick="dashDoGenerate()">Generate Insights</button>
            <button class="btn-modal-cancel" onclick="closeDashQbModal()">Not now</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const CSRF             = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const SAVE_CTX_URL     = '{{ route("insights.save-business-context") }}';
    const GENERATE_URL     = '{{ route("insights.generate") }}';
    const INSIGHTS_URL     = '{{ route("insights.index") }}';
    const HAS_BUSINESS_CTX = {{ ($viewingAsUser ?? auth()->user())->business_context ? 'true' : 'false' }};
    const DB_LIMIT_REACHED = {{ ($monthlyLimit !== null && ($monthlyUsed ?? 0) >= $monthlyLimit) ? 'true' : 'false' }};
    const LS_KEY           = 'dash_processing_insight_id';
    let dashboardProcessingSwalOpen = false;

    // ── On load: if generation is running, show a non-blocking notice only ──────
    const serverInProgress = {{ !empty($insightInProgress) ? 'true' : 'false' }};
    const lsProcessingId   = localStorage.getItem(LS_KEY);

    if (serverInProgress || lsProcessingId) {
        // Show a small, non-blocking banner — do NOT replace the button or block the page
        showProcessingBar('Insight generation is running in the background. You can use this page normally.');
    }

    // ── Entry point: "Generate Insights" button click ────────────────────────
    window.dashTriggerGenerate = function () {
        if (DB_LIMIT_REACHED) {
            openDashLimitModal();
            return;
        }
        if (!HAS_BUSINESS_CTX) {
            openIndustryModal();
        } else {
            openQbModal();
        }
    };

    // ── Industry modal ────────────────────────────────────────────────────────
    window.openIndustryModal = function () {
        document.getElementById('industryModal').classList.add('open');
    };

    window.closeIndustryModal = function () {
        document.getElementById('industryModal').classList.remove('open');
    };

    window.saveIndustryAndProceed = function () {
        const select = document.getElementById('industrySelect');
        const value  = select.value;
        if (!value) {
            select.style.borderColor = '#ef4444';
            return;
        }
        select.style.borderColor = '';

        const btn = document.getElementById('indBtnConfirm');
        btn.disabled    = true;
        btn.textContent = 'Saving…';

        fetch(SAVE_CTX_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ business_context: value }),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled    = false;
            btn.textContent = 'Continue';
            if (data.status === 'success') {
                closeIndustryModal();
                openQbModal();
            } else {
                alert(data.message || 'Could not save industry. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled    = false;
            btn.textContent = 'Continue';
            alert('Network error. Please try again.');
        });
    };

    // ── QB confirm modal ──────────────────────────────────────────────────────
    window.openQbModal = function () {
        document.getElementById('dashQbModal').classList.add('open');
    };

    window.closeDashQbModal = function () {
        document.getElementById('dashQbModal').classList.remove('open');
    };

    // ── Actually dispatch insight generation ──────────────────────────────────
    window.dashDoGenerate = function () {
        closeDashQbModal();
        const btn = document.getElementById('dashGenerateBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Starting…'; }

        fetch(GENERATE_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'processing') {
                // Save report ID to localStorage so the dashboard and insights pages can both detect processing
                localStorage.setItem(LS_KEY, data.report_id);
                localStorage.setItem('insights_processing_ids', data.report_id);
                // Navigate to insights page — generation continues in background
                window.location.href = INSIGHTS_URL;
            } else {
                if (btn) { btn.disabled = false; btn.textContent = 'Generate Insights'; }
                if (data.limit_reached) {
                    openDashLimitModal();
                } else if (data.reconnect_required && data.quickbooks_url) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Reconnect QuickBooks',
                        html: (data.message || 'Your QuickBooks link is no longer valid.') + '<br><br><a href="' + data.quickbooks_url + '" style="color:var(--orange);font-weight:600">Open QuickBooks →</a>',
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            }
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Generate Insights'; }
            Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
        });
    };

    // ── Limit-reached modal ───────────────────────────────────────────────────
    window.openDashLimitModal = function () {
        document.getElementById('dashLimitModal').classList.add('open');
    };

    window.closeDashLimitModal = function () {
        document.getElementById('dashLimitModal').classList.remove('open');
    };

    // ── Helpers ───────────────────────────────────────────────────────────────
    function showProcessingBar(msg) {
        dashboardProcessingSwalOpen = true;
        Swal.fire({
            title: 'Generating insights',
            html: '<p style="margin:0 0 12px;color:#64748b;">' + (msg || 'Syncing your QuickBooks data and generating AI insights. This may take a few minutes.') + '</p>' +
                '<a href="' + INSIGHTS_URL + '" style="color:var(--orange);font-weight:600;text-decoration:none;">View Progress →</a>',
            allowOutsideClick: true,
            allowEscapeKey: true,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    }

    function hideProcessingBar() {
        if (dashboardProcessingSwalOpen && Swal.isVisible()) {
            Swal.close();
        }
        dashboardProcessingSwalOpen = false;
    }

    function showGeneratingLoader() {
        const btn    = document.getElementById('dashGenerateBtn');
        const loader = document.getElementById('dashGeneratingLoader');
        if (btn)    btn.style.display    = 'none';
        if (loader) loader.style.display = 'flex';
    }

    function resetDashBtn() {
        const btn    = document.getElementById('dashGenerateBtn');
        const loader = document.getElementById('dashGeneratingLoader');
        if (btn)    { btn.style.display = ''; btn.disabled = false; btn.style.opacity = ''; }
        if (loader) loader.style.display = 'none';
    }
}());
</script>
@endpush
