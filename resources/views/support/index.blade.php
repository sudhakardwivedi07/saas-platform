@extends('layouts.app')

@section('page-title', 'Support')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-1);margin:0 0 2px 0;">Support</h1>
        <p style="font-size:0.875rem;color:var(--text-2);margin:0;">Submit and track support requests for your account.</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-primary" onclick="document.getElementById('newTicketModal').style.display='flex'">
            + New Ticket
        </button>
    </div>
</div>

{{-- Info Banner --}}
<div class="info-banner" style="margin-bottom:1.5rem;">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--blue);">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="info-banner-txt">
        Need urgent help? Email us at
        <a href="mailto:support@example.com" style="color:var(--blue);font-weight:600;text-decoration:none;">support@example.com</a>
        or call <strong>1-800-000-0000</strong> (Mon–Fri, 9am–6pm EST). Average response time is under 4 hours.
    </span>
</div>

{{-- Tickets Table --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <div>
            <div class="card-title">Your Tickets</div>
            <div class="card-sub">All support requests submitted by your team.</div>
        </div>
    </div>

    @php $tickets = $tickets ?? collect(); @endphp

    @if($tickets->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;">
            <div style="width:56px;height:56px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <p style="font-size:0.9375rem;font-weight:600;color:var(--text-1);margin:0 0 4px 0;">No tickets yet</p>
            <p style="font-size:0.8125rem;color:var(--text-2);margin:0 0 1rem 0;">You have not submitted any support requests.</p>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('newTicketModal').style.display='flex'">
                + New Ticket
            </button>
        </div>
    @else
        <div class="tc">
            <table>
                <thead>
                    <tr class="tc-head">
                        <th style="width:80px;">ID</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td style="font-size:0.8125rem;color:var(--text-2);font-family:monospace;">
                            #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="font-weight:500;color:var(--text-1);">{{ $ticket->subject }}</td>
                        <td>
                            @php
                                $priority = strtolower($ticket->priority ?? 'low');
                                $priClass = match($priority) {
                                    'high'   => 'b-red',
                                    'medium' => 'b-amber',
                                    default  => 'b-gray',
                                };
                            @endphp
                            <span class="badge {{ $priClass }}">{{ ucfirst($priority) }}</span>
                        </td>
                        <td>
                            @php
                                $tStatus = strtolower($ticket->status ?? 'open');
                                $stClass = match($tStatus) {
                                    'open'       => 'b-blue',
                                    'in_progress'=> 'b-amber',
                                    'resolved'   => 'b-green',
                                    'closed'     => 'b-gray',
                                    default      => 'b-gray',
                                };
                            @endphp
                            <span class="badge {{ $stClass }}">{{ ucwords(str_replace('_', ' ', $tStatus)) }}</span>
                        </td>
                        <td style="font-size:0.8125rem;color:var(--text-2);">
                            {{ $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at)->format('M j, Y') : '—' }}
                        </td>
                        <td>
                            <a href="#" class="btn btn-ghost btn-xs" style="text-decoration:none;">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- New Ticket Modal --}}
<div id="newTicketModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="width:100%;max-width:540px;">
        <button class="modal-close" onclick="document.getElementById('newTicketModal').style.display='none'" aria-label="Close">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="modal-title">New Support Ticket</div>
        <div class="modal-sub">Describe your issue and we'll get back to you as soon as possible.</div>

        <form method="POST" action="{{ route('support.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mf-field">
                <label class="mf-label" for="ticket-subject">Subject</label>
                <input type="text" id="ticket-subject" name="subject" class="mf-inp"
                       placeholder="Brief description of your issue" required>
            </div>

            <div class="mf-field">
                <label class="mf-label">Priority</label>
                <div style="display:flex;gap:8px;margin-top:4px;" id="priorityToggleGroup">
                    @foreach(['low' => ['b-gray','Low'], 'medium' => ['b-amber','Medium'], 'high' => ['b-red','High']] as $val => $meta)
                    <button type="button"
                        class="btn btn-sm priority-toggle"
                        data-value="{{ $val }}"
                        style="flex:1;border:2px solid var(--border);background:#fff;color:var(--text-2);transition:all .15s;"
                        onclick="selectPriority(this)">
                        {{ $meta[1] }}
                    </button>
                    @endforeach
                </div>
                <input type="hidden" name="priority" id="priorityInput" value="low">
            </div>

            <div class="mf-field">
                <label class="mf-label" for="ticket-message">Message</label>
                <textarea id="ticket-message" name="message" class="mf-textarea" rows="5"
                          placeholder="Provide as much detail as possible..." required></textarea>
            </div>

            <div class="mf-field">
                <label class="mf-label" for="ticket-attachment">Attachment <span style="color:var(--text-3);font-weight:400;">(optional)</span></label>
                <input type="file" id="ticket-attachment" name="attachment"
                       style="display:block;width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;font-size:0.875rem;color:var(--text-2);background:#fafafa;cursor:pointer;">
                <span style="font-size:0.75rem;color:var(--text-3);margin-top:4px;display:block;">Max 10MB. Accepted: jpg, png, pdf, zip.</span>
            </div>

            <div class="mf-foot">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('newTicketModal').style.display='none'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectPriority(btn) {
    document.querySelectorAll('.priority-toggle').forEach(function(b) {
        b.style.borderColor = 'var(--border)';
        b.style.background = '#fff';
        b.style.color = 'var(--text-2)';
        b.style.fontWeight = '400';
    });
    btn.style.borderColor = 'var(--blue)';
    btn.style.background = 'rgba(26,106,168,0.07)';
    btn.style.color = 'var(--blue)';
    btn.style.fontWeight = '600';
    document.getElementById('priorityInput').value = btn.dataset.value;
}
// Activate "low" by default on load
document.addEventListener('DOMContentLoaded', function() {
    var defaultBtn = document.querySelector('.priority-toggle[data-value="low"]');
    if (defaultBtn) selectPriority(defaultBtn);
});
</script>

@endsection
