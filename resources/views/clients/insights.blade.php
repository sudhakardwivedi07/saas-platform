@extends('layouts.app')

@section('page-title', 'Insights')

@section('content')

{{-- Back Button --}}
<a href="{{ route('clients.show', $client->id) }}" class="back-btn">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to {{ $client->name }}
</a>

{{-- Page Header --}}
<div class="ph" style="margin-top:0.5rem;">
    <div class="ph-left">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-1);margin:0 0 2px 0;">
            Insights &mdash; {{ $client->name }}
        </h1>
        <p style="font-size:0.875rem;color:var(--text-2);margin:0;">AI-generated financial insights and reports for this client.</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-primary" disabled
                style="opacity:0.5;cursor:not-allowed;"
                title="Insight generation coming soon">
            + Generate Insight
        </button>
    </div>
</div>

{{-- Search --}}
<div class="card" style="margin-bottom:1.25rem;padding:0.75rem 1rem;">
    <div class="srch-wrap" style="max-width:380px;">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="2"
             style="position:absolute;left:10px;top:50%;transform:translateY(-50%);">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" class="srch-inp" placeholder="Search insights..." id="insightSearch"
               oninput="filterInsights(this.value)">
    </div>
</div>

{{-- Insights Table --}}
<div class="card">
    <div style="margin-bottom:1.25rem;">
        <div class="card-title">Generated Insights</div>
        <div class="card-sub">Financial reports and AI analysis for {{ $client->name }}.</div>
    </div>

    @php $insights = $insights ?? collect(); @endphp

    @if($insights->isEmpty())
        <div style="text-align:center;padding:3.5rem 1rem;">
            <div style="width:64px;height:64px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p style="font-size:0.9375rem;font-weight:600;color:var(--text-1);margin:0 0 6px 0;">No insights generated yet.</p>
            <p style="font-size:0.8125rem;color:var(--text-2);margin:0;max-width:360px;margin-inline:auto;">
                Once QuickBooks data is connected, you can generate AI-powered financial insights for {{ $client->name }}.
            </p>
        </div>
    @else
        <div class="tc">
            <table id="insightsTable">
                <thead>
                    <tr class="tc-head">
                        <th>Title</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="width:120px;text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($insights as $insight)
                    <tr class="insight-row">
                        <td>
                            <a href="{{ route('clients.insights.show', [$client->id, $insight->id]) }}"
                               style="font-weight:600;color:var(--blue);text-decoration:none;font-size:0.875rem;">
                                {{ $insight->title }}
                            </a>
                            @if($insight->summary ?? false)
                                <div style="font-size:0.75rem;color:var(--text-2);margin-top:2px;max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $insight->summary }}
                                </div>
                            @endif
                        </td>
                        <td style="font-size:0.875rem;color:var(--text-1);">{{ $client->name }}</td>
                        <td style="font-size:0.8125rem;color:var(--text-2);">
                            {{ $insight->created_at ? \Carbon\Carbon::parse($insight->created_at)->format('M j, Y') : '—' }}
                        </td>
                        <td>
                            <span class="badge b-green">Ready</span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">
                                <a href="{{ route('clients.insights.download', [$client->id, $insight->id]) }}"
                                   class="btn btn-xs btn-secondary"
                                   style="text-decoration:none;"
                                   title="Download PDF">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download
                                </a>
                                <button class="btn btn-xs"
                                        style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;"
                                        onclick="deleteInsight({{ $insight->id }}, '{{ addslashes($insight->title) }}')"
                                        title="Delete insight">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
function filterInsights(query) {
    var q = query.toLowerCase();
    document.querySelectorAll('#insightsTable .insight-row').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function deleteInsight(id, title) {
    if (!confirm('Delete insight "' + title + '"? This cannot be undone.')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/clients/{{ $client->id }}/insights/' + id;
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
                   + '<input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(form);
    form.submit();
}
</script>

@endsection
