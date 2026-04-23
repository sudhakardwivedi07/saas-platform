@extends('layouts.app')
@section('page-title', 'My Account')

@push('styles')
<style>
/* ── Page Header + Tab Bar ───────────────────────────── */
.ua-page-header {
    background: #fff;
    border-bottom: 1px solid var(--border);
    margin: -24px -28px 24px;
    padding: 18px 28px 0;
}
.ua-page-header h1 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-1);
    margin-bottom: 14px;
}
.ua-tabs {
    display: flex;
    gap: 0;
}
.ua-tab {
    padding: 14px 22px;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-3);
    cursor: pointer;
    border: none;
    background: transparent;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color .15s, border-color .15s;
    font-family: inherit;
    white-space: nowrap;
}
.ua-tab:hover { color: var(--text-1); }
.ua-tab.active { color: var(--orange); border-bottom-color: var(--orange); font-weight: 600; }
.ua-tab-panel { display: none; }
.ua-tab-panel.active { display: block; }

/* ── Subscription ───────────────────────────────────── */
.sub-status-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 22px 24px;
    margin-bottom: 20px;
    box-shadow: var(--sh-sm);
}
.sub-status-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}
.sub-status-title { font-size: 15px; font-weight: 700; color: var(--text-1); }
.sub-billing-badge {
    font-size: 11.5px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1.5px solid var(--orange);
    color: var(--orange);
}
.sub-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
@media (max-width: 700px) { .sub-info-grid { grid-template-columns: 1fr; } }
.sub-info-box {
    background: #fafbfc;
    border: 1px solid var(--border);
    border-radius: 9px;
    padding: 14px 18px;
}
.sub-info-lbl { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: var(--text-3); margin-bottom: 6px; }
.sub-info-val { font-size: 15px; font-weight: 600; color: var(--text-1); }

.pro-plan-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 0;
    overflow: hidden;
    box-shadow: var(--sh-sm);
}
.pro-plan-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 22px 24px 18px;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 12px;
}
.pro-plan-title { font-size: 16px; font-weight: 700; color: var(--text-1); margin-bottom: 3px; }
.pro-plan-sub { font-size: 13px; color: var(--text-3); }
.pro-plan-price { text-align: right; }
.pro-plan-amount { font-size: 24px; font-weight: 700; color: var(--blue); }
.pro-plan-amount span { font-size: 14px; font-weight: 500; color: var(--text-3); }
.pro-plan-billing { font-size: 12px; color: var(--text-3); margin-top: 3px; }
.pro-plan-body {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 0;
}
@media (max-width: 700px) { .pro-plan-body { grid-template-columns: 1fr; } }
.pro-plan-includes {
    padding: 22px 24px;
    border-right: 1px solid var(--border);
}
.pro-plan-actions {
    padding: 22px 24px;
}
.pro-section-lbl {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-1);
    margin-bottom: 14px;
}
.pro-feat-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.pro-feat-list li { display: flex; align-items: flex-start; gap: 9px; font-size: 13px; color: var(--text-1); }
.pro-feat-check { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; color: var(--green); }
.pro-action-hint { font-size: 12.5px; color: var(--text-3); line-height: 1.6; margin-bottom: 18px; }
.pro-plan-footer {
    display: flex;
    justify-content: space-between;
    padding: 10px 24px;
    border-top: 1px solid var(--border);
    background: #fafbfc;
}
.pro-plan-footer span { font-size: 11.5px; color: var(--text-3); }

/* ── Support ─────────────────────────────────────────── */
.sup-ph {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 10px;
}
.sup-ph-title { font-size: 17px; font-weight: 700; color: var(--text-1); margin-bottom: 3px; }
.sup-ph-sub { font-size: 13px; color: var(--text-3); }
.sup-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    background: #fafbfc;
}
.sup-date-inp {
    height: 34px;
    border: 1px solid var(--border);
    border-radius: 7px;
    padding: 0 10px 0 32px;
    font-size: 13px;
    font-family: inherit;
    color: var(--text-2);
    background: #fff;
    outline: none;
    min-width: 160px;
}
.sup-date-inp:focus { border-color: var(--blue); }
.date-inp-wrap { position: relative; }
.date-inp-wrap svg { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); pointer-events: none; stroke: var(--text-3); }

