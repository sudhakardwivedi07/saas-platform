@extends('layouts.superadmin.app')
@section('title','Support')
@section('page-title','Support')

@section('content')
<div class="ph">
    <div>
        <h1>Support</h1>
        <p>Manage escalated tickets from partner firms</p>
    </div>
</div>

<div class="info-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="info-banner-txt">Escalated support tickets from partner firms. Firm-level tickets are managed within each firm's own portal.</div>
</div>

<div class="tc">
    {{-- Filters row --}}
    <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--border)">
        <div class="srch-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="srch-inp" id="ticket-search" placeholder="Search tickets..." autocomplete="off">
        </div>
        <select class="sel" id="status-filter" autocomplete="off">
            <option value="">All Status</option>
            <option value="open">Open</option>
            <option value="in progress">In Progress</option>
            <option value="sla breach">SLA Breach</option>
            <option value="resolved">Resolved</option>
        </select>
        <select class="sel" id="priority-filter" autocomplete="off">
            <option value="">All Priority</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
    </div>

    <div style="overflow-x:auto">
        <table id="tickets-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th style="width:110px">TICKET ID</th>
                    <th>FIRM</th>
                    <th>SUBJECT</th>
                    <th style="width:110px">PRIORITY</th>
                    <th style="width:130px">STATUS</th>
                    <th style="width:150px">RAISED ON</th>
                    <th style="width:160px">ASSIGNED TO</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody>
                @php $tickets = $tickets ?? collect([]); @endphp

                @forelse($tickets as $ticket)
                @php
                    $t = is_array($ticket) ? $ticket : $ticket->toArray();
                    $priority = strtolower($t['priority']);
                    $status   = strtolower($t['status']);
                @endphp
                <tr data-priority="{{ $priority }}" data-status="{{ $status }}">
                    <td>
                        <span style="font-weight:600;color:var(--orange);font-size:13px">{{ $t['id'] }}</span>
                    </td>
                    <td>
                        <span style="font-weight:600;font-size:13px;color:var(--text-1)">{{ $t['firm'] }}</span>
                    </td>
                    <td style="font-size:13px;color:var(--text-2)">{{ $t['subject'] }}</td>
                    <td>
                        @if($priority === 'high')
                            <span class="badge b-red" style="font-size:12px">High</span>
                        @elseif($priority === 'medium')
                            <span class="badge b-amber" style="font-size:12px">Medium</span>
                        @else
                            <span class="badge b-gray" style="font-size:12px">Low</span>
                        @endif
                    </td>
                    <td>
                        @if($status === 'open')
                            <span class="badge b-blue" style="font-size:12px">Open</span>
                        @elseif($status === 'in progress')
                            <span class="badge b-amber" style="font-size:12px">In Progress</span>
                        @elseif($status === 'sla breach')
                            <span style="font-size:12.5px;font-weight:600;color:var(--red)">SLA Breach</span>
                        @elseif($status === 'resolved')
                            <span class="badge b-green" style="font-size:12px">Resolved</span>
                        @else
                            <span class="badge b-gray" style="font-size:12px">{{ ucfirst($t['status']) }}</span>
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:var(--text-2);line-height:1.5">{!! $t['raised_on'] !!}</td>
                    <td>
                        @if(!empty($t['assigned_to']))
                            <span style="font-size:13px;color:var(--text-2)">{{ $t['assigned_to'] }}</span>
                        @else
                            <span style="font-size:13px;font-weight:600;color:var(--red)">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        <div class="dm-wrap">
                            <button class="dot-menu" data-menu="dm-tk-{{ $loop->index }}" title="Actions">•••</button>
                            <div class="dm-popup" id="dm-tk-{{ $loop->index }}">
                                <button class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Ticket
                                </button>
                                <button class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Assign
                                </button>
                                <div class="dm-divider"></div>
                                <button class="dm-item dm-danger">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                    Close Ticket
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:56px 20px">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        <div style="font-size:14px;font-weight:500;color:var(--text-2)">No support tickets</div>
                        <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Escalated tickets from partner firms will appear here.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var searchInp      = document.getElementById('ticket-search');
    var statusFilter   = document.getElementById('status-filter');
    var priorityFilter = document.getElementById('priority-filter');
    var tbody          = document.querySelector('#tickets-table tbody');

    function filterRows() {
        var q        = searchInp.value.toLowerCase().trim();
        var status   = statusFilter.value.toLowerCase();
        var priority = priorityFilter.value.toLowerCase();

        Array.from(tbody.querySelectorAll('tr[data-priority]')).forEach(function(row) {
            var text     = row.textContent.toLowerCase();
            var rowSt    = row.getAttribute('data-status');
            var rowPr    = row.getAttribute('data-priority');

            var matchQ  = !q      || text.indexOf(q) !== -1;
            var matchSt = !status || rowSt === status;
            var matchPr = !priority || rowPr === priority;

            row.style.display = (matchQ && matchSt && matchPr) ? '' : 'none';
        });
    }

    searchInp.addEventListener('keyup', filterRows);
    statusFilter.addEventListener('change', filterRows);
    priorityFilter.addEventListener('change', filterRows);
})();
</script>
@endpush
