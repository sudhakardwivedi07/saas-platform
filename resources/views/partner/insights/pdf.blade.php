<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $report->report_name ?? 'Insight' }}</title>
<style>
    @page { margin: 50px 0 0; size: letter; }
    * { margin:0; padding:0; box-sizing:border-box; font-family:'DM Sans', Arial, Helvetica, sans-serif; }
    body { font-size:10.5pt; line-height:1.55; color:#374151; }
    em, i { font-style:italic; }
    strong, b { font-weight:700; }
    .page { padding:0 60px 88px; }
    /* .ph = page-top heading spacer (70px); .pts = pre-table spacer (50px) */
    .ph  { height:70px; font-size:1px; line-height:1; color:transparent; page-break-after:avoid; }
    .pts { height:10px; font-size:1px; line-height:1; color:transparent; page-break-after:avoid; }
    .page + .page { page-break-before:always; }

    /* ── Cover page ─────────────────────────────────────────────── */
    .cover-page { padding:48px 64px 20px; }
    .firm-header { display:block; margin-bottom:28px; padding-bottom:16px; border-bottom:2px solid #1d3557; }
    .firm-logo { max-height:52px; max-width:180px; }
    .firm-logo-icon {
        display:inline-block; width:52px; height:52px;
        background:#f06b25; border-radius:6px;
        text-align:center; line-height:52px;
        font-size:22pt; font-weight:700; color:#fff;
    }
    .firm-name { font-size:14pt; font-weight:700; color:#1a202c; margin-top:8px; }
    .firm-sub  { font-size:9pt; color:#64748b; }

    .analysis-label { font-size:7.5pt; font-weight:700; letter-spacing:2.5px; color:#c9952a; text-transform:uppercase; margin-bottom:10px; margin-top:36px; }
    .company-name   { font-size:28pt; font-weight:700; color:#1a202c; margin-bottom:10px; line-height:1.2; }
    .orange-bar     { height:3px; width:100%; background:#1d3557; margin-bottom:28px; }

    .meta-table { width:100%; border-collapse:collapse; border-top:2px solid #1d3557; margin-top:16px; }
    .meta-table td { padding:14px 8px 0 0; vertical-align:top; width:33%; }
    .meta-label { font-size:7pt; font-weight:700; letter-spacing:1.2px; color:#9ca3af; text-transform:uppercase; margin-bottom:5px; display:block; }
    .meta-value { font-size:11pt; font-weight:600; color:#1a202c; display:block; margin-bottom:2px; }
    .meta-sub   { font-size:9pt; color:#64748b; display:block; }

    /* ── TOC page ────────────────────────────────────────────────── */
    .toc-section-label { font-size:7pt; font-weight:700; letter-spacing:2px; color:#c9952a; text-transform:uppercase; margin-bottom:8px; }
    .toc-heading { font-size:22pt; font-weight:700; color:#1a202c; margin-bottom:24px; }
    .toc-item { display:table; width:100%; border-bottom:1px solid #f1f5f9; padding:10px 0; }
    .toc-num  { display:table-cell; font-size:9pt; font-weight:700; color:#9ca3af; width:32px; vertical-align:middle; }
    .toc-name { display:table-cell; font-size:11pt; color:#374151; font-weight:500; vertical-align:middle; }
    .toc-page { display:table-cell; font-size:10pt; font-weight:600; color:#1d3557; text-align:right; width:28px; vertical-align:middle; }

    /* ── Section pages ───────────────────────────────────────────── */
    .section-label { font-size:7pt; font-weight:700; letter-spacing:2px; color:#c9952a; text-transform:uppercase; margin-bottom:6px; }
    .section-title { font-size:22pt; font-weight:700; color:#1a202c; margin-bottom:10px; }
    .section-sep   { border-top:2px solid #1d3557; margin-bottom:16px; }
    .section-content { font-size:10.5pt; line-height:1.55; color:#374151; }

    .section-content p  { margin-top:0; margin-bottom:7px; }
    .section-content h3 {
        font-size:11.5pt; font-weight:700;
        color:{{ $branding?->primary_color ?? '#1a6aa8' }};
        margin:20px 0 4px; padding-top:14px;
        border-top:1px solid #e8edf4;
        page-break-after:avoid;
    }
    .section-content h4 { font-size:11pt; font-weight:700; color:{{ $branding?->primary_color ?? '#1a6aa8' }}; margin:8px 0 3px; page-break-after:avoid; }
    .section-content ul, .section-content ol { margin-left:22px; margin-bottom:8px; margin-top:2px; }
    .section-content li { margin-bottom:4px; line-height:1.55; }
    .section-content ul li { list-style-type:disc; }
    .section-content ol li { list-style-type:decimal; }
    .section-content strong, .section-content b { color:#1a202c; font-weight:700; }
    .section-content li strong { color:inherit; font-weight:normal; }
    .section-content p.is-section-heading { margin:8px 0 2px; page-break-after:avoid; }
    .section-content p.is-section-heading > strong { display:block; font-size:11pt; font-weight:700; color:#1a202c; }
    .section-content .insight-subheading { font-weight:700; font-size:11pt; color:#1a202c; margin:8px 0 2px; page-break-after:avoid; }

    .section-content table { width:100%; border-collapse:collapse; margin:8px 0 12px; font-size:9.5pt; page-break-inside:avoid; page-break-before:avoid; }
    .section-content table th { padding:9px 12px; font-weight:700; color:{{ $branding?->accent_color ?? '#f06b21' }}; background:#f7fafc; text-align:left; border:1px solid #e2e8f0; border-bottom:2px solid #dde4ec; }
    .section-content table td { padding:7px 12px; color:#374151; border-bottom:1px solid #e8edf4; word-wrap:break-word; }
    .section-content table td.num { text-align:right !important; }
    .section-content table th.num { text-align:right !important; }
    .section-content table tbody tr:nth-child(even) { background:#f7f9fc; }
    hr { margin:10px 0; border:none; border-top:1px solid #e2e8f0; }

    /* ── Footer ──────────────────────────────────────────────────── */
    .page-footer {
        position:fixed; bottom:0; left:0; right:0;
        padding:5px 60px 6px; background:#fff;
        border-top:1px solid #e8ecf0;
    }
    .footer-bar { display:table; width:100%; margin-bottom:2px; }
    .footer-report { display:table-cell; font-size:8pt; color:#9ca3af; text-align:left; vertical-align:middle; }
    .footer-pg { display:table-cell; font-size:8.5pt; font-weight:700; color:#1d3557; text-align:right; vertical-align:middle; }
    .footer-pg::before { content:counter(page); }
    .footer-conf { font-size:7.5pt; color:#b8c0cc; text-align:center; }

    /* ── Running page header ─────────────────────────────────────── */
    .pdf-running-header {
        position:fixed; top:0; left:0; right:0; height:42px;
        padding:10px 60px 6px; background:#fff;
        border-bottom:1px solid #c8d4e0;
    }
    .prh-right { text-align:right; font-size:8.5pt; color:#9ca3af; }
    .prh-right strong { color:#1d3557; font-weight:600; }

    /* ── Cover meta stack (vertical) ─────────────────────────────── */
    .meta-stack { border-top:2px solid #1d3557; margin-top:24px; padding-top:4px; }
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
    <div class="ph">&nbsp;</div>
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
    <div class="ph">&nbsp;</div>
    <div class="section-title">{{ $section['title'] }}</div>
    <div class="section-sep"></div>
    <div class="section-content">
        @php
            $sc = $section['formatted_content'] ?? e($section['content'] ?? '');
            // Step 1: inject pre-table spacer
            $sc = preg_replace('/(<table[\s>])/i', '<div class="pts">&nbsp;</div>$1', $sc) ?? $sc;
            // Step 2: move spacer before <p>label</p> that precedes spacer+table
            $sc = preg_replace('/(<p\b[^>]*>(?:(?!<\/p>).)*?<\/p>)\s*<div class="pts">&nbsp;<\/div>(<table[\s>])/is', '<div class="pts">&nbsp;</div>$1$2', $sc) ?? $sc;
            // Step 3: empty td cells → &nbsp; so rows don't look visually incomplete
            $sc = preg_replace('/<td([^>]*)>\s*<\/td>/i', '<td$1>&nbsp;</td>', $sc) ?? $sc;
            // Step 4: two-pass table alignment
            // Pass A detects which column indices are numeric; Pass B right-aligns td + th for those columns
            $aligned = preg_replace_callback('/<table\b[^>]*>.*?<\/table>/is', function($tm) {
                $tbl = $tm[0]; $numCols = [];
                preg_replace_callback('/<tr\b[^>]*>(.*?)<\/tr>/is', function($rm) use (&$numCols) {
                    $c = 0;
                    preg_replace_callback('/<td\b[^>]*>(.*?)<\/td>/is', function($cm) use (&$c, &$numCols) {
                        if (preg_match('/^\s*\(?[\$£€]?[+\-]?[\d][\d,\.]*[%KMBkmb]?\)?\s*$/', trim(strip_tags($cm[1])))) { $numCols[$c] = true; }
                        $c++; return $cm[0];
                    }, $rm[1]);
                    return $rm[0];
                }, $tbl);
                if (empty($numCols)) return $tbl;
                $tbl = preg_replace_callback('/<tr\b([^>]*)>(.*?)<\/tr>/is', function($rm) use ($numCols) {
                    $inner = $rm[2]; $c = 0;
                    $inner = preg_replace_callback('/<td\b([^>]*)>(.*?)<\/td>/is', function($cm) use (&$c, $numCols) {
                        $a = rtrim($cm[1]);
                        if (isset($numCols[$c]) && !preg_match('/\balign\s*=/i', $a)) { $a .= ' align="right"'; }
                        $c++; return '<td' . $a . '>' . $cm[2] . '</td>';
                    }, $inner) ?? $inner;
                    $c2 = 0;
                    $inner = preg_replace_callback('/<th\b([^>]*)>(.*?)<\/th>/is', function($cm) use (&$c2, $numCols) {
                        $a = rtrim($cm[1]);
                        if (isset($numCols[$c2])) {
                            if (preg_match('/\bclass\s*=\s*"([^"]*)"/i', $a, $clm)) { $a = str_replace($clm[0], 'class="'.$clm[1].' num"', $a); }
                            elseif (!preg_match('/\bclass\s*=/i', $a)) { $a .= ' class="num"'; }
                            if (!preg_match('/\balign\s*=/i', $a)) { $a .= ' align="right"'; }
                        }
                        $c2++; return '<th' . $a . '>' . $cm[2] . '</th>';
                    }, $inner) ?? $inner;
                    return '<tr' . $rm[1] . '>' . $inner . '</tr>';
                }, $tbl) ?? $tbl;
                return $tbl;
            }, $sc);
            if ($aligned !== null) { $sc = $aligned; }
        @endphp
        {!! $sc !!}
    </div>
</div>
@endforeach

{{-- ── Fixed running header (every page including overflow) ──────── --}}
<div class="pdf-running-header">
    <div class="prh-right"><strong>{{ $report->company?->company_name ?? '' }}</strong></div>
</div>

{{-- ── Fixed footer (every page) ───────────────────────────────── --}}
<div class="page-footer">
    <div class="footer-bar">
        <span class="footer-report">CFO Insights Report</span>
        <span class="footer-pg"></span>
    </div>
    <div class="footer-conf">Confidential — Prepared for {{ $report->company?->company_name ?? $report->user?->name ?? '' }}</div>
</div>

</body>
</html>
