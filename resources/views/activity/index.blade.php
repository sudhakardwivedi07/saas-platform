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

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">From</label>
            <input type="date" class="f-inp" id="filterFrom" style="min-width:150px;"
                   value="{{ request('from', '') }}">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">To</label>
            <input type="date" class="f-inp" id="filterTo" style="min-width:150px;"
                   value="{{ request('to', '') }}">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:0.75rem;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:0.04em;">Event Type</label>
            <select class="sel" id="filterEventType" style="min-width:190px;">
                <option value="">All Events</option>
                <option value="login" {{ request('event_type') === 'login' ? 'selected' : '' }}>Login</option>
                <option value="logout" {{ request('event_type') === 'logout' ? 'selected' : '' }}>Logout</option>
                <option value="user_invited" {{ request('event_type') === 'user_invited' ? 'selected' : '' }}>User Invited</option>
                <option value="user_removed" {{ request('event_type') === 'user_removed' ? 'selected' : '' }}>User Removed</option>
                <option value="settings_updated" {{ request('event_type') === 'settings_updated' ? 'selected' : '' }}>Settings Updated</option>
                <option value="ticket_created" {{ request('event_type') === 'ticket_created' ? 'selected' : '' }}>Ticket Created</option>
                <option value="company_created" {{ request('event_type') === 'company_created' ? 'selected' : '' }}>Company Created</option>
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
            <table>
                <thead>
                    <tr class="tc-head">
                        <th>Event</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @php
                                    $eventType = $activity->event ?? $activity->description ?? 'unknown';
                                    $icoColor = match(true) {
                                        str_contains($eventType, 'login')   => '#16a34a',
                                        str_contains($eventType, 'delete')  => '#dc2626',
                                        str_contains($eventType, 'create')  => 'var(--blue)',
                                        str_contains($eventType, 'update')  => 'var(--orange)',
                                        default                             => 'var(--text-3)',
                                    };
                                @endphp
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $icoColor }};flex-shrink:0;display:inline-block;"></span>
                                <span style="font-weight:500;color:var(--text-1);font-size:0.875rem;">
                                    {{ ucwords(str_replace(['_','-'], ' ', $eventType)) }}
                                </span>
                            </div>
                            @if(!empty($activity->properties) && is_array($activity->properties))
                                <div style="font-size:0.75rem;color:var(--text-3);margin-top:2px;padding-left:16px;">
                                    {{ Arr::get($activity->properties, 'description', '') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:0.875rem;font-weight:500;color:var(--text-1);">
                                {{ optional($activity->causer)->name ?? 'System' }}
                            </div>
                            <div style="font-size:0.75rem;color:var(--text-2);">
                                {{ optional($activity->causer)->email ?? '' }}
                            </div>
                        </td>
                        <td style="font-size:0.8125rem;color:var(--text-2);font-family:monospace;">
                            {{ $activity->properties['ip'] ?? '—' }}
                        </td>
                        <td style="font-size:0.8125rem;color:var(--text-2);">
                            <div>{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('M j, Y') : '—' }}</div>
                            <div style="color:var(--text-3);">{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('g:i A') : '' }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(method_exists($activities, 'links'))
            <div style="margin-top:1rem;">{{ $activities->links() }}</div>
        @endif
    @endif
</div>

<script>
function applyFilters() {
    var from = document.getElementById('filterFrom').value;
    var to   = document.getElementById('filterTo').value;
    var type = document.getElementById('filterEventType').value;
    var url  = new URL(window.location.href);
    if (from) url.searchParams.set('from', from); else url.searchParams.delete('from');
    if (to)   url.searchParams.set('to', to);     else url.searchParams.delete('to');
    if (type) url.searchParams.set('event_type', type); else url.searchParams.delete('event_type');
    window.location.href = url.toString();
}
function clearFilters() {
    window.location.href = window.location.pathname;
}
</script>

@endsection
