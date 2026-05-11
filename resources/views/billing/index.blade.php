@extends('layouts.app')
@section('title', 'Subscription & Usage')
@section('page-title', 'Subscription & Usage')

@push('styles')
<style>
/* ── Current Plan Banner ────────────────────────────── */
.plan-banner {
    background: linear-gradient(135deg, #1a3a5c 0%, #1a6aa8 100%);
    border-radius: 14px;
    padding: 26px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.plan-banner-left .plan-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: rgba(255,255,255,.65);
    margin-bottom: 6px;
}
.plan-banner-left .plan-name {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
}
.plan-banner-left .plan-meta {
    font-size: 13px;
    color: rgba(255,255,255,.75);
}
.plan-banner-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.plan-status-badge {
    background: rgba(255,255,255,.15);
    border: 1.5px solid rgba(255,255,255,.35);
    color: #fff;
    border-radius: 8px;
    padding: 5px 14px;
    font-size: 13px;
    font-weight: 600;
}
.plan-cancel-btn {
    background: rgba(255,255,255,.12);
    border: 1.5px solid rgba(255,255,255,.4);
    color: #fff;
    border-radius: 8px;
    padding: 7px 16px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background .13s;
}
.plan-cancel-btn:hover { background: rgba(255,255,255,.22); }
.plan-reactivate-btn {
    background: rgba(22,163,74,.2);
    border: 1.5px solid rgba(22,163,74,.6);
    color: #fff;
    border-radius: 8px;
    padding: 7px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background .13s;
}
.plan-reactivate-btn:hover { background: rgba(22,163,74,.35); }

/* ── Plan Cards ─────────────────────────────────────── */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.plan-card {
    background: #fff;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 22px 20px 18px;
    position: relative;
    display: flex;
    flex-direction: column;
}
.plan-card.is-current { border-color: var(--orange); box-shadow: 0 0 0 1px var(--orange); }
.plan-card-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--orange);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    padding: 3px 12px;
    border-radius: 20px;
    white-space: nowrap;
}
.plan-card-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--text-3);
    margin-bottom: 8px;
}
.plan-card.is-current .plan-card-label { color: var(--orange); }
.plan-card-price {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-1);
    margin-bottom: 4px;
    line-height: 1;
}
.plan-card-price span { font-size: 14px; font-weight: 400; color: var(--text-3); }
.plan-card-sub { font-size: 12.5px; color: var(--text-3); margin-bottom: 16px; }
.plan-card-features { list-style: none; margin-bottom: 20px; flex: 1; }
.plan-card-features li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-2);
    margin-bottom: 8px;
}
.plan-card-features li svg { flex-shrink: 0; }

/* ── Plan Details table ─────────────────────────────── */
.plan-details-table {
    width: 100%;
    border-collapse: collapse;
}
.plan-details-table thead th {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--text-3);
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    text-align: left;
}
.plan-details-table tbody td {
    padding: 14px 14px;
    font-size: 13px;
    color: var(--text-1);
    border-bottom: 1px solid var(--bg);
}

/* ── Usage KPI cards ────────────────────────────────── */
.usage-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.usage-kpi {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid var(--border);
}
.usage-kpi.bl { border-left-color: var(--blue); }
.usage-kpi.or { border-left-color: var(--orange); }
.usage-kpi.rd { border-left-color: var(--red); }
.usage-kpi-lbl { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--text-3); margin-bottom: 10px; }
.usage-kpi-val { font-size: 32px; font-weight: 700; color: var(--text-1); margin-bottom: 4px; line-height: 1; }
.usage-kpi-val.orange { color: var(--orange); }
.usage-kpi-val.red    { color: var(--red); }
.usage-kpi-ctx { font-size: 12.5px; color: var(--text-3); }

/* ── Usage progress bar ─────────────────────────────── */
.mini-bar-wrap { width: 80px; height: 5px; background: var(--bg); border-radius: 3px; overflow: hidden; }
.mini-bar      { height: 100%; border-radius: 3px; }

