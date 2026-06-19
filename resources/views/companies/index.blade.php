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
        @if(auth()->user()->can('add_company') || auth()->user()->hasRole('client'))
        @if($canAddCompany)
        <a href="javascript:void(0)" class="btn btn-primary" onclick="openAddCoModal()">
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
                <option value="">Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
                <option value="pending payment">Pending Payment</option>
            </select>
            <div class="dr-wrap" id="coi-dr">
                <div class="dr-field" onclick="drOpen('coi-dr')">
                    <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span class="dr-text" id="coi-dr-text">Start Date – End Date</span>
                    <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('coi-dr')" title="Clear date filter">&#x2715;</button>
                </div>
                <div class="dr-menu" id="coi-dr-menu">
                    <ul class="dr-list">
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','today',this)">Today</li>
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','last30',this)">Last 30 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','thisMonth',this)">This Month</li>
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','lastMonth',this)">Last Month</li>
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','last90',this)">Last 90 Days</li>
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','last6m',this)">Last 6 Months</li>
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','last1y',this)">Last 1 Year</li>
                        <li class="dr-opt" onclick="drPickPreset('coi-dr','custom',this)">Custom Range</li>
                    </ul>
                    <div class="dr-custom-row" id="coi-dr-custom">
                        <input type="date" class="dr-date-inp" id="coi-dr-from">
                        <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                        <input type="date" class="dr-date-inp" id="coi-dr-to">
                        <button class="dr-apply-btn" onclick="drApplyCustom('coi-dr')">Apply</button>
                    </div>
                </div>
            </div>
            <button class="btn btn-danger" onclick="resetCoFilters()">Reset</button>
        </div>
        <span class="badge b-gray" id="co-count-badge">{{ $companies->count() }} compan{{ $companies->count() !== 1 ? 'ies' : 'y' }}</span>
    </div>

    <table id="co-table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>QB</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th style="width:48px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $co)
                @php
                    $sub       = $co->subscription;
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
                            <div style="min-width:0">
                                <div class="cell-trunc" style="font-weight:500;font-size:13px;color:var(--text-1)" title="{{ $co->company_name }}">{{ $co->company_name }}</div>
                                @if($co->subdomain)
                                <div class="cell-trunc-sm" style="font-size:11.5px;color:var(--text-3)">{{ $co->subdomain }}.{{ config('tenancy.root_domain', 'yourdomain.com') }}</div>
                                @endif
                                @if($co->company_email)
                                <div class="cell-trunc-sm" style="font-size:11px;color:var(--text-3)" title="{{ $co->company_email }}">{{ $co->company_email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($isQB)
                            <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        @endif
                    </td>
                    <td data-search="{{ $displayStatus }}">
                        <span class="badge {{ $stClass }}">
                            <span class="badge-dot" style="background:{{ $stDot }}"></span>
                            {{ $stLabel }}
                        </span>
                    </td>
                    <td style="font-size:12.5px;color:var(--text-2)" data-date="{{ $co->created_at?->format('Y-m-d') ?? '' }}">
                        {{ $co->created_at?->format(active_date_format()) ?? '—' }}
                    </td>
                    <td>
                        <div class="dm-wrap">
                            <button class="dot-menu" data-menu="dm-co-{{ $co->id }}" title="Actions">•••</button>
                            <div class="dm-popup" id="dm-co-{{ $co->id }}">
                                <a href="javascript:void(0)" class="dm-item"
                                   onclick="openClientEditCoModal(
                                       {{ $co->id }},
                                       '{{ addslashes($co->company_name) }}',
                                       '{{ addslashes($co->company_email ?? '') }}',
                                       '{{ addslashes($co->industry_type ?? '') }}',
                                       '{{ addslashes($co->entity_type ?? '') }}'
                                   )">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>

<style>
.co-err{font-size:11.5px;color:#ef4444;margin-top:5px;display:flex;align-items:center;gap:5px;line-height:1.3}
.co-err::before{content:'';height:12px;flex-shrink:0;background:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='2'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cline x1='12' y1='8' x2='12' y2='12'/%3E%3Cline x1='12' y1='16' x2='12.01' y2='16'/%3E%3C/svg%3E") center/contain no-repeat}
.mf-inp.co-invalid,.mf-sel.co-invalid{border-color:#ef4444!important;box-shadow:0 0 0 3px rgba(239,68,68,.08)}
</style>

{{-- ── Add Company Modal ── --}}
<div class="modal-overlay" id="modal-add-co" onclick="if(event.target===this)closeAddCoModal()">
    <div class="modal-box" style="width:560px;max-height:90vh;overflow-y:auto;padding-bottom:0">
        <button class="modal-close" onclick="closeAddCoModal()">&times;</button>
        <div class="modal-title">Add New Company</div>
        <div class="modal-sub">Enter the details for the new company you want to add.</div>
        <form method="POST" action="{{ route('my-companies.store') . (isset($__imp) ? '?_imp=' . $__imp['token'] : '') }}" id="modal-add-co-form" novalidate>
            @csrf

            <div style="font-size:14px;font-weight:600;color:var(--text-1);margin-bottom:14px;display:flex;align-items:center;gap:8px">
                <span style="width:22px;height:22px;border-radius:50%;background:var(--blue);color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">1</span>
                Company Information
            </div>

            <div class="mf-field">
                <label class="mf-label">Company Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="company_name" id="co-modal-name" class="mf-inp" placeholder="Acme Inc." maxlength="100" autocomplete="off">
                <div id="co-modal-err-name" class="co-err" style="display:none"></div>
            </div>

            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Industry Type</label>
                    <select name="industry_type" id="co-modal-industry" class="mf-sel" style="height:38px">
                        <option value="">Select Industry Type</option>
                        @foreach(['Technology','Healthcare','Finance','Manufacturing','Retail','Real Estate','Construction','Transportation','Energy','Agriculture','Education','Consulting','Legal Services','Entertainment','Automotive','Other'] as $ind)
                        <option value="{{ $ind }}">{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Entity Type</label>
                    <select name="entity_type" id="co-modal-entity" class="mf-sel" style="height:38px">
                        <option value="">Select Entity Type</option>
                        @foreach(['LLC','C-Corporation','S-Corporation','Sole Proprietorship','Partnership','LLP','Non-Profit','Trust','Other'] as $ent)
                        <option value="{{ $ent }}">{{ $ent }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mf-foot" style="position:sticky;bottom:0;background:var(--card-bg,#fff);padding:16px 0;border-top:1px solid var(--border);margin-top:20px">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAddCoModal()">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-add-co-modal" onclick="validateAndSubmitAddCoModal()">
                    Add Company
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Edit Company Modal ── --}}
<div class="modal-overlay" id="modal-client-edit-co" onclick="if(event.target===this)closeClientEditCoModal()">
    <div class="modal-box" style="width:560px;max-height:90vh;overflow-y:auto;padding-bottom:0">
        <button class="modal-close" onclick="closeClientEditCoModal()">&times;</button>
        <div class="modal-title">Edit Company</div>
        <div class="modal-sub">Update the details for this company.</div>
        <form method="POST" id="modal-client-edit-co-form" novalidate>
            @csrf

            <div class="mf-field">
                <label class="mf-label">Company Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="company_name" id="client-edit-co-name" class="mf-inp" placeholder="Acme Inc." maxlength="100">
                <div id="client-edit-err-co-name" class="co-err" style="display:none"></div>
            </div>

            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Industry Type</label>
                    <select name="industry_type" id="client-edit-co-industry" class="mf-sel" style="height:38px">
                        <option value="">Select Industry Type</option>
                        @foreach(['Technology','Healthcare','Finance','Manufacturing','Retail','Real Estate','Construction','Transportation','Energy','Agriculture','Education','Consulting','Legal Services','Entertainment','Automotive','Other'] as $ind)
                        <option value="{{ $ind }}">{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Entity Type</label>
                    <select name="entity_type" id="client-edit-co-entity" class="mf-sel" style="height:38px">
                        <option value="">Select Entity Type</option>
                        @foreach(['LLC','C-Corporation','S-Corporation','Sole Proprietorship','Partnership','LLP','Non-Profit','Trust','Other'] as $ent)
                        <option value="{{ $ent }}">{{ $ent }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mf-foot" style="position:sticky;bottom:0;background:var(--card-bg,#fff);padding:16px 0;border-top:1px solid var(--border);margin-top:20px">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeClientEditCoModal()">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-edit-co-modal" onclick="validateAndSubmitClientEditCoModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
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
        order: [[3, 'desc']],
        columnDefs: [{ orderable: false, targets: [4] }],
        infoCallback: function(settings, start, end, max, total, pre) {
            return 'Showing ' + (total === 0 ? 0 : start) + '–' + end + ' of ' + total + ' ' + (total !== 1 ? 'companies' : 'company');
        },
        language: {
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
        table.column(3).search(this.value ? '^' + this.value + '$' : '', true, false, true).draw();
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'co-table') return true;
        var from = document.getElementById('coi-dr-from') ? document.getElementById('coi-dr-from').value : '';
        var to   = document.getElementById('coi-dr-to')   ? document.getElementById('coi-dr-to').value   : '';
        if (!from && !to) return true;
        var row = table.row(dataIndex).node();
        if (!row) return true;
        var dateVal = row.querySelector('td[data-date]')?.getAttribute('data-date') || '';
        if (!dateVal) return true;
        if (from && dateVal < from) return false;
        if (to   && dateVal > to)   return false;
        return true;
    });
    _drCbs['coi-dr'] = function() { table.draw(); };
});

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

function resetCoFilters() {
    document.getElementById('co-search').value        = '';
    document.getElementById('co-status-filter').value = '';
    drClear('coi-dr');
    if (window.$ && $('#co-table').length) {
        $('#co-table').DataTable().search('').columns().search('').draw();
    }
}

/* ── Shared modal field helpers ── */
function _cliCoErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = msg ? 'flex' : 'none';
}
function _cliCoMark(id, hasErr) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('co-invalid', hasErr);
}

/* ── Add Company Modal ── */
function openAddCoModal() {
    document.getElementById('modal-add-co').classList.add('open');
    setTimeout(function() {
        var f = document.getElementById('co-modal-name');
        if (f) f.focus();
    }, 80);
}

function closeAddCoModal() {
    var btn = document.getElementById('btn-add-co-modal');
    if (btn) { btn.disabled = false; btn.textContent = 'Add Company'; }
    document.getElementById('modal-add-co').classList.remove('open');
    document.getElementById('modal-add-co-form').reset();
    ['co-modal-err-name','co-modal-err-email'].forEach(function(id) { _cliCoErr(id, ''); });
    ['co-modal-name','co-modal-email'].forEach(function(id) { _cliCoMark(id, false); });
}

function validateAndSubmitAddCoModal() {
    var ok = true;
    var emailRx = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,24}$/;

    var coNameRx = /^[a-zA-Z0-9\s.,&'\-]+$/;
    var nameVal = (document.getElementById('co-modal-name')?.value || '').trim();
    if (!nameVal) {
        _cliCoErr('co-modal-err-name', 'Company Name is required.');
        _cliCoMark('co-modal-name', true);
        ok = false;
    } else if (!coNameRx.test(nameVal)) {
        _cliCoErr('co-modal-err-name', "Only letters, numbers, spaces and . , & - ' are allowed.");
        _cliCoMark('co-modal-name', true);
        ok = false;
    } else {
        _cliCoErr('co-modal-err-name', '');
        _cliCoMark('co-modal-name', false);
    }

    if (ok) {
        var btn = document.getElementById('btn-add-co-modal');
        if (btn) { btn.disabled = true; btn.textContent = 'Adding…'; }
        document.getElementById('modal-add-co-form').submit();
    }
}

// Real-time validation clearing — Add & Edit Company modals (client portal)
(function() {
    var emailRx = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,24}$/;

    function clearOnInput(fieldId, errId) {
        var el = document.getElementById(fieldId);
        if (!el) return;
        el.addEventListener('input', function() {
            if (this.value.trim()) { _cliCoErr(errId, ''); _cliCoMark(fieldId, false); }
        });
    }
    function clearOnEmailInput(fieldId, errId) {
        var el = document.getElementById(fieldId);
        if (!el) return;
        el.addEventListener('input', function() {
            var v = this.value.trim();
            if (!v || emailRx.test(v)) { _cliCoErr(errId, ''); _cliCoMark(fieldId, false); }
        });
    }

    // Add Company modal
    clearOnInput('co-modal-name', 'co-modal-err-name');

    // Edit Company modal
    clearOnInput('client-edit-co-name', 'client-edit-err-co-name');

    // Real-time company name character filtering
    ['co-modal-name', 'client-edit-co-name'].forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function() {
            var pos = this.selectionStart;
            var cleaned = this.value.replace(/[^a-zA-Z0-9\s.,&'\-]/g, '');
            if (cleaned !== this.value) {
                this.value = cleaned;
                this.setSelectionRange(Math.max(0, pos - 1), Math.max(0, pos - 1));
            }
        });
    });
})();

