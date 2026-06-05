<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $report->report_name ?? 'Insight' }}</title>
<style>
    @page { margin: 0; size: letter; }
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial, Helvetica, sans-serif; }
    body { font-size:10pt; line-height:1.5; color:#000; }
    em, i { font-style:normal !important; }
    strong, b { font-weight:bold; }
    .page { page-break-after:always; padding:50px 60px; }
    .page:last-child { page-break-after:avoid; }

    /* ── Cover page ─────────────────────────────────────────────── */
    .cover-page { padding:50px 60px; }
    .firm-header { display:block; margin-bottom:30px; padding-bottom:20px; border-bottom:1px solid #e8ecf0; }
    .firm-logo { max-height:52px; max-width:160px; }
    .firm-logo-icon {
        display:inline-block; width:52px; height:52px;
        background:#f06b25; border-radius:8px;
        text-align:center; line-height:52px;
        font-size:22pt; font-weight:bold; color:#fff;
    }
    .firm-name { font-size:16pt; font-weight:bold; color:#1a202c; margin-top:8px; }
    .firm-sub  { font-size:9pt; color:#64748b; }

    .analysis-label { font-size:7pt; font-weight:bold; letter-spacing:2px; color:#9ca3af; text-transform:uppercase; margin-bottom:6px; margin-top:24px; }
    .company-name   { font-size:26pt; font-weight:bold; color:#1a202c; margin-bottom:4px; }
    .report-sub     { font-size:11pt; color:#64748b; margin-bottom:12px; }
    .orange-bar     { height:3px; width:56px; background:#f06b25; margin-bottom:22px; }

    .meta-table { width:100%; border-collapse:collapse; border-top:1px solid #f1f5f9; padding-top:14px; margin-top:14px; }
    .meta-table td { padding:12px 6px 0 0; vertical-align:top; width:33%; }
    .meta-label { font-size:7pt; font-weight:bold; letter-spacing:1px; color:#9ca3af; text-transform:uppercase; margin-bottom:5px; display:block; }
    .meta-value { font-size:11pt; font-weight:bold; color:#1a202c; display:block; margin-bottom:2px; }
    .meta-sub   { font-size:9pt; color:#64748b; display:block; }

    /* ── TOC page ────────────────────────────────────────────────── */
    .toc-section-label { font-size:7pt; font-weight:bold; letter-spacing:2px; color:#9ca3af; text-transform:uppercase; margin-bottom:4px; }
    .toc-heading { font-size:18pt; font-weight:bold; color:#1a202c; margin-bottom:20px; }
    .toc-item { display:block; border-bottom:1px solid #f8fafc; padding:8px 0; }
    .toc-num  { font-size:9pt; font-weight:bold; color:#9ca3af; display:inline-block; width:26px; }
    .toc-name { font-size:11pt; color:#374151; font-weight:500; }
    .toc-page { font-size:10pt; color:#9ca3af; float:right; }

    /* ── Section pages ───────────────────────────────────────────── */
    .section-label { font-size:7pt; font-weight:bold; letter-spacing:2px; color:#9ca3af; text-transform:uppercase; margin-bottom:6px; }
    .section-title { font-size:18pt; font-weight:bold; color:#1a202c; margin-bottom:10px; }
    .section-sep   { border-top:1px solid #e2e8f0; margin-bottom:18px; }
    .section-content { font-size:10pt; line-height:1.55; color:#374151; }

    .section-content p  { margin-bottom:8px; }
    .section-content h3 { font-size:11pt; font-weight:bold; color:#1a6aa8; margin:14px 0 6px; text-transform:uppercase; }
    .section-content h4 { font-size:10pt; font-weight:bold; color:#1a6aa8; margin:10px 0 4px; }
    .section-content ul, .section-content ol { margin-left:20px; margin-bottom:10px; }
    .section-content li { margin-bottom:4px; line-height:1.4; }
    .section-content ul li { list-style-type:disc; }
    .section-content ol li { list-style-type:decimal; }
    .section-content strong, .section-content b { color:#1a6aa8; font-weight:bold; }

    .section-content table { width:100%; border-collapse:collapse; margin:10px 0; font-size:9pt; page-break-inside:avoid; }
    .section-content table th { border:1px solid #000; padding:5px 7px; font-weight:bold; color:#000; background:#f5f5f5; text-align:left; }
    .section-content table td { border:1px solid #000; padding:5px 7px; color:#000; word-wrap:break-word; }
    hr { margin:10px 0; border:none; border-top:1px solid #e2e8f0; }

    /* ── Footer ──────────────────────────────────────────────────── */
    .page-footer {
        position:fixed; bottom:0; left:0; right:0;
        padding:8px 60px;
        border-top:1px solid #f1f5f9;
        display:block;
    }
    .footer-brand { font-size:8pt; color:#9ca3af; }
    .footer-conf  { font-size:8pt; color:#9ca3af; float:right; }
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
    <div class="report-sub">{{ $report->report_name }}</div>
    <div class="orange-bar"></div>

    <table class="meta-table">
        <tr>
            <td>
                <span class="meta-label">PREPARED FOR</span>
                <span class="meta-value">{{ $report->user?->name ?? $report->company?->company_name ?? '—' }}</span>
                <span class="meta-sub">{{ $report->company?->company_name ?? '' }}</span>
            </td>
            <td>
                <span class="meta-label">PREPARED BY</span>
                <span class="meta-value">{{ $companyName }}</span>
            </td>
            <td>
                <span class="meta-label">DATE</span>
                <span class="meta-value">{{ $reportDate }}</span>
            </td>
        </tr>
    </table>
</div>

{{-- ── Table of Contents page ──────────────────────────────────── --}}
@if(count($sections) > 0)
<div class="page">
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
    <div class="section-label">SECTION {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
    <div class="section-title">{{ $section['title'] }}</div>
    <div class="section-sep"></div>
    <div class="section-content">
        {!! $section['formatted_content'] ?? e($section['content'] ?? '') !!}
    </div>
</div>
@endforeach

</body>
</html>
