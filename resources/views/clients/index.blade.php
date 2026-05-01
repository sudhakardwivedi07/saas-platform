@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>Client Management</h1>
        <p>Manage clients and their companies across your partner portfolio</p>
    </div>
    <div class="ph-actions">
        @if($hasActivePlan ?? false)
        <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-add-client').classList.add('open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
        @else
        <button type="button" class="btn btn-primary" disabled title="No plan is assigned to this account. Choose a plan under Subscription &amp; Usage first.">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
        @endif
    </div>
</div>

@if(!($hasActivePlan ?? false))
<div class="info-banner" style="margin-bottom: 1rem;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span class="info-banner-txt">
        No plan is assigned to this account. Select a subscription plan on
        <a href="{{ route('billing.index') }}" style="color:var(--blue);font-weight:600;text-decoration:none">Subscription &amp; Usage</a>
        before adding clients or companies.
    </span>
</div>
@endif

{{-- Tab Bar --}}
<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-clients">
        Clients
        <span class="tab-badge">{{ $clients->count() }}</span>
    </button>
    <button class="tab-btn" data-tab="tab-companies">
        Companies
        <span class="tab-badge">{{ $companies->count() }}</span>
    </button>
</div>

{{-- ── Tab: Clients ──────────────────────────────────────── --}}
<div class="tab-panel active" id="tab-clients">
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="client-search" placeholder="Search clients…" autocomplete="off">
                </div>
                <select class="sel" id="client-status-filter" autocomplete="off">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <span class="badge b-gray" id="client-count-badge">{{ $clients->count() }} clients</span>
        </div>
        <table id="clients-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Client</th>
                    <th style="text-align:center">Companies</th>
                    <th>Status</th>
                    <th style="text-align:center">QB Coverage</th>
                    <th style="text-align:center">Insights Used</th>
                    <th style="text-align:center">Remaining</th>
                    <th>Last Activity</th>
                    <th style="width:48px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                @php
                    $initials     = strtoupper(substr($client->name, 0, 1)) . strtoupper(substr(strstr($client->name, ' ') ?: ' ', 1, 1));
                    $status       = $client->status ?? 'active';
                    $co           = $client->company;
                    $qbConnected  = $co && $co->quickbooksConnections->where('is_active', true)->isNotEmpty() ? 1 : 0;
                    $qbTotal      = $co ? 1 : 0;
                    $plan         = $co ? ($co->clientPackage ?? $co->package) : null;
                    $insightsUsed = $co?->insights_used ?? 0;
                    $maxInsights  = $plan
                        ? ($plan->insights_per_month ?? $plan->max_insights_per_month ?? $plan->max_documents ?? null)
                        : null;
                    $remaining    = $maxInsights !== null ? max(0, (int) $maxInsights - (int) $insightsUsed) : null;
                    $lastActivity = $co?->last_active_at ?? $client->updated_at ?? $client->created_at;
                @endphp
                <tr data-search="{{ strtolower($client->name . ' ' . $client->email . ' ' . $status) }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:9px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;letter-spacing:-.2px">{{ $initials }}</div>
                            <div>
                                <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $client->name }}</div>
                                <div style="font-size:11.5px;color:var(--text-3)">{{ $client->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:center;font-size:13px;color:var(--text-2)">{{ $qbTotal }}</td>
                    <td data-search="{{ $status }}">
                        @if($status === 'active')
                            <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                        @elseif($status === 'suspended')
                            <span class="badge b-red"><span class="badge-dot" style="background:var(--red)"></span>Suspended</span>
                        @else
                            <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        @if($qbTotal > 0)
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:500;color:{{ $qbConnected === $qbTotal ? 'var(--green)' : ($qbConnected > 0 ? '#f59e0b' : 'var(--red)') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><circle cx="12" cy="20" r="1" fill="currentColor"/>
                                </svg>
                                {{ $qbConnected }}/{{ $qbTotal }}
                            </span>
                        @else
                            <span style="color:var(--text-3);font-size:13px">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;font-size:13px;font-weight:500;color:var(--text-1)">{{ $insightsUsed }}</td>
                    <td style="text-align:center;font-size:13px;font-weight:500;color:{{ $remaining === 0 ? 'var(--red)' : 'var(--text-1)' }}">
                        @if($remaining === null)
                            <span style="color:var(--text-3)">—</span>
                        @else
                            {{ $remaining }}
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:var(--text-2)">{{ $lastActivity?->diffForHumans() ?? '—' }}</td>
                    <td>
                        <div class="dm-wrap">
                            <button class="dot-menu" data-menu="dm-cl-{{ $client->id }}" title="Actions">•••</button>
                            <div class="dm-popup" id="dm-cl-{{ $client->id }}">
                                <a href="{{ route('clients.show', $client) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    View Client Details
                                </a>
                                <a href="{{ route('clients.dashboard', $client) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                    View Dashboard
                                </a>
                                <a href="{{ route('clients.insights', $client) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    View Insights
                                </a>
                                @if($co)
                                <a href="javascript:void(0)" class="dm-item" onclick="openAssignClientPlanModal({{ $co->id }}, '{{ addslashes($client->name) }}', {{ $co->client_package_id ?? 'null' }})">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                    Assign Plan
                                </a>
                                @endif
                                <div class="dm-divider"></div>
                                <a href="javascript:void(0)" class="dm-item dm-danger" onclick="toggleClientStatus({{ $client->id }}, '{{ addslashes($client->name) }}', '{{ $status }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    {{ $status === 'active' ? 'Disable Client' : 'Enable Client' }}
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Tab: Companies ────────────────────────────────────── --}}
<div class="tab-panel" id="tab-companies">
    <div class="tc">
        <div class="tc-head" style="flex-wrap:wrap;gap:10px">
            <div class="tc-filters" style="flex-wrap:wrap;gap:8px">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" id="company-search" placeholder="Search companies…" autocomplete="off">
                </div>
                <select class="sel" id="company-status-filter" autocomplete="off">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="quota full">Quota Full</option>
                    <option value="inactive">Inactive</option>
                    <option value="trial">Trial</option>
                    <option value="suspended">Suspended</option>
                </select>
                <select class="sel" id="company-qb-filter" autocomplete="off">
                    <option value="">QB Status</option>
                    <option value="connected">Connected</option>
                    <option value="not connected">Not Connected</option>
                </select>
                <select class="sel" id="company-client-filter" autocomplete="off">
                    <option value="">All Clients</option>
                    @foreach($clientsList as $cl)
                    <option value="{{ $cl->name }}">{{ $cl->name }}</option>
                    @endforeach
                </select>
                {{-- Date range filter --}}
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="position:relative;display:flex;align-items:center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="2" width="13" height="13" style="position:absolute;left:9px;pointer-events:none"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="co-date-from" class="f-inp" style="height:34px;padding-left:28px;width:140px;font-size:12.5px" autocomplete="off">
                    </div>
                    <span style="color:var(--text-3);font-size:13px">–</span>
                    <div style="position:relative;display:flex;align-items:center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="2" width="13" height="13" style="position:absolute;left:9px;pointer-events:none"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="co-date-to" class="f-inp" style="height:34px;padding-left:28px;width:140px;font-size:12.5px" autocomplete="off">
                    </div>
                </div>
            </div>
            @if($hasActivePlan ?? false)
            <a href="{{ route('my-companies.create') }}" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Company
            </a>
            @else
            <button type="button" class="btn btn-primary btn-sm" disabled title="No plan is assigned. Choose a plan under Subscription &amp; Usage first.">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Company
            </button>
            @endif
        </div>

        <table id="companies-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th style="text-align:center">QB</th>
                    <th style="text-align:center">Insights Used</th>
                    <th style="text-align:center">Remaining</th>
                    <th>Last Insight</th>
                    <th style="width:48px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                @php
                    $owner        = $company->users->first();
                    $isQB         = $company->quickbooksConnections->where('is_active', true)->isNotEmpty();
                    $plan         = $company->clientPackage ?? $company->package;
                    $maxDocs      = $plan
                        ? ($plan->insights_per_month ?? $plan->max_insights_per_month ?? $plan->max_documents ?? null)
                        : null;
                    $insightsUsed = (int) ($company->insights_used ?? 0);
                    $remaining    = $maxDocs !== null ? max(0, (int) $maxDocs - $insightsUsed) : null;
                    $isQuotaFull  = $maxDocs !== null && $remaining === 0 && ($company->status ?? '') === 'active';
                    $rawStatus    = $company->status ?? 'active';
                    $displayStatus = $isQuotaFull ? 'quota full' : strtolower($rawStatus);
                    $lastInsight  = null; // wire to insights table when available
                    $lastInsightOld = false;
                    $colors = ['#1a6aa8','#f06b21','#10b981','#7c3aed','#e11d48','#0d9488'];
                    $avatarColor = $colors[$company->id % count($colors)];
                    $initials = strtoupper(substr($company->company_name, 0, 1))
                              . strtoupper(substr(strstr($company->company_name, ' ') ?: '  ', 1, 1));
                @endphp
                <tr data-created="{{ $company->created_at->format('Y-m-d') }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:9px;background:{{ $avatarColor }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">{{ $initials }}</div>
                            <div>
                                <div style="font-weight:600;font-size:13px;color:var(--text-1)">{{ $company->company_name }}</div>
                                @if($company->company_email)
                                <div style="font-size:11px;color:var(--text-3)">{{ $company->company_email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px;color:var(--text-2)">
                        @if($owner)
                        <a href="{{ route('clients.show', $owner->id) }}" style="color:var(--blue);text-decoration:none;font-size:13px">{{ $owner->name }}</a>
                        @else
                        <span style="color:var(--text-3)">—</span>
                        @endif
                    </td>
                    <td data-search="{{ $displayStatus }}">
                        @if($isQuotaFull)
                            <span class="badge b-red" style="font-size:11.5px"><span class="badge-dot" style="background:var(--red)"></span>Quota Full</span>
                        @elseif($rawStatus === 'active')
                            <span class="badge b-green" style="font-size:11.5px"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                        @elseif($rawStatus === 'trial')
                            <span class="badge b-amber" style="font-size:11.5px"><span class="badge-dot" style="background:var(--amber)"></span>Trial</span>
                        @elseif($rawStatus === 'suspended')
                            <span class="badge b-red" style="font-size:11.5px"><span class="badge-dot" style="background:var(--red)"></span>Suspended</span>
                        @else
                            <span class="badge b-gray" style="font-size:11.5px"><span class="badge-dot" style="background:var(--text-3)"></span>Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:center" data-search="{{ $isQB ? 'connected' : 'not connected' }}">
                        @if($isQB)
                            <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="17" height="17"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" width="17" height="17"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        @endif
                    </td>
                    <td style="text-align:center;font-size:13px;color:var(--text-1);font-weight:500">
                        {{ $insightsUsed }}
                    </td>
                    <td style="text-align:center;font-size:13px;font-weight:600;color:{{ ($remaining !== null && $remaining === 0) ? 'var(--red)' : 'var(--text-1)' }}">
                        @if($remaining === null)
                            <span style="color:var(--text-3);font-weight:500">—</span>
                        @else
                            {{ $remaining }}
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:{{ $lastInsightOld ? 'var(--red)' : 'var(--text-2)' }}">
                        {{ $lastInsight ? \Carbon\Carbon::parse($lastInsight)->diffForHumans() : '—' }}
                    </td>
                    <td>
                        <div class="dm-wrap">
                            <button class="dot-menu" data-menu="dm-co-{{ $company->id }}" title="Actions">•••</button>
                            <div class="dm-popup" id="dm-co-{{ $company->id }}">
                                <a href="{{ route('my-companies.edit', $company->id) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </a>
                                @if($owner)
                                <a href="{{ route('clients.dashboard', $owner->id) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                    View Dashboard
                                </a>
                                <a href="{{ route('clients.insights', $owner->id) }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    View Insights
                                </a>
                                @endif
                                <a class="dm-item" href="javascript:void(0)" onclick="openAssignPlanModal({{ $company->id }}, '{{ addslashes($company->company_name) }}', {{ $company->client_package_id ?? 'null' }})">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                    Assign Plan
                                </a>
                                <div class="dm-divider"></div>
                                <form method="POST" action="{{ route('my-companies.destroy', $company->id) }}" onsubmit="return confirmCompanyDelete(this);">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dm-item dm-danger" style="width:100%;text-align:left;border:0;background:transparent;cursor:pointer">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2"/><path d="M19 6l-1 14a1 1 0 01-1 1H7a1 1 0 01-1-1L5 6"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Assign Plan Modal (Clients tab) ─────────────────── --}}
<div class="modal-overlay" id="modal-assign-client-plan" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:420px">
        <button class="modal-close" onclick="document.getElementById('modal-assign-client-plan').classList.remove('open')">&times;</button>
        <div class="modal-title">Assign Plan</div>
        <div class="modal-sub" id="assign-client-plan-company-name"></div>
        <div class="mf-field" style="margin-top:4px">
            <label class="mf-label">Select Plan</label>
            <select name="client_package_id" id="assign-client-plan-select" class="mf-sel">
                <option value="">— No Plan —</option>
                @foreach($clientPackages ?? [] as $pkg)
                <option value="{{ $pkg->id }}">{{ $pkg->name }}@if($pkg->monthly_price) — ${{ number_format($pkg->monthly_price, 2) }}/mo @endif</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" id="assign-client-plan-company-id">
        <div class="mf-foot">
            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-assign-client-plan').classList.remove('open')">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="submitAssignClientPlan()">Assign Plan</button>
        </div>
    </div>
</div>

{{-- Assign Plan Modal --}}
<div class="modal-overlay" id="modal-assign-plan" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:420px">
        <button class="modal-close" onclick="document.getElementById('modal-assign-plan').classList.remove('open')">&times;</button>
        <div class="modal-title">Assign Plan</div>
        <div class="modal-sub" id="assign-plan-company-name"></div>
        <form id="assign-plan-form" method="POST">
            @csrf @method('POST')
            <input type="hidden" id="assign-plan-company-id">
            <div class="mf-field" style="margin-top:4px">
                <label class="mf-label">Select Plan</label>
                <select name="client_package_id" id="assign-plan-select" class="mf-sel" required>
                    <option value="">— No Plan —</option>
                    @foreach($clientPackages ?? [] as $pkg)
                    <option value="{{ $pkg->id }}">{{ $pkg->name }}@if($pkg->monthly_price) — ${{ number_format($pkg->monthly_price, 2) }}/mo @endif</option>
                    @endforeach
                </select>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-assign-plan').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Assign Plan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Add Client Modal ─────────────────────────────────── --}}
{{-- ── Add New Client Modal ──────────────────────────────── --}}
<div class="modal-overlay {{ $errors->any() ? 'open' : '' }}" id="modal-add-client" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:560px">
        <button class="modal-close" onclick="document.getElementById('modal-add-client').classList.remove('open')">&times;</button>
        <div class="modal-title">Add New Client</div>
        <div class="modal-sub">Invite a new client to your partner portal</div>

        {{-- Tab Toggle --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;background:var(--bg);border-radius:10px;padding:4px;margin-bottom:20px;gap:0">
            <button type="button" id="tab-manual-btn" onclick="switchAddClientTab('manual')"
                style="border:none;border-radius:8px;padding:9px 0;font-size:13px;font-weight:600;cursor:pointer;background:var(--orange);color:#fff;transition:all .15s">
                Manual Entry
            </button>
            <button type="button" id="tab-bulk-btn" onclick="switchAddClientTab('bulk')"
                style="border:none;border-radius:8px;padding:9px 0;font-size:13px;font-weight:600;cursor:pointer;background:transparent;color:var(--text-2);transition:all .15s">
                Bulk Upload
            </button>
        </div>

        @if($errors->any())
        <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 13px;margin-bottom:14px">
            @foreach($errors->all() as $error)
            <div style="font-size:12.5px;color:var(--red);display:flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ $error }}
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── Manual Entry Panel ─────────────────────────── --}}
        <div id="panel-manual">
            <form method="POST" action="{{ route('clients.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Full Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="name" class="mf-inp"
                            placeholder="e.g. Sarah Chen" value="{{ old('name') }}" required>
                    </div>
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Email ID <span style="color:var(--red)">*</span></label>
                        <input type="email" name="email" class="mf-inp"
                            placeholder="sarah@chen.com" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Phone Number</label>
                        <input type="tel" name="phone" class="mf-inp" placeholder="+1 (555) 000-0000" value="{{ old('phone') }}">
                    </div>
                    <div class="mf-field" style="margin:0 0 14px">
                        <label class="mf-label">Company Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="company_name" class="mf-inp" placeholder="e.g. TechCorp Inc." value="{{ old('company_name') }}" required>
                    </div>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Industry Type <span style="color:var(--red)">*</span></label>
                    <select name="industry_type" class="mf-sel" style="height:40px" required>
                        <option value="" disabled {{ old('industry_type') ? '' : 'selected' }}>Select Industry Type</option>
                        @foreach(['Technology','Healthcare','Finance','Manufacturing','Retail','Real Estate','Construction','Transportation','Energy','Agriculture','Education','Consulting','Legal Services','Entertainment','Automotive','Other'] as $ind)
                        <option value="{{ $ind }}" {{ old('industry_type') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Plan Tier</label>
                    <select name="client_package_id" class="mf-sel" style="height:40px">
                        <option value="">— No Plan —</option>
                        @foreach($clientPackages ?? [] as $pkg)
                        <option value="{{ $pkg->id }}" {{ old('client_package_id') == $pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }}@if(($pkg->insights_per_month ?? $pkg->max_insights_per_month ?? $pkg->max_documents ?? null)) — {{ $pkg->insights_per_month ?? $pkg->max_insights_per_month ?? $pkg->max_documents }} insights / company / month @endif
                        </option>
                        @endforeach
                    </select>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Plan tiers are managed by the CFO Insights platform.</div>
                </div>
                <div class="mf-field">
                    <label class="mf-label" style="display:flex;align-items:center;justify-content:space-between">
                        <span>Password <span style="color:var(--red)">*</span></span>
                    </label>
                    <div style="position:relative;display:flex;align-items:center">
                        <input type="password" name="password" id="ac-password" class="mf-inp"
                            placeholder="Min. 8 characters" style="padding-right:88px" required>
                        {{-- Show/hide toggle --}}
                        <button type="button" title="Show/hide password"
                            onclick="toggleAcPassword()"
                            style="position:absolute;right:44px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--text-3);display:flex;align-items:center">
                            <svg id="ac-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        {{-- Auto-generate button --}}
                        <button type="button" title="Auto-generate password"
                            onclick="generateAcPassword()"
                            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--blue);display:flex;align-items:center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                        </button>
                    </div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px">Click <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11" style="vertical-align:middle"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg> to auto-generate a secure password.</div>
                </div>
                <div style="background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:12.5px;color:#3b82f6;line-height:1.5">
                    An email invite will be sent to the client automatically after submission.
                </div>
                <div class="mf-foot">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-add-client').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Client</button>
                </div>
            </form>
        </div>

        {{-- ── Bulk Upload Panel ──────────────────────────── --}}
        <div id="panel-bulk" style="display:none">
            <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:10px">Upload CSV File</div>

            {{-- Drop zone --}}
            <div id="csv-drop-zone"
                style="border:2px dashed #c7d9f0;border-radius:12px;background:rgba(59,130,246,.04);padding:36px 20px;text-align:center;cursor:pointer;transition:border-color .15s"
                onclick="document.getElementById('csv-file-input').click()"
                ondragover="event.preventDefault();this.style.borderColor='var(--blue)'"
                ondragleave="this.style.borderColor='#c7d9f0'"
                ondrop="handleCsvDrop(event)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.8" width="32" height="32" style="margin:0 auto 10px;display:block">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <div style="font-size:13.5px;color:var(--text-2)">
                    Drag &amp; drop your CSV here, or <span style="color:var(--blue);font-weight:500;text-decoration:underline">click to browse</span>
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:6px">.csv files only · Max 5 MB · Up to 200 clients per upload</div>
                <input type="file" id="csv-file-input" accept=".csv" style="display:none" onchange="handleCsvFile(this.files[0])">
            </div>
            <div id="csv-file-name" style="font-size:12px;color:var(--green);margin-top:8px;display:none"></div>

            {{-- CSV Format --}}
            <div style="margin-top:18px">
                <div style="font-size:13.5px;font-weight:600;color:var(--text-1);margin-bottom:10px">CSV Format</div>
                <div style="border:1px solid var(--border);border-radius:10px;padding:14px 16px">
                    <div style="font-size:12.5px;color:var(--text-2);margin-bottom:10px">Your CSV must include the following columns in order:</div>
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:10px 14px;font-family:monospace;font-size:13px;color:var(--text-1)">
                        client_name, email, plan_tier
                    </div>
                    <div style="font-size:12px;color:var(--text-3);margin-top:10px">
                        Example: <em>TechCorp Inc., info@techcorp.com, Professional</em>
                    </div>
                    <a href="javascript:void(0)" onclick="downloadSampleCsv()" style="display:inline-flex;align-items:center;gap:5px;font-size:12.5px;color:var(--blue);margin-top:10px;text-decoration:none;font-weight:500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download sample CSV
                    </a>
                </div>
            </div>

            <div style="background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:11px 14px;margin-top:16px;margin-bottom:16px;font-size:12.5px;color:#3b82f6;line-height:1.5">
                All clients in the CSV will receive an email invite automatically after upload.
            </div>

            <div id="bulk-error" style="display:none;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 13px;margin-bottom:14px;font-size:12.5px;color:var(--red)"></div>

            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-add-client').classList.remove('open')">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitBulkUpload()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload &amp; Invite
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Delete Confirmation Modal ────────────────────────── --}}
<div class="modal-overlay" id="modal-delete-client" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="max-width:400px">
        <button class="modal-close" onclick="document.getElementById('modal-delete-client').classList.remove('open')">&times;</button>
        <div class="modal-title">Delete Client</div>
        <div class="modal-sub" id="delete-msg">Are you sure?</div>
        <form method="POST" id="delete-form">
            @csrf @method('DELETE')
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-delete-client').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script>
@php
    $billingIndex = route('billing.index');
    $myCompaniesCreate = route('my-companies.create');
    $hasPlan = $hasActivePlan ?? false;

    $dtEmptyClientsHtml = $hasPlan
        ? '<div style="text-align:center;padding:48px 20px"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.4" style="margin:0 auto 12px;display:block"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg><div style="font-size:14px;font-weight:500;color:#6b7280">Your clients will be listed here</div><div style="font-size:12.5px;color:#9ca3af;margin-top:4px;max-width:560px;margin-inline:auto">This is where you view and manage all the clients connected to your portal. Add clients individually or import your full list via CSV. Once added, each client appears here with status, assigned plan, and key login details.</div><div style="margin-top:14px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap"><button type="button" onclick="document.getElementById(\'modal-add-client\').classList.add(\'open\'); switchAddClientTab(\'manual\');" class="btn btn-primary btn-sm">+ Add Your First Client</button><button type="button" onclick="document.getElementById(\'modal-add-client\').classList.add(\'open\'); switchAddClientTab(\'bulk\');" class="btn btn-ghost btn-sm">Bulk Import CSV</button></div><div style="font-size:11px;color:#9ca3af;margin-top:10px">Need a template? <a href=\'javascript:void(0)\' onclick=\'downloadSampleCsv()\' style=\'color:var(--blue);text-decoration:none\'>Download the CSV upload template</a> →</div></div>'
        : '<div style="text-align:center;padding:48px 20px"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.4" style="margin:0 auto 12px;display:block"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg><div style="font-size:14px;font-weight:500;color:#6b7280">An active plan is required</div><div style="font-size:12.5px;color:#9ca3af;margin-top:4px;max-width:520px;margin-inline:auto">Subscribe on Subscription &amp; Usage before adding clients or importing CSV.</div><div style="margin-top:14px"><a href="' . e($billingIndex) . '" class="btn btn-primary btn-sm">Go to Subscription &amp; Usage</a></div></div>';

    $dtEmptyCompaniesHtml = $hasPlan
        ? '<div style="text-align:center;padding:56px 20px"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.4" style="margin:0 auto 12px;display:block"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg><div style="font-size:14px;font-weight:500;color:#6b7280">Your client companies will be listed here</div><div style="font-size:12.5px;color:#9ca3af;margin-top:4px;max-width:560px;margin-inline:auto">This is where you view and manage all companies associated with your clients. Companies are added during client onboarding or created later and linked to a client. Each company appears here with its QuickBooks status and insight activity.</div><div style="margin-top:14px"><a href="' . e($myCompaniesCreate) . '" class="btn btn-primary btn-sm">+ Add a Company</a></div><div style="font-size:11px;color:#9ca3af;margin-top:10px">Each company needs a QuickBooks connection to start generating CFO-ready insight reports.</div></div>'
        : '<div style="text-align:center;padding:56px 20px"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.4" style="margin:0 auto 12px;display:block"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg><div style="font-size:14px;font-weight:500;color:#6b7280">An active plan is required</div><div style="font-size:12.5px;color:#9ca3af;margin-top:4px;max-width:520px;margin-inline:auto">Choose a subscription plan before adding companies.</div><div style="margin-top:14px"><a href="' . e($billingIndex) . '" class="btn btn-primary btn-sm">Go to Subscription &amp; Usage</a></div></div>';
@endphp
$(document).ready(function() {
    // Reset filters on load (bfcache fix)
    document.getElementById('client-search').value   = '';
    document.getElementById('client-status-filter').value = '';
    document.getElementById('company-search').value  = '';
    document.getElementById('company-status-filter').value = '';
    document.getElementById('company-qb-filter').value = '';

    var clientTable = $('#clients-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
        columnDefs: [{ orderable: false, targets: [3, 7] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ clients',
            infoEmpty: 'No clients added yet',
            paginate: { previous: '‹', next: '›' },
            emptyTable: {!! json_encode($dtEmptyClientsHtml) !!},
        },
        drawCallback: function() {
            var info = this.api().page.info();
            document.getElementById('client-count-badge').textContent = info.recordsDisplay + ' client' + (info.recordsDisplay !== 1 ? 's' : '');
        }
    });

    document.getElementById('client-search').addEventListener('keyup', function() {
        clientTable.search(this.value).draw();
    });
    document.getElementById('client-status-filter').addEventListener('change', function() {
        clientTable.column(2).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

    // Reset company filters
    document.getElementById('company-client-filter').value = '';
    document.getElementById('co-date-from').value = '';
    document.getElementById('co-date-to').value = '';

    var coTable = $('#companies-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
        columnDefs: [{ orderable: false, targets: [7] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ companies',
            infoEmpty: 'No companies added yet',
            paginate: { previous: '‹', next: '›' },
            emptyTable: {!! json_encode($dtEmptyCompaniesHtml) !!},
        }
    });

    document.getElementById('company-search').addEventListener('keyup', function() {
        coTable.search(this.value).draw();
    });
    document.getElementById('company-status-filter').addEventListener('change', function() {
        coTable.column(2).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
    document.getElementById('company-qb-filter').addEventListener('change', function() {
        coTable.column(3).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
    document.getElementById('company-client-filter').addEventListener('change', function() {
        coTable.column(1).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

    // Date range filter — filter by data-created on tr
    function applyDateFilter() {
        var from = document.getElementById('co-date-from').value;
        var to   = document.getElementById('co-date-to').value;
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'companies-table') return true;
            var row = coTable.row(dataIndex).node();
            var created = row ? row.getAttribute('data-created') : null;
            if (!created) return true;
            if (from && created < from) return false;
            if (to   && created > to)   return false;
            return true;
        });
        coTable.draw();
        $.fn.dataTable.ext.search.pop();
    }
    document.getElementById('co-date-from').addEventListener('change', applyDateFilter);
    document.getElementById('co-date-to').addEventListener('change', applyDateFilter);
});

// ── Add Client password helpers ──────────────────────────────
function toggleAcPassword() {
    var inp  = document.getElementById('ac-password');
    var icon = document.getElementById('ac-eye-icon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        inp.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
function generateAcPassword() {
    var chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    var pwd = '';
    for (var i = 0; i < 12; i++) pwd += chars[Math.floor(Math.random() * chars.length)];
    var inp = document.getElementById('ac-password');
    inp.type  = 'text';
    inp.value = pwd;
    document.getElementById('ac-eye-icon').innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
}

// ── Add Client Modal tabs ────────────────────────────────────
function switchAddClientTab(tab) {
    var isManual = tab === 'manual';
    document.getElementById('panel-manual').style.display = isManual ? '' : 'none';
    document.getElementById('panel-bulk').style.display   = isManual ? 'none' : '';
    var manualBtn = document.getElementById('tab-manual-btn');
    var bulkBtn   = document.getElementById('tab-bulk-btn');
    manualBtn.style.background = isManual ? 'var(--orange)' : 'transparent';
    manualBtn.style.color      = isManual ? '#fff' : 'var(--text-2)';
    bulkBtn.style.background   = isManual ? 'transparent' : 'var(--orange)';
    bulkBtn.style.color        = isManual ? 'var(--text-2)' : '#fff';
}

var _csvFile = null;
function handleCsvFile(file) {
    if (!file) return;
    _csvFile = file;
    var el = document.getElementById('csv-file-name');
    el.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    el.style.display = 'block';
    document.getElementById('csv-drop-zone').style.borderColor = 'var(--green)';
}
function handleCsvDrop(e) {
    e.preventDefault();
    document.getElementById('csv-drop-zone').style.borderColor = '#c7d9f0';
    var file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) handleCsvFile(file);
}
function downloadSampleCsv() {
    var csv = 'client_name,email,plan_tier\nTechCorp Inc.,info@techcorp.com,Professional\nAcme Corp,hello@acme.com,Starter\n';
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'sample_clients.csv';
    a.click();
}
function submitBulkUpload() {
    var errBox = document.getElementById('bulk-error');
    errBox.style.display = 'none';
    if (!_csvFile) {
        errBox.textContent   = 'Please select a CSV file first.';
        errBox.style.display = 'block';
        return;
    }
    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('csv_file', _csvFile);
    fetch('{{ route("clients.bulk-upload") }}', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(function(data) {
            if (data.status === 'success') {
                document.getElementById('modal-add-client').classList.remove('open');
                window.location.reload();
            } else {
                errBox.textContent   = data.message || 'Upload failed.';
                errBox.style.display = 'block';
            }
        })
        .catch(function() {
            errBox.textContent   = 'Network error. Please try again.';
            errBox.style.display = 'block';
        });
}

function toggleClientStatus(id, name, currentStatus) {
    var action = currentStatus === 'active' ? 'disable' : 'enable';
    var isDisable = currentStatus === 'active';
    Swal.fire({
        title: (isDisable ? 'Disable' : 'Enable') + ' client?',
        text: 'Are you sure you want to ' + action + ' client "' + name + '"?',
        icon: isDisable ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: isDisable ? 'Yes, disable' : 'Yes, enable',
        cancelButtonText: 'Cancel',
        confirmButtonColor: isDisable ? '#ef4444' : '#10b981',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(function (result) {
        if (!result.isConfirmed) return;
        fetch('{{ route("clients.toggle-status", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(function(data) {
            if (data.status === 'success') window.location.reload();
        });
    });
}

function openAssignClientPlanModal(companyId, clientName, currentPlanId) {
    document.getElementById('assign-client-plan-company-id').value = companyId;
    document.getElementById('assign-client-plan-company-name').textContent = 'Assigning plan to: ' + clientName;
    var sel = document.getElementById('assign-client-plan-select');
    sel.value = currentPlanId || '';
    document.getElementById('modal-assign-client-plan').classList.add('open');
}

function submitAssignClientPlan() {
    var companyId = document.getElementById('assign-client-plan-company-id').value;
    var planId    = document.getElementById('assign-client-plan-select').value;
    var formData  = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('client_package_id', planId);
    fetch('{{ route("my-companies.assign-plan", ":id") }}'.replace(':id', companyId), { method: 'POST', body: formData })
        .then(r => r.json())
        .then(function(data) {
            if (data.status === 'success') {
                document.getElementById('modal-assign-client-plan').classList.remove('open');
                window.location.reload();
            }
        });
}

function confirmDelete(id, name) {
    document.getElementById('delete-msg').textContent = 'Delete "' + name + '"? This cannot be undone.';
    document.getElementById('delete-form').action = '{{ route("clients.destroy", ":id") }}'.replace(':id', id);
    document.getElementById('modal-delete-client').classList.add('open');
}

function confirmCompanyDelete(formEl) {
    Swal.fire({
        title: 'Delete company?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    }).then(function(result) {
        if (result.isConfirmed) {
            formEl.submit();
        }
    });

    return false;
}

function openAssignPlanModal(companyId, companyName, currentPlanId) {
    document.getElementById('assign-plan-company-name').textContent = 'Assigning plan to: ' + companyName;
    document.getElementById('assign-plan-company-id').value = companyId;
    var sel = document.getElementById('assign-plan-select');
    if (currentPlanId) {
        sel.value = currentPlanId;
    } else {
        sel.value = '';
    }
    document.getElementById('modal-assign-plan').classList.add('open');
}

document.getElementById('assign-plan-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var companyId = document.getElementById('assign-plan-company-id').value;
    var planId    = document.getElementById('assign-plan-select').value;
    var csrf      = document.querySelector('meta[name="csrf-token"]').content;

    fetch('{{ route("my-companies.assign-plan", ":id") }}'.replace(':id', companyId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ client_package_id: planId || null })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        document.getElementById('modal-assign-plan').classList.remove('open');
        if (res.status === 'success' || res.message) {
            showToast(res.message || 'Plan assigned.', 'success');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            showToast(res.message || 'Failed to assign plan.', 'error');
        }
    })
    .catch(function() { showToast('Request failed.', 'error'); });
});

// Re-open add client modal on validation error
@if($errors->any())
document.getElementById('modal-add-client').classList.add('open');
@foreach($errors->all() as $error)
showToast('{{ addslashes($error) }}', 'error');
@endforeach
@endif
</script>
@endpush