@media(max-width:960px){
    .plans-grid { grid-template-columns: 1fr; }
    .usage-kpi-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Subscription &amp; Usage</h1>
        <p>Manage your Insights plan and monitor company-level consumption</p>
    </div>
</div>


{{-- ── 1. Current Plan Banner ──────────────────────── --}}
@php
    $pkg        = $subscription?->package;
    $subStatus  = $subscription?->status ?? 'none';
    $renewsAt   = $subscription?->current_period_end;
    $planName   = $pkg?->name ?? 'No Plan';
    $maxClients = $pkg?->max_users ?? 0;
    $maxInsights = $pkg?->max_documents ?? 0;
    $price      = $pkg?->monthly_price ?? 0;
@endphp

<div class="plan-banner">
    <div class="plan-banner-left">
        <div class="plan-label">Current Plan</div>
        <div class="plan-name">{{ $planName }}</div>
        <div class="plan-meta">
            @if($maxClients) {{ $maxClients }} clients · @endif
            @if($maxInsights) {{ number_format($maxInsights) }} insights / month · @endif
            @if($price) ${{ number_format($price, 0) }} / month @endif
            @if($subStatus === 'canceled' && $subscription?->ends_at)
                · Access until {{ $subscription->ends_at->format('M j, Y') }}
            @elseif($renewsAt)
                · Renews {{ $renewsAt->format('M j, Y') }}
            @endif
        </div>
    </div>
    <div class="plan-banner-right">
        @if($subStatus === 'active')
        <span class="plan-status-badge">Active</span>
        @elseif($subStatus === 'trialing')
        <span class="plan-status-badge" style="background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.5)">Trial</span>
        @elseif($subStatus === 'past_due')
        <span class="plan-status-badge" style="background:rgba(239,68,68,.2);border-color:rgba(239,68,68,.5)">Past Due</span>
        @elseif($subStatus === 'canceled')
        <span class="plan-status-badge" style="background:rgba(239,68,68,.18);border-color:rgba(239,68,68,.5)">Canceled</span>
        @else
        <span class="plan-status-badge" style="background:rgba(255,255,255,.1)">No Plan</span>
        @endif

        @if($subStatus === 'canceled' && $subscription?->ends_at && $subscription->ends_at->isFuture())
        <button class="plan-reactivate-btn" onclick="reactivateSubscription()">Reactivate Subscription</button>
        @elseif(in_array($subStatus, ['active', 'trialing', 'past_due']) && $subscription)
        <button class="plan-cancel-btn" onclick="cancelSubscription()">Cancel Subscription</button>
        @endif
    </div>
</div>

{{-- ── 2. Available Plans ───────────────────────────── --}}
<div style="margin-bottom:16px">
    <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:4px">Available Plans</div>
    @if($pkg)
    <div style="font-size:13px;color:var(--text-3)">You are on the <strong>{{ $planName }}</strong> plan. You can switch to any plan below. A switch takes effect from the next billing cycle.</div>
    @else
    <div style="font-size:13px;color:var(--text-3)">Choose a plan to get started.</div>
    @endif
</div>

@if($packages->isNotEmpty())
<div class="plans-grid">
    @foreach($packages as $p)
    @php $isCurrent = $subscription?->package_id === $p->id; @endphp
    <div class="plan-card {{ $isCurrent ? 'is-current' : '' }}">
        @if($isCurrent)
        <div class="plan-card-badge">Current Plan</div>
        @endif
        <div class="plan-card-label">{{ strtoupper($p->name) }}</div>
        <div class="plan-card-price">
            ${{ number_format($p->monthly_price ?? 0, 0) }}<span>/month</span>
        </div>
        <div class="plan-card-sub">
            @if($p->max_users) Up to {{ $p->max_users }} clients @else Unlimited clients @endif
        </div>
        <ul class="plan-card-features">
            @if($p->max_users)
            <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                {{ $p->max_users }} clients
            </li>
            @endif
            @if($p->max_documents)
            <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                {{ number_format($p->max_documents) }} insights / month
            </li>
            @endif
            @if(!empty($p->features) && is_array($p->features))
                @foreach(array_slice($p->features, 0, 5) as $feat)
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ Str::title(str_replace('_', ' ', $feat)) }}
                </li>
                @endforeach
            @else
                @if($p->max_storage_gb)
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ $p->max_storage_gb }}GB storage
                </li>
                @endif
                @if($p->is_trial_allowed)
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ $p->trial_days }}-day free trial
                </li>
                @endif
            @endif
        </ul>
        <div style="margin-top:auto">
            @if($isCurrent)
            <button disabled class="btn btn-primary" style="width:100%;justify-content:center;cursor:not-allowed;opacity:.85">Current Plan</button>
            @else
            <button class="btn {{ $subscription ? 'btn-ghost' : 'btn-primary' }}" style="width:100%;justify-content:center"
                    onclick="startCheckout({{ $p->id }}, this)">
                {{ $subscription ? ($p->monthly_price > ($pkg?->monthly_price ?? 0) ? 'Upgrade to ' : 'Switch to ') . $p->name : 'Get ' . $p->name }}
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── 3. Plan Details table ───────────────────────── --}}
@if($subscription && $pkg)
<div class="tc" style="margin-bottom:24px">
    <div class="tc-head" style="padding-bottom:0">
        <div style="font-size:14px;font-weight:600;color:var(--text-1)">Plan Details</div>
    </div>
    <div style="overflow-x:auto">
        <table class="plan-details-table">
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Total Clients</th>
                    <th>Insight Limit</th>
                    <th>Price</th>
                    <th>Billing Cycle</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:600">{{ $pkg->name }}</td>
                    <td>{{ $pkg->max_users ? $pkg->max_users . ' clients' : 'Unlimited' }}</td>
                    <td>{{ $pkg->max_documents ? number_format($pkg->max_documents) . ' insights / month' : 'Unlimited' }}</td>
                    <td style="font-weight:600;color:var(--blue)">${{ number_format($pkg->monthly_price ?? 0, 0) }} / month</td>
                    <td>{{ ucfirst($subscription->billing_cycle ?? 'monthly') }}</td>
                    <td>
                        @if($subStatus === 'active')
                            <span class="badge b-green">Active</span>
                        @elseif($subStatus === 'trialing')
                            <span class="badge b-amber">Trial</span>
                        @elseif($subStatus === 'past_due')
                            <span class="badge b-red">Past Due</span>
                        @else
                            <span class="badge b-gray">{{ ucfirst($subStatus) }}</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── 4. Usage KPI cards ──────────────────────────── --}}
