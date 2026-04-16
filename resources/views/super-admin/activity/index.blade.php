@extends('layouts.superadmin.app')
@section('title','Activity Log')
@section('page-title','Activity Log')

@section('content')
<div class="ph">
    <div>
        <h1>Activity Log</h1>
        <p>All platform-level events and actions</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </button>
    </div>
</div>

<div class="tc">
    <div class="tc-head">
        <div class="toolbar">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" placeholder="Search events…">
            </div>
            <select class="sel">
                <option>All Events</option>
                <option>Login</option>
                <option>Company Created</option>
                <option>Payment</option>
                <option>Settings Changed</option>
            </select>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Event</th>
                <th>User</th>
                <th>IP Address</th>
                <th>Date &amp; Time</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4" style="text-align:center;color:var(--text-3);padding:40px">Activity logging will be implemented once the spatie/laravel-activitylog package is configured.</td></tr>
        </tbody>
    </table>
</div>
@endsection
