@extends('layouts.superadmin.app')
@section('title', $ticketId . ' — Support')
@section('page-title', 'Support')

@php
    $status   = $ticket->status;
    $priority = $ticket->priority;

    $statusLabel = ['open'=>'Open','in_progress'=>'In Progress','on_hold'=>'On Hold',
                    'sla_breach'=>'SLA Breach','resolved'=>'Resolved','closed'=>'Closed'][$status]
                    ?? ucwords(str_replace('_',' ',$status));
@endphp

@section('content')

{{-- ── Page header ──────────────────────────────────────────────────── --}}
<div class="ph">
    <div>
        <h1>{{ $ticketId }}</h1>
        <p>{{ $ticket->company->company_name ?? '-' }} &mdash; Support Ticket Detail</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.support') }}" class="btn-outline"
           style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-size:13.5px;padding:8px 16px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Support
        </a>
    </div>
</div>

{{-- ── Main card ─────────────────────────────────────────────────────── --}}
<div class="tc" style="overflow:visible">

    {{-- Ticket identity bar --}}
    <div style="display:flex;align-items:center;gap:16px;padding:20px 24px;border-bottom:1px solid var(--border)">
        <div style="width:44px;height:44px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </div>
        <div>
            <div style="font-size:18px;font-weight:700;color:var(--text-1)">{{ $ticketId }}</div>
            <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
                <span style="font-size:13px;color:var(--text-3)">{{ $ticket->company->company_name ?? '-' }}</span>
                @if($priority === 'high')
                    <span class="badge b-red" style="font-size:11.5px">High</span>
                @elseif($priority === 'medium')
                    <span class="badge b-amber" style="font-size:11.5px">Medium</span>
                @else
                    <span class="badge b-gray" style="font-size:11.5px">Low</span>
                @endif
                @if($status === 'open')
                    <span class="badge b-blue" style="font-size:11.5px">Open</span>
                @elseif($status === 'in_progress')
                    <span class="badge b-amber" style="font-size:11.5px">In Progress</span>
                @elseif($status === 'on_hold')
                    <span class="badge b-gray" style="font-size:11.5px">On Hold</span>
                @elseif($status === 'sla_breach')
                    <span style="font-size:11.5px;font-weight:600;color:var(--red)">SLA Breach</span>
                @elseif($status === 'resolved')
                    <span class="badge b-green" style="font-size:11.5px">Resolved</span>
                @else
                    <span class="badge b-gray" style="font-size:11.5px">{{ $statusLabel }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Meta bar --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);background:#f8fafc;border-bottom:1px solid var(--border)">
        <div style="padding:14px 24px;border-right:1px solid var(--border)">
            <div style="font-size:10.5px;font-weight:600;letter-spacing:.07em;color:var(--text-3);text-transform:uppercase;margin-bottom:5px">Raised On</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">{{ $ticket->created_at->format('M d, Y g:i A') }}</div>
        </div>
        <div style="padding:14px 24px;border-right:1px solid var(--border)">
            <div style="font-size:10.5px;font-weight:600;letter-spacing:.07em;color:var(--text-3);text-transform:uppercase;margin-bottom:5px">Assigned To</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">{{ $ticket->assigned_to ?: '—' }}</div>
        </div>
        <div style="padding:14px 24px">
            <div style="font-size:10.5px;font-weight:600;letter-spacing:.07em;color:var(--text-3);text-transform:uppercase;margin-bottom:5px">Status</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)" id="show-status-text">{{ $statusLabel }}</div>
        </div>
    </div>

    {{-- Body --}}
    <div style="padding:28px 24px">

        {{-- Subject --}}
        <div style="margin-bottom:22px">
            <div style="font-size:10.5px;font-weight:600;letter-spacing:.07em;color:var(--text-3);text-transform:uppercase;margin-bottom:6px">Subject</div>
            <div style="font-size:15px;font-weight:700;color:var(--text-1)">{{ $ticket->subject }}</div>
        </div>

        {{-- Description --}}
        <div style="margin-bottom:28px">
            <div style="font-size:10.5px;font-weight:600;letter-spacing:.07em;color:var(--text-3);text-transform:uppercase;margin-bottom:8px">Description</div>
            <div style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:16px 20px;font-size:13.5px;color:var(--text-2);line-height:1.75;white-space:pre-wrap">{{ $ticket->message }}</div>
        </div>

        {{-- Attachments --}}
        @if(count($attachments))
        <div style="margin-bottom:28px">
            <div style="font-size:10.5px;font-weight:600;letter-spacing:.07em;color:var(--text-3);text-transform:uppercase;margin-bottom:10px">Attachments</div>
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($attachments as $file)
                <div style="display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:13px 18px">
                    <div style="display:flex;align-items:center;gap:13px">
                        @if($file['ext'] === 'pdf')
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.6"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        @elseif(in_array($file['ext'],['csv','xls','xlsx']))
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                        @else
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.6"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        @endif
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">{{ $file['name'] }}</div>
                            @if($file['size'])
                            <div style="font-size:12px;color:var(--text-3);margin-top:2px">{{ $file['size'] }}</div>
                            @endif
                        </div>
                    </div>
                    <a href="{{ $file['url'] }}" download
                       style="font-size:13px;font-weight:500;color:var(--text-2);border:1px solid var(--border);border-radius:7px;padding:6px 16px;text-decoration:none;background:#fff"
                       onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">Download</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Internal Notes --}}
        <div>
            <div style="font-size:14px;font-weight:700;color:var(--text-1);margin-bottom:10px">Internal Notes</div>
            <textarea id="notes-area" rows="5"
                placeholder="Add internal notes or resolution steps visible only to the CFO Insights team..."
                style="width:100%;padding:13px 16px;border:1px solid var(--border);border-radius:10px;font-size:13.5px;color:var(--text-2);font-family:inherit;resize:vertical;box-sizing:border-box;line-height:1.65">{{ $ticket->notes }}</textarea>
        </div>

    </div>

    {{-- Footer --}}
    <div style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid var(--border);background:#fafafa">
        <a href="{{ route('superadmin.support') }}" class="btn-outline"
           style="padding:9px 20px;font-size:13.5px;text-decoration:none">Close</a>
        <button id="show-update-status-btn" class="btn-outline" style="padding:9px 20px;font-size:13.5px">Update Status</button>
        <button id="save-notes-btn" class="btn-primary" style="padding:9px 22px;font-size:13.5px">Save Notes</button>
    </div>
