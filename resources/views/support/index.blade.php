@extends('layouts.app')
@section('title', 'Support')
@section('page-title', 'Support')

@push('styles')
<link rel="stylesheet" href="{{ asset(config('assets.datatables_css')) }}">
<style>
/* ── DataTables ────────────────────────────────────── */
.dt-tscroll{overflow-x:auto}
.dt-footer{display:flex;align-items:center;justify-content:space-between;padding:12px 4px 0;font-size:12.5px;color:var(--text-3);flex-wrap:wrap;gap:8px}
div.dataTables_info{font-size:12.5px;color:var(--text-3)}
div.dataTables_paginate .paginate_button{padding:4px 9px;border-radius:6px;font-size:12.5px;cursor:pointer;border:1px solid transparent}
div.dataTables_paginate .paginate_button.current{background:var(--orange);color:#fff!important;border-color:var(--orange)}
div.dataTables_paginate .paginate_button:hover:not(.current){background:var(--bg);border-color:var(--border)}
div.dataTables_paginate .paginate_button.disabled{color:var(--text-3)!important;cursor:default}

/* ── Tabs ──────────────────────────────────────────── */
.sup-tabs{display:flex;gap:0;border-bottom:1px solid var(--border);padding:0 20px;background:#fff}
.sup-tab{padding:13px 20px;font-size:13.5px;font-weight:500;color:var(--text-3);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;display:flex;align-items:center;gap:8px;user-select:none;border-top:none;border-left:none;border-right:none;background:none}
.sup-tab:hover{color:var(--text-1)}
.sup-tab.active{color:var(--orange);border-bottom-color:var(--orange);font-weight:600}
.sup-tab .tab-count{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:1px 8px;font-size:11.5px;font-weight:600;color:var(--text-3)}
.sup-tab.active .tab-count{background:#fff3ec;border-color:#fcd9b7;color:var(--orange)}

/* ── Ticket ID ─────────────────────────────────────── */
.tk-id{font-weight:700;color:var(--orange);font-size:13px;font-family:monospace;cursor:pointer}
.tk-id:hover{text-decoration:underline}

/* ── Priority ──────────────────────────────────────── */
.pri-high  {color:#dc2626;font-weight:600;font-size:13px}
.pri-medium{color:#d97706;font-weight:600;font-size:13px}
.pri-low   {color:var(--text-3);font-weight:500;font-size:13px}

/* ── Status badges ─────────────────────────────────── */
.st-open       {background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe}
.st-in_progress{background:#fffbeb;color:#d97706;border:1px solid #fde68a}
.st-resolved   {background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.st-closed     {background:#f9fafb;color:#6b7280;border:1px solid #e5e7eb}
.st-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap}

/* ── Filter bar ────────────────────────────────────── */
.filter-bar{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap}
.filter-sel{padding:6px 12px;border:1px solid var(--border);border-radius:7px;font-size:13px;color:var(--text-1);background:#fff;outline:none;cursor:pointer}
.filter-sel:focus{border-color:var(--orange)}

/* ── Table ─────────────────────────────────────────── */
.sup-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);padding:10px 14px;border-bottom:1px solid var(--border);white-space:nowrap}
.sup-table td{padding:12px 14px;border-bottom:1px solid var(--bg);vertical-align:middle;font-size:13px}
.sup-table tbody tr:last-child td{border-bottom:none}
.sup-table tbody tr:hover td{background:var(--bg)}
.raised-date{font-size:12.5px;color:var(--text-1)}
.raised-time{font-size:11.5px;color:var(--text-3);margin-top:1px}
.firm-name{font-size:13px;font-weight:600;color:var(--text-1)}
.firm-user{font-size:11.5px;color:var(--text-3);margin-top:1px}

/* ── Three-dot menu ────────────────────────────────── */
.dm-btn{background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;color:var(--text-3);font-size:18px;line-height:1}
.dm-btn:hover{background:var(--bg);color:var(--text-1)}
.dm-popup{position:fixed;z-index:9999;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:6px;min-width:160px;display:none}
.dm-popup.open{display:block}
.dm-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;font-size:13px;color:var(--text-1);cursor:pointer;white-space:nowrap;background:none;border:none;width:100%;text-align:left}
.dm-item:hover{background:var(--bg)}
.dm-item svg{flex-shrink:0}

/* ── View Ticket Modal ─────────────────────────────── */
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

/* ── Update Status Modal ───────────────────────────── */
.us-cur{width:100%;padding:9px 12px;background:var(--bg);border:1px solid var(--border);border-radius:7px;font-size:13px;color:var(--text-3);cursor:not-allowed}

/* ── Ticket Form Validation ────────────────────────── */
.tk-err {
    font-size: 11.5px; color: var(--red); margin-top: 4px;
    display: flex; align-items: center; gap: 4px;
}
.tk-err::before {
    content: '';
    display: inline-block; width: 12px; height: 12px; flex-shrink: 0;
    background: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='2'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cline x1='12' y1='8' x2='12' y2='12'/%3E%3Cline x1='12' y1='16' x2='12.01' y2='16'/%3E%3C/svg%3E") center/contain no-repeat;
}
.mf-inp.tk-invalid, .mf-textarea.tk-invalid { border-color: var(--red) !important; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Support</h1>
        <p>Manage client support tickets and contact the  Insights team</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-primary" id="btnNewTicket">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Raise a Ticket
        </button>
    </div>
</div>

{{-- Tabs Card --}}
<div class="card" style="padding:0;overflow:hidden">

    {{-- Tab Bar --}}
    <div class="sup-tabs">
        <button class="sup-tab active" data-tab="client">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Client Tickets
            <span class="tab-count" id="count-client">{{ count($clientTickets ?? []) }}</span>
        </button>
        <button class="sup-tab" data-tab="my">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            My Tickets
            <span class="tab-count" id="count-my">{{ count($myTickets ?? []) }}</span>
        </button>
    </div>

    {{-- ── CLIENT TICKETS PANE ─────────────────────────────────────────── --}}
    <div id="pane-client" class="tab-pane">

        <div class="filter-bar">
            <div class="srch-wrap" style="flex:1;min-width:180px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="ct-search" placeholder="Search tickets…" autocomplete="off">
            </div>
            <select class="filter-sel" id="ct-status-filter">
                <option value="">All Status</option>
                <option value="Open">Open</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Closed">Closed</option>
            </select>
            <select class="filter-sel" id="ct-priority-filter">
                <option value="">All Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
            <button class="btn btn-danger" onclick="resetCtFilters()">Reset</button>
        </div>

        <div class="dt-tscroll">
            <table id="client-tickets-table" class="sup-table" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="width:110px">Ticket ID</th>
                        <th style="width:160px">Firm / Client</th>
                        <th>Subject</th>
                        <th style="width:100px">Priority</th>
                        <th style="width:130px">Status</th>
                        <th style="width:130px">Raised On</th>
                        <th style="width:48px"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($clientTickets ?? [] as $ticket)
                @php
                    $tkId    = 'TK-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
                    $pri     = strtolower($ticket->priority ?? 'low');
                    $st      = strtolower($ticket->status ?? 'open');
                    $stLabel = match($st) { 'in_progress'=>'In Progress','resolved'=>'Resolved','closed'=>'Closed',default=>'Open' };
                @endphp
                <tr>
                    <td>
                        <span class="tk-id"
                            data-show="{{ route('support.show', $ticket->id) }}"
                            data-update="{{ route('support.update', $ticket->id) }}"
                            data-notes="{{ route('support.notes', $ticket->id) }}"
                            data-can-update="1">
                            {{ $tkId }}
                        </span>
                    </td>
                    <td>
                        <div class="firm-name">{{ $ticket->company?->company_name ?? '—' }}</div>
                        <div class="firm-user">{{ $ticket->user?->name ?? '—' }}</div>
                    </td>
                    <td style="color:var(--text-1);font-weight:500;max-width:280px">{{ $ticket->subject }}</td>
                    <td><span class="pri-{{ $pri }}">{{ ucfirst($pri) }}</span></td>
                    <td><span class="st-badge st-{{ $st }}">{{ $stLabel }}</span></td>
                    <td>
                        <div class="raised-date">{{ $ticket->created_at->format(active_date_format()) }}</div>
                        <div class="raised-time">{{ $ticket->created_at->format('g:i A') }}</div>
                    </td>
                    <td style="position:relative">
                        <button class="dm-btn" data-menu="cdm-{{ $ticket->id }}">···</button>
                        <div class="dm-popup" id="cdm-{{ $ticket->id }}">
                            <button class="dm-item tk-view-btn"
                                data-show="{{ route('support.show', $ticket->id) }}"
                                data-update="{{ route('support.update', $ticket->id) }}"
                                data-notes="{{ route('support.notes', $ticket->id) }}"
                                data-can-update="1">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View Ticket
                            </button>
                            <button class="dm-item tk-status-btn"
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

    {{-- ── MY TICKETS PANE ─────────────────────────────────────────────── --}}
    <div id="pane-my" class="tab-pane" style="display:none">

        <div class="filter-bar">
            <div class="srch-wrap" style="flex:1;min-width:180px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="mt-search" placeholder="Search tickets…" autocomplete="off">
            </div>
            <select class="filter-sel" id="mt-status-filter">
                <option value="">All Status</option>
                <option value="Open">Open</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Closed">Closed</option>
            </select>
            <select class="filter-sel" id="mt-priority-filter">
                <option value="">All Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
            <button class="btn btn-danger" onclick="resetMtFilters()">Reset</button>
        </div>

        <div class="dt-tscroll">
            <table id="my-tickets-table" class="sup-table" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="width:110px">Ticket ID</th>
                        <th>Subject</th>
                        <th style="width:100px">Priority</th>
                        <th style="width:130px">Status</th>
                        <th style="width:130px">Raised On</th>
                        <th style="width:48px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($myTickets ?? [] as $ticket)
                @php
                    $tkId    = 'TK-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
                    $pri     = strtolower($ticket->priority ?? 'low');
                    $st      = strtolower($ticket->status ?? 'open');
                    $stLabel = match($st) { 'in_progress'=>'In Progress','resolved'=>'Resolved','closed'=>'Closed',default=>'Open' };
                @endphp
                <tr>
                    <td>
                        <span class="tk-id"
                            data-show="{{ route('support.show', $ticket->id) }}"
                            data-update="{{ route('support.update', $ticket->id) }}"
                            data-notes="{{ route('support.notes', $ticket->id) }}"
                            data-can-update="0">
                            {{ $tkId }}
                        </span>
                    </td>
                    <td style="color:var(--text-1);font-weight:500;max-width:340px">{{ $ticket->subject }}</td>
                    <td><span class="pri-{{ $pri }}">{{ ucfirst($pri) }}</span></td>
                    <td><span class="st-badge st-{{ $st }}">{{ $stLabel }}</span></td>
                    <td>
                        <div class="raised-date">{{ $ticket->created_at->format(active_date_format()) }}</div>
                        <div class="raised-time">{{ $ticket->created_at->format('g:i A') }}</div>
                    </td>
                    <td style="position:relative">
                        <button class="dm-btn" data-menu="mdm-{{ $ticket->id }}">···</button>
                        <div class="dm-popup" id="mdm-{{ $ticket->id }}">
                            <button class="dm-item tk-view-btn"
                                data-show="{{ route('support.show', $ticket->id) }}"
                                data-update="{{ route('support.update', $ticket->id) }}"
                                data-notes="{{ route('support.notes', $ticket->id) }}"
                                data-can-update="0">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View Ticket
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── View Ticket Modal ──────────────────────────────────────────────────────── --}}
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
            <div class="vt-meta-col" id="vt-company-col">
                <span class="vt-meta-lbl">Company</span>
                <span class="vt-meta-val" id="vt-company"></span>
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
                    <span class="att-name" id="vt-att-name"></span>
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
            <button class="btn btn-secondary" id="vt-update-status-btn" style="display:none">Update Status</button>
            <button class="btn btn-primary" id="vt-save-notes-btn">Save Notes</button>
        </div>
    </div>
</div>

{{-- ── Update Status Modal ────────────────────────────────────────────────────── --}}
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

{{-- ── New Ticket Modal ────────────────────────────────────────────────────────── --}}
<div id="newTicketModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:540px;width:100%">
        <button class="modal-close" onclick="document.getElementById('newTicketModal').style.display='none'">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="modal-title">New Support Ticket</div>
        <div class="modal-sub">Describe your issue and we'll get back to you as soon as possible.</div>

        <form method="POST" action="{{ route('support.store') }}" enctype="multipart/form-data" id="new-ticket-form" novalidate>
            @csrf
            <div class="mf-field">
                <label class="mf-label" for="ticket-subject">Subject <span style="color:var(--red)">*</span></label>
                <input type="text" id="ticket-subject" name="subject" class="mf-inp"
                       placeholder="Brief description of your issue" value="{{ old('subject') }}" maxlength="100">
                <div id="err-tk-subject" class="tk-err" style="display:none"></div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Priority <span style="color:var(--red)">*</span></label>
                <div style="display:flex;gap:8px;margin-top:4px">
                    @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High'] as $v => $l)
                    <button type="button" class="btn btn-sm priority-toggle" data-value="{{ $v }}"
                        style="flex:1;border:2px solid var(--border);background:#fff;color:var(--text-2);transition:all .15s">{{ $l }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="priority" id="priorityInput" value="{{ old('priority','low') }}">
                <div id="err-tk-priority" class="tk-err" style="display:none"></div>
            </div>
            <div class="mf-field">
                <label class="mf-label" for="ticket-message">Message <span style="color:var(--red)">*</span></label>
                <textarea id="ticket-message" name="message" class="mf-textarea" rows="5"
                          placeholder="Provide as much detail as possible..." maxlength="500">{{ old('message') }}</textarea>
                <div style="font-size:11px;color:var(--text-3);margin-top:3px;text-align:right">
                    <span id="tk-msg-count">0</span>/500 characters
                </div>
                <div id="err-tk-message" class="tk-err" style="display:none"></div>
            </div>
            <div class="mf-field">
                <label class="mf-label" for="ticket-attachment">
                    Attachment <span style="color:var(--text-3);font-weight:400">(optional)</span>
                </label>
                <input type="file" id="ticket-attachment" name="attachment"
                       accept=".jpg,.jpeg,.png,.pdf,.zip"
                       style="display:block;width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;font-size:13px;color:var(--text-2);background:#fafafa;cursor:pointer">
                <span style="font-size:11.5px;color:var(--text-3);margin-top:4px;display:block">Max 10MB. Accepted: jpg, jpeg, png, pdf, zip.</span>
                <div id="err-tk-attachment" class="tk-err" style="display:none"></div>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('newTicketModal').style.display='none'">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="validateAndSubmitTicket()">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>

{{-- PHP data bridge (no Blade inside script blocks) --}}
<div id="support-data" style="display:none"
    data-has-errors="{{ $errors->any() ? '1' : '0' }}"
    data-form-errors="{{ htmlspecialchars(json_encode($errors->all()), ENT_QUOTES, 'UTF-8') }}"
    data-default-priority="{{ old('priority','low') }}"></div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
var _currentUpdateUrl = '';
var _currentNotesUrl  = '';
var _currentTicketId  = '';

var ST_LABEL = { open:'Open', in_progress:'In Progress', resolved:'Resolved', closed:'Closed' };
var ST_CLASS = { open:'st-open', in_progress:'st-in_progress', resolved:'st-resolved', closed:'st-closed' };

$(document).ready(function () {

    // ── Client Tickets DataTable ─────────────────────────────────────────────
    var ctTable = $('#client-tickets-table').DataTable({
        paging:true, pageLength:15, lengthChange:false,
        searching:true, ordering:true, info:true,
        order:[[0,'desc']],
        dom:'t<"dt-footer"ip>',
        columnDefs:[{orderable:false,targets:[1,6]}],
        language:{
            info:'Showing _START_–_END_ of _TOTAL_ tickets',
            infoEmpty:'No tickets found',
            paginate:{previous:'‹',next:'›'},
            emptyTable:
                '<div style="text-align:center;padding:60px 24px 48px">'
                +'<div style="width:72px;height:72px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">'
                +'<svg width="32" height="32" fill="none" stroke="#3b82f6" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
                +'</div>'
                +'<div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:12px">No client tickets at the moment</div>'
                +'<div style="font-size:13.5px;color:#6b7280;line-height:1.75;max-width:480px;margin:0 auto 16px">'
                +'When your clients encounter issues within their portal — login problems, QuickBooks sync errors, or questions about their insights — those tickets are logged here. You can view, track, and respond to each one directly from this section.'
                +'</div>'
                +'<div style="font-size:12px;color:#9ca3af">Client tickets are raised from within the client portal and routed to you automatically.</div>'
                +'</div>',
        },
        drawCallback:function(){document.querySelectorAll('.dm-popup').forEach(function(p){p.classList.remove('open');})}
    });

    document.getElementById('ct-search').addEventListener('keyup',function(){ctTable.search(this.value).draw();});
    document.getElementById('ct-status-filter').addEventListener('change',function(){
        ctTable.column(4).search(this.value?'^'+this.value+'$':'',true,false,true).draw();
    });
    document.getElementById('ct-priority-filter').addEventListener('change',function(){
        ctTable.column(3).search(this.value?'^'+this.value+'$':'',true,false,true).draw();
    });

    // ── My Tickets DataTable ─────────────────────────────────────────────────
    var mtTable = $('#my-tickets-table').DataTable({
        paging:true, pageLength:15, lengthChange:false,
        searching:true, ordering:true, info:true,
        order:[[0,'desc']],
        dom:'t<"dt-footer"ip>',
        columnDefs:[{orderable:false,targets:[5]}],
        language:{
            info:'Showing _START_–_END_ of _TOTAL_ tickets',
            infoEmpty:'No tickets found',
            paginate:{previous:'‹',next:'›'},
            emptyTable:
                '<div style="text-align:center;padding:60px 24px 48px">'
                +'<div style="width:72px;height:72px;border-radius:50%;background:#fff3ec;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">'
                +'<svg width="32" height="32" fill="none" stroke="#f97316" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>'
                +'</div>'
                +'<div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:12px">You have not raised any tickets yet</div>'
                +'<div style="font-size:13.5px;color:#6b7280;line-height:1.75;max-width:480px;margin:0 auto 24px">'
                +'Need help from the CFO Insights team? Raise a ticket here for any platform issue, billing query, branding assistance, or configuration support.'
                +'</div>'
                +'<button class="open-new-ticket-btn" style="background:#e8521a;color:#fff;border:none;padding:11px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">+ Raise a Ticket to Insights</button>'
                +'</div>',
        },
        drawCallback:function(){document.querySelectorAll('.dm-popup').forEach(function(p){p.classList.remove('open');})}
    });

    document.getElementById('mt-search').addEventListener('keyup',function(){mtTable.search(this.value).draw();});
    document.getElementById('mt-status-filter').addEventListener('change',function(){
        mtTable.column(3).search(this.value?'^'+this.value+'$':'',true,false,true).draw();
    });
    document.getElementById('mt-priority-filter').addEventListener('change',function(){
        mtTable.column(2).search(this.value?'^'+this.value+'$':'',true,false,true).draw();
    });

    // ── Init: reopen modal if validation error ───────────────────────────────
    var el = document.getElementById('support-data');
    if (el) {
        var priority = el.dataset.defaultPriority || 'low';
        var hasErr   = el.dataset.hasErrors === '1';
        var errs     = JSON.parse(el.dataset.formErrors || '[]');
        var btn = document.querySelector('.priority-toggle[data-value="' + priority + '"]');
        if (btn) selectPriority(btn);
        if (hasErr) {
            document.getElementById('newTicketModal').style.display = 'flex';
            errs.forEach(function(msg){showToast(msg,'error');});
        }
    }
});

// ── Tab switching ────────────────────────────────────────────────────────────
document.querySelectorAll('.sup-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.sup-tab').forEach(function(t){t.classList.remove('active');});
        document.querySelectorAll('.tab-pane').forEach(function(p){p.style.display='none';});
        this.classList.add('active');
        document.getElementById('pane-' + this.dataset.tab).style.display = 'block';
        document.querySelectorAll('.dm-popup').forEach(function(p){p.classList.remove('open');});
    });
});

// ── Priority toggle ──────────────────────────────────────────────────────────
function selectPriority(btn) {
    document.querySelectorAll('.priority-toggle').forEach(function(b){
        b.style.borderColor='var(--border)';b.style.background='#fff';
        b.style.color='var(--text-2)';b.style.fontWeight='400';
    });
    btn.style.borderColor='var(--blue)';
    btn.style.background='rgba(26,106,168,.07)';
    btn.style.color='var(--blue)';
    btn.style.fontWeight='600';
    document.getElementById('priorityInput').value = btn.dataset.value;
}

// ── Global click handler ─────────────────────────────────────────────────────
// Note: three-dot toggle (.dm-btn / [data-menu]) is handled by the layout's
// global handler which uses position:fixed. Do NOT duplicate it here.
document.addEventListener('click', function(e) {
    // View ticket (TK-ID link or View Ticket menu item)
    var viewBtn = e.target.closest('.tk-id, .tk-view-btn');
    if (viewBtn) {
        document.querySelectorAll('.dm-popup.open').forEach(function(p){p.classList.remove('open');});
        var canUpdate = viewBtn.dataset.canUpdate === '1';
        openViewTicket(viewBtn.dataset.show, viewBtn.dataset.update, viewBtn.dataset.notes, canUpdate);
        return;
    }
    // Update Status menu item
    var stBtn = e.target.closest('.tk-status-btn');
    if (stBtn) {
        document.querySelectorAll('.dm-popup.open').forEach(function(p){p.classList.remove('open');});
        openUpdateStatus(stBtn.dataset.update, stBtn.dataset.tk, stBtn.dataset.status);
        return;
    }
    // Empty-state "Raise a Ticket" button
    if (e.target.closest('.open-new-ticket-btn')) {
        document.getElementById('newTicketModal').style.display = 'flex';
        return;
    }
    // Priority toggle
    var priBtn = e.target.closest('.priority-toggle');
    if (priBtn) { selectPriority(priBtn); return; }
});

document.getElementById('btnNewTicket').addEventListener('click', function() {
    // Switch to My Tickets tab first
    document.querySelectorAll('.sup-tab').forEach(function(t){t.classList.remove('active');});
    document.querySelectorAll('.tab-pane').forEach(function(p){p.style.display='none';});
    document.querySelector('[data-tab="my"]').classList.add('active');
    document.getElementById('pane-my').style.display = 'block';
    document.getElementById('newTicketModal').style.display = 'flex';
});

// ── View Ticket ──────────────────────────────────────────────────────────────
function openViewTicket(showUrl, updateUrl, notesUrl, canUpdate) {
    _currentUpdateUrl = updateUrl;
    _currentNotesUrl  = notesUrl;

    fetch(showUrl, {headers:{Accept:'application/json','X-CSRF-TOKEN':csrf}})
        .then(function(r){return r.json();})
        .then(function(t) {
            _currentTicketId = t.ticket_id;

            document.getElementById('vt-id').textContent      = t.ticket_id;
            document.getElementById('vt-created').textContent = 'Submitted on ' + t.created_at;
            document.getElementById('vt-raised').textContent  = t.created_at;
            document.getElementById('vt-by').textContent      = t.submitted_by;
            document.getElementById('vt-status-text').textContent = ST_LABEL[t.status] || t.status;
            document.getElementById('vt-subject').textContent = t.subject;
            document.getElementById('vt-message').textContent = t.message;
            document.getElementById('vt-notes').value         = t.notes || '';

            // Company column (hide if own ticket)
            var compCol = document.getElementById('vt-company-col');
            if (t.company_name) {
                document.getElementById('vt-company').textContent = t.company_name;
                compCol.style.display = '';
            } else {
                compCol.style.display = 'none';
            }

            // Priority badge
            var priEl = document.getElementById('vt-pri-badge');
            priEl.className   = 'badge ' + (t.priority==='high'?'b-red':t.priority==='medium'?'b-amber':'b-gray');
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

            // Show/hide Update Status button in modal
            var usBtn = document.getElementById('vt-update-status-btn');
            usBtn.style.display = canUpdate ? '' : 'none';
            usBtn.onclick = function() {
                openUpdateStatus(t.update_url, t.ticket_id, t.status);
            };

            document.getElementById('viewTicketModal').style.display = 'flex';
        })
        .catch(function(){showToast('Failed to load ticket.','error');});
}

// ── Save Notes ───────────────────────────────────────────────────────────────
document.getElementById('vt-save-notes-btn').addEventListener('click', function() {
    if (!_currentNotesUrl) return;
    var notes = document.getElementById('vt-notes').value;
    fetch(_currentNotesUrl, {
        method:'PATCH',
        headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':csrf},
        body:JSON.stringify({notes:notes})
    })
    .then(function(r){return r.json();})
    .then(function(data){
        if (data.success) showToast('Notes saved.','success');
        else showToast('Failed to save notes.','error');
    })
    .catch(function(){showToast('Failed to save notes.','error');});
});

// ── Update Status ─────────────────────────────────────────────────────────────
function openUpdateStatus(updateUrl, tkId, currentStatus) {
    _currentUpdateUrl = updateUrl;
    document.getElementById('us-sub').textContent      = 'Changing status for ' + tkId;
    document.getElementById('us-cur-status').value     = ST_LABEL[currentStatus] || currentStatus;
    document.getElementById('us-new-status').value     = '';
    document.getElementById('us-note').value           = '';
    document.getElementById('viewTicketModal').style.display   = 'none';
    document.getElementById('updateStatusModal').style.display = 'flex';
}

// ── New Ticket Validation ────────────────────────────────────────────────────
function validateAndSubmitTicket() {
    var ok = true;

    function tkErr(id, msg) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = msg || '';
        el.style.display = msg ? 'flex' : 'none';
    }
    function tkMark(el, invalid) {
        if (!el) return;
        if (invalid) el.classList.add('tk-invalid');
        else el.classList.remove('tk-invalid');
    }

    // Subject
    var subEl  = document.getElementById('ticket-subject');
    var subVal = subEl ? subEl.value.trim() : '';
    var subRx  = /^[a-zA-Z0-9\s.,\-_\/&()]+$/;
    if (!subVal) {
        tkErr('err-tk-subject', 'Subject is required.'); tkMark(subEl, true); ok = false;
    } else if (subVal.length < 3) {
        tkErr('err-tk-subject', 'Subject must be at least 3 characters long.'); tkMark(subEl, true); ok = false;
    } else if (subVal.length > 100) {
        tkErr('err-tk-subject', 'Subject cannot exceed 100 characters.'); tkMark(subEl, true); ok = false;
    } else if (!subRx.test(subVal)) {
        tkErr('err-tk-subject', 'Subject contains invalid characters.'); tkMark(subEl, true); ok = false;
    } else {
        tkErr('err-tk-subject', ''); tkMark(subEl, false);
        if (subEl) subEl.value = subVal;
    }

    // Priority (already selected by default so normally always passes)
    var priVal = document.getElementById('priorityInput') ? document.getElementById('priorityInput').value : '';
    if (!priVal || ['low','medium','high'].indexOf(priVal) === -1) {
        tkErr('err-tk-priority', 'Please select a priority.'); ok = false;
    } else {
        tkErr('err-tk-priority', '');
    }

    // Message
    var msgEl  = document.getElementById('ticket-message');
    var msgVal = msgEl ? msgEl.value.trim() : '';
    if (!msgVal) {
        tkErr('err-tk-message', 'Description is required.'); tkMark(msgEl, true); ok = false;
    } else if (msgVal.length < 10) {
        tkErr('err-tk-message', 'Description must be at least 10 characters long.'); tkMark(msgEl, true); ok = false;
    } else if (msgVal.length > 500) {
        tkErr('err-tk-message', 'Description cannot exceed 500 characters.'); tkMark(msgEl, true); ok = false;
    } else {
        tkErr('err-tk-message', ''); tkMark(msgEl, false);
        if (msgEl) msgEl.value = msgVal;
    }

    // Attachment (optional)
    var attEl = document.getElementById('ticket-attachment');
    if (attEl && attEl.files && attEl.files.length > 0) {
        var file     = attEl.files[0];
        var extRx    = /\.(jpg|jpeg|png|pdf|zip)$/i;
        var nameRx   = /^[a-zA-Z0-9\s.\-_]+$/;
        var maxBytes = 10 * 1024 * 1024;
        var baseName = file.name.replace(/\.[^.]+$/, '');

        if (!extRx.test(file.name)) {
            tkErr('err-tk-attachment', 'Invalid file format. Allowed formats are JPG, JPEG, PNG, PDF, and ZIP.'); ok = false;
        } else if (file.size > maxBytes) {
            tkErr('err-tk-attachment', 'File size cannot exceed 10 MB.'); ok = false;
        } else if (!nameRx.test(baseName)) {
            tkErr('err-tk-attachment', 'Attachment file name contains invalid characters.'); ok = false;
        } else {
            tkErr('err-tk-attachment', '');
        }
    } else {
        tkErr('err-tk-attachment', '');
    }

    if (ok) {
        document.getElementById('new-ticket-form').submit();
    }
}

