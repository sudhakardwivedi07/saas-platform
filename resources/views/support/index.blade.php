@extends('layouts.app')
@section('title', 'Support')
@section('page-title', 'Support')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
/* ── DataTables overrides ─────────────────────────── */
.dt-tscroll{overflow-x:auto}
.dt-footer{display:flex;align-items:center;justify-content:space-between;padding:12px 4px 0;font-size:12.5px;color:var(--text-3);flex-wrap:wrap;gap:8px}
div.dataTables_info{font-size:12.5px;color:var(--text-3)}
div.dataTables_paginate .paginate_button{padding:4px 9px;border-radius:6px;font-size:12.5px;cursor:pointer;border:1px solid transparent}
div.dataTables_paginate .paginate_button.current{background:var(--orange);color:#fff!important;border-color:var(--orange)}
div.dataTables_paginate .paginate_button:hover:not(.current){background:var(--bg);border-color:var(--border)}
div.dataTables_paginate .paginate_button.disabled{color:var(--text-3)!important;cursor:default}

/* ── Ticket ID link style ─────────────────────────── */
.tk-id{font-weight:700;color:var(--orange);font-size:13px;font-family:monospace;cursor:pointer}
.tk-id:hover{text-decoration:underline}

/* ── Priority text colors ─────────────────────────── */
.pri-high  {color:var(--red);font-weight:600;font-size:13px}
.pri-medium{color:#d97706;font-weight:600;font-size:13px}
.pri-low   {color:var(--text-2);font-weight:500;font-size:13px}

/* ── Status badges ────────────────────────────────── */
.st-open      {background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe}
.st-in_progress{background:#fffbeb;color:#d97706;border:1px solid #fde68a}
.st-resolved  {background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.st-closed    {background:#f9fafb;color:#6b7280;border:1px solid #e5e7eb}
.st-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap}

/* ── Filters bar ──────────────────────────────────── */
.filter-bar{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap}
.filter-bar .srch-wrap{flex:1;min-width:200px}
.filter-sel{padding:6px 12px;border:1px solid var(--border);border-radius:7px;font-size:13px;color:var(--text-1);background:#fff;outline:none;cursor:pointer}
.filter-sel:focus{border-color:var(--orange)}

/* ── Table ────────────────────────────────────────── */
#tickets-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);padding:10px 14px;border-bottom:1px solid var(--border);white-space:nowrap}
#tickets-table td{padding:12px 14px;border-bottom:1px solid var(--bg);vertical-align:middle;font-size:13px}
#tickets-table tbody tr:last-child td{border-bottom:none}
#tickets-table tbody tr:hover td{background:var(--bg)}
.raised-date{font-size:12.5px;color:var(--text-1)}
.raised-time{font-size:11.5px;color:var(--text-3);margin-top:1px}

/* ── Three-dot menu ───────────────────────────────── */
.dm-btn{background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;color:var(--text-3);font-size:18px;line-height:1}
.dm-btn:hover{background:var(--bg);color:var(--text-1)}
.dm-popup{position:absolute;right:8px;top:100%;z-index:200;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:6px;min-width:160px;display:none}
.dm-popup.open{display:block}
.dm-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;font-size:13px;color:var(--text-1);cursor:pointer;white-space:nowrap;background:none;border:none;width:100%;text-align:left}
.dm-item:hover{background:var(--bg)}
.dm-item svg{flex-shrink:0}

/* ── View Ticket Modal ────────────────────────────── */
.vt-header{display:flex;align-items:flex-start;gap:14px;margin-bottom:16px}
.vt-icon{width:44px;height:44px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.vt-meta-box{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:14px 18px;display:flex;gap:32px;flex-wrap:wrap;margin-bottom:16px}
.vt-meta-col{display:flex;flex-direction:column;gap:4px}
.vt-meta-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3)}
.vt-meta-val{font-size:13px;font-weight:500;color:var(--text-1)}
.vt-section-lbl{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);margin-bottom:6px}
.vt-desc-box{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px 14px;font-size:13.5px;color:var(--text-1);line-height:1.65;white-space:pre-wrap;max-height:200px;overflow-y:auto}
.att-row{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;margin-bottom:6px}
.att-info{display:flex;align-items:center;gap:10px}
.att-icon{width:32px;height:32px;border-radius:6px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.att-name{font-size:13px;font-weight:500;color:var(--text-1)}
.att-dl{padding:5px 12px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:500;color:var(--text-2);background:#fff;cursor:pointer;text-decoration:none}
.att-dl:hover{background:var(--bg)}

/* ── Update Status Modal ──────────────────────────── */
.us-cur{width:100%;padding:9px 12px;background:var(--bg);border:1px solid var(--border);border-radius:7px;font-size:13px;color:var(--text-3);cursor:not-allowed}
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Support</h1>
        <p>Submit and track support requests for your account.</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-primary" id="btnNewTicket">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Ticket
        </button>
    </div>
</div>

{{-- Info Banner --}}
<div class="info-banner" style="margin-bottom:1.25rem">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="info-banner-txt">
        Need urgent help? Email us at
        <a href="mailto:support@example.com" style="color:var(--blue);font-weight:600;text-decoration:none">support@example.com</a>
        or call <strong>1-800-000-0000</strong> (Mon–Fri, 9am–6pm EST). Average response time is under 4 hours.
    </span>
</div>

{{-- Tickets Table --}}
<div class="card" style="padding:0;overflow:hidden">

    {{-- Filters --}}
    <div class="filter-bar">
        <div class="srch-wrap" style="flex:1;min-width:180px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="srch-inp" id="ticket-search" placeholder="Search tickets…" autocomplete="off">
        </div>
        <select class="filter-sel" id="status-filter">
            <option value="">All Status</option>
            <option value="Open">Open</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
            <option value="Closed">Closed</option>
        </select>
        <select class="filter-sel" id="priority-filter">
            <option value="">All Priority</option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
        </select>
    </div>

    <div class="dt-tscroll">
        <table id="tickets-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th style="width:110px">Ticket ID</th>
                    <th>Subject</th>
                    <th style="width:100px">Priority</th>
                    <th style="width:130px">Status</th>
                    <th style="width:130px">Raised On</th>
                    <th style="width:48px"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($tickets ?? [] as $ticket)
            @php
                $tkId    = 'TK-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
                $pri     = strtolower($ticket->priority ?? 'low');
                $st      = strtolower($ticket->status ?? 'open');
                $stLabel = match($st) {
                    'in_progress' => 'In Progress',
                    'resolved'    => 'Resolved',
                    'closed'      => 'Closed',
                    default       => 'Open',
                };
            @endphp
            <tr>
                <td>
                    <span class="tk-id" data-id="{{ $ticket->id }}"
                          data-show="{{ route('support.show', $ticket->id) }}"
                          data-update="{{ route('support.update', $ticket->id) }}"
                          data-notes="{{ route('support.notes', $ticket->id) }}">
                        {{ $tkId }}
                    </span>
                </td>
                <td style="color:var(--text-1);font-weight:500;max-width:320px">{{ $ticket->subject }}</td>
                <td><span class="pri-{{ $pri }}">{{ ucfirst($pri) }}</span></td>
                <td>
                    <span class="st-badge st-{{ $st }}">{{ $stLabel }}</span>
                </td>
                <td>
                    <div class="raised-date">{{ $ticket->created_at->format('M j, Y') }}</div>
                    <div class="raised-time">{{ $ticket->created_at->format('g:i A') }}</div>
                </td>
                <td style="position:relative">
                    <button class="dm-btn" data-menu="dm-{{ $ticket->id }}">···</button>
                    <div class="dm-popup" id="dm-{{ $ticket->id }}">
                        <button class="dm-item tk-view-btn"
                            data-id="{{ $ticket->id }}"
                            data-show="{{ route('support.show', $ticket->id) }}"
                            data-update="{{ route('support.update', $ticket->id) }}"
                            data-notes="{{ route('support.notes', $ticket->id) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            View Ticket
                        </button>
                        <button class="dm-item tk-status-btn"
                            data-id="{{ $ticket->id }}"
                            data-tk="{{ $tkId }}"
                            data-status="{{ $st }}"
                            data-update="{{ route('support.update', $ticket->id) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Update Status
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── View Ticket Modal ─────────────────────────────────────────────── --}}
<div id="viewTicketModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:600px;width:100%;max-height:90vh;overflow-y:auto">
        <button class="modal-close" onclick="document.getElementById('viewTicketModal').style.display='none'">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="vt-header">
            <div class="vt-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8" width="22" height="22"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span style="font-size:17px;font-weight:700;color:var(--text-1)" id="vt-id">TK-0000</span>
                    <span id="vt-pri-badge"></span>
                    <span id="vt-st-badge"></span>
                </div>
                <div style="font-size:12.5px;color:var(--text-3);margin-top:3px" id="vt-created"></div>
            </div>
        </div>

        <div class="vt-meta-box">
            <div class="vt-meta-col">
                <span class="vt-meta-lbl">Raised On</span>
                <span class="vt-meta-val" id="vt-raised"></span>
            </div>
            <div class="vt-meta-col">
                <span class="vt-meta-lbl">Submitted By</span>
                <span class="vt-meta-val" id="vt-by"></span>
            </div>
            <div class="vt-meta-col">
                <span class="vt-meta-lbl">Status</span>
                <span class="vt-meta-val" id="vt-status-text"></span>
            </div>
        </div>

        <div class="mf-field">
            <div class="vt-section-lbl">Subject</div>
            <div style="font-size:14px;font-weight:600;color:var(--text-1)" id="vt-subject"></div>
        </div>

        <div class="mf-field">
            <div class="vt-section-lbl">Description</div>
            <div class="vt-desc-box" id="vt-message"></div>
        </div>

        <div class="mf-field" id="vt-att-section" style="display:none">
            <div class="vt-section-lbl">Attachments</div>
            <div class="att-row">
                <div class="att-info">
                    <div class="att-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <div class="att-name" id="vt-att-name"></div>
                    </div>
                </div>
                <a class="att-dl" id="vt-att-link" href="#" target="_blank">Download</a>
            </div>
        </div>

        <div class="mf-field">
            <div class="vt-section-lbl" style="margin-bottom:6px">Internal Notes</div>
            <textarea id="vt-notes" rows="3" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text-1);resize:vertical;outline:none;font-family:inherit"
                placeholder="Add internal notes or resolution steps visible only to your team…"></textarea>
        </div>

        <div class="mf-foot">
            <button class="btn btn-secondary" onclick="document.getElementById('viewTicketModal').style.display='none'">Close</button>
            <button class="btn btn-secondary" id="vt-update-status-btn">Update Status</button>
            <button class="btn btn-primary" id="vt-save-notes-btn">Save Notes</button>
        </div>
    </div>
</div>

{{-- ── Update Status Modal ───────────────────────────────────────────── --}}
<div id="updateStatusModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:440px;width:100%">
        <button class="modal-close" onclick="document.getElementById('updateStatusModal').style.display='none'">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="modal-title">Update Status</div>
        <div class="modal-sub" id="us-sub">Changing status for TK-0000</div>

        <div class="mf-field">
            <label class="mf-label">Current Status</label>
            <input type="text" class="us-cur" id="us-cur-status" readonly>
        </div>

        <div class="mf-field">
            <label class="mf-label">New Status <span style="color:var(--red)">*</span></label>
            <select id="us-new-status" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:7px;font-size:13px;color:var(--text-1);background:#fff;outline:none">
                <option value="">Select new status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
        </div>

        <div class="mf-field">
            <label class="mf-label">Note <span style="color:var(--text-3);font-weight:400">(optional)</span></label>
            <textarea id="us-note" rows="3" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text-1);resize:vertical;outline:none;font-family:inherit"
                placeholder="Add a note about this status change…"></textarea>
        </div>

        <div class="mf-foot">
            <button class="btn btn-secondary" onclick="document.getElementById('updateStatusModal').style.display='none'">Cancel</button>
            <button class="btn btn-primary" id="us-submit-btn">Update Status</button>
        </div>
    </div>
