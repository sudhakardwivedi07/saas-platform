@extends('layouts.app')
@section('page-title', 'Insights')

@push('styles')
<style>
/* ── Page Header ─────────────────────────────────── */
.pi-page-header { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;flex-wrap:wrap;gap:12px; }
.pi-page-title { font-size:22px;font-weight:700;color:#1a202c;margin:0 0 5px; }
.pi-page-subtitle { font-size:13px;color:#64748b;margin:0; }
.pi-page-header-actions { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }

/* ── Stats Cards ─────────────────────────────────── */
.pi-stats-row { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px; }
.pi-stat-card { background:#fff;border-radius:10px;padding:14px 16px;box-shadow:0 2px 8px rgba(0,0,0,.06);border-left:4px solid transparent; }
.pi-stat-card.total  { border-left-color:var(--blue); }
.pi-stat-card.used   { border-left-color:#10b981; }
.pi-stat-card.remain { border-left-color:var(--orange); }
.pi-stat-card.avg    { border-left-color:var(--blue); }
.pi-stat-top { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px; }
.pi-stat-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em; }
.pi-stat-label.total  { color:#64748b; }
.pi-stat-label.used   { color:#10b981; }
.pi-stat-label.remain { color:var(--orange); }
.pi-stat-label.avg    { color:#64748b; }
.pi-stat-value { font-size:26px;font-weight:700;line-height:1;margin-bottom:4px; }
.pi-stat-value.total  { color:#1a202c; }
.pi-stat-value.used   { color:#10b981; }
.pi-stat-value.remain { color:var(--orange); }
.pi-stat-value.avg    { color:#1a202c; }
.pi-stat-sub { font-size:11px;color:#94a3b8; }
.pi-stat-icon { width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.pi-stat-icon.total  { background:var(--blue-light); color:var(--blue); }
.pi-stat-icon.used   { background:#ecfdf5; color:#10b981; }
.pi-stat-icon.remain { background:var(--orange-light); color:var(--orange); }
.pi-stat-icon.avg    { background:var(--blue-light); color:var(--blue); }

/* ── Tabs ────────────────────────────────────────── */
.pi-tabs { display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:20px; }
.pi-tab-btn { padding:10px 24px;border:none;background:none;font-size:14px;font-weight:500;color:#64748b;cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s;white-space:nowrap; }
.pi-tab-btn.active { color:var(--blue);border-bottom-color:var(--blue);font-weight:600; }
.pi-tab-btn:hover:not(.active) { color:#1a202c; }
.pi-tab-panel { display:none; }
.pi-tab-panel.active { display:block; }
/* ── Period Badges ───────────────────────────────── */
.pi-period-badge { display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;letter-spacing:.01em; }
.pi-period-month   { background:#eff6ff;color:#2563eb; }
.pi-period-quarter { background:#f3e8ff;color:#7c3aed; }
.pi-period-annual  { background:#fefce8;color:#92400e; }
.pi-period-range   { background:#f0fdf4;color:#166534; }

/* ── Toolbar ─────────────────────────────────────── */
.pi-toolbar { background:#fff;border-radius:12px;padding:14px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap; }
.pi-toolbar-right { margin-left:auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.pi-search-wrap { position:relative;display:flex;align-items:center; }
.pi-search-wrap svg { position:absolute;left:10px;color:#94a3b8;pointer-events:none; }
.pi-search-inp { height:34px;padding:0 12px 0 32px;border:1px solid #e2e8f0;border-radius:7px;font-size:13px;font-family:inherit;color:#1a202c;background:#fff;outline:none;width:210px;transition:border-color .2s; }
.pi-search-inp:focus { border-color:var(--blue); }
.pi-status-sel { height:34px;border:1px solid #e2e8f0;border-radius:7px;padding:0 28px 0 10px;font-size:13px;font-family:inherit;color:#1a202c;background:#fff url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 10px center;-webkit-appearance:none;appearance:none;outline:none;cursor:pointer;min-width:140px; }
.pi-status-sel:focus { border-color:var(--blue); }
.pi-btn-reset { height:34px;padding:0 14px;border:1px solid #ef4444;border-radius:7px;background:#fff;color:#ef4444;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s; }
.pi-btn-reset:hover { background:#fef2f2; }
.pi-btn-generate { height:38px;padding:0 20px;background:var(--orange);color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;transition:background .2s;white-space:nowrap; }
.pi-btn-generate:hover:not(:disabled) { background:var(--orange-dark); }
.pi-btn-generate:disabled { background:#9ca3af !important;cursor:not-allowed;pointer-events:none; }
.pi-btn-export { height:38px;padding:0 16px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;color:#374151;font-size:13.5px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:7px;transition:all .2s; }
.pi-btn-export:hover { background:#f8fafc;color:#1a202c; }
.pi-btn-export:disabled { opacity:.45;cursor:not-allowed;pointer-events:none; }
.pi-btn-compare { height:38px;padding:0 20px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;transition:background .2s;white-space:nowrap; }
.pi-btn-compare:hover { background:#1d4ed8; }

/* ── Table ───────────────────────────────────────── */
.pi-table-card { background:#fff;border-radius:12px;padding:0;box-shadow:0 2px 8px rgba(0,0,0,.06);overflow:hidden;min-width:0; }
.pi-table { width:100%;border-collapse:collapse;min-width:700px; }
.pi-table th { text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:#64748b;border-bottom:1px solid #f1f5f9;background:#f8fafc;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap; }
.pi-table td { padding:14px 16px;font-size:13.5px;color:#1a202c;border-bottom:1px solid #f8fafc;vertical-align:middle; }
.pi-table th:first-child, .pi-table td:first-child { max-width:340px; }
.pi-table tr:last-child td { border-bottom:none; }
.pi-table tr:hover td { background:#fafbfc; }
.pi-name-cell { display:flex;align-items:center;gap:10px;min-width:0;overflow:hidden; }
.pi-name-link, .pi-name-deleted { overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0; }
.pi-name-cell > span:not(.pi-name-icon) { overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0; }
.pi-name-icon { color:#94a3b8;flex-shrink:0; }
.pi-name-link { color:var(--blue);text-decoration:none;font-weight:500; }
.pi-name-link:hover { text-decoration:underline; }
.pi-name-deleted { color:#9ca3af;font-weight:500;text-decoration:line-through; }

/* ── Comparisons banner ──────────────────────────── */
.pi-compare-banner { background:#fff;border-radius:12px;padding:20px 24px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;box-shadow:0 2px 8px rgba(0,0,0,.06); }
.pi-compare-banner-left { display:flex;align-items:center;gap:16px; }
.pi-compare-banner-icon { width:44px;height:44px;border-radius:10px;background:var(--orange-light);color:var(--orange);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.pi-compare-banner-text h3 { color:#1a202c;font-size:16px;font-weight:700;margin:0 0 3px; }
.pi-compare-banner-text p { color:#64748b;font-size:13px;margin:0; }

/* ── Tab count badge ─────────────────────────────── */
.pi-tab-count { display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:10px;font-size:11px;font-weight:700;margin-left:6px;background:#e2e8f0;color:#64748b;transition:all .2s; }
.pi-tab-btn.active .pi-tab-count { background:var(--blue);color:#fff; }

/* ── Period pills ────────────────────────────────── */
.pi-period-pill { display:inline-flex;align-items:center;gap:6px;white-space:nowrap; }
.pi-period-tag { padding:2px 10px;border-radius:6px;font-size:12px;font-weight:600;background:#eff6ff;color:var(--blue);border:1px solid #bfdbfe; }
.pi-period-tag.b { background:#fff7ed;color:var(--orange);border-color:#fed7aa; }
.pi-period-arrow { color:#94a3b8;font-size:13px; }

/* ── Badges ──────────────────────────────────────── */
.badge { padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;display:inline-block; }
.badge-draft      { background:#fef3c7;color:#92400e; }
.badge-published  { background:#d1fae5;color:#065f46; }
.badge-processing { background:#e0f2fe;color:#0369a1; }
.badge-failed     { background:#fee2e2;color:#991b1b; }
.badge-deleted    { background:#f3f4f6;color:#6b7280; }
.badge-completed  { background:#d1fae5;color:#065f46; }
.badge-pending    { background:#e0f2fe;color:#0369a1; }

/* ── Action buttons ──────────────────────────────── */
.pi-actions { display:flex;gap:6px;align-items:center; }
.pi-btn-view { background:#fff;color:var(--orange);padding:5px 14px;border-radius:6px;font-weight:500;font-size:12.5px;border:1.5px solid var(--orange);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .2s,color .2s;white-space:nowrap; }
.pi-btn-view:hover { background:var(--orange);color:#fff;text-decoration:none; }
.pi-btn-view:disabled { background:#f3f4f6;color:#9ca3af;border-color:#d1d5db;cursor:not-allowed; }
.pi-btn-portal { background:var(--orange);color:#fff;padding:5px 14px;border-radius:6px;font-weight:500;font-size:12.5px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .2s;white-space:nowrap; }
.pi-btn-portal:hover { background:var(--orange-dark);color:#fff;text-decoration:none; }
.pi-btn-retry { background:var(--orange);color:#fff;padding:5px 14px;border-radius:6px;font-weight:500;font-size:12.5px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:background .2s; }
.pi-btn-retry:hover { background:var(--orange-dark); }
.pi-btn-icon { width:30px;height:30px;border-radius:6px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;flex-shrink:0; }
.pi-btn-icon.dl  { background:var(--orange-light);color:var(--orange);border:1px solid #fddbb5; }
.pi-btn-icon.del { background:#fee2e2;color:#ef4444; }
.pi-btn-icon:hover { transform:scale(1.08); }

/* ── Download Modal ──────────────────────────────── */
.pi-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:none;align-items:center;justify-content:center; }
.pi-modal-overlay.open { display:flex; }
.pi-modal { background:#fff;border-radius:16px;padding:32px;width:480px;max-width:95vw;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.16);animation:piSlide .25s ease; }
@keyframes piSlide { from{opacity:0;transform:translateY(-16px)} to{opacity:1;transform:translateY(0)} }
.pi-modal-close { position:absolute;top:16px;right:16px;width:28px;height:28px;border:none;background:none;color:#94a3b8;cursor:pointer;font-size:18px;line-height:1;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:all .2s; }
.pi-modal-close:hover { background:#f1f5f9;color:#1a202c; }
.pi-modal h3 { font-size:20px;font-weight:700;color:#1a202c;margin:0 0 6px; }
.pi-modal .pi-modal-sub { font-size:13.5px;color:#6b7280;margin:0 0 22px; }
.pi-modal label { display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px; }
.pi-modal select { width:100%;height:42px;padding:0 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13.5px;font-family:inherit;color:#1a202c;background:#fff;outline:none;cursor:pointer;transition:border-color .2s;margin-bottom:16px; }
.pi-modal select:focus { border-color:var(--orange); }
.pi-modal select:disabled { background:#f8fafc;color:#9ca3af;cursor:not-allowed; }
.pi-modal-note { background:var(--blue-pale);border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:12.5px;color:var(--blue);margin-bottom:0;line-height:1.5; }
.pi-modal-actions { display:flex;gap:10px;margin-top:20px; }
.pi-modal-btn-generate { flex:1;background:var(--orange);color:#fff;border:none;padding:12px 20px;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;display:flex;align-items:center;justify-content:center;gap:8px; }
.pi-modal-btn-generate:hover { background:var(--orange-dark); }
.pi-modal-btn-generate:disabled { opacity:.6;cursor:not-allowed; }
.pi-modal-btn-cancel { flex:1;background:#fff;color:#374151;border:1.5px solid #e2e8f0;padding:11px 20px;border-radius:9px;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s; }
.pi-modal-btn-cancel:hover { background:#f8fafc; }
.pi-modal-btn-primary { flex:1;background:var(--blue);color:#fff;border:none;padding:12px 20px;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;display:flex;align-items:center;justify-content:center;gap:8px; }
.pi-modal-btn-primary:hover { background:#1d4ed8; }
.pi-modal-btn-primary:disabled { opacity:.6;cursor:not-allowed; }

/* ── Download Modal ──────────────────────────────── */
.pi-dl-options { display:flex;flex-direction:column;gap:10px;margin-bottom:24px; }
.pi-dl-option { cursor:pointer;display:block; }
.pi-dl-option input[type=radio] { position:absolute;opacity:0;width:0;height:0; }
.pi-dl-card { display:flex;align-items:center;gap:14px;padding:14px 16px;border:1.5px solid #e2e8f0;border-radius:12px;background:#fff;transition:all .2s;cursor:pointer; }
.pi-dl-card:hover { border-color:#93c5fd;background:#f8faff; }
.pi-dl-card.pi-dl-selected { border-color:var(--blue,#2563eb);background:#eff6ff; }
.pi-dl-icon-box { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.pi-dl-icon-box.zip { background:#1d4ed8; }
.pi-dl-icon-box.pdf { background:#475569; }
.pi-dl-icon-box svg { width:22px;height:22px;stroke:#fff;fill:none;stroke-width:1.8; }
.pi-dl-info { flex:1;min-width:0; }
.pi-dl-title { font-size:14px;font-weight:600;color:#1a202c;margin-bottom:2px; }
.pi-dl-desc { font-size:12.5px;color:#6b7280;line-height:1.4; }
.pi-dl-radio-dot { width:18px;height:18px;border-radius:50%;border:2px solid #cbd5e0;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .2s; }
.pi-dl-card.pi-dl-selected .pi-dl-radio-dot { border-color:var(--blue,#2563eb); }
.pi-dl-card.pi-dl-selected .pi-dl-radio-dot::after { content:'';width:10px;height:10px;border-radius:50%;background:var(--blue,#2563eb);display:block; }

@media(max-width:900px) { .pi-stats-row { grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px) { .pi-stats-row { grid-template-columns:1fr; } .pi-page-header { flex-direction:column; } .pi-toolbar { flex-direction:column;align-items:stretch; } }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="pi-page-header">
    <div>
        <h1 class="pi-page-title">Insights</h1>
        <p class="pi-page-subtitle">All CFO financial insights generated across your client portfolio</p>
    </div>
    <div class="pi-page-header-actions" id="pi-header-actions">
        {{-- Buttons are toggled by tab --}}
        <div id="pi-reports-actions" style="display:flex;gap:10px;align-items:center;">
            <button id="piExportBtn" class="pi-btn-export" onclick="piExportCSV()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </button>
            <button id="piHeaderGenerateBtn" class="pi-btn-generate" onclick="{{ $limitReached ?? false ? 'piShowLimitModal()' : 'piOpenGenerateModal()' }}"
                @if($limitReached ?? false) title="Monthly insight limit reached" @endif>
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                Generate New Insight
            </button>
        </div>
        <div id="pi-comparisons-actions" style="display:none;"></div>
    </div>
</div>

@if($limitReached ?? false)
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        <strong style="color:#b91c1c;font-size:14px;">Monthly insight limit reached</strong>
        <p style="color:#991b1b;font-size:13px;margin:2px 0 0;">You have used all <strong>{{ $totalInsights }}</strong> insights for this month. Your limit resets on <strong>{{ now()->endOfMonth()->format(active_date_format()) }}</strong>. Please upgrade your plan for more insights.</p>
    </div>
</div>
@endif

{{-- Stats Row --}}
<div class="pi-stats-row">
    <div class="pi-stat-card total">
        <div class="pi-stat-top">
            <span class="pi-stat-label total">Total Insights</span>
            <div class="pi-stat-icon total">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="pi-stat-value total">{{ $totalInsights }}</div>
        <div class="pi-stat-sub">Monthly limit from your plan</div>
    </div>
    <div class="pi-stat-card used">
        <div class="pi-stat-top">
            <span class="pi-stat-label used">Insights Used</span>
            <div class="pi-stat-icon used">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>
        <div class="pi-stat-value used">{{ $insightsUsed }}</div>
        <div class="pi-stat-sub">Insights generated this month</div>
    </div>
    <div class="pi-stat-card remain">
        <div class="pi-stat-top">
            <span class="pi-stat-label remain">Insights Remaining</span>
            <div class="pi-stat-icon remain">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
        </div>
        <div class="pi-stat-value remain">{{ $insightsRemaining }}</div>
        <div class="pi-stat-sub">Resets {{ now()->endOfMonth()->format(active_date_format()) }}</div>
    </div>
    <div class="pi-stat-card avg">
        <div class="pi-stat-top">
            <span class="pi-stat-label avg">Average Used Per Client</span>
            <div class="pi-stat-icon avg">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
        </div>
        <div class="pi-stat-value avg">{{ $avgPerClient }}</div>
        <div class="pi-stat-sub">Insights used average per client</div>
    </div>
</div>

{{-- Tabs --}}
<div class="pi-tabs">
    <button class="pi-tab-btn active" id="pi-tab-reports" onclick="piSwitchTab('reports')">
        Reports<span class="pi-tab-count" id="pi-tab-reports-count">{{ $reports->count() }}</span>
    </button>
    <button class="pi-tab-btn" id="pi-tab-comparisons" onclick="piSwitchTab('comparisons')">
        Comparisons<span class="pi-tab-count" id="pi-tab-comparisons-count">{{ $compareReports->count() }}</span>
    </button>
</div>

{{-- ─── Reports Tab ─────────────────────────────────────────────────────── --}}
<div class="pi-tab-panel active" id="pi-panel-reports">

    {{-- Toolbar --}}
    <div class="pi-toolbar">
        <div class="pi-search-wrap">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="piSearch" class="pi-search-inp" placeholder="Search insights…" oninput="piApplyFilters()">
        </div>
        <select id="piStatusFilter" class="pi-status-sel" onchange="piApplyFilters()">
            <option value="">Select Status</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="processing">Processing</option>
            <option value="failed">Failed</option>
            <option value="deleted">Deleted</option>
        </select>
        <div class="dr-wrap" id="pi-dr">
            <div class="dr-field" onclick="drOpen('pi-dr')">
                <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="dr-text" id="pi-dr-text">Start Date – End Date</span>
                <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('pi-dr')" title="Clear date filter">&#x2715;</button>
            </div>
            <div class="dr-menu" id="pi-dr-menu">
                <ul class="dr-list">
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','today',this)">Today</li>
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','last30',this)">Last 30 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','thisMonth',this)">This Month</li>
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','lastMonth',this)">Last Month</li>
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','last90',this)">Last 90 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','last6m',this)">Last 6 Months</li>
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','last1y',this)">Last 1 Year</li>
                    <li class="dr-opt" onclick="drPickPreset('pi-dr','custom',this)">Custom Range</li>
                </ul>
                <div class="dr-custom-row" id="pi-dr-custom">
                    <input type="date" class="dr-date-inp" id="pi-dr-from">
                    <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                    <input type="date" class="dr-date-inp" id="pi-dr-to">
                    <button class="dr-apply-btn" onclick="drApplyCustom('pi-dr')">Apply</button>
                </div>
            </div>
        </div>
        <button class="btn btn-danger" onclick="piReset()">Reset</button>
    </div>

{{-- Table --}}
<div class="pi-table-card">
    <div class="table-responsive">
        <table class="pi-table" id="pi-table">
            <thead>
                <tr>
                    <th>Insight Report</th>
                    <th>Generated</th>
                    <th>Period</th>
                    <th>Client Name</th>
                    <th>Company Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="piTableBody">
                @foreach($reports as $report)
                @php
                    $isDeleted   = $report->trashed() || strtolower($report->status) === 'deleted';
                    $isProcessing= $report->isProcessing();
                    $isFailed    = strtolower($report->status) === 'failed';
                    $isReady     = $report->isReady();
                    $publishSt   = $report->publish_status ?? 'draft';
                    $clientName  = $report->user?->name ?? '—';
                    $companyName = $report->company?->company_name ?? '—';

                    $rowStatus = $isDeleted ? 'deleted'
                        : ($isProcessing ? 'processing'
                        : ($isFailed ? 'failed'
                        : ($isReady ? $publishSt : 'processing')));
                    $displayName = trim(preg_replace('/\s*\(Analysis\s+\d+\)\s*$/i', '', $report->report_name)) . ' - Insight Report';

                    $pStart = $report->report_start_date;
                    $pEnd   = $report->report_end_date;
                    if ($pStart && $pEnd) {
                        $monthsSpanned = ($pEnd->year - $pStart->year) * 12 + ($pEnd->month - $pStart->month) + 1;
                        if ($pStart->month === 1 && $pEnd->month === 12 && $pStart->year === $pEnd->year) {
                            $periodLabel = $pStart->format('Y') . ' (Annual)';
                            $periodType  = 'annual';
                        } elseif ($monthsSpanned === 3 && in_array($pStart->month, [1, 4, 7, 10])) {
                            $periodLabel = 'Q' . (int) ceil($pStart->month / 3) . ' ' . $pStart->format('Y');
                            $periodType  = 'quarter';
                        } elseif ($monthsSpanned === 1) {
                            $periodLabel = $pStart->format('M Y');
                            $periodType  = 'month';
                        } else {
                            $periodLabel = $pStart->format('M Y') . ' – ' . $pEnd->format('M Y');
                            $periodType  = 'range';
                        }
                    } else {
                        $periodLabel = null;
                        $periodType  = 'none';
                    }
                @endphp
                <tr id="pi-row-{{ $report->id }}"
                    data-search="{{ strtolower($displayName . ' ' . $clientName . ' ' . $companyName) }}"
                    data-status="{{ $rowStatus }}"
                    data-company-id="{{ $report->company_id }}"
                    data-user-id="{{ $report->user_id }}"
                    style="{{ $isDeleted ? 'opacity:0.65;' : '' }}">
                    <td>
                        <div class="pi-name-cell">
                            <span class="pi-name-icon">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            @if($isDeleted)
                                <span class="badge badge-deleted">Deleted</span>
                            @elseif($isProcessing)
                                <span class="badge badge-processing" id="pi-badge-{{ $report->id }}">Processing</span>
                            @elseif($isFailed)
                                <span class="badge badge-failed">Failed</span>
                            @elseif($isReady)
                                @if($publishSt === 'published')
                                    <span class="badge badge-published">Published</span>
                                @else
                                    <span class="badge badge-draft">Draft</span>
                                @endif
                            @else
                                <span class="badge badge-processing">Processing</span>
                            @endif
                        </td>
                        <td>
                            @if($isDeleted)
                                <span style="font-size:12px;color:#9ca3af;font-style:italic;">—</span>
                            @else
                                <div class="pi-actions">
                                    @if($isProcessing)
                                        <button class="pi-btn-view" disabled>Processing…</button>
                                    @elseif($isFailed)
                                        <button class="pi-btn-retry" onclick="piRetry({{ $report->id }})">Retry</button>
                                    @else
                                        <div class="dm-wrap">
                                            <button class="dot-menu" data-menu="pi-dm-{{ $report->id }}" title="Actions">•••</button>
                                            <div class="dm-popup" id="pi-dm-{{ $report->id }}">
                                                <a href="{{ route('partner-insights.view', $report->encrypted_id) }}" class="dm-item">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    View Insight
                                                </a>
                                                @if($report->user_id)
                                                <div class="dm-divider"></div>
                                                <form method="POST" action="{{ route('clients.login-as', $report->user_id) }}" style="margin:0">
                                                    @csrf
                                                    <button type="submit" class="dm-item" style="width:100%;text-align:left;border:0;background:transparent;cursor:pointer">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                        View as Client
                                                    </button>
                                                </form>
                                                @endif
                                                <div class="dm-divider"></div>
                                                <a href="javascript:void(0)" class="dm-item" onclick="piDownload({{ $report->id }})">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    Download
                                                </a>
                                                <div class="dm-divider"></div>
                                                <a href="javascript:void(0)" class="dm-item dm-item--danger" onclick="piDelete({{ $report->id }}, {{ json_encode($displayName) }})">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                                    Delete
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>{{-- end Reports tab --}}

{{-- ─── Comparisons Tab ─────────────────────────────────────────────────── --}}
<div class="pi-tab-panel" id="pi-panel-comparisons">

    {{-- Banner --}}
    <div class="pi-compare-banner">
        <div class="pi-compare-banner-left">
            <div class="pi-compare-banner-icon">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="pi-compare-banner-text">
                <h3>Comparison Reports</h3>
                <p>Compare two insight reports of the same company across different periods to spot trends, drift, and outliers.</p>
            </div>
        </div>
        <button class="pi-btn-generate" onclick="piOpenCompareModal()">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Compare Insights
        </button>
    </div>

    {{-- Comparisons Toolbar --}}
    <div class="pi-toolbar">
        <div class="pi-search-wrap">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="pcSearch" class="pi-search-inp" placeholder="Search comparisons…" oninput="pcApplyFilters()">
        </div>
        <select id="pcClientFilter" class="pi-status-sel" onchange="pcApplyFilters()">
            <option value="">All Clients</option>
            @foreach($compareReports->pluck('user.name')->filter()->unique()->sort() as $clientName)
                <option value="{{ $clientName }}">{{ $clientName }}</option>
            @endforeach
        </select>
        <button class="btn btn-danger" onclick="pcReset()">Reset</button>
    </div>

    {{-- Comparisons Table --}}
    <div class="pi-table-card">
        <div class="table-responsive">
            <table class="pi-table" id="pi-compare-table">
                <thead>
                    <tr>
                        <th>Comparison Report</th>
                        <th>Client Name</th>
                        <th>Company Name</th>
                        <th>Periods Compared</th>
                        <th>Generated</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="piCompareTableBody">
                    @foreach($compareReports as $cr)
                    @php
                        $crCompany    = $cr->company?->company_name ?? '—';
                        $crClient     = $cr->user?->name ?? '—';
                        $crName       = $crCompany . ' — ' . $cr->period_a . ' vs ' . $cr->period_b;
                        $crViewUrl    = route('partner-insights.compare.view', $cr->encrypted_id);
                    @endphp
                    <tr id="pi-cr-row-{{ $cr->id }}" data-client="{{ $crClient }}" data-search="{{ strtolower($crName . ' ' . $crClient . ' ' . $crCompany) }}">
                        <td>
                            <div class="pi-name-cell">
                                <span class="pi-name-icon">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </span>
                                <a href="{{ $crViewUrl }}" class="pi-name-link">{{ $crName }}</a>
                            </div>
                        </td>
                        <td style="color:#64748b;">{{ $crClient }}</td>
                        <td style="color:#64748b;">{{ $crCompany }}</td>
                        <td>
                            <div class="pi-period-pill">
                                <span class="pi-period-tag">{{ $cr->period_a }}</span>
                                <span class="pi-period-arrow">→</span>
                                <span class="pi-period-tag b">{{ $cr->period_b }}</span>
                            </div>
                        </td>
                        <td style="color:#64748b;" data-order="{{ $cr->created_at->format('Y-m-d H:i:s') }}">{{ $cr->created_at->format(active_date_format()) }}</td>
                        <td>
                            @if($cr->status === 'completed')
                                <span class="badge badge-completed">Ready</span>
                            @elseif($cr->status === 'failed')
                                <span class="badge badge-failed">Failed</span>
                        </div>
                    </td>
                    <td style="color:#64748b;" data-date="{{ $report->created_at->format('Y-m-d') }}" data-order="{{ $report->created_at->format('Y-m-d H:i:s') }}">{{ $report->created_at->format(active_date_format() . ', g:i A') }}</td>
                    <td>
                        @if($periodLabel)
                            <span class="pi-period-badge pi-period-{{ $periodType }}">{{ $periodLabel }}</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="color:#64748b;"><div class="cell-trunc-sm" title="{{ $clientName }}">{{ $clientName }}</div></td>
                    <td style="color:#64748b;"><div class="cell-trunc" title="{{ $companyName }}">{{ $companyName }}</div></td>
                    <td data-search="{{ $rowStatus }}">
                        @if($isDeleted)
                            <span class="badge badge-deleted">Deleted</span>
                        @elseif($isProcessing)
                            <span class="badge badge-processing" id="pi-badge-{{ $report->id }}">Processing</span>
                        @elseif($isFailed)
                            <span class="badge badge-failed">Failed</span>
                        @elseif($isReady)
                            @if($publishSt === 'published')
                                <span class="badge badge-published">Published</span>
                            @else
                                <span class="badge badge-pending">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="pi-actions">
                                <div class="dm-wrap">
                                    <button class="dot-menu" data-menu="pi-cdm-{{ $cr->id }}" title="Actions">•••</button>
                                    <div class="dm-popup" id="pi-cdm-{{ $cr->id }}">
                                        <a href="{{ $crViewUrl }}" class="dm-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            View Report
                                        </a>
                                        <div class="dm-divider"></div>
                                        <a href="javascript:void(0)" class="dm-item dm-item--danger" onclick="piDeleteCompare({{ $cr->id }}, {{ json_encode($crName) }})">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>{{-- end Comparisons tab --}}

{{-- Generate New Insight Modal --}}
<div id="piGenerateModal" class="pi-modal-overlay" onclick="piCloseModalOnBackdrop(event)">
    <div class="pi-modal">
        <button class="pi-modal-close" onclick="piCloseGenerateModal()" title="Close">&times;</button>
        <h3>Generate New Insight</h3>
        <p class="pi-modal-sub">Select a client, company and reporting period to generate CFO financial insights</p>

        <label for="piClientSelect">Select Client <span style="color:#ef4444">*</span></label>
        <select id="piClientSelect" onchange="piOnClientChange()">
            <option value="">Choose a client…</option>
        </select>

        <label for="piCompanySelect">Select Company <span style="color:#ef4444">*</span></label>
        <select id="piCompanySelect" disabled>
            <option value="">Choose a company…</option>
        </select>

        <label for="piDateRangePreset" style="margin-top:14px;display:block;font-size:13px;font-weight:500;margin-bottom:4px;">Reporting Period <span style="color:#ef4444">*</span></label>
        <select id="piDateRangePreset" onchange="piOnDateRangeChange()" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff url('data:image/svg+xml,%3Csvg width=\'10\' height=\'6\' viewBox=\'0 0 10 6\'%3E%3Cpath d=\'M1 1l4 4 4-4\' stroke=\'%2394a3b8\' stroke-width=\'1.5\' fill=\'none\' stroke-linecap=\'round\'/%3E%3C/svg%3E') no-repeat right 10px center;-webkit-appearance:none;appearance:none;box-sizing:border-box;">
            <option value="last_month" selected>Last Month</option>
            <option value="this_month">This Month</option>
            <option value="current_quarter">This Quarter</option>
            <option value="last_quarter">Last Quarter</option>
            <option value="this_year">This Year (YTD)</option>
            <option value="last_year">Last Year</option>
            <option value="custom">Custom Date Range</option>
        </select>

        <div id="piCustomDateWrap" style="display:none;">
            <div style="display:flex;gap:12px;margin-top:12px;">
                <div style="flex:1;">
                    <label for="piStartDate" style="display:block;font-size:13px;font-weight:500;margin-bottom:4px;">Start Date <span style="color:#ef4444">*</span></label>
                    <input type="date" id="piStartDate" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff;box-sizing:border-box;" />
                </div>
                <div style="flex:1;">
                    <label for="piEndDate" style="display:block;font-size:13px;font-weight:500;margin-bottom:4px;">End Date <span style="color:#ef4444">*</span></label>
                    <input type="date" id="piEndDate" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff;box-sizing:border-box;" />
                </div>
            </div>
        </div>
        <div id="pi-date-warning" style="display:none;padding:8px 12px;border-radius:8px;font-size:12.5px;color:#991b1b;background:#fef2f2;border:1px solid #fecaca;margin-top:8px;"></div>

        <div class="pi-modal-note" style="margin-top:12px;">
            Only companies with a connected accounting tool are listed. Connect more companies from the <strong>Connections</strong> page.
        </div>

        <div id="pi-limit-warning" style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;line-height:1.5;margin-top:12px"></div>

        <div class="pi-modal-actions">
            <button class="pi-modal-btn-cancel" onclick="piCloseGenerateModal()">Cancel</button>
            <button class="pi-modal-btn-generate" id="piBtnGenerate" onclick="piDoGenerate()" disabled>
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                Generate Insight
            </button>
        </div>
    </div>
</div>

{{-- Compare Insights Modal --}}
<div id="piCompareModal" class="pi-modal-overlay" onclick="piCloseCompareModalOnBackdrop(event)">
    <div class="pi-modal">
        <button class="pi-modal-close" onclick="piCloseCompareModal()" title="Close">&times;</button>
        <h3>Compare Insights</h3>
        <p class="pi-modal-sub">Select a client, company and two insight reports to compare financial performance</p>

        <label for="pcClientSelect">Select Client <span style="color:#ef4444">*</span></label>
        <select id="pcClientSelect" onchange="pcOnClientChange()">
            <option value="">Choose a client…</option>
        </select>

        <label for="pcCompanySelect">Select Company <span style="color:#ef4444">*</span></label>
        <select id="pcCompanySelect" disabled onchange="pcOnCompanyChange()">
            <option value="">Choose a company…</option>
        </select>

        <label for="pcInsightA">Insight A <span style="color:#ef4444">*</span></label>
        <select id="pcInsightA" disabled onchange="pcOnInsightAChange()">
            <option value="">Select Insight A…</option>
        </select>

        <label for="pcInsightB">Insight B <span style="color:#ef4444">*</span></label>
        <select id="pcInsightB" disabled onchange="pcUpdateCompareBtn()">
            <option value="">Select Insight B…</option>
        </select>

        <div id="pc-error" style="display:none;padding:8px 12px;border-radius:8px;font-size:12.5px;color:#991b1b;background:#fef2f2;border:1px solid #fecaca;margin-top:-8px;margin-bottom:0;"></div>

        <div class="pi-modal-actions">
            <button class="pi-modal-btn-cancel" onclick="piCloseCompareModal()">Cancel</button>
            <button class="pi-modal-btn-primary" id="pcBtnCompare" onclick="pcDoCompare()" disabled>
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Compare
            </button>
        </div>
    </div>
</div>

{{-- Download Format Modal --}}
<div id="piDownloadModal" class="pi-modal-overlay">
    <div class="pi-modal">
        <button class="pi-modal-close" onclick="piCloseDownloadModal()" title="Close">&times;</button>
        <h3>Download Report</h3>
        <p class="pi-modal-sub">Choose the format to download</p>

        <div class="pi-dl-options">
            <label class="pi-dl-option">
                <input type="radio" name="pi-dl-format" value="zip" checked>
                <div class="pi-dl-card pi-dl-selected">
                    <div class="pi-dl-icon-box zip">
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <div class="pi-dl-info">
                        <div class="pi-dl-title">KPI Data Report (ZIP)</div>
                        <div class="pi-dl-desc">Excel KPI data files packaged as a ZIP archive</div>
                    </div>
                    <div class="pi-dl-radio-dot"></div>
                </div>
            </label>
            <label class="pi-dl-option">
                <input type="radio" name="pi-dl-format" value="pdf">
                <div class="pi-dl-card">
                    <div class="pi-dl-icon-box pdf">
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="pi-dl-info">
                        <div class="pi-dl-title">Insight Analysis Report (PDF)</div>
                        <div class="pi-dl-desc">Full insight analysis report exported as PDF</div>
                    </div>
                    <div class="pi-dl-radio-dot"></div>
                </div>
            </label>
        </div>

        <div class="pi-modal-actions">
            <button class="pi-modal-btn-cancel" onclick="piCloseDownloadModal()">Cancel</button>
            <button class="pi-modal-btn-generate" onclick="piConfirmDownload()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const HDR_SPIN = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;
    const HDR_ICON = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;
    const SPIN_ICON = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;
    const GEN_ICON  = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;
    const CMP_ICON  = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`;

    var table, compareTable;

    // ── Tab switching ─────────────────────────────────────────────────────────
    window.piSwitchTab = function (tab) {
        ['reports', 'comparisons'].forEach(function (t) {
            document.getElementById('pi-tab-' + t).classList.toggle('active', t === tab);
            document.getElementById('pi-panel-' + t).classList.toggle('active', t === tab);
        });
        document.getElementById('pi-reports-actions').style.display     = (tab === 'reports')     ? 'flex' : 'none';
        document.getElementById('pi-comparisons-actions').style.display = (tab === 'comparisons') ? 'flex' : 'none';
    };

    // ── Header button sync ────────────────────────────────────────────────────
    function piSyncHeaderBtn() {
        const btn       = document.getElementById('piHeaderGenerateBtn');
        const exportBtn = document.getElementById('piExportBtn');
        const anyProcessing = table
            ? table.rows('[data-status="processing"]').count() > 0
            : !!document.querySelector('#pi-table tbody tr[data-status="processing"]');
        if (btn) {
            btn.disabled  = anyProcessing;
            btn.innerHTML = anyProcessing ? HDR_SPIN + ' Generating...' : HDR_ICON + ' Generate New Insight';
        }
        if (exportBtn) {
            exportBtn.disabled = anyProcessing;
            exportBtn.title    = anyProcessing ? 'Export unavailable while generation is in progress' : '';
        }
    }

    // ── DataTable init ────────────────────────────────────────────────────────
    $(document).ready(function () {
        table = $('#pi-table').DataTable({
            paging:       true,
            pageLength:   10,
            lengthChange: false,
            searching:    true,
            ordering:     true,
            info:         true,
            dom:          '<"dt-tscroll"t><"dt-footer"ip>',
            order:        [[1, 'desc']],
            columnDefs:   [{ orderable: false, targets: [-1] }],
            infoCallback: function(settings, start, end, max, total, pre) {
                return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total > 1 ? 'insights' : 'insight');
            },
            language: {
                infoFiltered: '',
                paginate:    { previous: '‹', next: '›' },
                emptyTable:  'No insights generated yet. Click <strong>Generate New Insight</strong> to get started.',
                zeroRecords: 'No matching records found.',
            },
        });

        compareTable = $('#pi-compare-table').DataTable({
            paging:       true,
            pageLength:   10,
            lengthChange: false,
            searching:    false,
            ordering:     true,
            info:         true,
            dom:          '<"dt-tscroll"t><"dt-footer"ip>',
            order:        [[4, 'desc']],
            columnDefs:   [{ orderable: false, targets: [-1] }],
            infoCallback: function(settings, start, end, max, total, pre) {
                return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total > 1 ? 'comparisons' : 'comparison');
            },
            language: {
                infoFiltered: '',
                paginate:    { previous: '‹', next: '›' },
                emptyTable:  'No comparisons yet. Click <strong>Compare Insights</strong> to get started.',
                zeroRecords: 'No matching records found.',
            },
        });

        // Date range ext.search
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!settings.nTable || settings.nTable.id !== 'pi-table') return true;
            var from = document.getElementById('pi-dr-from') ? document.getElementById('pi-dr-from').value : '';
            var to   = document.getElementById('pi-dr-to')   ? document.getElementById('pi-dr-to').value   : '';
            if (!from && !to) return true;
            var row = table.row(dataIndex).node();
            if (!row) return true;
            var dateVal = row.querySelector('td[data-date]')?.getAttribute('data-date') || '';
            if (!dateVal) return false;
            if (from && dateVal < from) return false;
            if (to   && dateVal > to)   return false;
            return true;
        });

        _drCbs['pi-dr'] = function () { table.draw(); };

        document.getElementById('piSearch').addEventListener('input', function () {
            table.search(this.value).draw();
        });

        // Wire status filter — col 5 (Status), search by data-search value
        document.getElementById('piStatusFilter').addEventListener('change', function () {
            table.column(5).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
        });

        piSyncHeaderBtn();

        document.querySelectorAll('#pi-table tbody tr[data-status="processing"]').forEach(function (r) {
            pollStatus(parseInt(r.id.replace('pi-row-', '')));
        });

        // Activate tab from URL ?tab=comparisons
        var urlTab = new URLSearchParams(window.location.search).get('tab');
        if (urlTab === 'comparisons') piSwitchTab('comparisons');
    });

    // ── Filters ───────────────────────────────────────────────────────────────
    window.piApplyFilters = function () {
        if (!table) return;
        const q  = (document.getElementById('piSearch')?.value ?? '').trim();
        const st = (document.getElementById('piStatusFilter')?.value ?? '');
        table.search(q);
        table.column(5).search(st ? '^' + st + '$' : '', true, false, true);
        table.draw();
    };

    window.piReset = function () {
        document.getElementById('piSearch').value       = '';
        document.getElementById('piStatusFilter').value = '';
        drClear('pi-dr');
        if (table) table.search('').columns().search('').draw();
    };

    // ── Export CSV ────────────────────────────────────────────────────────────
    window.piExportCSV = function () {
        const search    = (document.getElementById('piSearch')?.value    ?? '').trim();
        const status    = (document.getElementById('piStatusFilter')?.value ?? '').trim();
        const dateFrom  = (document.getElementById('pi-dr-from')?.value  ?? '').trim();
        const dateTo    = (document.getElementById('pi-dr-to')?.value    ?? '').trim();
        const params    = new URLSearchParams();
        if (search)   params.set('search',    search);
        if (status)   params.set('status',    status);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo)   params.set('date_to',   dateTo);
        const base = '{{ route("partner-insights.export-csv") }}';
        window.location.href = params.toString() ? base + '?' + params.toString() : base;
    };

    // ── Generate Modal ────────────────────────────────────────────────────────
    function piFmtDate(d) {
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    function piGetDateRange(preset) {
        const now = new Date();
        const y   = now.getFullYear();
        const m   = now.getMonth();
        const today = piFmtDate(now);
        switch (preset) {
            case 'this_week': {
                const dow  = now.getDay();
                const diff = (dow === 0) ? -6 : 1 - dow;
                return { start: piFmtDate(new Date(y, m, now.getDate() + diff)), end: today };
            }
            case 'this_month':
                return { start: piFmtDate(new Date(y, m, 1)), end: today };
            case 'last_month':
                return { start: piFmtDate(new Date(y, m - 1, 1)), end: piFmtDate(new Date(y, m, 0)) };
            case 'current_quarter': {
                const qm = Math.floor(m / 3) * 3;
                return { start: piFmtDate(new Date(y, qm, 1)), end: today };
            }
            case 'last_quarter': {
                const cq  = Math.floor(m / 3);
                const lqs = (cq === 0) ? new Date(y - 1, 9, 1)  : new Date(y, (cq - 1) * 3, 1);
                const lqe = (cq === 0) ? new Date(y - 1, 12, 0) : new Date(y, cq * 3, 0);
                return { start: piFmtDate(lqs), end: piFmtDate(lqe) };
            }
            case 'this_year':
                return { start: piFmtDate(new Date(y, 0, 1)), end: today };
            case 'last_year':
                return { start: piFmtDate(new Date(y - 1, 0, 1)), end: piFmtDate(new Date(y - 1, 11, 31)) };
            default:
                return { start: '', end: '' };
        }
    }

    window.piOnDateRangeChange = function () {
        const preset  = document.getElementById('piDateRangePreset').value;
        const wrap    = document.getElementById('piCustomDateWrap');
        const warnEl  = document.getElementById('pi-date-warning');
        const isCustom = preset === 'custom';
        wrap.style.display = isCustom ? 'block' : 'none';
        if (warnEl) warnEl.style.display = 'none';
        if (!isCustom) {
            const { start, end } = piGetDateRange(preset);
            const todayStr = piFmtDate(new Date());
            const startEl  = document.getElementById('piStartDate');
            const endEl    = document.getElementById('piEndDate');
            if (startEl) { startEl.value = start; startEl.max = todayStr; }
            if (endEl)   { endEl.value   = end;   endEl.max = todayStr; }
        } else {
            const todayStr = piFmtDate(new Date());
            const startEl  = document.getElementById('piStartDate');
            const endEl    = document.getElementById('piEndDate');
            if (startEl) { startEl.value = ''; startEl.max = todayStr; }
            if (endEl)   { endEl.value   = ''; endEl.max = todayStr; }
        }
    };

    window.piOpenGenerateModal = function () {
        document.getElementById('piGenerateModal').classList.add('open');
        const btn = document.getElementById('piBtnGenerate');
        const anyProcessing = !!document.querySelector('#pi-table tbody tr[data-status="processing"]');
        btn.disabled  = true;
        btn.innerHTML = anyProcessing ? SPIN_ICON + ' Generating...' : GEN_ICON + ' Generate Insight';
        const presetSel = document.getElementById('piDateRangePreset');
        if (presetSel) { presetSel.value = 'last_month'; piOnDateRangeChange(); }
        piLoadClients();
    };

    window.piCloseGenerateModal = function () {
        document.getElementById('piGenerateModal').classList.remove('open');
        document.getElementById('piClientSelect').innerHTML  = '<option value="">Choose a client…</option>';
        document.getElementById('piCompanySelect').innerHTML = '<option value="">Choose a company…</option>';
        document.getElementById('piCompanySelect').disabled  = true;
        const btn = document.getElementById('piBtnGenerate');
        btn.disabled = true; btn.innerHTML = GEN_ICON + ' Generate Insight';
        const warnEl = document.getElementById('pi-limit-warning');
        if (warnEl) warnEl.style.display = 'none';
        const dateWarn = document.getElementById('pi-date-warning');
        if (dateWarn) dateWarn.style.display = 'none';
        const presetSel = document.getElementById('piDateRangePreset');
        if (presetSel) { presetSel.value = 'last_month'; }
        const wrap = document.getElementById('piCustomDateWrap');
        if (wrap) wrap.style.display = 'none';
    };

    function piUpdateGenerateBtn() {
        const clientId  = document.getElementById('piClientSelect').value;
        const companyId = document.getElementById('piCompanySelect').value;
        const btn       = document.getElementById('piBtnGenerate');
        const warnEl    = document.getElementById('pi-limit-warning');

        if (!clientId || !companyId) {
            btn.disabled = true;
            btn.innerHTML = GEN_ICON + ' Generate Insight';
            if (warnEl) warnEl.style.display = 'none';
            return;
        }

        const selOpt = document.getElementById('piCompanySelect').selectedOptions[0];
        const limitAttr = selOpt ? selOpt.getAttribute('data-limit') : '';
        const usedAttr  = selOpt ? parseInt(selOpt.getAttribute('data-used') || '0', 10) : 0;
        const limitSet  = limitAttr !== '' && limitAttr !== null;
        const limit     = limitSet ? parseInt(limitAttr, 10) : 0;

        if (!limitSet) {
            btn.disabled = true;
            btn.innerHTML = GEN_ICON + ' Generate Insight';
            if (warnEl) {
                warnEl.style.cssText = 'display:block;padding:10px 14px;border-radius:8px;font-size:13px;line-height:1.5;margin-top:12px;background:#fffbeb;border:1px solid #fde68a;color:#92400e';
                warnEl.innerHTML = '<strong>No insight limit set for this company.</strong> Please set a limit before generating insights. <a href="{{ route("settings.index") }}#tab-insight-limits" style="color:#d97706;font-weight:600;text-decoration:underline">Go to Insight Limits settings</a>.';
            }
            return;
        }

        if (usedAttr >= limit) {
            btn.disabled = true;
            btn.innerHTML = GEN_ICON + ' Generate Insight';
            if (warnEl) {
                warnEl.style.cssText = 'display:block;padding:10px 14px;border-radius:8px;font-size:13px;line-height:1.5;margin-top:12px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b';
                warnEl.innerHTML = '<strong>Insight limit reached.</strong> This company has used <strong>' + usedAttr + '/' + limit + '</strong> insights. Please update the limit to generate more. <a href="{{ route("settings.index") }}#tab-insight-limits" style="color:#dc2626;font-weight:600;text-decoration:underline">Update limit</a>.';
            }
            return;
        }

        if (warnEl) warnEl.style.display = 'none';

        const alreadyRunning = !!document.querySelector(`#pi-table tbody tr[data-company-id="${companyId}"][data-status="processing"]`);
        btn.disabled  = alreadyRunning;
        btn.innerHTML = alreadyRunning ? SPIN_ICON + ' Generating...' : GEN_ICON + ' Generate Insight';
    }

    document.getElementById('piCompanySelect').addEventListener('change', piUpdateGenerateBtn);

    window.piCloseModalOnBackdrop = function (e) {
        if (e.target === document.getElementById('piGenerateModal')) piCloseGenerateModal();
    };

    function piLoadClients() {
        const sel = document.getElementById('piClientSelect');
        sel.innerHTML = '<option value="">Loading clients…</option>';
        sel.disabled  = true;
        fetch('{{ route("partner-insights.clients") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Choose a client…</option>';
            sel.disabled  = false;
            if (data.clients && data.clients.length > 0) {
                data.clients.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name + (c.email ? ' (' + c.email + ')' : '');
                    sel.appendChild(opt);
                });
            } else {
                sel.innerHTML = '<option value="">No QuickBooks-connected clients found</option>';
            }
        })
        .catch(() => { sel.innerHTML = '<option value="">Failed to load clients</option>'; sel.disabled = false; });
    }

    window.piOnClientChange = function () {
        const clientId = document.getElementById('piClientSelect').value;
        const compSel  = document.getElementById('piCompanySelect');
        const warnEl   = document.getElementById('pi-limit-warning');
        if (!clientId) {
            compSel.innerHTML = '<option value="">Choose a company…</option>';
            compSel.disabled  = true;
            document.getElementById('piBtnGenerate').disabled = true;
            if (warnEl) warnEl.style.display = 'none';
            return;
        }
        if (warnEl) warnEl.style.display = 'none';
        compSel.innerHTML = '<option value="">Loading companies…</option>';
        compSel.disabled  = true;
        fetch(`/partner-insights/clients/${clientId}/companies`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            compSel.innerHTML = '<option value="">Choose a company…</option>';
            compSel.disabled  = false;
            if (data.companies && data.companies.length > 0) {
                data.companies.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name.length > 50 ? c.name.substring(0, 47) + '…' : c.name;
                    opt.setAttribute('data-full-name', c.name);
                    opt.setAttribute('data-limit', c.custom_insight_limit !== null ? c.custom_insight_limit : '');
                    opt.setAttribute('data-used',  c.insights_used ?? 0);
                    compSel.appendChild(opt);
                });
                if (data.companies.length === 1) compSel.value = data.companies[0].id;
            } else {
                compSel.innerHTML = '<option value="">No QB-connected companies found</option>';
            }
            piUpdateGenerateBtn();
        })
        .catch(() => {
            compSel.innerHTML = '<option value="">Failed to load companies</option>';
            compSel.disabled = false;
            piUpdateGenerateBtn();
        });
    };

    window.piShowLimitModal = function () {
        Swal.fire({
            icon:  'warning',
            title: 'Monthly Limit Reached',
            html:  `<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">You have used all <strong>{{ $totalInsights }}</strong> insights for this billing cycle. Your limit resets on <strong>{{ now()->endOfMonth()->format(active_date_format()) }}</strong>.<br><br>Please upgrade your plan to generate more insights.</p>`,
            confirmButtonText: 'Upgrade Plan',
            showCancelButton:  true,
            cancelButtonText:  'Close',
            confirmButtonColor: '#f06b25',
        }).then(result => { if (result.isConfirmed) window.location.href = '{{ route("account.index") }}'; });
    };

    window.piDoGenerate = function () {
        const clientId    = document.getElementById('piClientSelect').value;
        const companyId   = document.getElementById('piCompanySelect').value;
        const companyName = document.getElementById('piCompanySelect').options[document.getElementById('piCompanySelect').selectedIndex]?.textContent?.trim() || '';
        const clientName  = document.getElementById('piClientSelect').options[document.getElementById('piClientSelect').selectedIndex]?.textContent?.split(' (')[0]?.trim() || '';
        if (!clientId || !companyId) return;

        const dateWarnEl = document.getElementById('pi-date-warning');
        const preset = document.getElementById('piDateRangePreset')?.value || 'this_month';
        let startDate, endDate;

        if (preset === 'custom') {
            startDate = document.getElementById('piStartDate')?.value || '';
            endDate   = document.getElementById('piEndDate')?.value   || '';
            if (!startDate || !endDate) {
                if (dateWarnEl) { dateWarnEl.textContent = 'Please select both a start date and end date.'; dateWarnEl.style.display = 'block'; }
                return;
            }
            if (startDate > endDate) {
                if (dateWarnEl) { dateWarnEl.textContent = 'Start date must be before or equal to end date.'; dateWarnEl.style.display = 'block'; }
                return;
            }
        } else {
            const range = piGetDateRange(preset);
            startDate = range.start;
            endDate   = range.end;
        }
        if (dateWarnEl) dateWarnEl.style.display = 'none';

        const selOpt    = document.getElementById('piCompanySelect').selectedOptions[0];
        const limitAttr = selOpt ? selOpt.getAttribute('data-limit') : '';
        const usedAttr  = selOpt ? parseInt(selOpt.getAttribute('data-used') || '0', 10) : 0;
        const limitSet  = limitAttr !== '' && limitAttr !== null;
        const limit     = limitSet ? parseInt(limitAttr, 10) : 0;

        if (!limitSet) {
            Swal.fire({
                icon: 'warning', title: 'Insight Limit Not Set',
                html: '<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">This company does not have an insight limit set. Please set a limit before generating insights.</p>',
                confirmButtonText: 'Set Limit', showCancelButton: true, cancelButtonText: 'Close', confirmButtonColor: '#f06b25',
            }).then(result => { if (result.isConfirmed) window.location.href = '{{ route("settings.index") }}#tab-insight-limits'; });
            return;
        }
        if (usedAttr >= limit) {
            Swal.fire({
                icon: 'error', title: 'Insight Limit Exceeded',
                html: '<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">This company has used <strong>' + usedAttr + '</strong> of <strong>' + limit + '</strong> insights. Please update the limit to continue generating insights.</p>',
                confirmButtonText: 'Update Limit', showCancelButton: true, cancelButtonText: 'Close', confirmButtonColor: '#f06b25',
            }).then(result => { if (result.isConfirmed) window.location.href = '{{ route("settings.index") }}#tab-insight-limits'; });
            return;
        }
        const hdrBtn   = document.getElementById('piHeaderGenerateBtn');
        const exportBtn = document.getElementById('piExportBtn');
        if (hdrBtn)    { hdrBtn.disabled = true; hdrBtn.innerHTML = HDR_SPIN + ' Generating...'; }
        if (exportBtn) { exportBtn.disabled = true; exportBtn.title = 'Export unavailable while generation is in progress'; }
        piCloseGenerateModal();
        Swal.fire({
            title: 'Generating insights',
            html: `<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">Starting insight generation&hellip; You can navigate to other pages freely while this runs.</p>`,
            showConfirmButton: false, allowOutsideClick: true, allowEscapeKey: true,
            didOpen: () => Swal.showLoading(),
            customClass: { popup: 'pi-swal-generating' },
        });
        fetch('{{ route("partner-insights.generate") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ company_id: parseInt(companyId), start_date: startDate, end_date: endDate }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'processing') {
                Swal.close();
                prependRow(data.report_id, data.report_name, data.client_name || clientName, data.company_name || companyName, companyId, data.user_id || '');
                pollStatus(data.report_id);
            } else if (data.limit_reached) {
                piSyncHeaderBtn();
                Swal.fire({
                    icon: 'warning', title: 'Monthly Limit Reached',
                    html: `<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">${data.message}</p>`,
                    confirmButtonText: 'Upgrade Plan', showCancelButton: true, cancelButtonText: 'Close', confirmButtonColor: '#f06b25',
                }).then(result => { if (result.isConfirmed) window.location.href = '{{ route("account.index") }}'; });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to generate insights.' });
                piSyncHeaderBtn();
            }
        })
        .catch(() => { Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' }); piSyncHeaderBtn(); });
    };

    // ── Poll status ───────────────────────────────────────────────────────────
    function pollStatus(id) {
        fetch(`/partner-insights/status/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') { setTimeout(() => pollStatus(id), 10000); return; }
            const r = data.report;
            if (r.has_insights) { markReady(r.id, r.encrypted_id); return; }
            if (r.is_failed)    { markFailed(r.id); return; }
            setTimeout(() => pollStatus(id), 8000);
        })
        .catch(() => setTimeout(() => pollStatus(id), 15000));
    }

    // ── Prepend new row ───────────────────────────────────────────────────────
    // ── Prepend new row into DataTable ────────────────────────────────────────
    // Columns: Insight Report | Generated | Period | Client | Company | Status | Action
    function prependRow(id, name, clientName, companyName, companyId, userId) {
        if (!table) return;
        const today = new Date().toISOString().slice(0, 10);
        const tr = document.createElement('tr');
        tr.id = 'pi-row-' + id;
        tr.dataset.status    = 'processing';
        tr.dataset.companyId = companyId || '';
        tr.dataset.userId    = userId || '';
        const rowDisplayName = name ? (name + ' - Insight Report') : 'Generating…';
        tr.innerHTML = `
            <td><div class="pi-name-cell">
                <span class="pi-name-icon"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                <span style="font-weight:500;">${rowDisplayName}</span>
            </div></td>
            <td style="color:#64748b;" data-date="${today}" data-order="${today}">Just now</td>
            <td><span style="color:#94a3b8;">—</span></td>
            <td style="color:#64748b;">${clientName || '—'}</td>
            <td style="color:#64748b;">${companyName || '—'}</td>
            <td data-search="processing"><span class="badge badge-processing" id="pi-badge-${id}">Processing</span></td>
            <td><div class="pi-actions"><button class="pi-btn-view" disabled>Processing…</button></div></td>`;
        table.row.add(tr).draw();
        piSyncHeaderBtn();
    }

    // ── Mark ready ────────────────────────────────────────────────────────────
    function markReady(id, encId) {
        const viewUrl = `/partner-insights/${encId}/view`;
        const row = document.getElementById('pi-row-' + id);
        if (row) {
            const badge = document.getElementById('pi-badge-' + id);
            if (badge) { badge.className = 'badge badge-draft'; badge.textContent = 'Draft'; }
            row.dataset.status = 'draft';
            if (row.cells[5]) row.cells[5].setAttribute('data-search', 'draft');

            const nameCell = row.querySelector('.pi-name-cell span[style]');
            if (nameCell) {
                const a = document.createElement('a');
                a.href = viewUrl; a.className = 'pi-name-link';
                const rawName = nameCell.textContent.trim();
                a.textContent = rawName.endsWith(' - Insight Report') ? rawName : rawName + ' - Insight Report';
                nameCell.replaceWith(a);
            }
            const actions = row.querySelector('.pi-actions');
            if (actions) {
                const userId   = row.dataset.userId || '';
                const reportId = row.id.replace('pi-row-', '');
                const portalItem = userId
                    ? `<div class="dm-divider"></div><form method="POST" action="/clients/${userId}/login-as" style="margin:0"><input type="hidden" name="_token" value="${CSRF}"><button type="submit" class="dm-item" style="width:100%;text-align:left;border:0;background:transparent;cursor:pointer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> View as Client</button></form>`
                    : '';
                const reportName = row.querySelector('.pi-name-link')?.textContent?.trim() || '';
                actions.innerHTML = `<div class="dm-wrap"><button class="dot-menu" data-menu="pi-dm-${reportId}" title="Actions">•••</button><div class="dm-popup" id="pi-dm-${reportId}"><a href="${viewUrl}" class="dm-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> View Insight</a>${portalItem}<div class="dm-divider"></div><a href="javascript:void(0)" class="dm-item" onclick="piDownload(${reportId})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Download</a><div class="dm-divider"></div><a href="javascript:void(0)" class="dm-item dm-item--danger" onclick="piDelete(${reportId}, ${JSON.stringify(reportName)})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg> Delete</a></div></div>`;
            }
            if (table) table.row(row).invalidate().draw(false);
        }
        piUpdateGenerateBtn();
        piSyncHeaderBtn();

        const compName = row?.cells[4]?.textContent?.trim() || '';
        Swal.fire({
            title: 'Insight Generated!',
            html:  `<p style="color:#6b7280;font-size:14px;line-height:1.6">Your CFO level Financial Insight is ready${compName ? ` for <strong>"${compName}"</strong>` : ''}.</p>`,
            icon: 'success', confirmButtonText: 'Ok', showCancelButton: false, confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b25',
            timer: 3000, timerProgressBar: true,
        }).then(() => window.location.reload());
    }

    // ── Mark failed ───────────────────────────────────────────────────────────
    function markFailed(id) {
        const row = document.getElementById('pi-row-' + id);
        if (!row) return;
        const badge = document.getElementById('pi-badge-' + id);
        if (badge) { badge.className = 'badge badge-failed'; badge.textContent = 'Failed'; }
        row.dataset.status = 'failed';
        if (row.cells[5]) row.cells[5].setAttribute('data-search', 'failed');
        const vb = row.querySelector('.pi-btn-view');
        if (vb) { vb.textContent = 'Failed'; vb.style.background = '#9ca3af'; }
        if (table) table.row(row).invalidate().draw(false);
        piSyncHeaderBtn();
    }

    // ── Download ──────────────────────────────────────────────────────────────
    window.piDownload = function (id) {
        const modal = document.getElementById('piDownloadModal');
        modal.dataset.reportId = id;
        document.querySelectorAll('input[name="pi-dl-format"]').forEach(r => { r.checked = r.value === 'zip'; });
        document.querySelectorAll('.pi-dl-card').forEach(card => {
            const r = card.closest('.pi-dl-option').querySelector('input[type=radio]');
            card.classList.toggle('pi-dl-selected', r && r.checked);
        });
        modal.classList.add('open');
    };

    window.piCloseDownloadModal = function () {
        document.getElementById('piDownloadModal').classList.remove('open');
    };

    window.piConfirmDownload = function () {
        const id     = document.getElementById('piDownloadModal').dataset.reportId;
        const format = document.querySelector('input[name="pi-dl-format"]:checked')?.value || 'zip';
        piCloseDownloadModal();
        if (format === 'pdf') {
            Swal.fire({ title: 'Preparing PDF', html: '<p>Generating PDF report…</p>', icon: 'info', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading() });
            window.location.href = `/partner-insights/${id}/export-pdf`;
            setTimeout(() => Swal.close(), 3500);
        } else {
            Swal.fire({ title: 'Preparing Download', html: '<p>Creating ZIP file…</p>', icon: 'info', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading() });
            window.location.href = `/partner-insights/${id}/download`;
            setTimeout(() => Swal.close(), 2500);
        }
    };

    document.querySelectorAll('input[name="pi-dl-format"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.pi-dl-card').forEach(card => {
                const r = card.closest('.pi-dl-option').querySelector('input[type=radio]');
                card.classList.toggle('pi-dl-selected', r && r.checked);
            });
        });
    });

    // ── Delete Report ─────────────────────────────────────────────────────────
    window.piDelete = function (id, name) {
        Swal.fire({
            title: 'Delete Insight?',
            html:  `<p>Deleting "<strong>${name}</strong>" cannot be undone.</p>`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete', cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', reverseButtons: true,
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`/partner-insights/${id}`, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const row = document.getElementById('pi-row-' + id);
                    if (row) {
                        row.style.opacity  = '0.65';
                        row.dataset.status = 'deleted';
                        const nameEl = row.querySelector('.pi-name-link, .pi-name-deleted, .pi-name-cell span[style]');
                        if (nameEl && !nameEl.classList.contains('pi-name-deleted')) {
                            const s = document.createElement('span'); s.className = 'pi-name-deleted'; s.textContent = nameEl.textContent.trim(); nameEl.replaceWith(s);
                        }
                        const statusCell = row.cells[5];
                        if (statusCell) { statusCell.setAttribute('data-search', 'deleted'); statusCell.innerHTML = '<span class="badge badge-deleted">Deleted</span>'; }
                        const actCell = row.cells[6];
                        if (actCell) actCell.innerHTML = '<span style="font-size:12px;color:#9ca3af;font-style:italic;">—</span>';
                        if (table) table.row(row).invalidate().draw(false);
                    }
                    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Could not delete.' });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed.' }));
        });
    };

    // ── Retry ─────────────────────────────────────────────────────────────────
    window.piRetry = function (id) {
        Swal.fire({ icon: 'info', title: 'To regenerate', text: 'Please use the Generate New Insight button and select the same client and company.' });
    };

    // ─────────────────────────────────────────────────────────────────────────
    // ── Compare Insights Modal ────────────────────────────────────────────────
    // ─────────────────────────────────────────────────────────────────────────

    window.piOpenCompareModal = function () {
        document.getElementById('piCompareModal').classList.add('open');
        pcLoadClients();
    };

    window.piCloseCompareModal = function () {
        document.getElementById('piCompareModal').classList.remove('open');
        pcResetModal();
    };

    window.piCloseCompareModalOnBackdrop = function (e) {
        if (e.target === document.getElementById('piCompareModal')) piCloseCompareModal();
    };

    function pcResetModal() {
        ['pcClientSelect','pcCompanySelect','pcInsightA','pcInsightB'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) { el.innerHTML = '<option value="">—</option>'; el.disabled = true; }
        });
        document.getElementById('pcClientSelect').innerHTML = '<option value="">Choose a client…</option>';
        document.getElementById('pcClientSelect').disabled = false;
        document.getElementById('pcCompanySelect').innerHTML = '<option value="">Choose a company…</option>';
        document.getElementById('pcInsightA').innerHTML = '<option value="">Select Insight A…</option>';
        document.getElementById('pcInsightB').innerHTML = '<option value="">Select Insight B…</option>';
        document.getElementById('pcBtnCompare').disabled = true;
        document.getElementById('pcBtnCompare').innerHTML = CMP_ICON + ' Compare';
        const errEl = document.getElementById('pc-error');
        if (errEl) errEl.style.display = 'none';
    }

    function pcLoadClients() {
        const sel = document.getElementById('pcClientSelect');
        sel.innerHTML = '<option value="">Loading clients…</option>';
        sel.disabled = true;
        fetch('{{ route("partner-insights.clients") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Choose a client…</option>';
            sel.disabled = false;
            if (data.clients && data.clients.length > 0) {
                data.clients.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name + (c.email ? ' (' + c.email + ')' : '');
                    sel.appendChild(opt);
                });
            } else {
                sel.innerHTML = '<option value="">No clients found</option>';
            }
        })
        .catch(() => { sel.innerHTML = '<option value="">Failed to load</option>'; sel.disabled = false; });
    }

    window.pcOnClientChange = function () {
        const clientId = document.getElementById('pcClientSelect').value;
        const compSel  = document.getElementById('pcCompanySelect');
        compSel.innerHTML = '<option value="">Choose a company…</option>';
        compSel.disabled = true;
        document.getElementById('pcInsightA').innerHTML = '<option value="">Select Insight A…</option>';
        document.getElementById('pcInsightA').disabled = true;
        document.getElementById('pcInsightB').innerHTML = '<option value="">Select Insight B…</option>';
        document.getElementById('pcInsightB').disabled = true;
        document.getElementById('pcBtnCompare').disabled = true;
        if (!clientId) return;

        compSel.innerHTML = '<option value="">Loading companies…</option>';
        fetch(`/partner-insights/clients/${clientId}/companies`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            compSel.innerHTML = '<option value="">Choose a company…</option>';
            compSel.disabled = false;
            if (data.companies && data.companies.length > 0) {
                data.companies.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name.length > 50 ? c.name.substring(0, 47) + '…' : c.name;
                    compSel.appendChild(opt);
                });
                if (data.companies.length === 1) { compSel.value = data.companies[0].id; pcOnCompanyChange(); }
            } else {
                compSel.innerHTML = '<option value="">No companies found</option>';
            }
        })
        .catch(() => { compSel.innerHTML = '<option value="">Failed to load</option>'; compSel.disabled = false; });
    };

    window.pcOnCompanyChange = function () {
        const companyId = document.getElementById('pcCompanySelect').value;
        const insA      = document.getElementById('pcInsightA');
        const insB      = document.getElementById('pcInsightB');
        insA.innerHTML = '<option value="">Select Insight A…</option>';
        insA.disabled = true;
        insB.innerHTML = '<option value="">Select Insight B…</option>';
        insB.disabled = true;
        document.getElementById('pcBtnCompare').disabled = true;
        if (!companyId) return;

        insA.innerHTML = '<option value="">Loading reports…</option>';
        fetch(`/partner-insights/companies/${companyId}/reports`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            insA.innerHTML = '<option value="">Select Insight A…</option>';
            insA.disabled = false;
            if (data.reports && data.reports.length > 0) {
                data.reports.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r.id;
                    opt.textContent = r.name + ' (' + r.period + ')';
                    opt.setAttribute('data-period', r.period);
                    insA.appendChild(opt);
                });
            } else {
                insA.innerHTML = '<option value="">No analyzed reports found</option>';
            }
        })
        .catch(() => { insA.innerHTML = '<option value="">Failed to load</option>'; insA.disabled = false; });
    };

    window.pcOnInsightAChange = function () {
        const insA      = document.getElementById('pcInsightA');
        const insB      = document.getElementById('pcInsightB');
        const selectedA = insA.value;
        insB.innerHTML = '<option value="">Select Insight B…</option>';
        insB.disabled = true;
        document.getElementById('pcBtnCompare').disabled = true;
        if (!selectedA) return;

        // Populate B with same options excluding A
        const opts = insA.querySelectorAll('option[value]:not([value=""])');
        insB.disabled = false;
        opts.forEach(function (opt) {
            if (opt.value !== selectedA) {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.textContent = opt.textContent;
                insB.appendChild(newOpt);
            }
        });
        if (insB.querySelectorAll('option[value]:not([value=""])').length === 0) {
            insB.innerHTML = '<option value="">Only one report available</option>';
            insB.disabled = true;
        }
    };

    window.pcUpdateCompareBtn = function () {
        const a = document.getElementById('pcInsightA').value;
        const b = document.getElementById('pcInsightB').value;
        document.getElementById('pcBtnCompare').disabled = !(a && b);
    };

    window.pcDoCompare = function () {
        const companyId = document.getElementById('pcCompanySelect').value;
        const reportIdA = document.getElementById('pcInsightA').value;
        const reportIdB = document.getElementById('pcInsightB').value;
        const errEl     = document.getElementById('pc-error');
        if (!companyId || !reportIdA || !reportIdB) return;

        const btn = document.getElementById('pcBtnCompare');
        btn.disabled = true;
        btn.innerHTML = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg> Comparing…`;

        if (errEl) errEl.style.display = 'none';

        fetch('{{ route("partner-insights.compare.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ company_id: parseInt(companyId), report_id_a: parseInt(reportIdA), report_id_b: parseInt(reportIdB) }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                piCloseCompareModal();

                // Switch to Comparisons tab
                piSwitchTab('comparisons');

                // Prepend new comparison row
                if (compareTable) {
                    const tr = document.createElement('tr');
                    tr.id = 'pi-cr-row-' + data.compare_id;
                    const crName    = (data.company_name || '—') + ' — ' + data.period_a + ' vs ' + data.period_b;
                    const crClient  = data.client_name || '—';
                    const viewUrl   = '{{ route("partner-insights.compare.view", ":eid") }}'.replace(':eid', data.encrypted_id || '');
                    const dmId      = 'pi-cdm-' + data.compare_id;
                    const statusBadge = data.compare_status === 'completed'
                        ? '<span class="badge badge-completed">Ready</span>'
                        : '<span class="badge badge-failed">Failed</span>';
                    const cmpIcon = `<svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`;
                    tr.dataset.client = crClient;
                    tr.dataset.search = (crName + ' ' + crClient + ' ' + (data.company_name || '')).toLowerCase();
                    tr.innerHTML = `
                        <td><div class="pi-name-cell">
                            <span class="pi-name-icon">${cmpIcon}</span>
                            <a href="${viewUrl}" class="pi-name-link">${crName}</a>
                        </div></td>
                        <td style="color:#64748b;">${crClient}</td>
                        <td style="color:#64748b;">${data.company_name || '—'}</td>
                        <td><div class="pi-period-pill"><span class="pi-period-tag">${data.period_a}</span><span class="pi-period-arrow">→</span><span class="pi-period-tag b">${data.period_b}</span></div></td>
                        <td style="color:#64748b;" data-order="${new Date().toISOString().slice(0, 19).replace('T',' ')}">${data.created_at}</td>
                        <td>${statusBadge}</td>
                        <td><div class="pi-actions"><div class="dm-wrap">
                            <button class="dot-menu" data-menu="${dmId}" title="Actions">•••</button>
                            <div class="dm-popup" id="${dmId}">
                                <a href="${viewUrl}" class="dm-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> View Report</a>
                                <div class="dm-divider"></div>
                                <a href="javascript:void(0)" class="dm-item dm-item--danger" onclick="piDeleteCompare(${data.compare_id}, ${JSON.stringify(crName)})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg> Delete</a>
                            </div>
                        </div></div></td>`;
                    compareTable.row.add(tr).draw();
                }

                Swal.fire({
                    icon: 'success',
                    title: data.compare_status === 'completed' ? 'Comparison Complete!' : 'Comparison Saved',
                    text: data.compare_status === 'completed'
                        ? 'The insight comparison has been generated successfully.'
                        : 'The comparison was saved but the analysis API returned an error.',
                    confirmButtonColor: '#2563eb',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            } else {
                btn.disabled = false;
                btn.innerHTML = CMP_ICON + ' Compare';
                if (errEl) { errEl.textContent = data.message || 'Failed to generate comparison.'; errEl.style.display = 'block'; }
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = CMP_ICON + ' Compare';
            if (errEl) { errEl.textContent = 'Network error. Please try again.'; errEl.style.display = 'block'; }
        });
    };

    // ── Comparison Filters ────────────────────────────────────────────────────
    window.pcApplyFilters = function () {
        const search = (document.getElementById('pcSearch')?.value || '').toLowerCase().trim();
        const client = (document.getElementById('pcClientFilter')?.value || '').toLowerCase().trim();
        const rows   = document.querySelectorAll('#piCompareTableBody tr');
        rows.forEach(function (row) {
            const rowSearch = (row.dataset.search || '').toLowerCase();
            const rowClient = (row.dataset.client || '').toLowerCase();
            const matchSearch = !search || rowSearch.includes(search);
            const matchClient = !client || rowClient === client;
            row.style.display = (matchSearch && matchClient) ? '' : 'none';
        });
    };

    window.pcReset = function () {
        const searchEl = document.getElementById('pcSearch');
        const clientEl = document.getElementById('pcClientFilter');
        if (searchEl) searchEl.value = '';
        if (clientEl) clientEl.value = '';
        pcApplyFilters();
    };

    // ── Delete Comparison ─────────────────────────────────────────────────────
    window.piDeleteCompare = function (id, name) {
        Swal.fire({
            title: 'Delete Comparison?',
            html:  `<p>Deleting "<strong>${name}</strong>" cannot be undone.</p>`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete', cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', reverseButtons: true,
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`/partner-insights/compare/${id}`, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const row = document.getElementById('pi-cr-row-' + id);
                    if (row && compareTable) { compareTable.row(row).remove().draw(false); }
                    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Could not delete.' });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed.' }));
        });
    };

}());
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
.pi-swal-generating { padding: 40px 36px 36px !important; border-radius: 16px !important; max-width: 420px !important; }
.pi-swal-generating .swal2-title { font-size: 22px !important; font-weight: 700 !important; color: #1a202c !important; margin-bottom: 10px !important; }
.pi-swal-generating .swal2-loading { margin-top: 18px !important; }
</style>
@endpush