</div>

{{-- ── Update Status Modal ──────────────────────────────────────────── --}}
<div id="us-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:28px 28px 24px;width:100%;max-width:440px;position:relative;box-shadow:0 8px 40px rgba(0,0,0,.18)">
        <button id="us-close" style="position:absolute;top:14px;right:14px;background:#f1f5f9;border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;font-size:16px;color:#64748b;display:flex;align-items:center;justify-content:center">✕</button>
        <h3 style="margin:0 0 3px;font-size:17px;font-weight:700;color:var(--text-1)">Update Status</h3>
        <p style="margin:0 0 20px;font-size:13px;color:var(--text-3)">Changing status for {{ $ticketId }}</p>

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px">Current Status</label>
        <input type="text" readonly value="{{ $statusLabel }}"
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;background:#f8fafc;font-size:13.5px;color:var(--text-3);box-sizing:border-box;margin-bottom:16px">

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px">New Status <span style="color:var(--red)">*</span></label>
        <select id="us-new-status"
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:13.5px;color:var(--text-1);background:#fff;box-sizing:border-box;margin-bottom:4px;cursor:pointer">
            <option value="">Select new status</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="on_hold">On Hold</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
        </select>
        <span id="us-err" style="display:none;font-size:12px;color:var(--red);margin-bottom:12px;display:block"></span>

        <label style="font-size:13px;font-weight:500;color:var(--text-2);display:block;margin-bottom:6px;margin-top:12px">Note (optional)</label>
        <textarea id="us-note" rows="3" placeholder="Add a note about this status change..."
            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:13.5px;color:var(--text-1);resize:vertical;box-sizing:border-box;font-family:inherit"></textarea>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
            <button id="us-cancel" class="btn-outline" style="padding:9px 20px;font-size:13.5px">Cancel</button>
            <button id="us-submit" class="btn-primary" style="padding:9px 22px;font-size:13.5px">Update Status</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var CSRF     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var ticketId = {{ $ticket->id }};

    var statusLabels = {
        open:'Open', in_progress:'In Progress', on_hold:'On Hold',
        sla_breach:'SLA Breach', resolved:'Resolved', closed:'Closed'
    };

    /* ── Save Notes ──────────────────────────────────────────────── */
    document.getElementById('save-notes-btn').addEventListener('click', function () {
        var btn   = this;
        var notes = document.getElementById('notes-area').value;
        btn.disabled    = true;
        btn.textContent = 'Saving…';

        fetch('{{ route("superadmin.support.save-notes", ":id") }}'.replace(':id', ticketId), {
            method:  'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ notes: notes })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                btn.textContent = 'Saved!';
                setTimeout(function () { btn.textContent = 'Save Notes'; }, 1800);
            }
        })
        .catch(function () { btn.textContent = 'Save Notes'; })
        .finally(function () { btn.disabled = false; });
    });

    /* ── Update Status Modal ─────────────────────────────────────── */
    var usModal  = document.getElementById('us-modal');
    var usNewSt  = document.getElementById('us-new-status');
    var usNote   = document.getElementById('us-note');
    var usErr    = document.getElementById('us-err');
    var usSubmit = document.getElementById('us-submit');

    function openModal()  { usModal.style.display = 'flex'; }
    function closeModal() { usModal.style.display = 'none'; }

    document.getElementById('show-update-status-btn').addEventListener('click', function () {
        usNewSt.value = ''; usNote.value = '';
        usErr.style.display = 'none';
        openModal();
    });
    document.getElementById('us-close').addEventListener('click',  closeModal);
    document.getElementById('us-cancel').addEventListener('click', closeModal);
    usModal.addEventListener('click', function (e) { if (e.target === usModal) closeModal(); });

    usSubmit.addEventListener('click', function () {
        var newSt = usNewSt.value;
        if (!newSt) {
            usErr.textContent   = 'Please select a new status.';
            usErr.style.display = 'block';
            return;
        }
        usErr.style.display  = 'none';
        usSubmit.disabled    = true;
        usSubmit.textContent = 'Updating…';

        fetch('{{ route("superadmin.support.update-status", ":id") }}'.replace(':id', ticketId), {
            method:  'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ status: newSt, note: usNote.value.trim() })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                document.getElementById('show-status-text').textContent = statusLabels[data.status] || data.status;
                closeModal();
            }
        })
        .finally(function () {
            usSubmit.disabled    = false;
            usSubmit.textContent = 'Update Status';
        });
    });
})();
</script>
@endpush
