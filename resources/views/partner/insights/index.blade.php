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
.pi-stats-row { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px; }
.pi-stat-card { background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 8px rgba(0,0,0,.06);border-left:4px solid transparent; }
.pi-stat-card.total  { border-left-color:var(--blue); }
.pi-stat-card.used   { border-left-color:#10b981; }
.pi-stat-card.remain { border-left-color:var(--orange); }
.pi-stat-card.avg    { border-left-color:var(--blue); }
.pi-stat-top { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px; }
.pi-stat-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em; }
.pi-stat-label.total  { color:#64748b; }
.pi-stat-label.used   { color:#10b981; }
.pi-stat-label.remain { color:var(--orange); }
.pi-stat-label.avg    { color:#64748b; }
.pi-stat-value { font-size:34px;font-weight:700;line-height:1;margin-bottom:6px; }
.pi-stat-value.total  { color:#1a202c; }
.pi-stat-value.used   { color:#10b981; }
.pi-stat-value.remain { color:var(--orange); }
.pi-stat-value.avg    { color:#1a202c; }
.pi-stat-sub { font-size:12px;color:#94a3b8; }
.pi-stat-icon { width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.pi-stat-icon.total  { background:var(--blue-light); color:var(--blue); }
.pi-stat-icon.used   { background:#ecfdf5; color:#10b981; }
.pi-stat-icon.remain { background:var(--orange-light); color:var(--orange); }
.pi-stat-icon.avg    { background:var(--blue-light); color:var(--blue); }

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

/* ── Badges ──────────────────────────────────────── */
.badge { padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;display:inline-block; }
.badge-draft      { background:#fef3c7;color:#92400e; }
.badge-published  { background:#d1fae5;color:#065f46; }
.badge-processing { background:#e0f2fe;color:#0369a1; }
.badge-failed     { background:#fee2e2;color:#991b1b; }
.badge-deleted    { background:#f3f4f6;color:#6b7280; }

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

/* ── Pagination ──────────────────────────────────── */
.pi-pagination { display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:8px; }
.pi-page-info { font-size:12.5px;color:#64748b; }
.pi-page-btns { display:flex;align-items:center;gap:4px; }
.pi-page-btn { height:32px;min-width:32px;padding:0 8px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:12.5px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .2s;white-space:nowrap; }
.pi-page-btn:hover:not(:disabled) { background:#f1f5f9;border-color:#cbd5e0; }
.pi-page-btn.active { background:var(--blue);color:#fff;border-color:var(--blue); }
.pi-page-btn:disabled { opacity:.45;cursor:not-allowed; }
.pi-page-btn.pi-page-nav { padding:0 14px;color:#374151; }
.pi-per-page { display:flex;align-items:center;gap:6px;font-size:12.5px;color:#64748b; }
.pi-per-page select { height:30px;border:1px solid #e2e8f0;border-radius:5px;padding:0 6px;font-size:12px;background:#fff;outline:none; }

/* ── Generate Modal ──────────────────────────────── */
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
    <div class="pi-page-header-actions">
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
                    $displayName = trim(preg_replace('/\s*\(Analysis\s+\d+\)\s*$/i', '', $report->report_name)) . ' - Insights Report';
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
                                <span class="pi-name-deleted">{{ $displayName }}</span>
                            @elseif($isReady)
                                <a href="{{ route('partner-insights.view', $report->encrypted_id) }}" class="pi-name-link">{{ $displayName }}</a>
                            @else
                                <span style="color:#1a202c;font-weight:500;">{{ $displayName }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="color:#64748b;" data-date="{{ $report->created_at->format('Y-m-d') }}" data-order="{{ $report->created_at->format('Y-m-d H:i:s') }}">{{ $report->created_at->format(active_date_format() . ', g:i A') }}</td>
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

{{-- Generate New Insight Modal --}}
<div id="piGenerateModal" class="pi-modal-overlay" onclick="piCloseModalOnBackdrop(event)">
    <div class="pi-modal">
        <button class="pi-modal-close" onclick="piCloseGenerateModal()" title="Close">&times;</button>
        <h3>Generate New Insight</h3>
        <p class="pi-modal-sub">Select a client and company to generate CFO financial insights</p>

        <label for="piClientSelect">Select Client <span style="color:#ef4444">*</span></label>
        <select id="piClientSelect" onchange="piOnClientChange()">
            <option value="">Choose a client…</option>
        </select>

        <label for="piCompanySelect">Select Company <span style="color:#ef4444">*</span></label>
        <select id="piCompanySelect" disabled>
            <option value="">Choose a company…</option>
        </select>

        <div style="display:flex;gap:12px;margin-top:14px;">
            <div style="flex:1;">
                <label for="piStartDate" style="display:block;font-size:13px;font-weight:500;margin-bottom:4px;">Start Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="piStartDate" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff;box-sizing:border-box;" />
            </div>
            <div style="flex:1;">
                <label for="piEndDate" style="display:block;font-size:13px;font-weight:500;margin-bottom:4px;">End Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="piEndDate" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff;box-sizing:border-box;" />
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

    var table;

    // ── Header button sync ────────────────────────────────────────────────────
    function piSyncHeaderBtn() {
        const btn       = document.getElementById('piHeaderGenerateBtn');
        const exportBtn = document.getElementById('piExportBtn');
        // Use DataTables API when available so all pages are checked, not just the visible DOM
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

        // Date range ext.search (col 1 = Generated, data-date attr)
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

        // Wire search input
        document.getElementById('piSearch').addEventListener('input', function () {
            table.search(this.value).draw();
        });

        // Wire status filter — col 4 (Status), search by data-search value
        document.getElementById('piStatusFilter').addEventListener('change', function () {
            table.column(4).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
        });

        piSyncHeaderBtn();

        // Auto-poll any in-progress rows
        document.querySelectorAll('#pi-table tbody tr[data-status="processing"]').forEach(function (r) {
            pollStatus(parseInt(r.id.replace('pi-row-', '')));
        });
    });

    // ── Filters ───────────────────────────────────────────────────────────────
    window.piApplyFilters = function () {
        if (!table) return;
        const q  = (document.getElementById('piSearch')?.value ?? '').trim();
        const st = (document.getElementById('piStatusFilter')?.value ?? '');
        table.search(q);
        table.column(4).search(st ? '^' + st + '$' : '', true, false, true);
        table.draw();
    };

    window.piReset = function () {
        document.getElementById('piSearch').value       = '';
        document.getElementById('piStatusFilter').value = '';
        drClear('pi-dr');
        if (table) table.search('').columns().search('').draw();
    };


    // ── Export CSV (with current search/filter state) ─────────────────────────
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
    function piSetDefaultDates() {
        const today = new Date();
        const yyyy  = today.getFullYear();
        const mm    = String(today.getMonth() + 1).padStart(2, '0');
        const dd    = String(today.getDate()).padStart(2, '0');
        const todayStr = `${yyyy}-${mm}-${dd}`;

        // Default: 12 months ago (start of that month) → today
        const startD = new Date(today.getFullYear(), today.getMonth() - 11, 1);
        const sy = startD.getFullYear();
        const sm = String(startD.getMonth() + 1).padStart(2, '0');
        const startStr = `${sy}-${sm}-01`;

        const endEl   = document.getElementById('piEndDate');
        const startEl = document.getElementById('piStartDate');
        if (endEl)   { endEl.value   = todayStr; endEl.max = todayStr; }
        if (startEl) { startEl.value = startStr; startEl.max = todayStr; }
    }

    window.piOpenGenerateModal = function () {
        document.getElementById('piGenerateModal').classList.add('open');
        const btn = document.getElementById('piBtnGenerate');
        const anyProcessing = !!document.querySelector('#pi-table tbody tr[data-status="processing"]');
        btn.disabled  = true;
        btn.innerHTML = anyProcessing ? SPIN_ICON + ' Generating...' : GEN_ICON + ' Generate Insight';
        piSetDefaultDates();
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

        // Read limit data from selected option
        const selOpt = document.getElementById('piCompanySelect').selectedOptions[0];
        const limitAttr = selOpt ? selOpt.getAttribute('data-limit') : '';
        const usedAttr  = selOpt ? parseInt(selOpt.getAttribute('data-used') || '0', 10) : 0;
        const limitSet  = limitAttr !== '' && limitAttr !== null;
        const limit     = limitSet ? parseInt(limitAttr, 10) : 0;

        // Check: limit not set
        if (!limitSet) {
            btn.disabled = true;
            btn.innerHTML = GEN_ICON + ' Generate Insight';
            if (warnEl) {
                warnEl.style.cssText = 'display:block;padding:10px 14px;border-radius:8px;font-size:13px;line-height:1.5;margin-top:12px;background:#fffbeb;border:1px solid #fde68a;color:#92400e';
                warnEl.innerHTML = '<strong>No insight limit set for this company.</strong> Please set a limit before generating insights. <a href="{{ route("settings.index") }}#tab-insight-limits" style="color:#d97706;font-weight:600;text-decoration:underline">Go to Insight Limits settings</a>.';
            }
            return;
        }

        // Check: limit exhausted
        if (usedAttr >= limit) {
            btn.disabled = true;
            btn.innerHTML = GEN_ICON + ' Generate Insight';
            if (warnEl) {
                warnEl.style.cssText = 'display:block;padding:10px 14px;border-radius:8px;font-size:13px;line-height:1.5;margin-top:12px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b';
                warnEl.innerHTML = '<strong>Insight limit reached.</strong> This company has used <strong>' + usedAttr + '/' + limit + '</strong> insights. Please update the limit to generate more. <a href="{{ route("settings.index") }}#tab-insight-limits" style="color:#dc2626;font-weight:600;text-decoration:underline">Update limit</a>.';
            }
            return;
        }

        // All good — clear warning
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
        // Clear warning immediately while companies are loading
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

        const startDate  = document.getElementById('piStartDate')?.value || '';
        const endDate    = document.getElementById('piEndDate')?.value || '';
        const dateWarnEl = document.getElementById('pi-date-warning');

        if (!startDate || !endDate) {
            if (dateWarnEl) { dateWarnEl.textContent = 'Please select both a start date and end date.'; dateWarnEl.style.display = 'block'; }
            return;
        }
        if (startDate > endDate) {
            if (dateWarnEl) { dateWarnEl.textContent = 'Start date must be before or equal to end date.'; dateWarnEl.style.display = 'block'; }
            return;
        }
        if (dateWarnEl) dateWarnEl.style.display = 'none';

        // Guard: check insight limit before calling the server
        const selOpt    = document.getElementById('piCompanySelect').selectedOptions[0];
        const limitAttr = selOpt ? selOpt.getAttribute('data-limit') : '';
        const usedAttr  = selOpt ? parseInt(selOpt.getAttribute('data-used') || '0', 10) : 0;
        const limitSet  = limitAttr !== '' && limitAttr !== null;
        const limit     = limitSet ? parseInt(limitAttr, 10) : 0;

        if (!limitSet) {
            Swal.fire({
                icon: 'warning',
                title: 'Insight Limit Not Set',
                html: '<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">This company does not have an insight limit set. Please set a limit before generating insights.</p>',
                confirmButtonText: 'Set Limit',
                showCancelButton: true,
                cancelButtonText: 'Close',
                confirmButtonColor: '#f06b25',
            }).then(result => {
                if (result.isConfirmed) window.location.href = '{{ route("settings.index") }}#tab-insight-limits';
            });
            return;
        }

        if (usedAttr >= limit) {
            Swal.fire({
                icon: 'error',
                title: 'Insight Limit Exceeded',
                html: '<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">This company has used <strong>' + usedAttr + '</strong> of <strong>' + limit + '</strong> insights. Please update the limit to continue generating insights.</p>',
                confirmButtonText: 'Update Limit',
                showCancelButton: true,
                cancelButtonText: 'Close',
                confirmButtonColor: '#f06b25',
            }).then(result => {
                if (result.isConfirmed) window.location.href = '{{ route("settings.index") }}#tab-insight-limits';
            });
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
                    icon:  'warning',
                    title: 'Monthly Limit Reached',
                    html:  `<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">${data.message}</p>`,
                    confirmButtonText: 'Upgrade Plan',
                    showCancelButton:  true,
                    cancelButtonText:  'Close',
                    confirmButtonColor: '#f06b25',
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

    // ── Prepend new row into DataTable ────────────────────────────────────────
    // Columns: Insight Report | Generated | Client | Company | Status | Action
    function prependRow(id, name, clientName, companyName, companyId, userId) {
        if (!table) return;
        const today = new Date().toISOString().slice(0, 10);
        const tr = document.createElement('tr');
        tr.id = 'pi-row-' + id;
        tr.dataset.status    = 'processing';
        tr.dataset.companyId = companyId || '';
        tr.dataset.userId    = userId || '';
        const rowDisplayName = name ? (name + ' - Insights Report') : 'Generating…';
        tr.innerHTML = `
            <td><div class="pi-name-cell">
                <span class="pi-name-icon"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                <span style="font-weight:500;">${rowDisplayName}</span>
            </div></td>
            <td style="color:#64748b;" data-date="${today}" data-order="${today}">Just now</td>
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
            if (row.cells[4]) row.cells[4].setAttribute('data-search', 'draft');

            const nameCell = row.querySelector('.pi-name-cell span[style]');
            if (nameCell) {
                const a = document.createElement('a');
                a.href = viewUrl; a.className = 'pi-name-link';
                const rawName = nameCell.textContent.trim();
                a.textContent = rawName.endsWith(' - Insights Report') ? rawName : rawName + ' - Insights Report';
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

        const compName = row?.cells[3]?.textContent?.trim() || '';
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
        if (row.cells[4]) row.cells[4].setAttribute('data-search', 'failed');
        const vb = row.querySelector('.pi-btn-view');
        if (vb) { vb.textContent = 'Failed'; vb.style.background = '#9ca3af'; }
        if (table) table.row(row).invalidate().draw(false);
        piSyncHeaderBtn();
    }

    // ── Download ──────────────────────────────────────────────────────────────
    window.piDownload = function (id) {
        const modal = document.getElementById('piDownloadModal');
        modal.dataset.reportId = id;
        // Reset to ZIP selection
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

    // Update card selection visual when radio changes
    document.querySelectorAll('input[name="pi-dl-format"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.pi-dl-card').forEach(card => {
                const r = card.closest('.pi-dl-option').querySelector('input[type=radio]');
                card.classList.toggle('pi-dl-selected', r && r.checked);
            });
        });
    });

    // ── Delete ────────────────────────────────────────────────────────────────
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
}());
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
.pi-swal-generating { padding: 40px 36px 36px !important; border-radius: 16px !important; max-width: 420px !important; }
.pi-swal-generating .swal2-title { font-size: 22px !important; font-weight: 700 !important; color: #1a202c !important; margin-bottom: 10px !important; }
.pi-swal-generating .swal2-loading { margin-top: 18px !important; }
</style>
@endpush
