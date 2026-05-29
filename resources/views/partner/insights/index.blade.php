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
.pi-stat-card.total  { border-left-color:#1a6aa8; }
.pi-stat-card.used   { border-left-color:#10b981; }
.pi-stat-card.remain { border-left-color:#f06b25; }
.pi-stat-card.avg    { border-left-color:#1a6aa8; }
.pi-stat-top { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px; }
.pi-stat-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em; }
.pi-stat-label.total  { color:#64748b; }
.pi-stat-label.used   { color:#10b981; }
.pi-stat-label.remain { color:#f06b25; }
.pi-stat-label.avg    { color:#64748b; }
.pi-stat-value { font-size:34px;font-weight:700;line-height:1;margin-bottom:6px; }
.pi-stat-value.total  { color:#1a202c; }
.pi-stat-value.used   { color:#10b981; }
.pi-stat-value.remain { color:#f06b25; }
.pi-stat-value.avg    { color:#1a202c; }
.pi-stat-sub { font-size:12px;color:#94a3b8; }
.pi-stat-icon { width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.pi-stat-icon.total  { background:#e8f2fb; color:#1a6aa8; }
.pi-stat-icon.used   { background:#ecfdf5; color:#10b981; }
.pi-stat-icon.remain { background:#fff3e6; color:#f06b25; }
.pi-stat-icon.avg    { background:#e8f2fb; color:#1a6aa8; }

/* ── Toolbar ─────────────────────────────────────── */
.pi-toolbar { background:#fff;border-radius:12px;padding:14px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap; }
.pi-toolbar-right { margin-left:auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.pi-search-wrap { position:relative;display:flex;align-items:center; }
.pi-search-wrap svg { position:absolute;left:10px;color:#94a3b8;pointer-events:none; }
.pi-search-inp { height:34px;padding:0 12px 0 32px;border:1px solid #e2e8f0;border-radius:7px;font-size:13px;font-family:inherit;color:#1a202c;background:#f8fafc;outline:none;width:210px;transition:border-color .2s; }
.pi-search-inp:focus { border-color:#1a6aa8; }
.pi-status-sel { height:34px;border:1px solid #e2e8f0;border-radius:7px;padding:0 28px 0 10px;font-size:13px;font-family:inherit;color:#1a202c;background:#f8fafc url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 10px center;-webkit-appearance:none;appearance:none;outline:none;cursor:pointer;min-width:140px; }
.pi-status-sel:focus { border-color:#1a6aa8; }
.pi-btn-reset { height:34px;padding:0 14px;border:1px solid #ef4444;border-radius:7px;background:#fff;color:#ef4444;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s; }
.pi-btn-reset:hover { background:#fef2f2; }
.pi-btn-generate { height:38px;padding:0 20px;background:#f06b25;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;transition:background .2s;white-space:nowrap; }
.pi-btn-generate:hover:not(:disabled) { background:#d95a1a; }
.pi-btn-generate:disabled { background:#9ca3af !important;cursor:not-allowed;pointer-events:none; }
.pi-btn-export { height:38px;padding:0 16px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;color:#374151;font-size:13.5px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:7px;transition:all .2s; }
.pi-btn-export:hover { background:#f8fafc;color:#1a202c; }

/* ── Table ───────────────────────────────────────── */
.pi-table-card { background:#fff;border-radius:12px;padding:0;box-shadow:0 2px 8px rgba(0,0,0,.06);overflow:hidden; }
.pi-table { width:100%;border-collapse:collapse; }
.pi-table th { text-align:left;padding:11px 16px;font-size:11px;font-weight:700;color:#64748b;border-bottom:1px solid #f1f5f9;background:#f8fafc;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap; }
.pi-table td { padding:14px 16px;font-size:13.5px;color:#1a202c;border-bottom:1px solid #f8fafc;vertical-align:middle; }
.pi-table tr:last-child td { border-bottom:none; }
.pi-table tr:hover td { background:#fafbfc; }
.pi-name-cell { display:flex;align-items:center;gap:10px; }
.pi-name-icon { color:#94a3b8;flex-shrink:0; }
.pi-name-link { color:#1a6aa8;text-decoration:none;font-weight:500; }
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
.pi-btn-view { background:#1a6aa8;color:#fff;padding:5px 14px;border-radius:6px;font-weight:500;font-size:12.5px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .2s;white-space:nowrap; }
.pi-btn-view:hover { background:#155a8a;color:#fff;text-decoration:none; }
.pi-btn-view:disabled { background:#9ca3af;cursor:not-allowed; }
.pi-btn-portal { background:#f06b25;color:#fff;padding:5px 14px;border-radius:6px;font-weight:500;font-size:12.5px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .2s;white-space:nowrap; }
.pi-btn-portal:hover { background:#d95a1a;color:#fff;text-decoration:none; }
.pi-btn-retry { background:#f06b25;color:#fff;padding:5px 14px;border-radius:6px;font-weight:500;font-size:12.5px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:background .2s; }
.pi-btn-retry:hover { background:#d95a1a; }
.pi-btn-icon { width:30px;height:30px;border-radius:6px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;flex-shrink:0; }
.pi-btn-icon.dl  { background:#fff3e6;color:#f06b25;border:1px solid #fddbb5; }
.pi-btn-icon.del { background:#fee2e2;color:#ef4444; }
.pi-btn-icon:hover { transform:scale(1.08); }

/* ── Pagination ──────────────────────────────────── */
.pi-pagination { display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:8px; }
.pi-page-info { font-size:12.5px;color:#64748b; }
.pi-page-btns { display:flex;align-items:center;gap:4px; }
.pi-page-btn { height:32px;min-width:32px;padding:0 8px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:12.5px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .2s;white-space:nowrap; }
.pi-page-btn:hover:not(:disabled) { background:#f1f5f9;border-color:#cbd5e0; }
.pi-page-btn.active { background:#1a6aa8;color:#fff;border-color:#1a6aa8; }
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
.pi-modal select:focus { border-color:#f06b25; }
.pi-modal select:disabled { background:#f8fafc;color:#9ca3af;cursor:not-allowed; }
.pi-modal-note { background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:12.5px;color:#1e40af;margin-bottom:22px;line-height:1.5; }
.pi-modal-actions { display:flex;gap:10px; }
.pi-modal-btn-generate { flex:1;background:#f06b25;color:#fff;border:none;padding:12px 20px;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;display:flex;align-items:center;justify-content:center;gap:8px; }
.pi-modal-btn-generate:hover { background:#d95a1a; }
.pi-modal-btn-generate:disabled { opacity:.6;cursor:not-allowed; }
.pi-modal-btn-cancel { flex:1;background:#fff;color:#374151;border:1.5px solid #e2e8f0;padding:11px 20px;border-radius:9px;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s; }
.pi-modal-btn-cancel:hover { background:#f8fafc; }

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
        <button class="pi-btn-export" onclick="piExportCSV()">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export
        </button>
        <button id="piHeaderGenerateBtn" class="pi-btn-generate" onclick="piOpenGenerateModal()">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
            Generate New Insight
        </button>
    </div>
</div>

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
        <div class="pi-stat-sub">Resets {{ now()->endOfMonth()->format('M d, Y') }}</div>
    </div>
    <div class="pi-stat-card avg">
        <div class="pi-stat-top">
            <span class="pi-stat-label avg">Avg Used Per Client</span>
            <div class="pi-stat-icon avg">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
        </div>
        <div class="pi-stat-value avg">{{ $avgPerClient }}</div>
        <div class="pi-stat-sub">Insights used avg per client</div>
    </div>
</div>

{{-- Toolbar --}}
<div class="pi-toolbar">
    <div class="pi-search-wrap">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" id="piSearch" class="pi-search-inp" placeholder="Search insights…" oninput="piApplyFilters()">
    </div>
    <select id="piStatusFilter" class="pi-status-sel" onchange="piApplyFilters()">
        <option value="">All Statuses</option>
        <option value="draft">Draft</option>
        <option value="published">Published</option>
        <option value="processing">Processing</option>
        <option value="failed">Failed</option>
        <option value="deleted">Deleted</option>
    </select>
    <button class="pi-btn-reset" onclick="piReset()">Reset</button>
</div>

{{-- Table --}}
<div class="pi-table-card">
    <div class="table-responsive">
        <table class="pi-table">
            <thead>
                <tr>
                    <th>Insight Report</th>
                    <th>Client Name</th>
                    <th>Company Name</th>
                    <th>Generated</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="piTableBody">
                @forelse($reports as $report)
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
                @endphp
                <tr id="pi-row-{{ $report->id }}"
                    data-search="{{ strtolower($report->report_name . ' ' . $clientName . ' ' . $companyName) }}"
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
                                <span class="pi-name-deleted">{{ $report->report_name }}</span>
                            @elseif($isReady)
                                <a href="{{ route('partner-insights.view', $report->encrypted_id) }}" class="pi-name-link">{{ $report->report_name }}</a>
                            @else
                                <span style="color:#1a202c;font-weight:500;">{{ $report->report_name }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="color:#64748b;">{{ $clientName }}</td>
                    <td style="color:#64748b;">{{ $companyName }}</td>
                    <td style="color:#64748b;">{{ $report->created_at->format('M d, Y') }}</td>
                    <td>
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
                                    <a href="{{ route('partner-insights.view', $report->encrypted_id) }}" class="pi-btn-view">View Insights</a>
                                    @if($report->user_id)
                                    <form method="POST" action="{{ route('clients.login-as', $report->user_id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="pi-btn-portal">View Client Portal</button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="pi-empty-row">
                    <td colspan="6" style="text-align:center;padding:40px;color:#94a3b8;font-size:14px;">
                        No insights generated yet. Click <strong>Generate New Insight</strong> to get started.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pi-pagination" id="piPagination">
        <div class="pi-per-page">
            Show
            <select onchange="piSetPerPage(parseInt(this.value))">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            Entries
        </div>
        <div class="pi-page-info" id="piPageInfo">—</div>
        <div class="pi-page-btns" id="piPageBtns"></div>
    </div>
</div>

{{-- Generate New Insight Modal --}}
<div id="piGenerateModal" class="pi-modal-overlay" onclick="piCloseModalOnBackdrop(event)">
    <div class="pi-modal">
        <button class="pi-modal-close" onclick="piCloseGenerateModal()" title="Close">&times;</button>
        <h3>Generate New Insight</h3>
        <p class="pi-modal-sub">Select a client and company to generate CFO financial insights</p>

        <label for="piClientSelect">Select Client</label>
        <select id="piClientSelect" onchange="piOnClientChange()">
            <option value="">Choose a client…</option>
        </select>

        <label for="piCompanySelect">Select Company</label>
        <select id="piCompanySelect" disabled>
            <option value="">Choose a company…</option>
        </select>

        <div class="pi-modal-note">
            Only companies with a connected accounting tool are listed. Connect more companies from the <strong>Connections</strong> page.
        </div>

        <div class="pi-modal-actions">
            <button class="pi-modal-btn-cancel" onclick="piCloseGenerateModal()">Cancel</button>
            <button class="pi-modal-btn-generate" id="piBtnGenerate" onclick="piDoGenerate()" disabled>
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                Generate Insights
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Pagination state ─────────────────────────────────────────────────────
    let allRows   = [];
    let filtered  = [];
    let perPage   = 10;
    let curPage   = 1;

    const HDR_SPIN = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;
    const HDR_ICON = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;

    function piSyncHeaderBtn() {
        const btn = document.getElementById('piHeaderGenerateBtn');
        if (!btn) return;
        const anyProcessing = allRows.some(r => r.dataset.status === 'processing');
        btn.disabled  = anyProcessing;
        btn.innerHTML = anyProcessing
            ? HDR_SPIN + ' Generating...'
            : HDR_ICON + ' Generate New Insight';
    }

    function initTable() {
        const tbody = document.getElementById('piTableBody');
        allRows = Array.from(tbody.querySelectorAll('tr[data-status]'));
        filtered = [...allRows];
        renderPage();
        piSyncHeaderBtn();
        // Auto-poll for processing rows
        allRows.filter(r => r.dataset.status === 'processing').forEach(r => {
            const id = r.id.replace('pi-row-', '');
            pollStatus(parseInt(id));
        });
    }

    function renderPage() {
        const total = filtered.length;
        const pages = Math.max(1, Math.ceil(total / perPage));
        if (curPage > pages) curPage = pages;

        allRows.forEach(r => r.style.display = 'none');
        const start = (curPage - 1) * perPage;
        const slice = filtered.slice(start, start + perPage);
        slice.forEach(r => r.style.display = '');

        // Empty state
        const empty = document.getElementById('pi-empty-row');
        if (empty) empty.style.display = total === 0 ? '' : 'none';

        // Page info
        const info = document.getElementById('piPageInfo');
        if (info) {
            if (total === 0) info.textContent = 'No matching entries';
            else info.textContent = `Showing ${start + 1} to ${Math.min(start + perPage, total)} of ${total} entries`;
        }

        // Page buttons
        const btns = document.getElementById('piPageBtns');
        if (btns) {
            btns.innerHTML = '';
            const prev = document.createElement('button');
            prev.className = 'pi-page-btn pi-page-nav'; prev.textContent = 'Previous';
            prev.disabled = curPage === 1;
            prev.onclick = () => { if (curPage > 1) { curPage--; renderPage(); } };
            btns.appendChild(prev);

            const maxBtns = Math.min(pages, 5);
            let startPage = Math.max(1, curPage - 2);
            let endPage   = Math.min(pages, startPage + maxBtns - 1);
            if (endPage - startPage < maxBtns - 1) startPage = Math.max(1, endPage - maxBtns + 1);

            for (let p = startPage; p <= endPage; p++) {
                const btn = document.createElement('button');
                btn.className = 'pi-page-btn' + (p === curPage ? ' active' : '');
                btn.textContent = p;
                const pg = p;
                btn.onclick = () => { curPage = pg; renderPage(); };
                btns.appendChild(btn);
            }

            const next = document.createElement('button');
            next.className = 'pi-page-btn pi-page-nav'; next.textContent = 'Next';
            next.disabled = curPage === pages;
            next.onclick = () => { if (curPage < pages) { curPage++; renderPage(); } };
            btns.appendChild(next);
        }
    }

    window.piSetPerPage = function (n) { perPage = n; curPage = 1; renderPage(); };

    // ── Filters ──────────────────────────────────────────────────────────────
    window.piApplyFilters = function () {
        const q  = (document.getElementById('piSearch')?.value ?? '').toLowerCase().trim();
        const st = (document.getElementById('piStatusFilter')?.value ?? '').toLowerCase();
        filtered = allRows.filter(r => {
            const ms = !q  || (r.dataset.search || '').includes(q);
            const mv = !st || (r.dataset.status || '') === st;
            return ms && mv;
        });
        curPage = 1;
        renderPage();
    };

    window.piReset = function () {
        document.getElementById('piSearch').value = '';
        document.getElementById('piStatusFilter').value = '';
        filtered = [...allRows];
        curPage = 1;
        renderPage();
    };

    // ── Export CSV ────────────────────────────────────────────────────────────
    window.piExportCSV = function () {
        const rows = [['Insight Report','Client Name','Company Name','Generated','Status']];
        allRows.forEach(r => {
            const cells = r.querySelectorAll('td');
            if (cells.length < 5) return;
            rows.push([
                cells[0].querySelector('a, .pi-name-deleted, span[style]')?.textContent?.trim() ?? '',
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
            ]);
        });
        const csv = rows.map(r => r.map(c => `"${c.replace(/"/g,'""')}"`).join(',')).join('\n');
        const a = document.createElement('a');
        a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
        a.download = 'insights_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };

    // ── Generate Modal ────────────────────────────────────────────────────────
    const SPIN_ICON = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;
    const GEN_ICON  = `<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>`;

    window.piOpenGenerateModal = function () {
        document.getElementById('piGenerateModal').classList.add('open');
        const btn = document.getElementById('piBtnGenerate');
        const anyProcessing = allRows.some(r => r.dataset.status === 'processing');
        btn.disabled  = true;
        btn.innerHTML = anyProcessing ? SPIN_ICON + ' Generating...' : GEN_ICON + ' Generate Insights';
        piLoadClients();
    };

    window.piCloseGenerateModal = function () {
        document.getElementById('piGenerateModal').classList.remove('open');
        document.getElementById('piClientSelect').innerHTML  = '<option value="">Choose a client…</option>';
        document.getElementById('piCompanySelect').innerHTML = '<option value="">Choose a company…</option>';
        document.getElementById('piCompanySelect').disabled  = true;
        const btn = document.getElementById('piBtnGenerate');
        btn.disabled  = true;
        btn.innerHTML = GEN_ICON + ' Generate Insights';
    };

    function piUpdateGenerateBtn() {
        const clientId  = document.getElementById('piClientSelect').value;
        const companyId = document.getElementById('piCompanySelect').value;
        const btn = document.getElementById('piBtnGenerate');

        if (!clientId || !companyId) {
            btn.disabled   = true;
            btn.innerHTML  = GEN_ICON + ' Generate Insights';
            return;
        }

        // Check if a processing report already exists for this company
        const alreadyRunning = allRows.some(
            r => String(r.dataset.companyId) === String(companyId) && r.dataset.status === 'processing'
        );

        if (alreadyRunning) {
            btn.disabled  = true;
            btn.innerHTML = SPIN_ICON + ' Generating...';
        } else {
            btn.disabled  = false;
            btn.innerHTML = GEN_ICON + ' Generate Insights';
        }
    }

    // Re-check button state whenever company selection changes
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
        .catch(() => {
            sel.innerHTML = '<option value="">Failed to load clients</option>';
            sel.disabled  = false;
        });
    }

    window.piOnClientChange = function () {
        const clientId = document.getElementById('piClientSelect').value;
        const compSel  = document.getElementById('piCompanySelect');

        if (!clientId) {
            compSel.innerHTML = '<option value="">Choose a company…</option>';
            compSel.disabled  = true;
            document.getElementById('piBtnGenerate').disabled = true;
            return;
        }

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
                    opt.value       = c.id;
                    opt.textContent = c.name;
                    compSel.appendChild(opt);
                });
                // Auto-select if only one company
                if (data.companies.length === 1) compSel.value = data.companies[0].id;
            } else {
                compSel.innerHTML = '<option value="">No QB-connected companies found</option>';
            }
            piUpdateGenerateBtn();
        })
        .catch(() => {
            compSel.innerHTML = '<option value="">Failed to load companies</option>';
            compSel.disabled  = false;
        });
    };

    window.piDoGenerate = function () {
        const clientId    = document.getElementById('piClientSelect').value;
        const companyId   = document.getElementById('piCompanySelect').value;
        const companyName = document.getElementById('piCompanySelect').options[document.getElementById('piCompanySelect').selectedIndex]?.textContent?.trim() || '';
        const clientName  = document.getElementById('piClientSelect').options[document.getElementById('piClientSelect').selectedIndex]?.textContent?.split(' (')[0]?.trim() || '';

        if (!clientId || !companyId) return;

        // Disable header button immediately
        const hdrBtn = document.getElementById('piHeaderGenerateBtn');
        if (hdrBtn) { hdrBtn.disabled = true; hdrBtn.innerHTML = HDR_SPIN + ' Generating...'; }

        // Close the form modal and immediately show the generating overlay
        piCloseGenerateModal();

        Swal.fire({
            title: 'Generating insights',
            html: `<p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0">
                       Starting insight generation&hellip; You can navigate to other pages freely while this runs.
                   </p>`,
            showConfirmButton: false,
            allowOutsideClick: true,
            allowEscapeKey: true,
            didOpen: () => Swal.showLoading(),
            customClass: { popup: 'pi-swal-generating' },
        });

        fetch('{{ route("partner-insights.generate") }}', {
            method:  'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body:    JSON.stringify({ company_id: parseInt(companyId) }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'processing') {
                Swal.close();
                prependRow(data.report_id, data.report_name, data.client_name || clientName, data.company_name || companyName, companyId, data.user_id || '');
                pollStatus(data.report_id);
            } else {
                Swal.fire({ icon:'error', title:'Error', text: data.message || 'Failed to generate insights.' });
                piSyncHeaderBtn();
            }
        })
        .catch(() => {
            Swal.fire({ icon:'error', title:'Error', text:'Network error. Please try again.' });
            piSyncHeaderBtn();
        });
    };

    // ── Poll status ───────────────────────────────────────────────────────────
    function pollStatus(id) {
        fetch(`/partner-insights/status/${id}`, { headers: { 'Accept':'application/json' } })
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

    function prependRow(id, name, clientName, companyName, companyId, userId) {
        const tbody = document.getElementById('piTableBody');
        const empty = document.getElementById('pi-empty-row');
        if (empty) empty.style.display = 'none';

        const tr = document.createElement('tr');
        tr.id = 'pi-row-' + id;
        tr.dataset.status    = 'processing';
        tr.dataset.companyId = companyId || '';
        tr.dataset.userId    = userId || '';
        tr.dataset.search    = (name + ' ' + clientName + ' ' + companyName).toLowerCase();
        tr.innerHTML = `
            <td><div class="pi-name-cell">
                <span class="pi-name-icon"><svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                <span style="font-weight:500;">${name || 'Generating…'}</span>
            </div></td>
            <td style="color:#64748b;">${clientName || '—'}</td>
            <td style="color:#64748b;">${companyName || '—'}</td>
            <td style="color:#64748b;">Just now</td>
            <td><span class="badge badge-processing" id="pi-badge-${id}">Processing</span></td>
            <td><div class="pi-actions"><button class="pi-btn-view" disabled>Processing…</button></div></td>`;
        tbody.prepend(tr);
        allRows.unshift(tr);
        filtered.unshift(tr);
        renderPage();
        piSyncHeaderBtn();
    }

    function markReady(id, encId) {
        const row = document.getElementById('pi-row-' + id);
        if (!row) return;
        const badge = document.getElementById('pi-badge-' + id);
        if (badge) { badge.className = 'badge badge-draft'; badge.textContent = 'Draft'; }
        row.dataset.status = 'draft';

        const viewUrl = `/partner-insights/${encId}/view`;
        const nameCell = row.querySelector('.pi-name-cell span[style]');
        if (nameCell) {
            const a = document.createElement('a');
            a.href = viewUrl; a.className = 'pi-name-link'; a.textContent = nameCell.textContent;
            nameCell.replaceWith(a);
        }
        const actions = row.querySelector('.pi-actions');
        if (actions) {
            const userId = row.dataset.userId || '';
            const portalBtn = userId
                ? `<form method="POST" action="/clients/${userId}/login-as" style="display:inline;">
                       <input type="hidden" name="_token" value="${CSRF}">
                       <button type="submit" class="pi-btn-portal">View Client Portal</button>
                   </form>`
                : '';
            actions.innerHTML = `<a href="${viewUrl}" class="pi-btn-view">View Insights</a>${portalBtn}`;
        }

        // Refresh the Generate button and header button
        piUpdateGenerateBtn();
        piSyncHeaderBtn();

        // Success popup
        const companyName = row?.cells[2]?.textContent?.trim() || '';
        Swal.fire({
            title: 'Insight Generated!',
            html: `<p style="color:#6b7280;font-size:14px;line-height:1.6">
                       Your CFO level Financial Insight is ready to view for <strong>"${companyName}"</strong>.
                   </p>`,
            icon: 'success',
            confirmButtonText: 'View Insight',
            showCancelButton: false,
            confirmButtonColor: '#f06b25',
        }).then(result => {
            if (result.isConfirmed) window.location.href = viewUrl;
        });
    }

    function markFailed(id) {
        const row = document.getElementById('pi-row-' + id);
        if (!row) return;
        const badge = document.getElementById('pi-badge-' + id);
        if (badge) { badge.className = 'badge badge-failed'; badge.textContent = 'Failed'; }
        row.dataset.status = 'failed';
        const vb = row.querySelector('.pi-btn-view');
        if (vb) { vb.textContent = 'Failed'; vb.style.background = '#9ca3af'; }
        piSyncHeaderBtn();
    }

    // ── Download ──────────────────────────────────────────────────────────────
    window.piDownload = function (id) {
        Swal.fire({ title:'Preparing Download', html:'<p>Creating ZIP file…</p>', icon:'info', allowOutsideClick:false, showConfirmButton:false, willOpen:()=>Swal.showLoading() });
        window.location.href = `/partner-insights/${id}/download`;
        setTimeout(() => Swal.close(), 2500);
    };

    // ── Delete ────────────────────────────────────────────────────────────────
    window.piDelete = function (id, name) {
        Swal.fire({
            title: 'Delete Insight?',
            html:  `<p>Deleting "<strong>${name}</strong>" cannot be undone.</p>`,
            icon:  'warning', showCancelButton:true, confirmButtonText:'Delete', cancelButtonText:'Cancel',
            confirmButtonColor:'#ef4444', cancelButtonColor:'#6b7280', reverseButtons:true,
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`/partner-insights/${id}`, {
                method: 'DELETE',
                headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':CSRF, 'Content-Type':'application/json', 'Accept':'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const row = document.getElementById('pi-row-' + id);
                    if (row) {
                        row.style.opacity = '0.65';
                        row.dataset.status = 'deleted';
                        const nameEl = row.querySelector('.pi-name-link, .pi-name-deleted, .pi-name-cell span[style]');
                        if (nameEl && !nameEl.classList.contains('pi-name-deleted')) {
                            const s = document.createElement('span'); s.className = 'pi-name-deleted'; s.textContent = nameEl.textContent.trim(); nameEl.replaceWith(s);
                        }
                        const badge = row.cells[4]; if (badge) badge.innerHTML = '<span class="badge badge-deleted">Deleted</span>';
                        const act   = row.cells[5]; if (act)   act.innerHTML = '<span style="font-size:12px;color:#9ca3af;font-style:italic;">—</span>';
                    }
                    Swal.fire({ icon:'success', title:'Deleted', timer:1800, showConfirmButton:false });
                } else {
                    Swal.fire({ icon:'error', title:'Error', text: data.message || 'Could not delete.' });
                }
            })
            .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Request failed.' }));
        });
    };

    // ── Retry (for failed) ────────────────────────────────────────────────────
    window.piRetry = function (id) {
        Swal.fire({ icon:'info', title:'To regenerate', text:'Please use the Generate New Insight button and select the same client and company.' });
    };

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', initTable);
    if (document.readyState === 'complete' || document.readyState === 'interactive') initTable();
}());
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
.pi-swal-generating { padding: 40px 36px 36px !important; border-radius: 16px !important; max-width: 420px !important; }
.pi-swal-generating .swal2-title { font-size: 22px !important; font-weight: 700 !important; color: #1a202c !important; margin-bottom: 10px !important; }
.pi-swal-generating .swal2-loading { margin-top: 18px !important; }
</style>
@endpush
