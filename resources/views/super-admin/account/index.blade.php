@extends('layouts.superadmin.app')
@section('title','My Account')
@section('page-title','My Account')

@section('content')
<div class="ph">
    <div>
        <h1>My Account</h1>
        <p>Manage your SuperAdmin profile &amp; platform settings</p>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-profile">Profile</button>
    <button class="tab-btn" data-tab="tab-global">Global Settings</button>
</div>

<div id="tab-profile" class="tab-panel active">
    <div class="acc-card" style="max-width:620px">
        <div class="acc-title">Profile Information</div>

        {{-- Avatar --}}
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:22px">
            <div style="width:52px;height:52px;border-radius:12px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff">
                {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
            </div>
            <div>
                <div style="font-size:14px;font-weight:600">{{ auth()->user()->name }}</div>
                <div style="font-size:12px;color:var(--text-3)">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.account.update') }}">
            @csrf
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">Full Name</label>
                    <input type="text" name="name" class="f-inp" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="f-group">
                    <label class="f-label">Email Address</label>
                    <input type="email" name="email" class="f-inp" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
            </div>
            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">New Password <span style="color:var(--text-3);font-weight:400">(leave blank to keep current)</span></label>
                    <input type="password" name="password" class="f-inp" placeholder="••••••••">
                </div>
                <div class="f-group">
                    <label class="f-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="f-inp" placeholder="••••••••">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<div id="tab-global" class="tab-panel">
    <div class="acc-card" style="max-width:620px">
        <div class="acc-title">Global Platform Settings</div>
        <p style="font-size:13px;color:var(--text-3);margin-bottom:18px">Configure platform-wide settings accessible from <a href="{{ route('superadmin.settings') }}" style="color:var(--blue)">Global Settings →</a></p>
        <div style="display:flex;gap:14px;flex-wrap:wrap">
            <a href="{{ route('superadmin.settings') }}" class="btn btn-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                Go to Global Settings
            </a>
            <a href="{{ route('superadmin.payment_settings') }}" class="btn btn-ghost">
                Payment Gateway
            </a>
        </div>
    </div>
</div>
@endsection
