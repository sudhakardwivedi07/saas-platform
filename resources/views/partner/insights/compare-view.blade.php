@extends('layouts.app')
@section('page-title', 'Comparison Report')

@push('styles')
<style>
/* ── Layout ──────────────────────────────────────────────────── */
.rdc { background:#f5f7fa;min-height:100vh; }

/* ── Back link ───────────────────────────────────────────────── */
.rd-back { display:inline-flex;align-items:center;gap:8px;height:40px;padding:0 18px;color:var(--blue);font-size:13.5px;font-weight:500;text-decoration:none;margin-bottom:18px;border:1px solid var(--blue);border-radius:7px;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,.08);transition:all .2s; }
.rd-back:hover { background:var(--blue-pale);color:var(--blue);text-decoration:none; }

/* ── Header card ─────────────────────────────────────────────── */
.rd-header { display:flex;justify-content:space-between;align-items:flex-start;background:#fff;border-radius:12px;padding:22px 26px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,.07);flex-wrap:wrap;gap:14px; }
.rd-header-left h1 { font-size:20px;font-weight:700;color:#1a202c;margin:0 0 8px; }
.rd-meta { display:flex;flex-wrap:wrap;gap:18px;margin-top:4px; }
.rd-meta-item { display:flex;align-items:center;gap:7px;color:#64748b;font-size:13px; }
.rd-meta-item i { color:#94a3b8;font-size:14px; }

/* ── Status badge ────────────────────────────────────────────── */
.rd-publish-badge { padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px; }
.rd-publish-badge.completed { background:#d1fae5;color:#065f46; }
.rd-publish-badge.failed    { background:#fee2e2;color:#991b1b; }
.rd-publish-badge.pending   { background:#fef3c7;color:#92400e; }

/* ── Header actions ──────────────────────────────────────────── */
.rd-header-actions { display:flex;align-items:center;gap:10px; }
.rd-btn { height:40px;padding:0 18px;border-radius:7px;font-size:13.5px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:none;transition:all .2s;white-space:nowrap; }
.rd-btn-more { background:#fff;color:#374151;border:1.5px solid #e2e8f0;padding:0 12px; }
.rd-btn-more:hover { background:#f8fafc;color:#1a202c; }

/* ── Slider ──────────────────────────────────────────────────── */
.insight-slider-container { position:relative; background:#f5f7fa; border-radius:12px; padding:20px; }
.insight-slider-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding:0 2px; position:relative; z-index:2; }
.insight-slider-counter { color:#64748b;font-size:14px;font-weight:500; }
.insight-slider-nav { display:flex;align-items:center;gap:16px; }
.isnbc { display:flex;align-items:center;gap:10px;border:1px solid #dbdbdb;border-radius:30px;padding:5px;cursor:pointer;transition:all .3s;background:none;font-family:inherit;outline:none; }
.isnbc:hover { border-color:var(--orange); }
.isnb { width:38px;height:38px;border-radius:50%;background:var(--orange);color:#fff;border:none;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;pointer-events:none; }
.isnl { font-size:13px;color:#474747;padding:0 10px 0 0;font-weight:400;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.isnl.last { padding:0 0 0 10px; }
.insight-slider-slides { position:relative;overflow:hidden; }
.insight-slider-slide { display:none;animation:fadeS .4s ease; }
.insight-slider-slide.active { display:block; }
@keyframes fadeS { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

/* ── Slide card ──────────────────────────────────────────────── */
.insight-slider-card { background:#fff;border-radius:12px;padding:28px 32px;box-shadow:0 2px 8px rgba(0,0,0,.07); }
.insight-slider-title { display:flex;align-items:center;gap:14px;margin-bottom:22px; }
.ist-icon { width:42px;height:42px;background:var(--orange);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;flex-shrink:0; }
.insight-slider-title h2 { margin:0;font-size:22px;font-weight:600;color:#1a202c; }
.insight-slider-content { color:#2d3748;font-size:14.5px;line-height:1.75; }
.insight-slider-content ul,.insight-slider-content ol { padding-left:22px;margin:4px 0 8px; }
.insight-slider-content li { margin:5px 0;list-style-type:circle; }
.insight-slider-content strong { color:#1a202c;font-weight:700; }
.insight-slider-content li strong { color:inherit;font-weight:normal; }
.insight-slider-content p { margin:0 0 8px; }
.insight-slider-content p.is-section-heading { margin:14px 0 4px; }
.insight-slider-content p.is-section-heading > strong { display:block;font-size:15px;font-weight:700;color:#1a202c;border-bottom:2px solid #e8eef5;padding-bottom:5px; }
.insight-slider-content .insight-subheading { font-weight:700;font-size:15px;color:#1a202c;margin:14px 0 4px;padding-bottom:5px;border-bottom:2px solid #e8eef5; }
.insight-slider-content h3,.insight-slider-content h4 { color:var(--blue);margin:14px 0 4px;font-weight:700;font-size:15px; }
.insight-slider-content table.insight-table { width:100%;border-collapse:collapse;margin:6px 0 10px;box-shadow:0 1px 3px rgba(0,0,0,.08);border-radius:8px;overflow:hidden; }
.insight-slider-content table.insight-table th { border:1px solid #e2e8f0;padding:6px 10px;font-weight:600;font-size:12.5px;color:var(--orange);background:#f7fafc;letter-spacing:.04em;text-transform:uppercase;line-height:1.4; }
.insight-slider-content table.insight-table td { border:1px solid #e2e8f0;padding:6px 10px;font-size:13px;color:#4a5568;line-height:1.4; }
.insight-slider-content table.insight-table tbody tr:nth-child(even) { background:#f9fafb; }
.insight-slider-content .table-responsive { overflow-x:auto;margin:6px 0 10px; }

/* ── Dots ────────────────────────────────────────────────────── */
.insight-slider-pagination { display:flex;justify-content:center;gap:8px;margin-top:20px; }
.isd { width:10px;height:10px;border-radius:50%;background:#cbd5e0;cursor:pointer;transition:all .3s; }
.isd.active { background:var(--blue);width:24px;border-radius:5px; }
.isd:hover { background:#94a3b8; }

@keyframes rdSpin { to { transform: rotate(360deg); } }

/* ── Responsive ──────────────────────────────────────────────── */
@media(max-width:1000px) { .rd-header { flex-direction:column; } .isnl { max-width:100px; } }
@media(max-width:700px) {
    .insight-slider-card { padding:18px 16px; }
    .insight-slider-title h2 { font-size:18px; }
    .insight-slider-header { flex-direction:column; gap:12px; align-items:flex-start; }
    .insight-slider-nav { width:100%; justify-content:space-between; }
    .insight-slider-content table.insight-table { min-width:480px; }
    .insight-slider-content table.insight-table th,
    .insight-slider-content table.insight-table td { padding:8px; font-size:13px; }
}
@media(max-width:480px) { .rd-meta { flex-direction:column; gap:8px; } .isnl { display:none; } .rd-header-left h1 { font-size:17px; } .insight-slider-card { padding:14px 12px; } }
</style>
@endpush

@php
$cr          = $compareReport;
$companyName = $cr->company?->company_name ?? '—';
$clientName  = $cr->user?->name ?? '—';
$crTitle     = $companyName . ' — ' . $cr->period_a . ' vs ' . $cr->period_b;
$badgeCls    = match($cr->status) { 'completed' => 'completed', 'failed' => 'failed', default => 'pending' };
$badgeLabel  = match($cr->status) { 'completed' => 'Ready', 'failed' => 'Failed', default => 'Pending' };
@endphp

@section('content')
<div class="rdc">

    <a href="{{ route('partner-insights.index', ['tab' => 'comparisons']) }}" class="rd-back">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Comparisons
    </a>

    {{-- Header --}}
    <div class="rd-header">
        <div class="rd-header-left">
            <h1>{{ $crTitle }}</h1>
            <div class="rd-meta">
                <div class="rd-meta-item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#94a3b8"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>{{ $cr->created_at->format(active_date_format() . ', g:i A') }}</span>
                </div>
                <div class="rd-meta-item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#94a3b8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>{{ $clientName }}</span>
                </div>
                <div class="rd-meta-item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#94a3b8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0H5m-2 0h2"/></svg>
                    <span>{{ $companyName }}</span>
                </div>
                <div class="rd-meta-item">
                    <span class="rd-publish-badge {{ $badgeCls }}">
                        @if($cr->status === 'completed')
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                        {{ $badgeLabel }}
                    </span>
                </div>
            </div>
        </div>

        @if($cr->status === 'completed' && !empty($cr->comparison_result))
        <div class="rd-header-actions">
            <div class="dm-wrap">
                <button class="rd-btn rd-btn-more" data-menu="cr-more-menu" title="Actions">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                </button>
                <div class="dm-popup" id="cr-more-menu">
                    <a href="{{ route('partner-insights.compare.preview', $cr->encrypted_id) }}" class="dm-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Preview
                    </a>
                    <div class="dm-divider"></div>
                    <a href="{{ route('partner-insights.compare.export-pdf', $cr->encrypted_id) }}" class="dm-item"
                       onclick="var el=this;el.textContent='Generating…';el.style.pointerEvents='none';setTimeout(function(){el.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.8\'><path d=\'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z\'/></svg> Export to PDF';el.style.pointerEvents='';},8000);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Export to PDF
                    </a>
                    @if(!empty($canShareInsight))
                    <div class="dm-divider"></div>
                    <a href="javascript:void(0)" class="dm-item" onclick="crShareInsight()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Share
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Slider container — same structure as detail.blade.php --}}
    <div id="insightSliderWrap">
        <div id="insightLoader" style="text-align:center;padding:60px;background:#fff;border-radius:12px;color:#64748b;">
            <svg style="display:block;margin:0 auto 12px;animation:rdSpin 1s linear infinite" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2"><path stroke-linecap="round" d="M12 2a10 10 0 110 20A10 10 0 0112 2z" opacity=".2"/><path stroke-linecap="round" d="M12 2a10 10 0 0110 10"/></svg>
            Loading comparison…
        </div>
        <div id="insightSliderContent" style="display:none;"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Share ─────────────────────────────────────────────────────────────────────
(function(){
    const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const SHARE_URL    = '/partner-insights/compare/{{ $cr->encrypted_id }}/share';
    const CLIENT_EMAIL = '{{ addslashes($cr->company?->users->first()?->email ?? $cr->company?->company_email ?? '') }}';

    window.crShareInsight = function () {
        const brandColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b25';

        if (!CLIENT_EMAIL) {
            Swal.fire({ icon: 'error', title: 'No Client Email', text: 'No email address is configured for this client.', confirmButtonColor: brandColor });
            return;
        }

        Swal.fire({
            html: `
                <div style="display:flex;flex-direction:column;align-items:center;padding:10px 0 6px;">
                    <div style="width:70px;height:70px;background:#fef2f2;border-radius:50%;border:2px solid #fca5a5;display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                        <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="#ef4444" stroke="none"/></svg>
                    </div>
                    <h2 style="font-size:20px;font-weight:700;color:#1a202c;margin:0 0 8px;">Share Comparison</h2>
                    <p style="font-size:14px;color:#64748b;margin:0;">Send this comparison report to the client via email?</p>
                </div>`,
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Confirm',
            confirmButtonColor: brandColor,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch(SHARE_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ recipient_email: CLIENT_EMAIL }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status !== 'success') {
                        Swal.showValidationMessage(data.message || 'Failed to send email.');
                    }
                    return data;
                })
                .catch(() => Swal.showValidationMessage('Network error. Please try again.'));
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then(result => {
            if (result.isConfirmed && result.value?.status === 'success') {
                Swal.fire({
                    html: `
                        <div style="display:flex;flex-direction:column;align-items:center;padding:10px 0 6px;">
                            <div style="width:70px;height:70px;background:#ecfdf5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
                                <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <h2 style="font-size:20px;font-weight:700;color:#1a202c;margin:0 0 8px;">Shared Successfully</h2>
                            <p style="font-size:14px;color:#64748b;margin:0;">Comparison shared successfully via email.</p>
                        </div>`,
                    showConfirmButton: true,
                    confirmButtonText: 'Got it',
                    confirmButtonColor: brandColor,
                });
            }
        });
    };
}());
</script>
<script>
(function(){
    if (!document.getElementById('insightLoader')) return;

    const ENCRYPTED_ID = '{{ $cr->encrypted_id }}';
    const DATA_URL     = '/partner-insights/compare/' + ENCRYPTED_ID + '/data';

    let insightSections = [];
    let currentSlide    = 0;

    // ── Fetch data — identical pattern to detail.blade.php ────────────────────
    fetch(DATA_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(resp => {
        document.getElementById('insightLoader').style.display = 'none';
        if (resp.status === 'success' && resp.data) {
            buildSlider(resp.data);
        } else {
            showError(resp.message || 'No comparison data available.');
        }
    })
    .catch(() => {
        document.getElementById('insightLoader').style.display = 'none';
        showError('Failed to load comparison. Please refresh the page.');
    });

    function showError(msg) {
        const wrap = document.getElementById('insightSliderContent');
        wrap.innerHTML = `<div style="padding:40px;text-align:center;color:#e53e3e;font-size:15px;background:#fff;border-radius:12px;">${msg}</div>`;
        wrap.style.display = '';
    }

    // ── Build slider — identical to detail.blade.php ──────────────────────────
    function buildSlider(data) {
        if (data && Array.isArray(data.sections) && data.sections.length > 0) {
            insightSections = data.sections.map(s => ({
                title:   s.title   || '',
                content: s.html    || s.content || '',
            }));
            renderSlider();
            return;
        }

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
        insightSections.forEach((_, i) => { html += `<div class="isd${i===0?' active':''}" onclick="goToSlide(${i})"></div>`; });
        html += '</div></div>';

        const wrap = document.getElementById('insightSliderContent');
        wrap.innerHTML = html;
        wrap.style.display = '';

        document.querySelectorAll('.insight-table').forEach(function(table) {
            var rightCols = new Set();
            table.querySelectorAll('tbody td').forEach(function(td) {
                if (/^\s*[\$£€]?[\d,]+(\.\d+)?\s*(%|[a-zA-Z]*)\s*$/.test(td.textContent.trim())) {
                    td.style.textAlign = 'right';
                    rightCols.add(td.cellIndex);
                }
            });
            table.querySelectorAll('thead th').forEach(function(th) {
                if (rightCols.has(th.cellIndex)) th.style.textAlign = 'right';
            });
        });

        document.querySelectorAll('.insight-slider-content p').forEach(function(p) {
            if (p.textContent.trim() !== '-') return;
            var next = p.nextElementSibling;
            if (!next) { p.remove(); return; }
            var prev = p.previousElementSibling;
            var ul = (prev && prev.tagName === 'UL') ? prev : document.createElement('ul');
            if (!prev || prev.tagName !== 'UL') p.parentNode.insertBefore(ul, p);
            var li = document.createElement('li');
            li.innerHTML = next.innerHTML;
            ul.appendChild(li);
            next.remove();
            p.remove();
        });

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

        const prevL   = document.getElementById('slPrevL');
        const nextL   = document.getElementById('slNextL');
        const prevIdx = (idx - 1 + total) % total;
        const nextIdx = (idx + 1) % total;
        if (prevL) prevL.textContent = insightSections[prevIdx]?.title ?? '—';
        if (nextL) nextL.textContent = insightSections[nextIdx]?.title ?? '—';
    };

    function getSectionIcon(title) {
        const t = (title || '').toLowerCase();
        const s = (p) => `<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">${p}</svg>`;
        if (t.includes('executive') || t.includes('summary') || t.includes('headline'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>');
        if (t.includes('financial') || t.includes('snapshot') || t.includes('trajectory'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>');
        if (t.includes('trend') || t.includes('driver') || t.includes('overview'))
            return s('<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>');
        if (t.includes('win') || t.includes('opportunit') || t.includes('improvement') || t.includes('trustworthy'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>');
        if (t.includes('risk') || t.includes('concern') || t.includes('caution'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>');
        if (t.includes('forensic'))
            return s('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>');
        if (t.includes('action') || t.includes('recommend') || t.includes('carry') || t.includes('forward'))
            return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>');
        return s('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>');
    }

    // ── Section parsing — identical to detail.blade.php ───────────────────────
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

        return [{ title:'Comparison Report', content:formatContent(reportText), rawText:reportText }];
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
        content = content.replace(/^-[ \t]*\n(?=\S)/gm, '- ');
        content = convertMarkdownTable(content);
        content = content.replace(/^###\s+(.+)$/gm,'<h3>$1</h3>').replace(/^####\s+(.+)$/gm,'<h4>$1</h4>');
        content = content.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/(?<!\*)\*([^*]+?)\*(?!\*)/g,'<em>$1</em>');
        content = content.replace(/([^\n\-\*\+]) +(<strong>[A-Za-z][^<]*:<\/strong>)/g,'$1\n\n$2');

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

        for (let i = 0; i < processed.length - 1; i++) {
            const c = (processed[i]||'').trim(), n = (processed[i+1]||'').trim();
            if (/^<strong>[^<]+:<\/strong>$/.test(c) && n && n !== '' && !/^<(ul|ol|table|div|h[1-6]|hr)/i.test(n)) {
                processed[i] = c + ' ' + n; processed.splice(i+1, 1); i--;
            }
        }
        for (let i = 1; i < processed.length; i++) {
            if (/^<strong>[^<]+:<\/strong>/.test((processed[i]||'').trim()) && (processed[i-1]||'') !== '') {
                processed.splice(i, 0, ''); i++;
            }
        }
        for (let i = 0; i < processed.length - 1; i++) {
            const curr = (processed[i] || '').trim();
            const next = (processed[i+1] || '').trim();
            if (curr && !/^<(ul|ol|table|div|h[1-6]|hr)/i.test(curr) && /^<(ul|ol)/i.test(next)) {
                processed[i] = `<div class="insight-subheading">${curr}</div>`;
            }
        }

        content = processed.join('\n');
        let fmt = '';
        content.split(/\n\n+/).forEach(para => {
            para = para.trim(); if (!para) return;
            if (para.match(/^<(ul|ol|table|div|h[1-6])/i)) fmt += para+'\n';
            else if (/^<strong>[^<]+<\/strong>$/.test(para)) fmt += '<p class="is-section-heading">'+para+'</p>\n';
            else fmt += '<p>'+para.replace(/\n/g,'<br>')+'</p>\n';
        });
        if (!fmt) fmt = '<p>'+content.replace(/\n/g,'<br>')+'</p>';
        return fmt.replace(/<p>[\s\-_*═─=]{3,}<\/p>/gi,'').replace(/<p>\s*<\/p>/g,'');
    }

    function convertMarkdownTable(text) {
        return text.replace(/(\|.+\|\n\|[\s\-\|:]+\|\n(?:\|.+\|\n?)+)/g, match => {
            const lines = match.trim().split('\n'); if (lines.length < 2) return match;
            const headers = lines[0].split('|').map(c=>c.trim()).filter(c=>c);
            const colAligns = new Array(headers.length).fill('left');
            for (let i=2;i<lines.length;i++) {
                const cells = lines[i].split('|').map(c=>c.trim()).filter(c=>c);
                cells.forEach((cell, ci) => { if (ci < colAligns.length && /^\s*[\$£€]?[\d,]+(\.\d+)?\s*(%|[a-zA-Z]*)\s*$/.test(cell)) colAligns[ci] = 'right'; });
            }
            let html = '<div class="table-responsive"><table class="insight-table"><thead><tr>';
            headers.forEach((c, ci) => html+=`<th style="text-align:${colAligns[ci]};">${c}</th>`);
            html += '</tr></thead><tbody>';
            for (let i=2;i<lines.length;i++) {
                const row = lines[i]; if (!row.trim()) continue;
                const cells = row.split('|').map(c=>c.trim()).filter(c=>c); if (!cells.length) continue;
                html += '<tr>'; cells.forEach(cell => html+=`<td style="text-align:${/^\s*[\$£€]?[\d,]+(\.\d+)?\s*(%|[a-zA-Z]*)\s*$/.test(cell)?'right':'left'};">${cell}</td>`); html += '</tr>';
            }
            return html+'</tbody></table></div>';
        });
    }
}());
</script>
@endpush
