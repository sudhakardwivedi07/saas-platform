@extends('layouts.superadmin.app')
@section('title', $company->company_name)
@section('page-title', $company->company_name)

@section('content')

@php
    $words       = array_filter(explode(' ', trim($company->company_name)));
    $initials    = strtoupper(substr($words[0] ?? '', 0, 1)) . strtoupper(substr($words[1] ?? ($words[0] ?? ''), 0, 1));
    $userCount   = $company->users->count();
    $planName    = $company->package?->name ?? 'No Plan';
    $monthlyFee  = $company->package?->monthly_price ? '$' . number_format($company->package->monthly_price, 0) : 'N/A';
    $memberSince = $company->created_at->format('M Y');
    $portalUrl   = $company->subdomain ? $company->subdomain . '.' . config('tenancy.root_domain') : null;

    $statusBadge = match($company->status) {
        'active'    => 'b-green',
        'suspended' => 'b-red',
        'trial'     => 'b-amber',
        default     => 'b-gray',
    };

    $subStatus   = $company->subscription?->status ?? $company->status;
    $subBadge    = match(strtolower($subStatus)) {
        'active'             => 'b-green',
        'trialing'           => 'b-amber',
        'canceled','cancelled' => 'b-red',
        default              => 'b-gray',
    };

    $nextBilling  = $company->subscription?->current_period_end
                        ? $company->subscription->current_period_end->format(active_date_format())
                        : 'N/A';
    $billingCycle = $company->subscription?->billing_cycle
                        ? ucfirst($company->subscription->billing_cycle)
                        : 'N/A';

    $headerSubtitle = collect(['Partner Firm', $planName !== 'No Plan' ? $planName : null, ucfirst($company->status)])->filter()->implode(' · ');
    $pageSubtitle   = collect(['Partner Firm', $planName !== 'No Plan' ? $planName : null])->filter()->implode(' · ');

    $bgColors  = ['#0C84BC','#10b981','#F06B21','#7c3aed','#0d9488','#e11d48','#b45309','#6366f1'];
    $avatarBg  = $bgColors[abs(crc32($company->company_name)) % 8];

    $packages  = \App\Models\SuperAdmin\Package::active()->orderBy('sort')->get();
@endphp

{{-- ── Top navigation bar ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;flex-wrap:wrap;gap:.5rem;">
    <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost btn-sm"
       style="display:inline-flex;align-items:center;gap:.35rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Firms
    </a>
    <div style="display:flex;gap:.6rem;align-items:center;">
        <a href="{{ route('superadmin.companies.edit', $company) }}" class="btn btn-ghost btn-sm"
           style="display:inline-flex;align-items:center;gap:.4rem;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Firm
        </a>
        <form method="POST" action="{{ route('superadmin.companies.impersonate', $company) }}" style="display:contents">
            @csrf
            <button class="btn btn-primary btn-sm" type="submit"
                    style="display:inline-flex;align-items:center;gap:.4rem;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Login as Firm
            </button>
        </form>
    </div>
</div>

{{-- ── Page title ── --}}
<div style="margin-bottom:1rem;">
    <h1 style="font-size:1.55rem;font-weight:700;color:var(--text-1);margin:0 0 .25rem;">{{ $company->company_name }}</h1>
    <div style="font-size:.875rem;color:var(--text-3);">{{ $pageSubtitle }}</div>
</div>

{{-- ── Gradient Header Card ── --}}
<div class="firm-detail-header" style="margin-bottom:1.25rem;align-items:center;gap:1.5rem;flex-wrap:wrap;">
    {{-- Left: avatar + info --}}
    <div style="display:flex;align-items:center;gap:1rem;flex:1;min-width:0;">
        <div style="width:54px;height:54px;border-radius:13px;background:{{ $avatarBg }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.15rem;font-weight:700;flex-shrink:0;">
            {{ $initials }}
        </div>
        <div style="min-width:0;">
            <div style="font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:.2rem;">{{ $company->company_name }}</div>
            <div style="font-size:.82rem;color:rgba(255,255,255,.75);margin-bottom:.3rem;">{{ $headerSubtitle }}</div>
            <div style="font-size:.78rem;color:rgba(255,255,255,.6);display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                @if($company->company_email)<span>{{ $company->company_email }}</span>@endif
                @if($company->company_phone)
                    <span style="opacity:.45">·</span>
                    <span>{{ $company->company_phone }}</span>
                @endif
            </div>
        </div>
    </div>
    {{-- Right: stat boxes --}}
    <div style="display:flex;gap:.65rem;flex-wrap:wrap;flex-shrink:0;">
        <div style="background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:.65rem 1.15rem;text-align:center;min-width:88px;">
            <div style="font-size:1.25rem;font-weight:700;color:#fff;line-height:1.2;">{{ $userCount }}</div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.65);margin-top:.2rem;white-space:nowrap;">Total Clients</div>
        </div>
        <div style="background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:.65rem 1.15rem;text-align:center;min-width:88px;">
            <div style="font-size:1.25rem;font-weight:700;color:#fff;line-height:1.2;">{{ $monthlyFee }}</div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.65);margin-top:.2rem;white-space:nowrap;">Monthly Fee</div>
        </div>
        <div style="background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:.65rem 1.15rem;text-align:center;min-width:88px;">
            <div style="font-size:1.25rem;font-weight:700;color:#fff;line-height:1.2;">${{ number_format($totalPaid, 0) }}</div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.65);margin-top:.2rem;white-space:nowrap;">Total Paid</div>
        </div>
        <div style="background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:.65rem 1.15rem;text-align:center;min-width:88px;">
            <div style="font-size:1.25rem;font-weight:700;color:#fff;line-height:1.2;">{{ $memberSince }}</div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.65);margin-top:.2rem;white-space:nowrap;">Member Since</div>
        </div>
    </div>
