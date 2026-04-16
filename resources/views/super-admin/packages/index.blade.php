@extends('layouts.superadmin.app')
@section('title','Plan Management')
@section('page-title','Plan Management')

@section('content')
<div class="ph">
    <div>
        <h1>Plan Management</h1>
        <p>Configure subscription plans for firms on the platform</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.packages.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Plan
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
    @forelse($packages as $pkg)
    <div class="card" style="position:relative;overflow:hidden">
        @if($pkg->is_recommended)
        <div style="position:absolute;top:12px;right:12px">
            <span class="badge b-orange">Recommended</span>
        </div>
        @endif

        {{-- Plan header --}}
        <div style="margin-bottom:14px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                <div style="width:40px;height:40px;background:var(--blue3);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:700">{{ $pkg->name }}</div>
                    @if($pkg->stripe_monthly_price_id)
                        <span class="badge b-green" style="font-size:10px;padding:1px 7px">
                            <span class="bdot" style="background:#10b981;width:5px;height:5px"></span>Stripe Connected
                        </span>
                    @else
                        <span class="badge b-gray" style="font-size:10px;padding:1px 7px">No Stripe ID</span>
                    @endif
                </div>
            </div>
            @if($pkg->description)
            <p style="font-size:12px;color:var(--text-3);line-height:1.4">{{ $pkg->description }}</p>
            @endif
        </div>

        {{-- Pricing --}}
        <div style="margin-bottom:14px">
            <div style="display:flex;align-items:baseline;gap:4px">
                <span style="font-size:30px;font-weight:700;letter-spacing:-.5px">
                    @if($pkg->is_free) Free @else ${{ number_format($pkg->monthly_price, 0) }} @endif
                </span>
                @if(!$pkg->is_free)<span style="font-size:13px;color:var(--text-3)">/mo</span>@endif
            </div>
            @if(!$pkg->is_free && $pkg->annual_price)
            <div style="font-size:11.5px;color:var(--text-3)">${{ number_format($pkg->annual_price, 0) }}/yr billed annually</div>
            @endif
        </div>

        {{-- Features list --}}
        <div style="margin-bottom:16px;display:flex;flex-direction:column;gap:7px">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                {{ $pkg->max_users ? $pkg->max_users . ' users' : 'Unlimited users' }}
            </div>
            @if($pkg->max_storage_gb)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                {{ $pkg->max_storage_gb }} GB storage
            </div>
            @endif
            @if($pkg->companies_count > 0)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                {{ $pkg->companies_count }} active {{ Str::plural('firm', $pkg->companies_count) }}
            </div>
            @endif
            @if($pkg->is_trial_allowed && $pkg->trial_days)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--amber)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ $pkg->trial_days }}-day free trial
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:8px;padding-top:14px;border-top:1px solid var(--border)">
            <a href="{{ route('superadmin.packages.edit', $pkg) }}" class="btn btn-ghost btn-sm" style="flex:1;justify-content:center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <form method="POST" action="{{ route('superadmin.packages.destroy', $pkg) }}"
                  onsubmit="return confirm('Delete this plan?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost-red btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:60px 20px">
        <div style="width:56px;height:56px;background:var(--blue3);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div style="font-size:15px;font-weight:600;margin-bottom:5px">No plans yet</div>
        <div style="font-size:13px;color:var(--text-3);margin-bottom:16px">Create your first subscription plan for firms.</div>
        <a href="{{ route('superadmin.packages.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create First Plan
        </a>
    </div>
    @endforelse
</div>
@endsection
