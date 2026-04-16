@extends('layouts.superadmin.app')
@section('title', $company->company_name)
@section('page-title', $company->company_name)

@section('content')

@php
    $initials    = strtoupper(substr($company->company_name, 0, 2));
    $userCount   = $company->users->count();
    $statusBadge = match($company->status) {
        'active'    => 'b-green',
        'suspended' => 'b-red',
        'trial'     => 'b-amber',
        default     => 'b-gray',
    };
    $planName    = $company->package?->name ?? 'No Plan';
    $subStatus   = $company->subscription?->status ?? 'None';
    $nextBilling = $company->subscription?->current_period_end
                    ? $company->subscription->current_period_end->format('M d, Y')
                    : 'N/A';
@endphp

{{-- Back button --}}
<div style="margin-bottom:1rem;">
    <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost btn-sm"
       style="display:inline-flex;align-items:center;gap:.35rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to Firms
    </a>
</div>

{{-- Gradient Header Card --}}
<div class="firm-detail-header">
    <div class="firm-detail-av">{{ $initials }}</div>
    <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.35rem;">
            <h2 style="font-size:1.4rem;font-weight:700;color:#fff;margin:0;">{{ $company->company_name }}</h2>
            <span class="badge {{ $statusBadge }}">{{ ucfirst($company->status) }}</span>
        </div>
        <div style="font-size:.875rem;color:rgba(255,255,255,.7);">{{ $planName }}</div>
    </div>
    {{-- Action buttons --}}
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-start;">
        <a href="{{ route('superadmin.companies.edit', $company) }}" class="btn btn-ghost btn-sm"
           style="color:#fff;border-color:rgba(255,255,255,.3);">Edit</a>
        <form method="POST" action="{{ route('superadmin.companies.impersonate', $company) }}">
            @csrf
            <button class="btn btn-primary btn-sm" type="submit">Login As</button>
        </form>
    </div>
</div>

{{-- Stat Chips Row --}}
<div style="display:flex;gap:.75rem;flex-wrap:wrap;margin:1rem 0;">
    <div class="stat-chip">
        <div class="stat-chip-val">$0</div>
        <div class="stat-chip-lbl">MRR</div>
    </div>
    <div class="stat-chip">
        <div class="stat-chip-val">{{ $userCount }}</div>
        <div class="stat-chip-lbl">Users</div>
    </div>
    <div class="stat-chip">
        <div class="stat-chip-val">N/A</div>
        <div class="stat-chip-lbl">QB</div>
    </div>
    <div class="stat-chip">
        <div class="stat-chip-val" style="color:#22c55e;">Good</div>
        <div class="stat-chip-lbl">Health</div>
    </div>
</div>

{{-- Info Banner --}}
<div class="info-banner" style="margin-bottom:1.25rem;">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="flex-shrink:0;color:#0C84BC;">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <span class="info-banner-txt">
        Viewing <strong>{{ $company->company_name }}</strong>. Use <strong>Login As</strong> to access their panel.
    </span>
</div>

{{-- Tabs --}}
<div class="tab-bar" id="firmTabs">
    <button class="tab-btn active" data-tab="overview">Overview</button>
    <button class="tab-btn" data-tab="users">Users</button>
    <button class="tab-btn" data-tab="billing">Billing</button>
</div>

{{-- ── Overview Tab ── --}}
<div class="tab-panel active" id="tab-overview">
    <div class="form-2col" style="margin-top:1rem;">

        {{-- Left: Company Details --}}
        <div class="card">
            <div style="padding:.9rem 1.1rem;border-bottom:1px solid var(--border);font-weight:600;color:#0f172a;">
                Company Details
            </div>
            <div style="padding:1rem 1.1rem;display:flex;flex-direction:column;gap:.85rem;">
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Email</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">{{ $company->company_email }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Subdomain</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">
                        <a href="{{ tenant_url($company->subdomain) }}" target="_blank"
                           style="color:#0C84BC;text-decoration:none;">
                            {{ $company->subdomain }}.{{ config('tenancy.root_domain') }}
                        </a>
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Currency</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">
                        {{ $company->currency ?? 'USD' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Timezone</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">
                        {{ $company->timezone ?? 'UTC' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Status</div>
                    <span class="badge {{ $statusBadge }}">{{ ucfirst($company->status) }}</span>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Joined</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">
                        {{ $company->created_at->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Subscription Info --}}
        <div class="card">
            <div style="padding:.9rem 1.1rem;border-bottom:1px solid var(--border);font-weight:600;color:#0f172a;">
                Subscription
            </div>
            <div style="padding:1rem 1.1rem;display:flex;flex-direction:column;gap:.85rem;">
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Plan</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">{{ $planName }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Subscription Status</div>
                    @php
                        $subBadge = match(strtolower($subStatus)) {
                            'active'   => 'b-green',
                            'trialing' => 'b-amber',
                            'canceled', 'cancelled' => 'b-red',
                            default    => 'b-gray',
                        };
                    @endphp
                    <span class="badge {{ $subBadge }}">{{ ucfirst($subStatus) }}</span>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Billing Cycle</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">
                        {{ $company->subscription?->billing_cycle ? ucfirst($company->subscription->billing_cycle) : 'N/A' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">Next Billing</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">{{ $nextBilling }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.2rem;">MRR</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:500;">$0</div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Users Tab ── --}}
<div class="tab-panel" id="tab-users">
    <div class="card" style="margin-top:1rem;">
        <div style="padding:.9rem 1.1rem;border-bottom:1px solid var(--border);font-weight:600;color:#0f172a;">
            Users &nbsp;<span style="font-size:.8rem;font-weight:400;color:#94a3b8;">({{ $userCount }})</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="tc">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($company->users as $user)
                    @php
                        $uStatus = $user->status ?? ($user->is_active ? 'active' : 'inactive');
                        $uBadge  = $uStatus === 'active' ? 'b-green' : 'b-gray';
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:.875rem;color:#0f172a;">{{ $user->name }}</div>
                        </td>
                        <td style="color:#64748b;font-size:.85rem;">{{ $user->email }}</td>
                        <td style="font-size:.85rem;">
                            <span class="badge b-blue">{{ ucfirst($user->role ?? 'User') }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $uBadge }}">{{ ucfirst($uStatus) }}</span>
                        </td>
                        <td style="color:#94a3b8;font-size:.8rem;">
                            {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y') : 'Never' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:2.5rem;color:#94a3b8;">
                            No users found for this firm.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Billing Tab ── --}}
<div class="tab-panel" id="tab-billing">
    <div class="card" style="margin-top:1rem;">
        <div style="padding:.9rem 1.1rem;border-bottom:1px solid var(--border);font-weight:600;color:#0f172a;">
            Billing History
        </div>
        <div style="padding:3rem 1.25rem;text-align:center;color:#94a3b8;">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                 style="margin:0 auto .75rem;display:block;color:#cbd5e1;">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            Payment history coming soon.
        </div>
    </div>
</div>

{{-- Tab switcher script --}}
<script>
(function () {
    var btns   = document.querySelectorAll('#firmTabs .tab-btn');
    var panels = document.querySelectorAll('.tab-panel');

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-tab');

            btns.forEach(function (b) { b.classList.remove('active'); });
            panels.forEach(function (p) { p.classList.remove('active'); });

            btn.classList.add('active');
            var panel = document.getElementById('tab-' + target);
            if (panel) panel.classList.add('active');
        });
    });
}());
</script>

@endsection
