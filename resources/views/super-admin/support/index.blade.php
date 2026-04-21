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
            <option value="in_progress">In Progress</option>
            <option value="on_hold">On Hold</option>
            <option value="sla_breach">SLA Breach</option>
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
                    $t        = is_array($ticket) ? $ticket : $ticket->toArray();
                    $priority = strtolower($t['priority']);
                    $status   = strtolower($t['status']);
                @endphp
                <tr data-priority="{{ $priority }}" data-status="{{ $status }}"
                    data-raw-id="{{ $t['raw_id'] }}"
                    data-ticket-id="{{ $t['id'] }}"
                    data-assigned="{{ $t['assigned_to'] ?? '' }}">
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
                    <td class="status-cell">
                        @if($status === 'open')
                            <span class="badge b-blue" style="font-size:12px">Open</span>
                        @elseif($status === 'in_progress')
                            <span class="badge b-amber" style="font-size:12px">In Progress</span>
                        @elseif($status === 'on_hold')
                            <span class="badge b-gray" style="font-size:12px">On Hold</span>
                        @elseif($status === 'sla_breach')
                            <span style="font-size:12.5px;font-weight:600;color:var(--red)">SLA Breach</span>
                        @elseif($status === 'resolved')
                            <span class="badge b-green" style="font-size:12px">Resolved</span>
                        @else
                            <span class="badge b-gray" style="font-size:12px">{{ ucwords(str_replace('_', ' ', $t['status'])) }}</span>
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:var(--text-2);line-height:1.5">{!! $t['raised_on'] !!}</td>
                    <td class="assignee-cell">
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
                                <a href="{{ route('superadmin.support.show', $t['raw_id']) }}" class="dm-item" style="text-decoration:none">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span style="color:#2563eb;font-weight:600">View Ticket</span>
                                </a>
                                <button class="dm-item update-status-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Update Status
                                </button>
                                <button class="dm-item assign-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    Assign
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

{{-- ── Update Status Modal ──────────────────────────────────────────────── --}}
<div id="update-status-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:28px 28px 24px;width:100%;max-width:440px;position:relative;box-shadow:0 8px 40px rgba(0,0,0,.18)">
        <button id="us-close" style="position:absolute;top:14px;right:14px;background:#f1f5f9;border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;font-size:16px;color:#64748b;display:flex;align-items:center;justify-content:center">✕</button>
        <h3 style="margin:0 0 3px;font-size:17px;font-weight:700;color:var(--text-1)">Update Status</h3>
        <p id="us-subtitle" style="margin:0 0 20px;font-size:13px;color:var(--text-3)"></p>

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px">Current Status</label>
        <input id="us-current" type="text" readonly
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;background:#f8fafc;font-size:13.5px;color:var(--text-3);box-sizing:border-box;margin-bottom:16px">

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px">New Status <span style="color:var(--red)">*</span></label>
        <select id="us-new-status"
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:13.5px;color:var(--text-1);background:#fff;box-sizing:border-box;margin-bottom:16px;cursor:pointer">
            <option value="">Select new status</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="on_hold">On Hold</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
        </select>
        <span id="us-status-err" style="display:none;font-size:12px;color:var(--red);margin-top:-12px;margin-bottom:10px;display:block"></span>

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px">Note (optional)</label>
        <textarea id="us-note" rows="3" placeholder="Add a note about this status change..."
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:13.5px;color:var(--text-1);resize:vertical;box-sizing:border-box;font-family:inherit"></textarea>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
            <button id="us-cancel" class="btn-outline" style="padding:9px 20px;font-size:13.5px">Cancel</button>
            <button id="us-submit" class="btn-primary" style="padding:9px 22px;font-size:13.5px">Update Status</button>
        </div>
    </div>
</div>

