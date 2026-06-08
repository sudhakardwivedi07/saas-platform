@extends('layouts.app')
@section('page-title', 'Insights')

@push('styles')
<style>
.insights-welcome-message { background:white;border-radius:12px;padding:40px;box-shadow:0 2px 8px rgba(0,0,0,.08); }
.welcome-header h2 { font-size:26px;font-weight:600;color:#1a6aa8;margin:0 0 8px; }
.welcome-header p  { color:#000;font-size:15px;line-height:1.6;margin:0 0 12px; }
.dashboard-action-cards-row { display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:30px; }
.dashboard-action-card { background:white;border:1px solid #dbdbdb;border-radius:5px;padding:24px;transition:all .3s ease; }
.dashboard-action-card:hover { border-color:var(--orange);box-shadow:0 4px 12px rgba(21,89,166,.1); }
.dashboard-action-card-icon { width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-bottom:16px; }
.dashboard-action-card h3 { font-size:18px;font-weight:600;color:#1a202c;margin:0 0 8px; }
.dashboard-action-card p  { color:#000;font-size:15px;line-height:1.5;margin:0 0 20px; }
.dashboard-action-card-btn { width:100%;padding:16px 24px;border-radius:8px;font-size:16px;font-weight:500;cursor:pointer;transition:all .3s ease;border:none;display:flex;align-items:center;justify-content:center;gap:8px; }
.dashboard-action-card-btn.primary { background:var(--orange);color:white; }
.dashboard-action-card-btn.primary:hover { filter:brightness(.85);color:white; }
.dashboard-action-card-btn:disabled { opacity:.7;cursor:not-allowed; }
.btn-generate-insight { background:var(--orange);color:white;padding:8px 18px;border:none;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:filter .2s; }
.btn-generate-insight:hover { filter:brightness(.85); }
.fi-bar { background:white;border-radius:10px;padding:12px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap; }
.bar-right { margin-left:auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.bar-select { height:34px;border:1px solid #e2e8f0;border-radius:7px;padding:0 28px 0 10px;font-size:13px;font-family:inherit;color:#1a202c;background:#f8fafc url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 10px center;-webkit-appearance:none;appearance:none;outline:none;cursor:pointer;min-width:140px; }
.bar-select:focus { border-color:#1a6aa8; }
.bar-search-wrap { position:relative;display:flex;align-items:center; }
.bar-search-wrap svg { position:absolute;left:10px;color:#94a3b8;pointer-events:none; }
.bar-search-inp { height:34px;padding:0 12px 0 32px;border:1px solid #e2e8f0;border-radius:7px;font-size:13px;font-family:inherit;color:#1a202c;background:#f8fafc;outline:none;width:200px; }
.bar-search-inp:focus { border-color:#1a6aa8; }
.reports-table-container { background:white;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,.08); }
.table-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:20px; }
.table-header h2 { font-size:20px;font-weight:600;color:#1a202c;margin:0; }
.reports-table { width:100%;border-collapse:collapse; }
.reports-table th { text-align:left;padding:10px 14px;font-size:12px;font-weight:700;color:#64748b;border-bottom:1px solid #e2e8f0;background:#f8fafc;text-transform:uppercase;letter-spacing:.04em; }
.reports-table td { padding:14px;font-size:14px;color:#1a202c;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
.reports-table tr:last-child td { border-bottom:none; }
.reports-table tr:hover td { background:#f8fafc; }
.insight-name-cell { display:flex;align-items:center;gap:10px; }
.insight-name-icon { color:#94a3b8;flex-shrink:0; }
.badge { padding:4px 12px;border-radius:20px;font-size:12px;font-weight:500; }
.badge.success { background:#d4f4dd;color:#10b981; }
.badge.warning { background:#fef3c7;color:#f59e0b; }
.badge.danger  { background:#fee2e2;color:#ef4444; }
.badge.info    { background:#e0f2fe;color:#0369a1; }
.action-buttons { display:flex;gap:8px;align-items:center; }
.btn-view-insight { background:#1a6aa8;color:white;padding:6px 16px;border-radius:6px;font-weight:500;font-size:13px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s; }
.btn-view-insight:hover { background:#155a8a;color:white;text-decoration:none; }
.btn-view-insight:disabled { background:#9ca3af;cursor:not-allowed;opacity:.7; }
.btn-icon { width:32px;height:32px;border-radius:6px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s; }
.btn-icon.download { background:#fff3e6;color:var(--orange);border:1px solid #fddbb5; }
.btn-icon.delete   { background:#fee2e2;color:#ef4444; }
.btn-icon:hover    { transform:scale(1.1); }
.qb-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:flex;align-items:center;justify-content:center;animation:fadeOverlay .2s ease; }
@keyframes fadeOverlay { from{opacity:0} to{opacity:1} }
.qb-modal { background:white;border-radius:20px;padding:40px 40px 32px;width:520px;max-width:95vw;text-align:center;position:relative;animation:slideModal .25s ease;box-shadow:0 20px 60px rgba(0,0,0,.18); }
@keyframes slideModal { from{opacity:0;transform:translateY(-18px)} to{opacity:1;transform:translateY(0)} }
.qb-modal-logo { margin:0 auto 16px;display:flex;align-items:center;justify-content:center; }
.qb-modal h3 { font-size:24px;font-weight:700;color:#1a202c;margin:0 0 12px; }
.qb-modal .qb-modal-sub { font-size:15px;color:#6b7280;line-height:1.6;margin:0 0 4px; }
.qb-modal ul { list-style:none;padding:0;margin:12px auto 10px;text-align:left;display:inline-block; }
.qb-modal ul li { font-size:15px;color:#374151;padding:3px 0;display:flex;align-items:center;gap:10px; }
.qb-modal ul li::before { content:'○';color:#6b7280;flex-shrink:0; }
.qb-modal .qb-modal-timing { font-size:15px;font-weight:700;color:var(--orange);margin:10px 0 24px; }
.qb-modal .qb-modal-actions { display:flex;flex-direction:row;gap:12px; }
.qb-modal .btn-modal-confirm { flex:1;background:var(--orange);color:white;border:none;padding:16px 20px;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;transition:filter .2s;box-shadow:0 5px 10px rgba(0,0,0,.15); }
.qb-modal .btn-modal-confirm:hover { filter:brightness(.85); }
.qb-modal .btn-modal-cancel { flex:1;background:transparent;color:var(--orange);border:1.5px solid var(--orange);padding:15px 20px;border-radius:10px;font-size:15px;font-weight:500;cursor:pointer;transition:all .2s; }
.qb-modal .btn-modal-cancel:hover { opacity:.8; }
.ind-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:10000;display:none;align-items:center;justify-content:center; }
.ind-modal-overlay.open { display:flex; }
.ind-modal { background:white;border-radius:20px;padding:36px 36px 28px;width:480px;max-width:95vw;text-align:center;animation:indSlide .25s ease;box-shadow:0 20px 60px rgba(0,0,0,.18); }
@keyframes indSlide { from{opacity:0;transform:translateY(-16px)} to{opacity:1;transform:translateY(0)} }
.ind-modal h3 { font-size:22px;font-weight:700;color:#1a202c;margin:0 0 10px; }
.ind-modal p  { font-size:14px;color:#6b7280;line-height:1.6;margin:0 0 20px; }
.ind-modal select { width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;font-family:inherit;color:#1a202c;background:#fff;outline:none;cursor:pointer;margin-bottom:20px;transition:border-color .2s; }
.ind-modal select:focus { border-color:var(--orange); }
.ind-modal-actions { display:flex;gap:12px; }
.ind-btn-confirm { flex:1;background:var(--orange);color:white;border:none;padding:13px 20px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:filter .2s; }
.ind-btn-confirm:hover { filter:brightness(.85); }
.ind-btn-confirm:disabled { opacity:.6;cursor:not-allowed; }
.ind-btn-cancel { flex:1;background:transparent;color:var(--orange);border:1.5px solid var(--orange);padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s; }
.ind-btn-cancel:hover { opacity:.8; }
</style>
@endpush

@section('content')

@php
    $hasCtx = !empty(auth()->user()->business_context);
@endphp

{{-- Filter bar --}}
<div class="fi-bar">
    <div style="font-size:14px;font-weight:600;color:#1a202c;">Firm Insights</div>
    <div class="bar-right">
        <select id="fiFiStatusFilter" class="bar-select" onchange="fiFiApplyFilters()">
            <option value="">All Statuses</option>
            <option value="analyzed">Analyzed</option>
            <option value="processing">Processing</option>
            <option value="failed">Failed</option>
            <option value="deleted">Deleted</option>
        </select>
        <div class="dr-wrap" id="fi-dr">
            <div class="dr-field" onclick="drOpen('fi-dr')">
                <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="dr-text" id="fi-dr-text">Start Date To End Date</span>
                <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('fi-dr')" title="Clear date filter">&#x2715;</button>
            </div>
            <div class="dr-menu" id="fi-dr-menu">
                <ul class="dr-list">
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','today',this)">Today</li>
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','last30',this)">Last 30 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','thisMonth',this)">This Month</li>
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','lastMonth',this)">Last Month</li>
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','last90',this)">Last 90 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','last6m',this)">Last 6 Months</li>
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','last1y',this)">Last 1 Year</li>
                    <li class="dr-opt" onclick="drPickPreset('fi-dr','custom',this)">Custom Range</li>
                </ul>
                <div class="dr-custom-row" id="fi-dr-custom">
                    <input type="date" class="dr-date-inp" id="fi-dr-from">
                    <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                    <input type="date" class="dr-date-inp" id="fi-dr-to">
                    <button class="dr-apply-btn" onclick="drApplyCustom('fi-dr')">Apply</button>
                </div>
            </div>
        </div>
        <div class="bar-search-wrap">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" id="fiFiSearchInput" class="bar-search-inp" placeholder="Search insights…" oninput="fiFiApplyFilters()">
        </div>
        <button type="button" class="btn btn-danger" onclick="fiFiResetFilters()">Reset</button>
    </div>
</div>

{{-- Welcome (no reports yet) --}}
<div id="fiFiWelcome" style="{{ $reports->isEmpty() ? '' : 'display:none;' }}">
    <div class="insights-welcome-message">
        <div class="welcome-header" style="margin-bottom:30px;">
            <h2>Welcome to Firm Insights</h2>
            <p>Generate financial insights for your firm using your QuickBooks data.</p>
            @if($qbConnection)
                <p>Your <span style="color:#f06b25;">QuickBooks</span> is connected. Sync your data to generate insights.</p>
            @else
                <p>Connect <span style="color:#f06b25;">QuickBooks</span> to begin the analysis.</p>
            @endif
        </div>

        <h3 style="font-size:18px;font-weight:600;color:#1a202c;margin:24px 0 16px;">
            {{ $qbConnection ? 'Generate your first insights' : 'Access your first insights' }}
        </h3>

        <div class="dashboard-action-cards-row" style="grid-template-columns:repeat(1,minmax(0,460px));">
            <div class="dashboard-action-card">
                <div class="dashboard-action-card-icon">
                    <svg width="40" height="40" viewBox="0 0 40 40">
                        <circle cx="20" cy="20" r="20" fill="#2CA01C"/>
                        <text x="50%" y="56%" dominant-baseline="middle" text-anchor="middle"
                              fill="white" font-size="16" font-weight="700" font-family="Arial,sans-serif">QB</text>
                    </svg>
                </div>
                @if($qbConnection)
                    <h3>Sync QuickBooks Data</h3>
                    <p>Fetch your latest financial data from QuickBooks and generate insights.</p>
                    <button class="dashboard-action-card-btn primary" id="fiFiSyncQBBtn" onclick="fiFiGenerateInsight()">
                        <i class="fa fa-sync-alt"></i> Generate Insights
                    </button>
                @elseif($qbConfigured)
                    <h3>Connect QuickBooks</h3>
                    <p>Automatically sync your financial data and generate real-time insights.</p>
                    <a href="{{ route('firm-quickbooks.connect') }}" class="dashboard-action-card-btn primary" style="text-decoration:none;">
                        <i class="fa fa-link"></i> Connect QuickBooks
                    </a>
                @else
                    <h3>QuickBooks Not Configured</h3>
                    <p>QuickBooks credentials are not set up yet. Please configure QuickBooks in Settings.</p>
                    <button class="dashboard-action-card-btn primary" disabled>QuickBooks Not Configured</button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Reports table --}}
<div id="fiFiTableContainer" style="{{ $reports->isNotEmpty() ? '' : 'display:none;' }}">
    <div class="reports-table-container">
        <div class="table-header">
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <h2>All Insights</h2>
                @if($qbConnection)
                <button id="fiFiBtnGenerateNew" onclick="fiFiGenerateInsight()" class="btn-generate-insight">
                    <i class="fa fa-sync-alt"></i> Generate New Insight
                </button>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="reports-table" id="fiFiTable">
                <thead>
                    <tr>
                        <th>Insight Name</th>
                        <th>Generated</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fiFiTableBody">
                    @foreach($reports as $report)
                    @php
                        $isDeleted = $report->trashed() || strtolower($report->status) === 'deleted';
                        $rowStatus = $isDeleted ? 'deleted'
                            : (in_array(strtolower($report->status), ['uploading','analyzing']) ? 'processing'
                            : strtolower($report->status));
                    @endphp
                    <tr id="fi-row-{{ $report->id }}"
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
                                    <a href="{{ !$report->isProcessing() ? route('firm-insights.view', $report->encrypted_id) : '#' }}"
                                       style="color:#1a6aa8;text-decoration:none;font-weight:500;{{ $report->isProcessing() ? 'pointer-events:none;color:#1a202c;' : '' }}">
                                        {{ $report->report_name }}
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td style="color:#64748b;" data-date="{{ $report->created_at->format('Y-m-d') }}">{{ $report->created_at->format(active_date_format()) }}</td>
                        <td style="color:#64748b;">{{ $report->formatted_file_size }}</td>
                        <td>
                            @if($isDeleted)
                                <span class="badge danger">Deleted</span>
                            @elseif($report->isReady())
                                <span class="badge success">Analyzed</span>
                            @elseif(strtolower($report->status) === 'failed')
                                <span class="badge danger">Failed</span>
                            @elseif(in_array(strtolower($report->status), ['uploading','analyzing']))
                                <span class="badge info" id="fi-status-{{ $report->id }}">{{ $report->status_label }}</span>
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
                                        <a href="{{ route('firm-insights.view', $report->encrypted_id) }}" class="btn-view-insight">
                                            View Insight
                                        </a>
                                    @endif
                                    <button class="btn-icon download" title="Download Files"
                                            onclick="fiFiDownload({{ $report->id }})">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </button>
                                    <button class="btn-icon delete" title="Delete"
                                            onclick="fiFiDelete({{ $report->id }}, '{{ addslashes($report->report_name) }}')">
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
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Industry modal --}}
<div id="fiFiIndustryModal" class="ind-modal-overlay">
    <div class="ind-modal">
        <h3>What's your industry?</h3>
        <p>We use industry benchmarks to put your financials in context. This helps our AI provide more relevant insights tailored to your business.</p>
        <select id="fiFiIndustrySelect">
            <option value="">— Select your industry —</option>
            @foreach(['Automotive','Technology','Healthcare','Manufacturing','Finance','Retail','Construction','Transportation','Energy','Agriculture','Education','Real Estate','Entertainment','Consulting','Legal Services','Other'] as $ind)
            <option value="{{ $ind }}" {{ auth()->user()->business_context === $ind ? 'selected' : '' }}>{{ $ind }}</option>
            @endforeach
        </select>
        <div class="ind-modal-actions">
            <button class="ind-btn-confirm" id="fiFiIndBtnConfirm" onclick="fiFiSaveIndustryAndProceed()">Continue</button>
            <button class="ind-btn-cancel" onclick="fiFiCloseIndustryModal()">Cancel</button>
        </div>
    </div>
</div>

{{-- QB Sync Confirm Modal --}}
<div id="fiFiQbModal" class="qb-modal-overlay" style="display:none;" onclick="fiFiCloseQbModal(event)">
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
            <button class="btn-modal-confirm" onclick="fiFiDoGenerate()">Generate Insights</button>
            <button class="btn-modal-cancel" onclick="fiFiCloseQbModal()">Not now</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const SAVE_CTX_URL = '{{ route("firm-insights.save-business-context") }}';
    const LS_KEY       = 'firm_insights_processing_id';
    const DASH_LS_KEY  = 'dash_processing_insight_id';
    const GLOBAL_LS_KEY = 'insights_processing_ids';
    let processingSwalOpen = false;

    function clearProcessingStorage() {
        localStorage.removeItem(LS_KEY);
        localStorage.removeItem(DASH_LS_KEY);
        localStorage.removeItem(GLOBAL_LS_KEY);
    }

    // Resume polling on page load for any in-progress reports
    const serverIds = @json($reports->filter(fn($r) => $r->isProcessing())->pluck('id'));
    let lsId = localStorage.getItem(LS_KEY);
    let allIds = [...new Set([...serverIds, ...(lsId ? [parseInt(lsId)] : [])])];

    if (allIds.length > 0) {
        showBar('Analyzing your QuickBooks data… You can navigate freely while this runs.');
        allIds.forEach(id => pollStatus(id));
    }

    // ── Generate flow ────────────────────────────────────────────────────────
    window.fiFiGenerateInsight = function () {
        if (!{{ !empty(auth()->user()->business_context) ? 'true' : 'false' }}) {
            openIndustryModal();
        } else {
            showQbModal();
        }
    };

    function openIndustryModal() {
        document.getElementById('fiFiIndustryModal').classList.add('open');
    }

    window.fiFiCloseIndustryModal = function () {
        document.getElementById('fiFiIndustryModal').classList.remove('open');
    };

    window.fiFiSaveIndustryAndProceed = function () {
        const sel = document.getElementById('fiFiIndustrySelect');
        if (!sel.value) { sel.style.borderColor = '#ef4444'; return; }
        sel.style.borderColor = '';

        const btn = document.getElementById('fiFiIndBtnConfirm');
        btn.disabled = true; btn.textContent = 'Saving…';

        fetch(SAVE_CTX_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ business_context: sel.value }),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.textContent = 'Continue';
            if (data.status === 'success') {
                window.fiFiCloseIndustryModal();
                showQbModal();
            } else {
                alert(data.message || 'Could not save industry.');
            }
        })
        .catch(() => { btn.disabled = false; btn.textContent = 'Continue'; alert('Network error.'); });
    };

    function showQbModal() {
        document.getElementById('fiFiQbModal').style.display = 'flex';
    }

    window.fiFiCloseQbModal = function (e) {
        if (e && e.target !== document.getElementById('fiFiQbModal')) return;
        document.getElementById('fiFiQbModal').style.display = 'none';
    };

    window.fiFiDoGenerate = function () {
        document.getElementById('fiFiQbModal').style.display = 'none';

        ['fiFiSyncQBBtn','fiFiBtnGenerateNew'].forEach(id => {
            const b = document.getElementById(id);
            if (b) { b.disabled = true; b.style.opacity = '0.7'; }
        });

        showBar('Starting insight generation… You can navigate to other pages freely.');

        fetch('{{ route("firm-insights.generate") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'processing') {
                localStorage.setItem(LS_KEY, data.report_id);
                showBar('Insight generation is running in the background. Feel free to navigate.');
                showTable();
                prependRow(data.report_id, data.report_name);
                pollStatus(data.report_id);
            } else {
                hideBar(); resetBtns();
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(() => { hideBar(); resetBtns(); Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' }); });
    };

    // ── Poll status ──────────────────────────────────────────────────────────
    function pollStatus(id) {
        fetch(`/firm-insights/status/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') { setTimeout(() => pollStatus(id), 10000); return; }
            const r = data.report;
            if (r.has_insights) {
                clearProcessingStorage();
                hideBar(); resetBtns();
                markReady(r.id, r.encrypted_id);
                return;
            }
            if (r.is_failed) {
                clearProcessingStorage();
                hideBar(); resetBtns();
                markFailed(r.id);
                Swal.fire({ icon: 'error', title: 'Generation Failed', text: 'Insight generation failed. Please try again.' });
                return;
            }
            setTimeout(() => pollStatus(id), 8000);
        })
        .catch(() => setTimeout(() => pollStatus(id), 15000));
    }

    // ── DOM helpers ──────────────────────────────────────────────────────────
    function showTable() {
        const w = document.getElementById('fiFiWelcome');
        const t = document.getElementById('fiFiTableContainer');
        if (w) w.style.display = 'none';
        if (t) t.style.display = '';
    }

    function prependRow(id, name) {
        const tbody = document.getElementById('fiFiTableBody');
        if (!tbody) { return; }
        const label = name || 'Generating…';
        const tr = document.createElement('tr');
        tr.id = 'fi-row-' + id;
        tr.dataset.search = label.toLowerCase();
        tr.innerHTML = `
            <td><div class="insight-name-cell">
                <span class="insight-name-icon">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span style="color:#1a202c;font-weight:500;">${label}</span>
            </div></td>
            <td style="color:#64748b;" data-date="">Just now</td>
            <td style="color:#64748b;">—</td>
            <td><span class="badge info" id="fi-status-${id}">Analyzing</span></td>
            <td><div class="action-buttons">
                <button class="btn-view-insight" disabled style="background:#9ca3af;">Processing…</button>
                <button class="btn-icon download" disabled style="opacity:.4;cursor:not-allowed;" title="Download">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>
                <button class="btn-icon delete" onclick="fiFiDelete(${id},'This insight')" title="Delete">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div></td>`;
        tbody.prepend(tr);
    }

    function markReady(id, encId) {
        const row   = document.getElementById('fi-row-' + id);
        const badge = document.getElementById('fi-status-' + id);
        if (!row) { return; }
        if (badge) { badge.className = 'badge success'; badge.textContent = 'Analyzed'; }
        const viewUrl = encId ? `/firm-insights/${encId}/view` : `/firm-insights/${id}/view`;
        const actions = row.querySelector('.action-buttons');
        if (actions) {
            actions.innerHTML = `
                <a href="${viewUrl}" class="btn-view-insight">View Insight</a>
                <button class="btn-icon download" onclick="fiFiDownload(${id})" title="Download">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>
                <button class="btn-icon delete" onclick="fiFiDelete(${id},'This insight')" title="Delete">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>`;
        }
        const nameEl = row.querySelector('.insight-name-cell span[style*="color:#1a202c"]');
        if (nameEl) {
            const a = document.createElement('a');
            a.href = viewUrl;
            a.style.cssText = 'color:#1a6aa8;text-decoration:none;font-weight:500;';
            a.textContent = nameEl.textContent;
            nameEl.replaceWith(a);
        }
    }

    function markFailed(id) {
        const badge = document.getElementById('fi-status-' + id);
        if (badge) { badge.className = 'badge danger'; badge.textContent = 'Failed'; }
        const row = document.getElementById('fi-row-' + id);
        if (row) {
            const vb = row.querySelector('.btn-view-insight');
            if (vb) { vb.style.background = '#9ca3af'; vb.textContent = 'Failed'; }
        }
    }

    function showBar(msg) {
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

    function hideBar() {
        if (processingSwalOpen && Swal.isVisible()) {
            Swal.close();
        }
        processingSwalOpen = false;
    }

    function resetBtns() {
        ['fiFiSyncQBBtn','fiFiBtnGenerateNew'].forEach(id => {
            const b = document.getElementById(id);
            if (b) { b.disabled = false; b.style.opacity = ''; b.innerHTML = '<i class="fa fa-sync-alt"></i> Generate New Insight'; }
        });
    }

    // ── Filters ──────────────────────────────────────────────────────────────
    window.fiFiApplyFilters = function () {
        const q    = (document.getElementById('fiFiSearchInput')?.value ?? '').toLowerCase();
        const st   = (document.getElementById('fiFiStatusFilter')?.value ?? '').toLowerCase();
        const from = document.getElementById('fi-dr-from') ? document.getElementById('fi-dr-from').value : '';
        const to   = document.getElementById('fi-dr-to')   ? document.getElementById('fi-dr-to').value   : '';
        document.querySelectorAll('#fiFiTableBody tr').forEach(row => {
            const ms = !q  || (row.dataset.search || '').includes(q);
            const mv = !st || (row.dataset.status || '') === st;
            let md = true;
            if (from || to) {
                const dateVal = row.querySelector('td[data-date]')?.getAttribute('data-date') || '';
                if (dateVal) {
                    if (from && dateVal < from) md = false;
                    if (to   && dateVal > to)   md = false;
                } else {
                    md = false;
                }
            }
            row.style.display = (ms && mv && md) ? '' : 'none';
        });
    };

    _drCbs['fi-dr'] = function () { fiFiApplyFilters(); };

    window.fiFiResetFilters = function () {
        const si = document.getElementById('fiFiSearchInput');
        const sf = document.getElementById('fiFiStatusFilter');
        if (si) si.value = '';
        if (sf) sf.value = '';
        drClear('fi-dr');
        fiFiApplyFilters();
    };

    // ── Download ─────────────────────────────────────────────────────────────
    window.fiFiDownload = function (id) {
        Swal.fire({ title:'Preparing Download', html:'<p>Creating ZIP file…</p>', icon:'info', allowOutsideClick:false, showConfirmButton:false, willOpen:()=>Swal.showLoading() });
        window.location.href = `/firm-insights/${id}/download`;
        setTimeout(() => Swal.close(), 2500);
    };

    // ── Delete ────────────────────────────────────────────────────────────────
    window.fiFiDelete = function (id, name) {
        Swal.fire({
            title: 'Are you sure?',
            html:  `<p>Deleting "<strong>${name}</strong>" will permanently remove this insight. This action cannot be undone.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`/firm-insights/${id}`, {
                method:  'DELETE',
                headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':CSRF, 'Content-Type':'application/json', 'Accept':'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const row = document.getElementById('fi-row-' + id);
                    if (row) {
                        row.style.opacity = '0.7';
                        row.dataset.status = 'deleted';
                        const nameEl = row.querySelector('.insight-name-cell a, .insight-name-cell span[style]');
                        if (nameEl) {
                            const s = document.createElement('span');
                            s.style.cssText = 'color:#9ca3af;font-weight:500;';
                            s.textContent = nameEl.textContent.trim();
                            nameEl.replaceWith(s);
                        }
                        const st = row.cells[3];
                        if (st) st.innerHTML = '<span class="badge danger">Deleted</span>';
                        const ac = row.cells[4];
                        if (ac) ac.innerHTML = '<span style="font-size:13px;color:#9ca3af;font-style:italic;">No actions available</span>';
                    }
                    Swal.fire({ icon:'success', title:'Deleted', text:'Insight deleted.', timer:2000, showConfirmButton:false });
                } else {
                    Swal.fire({ icon:'error', title:'Error', text:data.message || 'Could not delete.' });
                }
            })
            .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Request failed.' }));
        });
    };
}());
</script>
@endpush
