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
/* ── Action Card ─────────────────────────────────────────────── */
.dashboard-action-cards-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.dashboard-action-card {
    background: white;
    border: 1px solid #dbdbdb;
    border-radius: 5px;
    padding: 24px;
    transition: all 0.3s ease;
}
.dashboard-action-card:hover {
    border-color: #f06b25;
    box-shadow: 0 4px 12px rgba(21,89,166,0.1);
}
.dashboard-action-card-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}
.dashboard-action-card h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
    margin: 0 0 8px 0;
}
.dashboard-action-card p {
    color: #000;
    font-size: 15px;
    line-height: 1.5;
    margin: 0 0 20px 0;
}
.dashboard-action-card-btn {
    width: 100%;
    padding: 16px 24px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.dashboard-action-card-btn.primary { background: #f06b25; color: white; }
.dashboard-action-card-btn.primary:hover { background: #d95a1a; color: white; }
.dashboard-action-card-btn:disabled { opacity: 0.7; cursor: not-allowed; }
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
.search-box { position: relative; }
.search-box input {
    padding: 8px 12px 8px 36px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    width: 220px;
    outline: none;
}
.search-box input:focus { border-color: #1a6aa8; }
.search-box i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; }
.reports-table { width: 100%; border-collapse: collapse; }
.reports-table th {
    text-align: left;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.reports-table td {
    padding: 14px 14px;
    font-size: 14px;
    color: #1a202c;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.reports-table tr:last-child td { border-bottom: none; }
.reports-table tr:hover td { background: #f8fafc; }
.insight-name-cell { display: flex; align-items: center; gap: 10px; }
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
.qb-modal .qb-modal-timing { font-size: 15px; font-weight: 700; color: #f06b25; margin: 10px 0 24px; }
.qb-modal .qb-modal-actions { display: flex; flex-direction: row; gap: 12px; }
.qb-modal .btn-modal-confirm {
    flex: 1; background: #f06b25; color: white; border: none;
    padding: 16px 20px; border-radius: 10px; font-size: 15px; font-weight: 600;
    cursor: pointer; transition: background .2s;
    box-shadow: 0 5px 10px rgba(240,107,37,.3);
}
.qb-modal .btn-modal-confirm:hover { background: #d95a1a; }
.qb-modal .btn-modal-cancel {
    flex: 1; background: transparent; color: #f06b25;
    border: 1.5px solid #f06b25; padding: 15px 20px; border-radius: 10px;
    font-size: 15px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.qb-modal .btn-modal-cancel:hover { background: #fff4ee; }
/* ── Processing bar ─────────────────────────────────────────── */
.thinking-bar {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px;
    background: linear-gradient(135deg,#f0f7ff 0%,#f5f0ff 100%);
    border: 1px solid #dde8ff; border-radius: 10px;
    font-size: .875rem; color: #3b5bdb; font-weight: 500;
    margin-bottom: 1.25rem;
}
.thinking-dots span {
    display: inline-block; width: 7px; height: 7px;
    border-radius: 50%; background: #3b5bdb; margin: 0 2px;
    animation: bounce 1.2s infinite ease-in-out;
}
.thinking-dots span:nth-child(2) { animation-delay: .2s; }
.thinking-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes bounce {
    0%,80%,100% { transform: scale(.6); opacity: .5; }
    40%         { transform: scale(1); opacity: 1; }
}
</style>
@endpush

@section('content')

{{-- ── Processing bar ──────────────────────────────────────────────────── --}}
<div id="processingBar" style="display:none;">
    <div class="thinking-bar">
        <div class="thinking-dots"><span></span><span></span><span></span></div>
        <span id="processingMsg">Syncing your QuickBooks data and generating AI insights… This may take 2–5 minutes.</span>
    </div>
</div>

{{-- ── Welcome (no reports yet) ────────────────────────────────────────── --}}
<div id="insightsWelcomeMessage" style="{{ $reports->isEmpty() ? '' : 'display:none;' }}">
    <div class="insights-welcome-message">
        <div class="welcome-header" style="margin-bottom:30px;">
            <h2>Welcome to Insights</h2>
            <p>You're one step away from accessing your first <strong>CFO-level financial insights.</strong></p>
            @if($qbConnection)
                <p>Your <span style="color:#f06b25;">QuickBooks</span> is connected. Sync your data to generate insights. Your insights will appear here once the analysis is complete.</p>
            @else
                <p>Connect <span style="color:#f06b25;">QuickBooks</span> to begin the analysis. Your insights will appear here once the analysis is complete.</p>
            @endif
        </div>

        <h3 style="font-size:18px;font-weight:600;color:#1a202c;margin:24px 0 16px 0;">
            {{ $qbConnection ? 'Generate your first insights' : 'Access your first insights' }}
        </h3>

        <div class="dashboard-action-cards-row" style="grid-template-columns:repeat(1,minmax(0,460px));">
            <div class="dashboard-action-card">
                <div class="dashboard-action-card-icon">
                    <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="20" fill="#2CA01C"/>
                        <text x="50%" y="56%" dominant-baseline="middle" text-anchor="middle"
                              fill="white" font-size="16" font-weight="700" font-family="Arial,sans-serif">QB</text>
                    </svg>
                </div>
                @if($qbConnection)
                    <h3>Sync QuickBooks Data</h3>
                    <p>Fetch your latest financial data from QuickBooks and generate CFO-level insights.</p>
                    <button class="dashboard-action-card-btn primary" id="insightsSyncQBBtn" onclick="generateInsight()">
                        <i class="fa fa-sync-alt"></i> Generate Insights
                    </button>
                @elseif($qbConfigured)
                    <h3>Connect QuickBooks</h3>
                    <p>Automatically sync your financial data and generate real-time insights.</p>
                    <a href="{{ route('quickbooks.user-connect') }}" class="dashboard-action-card-btn primary" style="text-decoration:none;">
                        <i class="fa fa-link"></i> Connect QuickBooks
                    </a>
                @else
                    <h3>QuickBooks Not Configured</h3>
                    <p>QuickBooks credentials are not set up yet. Please contact your administrator to configure QuickBooks in Settings.</p>
                    <button class="dashboard-action-card-btn primary" disabled>QuickBooks Not Configured</button>
                @endif
            </div>
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
            <div style="display:flex;align-items:center;gap:16px;">
                <h2>All Insights</h2>
                @if($qbConnection)
                <button id="btnGenerateNew" onclick="generateInsight()"
                        style="background:#f06b25;color:white;padding:8px 18px;border:none;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .2s;"
                        onmouseover="this.style.background='#d95a1a'" onmouseout="this.style.background='#f06b25'">
                    <i class="fa fa-sync-alt"></i> Generate New Insight
                </button>
                @endif
            </div>
            <div class="search-box">
                <i class="fa fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search insights" onkeyup="searchReports()">
            </div>
        </div>

        <div class="table-responsive">
            <table class="reports-table" id="reportsTable">
                <thead>
                    <tr>
                        <th>Insight Name</th>
                        <th>Upload Date</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    @foreach($reports as $report)
                    <tr id="insight-row-{{ $report->id }}" data-search="{{ strtolower($report->report_name) }}">
                        <td>
                            <div class="insight-name-cell">
                                <span class="insight-name-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                <a href="{{ !$report->isProcessing() ? route('insights.view', $report->encrypted_id) : '#' }}"
                                   style="color:#1a6aa8;text-decoration:none;font-weight:500;{{ $report->isProcessing() ? 'pointer-events:none;color:#1a202c;' : '' }}">
                                    {{ $report->report_name }}
                                </a>
                            </div>
                        </td>
                        <td style="color:#64748b;">{{ $report->created_at->format('m/d/Y') }}</td>
                        <td style="color:#64748b;">{{ $report->formatted_file_size }}</td>
                        <td>
                            @if($report->isReady())
                                <span class="badge success">Analyzed</span>
                            @elseif(strtolower($report->status) === 'failed')
                                <span class="badge danger">Failed</span>
                            @elseif(in_array(strtolower($report->status), ['uploading','analyzing']))
                                <span class="badge info" id="status-badge-{{ $report->id }}">{{ $report->status_label }}</span>
                            @else
                                <span class="badge warning">{{ $report->status_label }}</span>
                            @endif
                        </td>
                        <td>
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
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── QB Sync Confirm Modal ────────────────────────────────────────────── --}}
<div id="qbGenerateModal" class="qb-modal-overlay" style="display:none;" onclick="closeGenerateModal(event)">
    <div class="qb-modal">
        <div class="qb-modal-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 70 70" width="70" height="70">
                <circle cx="35" cy="35" r="35" fill="#2CA01C"/>
                <!-- q: circle bowl + diagonal tail -->
                <circle cx="25" cy="36" r="11" fill="none" stroke="white" stroke-width="5.5"/>
                <line x1="32.5" y1="43.5" x2="38" y2="52.5" stroke="white" stroke-width="5.5" stroke-linecap="round"/>
                <!-- b: vertical stem + circle bowl -->
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
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Auto-poll in-progress reports
    const processingIds = @json($reports->filter(fn($r) => $r->isProcessing())->pluck('id'));
    if (processingIds.length > 0) {
        showProcessingBar('Analyzing your QuickBooks data…');
        processingIds.forEach(id => pollStatus(id));
    }

    // ── Generate (show confirmation modal first) ─────────────────────────
    window.generateInsight = function () {
        const modal = document.getElementById('qbGenerateModal');
        if (modal) modal.style.display = 'flex';
    };

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

        showProcessingBar('Syncing QuickBooks data and generating AI insights… This may take 2–5 minutes.');

        fetch('{{ route("insights.generate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                showTableView();
                prependPlaceholderRow(data.report_id);
                pollStatus(data.report_id);
            } else {
                hideProcessingBar();
                resetGenerateBtns();
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(() => {
            hideProcessingBar();
            resetGenerateBtns();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
        });
    };

    // ── Poll status ───────────────────────────────────────────────────────
    function pollStatus(reportId) {
        fetch(`/insights/status/${reportId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') return;
            const r = data.report;
            if (r.has_insights) {
                hideProcessingBar();
                resetGenerateBtns();
                markRowReady(r.id, r.encrypted_id);
                return;
            }
            if (r.is_failed) {
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

    // ── DOM helpers ───────────────────────────────────────────────────────
    function showTableView() {
        const welcome = document.getElementById('insightsWelcomeMessage');
        const table   = document.getElementById('reportsTableContainer');
        if (welcome) welcome.style.display = 'none';
        if (table)   table.style.display = '';
    }

    function prependPlaceholderRow(id) {
        const tbody = document.getElementById('reportsTableBody');
        if (!tbody) { location.reload(); return; }
        const tr = document.createElement('tr');
        tr.id = 'insight-row-' + id;
        tr.dataset.search = 'quickbooks insight';
        tr.innerHTML = `
            <td>
                <div class="insight-name-cell">
                    <span class="insight-name-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <span style="color:#1a202c;font-weight:500;">QuickBooks Insight — generating…</span>
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
        const row = document.getElementById('insight-row-' + id);
        const badge = document.getElementById('status-badge-' + id);
        if (!row) { location.reload(); return; }
        if (badge) { badge.className = 'badge success'; badge.textContent = 'Analyzed'; }
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
        // Update the name link
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
        const bar = document.getElementById('processingBar');
        const msgEl = document.getElementById('processingMsg');
        if (bar) bar.style.display = '';
        if (msgEl && msg) msgEl.textContent = msg;
    }

    function hideProcessingBar() {
        const bar = document.getElementById('processingBar');
        if (bar) bar.style.display = 'none';
    }

    function resetGenerateBtns() {
        const btn1 = document.getElementById('insightsSyncQBBtn');
        const btn2 = document.getElementById('btnGenerateNew');
        if (btn1) { btn1.disabled = false; btn1.style.opacity = ''; btn1.innerHTML = '<i class="fa fa-sync-alt"></i> Generate Insights'; }
        if (btn2) { btn2.disabled = false; btn2.style.opacity = ''; btn2.innerHTML = '<i class="fa fa-sync-alt"></i> Generate New Insight'; }
    }

    // ── Search ────────────────────────────────────────────────────────────
    window.searchReports = function () {
        const q = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#reportsTableBody tr').forEach(row => {
            row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
        });
    };

    // ── Download ──────────────────────────────────────────────────────────
    window.downloadInsight = function (id) {
        Swal.fire({
            title: 'Preparing Download',
            html: '<p>Creating ZIP file with all attachments...</p>',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => Swal.showLoading()
        });
        window.location.href = `/insights/${id}/download`;
        setTimeout(() => Swal.close(), 2500);
    };

    // ── Delete ────────────────────────────────────────────────────────────
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
                    var row = document.getElementById('insight-row-' + id);
                    if (row) row.remove();
                    Swal.fire({ icon: 'success', title: 'Deleted', text: 'Insight deleted successfully.', timer: 2000, showConfirmButton: false });
                    if (document.querySelectorAll('#reportsTableBody tr').length === 0) {
                        document.getElementById('reportsTableContainer').style.display = 'none';
                        document.getElementById('insightsWelcomeMessage').style.display = '';
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
