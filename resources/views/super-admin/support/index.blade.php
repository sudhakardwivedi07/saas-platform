@extends('layouts.superadmin.app')
@section('title','Support')
@section('page-title','Support')

@section('content')
<div class="ph">
    <div>
        <h1>Support</h1>
        <p>Manage platform support tickets</p>
    </div>
</div>

<div class="info-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="info-banner-txt">Support ticket management is coming soon. This section will display inbound tickets from firms, SLA tracking, and response management.</div>
</div>

<div class="tc">
    <div class="tc-head">
        <div style="font-size:14px;font-weight:600">Open Tickets</div>
        <span class="badge b-gray">0 open</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Firm</th>
                <th>Subject</th>
                <th>Priority</th>
                <th>Status</th>
                <th>SLA</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:40px">No open tickets</td></tr>
        </tbody>
    </table>
</div>
@endsection