<div class="usage-kpi-grid">
    <div class="usage-kpi bl">
        <div class="usage-kpi-lbl">Total Insights Used (MTD)</div>
        <div class="usage-kpi-val">{{ $totalInsightsUsed ?? 0 }}</div>
        <div class="usage-kpi-ctx">Across {{ ($subCompanies ?? collect())->count() }} companies</div>
    </div>
    <div class="usage-kpi or">
        <div class="usage-kpi-lbl">Total Insights Remaining</div>
        <div class="usage-kpi-val orange">{{ $totalInsightsRemaining ?? 0 }}</div>
        <div class="usage-kpi-ctx">Resets {{ now()->startOfMonth()->addMonth()->format('F j, Y') }}</div>
    </div>
    <div class="usage-kpi rd">
        <div class="usage-kpi-lbl">Companies at Risk</div>
        <div class="usage-kpi-val red">{{ $companiesAtRisk ?? 0 }}</div>
        <div class="usage-kpi-ctx">
            {{ !empty($atRiskContext) ? $atRiskContext : 'No issues detected' }}
        </div>
    </div>
</div>

{{-- ── 5. Company-wise Usage DataTable ───────────────── --}}
<div class="tc">
    <div class="tc-head">
        <div style="font-size:14px;font-weight:600;color:var(--text-1)">Company-wise Usage</div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12.5px;color:var(--text-3)">{{ ($subCompanies ?? collect())->count() }} companies across {{ $uniqueClients ?? 0 }} clients</span>
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="usage-search" placeholder="Search companies…" autocomplete="off">
            </div>
            <select class="sel" id="usage-status-filter" autocomplete="off">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="quota full">Quota Full</option>
                <option value="no usage">No Usage</option>
            </select>
        </div>
    </div>
    <table id="usage-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Client</th>
                    <th>Plan Tier</th>
                    <th>QB</th>
                    <th>Insights Used</th>
                    <th>Remaining</th>
                    <th>Last Generated</th>
                    <th>Status</th>
                    <th>Usage</th>
                </tr>
            </thead>
            <tbody>
                @php $subCompanies = $subCompanies ?? collect(); @endphp
                @foreach($subCompanies as $co)
                @php
                    $owner      = $co->users->first();
                    $plan       = $co->clientPackage ?? $co->package;
                    $planName   = $plan?->name ?? '—';
                    $limit      = $plan?->max_documents ?? 0;
                    $used       = $co->insights_used ?? 0;
                    $remaining  = $limit > 0 ? max(0, $limit - $used) : '∞';
                    $pct        = ($limit > 0 && $used > 0) ? min(100, round(($used / $limit) * 100)) : 0;
                    $isQB       = $co->quickbooksConnections->where('is_active', true)->isNotEmpty();
                    $coStatus   = $co->status ?? 'inactive';

                    if ($coStatus === 'inactive') {
                        $usageStatus = 'inactive';
                        $usageBadge  = 'b-gray';
                        $usageLabel  = 'Inactive';
                    } elseif ($pct >= 100) {
                        $usageStatus = 'quota full';
                        $usageBadge  = 'b-red';
                        $usageLabel  = 'Quota Full';
                    } elseif ($used === 0) {
                        $usageStatus = 'no usage';
                        $usageBadge  = 'b-gray';
                        $usageLabel  = 'No Usage';
                    } else {
                        $usageStatus = 'active';
                        $usageBadge  = 'b-green';
                        $usageLabel  = 'Active';
                    }

                    $barColor = match($usageStatus) {
                        'quota full' => 'var(--red)',
                        'active'     => 'var(--blue)',
                        default      => 'var(--border)',
                    };
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13px;color:var(--text-1)">{{ $co->company_name }}</div>
                        @if($co->subdomain)
                        <div style="font-size:11px;color:var(--text-3)">{{ $co->subdomain }}</div>
                        @endif
                    </td>
                    <td style="font-size:13px;color:var(--text-2)">{{ $owner?->name ?? '—' }}</td>
                    <td>
                        @if($planName !== '—')
                            <span class="badge b-blue" style="font-size:11px">{{ $planName }}</span>
                        @else
                            <span style="color:var(--text-3)">—</span>
                        @endif
                    </td>
                    <td>
                        @if($isQB)
                            <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:13px;font-weight:600;color:{{ $pct >= 100 ? 'var(--red)' : 'var(--text-1)' }}">{{ $used }}</span>
                    </td>
                    <td style="font-size:13px;color:{{ $remaining === 0 ? 'var(--red)' : 'var(--text-2)' }}">
                        {{ is_numeric($remaining) ? $remaining : $remaining }}
                    </td>
                    <td style="font-size:12.5px;color:{{ $co->last_active_at ? 'var(--text-2)' : 'var(--text-3)' }}">
                        {{ $co->last_active_at ? $co->last_active_at->diffForHumans() : 'N/A' }}
                    </td>
                    <td data-search="{{ $usageStatus }}">
                        <span class="badge {{ $usageBadge }}" style="font-size:11.5px">{{ $usageLabel }}</span>
                    </td>
                    <td>
                        <div class="mini-bar-wrap">
                            <div class="mini-bar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
