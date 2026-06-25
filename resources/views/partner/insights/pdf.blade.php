<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $report->report_name ?? 'Insight' }}</title>
<style>
    @page { margin: 0; size: letter; }
    * { margin:0; padding:0; box-sizing:border-box; font-family:'DM Sans', Arial, Helvetica, sans-serif; }
    body { font-size:10pt; line-height:1.65; color:#374151; }
    em, i { font-style:italic; }
    strong, b { font-weight:700; }
    .page { padding:0 60px 80px; }
    .page + .page { page-break-before:always; }

    /* ── Cover page ─────────────────────────────────────────────── */
    .cover-page { padding:56px 64px; }
    .firm-header { display:block; margin-bottom:32px; padding-bottom:20px; border-bottom:2px solid #1d3557; }
    .firm-logo { max-height:48px; max-width:150px; }
    .firm-logo-icon {
        display:inline-block; width:48px; height:48px;
        background:#f06b25; border-radius:6px;
        text-align:center; line-height:48px;
        font-size:20pt; font-weight:700; color:#fff;
    }
    .firm-name { font-size:14pt; font-weight:700; color:#1a202c; margin-top:8px; }
    .firm-sub  { font-size:9pt; color:#64748b; }

    .analysis-label { font-size:7pt; font-weight:700; letter-spacing:2.5px; color:#c9952a; text-transform:uppercase; margin-bottom:8px; margin-top:28px; }
    .company-name   { font-size:26pt; font-weight:700; color:#1a202c; margin-bottom:6px; line-height:1.2; }
    .orange-bar     { height:3px; width:64px; background:#1d3557; margin-bottom:24px; }

    .meta-table { width:100%; border-collapse:collapse; border-top:2px solid #1d3557; margin-top:16px; }
    .meta-table td { padding:14px 8px 0 0; vertical-align:top; width:33%; }
    .meta-label { font-size:7pt; font-weight:700; letter-spacing:1.2px; color:#9ca3af; text-transform:uppercase; margin-bottom:5px; display:block; }
    .meta-value { font-size:11pt; font-weight:600; color:#1a202c; display:block; margin-bottom:2px; }
    .meta-sub   { font-size:9pt; color:#64748b; display:block; }

    /* ── TOC page ────────────────────────────────────────────────── */
    .toc-section-label { font-size:7pt; font-weight:700; letter-spacing:2px; color:#c9952a; text-transform:uppercase; margin-bottom:6px; }
    .toc-heading { font-size:17pt; font-weight:700; color:#1a202c; margin-bottom:20px; }
    .toc-item { display:table; width:100%; border-bottom:1px solid #f1f5f9; padding:9px 0; }
    .toc-num  { display:table-cell; font-size:9pt; font-weight:700; color:#9ca3af; width:28px; vertical-align:middle; }
    .toc-name { display:table-cell; font-size:11pt; color:#374151; font-weight:500; vertical-align:middle; }
    .toc-page { display:table-cell; font-size:10pt; color:#9ca3af; text-align:right; width:24px; vertical-align:middle; }

    /* ── Section pages ───────────────────────────────────────────── */
    .section-label { font-size:7pt; font-weight:700; letter-spacing:2px; color:#c9952a; text-transform:uppercase; margin-bottom:6px; }
    .section-title { font-size:17pt; font-weight:700; color:#1a202c; margin-bottom:8px; }
    .section-sep   { border-top:2px solid #1d3557; margin-bottom:14px; }
    .section-content { font-size:10pt; line-height:1.55; color:#374151; }

    .section-content p  { margin-top:0; margin-bottom:6px; }
    .section-content h3 { font-size:11pt; font-weight:700; color:{{ $branding?->primary_color ?? '#1a6aa8' }}; margin:8px 0 2px; page-break-after:avoid; }
    .section-content h4 { font-size:10.5pt; font-weight:700; color:{{ $branding?->primary_color ?? '#1a6aa8' }}; margin:6px 0 2px; page-break-after:avoid; }
    .section-content ul, .section-content ol { margin-left:22px; margin-bottom:8px; margin-top:2px; }
    .section-content li { margin-bottom:3px; line-height:1.5; }
    .section-content ul li { list-style-type:disc; }
    .section-content ol li { list-style-type:decimal; }
    .section-content strong, .section-content b { color:#1a202c; font-weight:700; }
    .section-content li strong { color:inherit; font-weight:normal; }
    .section-content p.is-section-heading { margin:8px 0 2px; page-break-after:avoid; }
    .section-content p.is-section-heading > strong { display:block; font-size:11pt; font-weight:700; color:#1a202c; }
    .section-content .insight-subheading { font-weight:700; font-size:11pt; color:#1a202c; margin:8px 0 2px; page-break-after:avoid; }

    .section-content table { width:100%; border-collapse:collapse; margin:12px 0; font-size:9pt; page-break-inside:avoid; page-break-before:avoid; }
    .section-content table th { padding:8px 10px; font-weight:700; color:{{ $branding?->accent_color ?? '#f06b21' }}; background:#f7fafc; text-align:left; border:1px solid #e2e8f0; }
    .section-content table td { padding:6px 10px; color:#374151; border-bottom:1px solid #e8edf4; word-wrap:break-word; }
    .section-content table tbody tr:nth-child(even) { background:#f7f9fc; }
    hr { margin:10px 0; border:none; border-top:1px solid #e2e8f0; }

    /* ── Footer ──────────────────────────────────────────────────── */
    .page-footer {
        position:fixed; bottom:0; left:0; right:0;
        padding:8px 60px; background:#fff;
        border-top:1px solid #e8ecf0;
        text-align:center;
    }
    .footer-left  { font-size:8pt; color:#9ca3af; }

    /* ── Running page header (in-flow — inside each section page) ── */
    .pdf-run-header { padding-top:12px; margin-bottom:18px; }
    .prh-right { text-align:right; font-size:8.5pt; color:#9ca3af; margin-bottom:6px; }
    .prh-right strong { color:#1d3557; font-weight:600; }
    .prh-line { border-top:1px solid #c8d4e0; }

    /* ── Cover meta stack (vertical) ─────────────────────────────── */
    .meta-stack { border-top:2px solid #1d3557; margin-top:20px; padding-top:4px; }
    .meta-row { padding:10px 0 2px; border-bottom:1px solid #f1f5f9; display:block; }
    .meta-row:last-child { border-bottom:none; }
</style>
</head>
<body>

{{-- ── Cover page ──────────────────────────────────────────────── --}}
<div class="cover-page page">

    <div class="firm-header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="{{ $companyName }}" class="firm-logo">
        @else
            <div class="firm-logo-icon">{{ strtoupper(substr($companyName, 0, 1)) }}</div>
        @endif
        <div class="firm-name">{{ $companyName }}</div>
    </div>

    <div class="analysis-label">INSIGHTS ANALYSIS</div>
    <div class="company-name">{{ $report->company?->company_name ?? '' }}</div>
    <div class="orange-bar"></div>

    <div class="meta-stack">
        <div class="meta-row">
            <span class="meta-label">PREPARED FOR</span>
            <span class="meta-value">{{ $report->user?->name ?? $report->company?->company_name ?? '—' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">PREPARED BY</span>
            <span class="meta-value">{{ $preparedBy }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">REPORT DATE</span>
            <span class="meta-value">{{ $reportDate }}</span>
        </div>
    </div>
</div>

{{-- ── Table of Contents page ──────────────────────────────────── --}}
@if(count($sections) > 0)
<div class="page">
    <div class="pdf-run-header">
        <div class="prh-right">CFO Insights Report &nbsp;|&nbsp; <strong>{{ $report->company?->company_name ?? '' }}</strong></div>
        <div class="prh-line"></div>
    </div>
    <div class="toc-section-label">CONTENTS</div>
    <div class="toc-heading">Table of Contents</div>
    @foreach($sections as $i => $section)
    <div class="toc-item">
        <span class="toc-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
        <span class="toc-name">{{ $section['title'] }}</span>
        <span class="toc-page">{{ $i + 3 }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- ── Section pages ────────────────────────────────────────────── --}}
@foreach($sections as $i => $section)
<div class="page">
    <div class="pdf-run-header">
        <div class="prh-right">CFO Insights Report &nbsp;|&nbsp; <strong>{{ $report->company?->company_name ?? '' }}</strong></div>
        <div class="prh-line"></div>
    </div>
    <div class="section-title">{{ $section['title'] }}</div>
    <div class="section-sep"></div>
    <div class="section-content">
        {!! $section['formatted_content'] ?? e($section['content'] ?? '') !!}
    </div>
</div>
@endforeach

{{-- ── Fixed footer (every page) ───────────────────────────────── --}}
<div class="page-footer">
    <span class="footer-left">Confidential — Prepared for {{ $report->company?->company_name ?? $report->user?->name ?? '' }}</span>
</div>

</body>
</html>
