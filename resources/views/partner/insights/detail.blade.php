@extends('layouts.app')
@section('page-title', 'Insight Report')

@push('styles')
<style>
/* ── Layout ──────────────────────────────────────────────────── */
.rdc { background:#f5f7fa;min-height:100vh; }

/* ── Back link ───────────────────────────────────────────────── */
.rd-back { display:inline-flex;align-items:center;gap:8px;color:#0788be;font-size:13.5px;font-weight:500;text-decoration:none;margin-bottom:18px;padding:9px 16px;border:1px solid #0788be;border-radius:7px;background:#fff;box-shadow:0 2px 6px rgba(16,134,190,.12);transition:all .2s; }
.rd-back:hover { background:#f0f9ff;color:#1559a6;text-decoration:none; }

/* ── Header card ─────────────────────────────────────────────── */
.rd-header { display:flex;justify-content:space-between;align-items:flex-start;background:#fff;border-radius:12px;padding:22px 26px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,.07);flex-wrap:wrap;gap:14px; }
.rd-header-left h1 { font-size:20px;font-weight:700;color:#1a202c;margin:0 0 8px; }
.rd-meta { display:flex;flex-wrap:wrap;gap:18px;margin-top:4px; }
.rd-meta-item { display:flex;align-items:center;gap:7px;color:#64748b;font-size:13px; }
.rd-meta-item i { color:#94a3b8;font-size:14px; }
.rd-header-actions { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }

/* ── Buttons ─────────────────────────────────────────────────── */
.rd-btn { height:38px;padding:0 18px;border-radius:7px;font-size:13.5px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:none;transition:all .2s;white-space:nowrap; }
.rd-btn-outline { background:#fff;color:#374151;border:1.5px solid #e2e8f0; }
.rd-btn-outline:hover { background:#f8fafc;color:#1a202c;text-decoration:none; }
.rd-btn-edit { background:#fff;color:#1a6aa8;border:1.5px solid #1a6aa8; }
.rd-btn-edit:hover { background:#f0f7ff;text-decoration:none; }
.rd-btn-edit.active { background:#1a6aa8;color:#fff; }
.rd-btn-save { background:#10b981;color:#fff;border:1.5px solid #10b981; }
.rd-btn-save:hover { background:#059669;border-color:#059669;text-decoration:none; }
.rd-btn-pdf { background:#f06b25;color:#fff;border:1.5px solid #f06b25; }
.rd-btn-pdf:hover { background:#d95a1a;border-color:#d95a1a;text-decoration:none; }
.rd-btn:disabled { opacity:.6;cursor:not-allowed; }

/* ── Publish status badge ────────────────────────────────────── */
.rd-publish-badge { padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px; }
.rd-publish-badge.draft     { background:#fef3c7;color:#92400e; }
.rd-publish-badge.published { background:#d1fae5;color:#065f46; }

/* ── Edit mode toast ─────────────────────────────────────────── */
.rd-edit-toast { position:fixed;top:80px;right:24px;background:#10b981;color:#fff;padding:10px 18px;border-radius:8px;font-size:13.5px;font-weight:500;display:none;align-items:center;gap:8px;z-index:9000;box-shadow:0 4px 16px rgba(0,0,0,.15);animation:toastIn .3s ease; }
@keyframes toastIn { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
.rd-edit-toast.show { display:flex; }

/* ── Slider ──────────────────────────────────────────────────── */
.insight-slider-container { position:relative; }
.insight-slider-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding:0 2px; }
.insight-slider-counter { color:#64748b;font-size:14px;font-weight:500; }
.insight-slider-nav { display:flex;align-items:center;gap:16px; }
.isnbc { display:flex;align-items:center;gap:10px;border:1px solid #dbdbdb;border-radius:30px;padding:5px;cursor:pointer;transition:all .3s;background:none;font-family:inherit;outline:none; }
.isnbc:hover { border-color:#ef6a24; }
.isnb { width:38px;height:38px;border-radius:50%;background:linear-gradient(124deg,#ef6a24 20%,#f99d3c 88%);color:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;pointer-events:none; }
.isnl { font-size:13px;color:#474747;padding:0 10px 0 0;font-weight:400;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.isnl.last { padding:0 0 0 10px; }
.insight-slider-slides { position:relative;overflow:hidden; }
.insight-slider-slide { display:none;animation:fadeS .4s ease; }
.insight-slider-slide.active { display:block; }
@keyframes fadeS { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

/* ── Slide card ──────────────────────────────────────────────── */
.insight-slider-card { background:#fff;border-radius:12px;padding:28px 32px;box-shadow:0 2px 8px rgba(0,0,0,.07); }
.insight-slider-title { display:flex;align-items:center;gap:14px;margin-bottom:22px; }
.ist-icon { width:42px;height:42px;background:#f06b25;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;flex-shrink:0; }
.insight-slider-title h2 { margin:0;font-size:22px;font-weight:600;color:#1a202c; }
.insight-slider-content { color:#2d3748;font-size:14.5px;line-height:1.85; }
.insight-slider-content ul,.insight-slider-content ol { padding-left:20px;margin:4px 0; }
.insight-slider-content li { margin:7px 0;list-style-type:circle; }
.insight-slider-content strong { color:#1559a6;font-weight:600; }
.insight-slider-content p { margin:0 0 10px; }
.insight-slider-content h3,.insight-slider-content h4 { color:#1559a6;margin-top:10px;margin-bottom:0;font-weight:600; }
.insight-slider-content table.insight-table { width:100%;border-collapse:collapse;margin:6px 0;box-shadow:0 1px 3px rgba(0,0,0,.08);border-radius:8px;overflow:hidden; }
.insight-slider-content table.insight-table th { border:1px solid #e2e8f0;padding:11px 12px;font-weight:600;color:#2d3748;background:#f7fafc; }
.insight-slider-content table.insight-table td { border:1px solid #e2e8f0;padding:11px 12px;color:#4a5568; }
.insight-slider-content table.insight-table tbody tr:nth-child(even) { background:#f9fafb; }

/* ── Dots ────────────────────────────────────────────────────── */
.insight-slider-pagination { display:flex;justify-content:center;gap:8px;margin-top:20px; }
.isd { width:10px;height:10px;border-radius:50%;background:#cbd5e0;cursor:pointer;transition:all .3s; }
.isd.active { background:#0788be;width:24px;border-radius:5px; }
.isd:hover { background:#94a3b8; }

@keyframes rdSpin { to { transform: rotate(360deg); } }

/* ── Edit mode ───────────────────────────────────────────────── */
.edit-mode-active .insight-slider-content [contenteditable] {
    border-radius:6px;padding:4px 6px;margin:-4px -6px;
    background:#fffbeb;border:1px dashed #f59e0b;outline:none;
    cursor:text;transition:background .2s;min-height:24px;display:block;
}
.edit-mode-active .insight-slider-content [contenteditable]:focus {
    background:#fefce8;border-color:#d97706;
    box-shadow:0 0 0 3px rgba(245,158,11,.12);
}
.edit-mode-active .insight-slider-content li [contenteditable] {
    display:inline;
}
</style>
@endpush

@section('content')
<div class="rdc">

    <a href="{{ route('partner-insights.index') }}" class="rd-back">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Insights
    </a>

    {{-- Edit mode toast --}}
    <div id="rdEditToast" class="rd-edit-toast">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Edit mode toggled. Click on text to edit.
    </div>

    {{-- Header --}}
    <div class="rd-header">
        <div class="rd-header-left">
            <h1>{{ $report->report_name }} · {{ $report->company?->company_name ?? '' }}</h1>
            <div class="rd-meta">
                <div class="rd-meta-item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#94a3b8"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>{{ $report->created_at->format(active_date_format()) }}</span>
                </div>
                <div class="rd-meta-item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#94a3b8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span>{{ $report->formatted_file_size }}</span>
                </div>
                <div class="rd-meta-item report-meta-files">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#94a3b8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                    <span>{{ $report->attachments->count() }} file(s)</span>
                </div>
                <div class="rd-meta-item">
                    <span class="rd-publish-badge {{ $report->publish_status ?? 'draft' }}" id="rdPublishBadge">
                        @if(($report->publish_status ?? 'draft') === 'published')
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Published
                        @else
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Draft
                        @endif
                    </span>
                </div>
            </div>
        </div>

        @if($report->insight_json_data)
        <div class="rd-header-actions">
            @if($report->isDraft())
            <button id="rdBtnEdit" class="rd-btn rd-btn-edit" onclick="rdToggleEdit()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span id="rdBtnEditLabel">Edit Insight</span>
            </button>
            <button id="rdBtnSave" class="rd-btn rd-btn-save" onclick="rdSaveEdits()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span id="rdBtnSaveLabel">Save</span>
            </button>
            @endif
            <a href="{{ route('partner-insights.export-pdf', $report->id) }}" class="rd-btn rd-btn-pdf" id="rdBtnPdf"
               onclick="var btn=this;btn.querySelector('span').textContent='Generating…';btn.style.pointerEvents='none';btn.style.opacity='.8';setTimeout(function(){btn.querySelector('span').textContent='Export to PDF';btn.style.pointerEvents='';btn.style.opacity='';},8000);">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Export to PDF</span>
            </a>
        </div>
        @endif
    </div>

    {{-- Slider container --}}
    @if($report->insight_json_data)
        <div id="insightSliderWrap">
            <div id="insightLoader" style="text-align:center;padding:60px;background:#fff;border-radius:12px;color:#64748b;">
                <svg style="display:block;margin:0 auto 12px;animation:rdSpin 1s linear infinite" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" d="M12 2a10 10 0 110 20A10 10 0 0112 2z" opacity=".2"/><path stroke-linecap="round" d="M12 2a10 10 0 0110 10"/></svg>
                Loading insights…
            </div>
            <div id="insightSliderContent" style="display:none;"></div>
        </div>
    @else
        <div style="text-align:center;padding:60px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.07);">
            <p style="color:#718096;font-size:17px;">
                @if(strtolower($report->status) === 'failed')
                    Failed to generate insights for this report.
                @else
                    No insights available yet.
                @endif
            </p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function(){
    if (!document.getElementById('insightLoader')) return;

    const REPORT_ID      = {{ $report->id }};
    const IS_DRAFT       = {{ $report->isDraft() ? 'true' : 'false' }};
    const CSRF           = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const DATA_URL       = '/partner-insights/' + REPORT_ID + '/data';
    const SAVE_URL       = '/partner-insights/' + REPORT_ID + '/update-content';
    const PUBLISH_URL    = '/partner-insights/' + REPORT_ID + '/publish';

    let insightSections  = [];
    let currentSlide     = 0;
    let editMode         = false;

    // ── Fetch data ────────────────────────────────────────────────────────────
    fetch(DATA_URL, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } })
    .then(r => r.json())
    .then(resp => {
        document.getElementById('insightLoader').style.display = 'none';
        if (resp.status === 'success' && resp.data) {
            buildSlider(resp.data);
        } else {
            showError('No insight data available.');
        }
    })
    .catch(() => {
        document.getElementById('insightLoader').style.display = 'none';
        showError('Failed to load insights. Please refresh the page.');
    });

    function showError(msg) {
        const wrap = document.getElementById('insightSliderContent');
        wrap.innerHTML = `<div style="padding:40px;text-align:center;color:#e53e3e;font-size:15px;background:#fff;border-radius:12px;">${msg}</div>`;
        wrap.style.display = '';
    }

    // ── Build slider ──────────────────────────────────────────────────────────
    function buildSlider(data) {
        // If previously edited and saved as structured HTML sections, render directly
        if (data && Array.isArray(data.sections) && data.sections.length > 0) {
            insightSections = data.sections.map(s => ({
                title:   s.title   || '',
                content: s.html    || s.content || '',
            }));
            renderSlider();
            return;
        }

        // Fall back to parsing raw report text
        let reportText = '';
        if (typeof data === 'string') { reportText = data; }
        else if (data?.report) { reportText = data.report; }
        else if (typeof data === 'object') {
            let longest = '';
            for (const k in data) { if (typeof data[k] === 'string' && data[k].length > longest.length) longest = data[k]; }
            reportText = longest;
        }

        if (!reportText) { showError('No report content found.'); return; }

        insightSections = parseInsightSections(reportText);
        renderSlider();
    }

    function renderSlider() {
        const total = insightSections.length;

        let html = '<div class="insight-slider-container">';
        html += '<div class="insight-slider-header">';
        html += `<div class="insight-slider-counter"><span id="slCur">1</span> of <span id="slTotal">${total}</span></div>`;
        html += '<div class="insight-slider-nav">';
        html += `<button type="button" class="isnbc" id="slPrevC" onclick="changeSlide(-1)"><span class="isnb"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg></span><span class="isnl" id="slPrevL">—</span></button>`;
        html += `<button type="button" class="isnbc" id="slNextC" onclick="changeSlide(1)"><span class="isnl last" id="slNextL">—</span><span class="isnb"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span></button>`;
        html += '</div></div>';
        html += '<div class="insight-slider-slides">';

        insightSections.forEach((sec, i) => {
            html += `<div class="insight-slider-slide${i===0?' active':''}" id="sl-slide-${i}" data-slide-index="${i}">`;
            html += '<div class="insight-slider-card">';
            html += `<div class="insight-slider-title"><div class="ist-icon">${getSectionIcon(sec.title)}</div><h2>${sec.title}</h2></div>`;
            html += `<div class="insight-slider-content" id="sl-content-${i}">${sec.content}</div>`;
            html += '</div></div>';
        });

        html += '</div>';
        html += '<div class="insight-slider-pagination">';
        insightSections.forEach((_,i) => { html += `<div class="isd${i===0?' active':''}" onclick="goToSlide(${i})"></div>`; });
        html += '</div>';
        html += '</div>';

        const wrap = document.getElementById('insightSliderContent');
        wrap.innerHTML = html;
        wrap.style.display = '';

        goToSlide(0);
    }

    window.changeSlide = function(dir) {
        const total = insightSections.length;
        let next = currentSlide + dir;
        if (next < 0) next = total - 1;
        else if (next >= total) next = 0;
        goToSlide(next);
    };

    window.goToSlide = function(idx) {
        const total = insightSections.length;
        currentSlide = idx;

        document.querySelectorAll('.insight-slider-slide').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.isd').forEach((d, i) => d.classList.toggle('active', i === idx));

        const active = document.getElementById('sl-slide-' + idx);
        if (active) active.classList.add('active');

        const cur = document.getElementById('slCur'); if (cur) cur.textContent = idx + 1;

        const prevL = document.getElementById('slPrevL');
        const nextL = document.getElementById('slNextL');
        const prevIdx = (idx - 1 + total) % total;
        const nextIdx = (idx + 1) % total;
        if (prevL) prevL.textContent = insightSections[prevIdx]?.title ?? '—';
        if (nextL) nextL.textContent = insightSections[nextIdx]?.title ?? '—';
    };

    function getSectionIcon(title) {
        const t = (title || '').toLowerCase();
        const s = (p) => `<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">${p}</svg>`;
        if (t.includes('executive') || t.includes('summary'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>');
        if (t.includes('financial') || t.includes('snapshot'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>');
        if (t.includes('trend') || t.includes('driver'))
            return s('<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>');
        if (t.includes('win') || t.includes('opportunit'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>');
        if (t.includes('risk') || t.includes('concern'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>');
        if (t.includes('forensic'))
            return s('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>');
        if (t.includes('action') || t.includes('recommend'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>');
        return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>');
    }

    // ── Edit mode ─────────────────────────────────────────────────────────────
    window.rdToggleEdit = function () {
        if (!IS_DRAFT) return;
        editMode = !editMode;

        const btn      = document.getElementById('rdBtnEdit');
        const btnLabel = document.getElementById('rdBtnEditLabel');
        const pdfBtn   = document.getElementById('rdBtnPdf');
        const slider   = document.getElementById('insightSliderContent');
        const toast    = document.getElementById('rdEditToast');

        if (editMode) {
            if (btnLabel) btnLabel.textContent = 'Cancel Edit';
            btn?.classList.add('active');
            if (pdfBtn)  { pdfBtn.style.opacity = '.4'; pdfBtn.style.pointerEvents = 'none'; }
            slider?.classList.add('edit-mode-active');
            makeContentEditable(true);
            toast?.classList.add('show');
            setTimeout(() => toast?.classList.remove('show'), 4000);
        } else {
            if (btnLabel) btnLabel.textContent = 'Edit Insight';
            btn?.classList.remove('active');
            if (pdfBtn)  { pdfBtn.style.opacity = ''; pdfBtn.style.pointerEvents = ''; }
            slider?.classList.remove('edit-mode-active');
            makeContentEditable(false);
        }
    };

    function makeContentEditable(on) {
        // Wrap text-containing elements inside .insight-slider-content with contenteditable
        document.querySelectorAll('.insight-slider-content').forEach(container => {
            if (on) {
                // Make each <p>, <li>, <h3>, <h4>, <td>, <th> editable
                container.querySelectorAll('p, li, h3, h4, td, th').forEach(el => {
                    el.setAttribute('contenteditable', 'true');
                    el.setAttribute('spellcheck', 'false');
                });
            } else {
                container.querySelectorAll('[contenteditable]').forEach(el => {
                    el.removeAttribute('contenteditable');
                    el.removeAttribute('spellcheck');
                });
            }
        });
    }

    window.rdSaveEdits = function () {
        // Collect innerHTML so all formatting (bullets, bold, headings) is preserved exactly
        const sections = insightSections.map((sec, i) => {
            const contentEl = document.getElementById('sl-content-' + i);
            return {
                title: sec.title,
                html:  contentEl ? contentEl.innerHTML : (sec.content || ''),
            };
        });

        const btn = document.getElementById('rdBtnSave');
        btn.disabled = true;
        document.getElementById('rdBtnSaveLabel').textContent = 'Saving…';

        fetch(SAVE_URL, {
            method:  'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body:    JSON.stringify({ sections }),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            document.getElementById('rdBtnSaveLabel').textContent = 'Save';
            if (data.status === 'success') {
                Swal.fire({
                    html: `
                        <div style="display:flex;flex-direction:column;align-items:center;padding:10px 0 6px;">
                            <div style="width:70px;height:70px;background:#ecfdf5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                                <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <h2 style="font-size:20px;font-weight:700;color:#1a202c;margin:0 0 8px;">Saved Successfully</h2>
                            <p style="font-size:14px;color:#64748b;margin:0;">Your changes have been saved.</p>
                        </div>`,
                    showConfirmButton: true,
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#f06b25',
                    showCloseButton: false,
                    allowOutsideClick: true,
                    didOpen: (popup) => {
                        const actions = popup.querySelector('.swal2-actions');
                        if (actions) { actions.style.cssText = 'width:100%;padding:0 24px;display:flex;'; }
                        const btn = popup.querySelector('.swal2-confirm');
                        if (btn) { btn.style.cssText = 'flex:1;width:100%;border-radius:10px;font-size:15px;font-weight:600;padding:13px 0;background:#f06b25;border:none;color:#fff;cursor:pointer;'; }
                    },
                });
                rdToggleEdit();
            } else {
                Swal.fire({ icon:'error', title:'Error', text: data.message || 'Could not save.' });
            }
        })
        .catch(() => {
            btn.disabled = false;
            document.getElementById('rdBtnSaveLabel').textContent = 'Save';
            Swal.fire({ icon:'error', title:'Error', text:'Network error. Please try again.' });
        });
    };


    // ── Section parsing (mirrors firm insight view) ───────────────────────────
    const canonicalSections = ['Executive Summary','Financial Snapshot','Key Trends & Drivers','Wins & Opportunities','Risks & Concerns','Forensic Observations','Actionable Recommendations'];

    function normalizeTitle(t) { return t.replace(/\s+/g,' ').trim().toLowerCase(); }

    function parseInsightSections(reportText) {
        let sections = [];
        const escaped = canonicalSections.map(t => t.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'));
        const re = new RegExp('(?:^|\\n)('+escaped.join('|')+')\\s*\\n','g');
        const matches = []; let m;
        while ((m = re.exec(reportText)) !== null) matches.push({title:m[1], contentStart:m.index+m[0].length, matchStart:m.index});

        if (matches.length > 0) {
            matches.forEach((meta, i) => {
                const end = i < matches.length-1 ? matches[i+1].matchStart : reportText.length;
                const rawText = reportText.substring(meta.contentStart, end).trim();
                sections.push({ title:meta.title, content:formatContent(rawText), rawText });
            });
            if (sections.length > 0) return sections;
        }

        const parts = reportText.split(/\n\s*[═─=]{10,}\s*\n/);
        if (parts.length > 1) {
            const map = {}; const n2c = {};
            canonicalSections.forEach(c => n2c[normalizeTitle(c)] = c);
            parts.forEach(part => {
                part = part.trim(); if (!part) return;
                const nl = part.indexOf('\n');
                const title = nl !== -1 ? part.substring(0,nl).trim() : part;
                const body  = nl !== -1 ? part.substring(nl+1).trim() : '';
                if (title.length > 2) {
                    const exp = expandByCanonical(title, body, n2c);
                    Object.keys(exp).forEach(t => { map[normalizeTitle(t)] = {title:t, content:formatContent(exp[t]), rawText:exp[t]}; });
                }
            });
            if (Object.keys(map).length > 0) {
                canonicalSections.forEach(c => { const k = normalizeTitle(c); if (map[k]) sections.push(map[k]); });
                if (sections.length > 0) return sections;
            }
        }

        const mdPat = /^##\s+(?:\d+\.\s*)?(.+)$/gm; let mdM; const mdList = [];
        while ((mdM = mdPat.exec(reportText)) !== null) mdList.push({title:mdM[1].trim(), startIdx:mdM.index, endIdx:mdM.index+mdM[0].length});
        if (mdList.length > 0) {
            const list = [];
            mdList.forEach((mm,i) => {
                const end = i < mdList.length-1 ? mdList[i+1].startIdx : reportText.length;
                const rawText = reportText.substring(mm.endIdx, end).trim();
                const cleanTitle = mm.title.replace(/^\d+\.\s*/,'').trim();
                if (cleanTitle.length > 2 && rawText) list.push({title:cleanTitle, content:formatContent(rawText), rawText});
            });
            const byNorm = {};
            list.forEach(s => byNorm[normalizeTitle(s.title)] = s);
            canonicalSections.forEach(c => { const k = normalizeTitle(c); if (byNorm[k]) sections.push(byNorm[k]); });
            list.forEach(s => { if (!canonicalSections.some(c => normalizeTitle(c)===normalizeTitle(s.title))) sections.push(s); });
            if (sections.length > 0) return sections;
        }

        return [{ title:'Financial Insights', content:formatContent(reportText), rawText:reportText }];
    }

    function expandByCanonical(partTitle, partContent, n2c) {
        const result = {}; const lines = partContent.split('\n');
        let cur = partTitle, buf = [];
        lines.forEach(line => {
            const ln = normalizeTitle(line.trim());
            if (line.trim() !== '' && n2c.hasOwnProperty(ln)) { if (cur !== '') result[cur] = buf.join('\n').trim(); cur = n2c[ln]; buf = []; }
            else buf.push(line);
        });
        if (cur !== '') result[cur] = buf.join('\n').trim();
        return result;
    }

    function formatContent(content) {
        if (!content) return '';
        content = content.replace(/^[\s\-_*]{3,}$/gm,'').replace(/^[\s═─=]{3,}$/gm,'').replace(/\n{3,}/g,'\n\n').trim();
        content = convertMarkdownTable(content);
        content = content.replace(/^###\s+(.+)$/gm,'<h3>$1</h3>').replace(/^####\s+(.+)$/gm,'<h4>$1</h4>');
        content = content.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/(?<!\*)\*([^*]+?)\*(?!\*)/g,'<em>$1</em>');

        const lines = content.split('\n');
        let inList=false, listType=null, listItems=[], processed=[];
        lines.forEach(line => {
            const t = line.trim();
            if (/^[\-\*_]{3,}$/.test(t) || /^[═─=]{3,}$/.test(t)) return;
            if (t.startsWith('<') && /<(table|thead|tbody|tr|th|td|h[1-6]|ul|ol|\/ul|\/ol)/.test(t)) {
                if (inList && listItems.length) { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; inList=false; }
                processed.push(line); return;
            }
            const ul = t.match(/^[\-\*\+]\s+(.+)$/); const ol = t.match(/^\d+\.\s+(.+)$/);
            if (ul) { if (inList && listType!=='ul') { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; } inList=true; listType='ul'; listItems.push(`<li>${ul[1]}</li>`); }
            else if (ol && !t.match(/^[A-Z\s:]+$/)) { if (inList && listType!=='ol') { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; } inList=true; listType='ol'; listItems.push(`<li>${ol[1]}</li>`); }
            else { if (inList && listItems.length) { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; inList=false; } processed.push(line); }
        });
        if (inList && listItems.length) processed.push(`<${listType}>${listItems.join('')}</${listType}>`);
        content = processed.join('\n');

        let fmt = '';
        content.split(/\n\n+/).forEach(para => {
            para = para.trim(); if (!para) return;
            if (para.match(/^<(ul|ol|table|h[1-6])/i)) fmt += para+'\n';
            else fmt += '<p>'+para.replace(/\n/g,'<br>')+'</p>\n';
        });
        if (!fmt) fmt = '<p>'+content.replace(/\n/g,'<br>')+'</p>';
        return fmt.replace(/<p>[\s\-_*═─=]{3,}<\/p>/gi,'').replace(/<p>\s*<\/p>/g,'');
    }

    function convertMarkdownTable(text) {
        return text.replace(/(\|.+\|\n\|[\s\-\|:]+\|\n(?:\|.+\|\n?)+)/g, match => {
            const lines = match.trim().split('\n'); if (lines.length < 2) return match;
            let html = '<div class="table-responsive"><table class="insight-table"><thead><tr>';
            lines[0].split('|').map(c=>c.trim()).filter(c=>c).forEach(c => html+=`<th>${c}</th>`);
            html += '</tr></thead><tbody>';
            for (let i=2;i<lines.length;i++) {
                const row = lines[i]; if (!row.trim()) continue;
                const cells = row.split('|').map(c=>c.trim()).filter(c=>c); if (!cells.length) continue;
                html += '<tr>'; cells.forEach(cell => html+=`<td>${cell}</td>`); html += '</tr>';
            }
            return html+'</tbody></table></div>';
        });
    }
}());
</script>
@endpush
