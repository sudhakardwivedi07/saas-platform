@extends('layouts.app')
@section('title', 'My Companies')
@section('page-title', 'My Companies')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>My Companies</h1>
        <p>Create and manage multiple companies from one account</p>
    </div>
    <div class="ph-actions">
        @if(auth()->user()->can('add_company') || auth()->user()->hasRole('user'))
        @if($canAddCompany)
        <a href="{{ route('my-companies.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New Company
        </a>
        @else
        <button class="btn btn-primary" disabled title="{{ $hasActivePlan ? 'Company limit reached (' . $maxCompanies . ' max)' : 'No plan assigned to this account' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New Company
        </button>
        @endif
        @endif
    </div>
</div>

@if(!$canAddCompany)
<div class="info-banner" style="margin-bottom: 1rem;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span class="info-banner-txt">
        {{ $hasActivePlan
            ? 'You have reached your company limit (' . $currentCompaniesCount . '/' . $maxCompanies . '). Upgrade your plan to add more companies.'
            : 'No plan is assigned to this account. Please select a plan before adding companies.' }}
    </span>
</div>
@endif


<div class="tc">
    <div class="tc-head">
        <div class="tc-filters">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="srch-inp" id="co-search" placeholder="Search companies…" autocomplete="off">
            </div>
            <select class="sel" id="co-status-filter" autocomplete="off">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
                <option value="pending payment">Pending Payment</option>
            </select>
        </div>
        <span class="badge b-gray" id="co-count-badge">{{ $companies->count() }} companies</span>
    </div>

    <table id="co-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Plan</th>
                    <th>Subscription</th>
                    <th>QB</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th style="width:80px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $co)
                @php
                    $sub       = $co->subscription;
                    $planName  = $co->clientPackage?->name ?? $co->package?->name ?? '—';
                    $rawStatus = $co->status ?? 'inactive';
                    $displayStatus = match($rawStatus) {
                        'active'    => 'active',
                        'suspended' => 'suspended',
                        'inactive'  => 'pending payment',
                        default     => strtolower($rawStatus),
                    };
                    $stClass = match($rawStatus) {
                        'active'    => 'b-green',
                        'suspended' => 'b-red',
                        default     => 'b-amber',
                    };
                    $stDot = match($rawStatus) {
                        'active'    => 'var(--green)',
                        'suspended' => 'var(--red)',
                        default     => 'var(--amber)',
                    };
                    $stLabel = match($rawStatus) {
                        'active'    => 'Active',
                        'suspended' => 'Suspended',
                        default     => 'Pending Payment',
                    };
                    $initials = strtoupper(substr($co->company_name, 0, 1))
                              . strtoupper(substr(strstr($co->company_name, ' ') ?: '  ', 1, 1));
                    $colors = ['#1a6aa8','#f06b21','#10b981','#7c3aed','#e11d48','#0d9488'];
                    $color  = $colors[$co->id % count($colors)];
                    $isQB   = $co->quickbooksConnections?->where('is_active', true)->isNotEmpty() ?? false;
                    $subStatus = $sub ? ucfirst($sub->status) : 'None';
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:9px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0">{{ $initials }}</div>
                            <div>
                                <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $co->company_name }}</div>
                                @if($co->subdomain)
                                <div style="font-size:11.5px;color:var(--text-3)">{{ $co->subdomain }}.{{ config('tenancy.root_domain', 'yourdomain.com') }}</div>
                                @endif
                                @if($co->company_email)
                                <div style="font-size:11px;color:var(--text-3)">{{ $co->company_email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($planName !== '—')
                            <span class="badge b-blue" style="font-size:11.5px">{{ $planName }}</span>
                        @else
                            <span style="color:var(--text-3);font-size:13px">—</span>
                        @endif
                    </td>
                    <td>
                        @if($sub)
                            @php
                                $subBadge = match($sub->status) {
                                    'active'   => 'b-green',
                                    'trialing' => 'b-amber',
                                    'past_due' => 'b-red',
                                    default    => 'b-gray',
                                };
                            @endphp
                            <span class="badge {{ $subBadge }}" style="font-size:11.5px">{{ $subStatus }}</span>
                            @if($co->trial_ends_at && $co->trial_ends_at->isFuture())
                            <div style="font-size:11px;color:var(--amber);margin-top:2px">Ends {{ $co->trial_ends_at->format('M j, Y') }}</div>
                            @endif
                        @else
                            <span style="color:var(--text-3);font-size:13px">None</span>
                        @endif
                    </td>
                    <td>
                        @if($isQB)
                            <span class="badge b-green" style="font-size:11.5px">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="10" height="10"><polyline points="20 6 9 17 4 12"/></svg>
                                Connected
                            </span>
                        @else
                            <span style="color:var(--text-3);font-size:13px">—</span>
                        @endif
                    </td>
                    <td data-search="{{ $displayStatus }}">
                        <span class="badge {{ $stClass }}">
                            <span class="badge-dot" style="background:{{ $stDot }}"></span>
                            {{ $stLabel }}
                        </span>
                    </td>
                    <td style="font-size:12.5px;color:var(--text-2)">
                        {{ $co->created_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td>
                        <div class="dm-wrap">
                            <button class="dot-menu" data-menu="dm-co-{{ $co->id }}" title="Actions">•••</button>
                            <div class="dm-popup" id="dm-co-{{ $co->id }}">
                                @if($rawStatus === 'active' && $co->login_url)
                                <a href="{{ $co->login_url }}" target="_blank" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    Open Portal
                                </a>
                                @endif
                                <a href="{{ route('quickbooks.index') }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                                    QuickBooks
                                </a>
                                @if($rawStatus === 'inactive')
                                <div class="dm-divider"></div>
                                <a href="{{ route('billing.index') }}" class="dm-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                    Complete Payment
                                </a>
                                @endif
                            </div>
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
    document.getElementById('co-search').value        = '';
    document.getElementById('co-status-filter').value = '';

    var table = $('#co-table').DataTable({
        paging: true, pageLength: 15, lengthChange: false,
        searching: true, ordering: true, info: true, dom: '<"dt-tscroll"t><"dt-footer"ip>',
        order: [[5, 'desc']],
        columnDefs: [{ orderable: false, targets: [6] }],
        language: {
            info: 'Showing _START_–_END_ of _TOTAL_ companies',
            infoEmpty: 'No companies found',
            paginate: { previous: '‹', next: '›' },
            emptyTable: '<div style="text-align:center;padding:48px 20px"><svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.4" style="margin:0 auto 14px;display:block"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg><div style="font-size:14px;font-weight:500;color:#6b7280">No companies yet</div><div style="font-size:12.5px;color:#9ca3af;margin-top:4px">Create your first company to get started.</div></div>',
        },
        drawCallback: function() {
            var info = this.api().page.info();
            document.getElementById('co-count-badge').textContent = info.recordsDisplay + ' compan' + (info.recordsDisplay !== 1 ? 'ies' : 'y');
        }
    });

    document.getElementById('co-search').addEventListener('keyup', function() { table.search(this.value).draw(); });
    document.getElementById('co-status-filter').addEventListener('change', function() {
        table.column(4).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });
});
</script>
@endpush
