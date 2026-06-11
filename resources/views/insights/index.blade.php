@extends('layouts.app')

@section('page-title', 'Insights')

@push('styles')
<style>
/* ── Welcome / Onboarding ─────────────────────────────────────── */
.insights-welcome-message {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.welcome-header h2 {
    font-size: 26px;
    font-weight: 600;
    color: #1a6aa8;
    margin: 0 0 8px 0;
}
.welcome-header p {
    color: #000;
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 12px 0;
}
.btn-generate-insight { background:var(--orange);color:white;padding:8px 18px;border:none;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:filter .2s; }
.btn-generate-insight:hover { filter:brightness(.85); }
/* ── Company / Filter Bar ────────────────────────────────────── */
.insights-company-bar {
    background: white;
    border-radius: 10px;
    padding: 12px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.viewing-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: #64748b;
    white-space: nowrap;
}
.viewing-company {
    font-size: 14px;
    font-weight: 700;
    color: #1a202c;
    white-space: nowrap;
}
.company-selector-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.bar-select-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.bar-select-wrap svg.bar-select-arrow {
    position: absolute;
    right: 10px;
    pointer-events: none;
    color: #94a3b8;
    flex-shrink: 0;
}
.bar-select {
    height: 36px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    padding: 0 32px 0 12px;
    font-size: 13px;
    font-family: inherit;
    color: #1a202c;
    background: #fff;
    -webkit-appearance: none;
    appearance: none;
    outline: none;
    cursor: pointer;
    min-width: 160px;
    font-weight: 500;
    transition: border-color .15s;
}
.bar-select:hover { border-color: #9ca3af; }
.bar-select:focus { border-color: var(--blue, #1a6aa8); box-shadow: 0 0 0 3px rgba(26,106,168,.12); }
.bar-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.bar-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.bar-search-wrap svg {
    position: absolute;
    left: 10px;
    color: #94a3b8;
    pointer-events: none;
}
.bar-search-inp {
    height: 34px;
    padding: 0 12px 0 32px;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    font-size: 13px;
    font-family: inherit;
    color: #1a202c;
    background: #f8fafc;
    outline: none;
    width: 200px;
}
.bar-search-inp:focus { border-color: #1a6aa8; }
/* ── Monthly Usage Bar ───────────────────────────────────────── */
.monthly-usage-bar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #1a202c;
}
.monthly-usage-bar .usage-label { color: #64748b; font-size: 12px; }
.monthly-usage-bar .usage-count { font-weight: 700; }
.usage-progress-fill.warn  { background: #f59e0b; }
.usage-progress-fill.full  { background: #ef4444; }
/* ── Reports Table ───────────────────────────────────────────── */
.reports-table-container {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.table-header h2 { font-size: 20px; font-weight: 600; color: #1a202c; margin: 0; }
.reports-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.reports-table th {
    text-align: left;
    padding: 10px 20px;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
    overflow: hidden;
}
.reports-table td {
    padding: 14px 20px;
    font-size: 14px;
    color: #1a202c;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.reports-table th:nth-child(1), .reports-table td:nth-child(1) { width: 36%; }
.reports-table th:nth-child(2), .reports-table td:nth-child(2) { width: 16%; }
.reports-table th:nth-child(3), .reports-table td:nth-child(3) { width: 12%; }
.reports-table th:nth-child(4), .reports-table td:nth-child(4) { width: 14%; }
.reports-table th:nth-child(5), .reports-table td:nth-child(5) { width: 22%; white-space: nowrap; }
.reports-table tr:last-child td { border-bottom: none; }
.reports-table tr:hover td { background: #f8fafc; }
.insight-name-cell { display: flex; align-items: center; gap: 10px; overflow: hidden; }
.insight-name-icon { color: #94a3b8; flex-shrink: 0; }
.badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
.badge.success { background: #d4f4dd; color: #10b981; }
.badge.warning { background: #fef3c7; color: #f59e0b; }
.badge.danger  { background: #fee2e2; color: #ef4444; }
.badge.info    { background: #e0f2fe; color: #0369a1; }
.action-buttons { display: flex; gap: 8px; align-items: center; }
.btn-view-insight {
    background: #1a6aa8;
    color: white;
    padding: 6px 16px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 13px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .2s;
}
.btn-view-insight:hover { background: #155a8a; color: white; text-decoration: none; }
.btn-view-insight:disabled { background: #9ca3af; cursor: not-allowed; opacity: .7; }
.btn-icon {
    width: 32px; height: 32px;
    border-radius: 6px; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s;
}
.btn-icon.download { background: #fff3e6; color: #f06b25; border: 1px solid #fddbb5; }
.btn-icon.delete   { background: #fee2e2; color: #ef4444; }
.btn-icon:hover    { transform: scale(1.1); }
/* ── Generate Confirm Modal ─────────────────────────────────── */
.qb-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 9999; display: flex; align-items: center; justify-content: center;
    animation: fadeOverlay .2s ease;
}
@keyframes fadeOverlay { from { opacity: 0; } to { opacity: 1; } }
.qb-modal {
    background: white; border-radius: 20px;
    padding: 40px 40px 32px; width: 520px; max-width: 95vw;
    text-align: center; position: relative;
    animation: slideModal .25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
@keyframes slideModal { from { opacity: 0; transform: translateY(-18px); } to { opacity: 1; transform: translateY(0); } }
.qb-modal-logo { margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
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
    cursor: pointer; transition: filter .2s;
    box-shadow: 0 5px 10px rgba(0,0,0,.15);
}
.qb-modal .btn-modal-confirm:hover { filter: brightness(.85); }
.qb-modal .btn-modal-cancel {
    flex: 1; background: transparent; color: var(--orange);
    border: 1.5px solid var(--orange); padding: 15px 20px; border-radius: 10px;
    font-size: 15px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.qb-modal .btn-modal-cancel:hover { opacity: .8; }
/* ── Industry Modal ─────────────────────────────────────────── */
.ind-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 10000; display: none; align-items: center; justify-content: center;
}
.ind-modal-overlay.open { display: flex; }
.ind-modal {
    background: white; border-radius: 20px;
    padding: 36px 36px 28px; width: 480px; max-width: 95vw;
    text-align: center;
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
    cursor: pointer; transition: filter .2s;
}
.ind-btn-confirm:hover { filter: brightness(.85); }
.ind-btn-confirm:disabled { opacity: .6; cursor: not-allowed; }
.ind-btn-cancel {
    flex: 1; background: transparent; color: var(--orange);
    border: 1.5px solid var(--orange); padding: 12px 20px; border-radius: 10px;
    font-size: 14px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.ind-btn-cancel:hover { opacity: .8; }
/* ── Free Report Limit Reached Modal ───────────────────────── */
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
    flex: 1; background: #f06b25; color: white; border: none;
    padding: 14px 20px; border-radius: 10px; font-size: 14.5px; font-weight: 600;
    cursor: pointer; transition: background .2s; text-decoration: none;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 5px 10px rgba(240,107,37,.3);
}
.limit-btn-subscribe:hover { background: #d95a1a; color: white; text-decoration: none; }
.limit-btn-later {
    flex: 1; background: transparent; color: #f06b25;
    border: 1.5px solid #f06b25; padding: 13px 20px; border-radius: 10px;
    font-size: 14.5px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.limit-btn-later:hover { background: #fff4ee; }
</style>
@endpush

@section('content')

@if(!empty($isAdminView))
@php $adminViewUser = $viewingAsUser ?? auth()->user(); @endphp
<div style="display:flex;align-items:center;gap:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:10px 16px;margin-bottom:18px;font-size:13px;color:#1d4ed8">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
    <span>You are viewing <strong>{{ $adminViewUser->name }}</strong>'s insights in read-only mode.</span>
    <a href="{{ $backUrl ?? route('dashboard') }}" style="margin-left:auto;color:#1d4ed8;font-weight:600;text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;gap:4px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="15 18 9 12 15 6"/></svg>
        Back to My Dashboard
    </a>
</div>
@endif

@php
    $user            = $viewingAsUser ?? auth()->user();
    $hasCtx          = !empty($user->business_context);
    $monthlyLimit    = $monthlyLimit ?? null;
    $monthlyUsed     = $monthlyUsed ?? 0;
    $limitReached    = $monthlyLimit !== null && $monthlyUsed >= $monthlyLimit;
    $usagePct        = $monthlyLimit ? min(100, round($monthlyUsed / $monthlyLimit * 100)) : 0;
    $usageClass      = $usagePct >= 100 ? 'full' : ($usagePct >= 80 ? 'warn' : '');
    $planName        = $currentPlan?->name ?? null;
@endphp

{{-- ── Company Selector Bar ─────────────────────────────────────────────── --}}
{{-- ── Filter / Company Bar ─────────────────────────────────────────────── --}}
<div class="insights-company-bar">
    {{-- Left: company context --}}
    <!-- <div class="viewing-label">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M3 7a2 2 0 012-2h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            <path d="M16 3v4M8 3v4"/>
        </svg>
        Viewing Insights For
    </div> -->
    <!-- <div class="viewing-company">{{ $selectedCompany?->company_name ?? 'Your Company' }}</div> -->

    {{-- Company selector: dropdown for multiple companies, label for single --}}
    @php $hasReports = $reports->isNotEmpty(); @endphp
    @if($companies->count() > 1)
    <div class="company-selector-wrap">
        <div class="bar-select-wrap">
            <select class="bar-select" onchange="switchCompany(this.value)">
                @foreach($companies as $co)
                    <option value="{{ $co->id }}" {{ $co->id == $selectedCompanyId ? 'selected' : '' }}>
                        {{ $co->company_name }}
                    </option>
                @endforeach
            </select>
            <svg class="bar-select-arrow" width="12" height="7" viewBox="0 0 12 7" fill="none">
                <path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </div>
    @elseif($companies->count() === 1)
    <div class="company-selector-wrap">
        <span style="display:inline-flex;align-items:center;height:36px;padding:0 14px;background:#f1f5f9;border:1.5px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:500;color:#1a202c;white-space:nowrap;gap:6px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            {{ $companies->first()->company_name }}
        </span>
    </div>
    @endif

    {{-- Right: usage bar + status filter + search --}}
    <div class="bar-right">
        <div class="monthly-usage-bar" style="margin-left:0">
            <span class="usage-label">This month</span>
            <span class="usage-count" style="color:{{ $limitReached ? '#ef4444' : '#1a202c' }}">
                {{ $monthlyUsed }}
            </span>
        </div>

        {{-- Status filter --}}
        <select id="statusFilter" class="bar-select" style="min-width:140px;{{ !$hasReports ? 'opacity:0.5;cursor:not-allowed;pointer-events:none;' : '' }}" {{ !$hasReports ? 'disabled' : '' }} onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="processing">Processing</option>
            <option value="failed">Failed</option>
            <option value="deleted">Deleted</option>
        </select>

        {{-- Search --}}
        <div class="bar-search-wrap" style="{{ !$hasReports ? 'opacity:0.5;pointer-events:none;' : '' }}">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" id="searchInput" class="bar-search-inp" placeholder="Search insights…" {{ !$hasReports ? 'disabled' : '' }} oninput="applyFilters()">
        </div>
    </div>
</div>

{{-- ── Welcome (no reports yet) ────────────────────────────────────────── --}}
<div id="insightsWelcomeMessage" style="{{ $reports->isEmpty() ? '' : 'display:none;' }}">
    <div class="insights-welcome-message">
        <div class="welcome-header">
            <h2>Welcome to Insights</h2>
            <p>You're one step away from accessing your first <strong>financial insights.</strong></p>
            @if($qbConnection)
                <p>Your insights will appear here once the analysis is complete.</p>
            @else
                <p>Connect <span style="color:#f06b25;">QuickBooks</span> to begin the analysis. Your insights will appear here once the analysis is complete.</p>
            @endif
            @if($limitReached)
                <div class="limit-reached-notice" style="margin-top:12px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:13.5px;">
                    <strong>Monthly limit reached.</strong> You've used all {{ $monthlyLimit }} insights for this month. Please upgrade your plan or wait until next month.
                </div>
            @endif
        </div>

        <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e5e7eb;text-align:center;">
            <p style="color:#64748b;font-size:14px;margin:0;">
                You can also start from the Dashboard at any time.
                <a href="{{ route('dashboard') }}" style="color:#1a6aa8;text-decoration:none;font-weight:500;">Go to Dashboard →</a>
            </p>
        </div>
    </div>
</div>

{{-- ── Reports Table ────────────────────────────────────────────────────── --}}
<div id="reportsTableContainer" style="{{ $reports->isNotEmpty() ? '' : 'display:none;' }}">
    <div class="reports-table-container">
        <div class="table-header">
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <h2>All Insights</h2>
                @if(empty($isAdminView) && $qbConnection && !$limitReached)
                <!-- <button id="btnGenerateNew" onclick="generateInsight()" class="btn-generate-insight">
                    <i class="fa fa-sync-alt"></i> Generate New Insight
                </button> -->
                @elseif(!empty($isAdminView))
                {{-- read-only mode: no generate button --}}
                @elseif($limitReached)
                <span class="limit-badge-inline" style="font-size:12.5px;color:#ef4444;font-weight:600;background:#fef2f2;padding:6px 12px;border-radius:6px;border:1px solid #fecaca;">
                    Monthly limit reached ({{ $monthlyUsed }}/{{ $monthlyLimit }})
                </span>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="reports-table" id="reportsTable">
                <thead>
                    <tr>
                        <th>Insight Name</th>
                        <th>Generated</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    @foreach($reports as $report)
                    @php
                        $isDeleted  = $report->trashed() || strtolower($report->status) === 'deleted';
                        $rowStatus  = $isDeleted ? 'deleted'
                            : (in_array(strtolower($report->status), ['uploading','analyzing']) ? 'processing'
                            : ($report->isReady() ? ($report->publish_status ?? 'draft')
                            : strtolower($report->status)));
                    @endphp
                    <tr id="insight-row-{{ $report->id }}"
                        data-search="{{ strtolower($report->report_name) }}"
                        data-status="{{ $rowStatus }}"
                        style="{{ $isDeleted ? 'opacity:0.7;' : '' }}">
                        <td>
                            <div class="insight-name-cell">
                                <span class="insight-name-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                @if($isDeleted)
                                    <span style="color:#9ca3af;font-weight:500;">{{ $report->report_name }}</span>
                                @else
                                    <a href="{{ !$report->isProcessing() ? route('insights.view', $report->encrypted_id) : '#' }}"
                                       style="color:#1a6aa8;text-decoration:none;font-weight:500;{{ $report->isProcessing() ? 'pointer-events:none;color:#1a202c;' : '' }}">
                                        {{ $report->report_name }}
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td style="color:#64748b;">{{ $report->created_at->format(active_date_format()) }}</td>
                        <td style="color:#64748b;">{{ $report->formatted_file_size }}</td>
                        <td>
                            @if($isDeleted)
                                <span class="badge danger">Deleted</span>
                            @elseif($report->isReady())
                                @if(($report->publish_status ?? 'draft') === 'published')
                                    <span class="badge success">Published</span>
                                @else
                                    <span class="badge warning">Draft</span>
                                @endif
                            @elseif(strtolower($report->status) === 'failed')
                                <span class="badge danger">Failed</span>
                            @elseif(in_array(strtolower($report->status), ['uploading','analyzing']))
                                <span class="badge info" id="status-badge-{{ $report->id }}">{{ $report->status_label }}</span>
                            @else
                                <span class="badge warning">{{ $report->status_label }}</span>
                            @endif
                        </td>
                        <td>
                            @if($isDeleted)
                                <span style="font-size:13px;color:#9ca3af;font-style:italic;">No actions available</span>
                            @else
                                <div class="action-buttons">
                                    @if($report->isProcessing())
                                        <button class="btn-view-insight" disabled style="background:#9ca3af;cursor:not-allowed;">Processing…</button>
                                    @else
                                        <a href="{{ route('insights.view', $report->encrypted_id) }}" class="btn-view-insight">
                                            View Insight
                                        </a>
                                    @endif
                                    <button class="btn-icon download" title="Download Files"
                                            onclick="downloadInsight({{ $report->id }})">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </button>
                                    <button class="btn-icon delete" title="Delete"
                                            onclick="deleteInsight({{ $report->id }}, '{{ addslashes($report->report_name) }}')">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr id="insightNoMatch" style="display:none;">
                        <td colspan="5" style="text-align:center;padding:28px 16px;color:#64748b;font-size:13.5px;">No matching records found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Industry Type Selection Modal ──────────────────────────────────────── --}}
<div id="industryModal" class="ind-modal-overlay">
    <div class="ind-modal">
        <h3>What's your industry?</h3>
        <p>We use industry benchmarks to put your financials in context — from margins to cash flow trends. This helps our AI provide more relevant insights tailored to your business.</p>
        <select id="industrySelect">
            <option value="">— Select your industry —</option>
            <option value="Automotive" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Automotive' ? 'selected' : '' }}>Automotive</option>
            <option value="Technology" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Technology' ? 'selected' : '' }}>Technology</option>
            <option value="Healthcare" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
            <option value="Manufacturing" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
            <option value="Finance" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Finance' ? 'selected' : '' }}>Finance</option>
            <option value="Retail" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Retail' ? 'selected' : '' }}>Retail</option>
            <option value="Construction" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Construction' ? 'selected' : '' }}>Construction</option>
            <option value="Transportation" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Transportation' ? 'selected' : '' }}>Transportation</option>
            <option value="Energy" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Energy' ? 'selected' : '' }}>Energy</option>
            <option value="Agriculture" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Agriculture' ? 'selected' : '' }}>Agriculture</option>
            <option value="Education" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Education' ? 'selected' : '' }}>Education</option>
            <option value="Real Estate" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Real Estate' ? 'selected' : '' }}>Real Estate</option>
            <option value="Entertainment" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
            <option value="Consulting" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Consulting' ? 'selected' : '' }}>Consulting</option>
            <option value="Legal Services" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Legal Services' ? 'selected' : '' }}>Legal Services</option>
            <option value="Other" {{ ($viewingAsUser ?? auth()->user())->business_context === 'Other' ? 'selected' : '' }}>Other</option>
        </select>
        <div class="ind-modal-actions">
            <button class="ind-btn-confirm" id="indBtnConfirm" onclick="saveIndustryAndProceed()">Continue</button>
            <button class="ind-btn-cancel" onclick="closeIndustryModal()">Cancel</button>
        </div>
    </div>
</div>

{{-- ── Free Report Limit Reached Modal ─────────────────────────────────── --}}
<div id="limitReachedModal" class="limit-modal-overlay">
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
            @if($planName && $monthlyLimit)
                You're on the <strong>{{ $planName }}</strong> plan, which includes <strong>{{ $monthlyLimit }}</strong> insight{{ $monthlyLimit == 1 ? '' : 's' }} per month.
                You've used all your insights for this month.
            @elseif($monthlyLimit)
                You've used all <strong>{{ $monthlyLimit }}</strong> insights included in your plan this month.
            @else
                You've reached your monthly insight limit.
            @endif
            Upgrade your plan to generate more insights.
        </p>
        <div class="limit-modal-actions">
            <a href="{{ route('billing.index') }}" class="limit-btn-subscribe">Subscribe Now</a>
            <button class="limit-btn-later" onclick="closeLimitModal()">Maybe Later</button>
        </div>
    </div>
</div>

{{-- ── Download Report Modal ───────────────────────────────────────────── --}}
<div id="downloadReportModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:440px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.18);overflow:hidden">
        <div style="padding:22px 24px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:16px;font-weight:700;color:#0f172a">Download Report</div>
                <div style="font-size:13px;color:#64748b;margin-top:2px">Choose the format to download</div>
            </div>
            <button onclick="closeDownloadModal()" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;font-size:20px;line-height:1;padding:4px">&times;</button>
        </div>
        <div style="padding:20px 24px;display:flex;flex-direction:column;gap:10px">
            <label id="dl-opt-zip" onclick="selectDownloadOption('zip')"
                style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid #1a6aa8;border-radius:10px;cursor:pointer;transition:all .15s;background:#f0f7ff">
                <span style="width:38px;height:38px;border-radius:9px;background:#1a6aa8;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/><line x1="12" y1="11" x2="12" y2="17" stroke="#fff" stroke-width="2" stroke-linecap="round"/><path d="M9 14l3 3 3-3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:#0f172a">KPI Data Report (ZIP)</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px">Excel KPI data files packaged as a ZIP archive</div>
                </div>
                <span id="dl-radio-zip" style="width:18px;height:18px;border-radius:50%;border:2px solid #1a6aa8;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <span style="width:9px;height:9px;border-radius:50%;background:#1a6aa8;display:block"></span>
                </span>
            </label>
            <label id="dl-opt-pdf" onclick="selectDownloadOption('pdf')"
                style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;transition:all .15s;background:#fff">
                <span style="width:38px;height:38px;border-radius:9px;background:#64748b;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path d="M9 13h6M9 17h4" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:600;color:#0f172a">Insight Analysis Report (PDF)</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px">Full insight analysis report exported as PDF</div>
                </div>
                <span id="dl-radio-pdf" style="width:18px;height:18px;border-radius:50%;border:2px solid #e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <span style="width:9px;height:9px;border-radius:50%;background:transparent;display:block"></span>
                </span>
            </label>
        </div>
        <div style="padding:12px 24px 20px;display:flex;align-items:center;justify-content:flex-end;gap:10px;border-top:1px solid #f1f5f9">
            <button onclick="closeDownloadModal()" style="padding:9px 20px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:13px;font-weight:600;color:#64748b;cursor:pointer">Cancel</button>
            <button id="dl-confirm-btn" onclick="confirmDownload()" style="padding:9px 20px;border:none;border-radius:8px;background:#1a6aa8;font-size:13px;font-weight:600;color:#fff;cursor:pointer;display:flex;align-items:center;gap:6px">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download
            </button>
        </div>
    </div>
</div>

{{-- ── QB Sync Confirm Modal ────────────────────────────────────────────── --}}
<div id="qbGenerateModal" class="qb-modal-overlay" style="display:none;" onclick="closeGenerateModal(event)">
    <div class="qb-modal">
        <div class="qb-modal-logo">
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
            <button class="btn-modal-confirm" id="btnModalConfirm" onclick="doGenerate()">Generate Insights</button>
            <button class="btn-modal-cancel" onclick="closeGenerateModal()">Not now</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const CSRF              = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const SAVE_CTX_URL      = '{{ route("insights.save-business-context") }}';
    const HAS_BUSINESS_CTX  = {{ !empty(($viewingAsUser ?? auth()->user())->business_context) ? 'true' : 'false' }};
    const SELECTED_COMPANY  = {{ $selectedCompanyId }};
    const LIMIT_REACHED     = {{ $limitReached ? 'true' : 'false' }};
    const LS_KEY            = 'insights_processing_ids';
    const DASH_LS_KEY       = 'dash_processing_insight_id';
    let processingSwalOpen  = false;

    function clearProcessingStorage() {
        localStorage.removeItem(LS_KEY);
        localStorage.removeItem(DASH_LS_KEY);
    }

    // ── On load: resume polling for any in-progress reports ─────────────────
    const serverProcessingIds = @json($reports->filter(fn($r) => $r->isProcessing())->pluck('id'));

    // Also check localStorage for a report started on another page (e.g. dashboard)
    let lsId = localStorage.getItem(LS_KEY) || localStorage.getItem(DASH_LS_KEY);
    if (lsId && !localStorage.getItem(LS_KEY)) {
        localStorage.setItem(LS_KEY, lsId);
    }
    let allPollingIds = [...new Set([...serverProcessingIds, ...(lsId ? [parseInt(lsId)] : [])])];

    if (allPollingIds.length > 0) {
        showProcessingBar('Analyzing your QuickBooks data… You can navigate freely while this runs.');
        allPollingIds.forEach(id => pollStatus(id));
    }

    // ── Company switch ────────────────────────────────────────────────────────
    window.switchCompany = function (companyId) {
        const url = new URL(window.location.href);
        url.searchParams.set('company_id', companyId);
        window.location.href = url.toString();
    };

    // ── Generate (check limit → industry → QB confirmation) ──────────────────
    window.generateInsight = function () {
        if (LIMIT_REACHED) {
            openLimitModal();
            return;
        }
        if (!HAS_BUSINESS_CTX) {
            openIndustryModal();
        } else {
            showQbModal();
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
                showQbModal();
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

    // ── QB modal ──────────────────────────────────────────────────────────────
    function showQbModal() {
        const modal = document.getElementById('qbGenerateModal');
        if (modal) modal.style.display = 'flex';
    }

    window.closeGenerateModal = function (e) {
        if (e && e.target !== document.getElementById('qbGenerateModal')) return;
        const modal = document.getElementById('qbGenerateModal');
        if (modal) modal.style.display = 'none';
    };

    window.doGenerate = function () {
        const modal = document.getElementById('qbGenerateModal');
        if (modal) modal.style.display = 'none';

        const btn1 = document.getElementById('insightsSyncQBBtn');
        const btn2 = document.getElementById('btnGenerateNew');
        [btn1, btn2].forEach(b => { if (b) { b.disabled = true; b.style.opacity = '0.7'; } });

        showProcessingBar('Starting insight generation… You can navigate to other pages freely while this runs.');

        fetch('{{ route("insights.generate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ company_id: SELECTED_COMPANY }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'processing') {
                // Job dispatched — save ID so polling resumes after navigation
                localStorage.setItem(LS_KEY, data.report_id);
                showProcessingBar('Insight generation is running in the background. Feel free to navigate.');
                showTableView();
                prependPlaceholderRow(data.report_id, data.report_name);
                pollStatus(data.report_id);
            } else {
                hideProcessingBar();
                resetGenerateBtns();
                if (data.limit_reached) {
                    openLimitModal();
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
            hideProcessingBar();
            resetGenerateBtns();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
        });
    };

    // ── Poll status ───────────────────────────────────────────────────────────
    function pollStatus(reportId) {
        fetch(`/insights/status/${reportId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') { setTimeout(() => pollStatus(reportId), 10000); return; }
            const r = data.report;
            if (r.has_insights) {
                clearProcessingStorage();
                hideProcessingBar();
                resetGenerateBtns();
                markRowReady(r.id, r.encrypted_id);
                return;
            }
            if (r.is_failed) {
                clearProcessingStorage();
                hideProcessingBar();
                resetGenerateBtns();
                markRowFailed(r.id);
                Swal.fire({ icon: 'error', title: 'Generation Failed', text: 'Insight generation failed. Please try again.' });
                return;
            }
            setTimeout(() => pollStatus(reportId), 8000);
        })
        .catch(() => setTimeout(() => pollStatus(reportId), 15000));
    }

    // ── DOM helpers ───────────────────────────────────────────────────────────
    function showTableView() {
        const welcome = document.getElementById('insightsWelcomeMessage');
        const table   = document.getElementById('reportsTableContainer');
        if (welcome) welcome.style.display = 'none';
        if (table)   table.style.display = '';
    }

    function prependPlaceholderRow(id, reportName) {
        const tbody = document.getElementById('reportsTableBody');
        if (!tbody) { return; }
        const displayName = reportName || 'Generating…';
        const tr = document.createElement('tr');
        tr.id = 'insight-row-' + id;
        tr.dataset.search = displayName.toLowerCase();
        tr.innerHTML = `
            <td>
                <div class="insight-name-cell">
                    <span class="insight-name-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <span style="color:#1a202c;font-weight:500;">${displayName}</span>
                </div>
            </td>
            <td style="color:#64748b;">Just now</td>
            <td style="color:#64748b;">—</td>
            <td><span class="badge info" id="status-badge-${id}">Analyzing</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view-insight" disabled style="background:#9ca3af;">Processing…</button>
                    <button class="btn-icon download" disabled style="opacity:.4;cursor:not-allowed;" title="Download">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </button>
                    <button class="btn-icon delete" onclick="deleteInsight(${id},'This insight')" title="Delete">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </td>`;
        tbody.prepend(tr);
    }

    function markRowReady(id, encryptedId) {
        const row   = document.getElementById('insight-row-' + id);
        const badge = document.getElementById('status-badge-' + id);
        if (!row) { return; }
        if (badge) { badge.className = 'badge warning'; badge.textContent = 'Draft'; }
        if (row) row.dataset.status = 'draft';
        const viewUrl = encryptedId ? `/insights/${encryptedId}/view` : `/insights/${id}/view`;
        const actions = row.querySelector('.action-buttons');
        if (actions) {
            actions.innerHTML = `
                <a href="${viewUrl}" class="btn-view-insight">View Insight</a>
                <button class="btn-icon download" onclick="downloadInsight(${id})" title="Download">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>
                <button class="btn-icon delete" onclick="deleteInsight(${id},'This insight')" title="Delete">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>`;
        }
        const nameLink = row.querySelector('.insight-name-cell a, .insight-name-cell span[style*="color:#1a202c"]');
        if (nameLink && nameLink.tagName === 'SPAN') {
            const a = document.createElement('a');
            a.href = viewUrl;
            a.style.cssText = 'color:#1a6aa8;text-decoration:none;font-weight:500;';
            a.textContent = nameLink.textContent;
            nameLink.replaceWith(a);
        }
    }

    function markRowFailed(id) {
        const badge = document.getElementById('status-badge-' + id);
        if (badge) { badge.className = 'badge danger'; badge.textContent = 'Failed'; }
        const row = document.getElementById('insight-row-' + id);
        if (row) {
            const viewBtn = row.querySelector('.btn-view-insight');
            if (viewBtn) { viewBtn.style.background = '#9ca3af'; viewBtn.textContent = 'View Insight'; }
        }
    }

    function showProcessingBar(msg) {
        processingSwalOpen = true;
        Swal.fire({
            title: 'Generating insights',
            html: '<p style="margin:0;color:#64748b;">' + (msg || 'Syncing your QuickBooks data and generating AI insights. This may take 2-5 minutes.') + '</p>',
            allowOutsideClick: true,
            allowEscapeKey: true,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    }

    function hideProcessingBar() {
        if (processingSwalOpen && Swal.isVisible()) {
            Swal.close();
        }
        processingSwalOpen = false;
    }

    function resetGenerateBtns() {
        const btn1 = document.getElementById('insightsSyncQBBtn');
        const btn2 = document.getElementById('btnGenerateNew');
        if (btn1) { btn1.disabled = false; btn1.style.opacity = ''; btn1.innerHTML = '<i class="fa fa-sync-alt"></i> Generate Insights'; }
        if (btn2) { btn2.disabled = false; btn2.style.opacity = ''; btn2.innerHTML = '<i class="fa fa-sync-alt"></i> Generate New Insight'; }
    }

    // ── Search + Status filter ────────────────────────────────────────────────
    window.applyFilters = function () {
        const q      = (document.getElementById('searchInput')?.value ?? '').toLowerCase();
        const status = (document.getElementById('statusFilter')?.value ?? '').toLowerCase();
        let visible  = 0;
        document.querySelectorAll('#reportsTableBody tr[data-status]').forEach(row => {
            const matchSearch = !q      || (row.dataset.search || '').includes(q);
            const matchStatus = !status || (row.dataset.status || '') === status;
            const show = matchSearch && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const noMatch = document.getElementById('insightNoMatch');
        if (noMatch) noMatch.style.display = visible === 0 ? '' : 'none';
    };
    window.searchReports = window.applyFilters;

    // ── Download ──────────────────────────────────────────────────────────────
    let _dlInsightId = null;
    let _dlOption = 'zip';

    window.downloadInsight = function (id) {
        _dlInsightId = id;
        _dlOption = 'zip';
        selectDownloadOption('zip');
        document.getElementById('downloadReportModal').style.display = 'flex';
    };

    window.closeDownloadModal = function () {
        document.getElementById('downloadReportModal').style.display = 'none';
    };

    window.selectDownloadOption = function (opt) {
        _dlOption = opt;
        var isZip = opt === 'zip';

        var zipOpt = document.getElementById('dl-opt-zip');
        var pdfOpt = document.getElementById('dl-opt-pdf');
        var zipRadioInner = document.querySelector('#dl-radio-zip span');
        var pdfRadioInner = document.querySelector('#dl-radio-pdf span');
        var zipIcon = zipOpt.querySelector('span:first-child');
        var pdfIcon = pdfOpt.querySelector('span:first-child');

        zipOpt.style.border = isZip ? '2px solid #1a6aa8' : '2px solid #e2e8f0';
        zipOpt.style.background = isZip ? '#f0f7ff' : '#fff';
        document.getElementById('dl-radio-zip').style.borderColor = isZip ? '#1a6aa8' : '#e2e8f0';
        zipRadioInner.style.background = isZip ? '#1a6aa8' : 'transparent';
        zipIcon.style.background = isZip ? '#1a6aa8' : '#cbd5e1';

        pdfOpt.style.border = isZip ? '2px solid #e2e8f0' : '2px solid #1a6aa8';
        pdfOpt.style.background = isZip ? '#fff' : '#f0f7ff';
        document.getElementById('dl-radio-pdf').style.borderColor = isZip ? '#e2e8f0' : '#1a6aa8';
        pdfRadioInner.style.background = isZip ? 'transparent' : '#1a6aa8';
        pdfIcon.style.background = isZip ? '#64748b' : '#1a6aa8';
    };

    window.confirmDownload = function () {
        if (!_dlInsightId) return;
        var btn = document.getElementById('dl-confirm-btn');
        btn.textContent = 'Downloading…';
        btn.disabled = true;

        if (_dlOption === 'pdf') {
            window.location.href = '/insights/' + _dlInsightId + '/export-pdf';
        } else {
            window.location.href = '/insights/' + _dlInsightId + '/download';
        }

        setTimeout(function () {
            closeDownloadModal();
            btn.innerHTML = '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download';
            btn.disabled = false;
        }, 3000);
    };

    // Close modal on backdrop click
    document.getElementById('downloadReportModal').addEventListener('click', function (e) {
        if (e.target === this) closeDownloadModal();
    });

    // ── Refresh usage bar after delete ────────────────────────────────────────
    function refreshUsageBar(used, limit, limitReached) {
        // Update count text
        const countEl = document.querySelector('.usage-count');
        if (countEl) {
            countEl.textContent = used + ' / ' + limit;
            countEl.style.color = limitReached ? '#ef4444' : '#1a202c';
        }

        // Update progress fill
        const pct  = Math.min(100, Math.round(used / limit * 100));
        const fill = document.querySelector('.usage-progress-fill');
        if (fill) {
            fill.style.width = pct + '%';
            fill.className = 'usage-progress-fill' + (pct >= 100 ? ' full' : pct >= 80 ? ' warn' : '');
        }

        // Show/hide generate buttons based on new limit status
        const btnNew      = document.getElementById('btnGenerateNew');
        const limitBadge  = document.querySelector('.limit-badge-inline');

        if (!limitReached) {
            // Restore generate button if it was hidden
            if (!btnNew) {
                const header = document.querySelector('.table-header > div');
                if (header) {
                    const btn = document.createElement('button');
                    btn.id = 'btnGenerateNew';
                    btn.innerHTML = '<i class="fa fa-sync-alt"></i> Generate New Insight';
                    btn.style.cssText = 'background:#f06b25;color:white;padding:8px 18px;border:none;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .2s;';
                    btn.onmouseover = function(){ this.style.background='#d95a1a'; };
                    btn.onmouseout  = function(){ this.style.background='#f06b25'; };
                    btn.onclick = function(){ generateInsight(); };
                    header.appendChild(btn);
                }
            }
            if (limitBadge) limitBadge.remove();

            // Remove limit notice from welcome section too
            const limitNotice = document.querySelector('.limit-reached-notice');
            if (limitNotice) limitNotice.remove();
        }
    }

    // ── Limit-reached modal ───────────────────────────────────────────────────
    window.openLimitModal = function () {
        document.getElementById('limitReachedModal').classList.add('open');
    };

    window.closeLimitModal = function () {
        document.getElementById('limitReachedModal').classList.remove('open');
    };

    // ── Delete ────────────────────────────────────────────────────────────────
    window.deleteInsight = function (id, name) {
        Swal.fire({
            title: 'Are you sure?',
            html: `<p>Deleting "<strong>${name}</strong>" will permanently remove all CFO-level financial insights. This action cannot be undone.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete Insights',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        }).then(function(result) {
            if (!result.isConfirmed) return;

            var csrfToken = document.querySelector('meta[name="csrf-token"]')
                ? document.querySelector('meta[name="csrf-token"]').content
                : '{{ csrf_token() }}';

            fetch('/insights/' + id, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success') {
                    // Update row in-place to show Deleted status (row stays visible)
                    var row = document.getElementById('insight-row-' + id);
                    if (row) {
                        row.style.opacity = '0.7';
                        row.dataset.status = 'deleted';
                        // Name: make plain text
                        var nameCell = row.querySelector('.insight-name-cell a, .insight-name-cell span[style]');
                        if (nameCell) {
                            var span = document.createElement('span');
                            span.style.cssText = 'color:#9ca3af;font-weight:500;';
                            span.textContent = nameCell.textContent.trim();
                            nameCell.replaceWith(span);
                        }
                        // Status badge
                        var statusTd = row.cells[3];
                        if (statusTd) statusTd.innerHTML = '<span class="badge danger">Deleted</span>';
                        // Actions
                        var actionsTd = row.cells[4];
                        if (actionsTd) actionsTd.innerHTML = '<span style="font-size:13px;color:#9ca3af;font-style:italic;">No actions available</span>';
                    }
                    Swal.fire({ icon: 'success', title: 'Deleted', text: 'Insight deleted successfully.', timer: 2000, showConfirmButton: false });
                    // Refresh the monthly usage bar and generate buttons live
                    if (data.monthly_limit !== undefined && data.monthly_limit !== null) {
                        refreshUsageBar(data.monthly_used, data.monthly_limit, data.limit_reached);
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Could not delete insight.' });
                }
            })
            .catch(function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed. Please try again.' });
            });
        });
    };
}());
</script>
@endpush
