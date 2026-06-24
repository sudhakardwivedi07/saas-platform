@extends('layouts.app')
@section('page-title', $pageTitle ?? 'Preview Insight')

@push('styles')
<style>
/* ── Outer wrapper ─────────────────────────────────────────────────────────── */
.ipv-wrap { background:#f0f4f8; min-height:100vh; padding: 0 0 48px; }

/* ── Top bar ───────────────────────────────────────────────────────────────── */
.ipv-topbar {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:24px; padding-top:4px;
}
.ipv-back {
    display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 18px;
    color:#1a6aa8; font-size:13.5px; font-weight:500; text-decoration:none;
    border:1px solid #1a6aa8; border-radius:7px;
    background:#fff; box-shadow:0 2px 6px rgba(0,0,0,.08); transition:all .2s;
}
.ipv-back:hover { background:#eff6ff; color:#1a6aa8; text-decoration:none; }
.ipv-export-btn {
    display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 20px;
    color:#fff; font-size:13.5px; font-weight:500; text-decoration:none;
    border-radius:7px; background:var(--orange,#f06b25); border:1.5px solid var(--orange,#f06b25);
    transition:all .2s;
}
.ipv-export-btn:hover { background:#d9571c; text-decoration:none; color:#fff; }

/* ── Report card ───────────────────────────────────────────────────────────── */
.ipv-card {
    background:#fff; border-radius:14px; max-width:820px;
    margin:0 auto; box-shadow:0 4px 24px rgba(0,0,0,.10);
    overflow:hidden;
}

/* ── Cover section ─────────────────────────────────────────────────────────── */
.ipv-cover {
    padding:40px 48px 32px;
    border-left:4px solid var(--orange,#f06b25);
}
.ipv-firm-header {
    display:flex; align-items:center; gap:14px; margin-bottom:28px;
}
.ipv-firm-logo {
    width:52px; height:52px; border-radius:10px;
    object-fit:contain; flex-shrink:0;
    border:1px solid #e8ecf0;
}
.ipv-firm-logo-icon {
    width:52px; height:52px; border-radius:10px; flex-shrink:0;
    background:var(--orange,#f06b25); display:flex; align-items:center;
    justify-content:center; color:#fff;
}
.ipv-firm-name  { font-size:16px; font-weight:700; color:#1a202c; line-height:1.3; }
.ipv-firm-sub   { font-size:11px; color:#64748b; margin-top:2px; }

.ipv-analysis-label {
    font-size:9px; font-weight:700; letter-spacing:2px; color:#9ca3af;
    text-transform:uppercase; margin-bottom:6px;
}
.ipv-company-name  { font-size:28px; font-weight:700; color:#1a202c; margin-bottom:4px; line-height:1.2; }
.ipv-orange-bar    { height:3px; width:56px; background:var(--orange,#f06b25); border-radius:2px; margin-bottom:24px; }

.ipv-meta-grid {
    display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;
    padding-top:16px; border-top:1px solid #f1f5f9;
}
.ipv-meta-label { font-size:9px; font-weight:700; letter-spacing:1.2px; color:#9ca3af; text-transform:uppercase; margin-bottom:5px; }
.ipv-meta-value { font-size:13px; font-weight:600; color:#1a202c; margin-bottom:2px; }
.ipv-meta-sub   { font-size:11px; color:#64748b; }

/* ── Table of Contents ─────────────────────────────────────────────────────── */
.ipv-toc { padding:32px 48px; border-top:1px solid #f1f5f9; }
.ipv-section-label {
    font-size:9px; font-weight:700; letter-spacing:2px; color:#9ca3af;
    text-transform:uppercase; margin-bottom:4px;
}
.ipv-toc-heading { font-size:20px; font-weight:700; color:#1a202c; margin-bottom:20px; }
.ipv-toc-item {
    display:flex; align-items:center; gap:10px;
    padding:9px 0; border-bottom:1px solid #f8fafc;
}
.ipv-toc-num  { font-size:11px; font-weight:700; color:#9ca3af; width:24px; flex-shrink:0; }
.ipv-toc-name { flex:1; font-size:13px; color:#374151; font-weight:500; }
.ipv-toc-fill { flex:1; max-width:140px; }
.ipv-toc-dots { border-bottom:1px dotted #cbd5e0; height:1px; display:block; width:100%; }
.ipv-toc-page { font-size:12px; color:#9ca3af; flex-shrink:0; min-width:14px; text-align:right; }

/* ── Section ────────────────────────────────────────────────────────────────── */
.ipv-section { padding:32px 48px; border-top:1px solid #f1f5f9; }
.ipv-section-title { font-size:22px; font-weight:700; color:#1a202c; margin:6px 0 10px; }
.ipv-section-sep   { height:1px; background:#e2e8f0; margin-bottom:22px; }

/* ── Section content typography ─────────────────────────────────────────────── */
.ipv-section-content { font-size:14px; line-height:1.85; color:#374151; }
.ipv-section-content p { margin:0 0 10px; }
.ipv-section-content ul,
.ipv-section-content ol  { padding-left:22px; margin:4px 0 10px; }
.ipv-section-content li  { margin:6px 0; }
.ipv-section-content h3  { font-size:15px; font-weight:700; color:var(--blue,#1a6aa8); margin:14px 0 6px; }
.ipv-section-content h4  { font-size:14px; font-weight:700; color:var(--blue,#1a6aa8); margin:10px 0 4px; }
.ipv-section-content strong { color:#1a202c; font-weight:700; }
.ipv-section-content li strong { color:inherit; font-weight:normal; }
.ipv-section-content p.is-section-heading { margin:14px 0 4px; }
.ipv-section-content p.is-section-heading > strong { display:block; font-size:15px; font-weight:700; color:#1a202c; border-bottom:2px solid #e8eef5; padding-bottom:5px; }
.ipv-section-content .insight-subheading { font-weight:700; font-size:15px; color:#1a202c; margin:14px 0 4px; padding-bottom:5px; border-bottom:2px solid #e8eef5; }

.ipv-section-content table {
    width:100%; border-collapse:collapse; margin:10px 0;
    font-size:13px; border-radius:8px; overflow:hidden;
    box-shadow:0 1px 3px rgba(0,0,0,.07);
}
.ipv-section-content table th {
    border:1px solid #e2e8f0; padding:10px 12px;
    font-weight:600; color:#2d3748; background:#f7fafc;
    text-align:left;
}
.ipv-section-content table td {
    border:1px solid #e2e8f0; padding:10px 12px; color:#4a5568;
}
.ipv-section-content table tbody tr:nth-child(even) { background:#f9fafb; }

/* ── Footer stamp ─────────────────────────────────────────────────────────── */
.ipv-footer {
    padding:16px 48px; border-top:1px solid #f1f5f9;
    display:flex; justify-content:space-between; align-items:center;
}
.ipv-footer-brand { font-size:11px; color:#9ca3af; font-weight:500; }
.ipv-footer-conf  { font-size:11px; color:#9ca3af; }

/* ── Responsive ── */
@media(max-width:860px) {
    .ipv-cover, .ipv-toc, .ipv-section { padding-left:24px; padding-right:24px; }
    .ipv-meta-grid { grid-template-columns:1fr 1fr; }
    .ipv-footer { padding-left:24px; padding-right:24px; }
    .ipv-topbar { flex-wrap:wrap; gap:10px; }
    .ipv-export-btn { flex:1; justify-content:center; }
    .ipv-company-name { font-size:22px; }
}
@media(max-width:600px) {
    .ipv-cover { padding:24px 16px 20px; }
    .ipv-toc, .ipv-section { padding:24px 16px; }
    .ipv-meta-grid { grid-template-columns:1fr; gap:14px; }
    .ipv-footer { flex-direction:column; gap:6px; align-items:flex-start; padding:14px 16px; }
    .ipv-topbar { flex-direction:column; align-items:stretch; }
    .ipv-back, .ipv-export-btn { justify-content:center; }
    .ipv-company-name { font-size:20px; }
    .ipv-section-title { font-size:18px; }
    .ipv-section-content table { min-width:420px; }
    .ipv-section-content { overflow-x:auto; }
}
</style>
@endpush

@section('content')
<div class="ipv-wrap">

    {{-- Top bar --}}
    <div class="ipv-topbar">
        <a href="{{ route('insights.view', $report->encrypted_id) }}" class="ipv-back">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Insight
        </a>
        <a href="{{ route('insights.export-pdf', $report->id) }}" class="ipv-export-btn">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export to PDF
        </a>
    </div>

    <div class="ipv-card">

        {{-- ── Cover ─────────────────────────────────────── --}}
        <div class="ipv-cover">

            {{-- Firm header --}}
            <div class="ipv-firm-header">
                @if($branding?->logo)
                    <img src="{{ asset('storage/' . $branding->logo) }}" alt="{{ $companyName }}" class="ipv-firm-logo">
                @else
                    <div class="ipv-firm-logo-icon">
                        <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                @endif
                <div>
                    <div class="ipv-firm-name">{{ $companyName }}</div>
                    @if($appName && $appName !== $companyName)
                        <div class="ipv-firm-sub">{{ $appName }}</div>
                    @endif
                </div>
            </div>

            <div class="ipv-analysis-label">INSIGHTS ANALYSIS</div>
            <div class="ipv-company-name">{{ $report->company?->company_name ?? '' }}</div>
            <div class="ipv-orange-bar"></div>

            {{-- Metadata --}}
            <div class="ipv-meta-grid">
                <div>
                    <div class="ipv-meta-label">PREPARED FOR</div>
                    <div class="ipv-meta-value">{{ $report->user?->name ?? $report->company?->company_name ?? '—' }}</div>
                    <div class="ipv-meta-sub">{{ $report->company?->company_name ?? '' }}</div>
                </div>
                <div>
                    <div class="ipv-meta-label">PREPARED BY</div>
                    <div class="ipv-meta-value">{{ $companyName }}</div>
                </div>
                <div>
                    <div class="ipv-meta-label">DATE</div>
                    <div class="ipv-meta-value">{{ $report->created_at->format('F d, Y') }}</div>
                </div>
            </div>
        </div>

        {{-- ── Table of Contents ────────────────────────── --}}
        @if(count($sections) > 0)
        <div class="ipv-toc">
            <div class="ipv-section-label">CONTENTS</div>
            <div class="ipv-toc-heading">Table of Contents</div>
            @foreach($sections as $i => $section)
            <div class="ipv-toc-item">
                <span class="ipv-toc-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="ipv-toc-name">{{ $section['title'] }}</span>
                <span class="ipv-toc-fill"><span class="ipv-toc-dots"></span></span>
                <span class="ipv-toc-page">{{ $i + 3 }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── Sections ──────────────────────────────────── --}}
        @foreach($sections as $i => $section)
        <div class="ipv-section">
            <div class="ipv-section-title">{{ $section['title'] }}</div>
            <div class="ipv-section-sep"></div>
            <div class="ipv-section-content">{!! $section['formatted_content'] !!}</div>
        </div>
        @endforeach

        {{-- ── Footer ────────────────────────────────────── --}}
        <div class="ipv-footer">
            <span class="ipv-footer-brand">{{ $companyName }} Insights Platform</span>
            <span class="ipv-footer-conf">Confidential</span>
        </div>

    </div>
</div>
@endsection
