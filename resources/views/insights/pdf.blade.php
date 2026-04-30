<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CFO Insights Slides</title>
    <style>
        @page { margin: 0; size: letter; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { font-size: 10pt; line-height: 1.5; color: #000; padding: 50px 60px; }
        em, i { font-style: normal !important; }
        strong, b { font-weight: bold; }
        .page { page-break-after: always; padding: 0; }
        .page:last-child { page-break-after: avoid; }

        /* Cover page */
        .cover-page { text-align: center; padding: 100px 0 80px; }
        .cover-page h1 { font-size: 24pt; font-weight: bold; color: #F06B25; margin-bottom: 30px; }
        .cover-page .report-name { font-size: 12pt; color: #0788be; margin-bottom: 20px; }
        .cover-page .report-date { font-size: 11pt; color: #000; margin-bottom: 20px; }
        .cover-page .separator { border-top: 1px solid #0788be; width: 150px; margin: 20px auto 0; }

        /* Section pages */
        .section-header { margin-bottom: 15px; }
        .section-header h2 { font-size: 16pt; font-weight: bold; color: #0788be; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-separator { border-top: 1px solid #000; margin: 10px 0 15px; }

        /* Content */
        .section-content { font-size: 10pt; line-height: 1.5; color: #000; }
        .section-content h3 { font-size: 11pt; font-weight: bold; color: #0788be; margin: 15px 0 8px; text-transform: uppercase; }
        .section-content h4 { font-size: 10pt; font-weight: bold; color: #0788be; margin: 12px 0 6px; }
        .section-content p { margin-bottom: 8px; text-align: left; }
        .section-content ul, .section-content ol { margin-left: 20px; margin-bottom: 10px; padding-left: 0; }
        .section-content li { margin-bottom: 5px; line-height: 1.4; list-style-position: outside; }
        .section-content ul li { list-style-type: disc; }
        .section-content ol li { list-style-type: decimal; }
        .section-content strong, .section-content b { color: #0788be; font-weight: bold; }

        /* Tables */
        .section-content table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 9pt; page-break-inside: avoid; }
        .section-content table th { border: 1px solid #000; padding: 5px 6px; text-align: left; font-weight: bold; color: #000; background: #f5f5f5; }
        .section-content table td { border: 1px solid #000; padding: 5px 6px; color: #000; text-align: left; white-space: normal; word-wrap: break-word; }
        hr { margin: 12px 0; border: none; border-top: 1px solid #000; }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="page cover-page">
        <h1>CFO Insights Analysis</h1>
        <div class="report-name">{{ $fullTitle }}</div>
        <div class="report-date">{{ $reportDate }}</div>
        <div class="separator"></div>
    </div>

    <!-- Section Pages -->
    @foreach($sections as $section)
    <div class="page">
        <div class="section-header">
            <h2>{{ strtoupper(trim($section['title'])) }}</h2>
            <div class="section-separator"></div>
        </div>
        <div class="section-content">
            {!! $section['formatted_content'] ?? e($section['content']) !!}
        </div>
    </div>
    @endforeach
</body>
</html>