</div>

{{-- ── Two-column: Firm Details + Plan & Billing ── --}}
<div class="form-2col" style="margin-bottom:1.25rem;align-items:start;">

    {{-- Firm Details --}}
    <div class="card">
        <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);font-weight:600;font-size:.95rem;color:#0f172a;">
            Firm Details
        </div>
        @php
        $detailRows = [
            ['Firm Name',    $company->company_name,          null],
            ['Firm Type',    'Partner Firm',                  null],
            ['Email',        $company->company_email,         null],
            ['Phone',        $company->company_phone ?: '—',  null],
            ['Portal URL',   $portalUrl,                      'link'],
            ['Status',       ucfirst($company->status),       $statusBadge],
            ['Onboarded By', $company->onboardedBy?->name ?? '—', null],
            ['Onboarded On', $company->created_at->format(active_date_format()), null],
        ];
        @endphp
        @foreach($detailRows as [$label, $value, $type])
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;min-height:42px;">
            <span style="font-size:.82rem;color:var(--text-3);white-space:nowrap;flex-shrink:0;">{{ $label }}</span>
            @if($type && str_starts_with($type, 'b-'))
                <span class="badge {{ $type }}">{{ $value }}</span>
            @elseif($type === 'link' && $value)
                <a href="{{ str_starts_with($value, 'http') ? $value : 'https://' . $value }}" target="_blank"
                   style="font-size:.85rem;font-weight:500;color:#0C84BC;text-decoration:none;text-align:right;">{{ $value }}</a>
            @else
                <span style="font-size:.85rem;font-weight:500;color:#1e293b;text-align:right;">{{ $value ?? '—' }}</span>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Plan & Billing --}}
    <div class="card">
        <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);font-weight:600;font-size:.95rem;color:#0f172a;">
            Plan &amp; Billing
        </div>
        {{-- Current Plan --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;min-height:42px;">
            <span style="font-size:.82rem;color:var(--text-3);">Current Plan</span>
            @if($company->package)
                <span style="background:#e0f2fe;color:#0C84BC;font-size:.75rem;font-weight:600;padding:3px 11px;border-radius:999px;">{{ $planName }}</span>
            @else
                <span style="font-size:.85rem;color:#94a3b8;">—</span>
            @endif
        </div>
        {{-- Plan Price --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;min-height:42px;">
            <span style="font-size:.82rem;color:var(--text-3);">Plan Price</span>
            <span style="font-size:.9rem;font-weight:600;color:#F06B21;">
                {{ $company->package?->monthly_price ? '$' . number_format($company->package->monthly_price, 0) . '/month' : '—' }}
            </span>
        </div>
        {{-- Billing Cycle --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;min-height:42px;">
            <span style="font-size:.82rem;color:var(--text-3);">Billing Cycle</span>
            <span style="font-size:.85rem;font-weight:500;color:#1e293b;">{{ $billingCycle }}</span>
        </div>
        {{-- Next Billing --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;min-height:42px;">
            <span style="font-size:.82rem;color:var(--text-3);">Next Billing</span>
            <span style="font-size:.85rem;font-weight:500;color:#1e293b;">{{ $nextBilling }}</span>
        </div>
        {{-- Status --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1.25rem;border-bottom:1px solid var(--border);gap:1rem;min-height:42px;">
            <span style="font-size:.82rem;color:var(--text-3);">Status</span>
            <span class="badge {{ $subBadge }}">{{ ucfirst($subStatus) }}</span>
        </div>
        {{-- Change Plan button --}}
        <div style="padding:.9rem 1.25rem;">
            <button type="button" class="btn btn-ghost btn-sm"
                    onclick="openChangePlan({{ $company->id }}, '{{ addslashes($company->company_name) }}', {{ $company->package_id ?? 'null' }})">
                Change Plan
            </button>
        </div>
    </div>

</div>

{{-- ── Users ── --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);font-weight:600;font-size:.95rem;color:#0f172a;">
        Users <span style="font-size:.8rem;font-weight:400;color:#94a3b8;">({{ $userCount }})</span>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    @foreach(['Name','Email','Role','Status','Last Login'] as $th)
                    <th style="padding:.6rem 1.25rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);border-bottom:1px solid var(--border);background:#fafbfc;text-align:left;white-space:nowrap;">{{ $th }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($company->users as $user)
                @php
                    $uStatus = $user->status ?? ($user->is_active ? 'active' : 'inactive');
                    $uBadge  = $uStatus === 'active' ? 'b-green' : 'b-gray';
                    $roles   = $user->getRoleNames();
                    $roleLabel = $roles->isNotEmpty() ? ucfirst(str_replace('_', ' ', $roles->first())) : 'User';
                @endphp
                <tr>
                    <td style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);font-weight:600;font-size:.875rem;color:#0f172a;">{{ $user->name }}</td>
                    <td style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);color:#64748b;font-size:.85rem;">{{ $user->email }}</td>
                    <td style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);font-size:.85rem;">
                        <span class="badge b-blue">{{ $roleLabel }}</span>
                    </td>
                    <td style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);">
                        <span class="badge {{ $uBadge }}">{{ ucfirst($uStatus) }}</span>
                    </td>
                    <td style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);color:#94a3b8;font-size:.8rem;">
                        {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format(active_date_format()) : 'Never' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:2.5rem;color:#94a3b8;font-size:.875rem;">
                        No users found for this firm.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Change Plan Modal ── --}}
<div id="change-plan-modal" class="modal-overlay">
    <div class="modal-box" style="width:420px">
        <button class="modal-close" onclick="document.getElementById('change-plan-modal').classList.remove('open')">×</button>
        <div class="modal-title">Change Plan</div>
        <div id="change-plan-firm" class="modal-sub"></div>
        <form id="change-plan-form" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="company_name" value="{{ $company->company_name }}">
            <div class="mf-field">
                <label class="mf-label">Select Plan</label>
                <select name="package_id" id="cp-package" class="mf-inp">
                    <option value="">— No Plan —</option>
                    @foreach($packages as $pkg)
                    <option value="{{ $pkg->id }}"
                            {{ $company->package_id == $pkg->id ? 'selected' : '' }}>
                        {{ $pkg->name }} — ${{ number_format($pkg->monthly_price, 0) }}/mo
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="mf-foot">
                <button type="button" onclick="document.getElementById('change-plan-modal').classList.remove('open')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openChangePlan(id, name, currentPlanId) {
    document.getElementById('change-plan-form').action = `/superadmin/companies/${id}`;
    document.getElementById('change-plan-firm').textContent = `Firm: ${name}`;
    document.getElementById('cp-package').value = currentPlanId || '';
    document.getElementById('change-plan-modal').classList.add('open');
}
</script>

@endsection
