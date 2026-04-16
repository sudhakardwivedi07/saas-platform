@extends('layouts.app')
@section('page-title', 'Subscription & Usage')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Subscription &amp; Usage</h1>
        <p>Your current plan, usage metrics, and available upgrades</p>
    </div>
</div>

{{-- Current Plan Banner --}}
<div class="plan-card">
    <div class="plan-clabel">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        CURRENT PLAN
    </div>
    <div class="plan-name">{{ $subscription?->package?->name ?? 'No Plan' }}</div>

    <div style="display:flex;gap:28px;margin-top:14px;flex-wrap:wrap">
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--blue);font-weight:500;margin-bottom:3px">Billing Cycle</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">
                {{ $subscription ? ucfirst($subscription->billing_cycle ?? 'monthly') : '—' }}
            </div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--blue);font-weight:500;margin-bottom:3px">Next Billing</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">
                {{ $subscription?->current_period_end?->format('M j, Y') ?? '—' }}
            </div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--blue);font-weight:500;margin-bottom:3px">Status</div>
            <div>
                @php $subStatus = $subscription?->status ?? 'none'; @endphp
                @if($subStatus === 'active')
                    <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                @elseif($subStatus === 'trialing')
                    <span class="badge b-amber"><span class="badge-dot" style="background:var(--amber)"></span>Trial</span>
                @elseif($subStatus === 'past_due')
                    <span class="badge b-red"><span class="badge-dot" style="background:var(--red)"></span>Past Due</span>
                @else
                    <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>No Plan</span>
                @endif
            </div>
        </div>
        @if($subscription)
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--blue);font-weight:500;margin-bottom:3px">Price</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">
                ${{ number_format(($subscription->billing_cycle === 'annual' ? $subscription->package?->annual_price : $subscription->package?->monthly_price) ?? 0, 2) }}
                <span style="font-size:11.5px;font-weight:400;color:var(--text-3)">/{{ $subscription->billing_cycle === 'annual' ? 'yr' : 'mo' }}</span>
            </div>
        </div>
        @endif
    </div>

    <div class="plan-note">Need to upgrade? Choose a plan below.</div>
</div>

{{-- Usage Overview --}}
<div class="tc" style="margin-bottom:20px">
    <div class="tc-head">
        <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Usage Overview
        </div>
    </div>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Limit</th>
                    <th>Used</th>
                    <th style="width:130px">Usage</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pkg = $subscription?->package;
                    $userLimit    = $pkg?->max_users ?? 0;
                    $docLimit     = $pkg?->max_documents ?? 0;
                    $storageLimit = $pkg?->max_storage_gb ?? 0;
                    $qbLimit      = $pkg?->max_qb_connections ?? 0;

                    $userUsed    = $company?->users()->count() ?? 0;
                    $docUsed     = $company?->documents()->count() ?? 0;
                    $storageUsed = 0;
                    $qbUsed      = $company?->quickbooksConnections()->count() ?? 0;

                    $rows = [
                        ['label' => 'Users',           'limit' => $userLimit,    'used' => $userUsed,    'unit' => ''],
                        ['label' => 'Documents',        'limit' => $docLimit,     'used' => $docUsed,     'unit' => ''],
                        ['label' => 'Storage',          'limit' => $storageLimit, 'used' => $storageUsed, 'unit' => ' GB'],
                        ['label' => 'QB Connections',   'limit' => $qbLimit,      'used' => $qbUsed,      'unit' => ''],
                    ];
                @endphp
                @foreach($rows as $row)
                @php
                    $pct = ($row['limit'] > 0) ? min(100, round(($row['used'] / $row['limit']) * 100)) : 0;
                    $barColor = $pct >= 90 ? 'var(--red)' : ($pct >= 70 ? 'var(--amber)' : 'var(--blue)');
                @endphp
                <tr>
                    <td style="font-weight:500;color:var(--text-1)">{{ $row['label'] }}</td>
                    <td style="color:var(--text-2)">
                        {{ $row['limit'] > 0 ? $row['limit'] . $row['unit'] : '∞' }}
                    </td>
                    <td style="color:var(--text-1);font-weight:500">{{ $row['used'] }}{{ $row['unit'] }}</td>
                    <td>
                        @if($row['limit'] > 0)
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="ubar-wrap" style="flex:1;width:auto">
                                <div class="ubar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                            </div>
                            <span style="font-size:11.5px;color:var(--text-3);min-width:30px;text-align:right">{{ $pct }}%</span>
                        </div>
                        @else
                        <span style="font-size:12px;color:var(--text-3)">Unlimited</span>
                        @endif
                    </td>
                    <td>
                        @if($pct >= 90)
                            <span class="badge b-red" style="font-size:11px">Critical</span>
                        @elseif($pct >= 70)
                            <span class="badge b-amber" style="font-size:11px">High</span>
                        @else
                            <span class="badge b-green" style="font-size:11px">OK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Available Plans --}}