$(document).ready(function() {
    document.getElementById('usage-search').value        = '';
    document.getElementById('usage-status-filter').value = '';

    var table = $('#usage-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
        columnDefs: [{ orderable: false, targets: [3, 8] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ companies',
            infoEmpty: 'No companies',
            paginate: { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:48px 20px"><svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.4" style="margin:0 auto 12px;display:block"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg><div style="font-size:14px;font-weight:500;color:#6b7280">No companies yet</div><div style="font-size:12.5px;color:#9ca3af;margin-top:4px">Add companies to track their usage here.</div></div>',
        }
    });

    document.getElementById('usage-search').addEventListener('keyup', function() { table.search(this.value).draw(); });
    document.getElementById('usage-status-filter').addEventListener('change', function() {
        table.column(7).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
});

async function startCheckout(packageId, btn) {
    const orig = btn.textContent.trim();
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

async function reactivateSubscription() {
    const result = await Swal.fire({
        title: 'Reactivate Subscription?',
        text: 'Your subscription will be restored and you will continue to be billed at the end of your current billing period.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reactivate',
        cancelButtonText: 'Not Now',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    Swal.fire({
        title: 'Reactivating…',
        text: 'Please wait while we restore your subscription.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const resp = await fetch('{{ route("billing.reactivate-subscription") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                'Content-Type':     'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        });
        const res = await resp.json();

        if (res.status === 'success') {
            await Swal.fire({
                icon:             'success',
                title:            'Subscription Reactivated',
                text:             res.message || 'Welcome back! Your subscription is now active.',
                confirmButtonText: 'Go to Dashboard',
            });
            window.location.reload();
        } else {
            Swal.fire({
                icon:  'error',
                title: 'Reactivation Failed',
                text:  res.message || 'Something went wrong. Please try again.',
            });
        }
    } catch (e) {
        Swal.fire({
            icon:  'error',
            title: 'Network Error',
            text:  'Could not reach the server. Please try again.',
        });
    }
}

function cancelSubscription() {
    Swal.fire({
        title: 'Cancel Subscription?',
        text: 'Your plan will remain active until the end of the current billing period. You can re-subscribe at any time.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel Subscription',
        cancelButtonText: 'Keep Subscription',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(async function(result) {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Canceling…',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const resp = await fetch('{{ route("billing.cancel-subscription") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                    'Content-Type':     'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                },
            });
            const res = await resp.json();

            if (res.status === 'success') {
                await Swal.fire({
                    icon:             'success',
                    title:            'Subscription Canceled',
                    text:             res.message || 'You will retain access until the end of the current billing period.',
                    confirmButtonText:'OK',
                });
                window.location.reload();
            } else {
                Swal.fire({
                    icon:  'error',
                    title: 'Cancellation Failed',
                    text:  res.message || 'Something went wrong. Please try again.',
                });
            }
        } catch (e) {
            Swal.fire({
                icon:  'error',
                title: 'Network Error',
                text:  'Could not reach the server. Please try again.',
            });
        }
    });
}
</script>
@endpush