{{-- ── Assign Ticket Modal ──────────────────────────────────────────────── --}}
<div id="assign-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:28px 28px 24px;width:100%;max-width:440px;position:relative;box-shadow:0 8px 40px rgba(0,0,0,.18)">
        <button id="as-close" style="position:absolute;top:14px;right:14px;background:#f1f5f9;border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;font-size:16px;color:#64748b;display:flex;align-items:center;justify-content:center">✕</button>
        <h3 style="margin:0 0 3px;font-size:17px;font-weight:700;color:var(--text-1)">Assign Ticket</h3>
        <p id="as-subtitle" style="margin:0 0 20px;font-size:13px;color:var(--text-3)"></p>

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px">Current Assignee</label>
        <input id="as-current" type="text" readonly
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;background:#f8fafc;font-size:13.5px;color:var(--text-3);box-sizing:border-box;margin-bottom:16px">

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px">Assign To <span style="color:var(--red)">*</span></label>
        <select id="as-member"
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:13.5px;color:var(--text-1);background:#fff;box-sizing:border-box;cursor:pointer">
            <option value="">Select team member</option>
            @foreach($adminUsers ?? [] as $admin)
                <option value="{{ $admin->name }}">{{ $admin->name }}</option>
            @endforeach
        </select>
        <span id="as-member-err" style="display:none;font-size:12px;color:var(--red);margin-top:4px;display:block"></span>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px">
            <button id="as-cancel" class="btn-outline" style="padding:9px 20px;font-size:13.5px">Cancel</button>
            <button id="as-submit" class="btn-primary" style="padding:9px 22px;font-size:13.5px">Assign</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /* ── Fixed-position dot-menu (escapes overflow:auto container) ─ */
    document.querySelectorAll('.dot-menu').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var popupId = btn.getAttribute('data-menu');
            var popup   = document.getElementById(popupId);
            var isOpen  = popup.classList.contains('open');

            // Close all open popups first
            document.querySelectorAll('.dm-popup.open').forEach(function (p) {
                p.classList.remove('open');
                p.style.cssText = '';
            });

            if (!isOpen) {
                var rect   = btn.getBoundingClientRect();
                var popupW = 190;
                var left   = rect.right - popupW;
                if (left < 8) left = 8;

                popup.style.position = 'fixed';
                popup.style.top      = (rect.bottom + 4) + 'px';
                popup.style.left     = left + 'px';
                popup.style.right    = 'auto';
                popup.classList.add('open');
            }
        });
    });

    /* ── table filter ────────────────────────────────────────────── */
    var searchInp      = document.getElementById('ticket-search');
    var statusFilter   = document.getElementById('status-filter');
    var priorityFilter = document.getElementById('priority-filter');
    var tbody          = document.querySelector('#tickets-table tbody');

    function filterRows() {
        var q        = searchInp.value.toLowerCase().trim();
        var status   = statusFilter.value.toLowerCase();
        var priority = priorityFilter.value.toLowerCase();
        Array.from(tbody.querySelectorAll('tr[data-priority]')).forEach(function (row) {
            var text    = row.textContent.toLowerCase();
            var rowSt   = row.getAttribute('data-status');
            var rowPr   = row.getAttribute('data-priority');
            var matchQ  = !q        || text.indexOf(q) !== -1;
            var matchSt = !status   || rowSt === status;
            var matchPr = !priority || rowPr === priority;
            row.style.display = (matchQ && matchSt && matchPr) ? '' : 'none';
        });
    }
    searchInp.addEventListener('keyup', filterRows);
    statusFilter.addEventListener('change', filterRows);
    priorityFilter.addEventListener('change', filterRows);

    /* ── helpers ─────────────────────────────────────────────────── */
    var statusLabels = {
        open: 'Open', in_progress: 'In Progress', on_hold: 'On Hold',
        sla_breach: 'SLA Breach', resolved: 'Resolved', closed: 'Closed'
    };

    function statusBadgeHtml(s) {
        var map = {
            open:       '<span class="badge b-blue" style="font-size:12px">Open</span>',
            in_progress:'<span class="badge b-amber" style="font-size:12px">In Progress</span>',
            on_hold:    '<span class="badge b-gray" style="font-size:12px">On Hold</span>',
            sla_breach: '<span style="font-size:12.5px;font-weight:600;color:var(--red)">SLA Breach</span>',
            resolved:   '<span class="badge b-green" style="font-size:12px">Resolved</span>',
            closed:     '<span class="badge b-gray" style="font-size:12px">Closed</span>'
        };
        return map[s] || '<span class="badge b-gray" style="font-size:12px">' + s + '</span>';
    }

    function getRow(btn) { return btn.closest('tr'); }

    /* ── modal open/close helpers ────────────────────────────────── */
    function openModal(el) { el.style.display = 'flex'; }
    function closeModal(el) { el.style.display = 'none'; }

    /* ── Update Status Modal ─────────────────────────────────────── */
    var usModal    = document.getElementById('update-status-modal');
    var usSubtitle = document.getElementById('us-subtitle');
    var usCurrent  = document.getElementById('us-current');
    var usNewSt    = document.getElementById('us-new-status');
    var usNote     = document.getElementById('us-note');
    var usErr      = document.getElementById('us-status-err');
    var usSubmit   = document.getElementById('us-submit');
    var usActiveRow = null;

    document.querySelectorAll('.update-status-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row      = getRow(btn);
            usActiveRow  = row;
            var rawId    = row.getAttribute('data-raw-id');
            var tkId     = row.getAttribute('data-ticket-id');
            var curSt    = row.getAttribute('data-status');
            usSubtitle.textContent = 'Changing status for ' + tkId;
            usCurrent.value        = statusLabels[curSt] || curSt;
            usNewSt.value          = '';
            usNote.value           = '';
            usErr.style.display    = 'none';
            usErr.textContent      = '';
            usSubmit.setAttribute('data-id', rawId);
            openModal(usModal);
        });
    });

    document.getElementById('us-close').addEventListener('click',  function () { closeModal(usModal); });
    document.getElementById('us-cancel').addEventListener('click', function () { closeModal(usModal); });
    usModal.addEventListener('click', function (e) { if (e.target === usModal) closeModal(usModal); });

    usSubmit.addEventListener('click', function () {
        var newSt = usNewSt.value;
        if (!newSt) {
            usErr.textContent   = 'Please select a new status.';
            usErr.style.display = 'block';
            return;
        }
        usErr.style.display = 'none';
        var id    = usSubmit.getAttribute('data-id');
        var url   = '/superadmin/support/' + id + '/status';
        usSubmit.disabled    = true;
        usSubmit.textContent = 'Updating…';

        fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ status: newSt, note: usNote.value.trim() })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                usActiveRow.setAttribute('data-status', data.status);
                usActiveRow.querySelector('.status-cell').innerHTML = statusBadgeHtml(data.status);
                closeModal(usModal);
                filterRows();
            }
        })
        .finally(function () {
            usSubmit.disabled    = false;
            usSubmit.textContent = 'Update Status';
        });
    });

    /* ── Assign Ticket Modal ─────────────────────────────────────── */
    var asModal    = document.getElementById('assign-modal');
    var asSubtitle = document.getElementById('as-subtitle');
    var asCurrent  = document.getElementById('as-current');
    var asMember   = document.getElementById('as-member');
    var asErr      = document.getElementById('as-member-err');
    var asSubmit   = document.getElementById('as-submit');
    var asActiveRow = null;

    document.querySelectorAll('.assign-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row       = getRow(btn);
            asActiveRow   = row;
            var rawId     = row.getAttribute('data-raw-id');
            var tkId      = row.getAttribute('data-ticket-id');
            var curAssign = row.getAttribute('data-assigned') || '';
            asSubtitle.textContent = 'Assigning ' + tkId;
            asCurrent.value        = curAssign || '';
            asCurrent.placeholder  = curAssign ? '' : 'Unassigned';
            asMember.value         = '';
            asErr.style.display    = 'none';
            asErr.textContent      = '';
            asSubmit.setAttribute('data-id', rawId);
            openModal(asModal);
        });
    });

    document.getElementById('as-close').addEventListener('click',  function () { closeModal(asModal); });
    document.getElementById('as-cancel').addEventListener('click', function () { closeModal(asModal); });
    asModal.addEventListener('click', function (e) { if (e.target === asModal) closeModal(asModal); });

    asSubmit.addEventListener('click', function () {
        var member = asMember.value;
        if (!member) {
            asErr.textContent   = 'Please select a team member.';
            asErr.style.display = 'block';
            return;
        }
        asErr.style.display = 'none';
        var id  = asSubmit.getAttribute('data-id');
        var url = '/superadmin/support/' + id + '/assign';
        asSubmit.disabled    = true;
        asSubmit.textContent = 'Assigning…';

        fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ assigned_to: member })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                asActiveRow.setAttribute('data-assigned', data.assigned_to);
                asActiveRow.querySelector('.assignee-cell').innerHTML =
                    '<span style="font-size:13px;color:var(--text-2)">' + data.assigned_to + '</span>';
                closeModal(asModal);
            }
        })
        .finally(function () {
            asSubmit.disabled    = false;
            asSubmit.textContent = 'Assign';
        });
    });
})();
</script>
@endpush