<div style="margin-bottom:8px">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:4px">Available Plans</div>
    <div style="font-size:13px;color:var(--text-2);margin-bottom:16px">Upgrade to unlock more features and capacity.</div>
</div>

@if($packages->isNotEmpty())
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
    @foreach($packages as $pkg)
    @php $isCurrent = $subscription?->package_id === $pkg->id; @endphp
    <div class="card" style="margin-bottom:0;position:relative;{{ $isCurrent ? 'border-color:var(--blue);' : '' }}{{ $pkg->is_recommended ?? false ? 'box-shadow:0 0 0 2px var(--orange),var(--sh-sm);' : '' }}">
        @if($pkg->is_recommended ?? false)
        <div style="position:absolute;top:-11px;left:50%;transform:translateX(-50%);white-space:nowrap">
            <span class="badge b-orange" style="font-size:11px">Most Popular</span>
        </div>
        @endif
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
            <div class="card-title" style="margin-bottom:0">{{ $pkg->name }}</div>
            @if($isCurrent)
                <span class="badge b-blue" style="font-size:11px">Current</span>
            @endif
        </div>
        @if($pkg->description)
        <div style="font-size:12px;color:var(--text-3);margin-bottom:10px;line-height:1.45">{{ Str::limit($pkg->description, 70) }}</div>
        @endif
        <div style="margin-bottom:14px">
            <span style="font-size:26px;font-weight:700;color:var(--text-1)">${{ number_format($pkg->monthly_price ?? 0, 0) }}</span>
            <span style="font-size:13px;color:var(--text-3)">/month</span>
        </div>
        <ul style="margin-bottom:16px;list-style:none">
            @if($pkg->max_users)
            <li style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--text-2);margin-bottom:5px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Up to {{ $pkg->max_users }} users
            </li>
            @endif
            @if($pkg->max_storage_gb)
            <li style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--text-2);margin-bottom:5px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                {{ $pkg->max_storage_gb }} GB storage
            </li>
            @endif
            @if($pkg->is_trial_allowed ?? false)
            <li style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--text-2);margin-bottom:5px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ $pkg->trial_days ?? 0 }}-day free trial
            </li>
            @endif
            @if(!empty($pkg->features))
                @foreach(array_slice(is_array($pkg->features) ? $pkg->features : [], 0, 3) as $feat)
                <li style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--text-2);margin-bottom:5px">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ Str::title(str_replace('_', ' ', $feat)) }}
                </li>
                @endforeach
            @endif
        </ul>
        @if($isCurrent)
            <button disabled class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;opacity:.55;cursor:not-allowed">Current Plan</button>
        @else
            <button class="btn btn-primary btn-sm" style="width:100%;justify-content:center" onclick="startCheckout({{ $pkg->id }}, this)">
                Upgrade
            </button>
        @endif
    </div>
    @endforeach
</div>
@else
<div class="tc">
    <div style="text-align:center;padding:48px 20px">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <div style="font-size:14px;font-weight:500;color:var(--text-2)">No plans available</div>
        <div style="font-size:12.5px;color:var(--text-3);margin-top:5px">Contact support to configure your subscription options.</div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
async function startCheckout(packageId, btn) {
    const orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Redirecting…';
    try {
        const res = await fetch('{{ route("billing.checkout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ package_id: packageId, billing_cycle: 'monthly' })
        });
        const json = await res.json();
        if (json.checkout_url) {
            window.location.href = json.checkout_url;
        } else {
            showToast(json.message || 'Could not start checkout.', 'error');
            btn.disabled = false;
            btn.textContent = orig;
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = orig;
    }
}
</script>
@endpush
