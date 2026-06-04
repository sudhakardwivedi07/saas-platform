@extends('layouts.app')

@section('page-title', $insight->title ?? 'Insight Detail')

@section('content')

{{-- Back Button --}}
<a href="{{ route('clients.insights', $client->id) }}" class="back-btn">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to Insights
</a>

{{-- Breadcrumb --}}
<nav style="display:flex;align-items:center;gap:6px;font-size:0.8125rem;color:var(--text-2);margin:0.75rem 0 1rem 0;flex-wrap:wrap;">
    <a href="{{ route('clients.index') }}" style="color:var(--text-2);text-decoration:none;">Clients</a>
    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('clients.show', $client->id) }}" style="color:var(--text-2);text-decoration:none;">{{ $client->name }}</a>
    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('clients.insights', $client->id) }}" style="color:var(--text-2);text-decoration:none;">Insights</a>
    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span style="color:var(--text-1);font-weight:500;">{{ $insight->title ?? 'Detail' }}</span>
</nav>

{{-- Page Header with Pagination Nav --}}
<div class="ph" style="margin-bottom:1.5rem;align-items:flex-start;">
    <div class="ph-left" style="flex:1;min-width:0;">
        <h1 style="font-size:1.375rem;font-weight:700;color:var(--text-1);margin:0 0 4px 0;line-height:1.3;">
            {{ $insight->title ?? 'Financial Insight Report' }}
        </h1>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:0.8125rem;color:var(--text-2);">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:-2px;margin-right:3px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ isset($insight->created_at) ? \Carbon\Carbon::parse($insight->created_at)->format(active_date_format()) : 'N/A' }}
            </span>
            <span style="font-size:0.8125rem;color:var(--text-2);">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:-2px;margin-right:3px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ $client->name }}
            </span>
            <span class="badge b-green">Ready</span>
        </div>
    </div>

    {{-- Prev / Next navigation --}}
    <div class="ph-actions" style="display:flex;align-items:center;gap:8px;">
        @if($prevInsight ?? false)
            <a href="{{ route('clients.insights.show', [$client->id, $prevInsight->id]) }}"
               class="btn btn-ghost btn-sm" style="text-decoration:none;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Prev
            </a>
        @else
            <button class="btn btn-ghost btn-sm" disabled style="opacity:0.4;cursor:not-allowed;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Prev
            </button>
        @endif

        <span style="font-size:0.8125rem;color:var(--text-2);padding:0 2px;">
            {{ $currentIndex ?? 1 }} / {{ $totalInsights ?? 1 }}
        </span>

        @if($nextInsight ?? false)
            <a href="{{ route('clients.insights.show', [$client->id, $nextInsight->id]) }}"
               class="btn btn-ghost btn-sm" style="text-decoration:none;">
                Next
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7 7-7"/>
                </svg>
            </a>
        @else
            <button class="btn btn-ghost btn-sm" disabled style="opacity:0.4;cursor:not-allowed;">
                Next
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7 7-7"/>
                </svg>
            </button>
        @endif

        <a href="{{ route('clients.insights.download', [$client->id, $insight->id]) }}"
           class="btn btn-primary btn-sm" style="text-decoration:none;margin-left:4px;">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download PDF
        </a>
    </div>
</div>