// Message character counter
(function() {
    var msgEl   = document.getElementById('ticket-message');
    var counter = document.getElementById('tk-msg-count');
    if (!msgEl || !counter) return;
    function updateCount() { counter.textContent = msgEl.value.length; }
    msgEl.addEventListener('input', updateCount);
    updateCount();
})();

document.getElementById('us-submit-btn').addEventListener('click', function() {
    var newStatus = document.getElementById('us-new-status').value;
    if (!newStatus) { showToast('Please select a new status.','error'); return; }

    fetch(_currentUpdateUrl, {
        method:'PATCH',
        headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':csrf},
        body:JSON.stringify({status:newStatus,note:document.getElementById('us-note').value})
    })
    .then(function(r){return r.json();})
    .then(function(data){
        if (data.success) {
            showToast(data.ticket_id + ' status updated.','success');
            document.getElementById('updateStatusModal').style.display = 'none';
            setTimeout(function(){location.reload();},1200);
        } else {
            showToast('Update failed.','error');
        }
    })
    .catch(function(){showToast('Update failed.','error');});
});

@if(session('ticket_success'))
Swal.fire({
    icon: 'success',
    title: 'Ticket Submitted',
    text: 'Your request has been received. Our team will review it and respond shortly.',
    confirmButtonText: 'View Tickets',
    confirmButtonColor: '#f06b21',
    allowOutsideClick: false,
});
@endif

function resetCtFilters() {
    document.getElementById('ct-search').value         = '';
    document.getElementById('ct-status-filter').value  = '';
    document.getElementById('ct-priority-filter').value = '';
    if (window.$ && $('#client-tickets-table').length) {
        $('#client-tickets-table').DataTable().search('').columns().search('').draw();
    }
}

function resetMtFilters() {
    document.getElementById('mt-search').value         = '';
    document.getElementById('mt-status-filter').value  = '';
    document.getElementById('mt-priority-filter').value = '';
    if (window.$ && $('#my-tickets-table').length) {
        $('#my-tickets-table').DataTable().search('').columns().search('').draw();
    }
}
</script>
@endpush