</div>

{{-- ── New Ticket Modal ──────────────────────────────────────────────── --}}
<div id="newTicketModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:540px;width:100%">
        <button class="modal-close" onclick="document.getElementById('newTicketModal').style.display='none'">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="modal-title">New Support Ticket</div>
        <div class="modal-sub">Describe your issue and we'll get back to you as soon as possible.</div>

        <form method="POST" action="{{ route('support.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mf-field">
                <label class="mf-label" for="ticket-subject">Subject</label>
                <input type="text" id="ticket-subject" name="subject" class="mf-inp"
                       placeholder="Brief description of your issue" required value="{{ old('subject') }}">
            </div>
            <div class="mf-field">
                <label class="mf-label">Priority</label>
                <div style="display:flex;gap:8px;margin-top:4px">
                    @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High'] as $v => $l)
                    <button type="button" class="btn btn-sm priority-toggle" data-value="{{ $v }}"
                        style="flex:1;border:2px solid var(--border);background:#fff;color:var(--text-2);transition:all .15s">{{ $l }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="priority" id="priorityInput" value="{{ old('priority','low') }}">
            </div>
            <div class="mf-field">
                <label class="mf-label" for="ticket-message">Message</label>
                <textarea id="ticket-message" name="message" class="mf-textarea" rows="5"
                          placeholder="Provide as much detail as possible..." required>{{ old('message') }}</textarea>
            </div>
            <div class="mf-field">
                <label class="mf-label" for="ticket-attachment">
                    Attachment <span style="color:var(--text-3);font-weight:400">(optional)</span>
                </label>
                <input type="file" id="ticket-attachment" name="attachment"
                       style="display:block;width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;font-size:13px;color:var(--text-2);background:#fafafa;cursor:pointer">
                <span style="font-size:11.5px;color:var(--text-3);margin-top:4px;display:block">Max 10MB. Accepted: jpg, png, pdf, zip.</span>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('newTicketModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>

{{-- PHP data bridge via data-* (no Blade inside script blocks) --}}
<div id="support-data" style="display:none"
    data-has-errors="{{ $errors->any() ? '1' : '0' }}"
    data-form-errors="{{ htmlspecialchars(json_encode($errors->all()), ENT_QUOTES, 'UTF-8') }}"
    data-default-priority="{{ old('priority','low') }}"></div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
// ── State ────────────────────────────────────────────────────────────────────
var _currentUpdateUrl  = '';
var _currentNotesUrl   = '';
var _currentTicketId   = '';

// ── DataTable ─────────────────────────────────────────────────────────────────
$(document).ready(function () {
    var table = $('#tickets-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true,
        order: [[0, 'desc']],
        dom: 't<"dt-footer"ip>',
        columnDefs: [{ orderable: false, targets: [5] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ tickets',
            infoEmpty: 'No tickets found',
            paginate: { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:56px 20px">'
                + '<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.4" style="display:block;margin:0 auto 12px">'
                + '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>'
                + '<div style="font-size:14px;font-weight:500;color:#6b7280">No tickets yet</div>'
                + '<div style="font-size:12.5px;color:#9ca3af;margin-top:4px">Click "+ New Ticket" to get started.</div></div>',
        },
        drawCallback: function () {
            document.querySelectorAll('.dm-popup').forEach(function (p) { p.classList.remove('open'); });
        }
    });

    // External search
    document.getElementById('ticket-search').addEventListener('keyup', function () {
        table.search(this.value).draw();
    });

    // Status filter (column 3 hidden text)
    document.getElementById('status-filter').addEventListener('change', function () {
        table.column(3).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

    // Priority filter (column 2)
    document.getElementById('priority-filter').addEventListener('change', function () {
        table.column(2).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

    // Init form
    var el = document.getElementById('support-data');
    if (el) {
        var priority = el.dataset.defaultPriority || 'low';
        var hasErr   = el.dataset.hasErrors === '1';
        var errs     = JSON.parse(el.dataset.formErrors || '[]');
        var btn = document.querySelector('.priority-toggle[data-value="' + priority + '"]');
        if (btn) selectPriority(btn);
        if (hasErr) {
            document.getElementById('newTicketModal').style.display = 'flex';
            errs.forEach(function (msg) { showToast(msg, 'error'); });
        }
    }
});

// ── Priority toggle ──────────────────────────────────────────────────────────
function selectPriority(btn) {
    document.querySelectorAll('.priority-toggle').forEach(function (b) {
        b.style.borderColor = 'var(--border)'; b.style.background = '#fff';
        b.style.color = 'var(--text-2)'; b.style.fontWeight = '400';
    });
    btn.style.borderColor = 'var(--blue)';
    btn.style.background  = 'rgba(26,106,168,.07)';
    btn.style.color       = 'var(--blue)';
    btn.style.fontWeight  = '600';
    document.getElementById('priorityInput').value = btn.dataset.value;
}

// ── Three-dot menu clicks ────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    var dmBtn = e.target.closest('.dm-btn');
    if (dmBtn) {
        e.stopPropagation();
        var id = dmBtn.dataset.menu;
        document.querySelectorAll('.dm-popup.open').forEach(function (p) {
            if (p.id !== id) p.classList.remove('open');
        });
        document.getElementById(id).classList.toggle('open');
        return;
    }
    // View ticket from TK-ID link or View Ticket menu item
    var viewBtn = e.target.closest('.tk-id, .tk-view-btn');
    if (viewBtn) {
        document.querySelectorAll('.dm-popup.open').forEach(function (p) { p.classList.remove('open'); });
        openViewTicket(viewBtn.dataset.show, viewBtn.dataset.update, viewBtn.dataset.notes);
        return;
    }
    // Update Status from menu
    var stBtn = e.target.closest('.tk-status-btn');
    if (stBtn) {
        document.querySelectorAll('.dm-popup.open').forEach(function (p) { p.classList.remove('open'); });
        openUpdateStatus(stBtn.dataset.update, stBtn.dataset.tk, stBtn.dataset.status);
        return;
    }
    // Close menus on outside click
    document.querySelectorAll('.dm-popup.open').forEach(function (p) { p.classList.remove('open'); });
});

document.getElementById('btnNewTicket').addEventListener('click', function () {
    document.getElementById('newTicketModal').style.display = 'flex';
});

// ── View Ticket ──────────────────────────────────────────────────────────────
var PRI_CLASS = { high: 'pri-high', medium: 'pri-medium', low: 'pri-low' };
var ST_CLASS  = { open: 'st-open', in_progress: 'st-in_progress', resolved: 'st-resolved', closed: 'st-closed' };
var ST_LABEL  = { open: 'Open', in_progress: 'In Progress', resolved: 'Resolved', closed: 'Closed' };

function openViewTicket(showUrl, updateUrl, notesUrl) {
    _currentUpdateUrl = updateUrl;
    _currentNotesUrl  = notesUrl;

    fetch(showUrl, { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
        .then(function (r) { return r.json(); })
        .then(function (t) {
            _currentTicketId = t.ticket_id;

            document.getElementById('vt-id').textContent      = t.ticket_id;
            document.getElementById('vt-created').textContent = 'Submitted on ' + t.created_at;
            document.getElementById('vt-raised').textContent  = t.created_at;
            document.getElementById('vt-by').textContent      = t.submitted_by;
            document.getElementById('vt-status-text').textContent = ST_LABEL[t.status] || t.status;
            document.getElementById('vt-subject').textContent = t.subject;
            document.getElementById('vt-message').textContent = t.message;
            document.getElementById('vt-notes').value         = t.notes || '';

            // Priority badge
            var priEl = document.getElementById('vt-pri-badge');
            priEl.className   = 'badge ' + (t.priority === 'high' ? 'b-red' : t.priority === 'medium' ? 'b-amber' : 'b-gray');
            priEl.textContent = t.priority.charAt(0).toUpperCase() + t.priority.slice(1);

            // Status badge
            var stEl = document.getElementById('vt-st-badge');
            stEl.className   = 'st-badge ' + (ST_CLASS[t.status] || 'st-open');
            stEl.textContent = ST_LABEL[t.status] || t.status;

            // Attachment
            var attSec = document.getElementById('vt-att-section');
            if (t.attachment_name) {
                document.getElementById('vt-att-name').textContent = t.attachment_name;
                document.getElementById('vt-att-link').href = t.attachment_url;
                attSec.style.display = 'block';
            } else {
                attSec.style.display = 'none';
            }

            // Wire Update Status button in modal to open the update status modal
            document.getElementById('vt-update-status-btn').onclick = function () {
                openUpdateStatus(t.update_url, t.ticket_id, t.status);
            };

            document.getElementById('viewTicketModal').style.display = 'flex';
        })
        .catch(function () { showToast('Failed to load ticket.', 'error'); });
}

// ── Save Notes ───────────────────────────────────────────────────────────────
document.getElementById('vt-save-notes-btn').addEventListener('click', function () {
    if (!_currentNotesUrl) return;
    var notes = document.getElementById('vt-notes').value;
    fetch(_currentNotesUrl, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ notes: notes })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) { showToast('Notes saved.', 'success'); }
        else { showToast('Failed to save notes.', 'error'); }
    })
    .catch(function () { showToast('Failed to save notes.', 'error'); });
});

// ── Update Status Modal ───────────────────────────────────────────────────────
function openUpdateStatus(updateUrl, tkId, currentStatus) {
    _currentUpdateUrl = updateUrl;
    document.getElementById('us-sub').textContent         = 'Changing status for ' + tkId;
    document.getElementById('us-cur-status').value        = ST_LABEL[currentStatus] || currentStatus;
    document.getElementById('us-new-status').value        = '';
    document.getElementById('us-note').value              = '';
    // Close view modal if open
    document.getElementById('viewTicketModal').style.display = 'none';
    document.getElementById('updateStatusModal').style.display = 'flex';
}

document.getElementById('us-submit-btn').addEventListener('click', function () {
    var newStatus = document.getElementById('us-new-status').value;
    if (!newStatus) { showToast('Please select a new status.', 'error'); return; }

    fetch(_currentUpdateUrl, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ status: newStatus, note: document.getElementById('us-note').value })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            showToast(data.ticket_id + ' status updated.', 'success');
            document.getElementById('updateStatusModal').style.display = 'none';
            setTimeout(function () { location.reload(); }, 1200);
        } else {
            showToast('Update failed.', 'error');
        }
    })
    .catch(function () { showToast('Update failed.', 'error'); });
});
</script>
@endpush
