@extends('layouts.superadmin.app')
@section('title','Support')
@section('page-title','Support')

@push('styles')
<link rel="stylesheet" href="{{ asset(config('assets.datatables_bs5_css')) }}">
<style>
div.dataTables_wrapper div.dataTables_info { font-size:12.5px;color:var(--text-3);padding-top:14px }
div.dataTables_wrapper div.dataTables_paginate { padding-top:10px }
div.dataTables_wrapper div.dataTables_paginate .paginate_button {
    border-radius:7px !important;font-size:13px;border:1.5px solid var(--border) !important;
    background:#fff !important;color:var(--text-1) !important;padding:5px 11px !important;margin:0 2px;line-height:1.4
}
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover {
    background:#F06B21 !important;color:#fff !important;border-color:#F06B21 !important
}
div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover:not(.current) {
    background:var(--bg) !important;border-color:var(--text-3) !important
}
div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled,
div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled:hover { color:var(--text-3) !important }
table.dataTable thead th { cursor:pointer }
.dt-footer { padding:10px 18px 14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px }
.dt-footer .dataTables_info,.dt-footer .dataTables_paginate { padding-top:0 !important }
/* ── Date range picker ── */
.dr-wrap{position:relative;display:inline-block}
.dr-field{display:flex;align-items:center;gap:8px;height:34px;padding:0 12px;border:1.5px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;user-select:none;font-size:13px;min-width:220px;transition:border-color .14s}
.dr-field:hover,.dr-field.open{border-color:var(--orange)}
.dr-field .dr-cal-ico{flex-shrink:0;width:15px;height:15px;stroke:var(--text-3);transition:stroke .14s}
.dr-field.has-val .dr-cal-ico,.dr-field.open .dr-cal-ico{stroke:var(--orange)}
.dr-text{flex:1;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px}
.dr-text.has-val{color:var(--text-1)}
.dr-clear-btn{display:none;align-items:center;justify-content:center;width:17px;height:17px;border-radius:50%;background:var(--text-3);color:#fff;font-size:11px;line-height:1;border:none;cursor:pointer;padding:0;flex-shrink:0;transition:background .12s;font-family:inherit}
.dr-clear-btn:hover{background:var(--red,#dc2626)}
.dr-field.has-val .dr-clear-btn{display:flex}
.dr-menu{position:absolute;top:calc(100% + 4px);left:0;z-index:1050;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 28px rgba(0,0,0,.13);min-width:220px;display:none;overflow:hidden}
.dr-menu.open{display:block}
.dr-list{list-style:none;padding:6px 0;margin:0}
.dr-opt{padding:9px 16px;font-size:13px;color:var(--text-1);cursor:pointer;transition:background .1s;white-space:nowrap}
.dr-opt:hover{background:var(--bg)}
.dr-opt.active{background:#1a3f6f;color:#fff}
.dr-opt.active:hover{background:#183766}
.dr-custom-row{display:none;align-items:center;gap:6px;padding:10px 12px;border-top:1px solid var(--border);flex-wrap:wrap}
.dr-date-inp{flex:1;min-width:100px;height:32px;padding:0 8px;border:1px solid var(--border);border-radius:7px;font-size:12.5px;font-family:inherit;color:var(--text-1);outline:none;box-sizing:border-box}
.dr-date-inp:focus{border-color:var(--orange)}
.dr-apply-btn{padding:5px 13px;border:none;border-radius:7px;background:var(--orange);color:#fff;font-size:12.5px;font-weight:600;font-family:inherit;cursor:pointer;flex-shrink:0}
.dr-apply-btn:hover{background:#d95a1a}
.dr-menu.open.dr-cal-mode{display:flex}
.dr-menu.dr-cal-mode{flex-direction:row;min-width:660px;overflow:visible}
.dr-menu.dr-cal-mode .dr-list{border-right:1px solid var(--border);min-width:170px;flex-shrink:0;padding-top:4px}
.dr-cal-panel{display:flex;flex-direction:column;padding:12px 16px;flex:1;min-width:490px}
.dr-cal-months{display:flex;gap:20px}
.dr-month-wrap{flex:1;min-width:0}
.dr-month-hd{display:flex;align-items:center;gap:3px;margin-bottom:8px}
.dr-nav-btn{width:26px;height:26px;border:1px solid var(--border);border-radius:6px;background:#fff;cursor:pointer;font-size:15px;color:var(--text-2);flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:0;line-height:1;transition:background .1s}
.dr-nav-btn:hover{background:var(--bg)}
.dr-mon-sel{height:28px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-family:inherit;color:var(--text-1);background:#fff;padding:0 4px;cursor:pointer;outline:none;flex:1;min-width:0}
.dr-yr-sel{height:28px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-family:inherit;color:var(--text-1);background:#fff;padding:0 4px;cursor:pointer;outline:none;width:60px;flex-shrink:0}
.dr-mon-sel:focus,.dr-yr-sel:focus{border-color:var(--orange)}
.dr-day-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:1px}
.dr-day-hdr{text-align:center;font-size:10.5px;font-weight:700;color:var(--text-3);padding:5px 1px}
.dr-day-cell{text-align:center;font-size:12.5px;padding:6px 1px;cursor:pointer;border-radius:50%;color:var(--text-1);user-select:none;min-width:0}
.dr-day-cell:hover:not(.dr-day-out):not(.dr-day-sel-s):not(.dr-day-sel-e){background:rgba(0,0,0,.06);border-radius:50%}
.dr-day-out{color:var(--text-3);cursor:default;opacity:.38}
.dr-day-today{font-weight:700}
.dr-day-sel-s,.dr-day-sel-e{background:#2563eb!important;color:#fff!important;border-radius:50%!important}
.dr-day-in-range{background:rgba(37,99,235,.13);border-radius:0!important}
.dr-day-hover-preview{background:rgba(37,99,235,.22)!important;border-radius:50%!important}
.dr-cal-footer{display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:10px;border-top:1px solid var(--border)}
.dr-cal-rtxt{font-size:12.5px;color:var(--text-2);flex:1;margin-right:10px}
.dr-cal-btn-cancel{padding:6px 18px;border:none;border-radius:7px;background:#dc2626;color:#fff;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .12s}
.dr-cal-btn-cancel:hover{background:#b91c1c}
.dr-cal-btn-apply{padding:6px 18px;border:none;border-radius:7px;background:#2563eb;color:#fff;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .12s}
.dr-cal-btn-apply:hover{background:#1d4ed8}
</style>
@endpush

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
    {{-- Filters --}}
    <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--border);flex-wrap:wrap">
        <div class="srch-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="srch-inp" id="ticket-search" placeholder="Search tickets..." autocomplete="off">
        </div>
        <select class="sel" id="status-filter" autocomplete="off">
            <option value="">Select Status</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="on_hold">On Hold</option>
            <option value="sla_breach">SLA Breach</option>
            <option value="resolved">Resolved</option>
        </select>
        <select class="sel" id="priority-filter" autocomplete="off">
            <option value="">Select Priority</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
        <div class="dr-wrap" id="ticket-dr">
            <div class="dr-field" onclick="drOpen('ticket-dr')">
                <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="dr-text" id="ticket-dr-text">Start Date To End Date</span>
                <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('ticket-dr')" title="Clear date filter">&#x2715;</button>
            </div>
            <div class="dr-menu" id="ticket-dr-menu">
                <ul class="dr-list">
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','today',this)">Today</li>
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','last30',this)">Last 30 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','thisMonth',this)">This Month</li>
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','lastMonth',this)">Last Month</li>
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','last90',this)">Last 90 Days</li>
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','last6m',this)">Last 6 Months</li>
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','last1y',this)">Last 1 Year</li>
                    <li class="dr-opt" onclick="drPickPreset('ticket-dr','custom',this)">Custom Range</li>
                </ul>
                <div class="dr-custom-row" id="ticket-dr-custom">
                    <input type="date" class="dr-date-inp" id="ticket-dr-from">
                    <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                    <input type="date" class="dr-date-inp" id="ticket-dr-to">
                    <button class="dr-apply-btn" onclick="drApplyCustom('ticket-dr')">Apply</button>
                </div>
            </div>
        </div>
        <button class="btn btn-danger" id="reset-filters" style="white-space:nowrap">Reset</button>
        <span id="ticket-count-badge" class="badge b-gray" style="margin-left:auto">
            {{ count($tickets ?? []) }} tickets
        </span>
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
                    <th style="width:160px">RAISED ON</th>
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
                            <span class="badge b-red"   style="font-size:12px">High</span>
                        @elseif($priority === 'medium')
                            <span class="badge b-amber" style="font-size:12px">Medium</span>
                        @else
                            <span class="badge b-gray"  style="font-size:12px">Low</span>
                        @endif
                    </td>
                    <td class="status-cell">
                        @if($status === 'open')
                            <span class="badge b-blue"  style="font-size:12px">Open</span>
                        @elseif($status === 'in_progress')
                            <span class="badge b-amber" style="font-size:12px">In Progress</span>
                        @elseif($status === 'on_hold')
                            <span class="badge b-gray"  style="font-size:12px">On Hold</span>
                        @elseif($status === 'sla_breach')
                            <span style="font-size:12.5px;font-weight:600;color:var(--red)">SLA Breach</span>
                        @elseif($status === 'resolved')
                            <span class="badge b-green" style="font-size:12px">Resolved</span>
                        @else
                            <span class="badge b-gray"  style="font-size:12px">{{ ucwords(str_replace('_',' ',$t['status'])) }}</span>
                        @endif
                    </td>
                    {{-- data-sort = unix timestamp so DataTables sorts numerically --}}
                    <td data-sort="{{ $t['date_sort'] }}" data-date="{{ $t['date_iso'] }}"
                        style="font-size:12.5px;color:var(--text-2);line-height:1.5">
                        {{ $t['raised_on'] }}<br>
                        <span style="color:var(--text-3)">{{ $t['raised_time'] }}</span>
                    </td>
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
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
/* DR globals — must be defined before the IIFE that sets _drCbs['ticket-dr'] */
var _drCbs = {};
var _drCal = {};

(function () {
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /* ── DataTable ───────────────────────────────────────────────── */
    var table = $('#tickets-table').DataTable({
        paging:      true,
        pageLength:  15,
        lengthChange: false,
        searching:   true,
        ordering:    true,
        info:        true,
        dom: '<"dt-scroll"t><"dt-footer"ip>',
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] },
            { type: 'num', targets: [5] }
        ],
        infoCallback: function(settings, start, end, max, total, pre) {
            return 'Showing ' + start + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'tickets' : 'ticket');
        },
        language: {
            infoEmpty:   'No tickets found',
            paginate:    { previous: '‹', next: '›' },
            emptyTable:  'No support tickets',
            zeroRecords: 'No tickets match your filters'
        },
        drawCallback: function () {
            var info = this.api().page.info();
            var el = document.getElementById('ticket-count-badge');
            if (el) el.textContent = info.recordsDisplay + ' ticket' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    /* ── Date range custom filter (reads DR hidden inputs) ──────── */
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'tickets-table') return true;
        var from = document.getElementById('ticket-dr-from') ? document.getElementById('ticket-dr-from').value : '';
        var to   = document.getElementById('ticket-dr-to')   ? document.getElementById('ticket-dr-to').value   : '';
        if (!from && !to) return true;
        var row     = table.row(dataIndex).node();
        var dateVal = row ? (row.querySelector('td[data-date]') || {}).getAttribute('data-date') : '';
        if (!dateVal) return true;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    });

    /* ── Text search ─────────────────────────────────────────────── */
    document.getElementById('ticket-search').addEventListener('keyup', function () {
        table.search(this.value).draw();
    });

    /* ── Status filter (col 4, data-status attr used for exact match) */
    document.getElementById('status-filter').addEventListener('change', function () {
        // Use row-attribute filtering so badge HTML doesn't interfere
        table.draw();
    });

    /* ── Priority filter ─────────────────────────────────────────── */
    document.getElementById('priority-filter').addEventListener('change', function () {
        table.draw();
    });

    /* Row-attribute filters for status and priority */
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'tickets-table') return true;
        var status   = document.getElementById('status-filter').value;
        var priority = document.getElementById('priority-filter').value;
        var row      = table.row(dataIndex).node();
        if (!row) return true;
        if (status   && row.getAttribute('data-status')   !== status)   return false;
        if (priority && row.getAttribute('data-priority') !== priority)  return false;
        return true;
    });

    /* ── DR callback — redraw table when date range changes ─────── */
    _drCbs['ticket-dr'] = function () { table.draw(); };

    /* ── Reset ───────────────────────────────────────────────────── */
    document.getElementById('reset-filters').addEventListener('click', function () {
        document.getElementById('ticket-search').value   = '';
        document.getElementById('status-filter').value   = '';
        document.getElementById('priority-filter').value = '';
        drClear('ticket-dr');
        table.search('').draw();
    });

    /* ── Fixed-position dot-menu ─────────────────────────────────── */
    document.querySelectorAll('.dot-menu').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var popupId = btn.getAttribute('data-menu');
            var popup   = document.getElementById(popupId);
            var isOpen  = popup.classList.contains('open');
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
    document.addEventListener('click', function () {
        document.querySelectorAll('.dm-popup.open').forEach(function (p) {
            p.classList.remove('open');
            p.style.cssText = '';
        });
    });

    /* ── helpers ─────────────────────────────────────────────────── */
    var statusLabels = {
        open: 'Open', in_progress: 'In Progress', on_hold: 'On Hold',
        sla_breach: 'SLA Breach', resolved: 'Resolved', closed: 'Closed'
    };

    function statusBadgeHtml(s) {
        var map = {
            open:        '<span class="badge b-blue"  style="font-size:12px">Open</span>',
            in_progress: '<span class="badge b-amber" style="font-size:12px">In Progress</span>',
            on_hold:     '<span class="badge b-gray"  style="font-size:12px">On Hold</span>',
            sla_breach:  '<span style="font-size:12.5px;font-weight:600;color:var(--red)">SLA Breach</span>',
            resolved:    '<span class="badge b-green" style="font-size:12px">Resolved</span>',
            closed:      '<span class="badge b-gray"  style="font-size:12px">Closed</span>'
        };
        return map[s] || '<span class="badge b-gray" style="font-size:12px">' + s + '</span>';
    }

    function getRow(btn) { return btn.closest('tr'); }
    function openModal(el)  { el.style.display = 'flex'; }
    function closeModal(el) { el.style.display = 'none'; }

    /* ── Update Status Modal ─────────────────────────────────────── */
    var usModal     = document.getElementById('update-status-modal');
    var usSubtitle  = document.getElementById('us-subtitle');
    var usCurrent   = document.getElementById('us-current');
    var usNewSt     = document.getElementById('us-new-status');
    var usNote      = document.getElementById('us-note');
    var usErr       = document.getElementById('us-status-err');
    var usSubmit    = document.getElementById('us-submit');
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
                table.draw(false);
            }
        })
        .finally(function () {
            usSubmit.disabled    = false;
            usSubmit.textContent = 'Update Status';
        });
    });

    /* ── Assign Ticket Modal ─────────────────────────────────────── */
    var asModal     = document.getElementById('assign-modal');
    var asSubtitle  = document.getElementById('as-subtitle');
    var asCurrent   = document.getElementById('as-current');
    var asMember    = document.getElementById('as-member');
    var asErr       = document.getElementById('as-member-err');
    var asSubmit    = document.getElementById('as-submit');
    var asActiveRow = null;

    document.querySelectorAll('.assign-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row       = getRow(btn);
            asActiveRow   = row;
            var rawId     = row.getAttribute('data-raw-id');
            var tkId      = row.getAttribute('data-ticket-id');
            var curAssign = row.getAttribute('data-assigned') || '';
            asSubtitle.textContent = 'Assigning ' + tkId;
            asCurrent.value        = curAssign;
            asCurrent.placeholder  = curAssign ? '' : 'Unassigned';
            asMember.value         = '';
            asErr.style.display    = 'none';
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
        asErr.style.display  = 'none';
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

/* ── Date Range Picker (DR) functions ────────────────────────────────── */
var DR_FMT   = '{{ active_date_format() }}';
var _DR_MON  =['January','February','March','April','May','June','July','August','September','October','November','December'];
var _DR_DAY  = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

function drFmt(ymd) {
    if (!ymd) return '';
    var p = ymd.split('-');
    if (p.length < 3) return ymd;
    var y = +p[0], mo = +p[1] - 1, d = +p[2];
    var MS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var ML = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var map = { F:ML[mo], M:MS[mo], Y:String(y), m:String(mo+1).padStart(2,'0'), d:String(d).padStart(2,'0'), j:String(d) };
    return DR_FMT.replace(/[FMYmdj]/g, function(tok) {
        return Object.prototype.hasOwnProperty.call(map, tok) ? map[tok] : tok;
    });
}
function drYmd(dt) {
    var y = dt.getFullYear();
    var m = String(dt.getMonth() + 1).padStart(2, '0');
    var d = String(dt.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + d;
}
function drPresetRange(preset) {
    var now = new Date(); now.setHours(0,0,0,0);
    var from, to = drYmd(now);
    switch (preset) {
        case 'today':     from = to; break;
        case 'last30':    { var d2=new Date(now); d2.setDate(d2.getDate()-30);   from=drYmd(d2); break; }
        case 'thisMonth': { from=drYmd(new Date(now.getFullYear(),now.getMonth(),1));
                            to=drYmd(new Date(now.getFullYear(),now.getMonth()+1,0)); break; }
        case 'lastMonth': { from=drYmd(new Date(now.getFullYear(),now.getMonth()-1,1));
                            to=drYmd(new Date(now.getFullYear(),now.getMonth(),0)); break; }
        case 'last90':    { var d2=new Date(now); d2.setDate(d2.getDate()-90);   from=drYmd(d2); break; }
        case 'last6m':    { var d2=new Date(now); d2.setMonth(d2.getMonth()-6);  from=drYmd(d2); break; }
        case 'last1y':    { var d2=new Date(now); d2.setFullYear(d2.getFullYear()-1); from=drYmd(d2); break; }
        default: return null;
    }
    return { from: from, to: to };
}
function drOpen(id) {
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (!menu) return;
    document.querySelectorAll('.dr-menu.open').forEach(function(m) {
        if (m.id !== id + '-menu') {
            var wid = m.id.replace('-menu', '');
            m.classList.remove('open');
            var otherField = document.querySelector('#' + wid + ' .dr-field');
            if (otherField) otherField.classList.remove('open');
            drHideCalendar(wid);
        }
    });
    var isOpen = menu.classList.toggle('open');
    if (field) { isOpen ? field.classList.add('open') : field.classList.remove('open'); }
    if (!isOpen) drHideCalendar(id);
}
function drPickPreset(id, preset, el) {
    var list = el.closest('.dr-list');
    if (list) list.querySelectorAll('.dr-opt').forEach(function(li) { li.classList.remove('active'); });
    el.classList.add('active');
    if (preset === 'custom') {
        var customRow = document.getElementById(id + '-custom');
        if (customRow) customRow.style.display = 'none';
        drShowCalendar(id);
        return;
    }
    drHideCalendar(id);
    delete _drCal[id];
    var customRow = document.getElementById(id + '-custom');
    if (customRow) customRow.style.display = 'none';
    var range = drPresetRange(preset);
    if (!range) return;
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    if (fromEl) fromEl.value = range.from;
    if (toEl)   toEl.value   = range.to;
    drSetLabel(id, range.from, range.to);
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (menu)  menu.classList.remove('open');
    if (field) field.classList.remove('open');
    var cb = _drCbs[id];
    if (typeof cb === 'function') cb(range.from, range.to);
}
function drApplyCustom(id) {
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    var from   = fromEl ? fromEl.value : '';
    var to     = toEl   ? toEl.value   : '';
    if (!from || !to) return;
    if (from > to) return;
    drSetLabel(id, from, to);
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (menu)  menu.classList.remove('open');
    if (field) field.classList.remove('open');
    var cb = _drCbs[id];
    if (typeof cb === 'function') cb(from, to);
}
function drSetLabel(id, from, to) {
    var textEl = document.getElementById(id + '-text');
    var field  = document.querySelector('#' + id + ' .dr-field');
    if (from || to) {
        if (textEl) { textEl.textContent = drFmt(from) + ' to ' + drFmt(to); textEl.classList.add('has-val'); }
        if (field)  field.classList.add('has-val');
    } else {
        if (textEl) { textEl.textContent = 'Start Date To End Date'; textEl.classList.remove('has-val'); }
        if (field)  field.classList.remove('has-val');
    }
}
function drClear(id) {
    document.querySelectorAll('#' + id + ' .dr-opt').forEach(function(li) { li.classList.remove('active'); });
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    if (fromEl) fromEl.value = '';
    if (toEl)   toEl.value   = '';
    var customRow = document.getElementById(id + '-custom');
    if (customRow) customRow.style.display = 'none';
    drSetLabel(id, '', '');
    drHideCalendar(id);
    if (_drCal[id]) { _drCal[id].start = null; _drCal[id].end = null; _drCal[id].hover = null; }
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (menu)  menu.classList.remove('open');
    if (field) { field.classList.remove('open'); field.classList.remove('has-val'); }
    var cb = _drCbs[id];
    if (typeof cb === 'function') cb('', '');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dr-wrap')) {
        document.querySelectorAll('.dr-menu.open').forEach(function(m) {
            m.classList.remove('open');
            var wrapId = m.id.replace('-menu', '');
            var field  = document.querySelector('#' + wrapId + ' .dr-field');
            if (field) field.classList.remove('open');
            drHideCalendar(wrapId);
        });
    }
});
function drShowCalendar(id) {
    var menu = document.getElementById(id + '-menu');
    if (!menu) return;
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    var exFrom = fromEl ? fromEl.value : '';
    var exTo   = toEl   ? toEl.value   : '';
    if (!_drCal[id]) {
        var now = new Date();
        var y1, m1, y2, m2;
        if (exFrom) { var p = exFrom.split('-'); y1 = +p[0]; m1 = +p[1] - 1; }
        else        { y1 = now.getFullYear(); m1 = now.getMonth(); }
        if (exTo)   { var p2 = exTo.split('-'); y2 = +p2[0]; m2 = +p2[1] - 1; }
        else        { m2 = m1 === 11 ? 0 : m1 + 1; y2 = m1 === 11 ? y1 + 1 : y1; }
        if (y1 === y2 && m1 === m2) { m2 = m1 === 11 ? 0 : m1 + 1; y2 = m1 === 11 ? y1 + 1 : y1; }
        _drCal[id] = { y1: y1, m1: m1, y2: y2, m2: m2, start: null, end: null, hover: null };
    }
    menu.classList.add('dr-cal-mode');
    var fieldEl = document.querySelector('#' + id + ' .dr-field');
    if (fieldEl) {
        var rect = fieldEl.getBoundingClientRect();
        var calW = 664;
        var left = rect.left;
        if (left + calW > window.innerWidth - 8) left = Math.max(8, window.innerWidth - calW - 8);
        menu.style.position = 'fixed';
        menu.style.top      = (rect.bottom + 4) + 'px';
        menu.style.left     = left + 'px';
    }
    var old = document.getElementById(id + '-cal-panel');
    if (old) old.remove();
    var panel = document.createElement('div');
    panel.id        = id + '-cal-panel';
    panel.className = 'dr-cal-panel';
    menu.appendChild(panel);
    drRenderCal(id);
    panel.addEventListener('click',      function(e) { e.stopPropagation(); drCalClick(e, id); });
    panel.addEventListener('change',     function(e) { drCalChange(e, id); });
    panel.addEventListener('mouseover',  function(e) { drCalHover(e, id); });
    panel.addEventListener('mouseleave', function()  { drCalHoverEnd(id); });
}
function drHideCalendar(id) {
    var menu = document.getElementById(id + '-menu');
    if (menu) { menu.classList.remove('dr-cal-mode'); menu.style.position = ''; menu.style.top = ''; menu.style.left = ''; }
    var panel = document.getElementById(id + '-cal-panel');
    if (panel) panel.remove();
}
function drRenderCal(id) {
    var panel = document.getElementById(id + '-cal-panel');
    if (!panel) return;
    var s = _drCal[id];
    panel.innerHTML =
        '<div class="dr-cal-months" id="' + id + '-cal-months">' + drBuildMonth(id, s.y1, s.m1, 1) + drBuildMonth(id, s.y2, s.m2, 2) + '</div>' +
        '<div class="dr-cal-footer">' +
            '<span class="dr-cal-rtxt" id="' + id + '-cal-rtxt">' + drCalRangeTxt(id) + '</span>' +
            '<div style="display:flex;gap:8px"><button class="dr-cal-btn-cancel" data-cal-cancel="' + id + '">Cancel</button>' +
            '<button class="dr-cal-btn-apply" data-cal-apply="' + id + '">Apply</button></div></div>';
}
function drRefreshCalMonths(id) {
    var wrap = document.getElementById(id + '-cal-months');
    if (!wrap) return;
    var s = _drCal[id];
    wrap.innerHTML = drBuildMonth(id, s.y1, s.m1, 1) + drBuildMonth(id, s.y2, s.m2, 2);
    var rtxt = document.getElementById(id + '-cal-rtxt');
    if (rtxt) rtxt.textContent = drCalRangeTxt(id);
}
function drCalRangeTxt(id) {
    var s = _drCal[id];
    if (!s) return 'Select a date range';
    if (s.start && s.end) return drFmt(s.start) + ' To ' + drFmt(s.end);
    if (s.start)          return drFmt(s.start) + ' To ...';
    return 'Select a date range';
}
function drBuildMonth(id, year, month, side) {
    var s = _drCal[id];
    var curY = new Date().getFullYear();
    var todayStr = drYmd(new Date());
    var h = '<div class="dr-month-wrap"><div class="dr-month-hd">';
    h += '<button class="dr-nav-btn" data-nav="' + id + '" data-side="' + side + '" data-dir="-1">&#8249;</button>';
    h += '<select class="dr-mon-sel" data-sel="' + id + '" data-side="' + side + '" data-type="m">';
    for (var mi = 0; mi < 12; mi++) h += '<option value="' + mi + '"' + (mi === month ? ' selected' : '') + '>' + _DR_MON[mi] + '</option>';
    h += '</select>';
    h += '<select class="dr-yr-sel" data-sel="' + id + '" data-side="' + side + '" data-type="y">';
    for (var yi = curY - 5; yi <= curY + 3; yi++) h += '<option value="' + yi + '"' + (yi === year ? ' selected' : '') + '>' + yi + '</option>';
    h += '</select>';
    h += '<button class="dr-nav-btn" data-nav="' + id + '" data-side="' + side + '" data-dir="1">&#8250;</button></div>';
    h += '<div class="dr-day-grid">';
    for (var di = 0; di < 7; di++) h += '<div class="dr-day-hdr">' + _DR_DAY[di] + '</div>';
    var firstDow = new Date(year, month, 1).getDay();
    var startCol = firstDow === 0 ? 6 : firstDow - 1;
    var daysInMon  = new Date(year, month + 1, 0).getDate();
    var prevMonLen = new Date(year, month, 0).getDate();
    for (var pi = 0; pi < startCol; pi++) h += '<div class="dr-day-cell dr-day-out">' + (prevMonLen - startCol + pi + 1) + '</div>';
    for (var day = 1; day <= daysInMon; day++) {
        var mm  = String(month + 1).padStart(2, '0');
        var dd  = String(day).padStart(2, '0');
        var ymd = year + '-' + mm + '-' + dd;
        var lo = null, hi = null;
        if (s.start && s.end) { lo = s.start; hi = s.end; }
        else if (s.start && s.hover) { lo = s.start < s.hover ? s.start : s.hover; hi = s.start < s.hover ? s.hover : s.start; }
        var isStart = ymd === s.start;
        var isEnd   = (s.end && ymd === s.end) || (!s.end && s.hover && ymd === s.hover && ymd !== s.start);
        var inRange = lo && hi && ymd > lo && ymd < hi;
        var isToday = ymd === todayStr;
        var cls = 'dr-day-cell';
        if (isStart) cls += ' dr-day-sel-s';
        if (isEnd)   cls += ' dr-day-hover-preview';
        if (s.end && ymd === s.end) { cls = cls.replace('dr-day-hover-preview', ''); cls += ' dr-day-sel-e'; }
        if (inRange) cls += ' dr-day-in-range';
        if (isToday && !isStart && !(s.end && ymd === s.end)) cls += ' dr-day-today';
        h += '<div class="' + cls + '" data-ymd="' + ymd + '">' + day + '</div>';
    }
    var total = startCol + daysInMon;
    var tail  = total % 7 === 0 ? 0 : 7 - (total % 7);
    for (var ti = 1; ti <= tail; ti++) h += '<div class="dr-day-cell dr-day-out">' + ti + '</div>';
    h += '</div></div>';
    return h;
}
function drCalClick(e, id) {
    var nav = e.target.closest('[data-nav]');
    if (nav && nav.dataset.nav === id) {
        var s = _drCal[id]; var side = +nav.dataset.side; var dir = +nav.dataset.dir;
        if (side === 1) { s.m1 += dir; if (s.m1 > 11) { s.m1 = 0; s.y1++; } if (s.m1 < 0) { s.m1 = 11; s.y1--; } }
        else            { s.m2 += dir; if (s.m2 > 11) { s.m2 = 0; s.y2++; } if (s.m2 < 0) { s.m2 = 11; s.y2--; } }
        drRefreshCalMonths(id); return;
    }
    var cell = e.target.closest('[data-ymd]');
    if (cell && cell.dataset.ymd) {
        var ymd2 = cell.dataset.ymd; var s = _drCal[id];
        if (!s.start || (s.start && s.end)) { s.start = ymd2; s.end = null; s.hover = null; }
        else { if (ymd2 < s.start) { s.end = s.start; s.start = ymd2; } else { s.end = ymd2; } s.hover = null; }
        drRefreshCalMonths(id); return;
    }
    if (e.target.closest('[data-cal-cancel]')) { drClear(id); return; }
    var applyBtn = e.target.closest('[data-cal-apply]');
    if (applyBtn) {
        var s = _drCal[id];
        if (!s || !s.start || !s.end) return;
        var fromEl2 = document.getElementById(id + '-from');
        var toEl2   = document.getElementById(id + '-to');
        if (fromEl2) fromEl2.value = s.start;
        if (toEl2)   toEl2.value   = s.end;
        drSetLabel(id, s.start, s.end);
        drHideCalendar(id);
        var menu2  = document.getElementById(id + '-menu');
        var field2 = document.querySelector('#' + id + ' .dr-field');
        if (menu2)  menu2.classList.remove('open');
        if (field2) field2.classList.remove('open');
        var cb = _drCbs[id];
        if (typeof cb === 'function') cb(s.start, s.end);
    }
}
function drCalChange(e, id) {
    var sel = e.target.closest('[data-sel]');
    if (!sel || sel.dataset.sel !== id) return;
    var s = _drCal[id]; var side = +sel.dataset.side; var val = +sel.value;
    if (sel.dataset.type === 'm') { if (side === 1) s.m1 = val; else s.m2 = val; }
    else                          { if (side === 1) s.y1 = val; else s.y2 = val; }
    drRefreshCalMonths(id);
}
function drCalHover(e, id) {
    var cell = e.target.closest('[data-ymd]');
    if (!cell || !cell.dataset.ymd) return;
    var s = _drCal[id];
    if (!s || !s.start || s.end) return;
    if (s.hover === cell.dataset.ymd) return;
    s.hover = cell.dataset.ymd;
    drRefreshCalMonths(id);
}
function drCalHoverEnd(id) {
    var s = _drCal[id];
    if (!s || !s.start || s.end) return;
    s.hover = null;
    drRefreshCalMonths(id);
}
</script>
@endpush