/* Priority badges */
.pri-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.pri-urgent  { background: #fee2e2; color: #dc2626; }
.pri-high    { background: #fef3c7; color: #b45309; }
.pri-medium  { background: #dbeafe; color: #2563eb; }
.pri-normal  { background: #ccfbf1; color: #0d9488; }
.pri-low     { background: #f3f4f6; color: #6b7280; }

/* Status badges */
.st-badge2 {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.st-new        { background: #1f2937; color: #fff; }
.st-open2      { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.st-inprogress { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.st-resolved2  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.st-closed2    { background: #f9fafb; color: #6b7280; border: 1px solid #e5e7eb; }

/* View button */
.view-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 16px;
    background: #0d9488;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: opacity .14s;
}
.view-btn:hover { opacity: .85; }

/* Support table */
.sup-tbl th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-3); padding: 10px 16px; border-bottom: 1px solid var(--border); background: #fafbfc; white-space: nowrap; }
.sup-tbl td { padding: 13px 16px; border-bottom: 1px solid var(--border); font-size: 13px; vertical-align: middle; }
.sup-tbl tbody tr:last-child td { border-bottom: none; }
.sup-tbl tbody tr:hover td { background: #f9fafb; }

/* Ticket ID link */
.tk-id-lnk { font-weight: 700; color: var(--orange); font-family: monospace; cursor: pointer; }
.tk-id-lnk:hover { text-decoration: underline; }

/* View Ticket Modal reuse */
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
</style>
@endpush

@section('content')

{{-- Page Header + Tab Bar --}}
<div class="ua-page-header">
    <h1>My Account</h1>
    <div class="ua-tabs">
        <button class="ua-tab active" data-tab="tab-account">Account Details</button>
        <button class="ua-tab" data-tab="tab-subscription">Subscription</button>
        <button class="ua-tab" data-tab="tab-support">Support</button>
    </div>
</div>

{{-- ── Tab 1: Account Details ────────────────────────────────────── --}}
<div class="ua-tab-panel active" id="tab-account">
    <div class="acc-card">
        {{-- Avatar + Name --}}
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border)">
            <div style="width:52px;height:52px;border-radius:12px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff;letter-spacing:-.5px;flex-shrink:0">
                {{ strtoupper(substr($authUser->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(strstr($authUser->name ?? '', ' ') ?: '  ', 1, 1)) }}
            </div>
            <div>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">{{ $authUser->name }}</div>
                <div style="font-size:13px;color:var(--text-3);margin-top:2px">{{ $authUser->email }}</div>
                <div style="margin-top:5px">
                    <span class="badge b-orange" style="font-size:11px">{{ ucfirst($authUser->getRoleNames()->first() ?? 'User') }}</span>
                </div>
            </div>
        </div>

        {{-- Account Info Grid --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:26px">
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);margin-bottom:4px">Company</div>
                <div style="font-size:13.5px;font-weight:500;color:var(--text-1)">{{ $tenantCompany?->company_name ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);margin-bottom:4px">Member Since</div>
                <div style="font-size:13.5px;font-weight:500;color:var(--text-1)">{{ $authUser->created_at?->format('M j, Y') ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);margin-bottom:4px">Status</div>
                <div><span class="badge b-green" style="font-size:11px"><span class="badge-dot" style="background:var(--green)"></span> Active</span></div>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin:0 -22px 22px"></div>

        {{-- Profile Form --}}
        <div style="margin-bottom:26px">
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:16px">Personal Information</div>
            <form method="POST" action="{{ route('account.profile') }}">
                @csrf
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Full Name</label>
                        <input type="text" name="name" class="f-inp" value="{{ old('name', $authUser->name) }}" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Email Address</label>
                        <input type="email" name="email" class="f-inp" value="{{ old('email', $authUser->email) }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Profile
                </button>
            </form>
        </div>

        <div style="height:1px;background:var(--border);margin:0 -22px 22px"></div>

        {{-- Password Form --}}
        <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:4px">Change Password</div>
            <div style="font-size:12.5px;color:var(--text-3);margin-bottom:16px">Use a strong password that you don't use elsewhere.</div>
            <form method="POST" action="{{ route('account.password') }}">
                @csrf
                <div class="f-group">
                    <label class="f-label">Current Password</label>
                    <input type="password" name="current_password" class="f-inp" placeholder="Enter your current password" autocomplete="current-password" required style="max-width:400px">
                </div>
                <div class="form-2col" style="max-width:400px">
                    <div class="f-group">
                        <label class="f-label">New Password</label>
                        <input type="password" name="password" class="f-inp" placeholder="Min. 8 characters" autocomplete="new-password" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="f-inp" placeholder="Repeat new password" autocomplete="new-password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-ghost btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Save Password
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ── Tab 2: Subscription ───────────────────────────────────────── --}}
<div class="ua-tab-panel" id="tab-subscription">

    {{-- Subscription Status Card --}}
    @php
        $subActive  = $subscription?->isActive();
        $subTrial   = $subscription?->isTrialing();
        $planName   = $currentPlan?->name ?? 'Free';
        $planStatus = $subscription ? ucfirst($subscription->status) : 'Active';
        $upgradePlans = ($plans ?? collect())->filter(fn($p) => !($currentPlan && $currentPlan->id === $p->id))->values();
    @endphp
    <div class="sub-status-card">
        <div class="sub-status-header">
            <div class="sub-status-title">Subscription Status</div>
            @if($subTrial)
                <span class="sub-billing-badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a">Trial Active</span>
            @elseif($subActive)
                <span class="sub-billing-badge" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0">Active</span>
            @else
                <span class="sub-billing-badge">Billing Inactive</span>
            @endif
        </div>
        <div class="sub-info-grid">
            <div class="sub-info-box">
                <div class="sub-info-lbl">Current Plan</div>
                <div class="sub-info-val">{{ $planName }} &middot; {{ $planStatus }}</div>
            </div>
            <div class="sub-info-box">
                <div class="sub-info-lbl">Billing</div>
                <div class="sub-info-val" @if(!$subscription) style="color:var(--text-3)" @endif>
                    {{ $subscription ? ucfirst($subscription->billing_cycle) : 'Not Set Up' }}
                </div>
            </div>
            <div class="sub-info-box">
                <div class="sub-info-lbl">Next Invoice</div>
                <div class="sub-info-val" @if(!$subscription?->current_period_end) style="color:var(--text-3)" @endif>
                    {{ $subscription?->current_period_end?->format('M d, Y') ?? 'Not Set Up' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Upgrade plan cards --}}
    @forelse($upgradePlans as $plan)
        @php
            $features = is_array($plan->features) ? array_filter($plan->features) : [];
            $isFree   = (bool)($plan->is_free ?? false);
            $price    = $isFree ? null : number_format((float)($plan->monthly_price ?? 0), 2);
        @endphp
        <div class="pro-plan-card">
            <div class="pro-plan-header">
                <div>
                    <div class="pro-plan-title">{{ $plan->name }}</div>
                    <div class="pro-plan-sub">{{ $plan->description ?? 'Perfect for growing businesses' }}</div>
                </div>
                @if($price)
                    <div class="pro-plan-price">
                        <div class="pro-plan-amount">${{ $price }}<span>/month</span></div>
                        <div class="pro-plan-billing">Billed monthly &middot; Cancel anytime</div>
                    </div>
                @endif
            </div>

            <div class="pro-plan-body">
                <div class="pro-plan-includes">
                    <div class="pro-section-lbl">Includes</div>
                    <ul class="pro-feat-list">
                        @forelse($features as $feature)
                            <li>
                                <svg class="pro-feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                {{ $feature }}
                            </li>
                        @empty
                            <li>
                                <svg class="pro-feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                All features included
                            </li>
                        @endforelse
                    </ul>
                </div>

                <div class="pro-plan-actions">
                    <div class="pro-section-lbl">Actions</div>
                    <p class="pro-action-hint">Upgrading will unlock {{ $plan->name }} features for this workspace. Existing data remains intact.</p>
                    <button type="button" class="plan-btn btn-orange" style="width:100%;margin-bottom:10px;padding:12px 16px;border-radius:8px;font-size:13.5px;font-weight:600;cursor:pointer;border:none;background:var(--orange);color:#fff;display:flex;align-items:center;justify-content:center;font-family:inherit">
                        Upgrade to {{ $plan->name }}
                    </button>
                    <button type="button" class="plan-btn btn-orange-outline" style="width:100%;padding:12px 16px;border-radius:8px;font-size:13.5px;font-weight:600;cursor:pointer;border:2px solid var(--orange);background:#fff;color:var(--orange);display:flex;align-items:center;justify-content:center;font-family:inherit">
                        Contact Support
                    </button>
                </div>
            </div>

            <div class="pro-plan-footer">
                <span>Secure Platform</span>
                <span>Role-Based Access</span>
            </div>
        </div>
    @empty
        <div style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:32px 24px;text-align:center;color:var(--text-2)">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="1.5" style="margin-bottom:10px"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <div style="font-weight:600;font-size:14px;margin-bottom:4px">You're on our best plan!</div>
            <div style="font-size:13px;color:var(--text-3)">All available features are already included in your {{ $planName }} plan.</div>
        </div>
    @endforelse

</div>

{{-- ── Tab 3: Support ────────────────────────────────────────────── --}}
<div class="ua-tab-panel" id="tab-support">

    <div class="sup-ph">
        <div>
            <div class="sup-ph-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px;margin-right:6px"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                My Support Tickets
            </div>
            <div class="sup-ph-sub">View the status of your submitted tickets and get timely updates</div>
        </div>
        <button class="btn btn-primary" id="btnRaiseTicket">
            Raise a Ticket
        </button>
    </div>

    <div class="card" style="padding:0;overflow:hidden">

        {{-- Filter Bar --}}
        <div class="sup-filter-bar">
            <div class="srch-wrap" style="flex:1;max-width:300px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="ua-search" placeholder="Search tickets…" autocomplete="off">
            </div>
            <div class="date-inp-wrap">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="sup-date-inp" id="ua-date-from" title="From date">
            </div>
            <div class="date-inp-wrap">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="sup-date-inp" id="ua-date-to" title="To date">
            </div>
        </div>

        {{-- Table --}}
        <div style="overflow-x:auto">
            <table class="sup-tbl" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="width:110px">Ticket ID</th>
                        <th>Subject</th>
                        <th style="width:110px">Priority</th>
                        <th style="width:120px">Status</th>
                        <th style="width:150px">Raised</th>
                        <th style="width:150px">Last Response</th>
                        <th style="width:80px">Action</th>
                    </tr>
                </thead>
                <tbody id="ua-tickets-body">
                @forelse($myTickets ?? [] as $ticket)
                @php
                    $tkId    = '#' . $ticket->id;
                    $pri     = strtolower($ticket->priority ?? 'low');
                    $st      = strtolower($ticket->status ?? 'open');
                    $stLabel = match($st) {
                        'in_progress' => 'In Progress',
                        'resolved'    => 'Resolved',
                        'closed'      => 'Closed',
                        'open'        => 'New',
                        default       => ucfirst($st),
                    };
                    $stClass = match($st) {
                        'in_progress' => 'st-inprogress',
                        'resolved'    => 'st-resolved2',
                        'closed'      => 'st-closed2',
                        default       => 'st-new',
                    };
                    $priClass = match($pri) {
                        'urgent'  => 'pri-urgent',
                        'high'    => 'pri-high',
                        'medium'  => 'pri-medium',
                        'normal'  => 'pri-normal',
                        default   => 'pri-low',
                    };
                @endphp
                <tr>
                    <td>
                        <span class="tk-id-lnk"
                            data-show="{{ route('support.show', $ticket->id) }}"
                            data-notes="{{ route('support.notes', $ticket->id) }}">
                            {{ $tkId }}
                        </span>
                    </td>
                    <td style="font-weight:500;color:var(--text-1);max-width:280px">{{ $ticket->subject }}</td>
                    <td><span class="pri-badge {{ $priClass }}">{{ ucfirst($pri) }}</span></td>
                    <td><span class="st-badge2 {{ $stClass }}">{{ $stLabel }}</span></td>
                    <td>
                        <div style="font-size:13px;color:var(--text-1)">{{ $ticket->created_at->format('M j, Y') }}</div>
                        <div style="font-size:11.5px;color:var(--text-3)">{{ $ticket->created_at->format('g:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-size:13px;color:var(--text-1)">{{ $ticket->updated_at->format('M j, Y') }}</div>
                        <div style="font-size:11.5px;color:var(--text-3)">{{ $ticket->updated_at->format('g:i A') }}</div>
                    </td>
                    <td>
                        <button class="view-btn tk-id-lnk"
                            data-show="{{ route('support.show', $ticket->id) }}"
                            data-notes="{{ route('support.notes', $ticket->id) }}">
                            View
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:60px 24px">
                        <div style="width:64px;height:64px;border-radius:50%;background:#fff3ec;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                            <svg width="28" height="28" fill="none" stroke="var(--orange)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        </div>
                        <div style="font-size:15px;font-weight:700;color:var(--text-1);margin-bottom:8px">No support tickets yet</div>
                        <div style="font-size:13px;color:var(--text-3);margin-bottom:18px">Need help? Raise a ticket and our team will get back to you promptly.</div>
                        <button class="btn btn-primary btn-sm" onclick="document.getElementById('newTicketModal').style.display='flex'">Raise a Ticket</button>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

{{-- ── View Ticket Modal ──────────────────────────────────────────── --}}
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
            <div class="vt-section-lbl">Attachment</div>
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
        <div class="mf-foot">
            <button class="btn btn-secondary" onclick="document.getElementById('viewTicketModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

{{-- ── New Ticket Modal ───────────────────────────────────────────── --}}
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
                <label class="mf-label" for="nt-subject">Subject</label>
                <input type="text" id="nt-subject" name="subject" class="mf-inp" placeholder="Brief description of your issue" required value="{{ old('subject') }}">
            </div>
            <div class="mf-field">
                <label class="mf-label">Priority</label>
                <div style="display:flex;gap:8px;margin-top:4px">
                    @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High'] as $v => $l)
                    <button type="button" class="btn btn-sm ua-priority-toggle" data-value="{{ $v }}"
                        style="flex:1;border:2px solid var(--border);background:#fff;color:var(--text-2);transition:all .15s">{{ $l }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="priority" id="ua-priorityInput" value="{{ old('priority','low') }}">
            </div>
            <div class="mf-field">
                <label class="mf-label" for="nt-message">Message</label>
                <textarea id="nt-message" name="message" class="mf-textarea" rows="5" placeholder="Provide as much detail as possible…" required>{{ old('message') }}</textarea>
            </div>
            <div class="mf-field">
                <label class="mf-label" for="nt-attachment">Attachment <span style="color:var(--text-3);font-weight:400">(optional)</span></label>
                <input type="file" id="nt-attachment" name="attachment"
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

{{-- PHP data bridge --}}
<div id="ua-support-data" style="display:none"
    data-has-errors="{{ $errors->any() ? '1' : '0' }}"
    data-form-errors="{{ htmlspecialchars(json_encode($errors->all()), ENT_QUOTES, 'UTF-8') }}"
    data-default-priority="{{ old('priority','low') }}"></div>

@endsection

@push('scripts')
<script>
// ── Tab switching ─────────────────────────────────────────────────
document.querySelectorAll('.ua-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.ua-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('.ua-tab-panel').forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});

// ── URL ?tab= switching ───────────────────────────────────────────
(function() {
    var tabMap = { 'account-details': 'tab-account', 'subscription': 'tab-subscription', 'support': 'tab-support' };
    var params = new URLSearchParams(window.location.search);
    var tabKey = params.get('tab');
    if (tabKey && tabMap[tabKey]) {
        var btn = document.querySelector('.ua-tab[data-tab="' + tabMap[tabKey] + '"]');
        if (btn) btn.click();
    }
})();

// ── Search filter ─────────────────────────────────────────────────
document.getElementById('ua-search').addEventListener('keyup', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#ua-tickets-body tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ── Date range filter ─────────────────────────────────────────────
function applyDateFilter() {
    var from = document.getElementById('ua-date-from').value;
    var to   = document.getElementById('ua-date-to').value;
    document.querySelectorAll('#ua-tickets-body tr').forEach(function(row) {
        var dateCell = row.querySelector('td:nth-child(5)');
        if (!dateCell) return;
        var dateText = dateCell.textContent.trim().split('\n')[0].trim();
        var rowDate  = new Date(dateText);
        var show = true;
        if (from && rowDate < new Date(from)) show = false;
        if (to   && rowDate > new Date(to))   show = false;
        row.style.display = show ? '' : 'none';
    });
}
document.getElementById('ua-date-from').addEventListener('change', applyDateFilter);
document.getElementById('ua-date-to').addEventListener('change', applyDateFilter);

// ── Priority toggle ───────────────────────────────────────────────
function uaSelectPriority(btn) {
    document.querySelectorAll('.ua-priority-toggle').forEach(function(b) {
        b.style.borderColor = 'var(--border)'; b.style.background = '#fff';
        b.style.color = 'var(--text-2)'; b.style.fontWeight = '400';
    });
    btn.style.borderColor = 'var(--blue)';
    btn.style.background  = 'rgba(26,106,168,.07)';
    btn.style.color       = 'var(--blue)';
    btn.style.fontWeight  = '600';
    document.getElementById('ua-priorityInput').value = btn.dataset.value;
}

// ── Raise Ticket button ───────────────────────────────────────────
document.getElementById('btnRaiseTicket').addEventListener('click', function() {
    document.getElementById('newTicketModal').style.display = 'flex';
});

// ── Global click handler ──────────────────────────────────────────
document.addEventListener('click', function(e) {
    var viewBtn = e.target.closest('.tk-id-lnk');
    if (viewBtn && viewBtn.dataset.show) {
        openViewTicket(viewBtn.dataset.show, viewBtn.dataset.notes);
        return;
    }
    var priBtn = e.target.closest('.ua-priority-toggle');
    if (priBtn) { uaSelectPriority(priBtn); return; }
});

// ── View Ticket ───────────────────────────────────────────────────
var ST_LABEL = { open:'Open', in_progress:'In Progress', resolved:'Resolved', closed:'Closed' };
var ST_CLASS = { open:'st-open', in_progress:'st-in_progress', resolved:'st-resolved', closed:'st-closed' };

function openViewTicket(showUrl, notesUrl) {
    fetch(showUrl, { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
        .then(function(r) { return r.json(); })
        .then(function(t) {
            document.getElementById('vt-id').textContent          = t.ticket_id;
            document.getElementById('vt-created').textContent     = 'Submitted on ' + t.created_at;
            document.getElementById('vt-raised').textContent      = t.created_at;
            document.getElementById('vt-by').textContent          = t.submitted_by;
            document.getElementById('vt-status-text').textContent = ST_LABEL[t.status] || t.status;
            document.getElementById('vt-subject').textContent     = t.subject;
            document.getElementById('vt-message').textContent     = t.message;

            var priEl = document.getElementById('vt-pri-badge');
            priEl.className   = 'badge ' + (t.priority === 'high' ? 'b-red' : t.priority === 'medium' ? 'b-amber' : 'b-gray');
            priEl.textContent = t.priority.charAt(0).toUpperCase() + t.priority.slice(1);

            var stEl = document.getElementById('vt-st-badge');
            stEl.className   = 'st-badge ' + (ST_CLASS[t.status] || 'st-open');
            stEl.textContent = ST_LABEL[t.status] || t.status;

            var attSec = document.getElementById('vt-att-section');
            if (t.attachment_name) {
                document.getElementById('vt-att-name').textContent = t.attachment_name;
                document.getElementById('vt-att-link').href        = t.attachment_url;
                attSec.style.display = 'block';
            } else {
                attSec.style.display = 'none';
            }

            document.getElementById('viewTicketModal').style.display = 'flex';
        })
        .catch(function() { showToast('Failed to load ticket.', 'error'); });
}

// ── Init: reopen modal on validation error ────────────────────────
(function() {
    var el = document.getElementById('ua-support-data');
    if (!el) return;
    var priority = el.dataset.defaultPriority || 'low';
    var hasErr   = el.dataset.hasErrors === '1';
    var errs     = JSON.parse(el.dataset.formErrors || '[]');
    var btn = document.querySelector('.ua-priority-toggle[data-value="' + priority + '"]');
    if (btn) uaSelectPriority(btn);
    if (hasErr) {
        // Switch to support tab and open modal
        var supTab = document.querySelector('.ua-tab[data-tab="tab-support"]');
        if (supTab) supTab.click();
        document.getElementById('newTicketModal').style.display = 'flex';
        errs.forEach(function(msg) { showToast(msg, 'error'); });
    }
})();
</script>
@endpush