{{-- Section Cards --}}
<div style="display:flex;flex-direction:column;gap:1.25rem;" id="insightSections">

    {{-- Section 1: Executive Summary --}}
    <div class="card insight-section" id="section-1">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(26,106,168,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--blue)" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <div class="card-title">Executive Summary</div>
                <div class="card-sub">High-level overview of the financial period.</div>
            </div>
        </div>

        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;">
            @php
                $summaryPoints = [
                    'Total revenue for the period came in at the expected range based on prior trends.',
                    'Operating expenses remained stable with no significant anomalies detected.',
                    'Net profit margin is within the healthy threshold for this business category.',
                    'Cash flow from operations shows a positive trend compared to the previous period.',
                    'No outstanding invoices older than 60 days were identified in this review.',
                ];
            @endphp
            @foreach($summaryPoints as $point)
            <li style="display:flex;align-items:flex-start;gap:10px;font-size:0.875rem;color:var(--text-1);line-height:1.55;">
                <span style="width:20px;height:20px;border-radius:50%;background:rgba(26,106,168,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="var(--blue)" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                {{ $point }}
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Section 2: Key Findings --}}
    <div class="card insight-section" id="section-2">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(240,107,33,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--orange)" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <div class="card-title">Key Findings</div>
                <div class="card-sub">Notable patterns and areas requiring attention.</div>
            </div>
        </div>

        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;">
            @php
                $findings = [
                    ['text' => 'Accounts receivable aging shows 3 invoices approaching the 30-day threshold.', 'type' => 'warning'],
                    ['text' => 'Payroll expenses increased by approximately 8% compared to the prior quarter.', 'type' => 'info'],
                    ['text' => 'Subscription revenue is the highest-performing income category this period.', 'type' => 'success'],
                    ['text' => 'Office supplies and miscellaneous expenses appear higher than typical — review recommended.', 'type' => 'warning'],
                    ['text' => 'Tax liability estimate for Q2 is projected to fall within expected bounds.', 'type' => 'success'],
                ];
                $findingColors = ['warning' => '#f59e0b', 'info' => 'var(--blue)', 'success' => '#16a34a'];
                $findingBg     = ['warning' => 'rgba(245,158,11,0.1)', 'info' => 'rgba(26,106,168,0.1)', 'success' => 'rgba(22,163,74,0.1)'];
            @endphp
            @foreach($findings as $finding)
            <li style="display:flex;align-items:flex-start;gap:10px;font-size:0.875rem;color:var(--text-1);line-height:1.55;">
                <span style="width:20px;height:20px;border-radius:50%;background:{{ $findingBg[$finding['type']] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                    @if($finding['type'] === 'warning')
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="{{ $findingColors[$finding['type']] }}" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/>
                        </svg>
                    @elseif($finding['type'] === 'success')
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="{{ $findingColors[$finding['type']] }}" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="{{ $findingColors[$finding['type']] }}" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1"/>
                        </svg>
                    @endif
                </span>
                {{ $finding['text'] }}
            </li>
            @endforeach
        </ul>
    </div>

</div>

{{-- Progress Dots --}}
<div style="display:flex;align-items:center;justify-content:center;gap:8px;margin:2rem 0 1rem 0;" id="progressDots">
    <div style="width:10px;height:10px;border-radius:50%;background:var(--blue);transition:all .2s;" class="progress-dot active" data-section="section-1"></div>
    <div style="width:8px;height:8px;border-radius:50%;background:var(--border);transition:all .2s;" class="progress-dot" data-section="section-2"></div>
</div>

<script>
// Highlight active dot based on scroll position
(function() {
    var sections  = document.querySelectorAll('.insight-section');
    var dots      = document.querySelectorAll('.progress-dot');

    function updateDots() {
        var scrollY = window.scrollY + window.innerHeight / 2;
        var active  = 0;
        sections.forEach(function(sec, i) {
            var rect = sec.getBoundingClientRect();
            if (rect.top + window.scrollY <= scrollY) active = i;
        });
        dots.forEach(function(dot, i) {
            if (i === active) {
                dot.style.background = 'var(--blue)';
                dot.style.width      = '10px';
                dot.style.height     = '10px';
            } else {
                dot.style.background = 'var(--border)';
                dot.style.width      = '8px';
                dot.style.height     = '8px';
            }
        });
    }

    window.addEventListener('scroll', updateDots, { passive: true });

    dots.forEach(function(dot) {
        dot.style.cursor = 'pointer';
        dot.addEventListener('click', function() {
            var target = document.getElementById(dot.dataset.section);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
})();
</script>

@endsection
