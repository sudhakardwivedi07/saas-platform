@extends('layouts.app')

@section('page-title', 'Activity Log')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-1);margin:0 0 2px 0;">Activity Log</h1>
        <p style="font-size:0.875rem;color:var(--text-2);margin:0;">A full audit trail of actions taken within your account.</p>
    </div>
</div>

{{-- Info Banner --}}
<div class="info-banner" style="margin-bottom:1.5rem;">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--blue);">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="info-banner-txt">
        Activity log will record all actions taken within your account. Logs are retained for 90 days.
    </span>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="tc-filters" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div class="srch-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="srch-inp" id="activitySearch" placeholder="Search activity..." autocomplete="off">
        </div>

        {{-- Date range filter --}}
        <div class="dr-wrap" id="act-dr">
            <div class="dr-field" onclick="drOpen('act-dr')">
                <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="dr-text" id="act-dr-text">Start Date To End Date</span>
                <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('act-dr')" title="Clear date filter">&#x2715;</button>
            </div>
            <div class="dr-menu" id="act-dr-menu">
                <ul class="dr-list">
                    <li class="dr-opt" onclick="drPickPreset('act-dr','today',this)">Today</li>
                    <li class="dr-opt" onclick="drPickPreset('act-dr','last30',this)">Last 30 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('act-dr','thisMonth',this)">This Month</li>
                    <li class="dr-opt" onclick="drPickPreset('act-dr','lastMonth',this)">Last Month</li>
                    <li class="dr-opt" onclick="drPickPreset('act-dr','last90',this)">Last 90 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('act-dr','last6m',this)">Last 6 Months</li>
                    <li class="dr-opt" onclick="drPickPreset('act-dr','last1y',this)">Last 1 Year</li>
                    <li class="dr-opt" onclick="drPickPreset('act-dr','custom',this)">Custom Range</li>
                </ul>
                <div class="dr-custom-row" id="act-dr-custom">
                    <input type="date" class="dr-date-inp" id="act-dr-from" value="{{ request('from', '') }}">
                    <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                    <input type="date" class="dr-date-inp" id="act-dr-to" value="{{ request('to', '') }}">
                    <button class="dr-apply-btn" onclick="drApplyCustom('act-dr')">Apply</button>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">Event Type</label>
            <select class="sel" id="filterEventType" style="min-width:190px;">
                <option value="">All Events</option>
                @foreach(($eventTypes ?? collect()) as $eventType)
                    <option value="{{ $eventType }}" {{ request('event_type') === $eventType ? 'selected' : '' }}>
                        {{ ucwords(str_replace(['_', '-'], ' ', $eventType)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary btn-sm" onclick="applyFilters()">Apply</button>
        <button class="btn btn-ghost btn-sm" onclick="clearFilters()">Clear</button>
    </div>
</div>

{{-- Activity Table --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <div>
            <div class="card-title">Recent Activity</div>
            <div class="card-sub">Showing the latest events across your account.</div>
        </div>
        <span class="badge b-gray" id="activity-count-badge">{{ $activities->count() }} event{{ $activities->count() !== 1 ? 's' : '' }}</span>
    </div>

    @php $activities = $activities ?? collect(); @endphp

    @if($activities->isEmpty())
        <div style="text-align:center;padding:3.5rem 1rem;">
            <div style="width:64px;height:64px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p style="font-size:0.9375rem;font-weight:600;color:var(--text-1);margin:0 0 6px 0;">Activity logging coming soon</p>
            <p style="font-size:0.8125rem;color:var(--text-2);margin:0;max-width:340px;margin-inline:auto;">
                Once enabled, all actions taken within your account will appear here for auditing and compliance.
            </p>
        </div>
    @else
        <div class="tc">
            <table id="activity-table" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>User</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @php
                                    $eventType = $activity->event_type ?? 'unknown';
                                    $icoClass = match(true) {
                                        str_contains($eventType, 'login')   => 'activity-dot-success',
                                        str_contains($eventType, 'delete')  => 'activity-dot-danger',
                                        str_contains($eventType, 'create')  => 'activity-dot-info',
                                        str_contains($eventType, 'update')  => 'activity-dot-warning',
                                        default                             => 'activity-dot-muted',
                                    };
                                @endphp
                                <span class="{{ $icoClass }}"></span>
                                <span style="font-weight:500;color:var(--text-1);font-size:0.875rem;">
                                    {{ $activity->display_event }}
                                </span>
                            </div>
                            @if(!empty($activity->display_description) && $activity->display_description !== $activity->display_event)
                                <div style="font-size:0.75rem;color:var(--text-3);margin-top:2px;padding-left:16px;">
                                    {{ $activity->display_description }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:0.875rem;font-weight:500;color:var(--text-1);">
                                {{ optional($activity->user)->name ?? 'System' }}
                            </div>
                            <div style="font-size:0.75rem;color:var(--text-2);">
                                {{ optional($activity->user)->email ?? '' }}
                            </div>
                        </td>
                        <td style="font-size:0.8125rem;color:var(--text-2);" data-order="{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->timestamp : 0 }}">
                            <div>{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format(active_date_format()) : '—' }}</div>
                            <div style="color:var(--text-3);">{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('g:i A') : '' }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif
</div>

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
var activityTable;

function applyFilters() {
    var from = document.getElementById('act-dr-from') ? document.getElementById('act-dr-from').value : '';
    var to   = document.getElementById('act-dr-to')   ? document.getElementById('act-dr-to').value   : '';
    var type = document.getElementById('filterEventType').value;
    var url  = new URL(window.location.href);
    if (from) url.searchParams.set('from', from); else url.searchParams.delete('from');
    if (to)   url.searchParams.set('to', to);     else url.searchParams.delete('to');
    if (type) url.searchParams.set('event_type', type); else url.searchParams.delete('event_type');
    window.location.href = url.toString();
}
function clearFilters() {
    drClear('act-dr');
    document.getElementById('filterEventType').value = '';
    window.location.href = window.location.pathname;
}

// Register callback — navigates with URL params (server-side filter)
_drCbs['act-dr'] = function(from, to) {
    if (!from && !to) {
        window.location.href = window.location.pathname +
            (document.getElementById('filterEventType').value ? '?event_type=' + encodeURIComponent(document.getElementById('filterEventType').value) : '');
        return;
    }
    var url = new URL(window.location.href);
    if (from) url.searchParams.set('from', from); else url.searchParams.delete('from');
    if (to)   url.searchParams.set('to', to);     else url.searchParams.delete('to');
    window.location.href = url.toString();
};

// Pre-populate from URL params on page load
(function() {
    var from = '{{ request('from', '') }}';
    var to   = '{{ request('to', '') }}';
    if (from || to) {
        document.addEventListener('DOMContentLoaded', function() {
            var fromEl = document.getElementById('act-dr-from');
            var toEl   = document.getElementById('act-dr-to');
            if (fromEl) fromEl.value = from;
            if (toEl)   toEl.value   = to;
            // Show custom row and mark Custom Range active
            var customRow = document.getElementById('act-dr-custom');
            if (customRow) customRow.style.display = 'flex';
            var customOpt = document.querySelector('#act-dr .dr-opt:last-child');
            if (customOpt) customOpt.classList.add('active');
            drSetLabel('act-dr', from, to);
        });
    }
})();

$(document).ready(function() {
    if (document.getElementById('activity-table')) {
        activityTable = $('#activity-table').DataTable({
            paging: true,
            pageLength: 15,
            lengthChange: false,
            searching: true,
            ordering: true,
            info: true,
            dom: '<"dt-tscroll"t><"dt-footer"ip>',
            order: [[2, 'desc']],
            language: {
                info: 'Showing _START_–_END_ of _TOTAL_ events',
                infoEmpty: 'No activity found',
                paginate: { previous: '‹', next: '›' },
                emptyTable: '<div style="text-align:center;padding:48px 20px;color:#9ca3af;font-size:13px">No activity yet</div>',
            },
            drawCallback: function() {
                var info = this.api().page.info();
                document.getElementById('activity-count-badge').textContent = info.recordsDisplay + ' event' + (info.recordsDisplay !== 1 ? 's' : '');
            }
        });

        document.getElementById('activitySearch').addEventListener('keyup', function() {
            activityTable.search(this.value).draw();
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.activity-dot-success,.activity-dot-danger,.activity-dot-info,.activity-dot-warning,.activity-dot-muted{
    width:8px;
    height:8px;
    border-radius:50%;
    flex-shrink:0;
    display:inline-block;
}
.activity-dot-success{background:#16a34a;}
.activity-dot-danger{background:#dc2626;}
.activity-dot-info{background:var(--blue);}
.activity-dot-warning{background:var(--orange);}
.activity-dot-muted{background:var(--text-3);}
</style>
@endpush

@endsection
