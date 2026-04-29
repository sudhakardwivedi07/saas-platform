@extends('layouts.superadmin.app')
@section('title', 'Stripe Webhook Logs')
@section('page-title', 'Webhook Logs')

@push('styles')
<style>
.log-toolbar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:14px 18px;border-bottom:1px solid var(--border);background:#fafbfc }
.log-entry { border-bottom:1px solid var(--border);padding:0 }
.log-entry:last-child { border-bottom:none }
.log-row { display:flex;align-items:flex-start;gap:12px;padding:10px 18px;cursor:pointer;transition:background .1s }
.log-row:hover { background:var(--bg) }
.log-level { flex-shrink:0;width:68px;padding:2px 0;border-radius:5px;font-size:11px;font-weight:700;text-align:center;text-transform:uppercase;letter-spacing:.06em }
.lv-info    { background:#dbeafe;color:#1d4ed8 }
.lv-warning { background:#fef3c7;color:#b45309 }
.lv-error   { background:#fee2e2;color:#b91c1c }
.lv-critical{ background:#f3e8ff;color:#7c3aed }
.lv-debug   { background:#f1f5f9;color:#64748b }
.log-time { flex-shrink:0;font-size:11.5px;color:var(--text-3);font-family:monospace;white-space:nowrap;margin-top:2px;width:170px }
.log-msg  { flex:1;font-size:13px;color:var(--text-1);word-break:break-word;margin-top:1px }
.log-ctx  { display:none;padding:10px 18px 14px 260px;background:#f8fafc;border-top:1px dashed var(--border) }
.log-ctx pre { font-size:12px;color:var(--text-2);margin:0;white-space:pre-wrap;word-break:break-word;font-family:monospace;line-height:1.6 }
.log-entry.open .log-ctx { display:block }
.log-entry.open .log-row { background:var(--bg) }
.badge-count { display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;border-radius:10px;font-size:11px;font-weight:700;padding:0 5px }
.srch-wrap { position:relative;display:flex;align-items:center }
.srch-wrap svg { position:absolute;left:10px;width:14px;height:14px;stroke:var(--text-3) }
.srch-wrap input { padding:7px 12px 7px 32px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;outline:none;color:var(--text-1);background:#fff;min-width:220px }
.srch-wrap input:focus { border-color:var(--blue) }
.empty-state { text-align:center;padding:60px 24px;color:var(--text-3) }
</style>
@endpush

@section('content')
<div class="ph">
    <div>
        <h1>Stripe Webhook Logs</h1>
        <p>Real-time log of every Stripe event received at <code style="font-size:12px;background:var(--bg);padding:2px 7px;border-radius:5px;border:1px solid var(--border)">/webhooks/stripe</code></p>
    </div>
    <div class="ph-actions" style="gap:8px">
        <a href="{{ route('superadmin.webhook-logs.download') }}" class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.11"/></svg>
            Download Log
        </a>
        <form method="POST" action="{{ route('superadmin.webhook-logs.clear') }}" onsubmit="return confirm('Clear all Stripe logs?')">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);border-color:var(--red)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                Clear Logs
            </button>
        </form>
    </div>
</div>

{{-- Stats row --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    @php
        $total    = count($entries);
        $errors   = collect($entries)->whereIn('level', ['error','critical'])->count();
        $warnings = collect($entries)->where('level','warning')->count();
        $infos    = collect($entries)->where('level','info')->count();
    @endphp
    <div class="acc-card" style="margin:0;padding:16px 18px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);margin-bottom:6px">Total Entries</div>
        <div style="font-size:24px;font-weight:700;color:var(--text-1)">{{ $total }}</div>
        @if($logExists)
        <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">{{ $logSizeKb }} KB log file</div>
        @endif
    </div>
    <div class="acc-card" style="margin:0;padding:16px 18px;border-left:3px solid #ef4444">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#b91c1c;margin-bottom:6px">Errors</div>
        <div style="font-size:24px;font-weight:700;color:#b91c1c">{{ $errors }}</div>
        <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">error + critical</div>
    </div>
    <div class="acc-card" style="margin:0;padding:16px 18px;border-left:3px solid #f59e0b">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#b45309;margin-bottom:6px">Warnings</div>
        <div style="font-size:24px;font-weight:700;color:#b45309">{{ $warnings }}</div>
        <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">payment_failed etc.</div>
    </div>
    <div class="acc-card" style="margin:0;padding:16px 18px;border-left:3px solid #3b82f6">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#1d4ed8;margin-bottom:6px">Info</div>
        <div style="font-size:24px;font-weight:700;color:#1d4ed8">{{ $infos }}</div>
        <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">successful events</div>
    </div>
</div>

<div class="tc" style="padding:0">

    {{-- Toolbar --}}
    <div class="log-toolbar">
        {{-- Level filter --}}
        <div style="display:flex;gap:6px">
            @foreach([
                ['all',      'All',      'var(--text-2)', 'var(--border)',  $total],
                ['info',     'Info',     '#1d4ed8',       '#dbeafe',       $infos],
                ['warning',  'Warning',  '#b45309',       '#fef3c7',       $warnings],
                ['error',    'Error',    '#b91c1c',       '#fee2e2',       $errors],
            ] as [$val, $label, $color, $bg, $cnt])
            <a href="{{ route('superadmin.webhook-logs.index', array_merge(request()->query(), ['filter' => $val])) }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:7px;font-size:12.5px;font-weight:600;border:1.5px solid {{ $filter === $val ? $color : 'var(--border)' }};background:{{ $filter === $val ? $bg : '#fff' }};color:{{ $filter === $val ? $color : 'var(--text-2)' }};text-decoration:none;transition:all .12s">
                {{ $label }}
                <span style="background:{{ $filter === $val ? $color : 'var(--border)' }};color:{{ $filter === $val ? '#fff' : 'var(--text-2)' }};border-radius:10px;font-size:10.5px;font-weight:700;padding:0 6px;line-height:18px">{{ $cnt }}</span>
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('superadmin.webhook-logs.index') }}" style="display:flex;gap:8px;margin-left:auto">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search logs…" autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            @if($search)
            <a href="{{ route('superadmin.webhook-logs.index', ['filter' => $filter]) }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </form>

        {{-- Refresh --}}
        <a href="{{ route('superadmin.webhook-logs.index', request()->query()) }}" class="btn btn-ghost btn-sm" title="Refresh">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
        </a>
    </div>

    {{-- Webhook URL info --}}
    @if(!$logExists || count($entries) === 0)
    <div style="padding:14px 18px;background:#fffbeb;border-bottom:1px solid #fef3c7">
        <div style="font-size:12.5px;color:#92400e">
            <strong>No logs yet.</strong>
            Configure Stripe to send webhooks to one of these endpoints, then trigger a test event:
            <div style="margin-top:8px;display:flex;flex-direction:column;gap:4px">
                <code style="background:#fff;border:1px solid #fde68a;padding:4px 10px;border-radius:5px;font-size:12px;display:inline-block">Local:  http:-127.0.0.1:8000-webhooks-.stripe</code>
                <code style="background:#fff;border:1px solid #fde68a;padding:4px 10px;border-radius:5px;font-size:12px;display:inline-block">Server: https-dev-box.occams.ai-webhooks-stripe</code>
            </div>
        </div>
    </div>
    @endif

    {{-- Log entries --}}
    <div id="log-list">
        @forelse($entries as $i => $entry)
        @php
            $lvl = $entry['level'];
            $lvlClass = match($lvl) {
                'error', 'critical' => 'lv-error',
                'warning'           => 'lv-warning',
                'info'              => 'lv-info',
                default             => 'lv-debug',
            };
            $hasCtx = !empty(trim($entry['context']));
            // Prettify JSON context
            $ctxDisplay = '';
            if ($hasCtx) {
                $ctxStr = trim($entry['context']);
                $decoded = json_decode($ctxStr, true);
                $ctxDisplay = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $ctxStr;
            }
        @endphp
        <div class="log-entry" id="le-{{ $i }}">
            <div class="log-row" onclick="toggleEntry({{ $i }}, {{ $hasCtx ? 'true' : 'false' }})">
                <span class="log-level {{ $lvlClass }}">{{ $lvl }}</span>
                <span class="log-time">{{ $entry['datetime'] }}</span>
                <span class="log-msg">
                    @if(str_contains($entry['message'], '✓'))
                        <span style="color:var(--green)">{{ $entry['message'] }}</span>
                    @elseif(str_contains($entry['message'], '✗'))
                        <span style="color:var(--red)">{{ $entry['message'] }}</span>
                    @elseif(str_contains($entry['message'], '⬇') || str_contains($entry['message'], '▶'))
                        <span style="color:var(--blue)">{{ $entry['message'] }}</span>
                    @else
                        {{ $entry['message'] }}
                    @endif
                    @if($hasCtx)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" style="margin-left:5px;vertical-align:middle;color:var(--text-3)" id="arrow-{{ $i }}"><polyline points="6 9 12 15 18 9"/></svg>
                    @endif
                </span>
            </div>
            @if($hasCtx)
            <div class="log-ctx">
                <pre>{{ $ctxDisplay }}</pre>
            </div>
            @endif
        </div>
        @empty
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="44" height="44" style="display:block;margin:0 auto 14px;stroke:var(--text-3)"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <div style="font-size:14.5px;font-weight:600;margin-bottom:6px">No log entries found</div>
            <div style="font-size:13px">
                @if($search || $filter !== 'all')
                    No entries match your current filters. <a href="{{ route('superadmin.webhook-logs.index') }}" style="color:var(--blue)">Clear filters</a>
                @else
                    No Stripe webhook events have been logged yet. Trigger a test event from the Stripe dashboard.
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if(count($entries) === 100)
    <div style="padding:12px 18px;text-align:center;font-size:12.5px;color:var(--text-3);border-top:1px solid var(--border);background:#fafbfc">
        Showing the latest 100 entries. <a href="{{ route('superadmin.webhook-logs.download') }}" style="color:var(--blue)">Download full log</a> to see all entries.
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function toggleEntry(i, hasCtx) {
    if (!hasCtx) return;
    const entry = document.getElementById('le-' + i);
    const arrow = document.getElementById('arrow-' + i);
    entry.classList.toggle('open');
    if (arrow) arrow.style.transform = entry.classList.contains('open') ? 'rotate(180deg)' : '';
}

// Auto-refresh every 15 seconds if no filter/search active
@if(!$search && $filter === 'all')
setTimeout(function() {
    window.location.reload();
}, 15000);
@endif
</script>
@endpush
