@extends('layouts.app')

@section('page-title', 'Insight Report')

@push('styles')
<style>
.report-detail-container { background-color: #f5f7fa; min-height: 100vh; }

/* ── Header ── */
.report-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.report-detail-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 6px 0;
}
.report-meta { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 10px; }
.report-meta-item { display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 14px; }
.report-meta-item i { color: #94a3b8; font-size: 15px; }
.report-meta-item.report-meta-files i { color: #eab308; }

/* ── Header actions ── */
.report-header-actions { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }

/* ── Edit / Save btns ── */
.rd-btn { height:40px;padding:0 18px;border-radius:7px;font-size:13.5px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:none;transition:all .2s;white-space:nowrap; }
.rd-btn-edit { background:#fff;color:#1a6aa8;border:1.5px solid #1a6aa8; }
.rd-btn-edit:hover { background:#f0f7ff;text-decoration:none; }
.rd-btn-edit.active { background:#1a6aa8;color:#fff; }
.rd-btn-save { background:var(--orange);color:#fff;border:1.5px solid var(--orange); }
.rd-btn-save:hover { filter:brightness(.85);text-decoration:none; }
.rd-btn-outline { background:#fff;color:var(--blue,#1a6aa8);border:1.5px solid var(--blue,#1a6aa8); }
.rd-btn-outline:hover { background:#eff6ff;text-decoration:none; }
.rd-btn:disabled { opacity:.6;cursor:not-allowed; }

/* ── Edit mode content area ── */
.edit-mode-active .insight-slider-content[contenteditable] {
    border:2px solid #f59e0b;border-radius:8px;padding:14px 16px;
    outline:none;cursor:text;min-height:180px;
    transition:border-color .15s,box-shadow .15s;
}
.edit-mode-active .insight-slider-content[contenteditable]:focus {
    border-color:#d97706;box-shadow:0 0 0 3px rgba(245,158,11,.14);
}

/* ── Rich-text toolbar ── */
#ciEditToolbar {
    display:none;background:#fff;border:1px solid #e2e8f0;border-radius:9px;
    padding:7px 12px;margin-bottom:10px;box-shadow:0 2px 6px rgba(0,0,0,.07);
    align-items:center;gap:3px;flex-wrap:wrap;
}
#ciEditToolbar.visible { display:flex; }
.ci-tb-sep { width:1px;height:22px;background:#e2e8f0;margin:0 5px;flex-shrink:0; }
.ci-tb-btn {
    width:30px;height:30px;border:none;border-radius:5px;background:transparent;
    display:flex;align-items:center;justify-content:center;cursor:pointer;
    transition:background .13s,color .13s;color:#374151;flex-shrink:0;
}
.ci-tb-btn:hover { background:#f1f5f9; }
.ci-tb-btn.active { background:#dbeafe;color:#1d4ed8; }
.ci-tb-btn svg { pointer-events:none; }
.ci-tb-select {
    height:30px;border:1px solid #e2e8f0;border-radius:5px;padding:0 22px 0 8px;
    font-size:12.5px;font-family:inherit;color:#374151;background:#fff
    url("data:image/svg+xml,%3Csvg width='8' height='5' viewBox='0 0 8 5'%3E%3Cpath d='M1 1l3 3 3-3' stroke='%2394a3b8' stroke-width='1.3' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 6px center;
    -webkit-appearance:none;appearance:none;outline:none;cursor:pointer;min-width:96px;
}
.ci-tb-select:focus { border-color:#0788be; }

/* ── Export PDF btn ── */
.export-pdf-btn {
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: var(--orange);
    border-radius: 5px;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    box-shadow: 0 5px 10px 0 rgba(0,0,0,.15);
    transition: all .3s ease;
}
.export-pdf-btn:hover { filter: brightness(.85); color: white; text-decoration: none; transform: translateY(-1px); }

/* ── Back btn ── */
.btn-back {
    background: #fff;
    color: #0788BE;
    padding: 12px 20px;
    border-radius: 5px;
    border: 1px solid #0788be;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    font-weight: 500;
    transition: all .2s;
    box-shadow: 0 5px 10px 0 rgba(16,134,190,.2);
}
.btn-back:hover { background: #f0f9ff; color: #1559a6; text-decoration: none; }

/* ── Slider ── */
.insight-slider-container { position: relative; background: #f5f7fa; border-radius: 12px; min-height: 500px; margin-top: 0; }
.insight-slider-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    margin-top: 4px;
    padding: 0 4px;
}
.insight-slider-counter { color: #64748b; font-size: 15px; font-weight: 500; }
.insight-slider-nav { display: flex; align-items: center; gap: 20px; }
.insight-slider-nav-section-btn-container {
    display: flex; align-items: center; gap: 10px;
    border: 1px solid #DBDBDB; border-radius: 30px; padding: 5px;
    cursor: pointer; transition: all .3s ease;
    background: none; font-family: inherit; outline: none;
}
.insight-slider-nav-section-btn-container:hover { border-color: var(--orange); }
.insight-slider-nav-btn {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--orange);
    color: white; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0; pointer-events: none;
}
.insight-slider-nav-section-btn { font-size: 14px; color: #474747; padding: 0 10px 0 0; font-weight: 400; }
.insight-slider-nav-section-btn.last-child-btn { padding: 0 0 0 10px; }
.insight-slider-nav-section-btn:disabled { color: #94a3b8; cursor: not-allowed; }
.insight-slider-slides { position: relative; overflow: hidden; min-height: 400px; }
.insight-slider-slide { display: none; animation: fadeInSlide .4s ease; }
.insight-slider-slide.active { display: block; }
@keyframes fadeInSlide {
    from { opacity: 0; transform: translateX(20px); }
    to   { opacity: 1; transform: translateX(0); }
}
.insight-slider-card {
    background: white; border-radius: 12px; padding: 28px 32px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.insight-slider-title { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
.insight-slider-title-icon {
    width: 44px; height: 44px; background: var(--orange); border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; flex-shrink: 0;
}
.insight-slider-title h2 { margin: 0; font-size: 24px; font-weight: 600; color: #1a202c; }
.insight-slider-content { color: #2d3748; font-size: 15px; line-height: 1.8; }
.insight-slider-content ul, .insight-slider-content ol { padding-left: 20px; margin: 0; }
.insight-slider-content li { margin: 8px 0; list-style-type: circle; }
.insight-slider-content strong { color: #1559a6; font-weight: 600; }
.insight-slider-content p { margin: 0 0 10px 0; }
.insight-slider-content h3,
.insight-slider-content h4 { color: #1559a6; margin-top: 10px; margin-bottom: 0; font-weight: 600; }
.insight-slider-content table.insight-table {
    width: 100%; border-collapse: collapse; margin: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,.1); border-radius: 8px; overflow: hidden;
}
.insight-slider-content table.insight-table th {
    border: 1px solid #e2e8f0; padding: 12px; text-align: left;
    font-weight: 600; color: #2d3748; background: #f7fafc;
}
.insight-slider-content table.insight-table td {
    border: 1px solid #e2e8f0; padding: 12px; color: #4a5568;
}
.insight-slider-content table.insight-table tbody tr:nth-child(even) { background: #f9fafb; }

/* ── Pagination dots ── */
.insight-slider-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 24px; }
.insight-slider-dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: #cbd5e0; cursor: pointer; transition: all .3s ease;
}
.insight-slider-dot.active { background: #0788be; width: 24px; border-radius: 5px; }
.insight-slider-dot:hover { background: #94a3b8; }

/* ── Print/PDF ── */
@media print {
    .btn-back, .export-pdf-btn, .insight-slider-nav, .insight-slider-pagination { display: none !important; }
    .insight-slider-slide { display: block !important; page-break-after: always; }
    .insight-slider-card { box-shadow: none; border: 1px solid #e2e8f0; }
}
</style>
@endpush

@section('content')
<div class="report-detail-container">

    <a href="{{ route('insights.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Go Back
    </a>

    {{-- ── Report Header ──────────────────────────────────────────────────── --}}
    <div class="report-detail-header">
        <div>
            <h1>{{ $report->report_name }}</h1>
            <div class="report-meta">
                <div class="report-meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ $report->created_at->format(active_date_format()) }}</span>
                </div>
                <div class="report-meta-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>{{ $report->formatted_file_size }}</span>
                </div>
                <div class="report-meta-item report-meta-files">
                    <i class="fas fa-folder"></i>
                    <span>{{ $report->attachments->count() }} file(s)</span>
                </div>
                <div class="report-meta-item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#94a3b8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5l5 5v13a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                    <span id="ciVersionBadge" style="font-size:13px;color:#64748b;font-weight:500;">v{{ $report->version ?? 1 }}</span>
                </div>
            </div>
        </div>
        @if($report->insight_json_data)
        <div class="report-header-actions">
            <a href="{{ route('insights.preview', $report->id) }}" class="rd-btn rd-btn-outline" id="ciBtnPdfPreview">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Preview</span>
            </a>
            <button id="ciBtnEdit" class="rd-btn rd-btn-edit" onclick="ciToggleEdit()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span id="ciBtnEditLabel">Edit Insight</span>
            </button>
            <button id="ciBtnSave" class="rd-btn rd-btn-save" onclick="ciSaveEdits()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span id="ciBtnSaveLabel">Save</span>
            </button>
            <a href="{{ route('insights.export-pdf', $report->id) }}"
               class="export-pdf-btn"
           id="exportPdfBtn"
           onclick="
               var btn = this;
               btn.querySelector('span').textContent = 'Generating PDF';
               btn.querySelector('i').className = 'fas fa-spinner fa-spin';
               btn.style.pointerEvents = 'none';
               btn.style.opacity = '0.85';
               setTimeout(function(){
                   btn.querySelector('span').textContent = 'Export to PDF';
                   btn.querySelector('i').className = 'fas fa-file-pdf';
                   btn.style.pointerEvents = '';
                   btn.style.opacity = '';
               }, 8000);
           ">
            <span>Export to PDF</span>
            <i class="fas fa-file-pdf"></i>
        </a>
        </div>
        @endif
    </div>

    {{-- Rich-text toolbar (shown in edit mode) --}}
    <div id="ciEditToolbar">
        <select class="ci-tb-select" id="ciTbStyleSel" title="Text style" onchange="ciFormatBlock(this.value)">
            <option value="p">Normal</option>
            <option value="h2">Heading 2</option>
            <option value="h3">Heading 3</option>
            <option value="h4">Sub-heading</option>
        </select>
        <div class="ci-tb-sep"></div>
        <button class="ci-tb-btn" id="ciTbBold"      onclick="ciExecCmd('bold')"           title="Bold">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8"><path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
        </button>
        <button class="ci-tb-btn" id="ciTbItalic"    onclick="ciExecCmd('italic')"         title="Italic">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
        </button>
        <button class="ci-tb-btn" id="ciTbUnderline" onclick="ciExecCmd('underline')"      title="Underline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3"/><line x1="4" y1="21" x2="20" y2="21"/></svg>
        </button>
        <div class="ci-tb-sep"></div>
        <button class="ci-tb-btn" onclick="ciInsertLink()" title="Insert link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
        </button>
        <div class="ci-tb-sep"></div>
        <button class="ci-tb-btn" onclick="ciExecCmd('insertUnorderedList')" title="Bullet list">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/></svg>
        </button>
        <button class="ci-tb-btn" onclick="ciExecCmd('insertOrderedList')" title="Numbered list">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4" stroke="currentColor" stroke-width="1.8" fill="none"/><path d="M4 10h2" stroke="currentColor" stroke-width="1.8"/><path d="M6 14H4c0-1 2-2 2-3s-1-1.5-2-1" stroke="currentColor" stroke-width="1.8" fill="none"/></svg>
        </button>
        <div class="ci-tb-sep"></div>
        <button class="ci-tb-btn" id="ciTbAlignL" onclick="ciExecCmd('justifyLeft')"   title="Align left">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>
        </button>
        <button class="ci-tb-btn" id="ciTbAlignC" onclick="ciExecCmd('justifyCenter')" title="Align center">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
        </button>
        <button class="ci-tb-btn" id="ciTbAlignR" onclick="ciExecCmd('justifyRight')"  title="Align right">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="ci-tb-sep"></div>
        <button class="ci-tb-btn" onclick="ciExecCmd('removeFormat')" title="Clear formatting">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/><line x1="3" y1="21" x2="21" y2="3" stroke="currentColor" stroke-width="1.8" opacity=".5"/></svg>
        </button>
    </div>

    {{-- ── Slider Content ──────────────────────────────────────────────────── --}}
    @if($report->insight_json_data)
        <div id="insightAccordionContainer">
            <div id="insightLoaderShow" style="display:none;"></div>
            <div id="insightAccordionContentShow" style="display:none;"></div>
        </div>
    @else
        <div style="text-align:center;padding:60px;background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
            <p style="color:#718096;font-size:18px;">
                @if(strtolower($report->status) === 'failed')
                    Failed to generate insights for this report.
                @else
                    No insights available yet. Please check back later.
                @endif
            </p>
        </div>
    @endif

</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@push('scripts')
<script>
(function(){
    // Only fetch if the loader element exists (means insight_json_data is set)
    if (!document.getElementById('insightLoaderShow')) return;

    // Fetch insight data via AJAX
    if (window.showAppLoading) window.showAppLoading('Loading insights', 'Preparing your report insights...');
    fetch('/insights/{{ $report->id }}/data', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(response => {
        if (window.hideAppLoading) window.hideAppLoading();
        if (response.status === 'success' && response.data) {
            displayInsightSlider(response.data);
        } else {
            document.getElementById('insightLoaderShow').style.display = 'none';
            document.getElementById('insightAccordionContentShow').innerHTML =
                '<div style="padding:40px;text-align:center;color:#718096;font-size:16px;">No insight data available.</div>';
            document.getElementById('insightAccordionContentShow').style.display = '';
        }
    })
    .catch(() => {
        if (window.hideAppLoading) window.hideAppLoading();
        document.getElementById('insightLoaderShow').style.display = 'none';
        document.getElementById('insightAccordionContentShow').innerHTML =
            '<div style="padding:40px;text-align:center;color:#e53e3e;font-size:16px;">Failed to load insights. Please refresh the page.</div>';
        document.getElementById('insightAccordionContentShow').style.display = '';
    });

    // ── Display Slider ──────────────────────────────────────────────────
    function displayInsightSlider(data) {
        document.getElementById('insightLoaderShow').style.display = 'none';

        // Use pre-saved HTML sections if available (from inline edit saves)
        if (data && Array.isArray(data.sections) && data.sections.length > 0) {
            const sections = data.sections.map(s => ({ title: s.title || '', content: s.html || s.content || '' }));
            buildSliderHtml(sections);
            return;
        }

        let reportText = '';
        if (typeof data === 'string') {
            reportText = data;
        } else if (data && data.report && typeof data.report === 'string') {
            reportText = data.report;
        } else if (data && data.insight_json_data && data.insight_json_data.report) {
            reportText = data.insight_json_data.report;
        } else if (data && typeof data === 'object') {
            let longest = '';
            for (let key in data) {
                if (typeof data[key] === 'string' && data[key].length > longest.length) {
                    longest = data[key];
                }
            }
            reportText = longest;
        }

        if (!reportText) {
            document.getElementById('insightAccordionContentShow').innerHTML =
                '<div style="padding:40px;text-align:center;color:#718096;font-size:16px;background:white;border-radius:12px;">No report content found. The insight may still be processing.</div>';
            document.getElementById('insightAccordionContentShow').style.display = '';
            return;
        }

        const sections = parseInsightSections(reportText);
        buildSliderHtml(sections);
    }

    function buildSliderHtml(sections) {

        let html = '<div class="insight-slider-container">';
        html += '<div class="insight-slider-wrapper">';
        html += '<div class="insight-slider-header">';
        html += '<div class="insight-slider-counter"><span id="slider-current">1</span> of <span id="slider-total">' + sections.length + '</span></div>';
        html += '<div class="insight-slider-nav">';
        html += '<button type="button" class="insight-slider-nav-section-btn-container" id="slider-prev-container" onclick="changeSlide(-1)" aria-label="Previous">';
        html += '<span class="insight-slider-nav-btn" id="slider-prev"><i class="fas fa-chevron-left"></i></span>';
        html += '<p class="insight-slider-nav-section-btn" id="slider-prev-label">—</p>';
        html += '</button>';
        html += '<button type="button" class="insight-slider-nav-section-btn-container" id="slider-next-container" onclick="changeSlide(1)" aria-label="Next">';
        html += '<p class="insight-slider-nav-section-btn last-child-btn" id="slider-next-label">—</p>';
        html += '<span class="insight-slider-nav-btn" id="slider-next"><i class="fas fa-chevron-right"></i></span>';
        html += '</button>';
        html += '</div></div>';

        html += '<div class="insight-slider-slides">';
        sections.forEach((section, index) => {
            const icon = getSectionIcon(section.title);
            const isActive = index === 0 ? 'active' : '';
            html += `
                <div class="insight-slider-slide ${isActive}" data-slide-index="${index}">
                    <div class="insight-slider-card">
                        <div class="insight-slider-title">
                            <div class="insight-slider-title-icon"><i class="fas fa-${icon}"></i></div>
                            <h2>${section.title}</h2>
                        </div>
                        <div class="insight-slider-content">${section.content}</div>
                    </div>
                </div>`;
        });
        html += '</div>';

        html += '<div class="insight-slider-pagination">';
        sections.forEach((_, index) => {
            html += `<div class="insight-slider-dot ${index === 0 ? 'active' : ''}" onclick="goToSlide(${index})"></div>`;
        });
        html += '</div>';
        html += '</div></div>';

        window.insightSections = sections;
        window.currentSlide = 0;

        document.getElementById('insightAccordionContentShow').innerHTML = html;
        document.getElementById('insightAccordionContentShow').style.display = '';

        setTimeout(() => goToSlide(0), 100);
    }

    // ── Section Parsing ─────────────────────────────────────────────────
    const canonicalSections = [
        'Executive Summary', 'Financial Snapshot', 'Key Trends & Drivers',
        'Wins & Opportunities', 'Risks & Concerns', 'Forensic Observations',
        'Actionable Recommendations'
    ];

    function normalizeTitle(t) { return t.replace(/\s+/g, ' ').trim().toLowerCase(); }

    function parseInsightSections(reportText) {
        let sections = [];

        // Plain-text canonical titles on their own line
        const escapedTitles = canonicalSections.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
        const plainHeaderRegex = new RegExp('(?:^|\\n)(' + escapedTitles.join('|') + ')\\s*\\n', 'g');
        const plainMatches = [];
        let pm;
        while ((pm = plainHeaderRegex.exec(reportText)) !== null) {
            plainMatches.push({ title: pm[1], contentStart: pm.index + pm[0].length, matchStart: pm.index });
        }
        if (plainMatches.length > 0) {
            plainMatches.forEach((meta, i) => {
                const contentEnd = i < plainMatches.length - 1 ? plainMatches[i + 1].matchStart : reportText.length;
                let content = reportText.substring(meta.contentStart, contentEnd).trim();
                if (content.length > 0 || meta.title) {
                    sections.push({ title: meta.title, content: formatContent(content) });
                }
            });
            if (sections.length > 0) return sections;
        }

        // Separator lines (═ or =)
        const separatorRegex = /\n\s*[═─=]{10,}\s*\n/g;
        const parts = reportText.split(separatorRegex);
        if (parts.length > 1) {
            const sectionMap = {};
            const normToCanon = {};
            canonicalSections.forEach(c => normToCanon[normalizeTitle(c)] = c);
            parts.forEach(part => {
                part = part.trim();
                if (!part) return;
                const firstNL = part.indexOf('\n');
                let title = firstNL !== -1 ? part.substring(0, firstNL).trim() : part;
                let content = firstNL !== -1 ? part.substring(firstNL + 1).trim() : '';
                if (title.length > 2) {
                    const expanded = expandByCanonical(title, content, normToCanon);
                    Object.keys(expanded).forEach(t => {
                        const key = normalizeTitle(t);
                        sectionMap[key] = { title: t, content: formatContent(expanded[t]) };
                    });
                }
            });
            if (Object.keys(sectionMap).length > 0) {
                canonicalSections.forEach(c => {
                    const key = normalizeTitle(c);
                    if (sectionMap[key]) sections.push(sectionMap[key]);
                });
                if (sections.length > 0) return sections;
            }
        }

        // Markdown ## headers
        const mdPattern = /^##\s+(?:\d+\.\s*)?(.+)$/gm;
        let match;
        const mdMatches = [];
        while ((match = mdPattern.exec(reportText)) !== null) {
            mdMatches.push({ title: match[1].trim(), startIndex: match.index, endIndex: match.index + match[0].length });
        }
        if (mdMatches.length > 0) {
            const list = [];
            mdMatches.forEach((m, i) => {
                const startContent = m.endIndex;
                const endContent = i < mdMatches.length - 1 ? mdMatches[i + 1].startIndex : reportText.length;
                let content = reportText.substring(startContent, endContent).trim();
                let cleanTitle = m.title.replace(/^\d+\.\s*/, '').trim();
                if (cleanTitle.length > 2 && content) {
                    list.push({ title: cleanTitle, content: formatContent(content) });
                }
            });
            const byNorm = {};
            list.forEach(s => byNorm[normalizeTitle(s.title)] = s);
            canonicalSections.forEach(c => {
                const key = normalizeTitle(c);
                if (byNorm[key]) sections.push(byNorm[key]);
            });
            // Add any non-canonical sections
            list.forEach(s => {
                if (!canonicalSections.some(c => normalizeTitle(c) === normalizeTitle(s.title))) {
                    sections.push(s);
                }
            });
            if (sections.length > 0) return sections;
        }

        // Fallback: single section
        return [{ title: 'Financial Insights', content: formatContent(reportText) }];
    }

    function expandByCanonical(partTitle, partContent, normToCanon) {
        const result = {};
        const lines = partContent.split('\n');
        let currentTitle = partTitle;
        let buffer = [];
        for (let i = 0; i < lines.length; i++) {
            const lineTrimmed = lines[i].trim();
            const lineNorm = normalizeTitle(lineTrimmed);
            if (lineTrimmed !== '' && normToCanon.hasOwnProperty(lineNorm)) {
                if (currentTitle !== '') result[currentTitle] = buffer.join('\n').trim();
                currentTitle = normToCanon[lineNorm];
                buffer = [];
            } else {
                buffer.push(lines[i]);
            }
        }
        if (currentTitle !== '') result[currentTitle] = buffer.join('\n').trim();
        return result;
    }

    // ── Content Formatting ──────────────────────────────────────────────
    function formatContent(content) {
        if (!content) return '';
        content = content
            .replace(/^[\s\-_*]{3,}$/gm, '')
            .replace(/^[\s═─=]{3,}$/gm, '')
            .replace(/\n{3,}/g, '\n\n')
            .trim();

        content = convertMarkdownTable(content);
        content = content.replace(/^###\s+(.+)$/gm, '<h3>$1</h3>');
        content = content.replace(/^####\s+(.+)$/gm, '<h4>$1</h4>');
        content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        content = content.replace(/(?<!\*)\*([^*]+?)\*(?!\*)/g, '<em>$1</em>');

        const lines = content.split('\n');
        let inList = false, listType = null, listItems = [], processed = [];
        lines.forEach(line => {
            const trimmed = line.trim();
            if (/^[\-\*_]{3,}$/.test(trimmed) || /^[═─=]{3,}$/.test(trimmed)) return;
            if (trimmed.startsWith('<') && /<(table|thead|tbody|tr|th|td|h[1-6]|ul|ol|\/ul|\/ol)/.test(trimmed)) {
                if (inList && listItems.length > 0) {
                    processed.push(`<${listType}>${listItems.join('')}</${listType}>`);
                    listItems = []; inList = false;
                }
                processed.push(line); return;
            }
            const ulMatch = trimmed.match(/^[\-\*\+]\s+(.+)$/);
            const olMatch = trimmed.match(/^\d+\.\s+(.+)$/);
            if (ulMatch) {
                if (inList && listType !== 'ul') { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems = []; }
                inList = true; listType = 'ul'; listItems.push(`<li>${ulMatch[1]}</li>`);
            } else if (olMatch && !trimmed.match(/^[A-Z\s:]+$/)) {
                if (inList && listType !== 'ol') { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems = []; }
                inList = true; listType = 'ol'; listItems.push(`<li>${olMatch[1]}</li>`);
            } else {
                if (inList && listItems.length > 0) { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems = []; inList = false; }
                processed.push(line);
            }
        });
        if (inList && listItems.length > 0) processed.push(`<${listType}>${listItems.join('')}</${listType}>`);
        content = processed.join('\n');

        const paragraphs = content.split(/\n\n+/);
        let formatted = '';
        paragraphs.forEach(para => {
            para = para.trim();
            if (!para) return;
            if (para.match(/^<(ul|ol|table|h[1-6])/i)) {
                formatted += para + '\n';
            } else {
                formatted += '<p>' + para.replace(/\n/g, '<br>') + '</p>\n';
            }
        });
        if (!formatted) formatted = '<p>' + content.replace(/\n/g, '<br>') + '</p>';

        formatted = formatted
            .replace(/<p>[\s\-_*═─=]{3,}<\/p>/gi, '')
            .replace(/<p>\s*<\/p>/g, '');

        return formatted;
    }

    function convertMarkdownTable(text) {
        return text.replace(/(\|.+\|\n\|[\s\-\|:]+\|\n(?:\|.+\|\n?)+)/g, match => {
            const lines = match.trim().split('\n');
            if (lines.length < 2) return match;
            let html = '<div class="table-responsive"><table class="insight-table">\n<thead><tr>';
            const headerCells = lines[0].split('|').map(c => c.trim()).filter(c => c);
            headerCells.forEach(c => html += `<th>${c}</th>`);
            html += '</tr></thead>\n<tbody>';
            for (let i = 2; i < lines.length; i++) {
                const row = lines[i];
                if (!row.trim()) continue;
                const cells = row.split('|').map(c => c.trim()).filter(c => c);
                if (!cells.length) continue;
                html += '<tr>';
                cells.forEach(cell => {
                    const align = /^[\$0-9,.\-%\s]+$/.test(cell) ? 'right' : 'left';
                    html += `<td style="text-align:${align};">${cell}</td>`;
                });
                html += '</tr>\n';
            }
            html += '</tbody></table></div>';
            return html;
        });
    }

    // ── Slider Navigation ────────────────────────────────────────────────
    window.changeSlide = function(direction) {
        if (!window.insightSections || !window.insightSections.length) return;
        const total = window.insightSections.length;
        let next = window.currentSlide + direction;
        if (next < 0) next = total - 1;
        else if (next >= total) next = 0;
        goToSlide(next);
    };

    window.goToSlide = function(index) {
        if (!window.insightSections || !window.insightSections.length) return;
        const total = window.insightSections.length;
        if (index < 0 || index >= total) return;
        window.currentSlide = index;

        document.querySelectorAll('.insight-slider-slide').forEach(s => s.classList.remove('active'));
        const slide = document.querySelector(`.insight-slider-slide[data-slide-index="${index}"]`);
        if (slide) slide.classList.add('active');

        document.querySelectorAll('.insight-slider-dot').forEach((d, i) => {
            d.classList.toggle('active', i === index);
        });

        const counterEl = document.getElementById('slider-current');
        if (counterEl) counterEl.textContent = index + 1;

        const prevContainer = document.getElementById('slider-prev-container');
        const nextContainer = document.getElementById('slider-next-container');
        const prevLabel = document.getElementById('slider-prev-label');
        const nextLabel = document.getElementById('slider-next-label');

        if (prevContainer) prevContainer.style.display = index === 0 ? 'none' : 'flex';
        if (nextContainer) nextContainer.style.display = index === total - 1 ? 'none' : 'flex';
        if (total <= 1 && prevContainer && nextContainer) {
            prevContainer.style.display = 'none';
            nextContainer.style.display = 'none';
        }

        if (prevLabel) {
            const prevTitle = index > 0 ? window.insightSections[index - 1].title : '—';
            prevLabel.textContent = prevTitle;
            if (index === 0) prevLabel.style.display = 'none';
            else prevLabel.style.display = '';
        }
        if (nextLabel) {
            const nextTitle = index < total - 1 ? window.insightSections[index + 1].title : '—';
            nextLabel.textContent = nextTitle;
            if (index === total - 1) nextLabel.style.display = 'none';
            else nextLabel.style.display = '';
        }
    };

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (document.querySelector('.insight-slider-container')) {
            if (e.key === 'ArrowLeft') { e.preventDefault(); changeSlide(-1); }
            else if (e.key === 'ArrowRight') { e.preventDefault(); changeSlide(1); }
        }
    });

    // ── Section Icons ────────────────────────────────────────────────────
    function getSectionIcon(title) {
        const t = title.toLowerCase();
        if (t.includes('executive') && t.includes('summary')) return 'file-invoice';
        if (t.includes('financial') && t.includes('snapshot')) return 'chart-bar';
        if (t.includes('key') && t.includes('trend')) return 'chart-line';
        if (t.includes('win') && t.includes('opportunit')) return 'trophy';
        if (t.includes('risk') && t.includes('concern')) return 'shield-alt';
        if (t.includes('forensic')) return 'search';
        if (t.includes('actionable') && t.includes('recommendation')) return 'lightbulb';
        if (t.includes('trend')) return 'chart-line';
        if (t.includes('risk') || t.includes('assessment')) return 'shield-alt';
        if (t.includes('strategic') || t.includes('recommendation')) return 'lightbulb';
        if (t.includes('cash') || t.includes('flow')) return 'money-bill-wave';
        if (t.includes('profit') || t.includes('loss')) return 'balance-scale';
        if (t.includes('revenue') || t.includes('income')) return 'dollar-sign';
        if (t.includes('executive') || t.includes('summary')) return 'file-invoice';
        return 'chart-bar';
    }

    // ── Export to PDF ────────────────────────────────────────────────────
    window.exportToPDF = function() {
        const btn = document.getElementById('exportPdfBtn');
        const btnText = document.getElementById('exportPdfBtnText');
        if (btn) { btn.style.pointerEvents = 'none'; btn.style.opacity = '.8'; }
        if (btnText) btnText.textContent = 'Generating PDF…';

        // Show all slides for print, then restore
        const slides = document.querySelectorAll('.insight-slider-slide');
        slides.forEach(s => { s.dataset.wasActive = s.classList.contains('active'); s.style.display = 'block'; });

        setTimeout(() => {
            window.print();
            slides.forEach(s => {
                if (!s.dataset.wasActive || s.dataset.wasActive === 'false') s.style.display = 'none';
            });
            if (btn) { btn.style.pointerEvents = ''; btn.style.opacity = ''; }
            if (btnText) btnText.textContent = 'Export to PDF';
        }, 300);
    };

    // ── Edit / Save ──────────────────────────────────────────────────────
    const CI_REPORT_ID = {{ $report->id }};
    const CI_CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CI_SAVE_URL  = '/insights/' + CI_REPORT_ID + '/update-content';
    let ciEditMode = false;

    window.ciToggleEdit = function () {
        ciEditMode = !ciEditMode;

        const btn      = document.getElementById('ciBtnEdit');
        const btnLabel = document.getElementById('ciBtnEditLabel');
        const pdfBtn   = document.getElementById('exportPdfBtn');
        const slider   = document.getElementById('insightAccordionContentShow');
        const toolbar  = document.getElementById('ciEditToolbar');

        if (ciEditMode) {
            if (btnLabel) btnLabel.textContent = 'Cancel Edit';
            btn?.classList.add('active');
            if (pdfBtn) { pdfBtn.style.opacity = '.4'; pdfBtn.style.pointerEvents = 'none'; }
            slider?.classList.add('edit-mode-active');
            toolbar?.classList.add('visible');
            ciMakeEditable(true);
        } else {
            if (btnLabel) btnLabel.textContent = 'Edit Insight';
            btn?.classList.remove('active');
            if (pdfBtn) { pdfBtn.style.opacity = ''; pdfBtn.style.pointerEvents = ''; }
            slider?.classList.remove('edit-mode-active');
            toolbar?.classList.remove('visible');
            ciMakeEditable(false);
        }
    };

    function ciMakeEditable(on) {
        document.querySelectorAll('.insight-slider-content').forEach(container => {
            if (on) {
                container.setAttribute('contenteditable', 'true');
                container.setAttribute('spellcheck', 'false');
                container.style.outline = 'none';
            } else {
                container.removeAttribute('contenteditable');
                container.removeAttribute('spellcheck');
                container.style.outline = '';
            }
        });
    }

    // ── Toolbar commands ──────────────────────────────────────────────────
    window.ciExecCmd = function (cmd, value) {
        document.execCommand(cmd, false, value || null);
        ciUpdateToolbarState();
        const active = document.querySelector('.insight-slider-slide.active .insight-slider-content[contenteditable]');
        if (active) active.focus();
    };

    window.ciFormatBlock = function (tag) {
        document.execCommand('formatBlock', false, tag);
        setTimeout(() => {
            const sel = document.getElementById('ciTbStyleSel');
            if (sel) sel.value = 'p';
        }, 50);
        const active = document.querySelector('.insight-slider-slide.active .insight-slider-content[contenteditable]');
        if (active) active.focus();
    };

    window.ciInsertLink = function () {
        const url = window.prompt('Enter URL:', 'https://');
        if (url && url !== 'https://') {
            document.execCommand('createLink', false, url);
        }
        const active = document.querySelector('.insight-slider-slide.active .insight-slider-content[contenteditable]');
        if (active) active.focus();
    };

    function ciUpdateToolbarState() {
        const cmds = { ciTbBold:'bold', ciTbItalic:'italic', ciTbUnderline:'underline',
                       ciTbAlignL:'justifyLeft', ciTbAlignC:'justifyCenter', ciTbAlignR:'justifyRight' };
        Object.entries(cmds).forEach(([id, cmd]) => {
            const el = document.getElementById(id);
            if (el) el.classList.toggle('active', document.queryCommandState(cmd));
        });
    }

    document.addEventListener('selectionchange', function () {
        if (ciEditMode && !ciPreviewMode) ciUpdateToolbarState();
    });

    window.ciSaveEdits = function () {
        if (!window.insightSections) return;
        const sections = window.insightSections.map((sec, i) => {
            const slide   = document.querySelector(`.insight-slider-slide[data-slide-index="${i}"]`);
            const content = slide?.querySelector('.insight-slider-content');
            return { title: sec.title, html: content ? content.innerHTML : (sec.content || '') };
        });

        const btn = document.getElementById('ciBtnSave');
        if (btn) btn.disabled = true;
        document.getElementById('ciBtnSaveLabel').textContent = 'Saving…';

        fetch(CI_SAVE_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CI_CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ sections }),
        })
        .then(r => r.json())
        .then(data => {
            if (btn) btn.disabled = false;
            document.getElementById('ciBtnSaveLabel').textContent = 'Save';
            if (data.status === 'success') {
                window.insightSections = sections.map(s => ({ title: s.title, content: s.html }));
                const redirectUrl = data.redirect_url || null;
                const newVersion  = data.version      || null;
                Swal.fire({
                    html: `<div style="display:flex;flex-direction:column;align-items:center;padding:10px 0 6px;">
                        <div style="width:70px;height:70px;background:#ecfdf5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h2 style="font-size:20px;font-weight:700;color:#1a202c;margin:0 0 8px;">Insight Updated</h2>
                        <p style="font-size:14px;color:#64748b;margin:0;">${redirectUrl ? 'Your insight changes saved successfully as version <strong>v' + newVersion + '</strong>' : 'Your changes have been saved.'}</p>
                    </div>`,
                    showConfirmButton: true,
                    confirmButtonText: 'Got it',
                    confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b25',
                    allowOutsideClick: !redirectUrl,
                    didOpen: (popup) => {
                        const actions = popup.querySelector('.swal2-actions');
                        if (actions) { actions.style.cssText = 'width:100%;padding:0 24px;display:flex;'; }
                        const b = popup.querySelector('.swal2-confirm');
                        const brandColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b25';
                        if (b) { b.style.cssText = `flex:1;width:100%;border-radius:10px;font-size:15px;font-weight:600;padding:13px 0;background:${brandColor};border:none;color:#fff;cursor:pointer;`; }
                    },
                }).then(() => {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        ciToggleEdit();
                    }
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Could not save.' });
            }
        })
        .catch(() => {
            if (btn) btn.disabled = false;
            document.getElementById('ciBtnSaveLabel').textContent = 'Save';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' });
        });
    };

}());
</script>
@endpush
