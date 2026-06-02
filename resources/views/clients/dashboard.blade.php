@extends('layouts.app')
@section('title', $clientUser->name . "'s Dashboard")
@section('page-title', 'Client Dashboard')

@section('content')

<div id="insights-refresh-target" data-refresh="1" style="display:none"></div>

@php
    $co   = $clientUser->company;
    $plan = $co?->clientPackage ?? $co?->package;
    $isQB = $co && $co->quickbooksConnections->where('is_active', true)->isNotEmpty();
    $insightsUsed = $co?->insights_used ?? 0;
    $maxInsights  = $plan?->max_documents ?? null;
    $remaining    = $maxInsights !== null ? max(0, $maxInsights - $insightsUsed) : null;
    $st = $clientUser->status ?? 'active';
@endphp

{{-- Page header --}}
<div class="ph">
    <div class="ph-left">
        <div style="display:flex;align-items:center;gap:10px">
            <a href="{{ route('clients.index') }}" style="display:flex;align-items:center;color:var(--text-3);text-decoration:none">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div style="width:38px;height:38px;border-radius:10px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0">
                {{ strtoupper(substr($clientUser->name,0,1)) }}{{ strtoupper(substr(strstr($clientUser->name,' ') ?: ' ',1,1)) }}
            </div>
            <div>
                <h1 style="margin:0">{{ $clientUser->name }}</h1>
                <p style="margin:0">{{ $clientUser->email }}</p>
            </div>
        </div>
    </div>
    <div class="ph-actions">
        <a href="{{ route('clients.show', $clientUser->id) }}" class="btn btn-ghost btn-sm">View Profile</a>
        <a href="{{ route('clients.kpi-dashboard', $clientUser->id) }}" class="btn btn-primary btn-sm">KPI Dashboard</a>
        <a href="{{ route('clients.insights', $clientUser->id) }}" class="btn btn-ghost btn-sm">View Insights</a>
    </div>
</div>

{{-- Stats row --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:20px">

    <div class="acc-card" style="padding:16px 18px">
        <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">Status</div>
        <span class="badge {{ $st === 'active' ? 'b-green' : 'b-gray' }}" style="font-size:12.5px">
            <span class="badge-dot" style="background:{{ $st === 'active' ? 'var(--green)' : 'var(--text-3)' }}"></span>
            {{ ucfirst($st) }}
        </span>
    </div>

    <div class="acc-card" style="padding:16px 18px">
        <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">Company</div>
        <div style="font-size:14px;font-weight:600;color:var(--text-1)">{{ $co?->company_name ?? '—' }}</div>
    </div>

    <div class="acc-card" style="padding:16px 18px">
        <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">QB Connected</div>
        @if($isQB)
            <span class="badge b-green" style="font-size:12.5px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                Connected
            </span>
        @else
            <span class="badge b-gray" style="font-size:12.5px">Not Connected</span>
        @endif
    </div>

    <div class="acc-card" style="padding:16px 18px">
        <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">Insights Used</div>
        <div style="font-size:22px;font-weight:700;color:var(--text-1)">{{ $insightsUsed }}</div>
    </div>

    <div class="acc-card" style="padding:16px 18px">
        <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">Remaining</div>
        <div style="font-size:22px;font-weight:700;color:{{ $remaining === 0 ? 'var(--red)' : 'var(--text-1)' }}">
            {{ $remaining === null ? '∞' : $remaining }}
        </div>
    </div>

    <div class="acc-card" style="padding:16px 18px">
        <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">Plan</div>
        <div style="font-size:13px;font-weight:600;color:var(--text-1)">{{ $plan?->name ?? '—' }}</div>
        @if($plan?->monthly_price)
            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">${{ number_format($plan->monthly_price, 2) }}/mo</div>
        @endif
    </div>

</div>

{{-- Account details --}}
<div class="acc-card">
    <div class="acc-section-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Account Details
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-top:14px">
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Full Name</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $clientUser->name }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Email</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $clientUser->email }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Phone</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $clientUser->mobile ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Role</div>
            <span class="badge b-blue" style="font-size:12px">{{ $clientUser->roleDisplayName('User') }}</span>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Member Since</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $clientUser->created_at->format('M j, Y') }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Last Active</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $co?->last_active_at?->diffForHumans() ?? $clientUser->updated_at->diffForHumans() }}</div>
        </div>
        @if($co?->industry_type)
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Industry</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $co->industry_type }}</div>
        </div>
        @endif
        @if($co?->entity_type)
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Entity Type</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $co->entity_type }}</div>
        </div>
        @endif
        @if($co?->state)
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">State</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ $co->state }}</div>
        </div>
        @endif
    </div>
</div>

@endsection
