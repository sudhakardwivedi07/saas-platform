@extends('layouts.superadmin.app')
@section('title','Plan Management')
@section('page-title','Plan Management')

@section('content')
@php
    $accentColors = ['#f97316', '#7c3aed', '#10b981', '#ec4899', '#0ea5e9'];
    $nonRecommendedIdx = 0;
@endphp

{{-- Header --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px;">
    <div>
        <h1 style="font-size:22px; font-weight:800; color:#111827; margin:0;">Plan Management</h1>
        <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">Define and manage subscription plans offered to firms</p>
    </div>
    <a href="{{ route('superadmin.packages.create') }}"
       style="display:inline-flex; align-items:center; gap:6px; background:#f97316; color:#fff; padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; white-space:nowrap; box-shadow:0 2px 6px rgba(249,115,22,.35);">
        + Add Plan
    </a>
</div>

@if($packages->isEmpty())
<div style="background:white; border-radius:12px; padding:64px 32px; text-align:center; box-shadow:0 1px 4px rgba(0,0,0,.07);">
    <div style="width:56px; height:56px; background:#eff6ff; border-radius:14px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
    </div>
    <h3 style="font-size:17px; font-weight:600; color:#374151; margin:0 0 4px;">No plans yet</h3>
    <p style="font-size:13px; color:#9ca3af; margin:0 0 18px;">Create your first subscription plan for firms.</p>
    <a href="{{ route('superadmin.packages.create') }}"
       style="display:inline-flex; align-items:center; gap:6px; background:#f97316; color:white; padding:10px 22px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
        + Create First Plan
    </a>
</div>
@else

<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px;">
    @foreach($packages as $pkg)
    @php
        $isRecommended = (bool) $pkg->is_recommended;
        if ($isRecommended) {
            $accent     = '#3b82f6';
            $cardBorder = 'border: 2px solid #3b82f6;';
        } else {
            $accent     = $accentColors[$nonRecommendedIdx % count($accentColors)];
            $cardBorder = 'border: 1px solid #e5e7eb; border-top: 4px solid ' . $accent . ';';
            $nonRecommendedIdx++;
        }
        $firmCount = $pkg->companies_count ?? 0;
    @endphp

    {{-- Outer wrapper: flex column so inner card stretches to full row height --}}
    <div style="display:flex; flex-direction:column; position:relative; {{ $isRecommended ? 'padding-top:16px;' : '' }}">

        {{-- Most Popular Badge --}}
        @if($isRecommended)
        <div style="position:absolute; top:0; left:50%; transform:translateX(-50%); background:#3b82f6; color:#fff; font-size:10px; font-weight:700; letter-spacing:1.5px; padding:4px 18px; border-radius:20px; white-space:nowrap; z-index:10;">
            MOST POPULAR
        </div>
        @endif

        {{-- Card: flex column + flex:1 so it stretches to wrapper height --}}
        <div style="flex:1; display:flex; flex-direction:column; background:#fff; border-radius:12px; {{ $cardBorder }} box-shadow:0 2px 8px rgba(0,0,0,.07); padding:24px;">

            {{-- Plan name & status --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span style="font-size:13px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase; color:{{ $accent }};">
                    {{ $pkg->name }}
                </span>
                <span style="font-size:12px; font-weight:600; color:{{ $pkg->is_active ? '#16a34a' : '#9ca3af' }};">
                    {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            {{-- Price --}}
            <div style="margin-bottom:4px; line-height:1;">
                @if($pkg->is_free || $pkg->monthly_price == 0)
                <span style="font-size:42px; font-weight:800; color:#111827;">Free</span>
                @else
                <span style="font-size:42px; font-weight:800; color:#111827;">${{ number_format($pkg->monthly_price, 0) }}</span><span style="font-size:14px; color:#6b7280; margin-left:2px;">/month</span>
                @endif
            </div>

            {{-- Subtitle --}}
            <p style="font-size:13px; color:#6b7280; margin:0 0 16px;">
                @if($pkg->max_users)
                    Up to {{ $pkg->max_users }} clients
                    @if($pkg->max_insights_per_month) &middot; {{ number_format($pkg->max_insights_per_month) }} insights/mo @endif
                @elseif($pkg->max_insights_per_month)
                    Unlimited clients &middot; {{ number_format($pkg->max_insights_per_month) }} insights/mo
                @else
                    Unlimited clients &amp; insights
                @endif
            </p>

            {{-- Features — grows to fill available space --}}
            @if($pkg->features && count($pkg->features))
            <ul style="list-style:none; padding:0; margin:0 0 16px; flex:1;">
                @foreach($pkg->features as $feature)
                <li style="display:flex; align-items:flex-start; gap:8px; font-size:13px; color:#374151; margin-bottom:7px;">
                    <span style="color:#16a34a; font-weight:700; font-size:14px; line-height:1.4; flex-shrink:0;">&#10003;</span>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
            @else
            <div style="flex:1;"></div>
            @endif

            {{-- Divider --}}
            <hr style="border:none; border-top:1px solid #e5e7eb; margin:0 0 12px;">

            {{-- Firms count --}}
            <p style="font-size:13px; color:#9ca3af; margin:0 0 16px;">
                {{ $firmCount }} {{ Str::plural('firm', $firmCount) }} on this plan
            </p>

            {{-- Actions --}}
            <div style="display:flex; gap:10px; align-items:center;">
                <a href="{{ route('superadmin.packages.edit', $pkg) }}"
                   style="padding:8px 22px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none;
                          background:{{ $isRecommended ? $accent : '#fff' }};
                          color:{{ $isRecommended ? '#fff' : '#374151' }};
                          border:1px solid {{ $isRecommended ? $accent : '#d1d5db' }};">
                    Edit
                </a>
                <button onclick="togglePackage({{ $pkg->id }}, this)"
                        data-active="{{ $pkg->is_active ? '1' : '0' }}"
                        style="padding:8px 18px; border-radius:6px; font-size:13px; font-weight:600; border:1px solid #fca5a5; background:#fff; color:#ef4444; cursor:pointer;">
                    {{ $pkg->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </div>

        </div>
    </div>
    @endforeach
</div>
@endif

@endsection

@push('scripts')
<script>
async function togglePackage(id, btn) {
    btn.disabled = true;
    const res = await fetch(`/superadmin/packages/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
        }
    }).then(r => r.json());

    if (res.status === 'success') {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(res.message || 'Error', 'error');
        btn.disabled = false;
    }
}
</script>
@endpush