/* ── Edit Company Modal ── */
function openClientEditCoModal(id, name, email, industry, entity) {
    var saveBtn = document.getElementById('btn-edit-co-modal');
    if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg> Save Changes'; }
    var updateUrl = '{{ route("my-companies.update", ":id") . (isset($__imp) ? "?_imp=" . $__imp["token"] : "") }}'.replace(':id', id);
    document.getElementById('modal-client-edit-co-form').action = updateUrl;
    document.getElementById('client-edit-co-name').value  = name;

    var indSel = document.getElementById('client-edit-co-industry');
    for (var i = 0; i < indSel.options.length; i++) {
        indSel.options[i].selected = indSel.options[i].value === industry;
    }
    var entSel = document.getElementById('client-edit-co-entity');
    for (var i = 0; i < entSel.options.length; i++) {
        entSel.options[i].selected = entSel.options[i].value === entity;
    }

    ['client-edit-err-co-name'].forEach(function(id) { _cliCoErr(id, ''); });
    ['client-edit-co-name'].forEach(function(id) { _cliCoMark(id, false); });

    document.getElementById('modal-client-edit-co').classList.add('open');
    setTimeout(function() { document.getElementById('client-edit-co-name').focus(); }, 80);
}

function closeClientEditCoModal() {
    document.getElementById('modal-client-edit-co').classList.remove('open');
}

function validateAndSubmitClientEditCoModal() {
    var ok = true;

    var editCoNameRx = /^[a-zA-Z0-9\s.,&'\-]+$/;
    var nameVal = (document.getElementById('client-edit-co-name')?.value || '').trim();
    if (!nameVal) {
        _cliCoErr('client-edit-err-co-name', 'Company Name is required.');
        _cliCoMark('client-edit-co-name', true);
        ok = false;
    } else if (!editCoNameRx.test(nameVal)) {
        _cliCoErr('client-edit-err-co-name', "Only letters, numbers, spaces and . , & - ' are allowed.");
        _cliCoMark('client-edit-co-name', true);
        ok = false;
    } else {
        _cliCoErr('client-edit-err-co-name', '');
        _cliCoMark('client-edit-co-name', false);
    }

    if (ok) {
        var btn = document.getElementById('btn-edit-co-modal');
        if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
        document.getElementById('modal-client-edit-co-form').submit();
    }
}
</script>
@endpush
