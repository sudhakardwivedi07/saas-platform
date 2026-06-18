@extends('layouts.app')

@section('page-title', 'Client Detail')

@push('styles')
<style>
.ac-err {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--red, #ef4444);
    margin-top: 4px;
}
.mf-inp.ac-invalid { border-color: var(--red, #ef4444) !important; }
.co-err {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--red, #ef4444);
    margin-top: 4px;
}
.co-invalid { border-color: var(--red, #ef4444) !important; }
</style>
@endpush

@section('content')

@php
$allClientCompanies = collect($client->company ? [$client->company] : [])
    ->merge($client->managedCompanies ?? collect())
    ->unique('id');
$totalCompanies = $allClientCompanies->count();
@endphp

{{-- Back Button --}}
<a href="{{ route('clients.index') }}" class="back-btn">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to Clients
</a>

{{-- Client Header Card --}}
<div class="cd-header-card" style="margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

        {{-- Avatar --}}
        <div class="cd-avatar-ring">
            @php
                $initials = collect(explode(' ', $client->name))
                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                    ->take(2)
                    ->implode('');
            @endphp
            <span style="font-size:1.25rem;font-weight:700;color:#fff;letter-spacing:0.03em;">{{ $initials }}</span>
        </div>

        {{-- Name & Email --}}
        <div style="flex:1;min-width:0;">
            <div class="cd-name">{{ $client->name }}</div>
            <div class="cd-email">{{ $client->email }}</div>
        </div>

        {{-- Quick Actions --}}
        <div style="display:flex;gap:8px;">
            <button class="btn btn-secondary btn-sm"
                    onclick="openEditClientModal({{ $client->id }}, '{{ addslashes($client->name) }}', '{{ $client->email }}', '{{ addslashes($client->mobile ?? '') }}')">
                Edit Client
            </button>
        </div>
    </div>

    {{-- Stat Chips --}}
    <div class="cd-stat-chips" style="margin-top:1.25rem;">

        <div class="cd-chip">
            <span class="cd-chip-val">{{ $totalCompanies }}</span>
            <span class="cd-chip-lbl">{{ $totalCompanies === 1 ? 'Company' : 'Companies' }}</span>
        </div>

        <div class="cd-chip">
            @php
                $clientStatus = $client->status ?? 'active';
                $statusColor  = $clientStatus === 'active' ? '#16a34a' : '#dc2626';
            @endphp
            <span class="cd-chip-val">
                <span class="badge {{ $clientStatus === 'active' ? 'b-green' : 'b-red' }}">
                    {{ ucfirst($clientStatus) }}
                </span>
            </span>
            <span class="cd-chip-lbl">Status</span>
        </div>

    </div>
</div>

{{-- Tabs --}}
<div class="tab-bar" id="clientTabBar">
    <button class="tab-btn active" onclick="switchTab('overview', this)">Overview</button>
    <button class="tab-btn" onclick="switchTab('companies', this)">
        Companies
        <span style="margin-left:5px;background:var(--blue);color:#fff;font-size:0.65rem;font-weight:700;padding:1px 6px;border-radius:99px;">
            {{ $totalCompanies }}
        </span>
    </button>
    <button class="tab-btn" onclick="switchTab('activity', this)">Activity</button>
</div>

{{-- ==================== TAB: Overview ==================== --}}
<div class="tab-panel" id="tab-overview" style="display:block;">
    <div class="cd-section-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.25rem;">

        {{-- Notes --}}
        <div class="card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <div class="card-title">Notes</div>
                <button class="btn btn-ghost btn-xs" id="saveNotesBtn" onclick="saveNotes()" style="display:none;">Save</button>
            </div>
            <textarea id="clientNotes"
                      style="width:100%;min-height:160px;border:1px solid var(--border);border-radius:8px;padding:10px 12px;font-size:0.875rem;color:var(--text-1);resize:vertical;line-height:1.6;outline:none;transition:border-color .15s;"
                      placeholder="Add notes about this client..."
                      oninput="document.getElementById('saveNotesBtn').style.display='inline-flex'">{{ $client->notes ?? '' }}</textarea>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-title" style="margin-bottom:1rem;">Timeline</div>
            <div style="position:relative;padding-left:20px;">

                {{-- Timeline line --}}
                <div style="position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:var(--border);border-radius:2px;"></div>

                @php
                    $timelineItems = [
                        ['label' => 'Client account created',       'date' => $client->created_at, 'color' => 'var(--blue)'],
                        ['label' => 'First login recorded',         'date' => $client->last_login_at ?? null, 'color' => '#16a34a'],
                        ['label' => 'Profile last updated',         'date' => $client->updated_at,  'color' => 'var(--orange)'],
                    ];
                @endphp

                @foreach($timelineItems as $item)
                <div style="position:relative;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:10px;">
                    <div style="width:14px;height:14px;border-radius:50%;background:{{ $item['color'] }};flex-shrink:0;margin-top:2px;box-shadow:0 0 0 3px #fff,0 0 0 4px {{ $item['color'] }}44;"></div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:500;color:var(--text-1);">{{ $item['label'] }}</div>
                        <div style="font-size:0.75rem;color:var(--text-3);margin-top:1px;">
                            {{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format(active_date_format() . ' \a\t g:i A') : 'Not yet' }}
                        </div>
                    </div>
                </div>
                @endforeach

                @if($totalCompanies > 0)
                <div style="position:relative;display:flex;align-items:flex-start;gap:10px;">
                    <div style="width:14px;height:14px;border-radius:50%;background:#7c3aed;flex-shrink:0;margin-top:2px;box-shadow:0 0 0 3px #fff,0 0 0 4px #7c3aed44;"></div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:500;color:var(--text-1);">
                            {{ $totalCompanies }} {{ $totalCompanies === 1 ? 'company' : 'companies' }} connected
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-3);margin-top:1px;">Active</div>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>

{{-- ==================== TAB: Companies ==================== --}}
<div class="tab-panel" id="tab-companies" style="display:none;margin-top:1.25rem;">
    <div class="card">
        <div style="margin-bottom:1.25rem;">
            <div class="card-title">Companies</div>
            <div class="card-sub">All companies associated with {{ $client->name }}.</div>
        </div>

        @if($totalCompanies === 0)
            <div style="text-align:center;padding:2.5rem 1rem;">
                <div style="width:48px;height:48px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem auto;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p style="font-size:0.9rem;font-weight:600;color:var(--text-1);margin:0 0 4px 0;">No companies linked</p>
                <p style="font-size:0.8125rem;color:var(--text-2);margin:0;">This client has no companies connected yet.</p>
            </div>
        @else
            <div class="tc">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>QB Connected</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allClientCompanies as $company)
                        @php $isQB = $company->quickbooksConnections?->where('is_active', true)->isNotEmpty(); @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600;color:var(--text-1);font-size:0.875rem;text-transform:capitalize">{{ $company->company_name }}</div>
                                @if($company->company_email)
                                    <div style="font-size:0.75rem;color:var(--text-2);">{{ $company->company_email }}</div>
                                @endif
                            </td>
                            <td>
                                @if($isQB)
                                    <span class="badge b-green">Connected</span>
                                @else
                                    <span class="badge b-gray">Not connected</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ ($company->status ?? 'active') === 'active' ? 'b-green' : 'b-gray' }}">
                                    {{ ucfirst($company->status ?? 'active') }}
                                </span>
                            </td>
                            <td style="font-size:0.8125rem;color:var(--text-2);">
                                {{ $company->created_at?->format(active_date_format()) ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ==================== TAB: Activity ==================== --}}
<div class="tab-panel" id="tab-activity" style="display:none;margin-top:1.25rem;">
    <div class="card">
        <div class="card-title" style="margin-bottom:0.25rem;">Activity</div>
        <div class="card-sub" style="margin-bottom:1.25rem;">Recent actions taken by or on behalf of this client and their companies.</div>

        @if($activityLogs->isEmpty())
        <div style="text-align:center;padding:2.5rem 1rem;">
            <div style="width:48px;height:48px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem auto;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p style="font-size:0.9rem;font-weight:600;color:var(--text-1);margin:0 0 4px 0;">No activity recorded</p>
            <p style="font-size:0.8125rem;color:var(--text-2);margin:0;">Activity for this client will appear here once events are logged.</p>
        </div>
        @else
        <div>
            @foreach($activityLogs as $log)
            @php
                $evtType = strtolower($log->event_type ?? '');
                $iconColor = match(true) {
                    str_contains($evtType, 'login')        => '#3b82f6',
                    str_contains($evtType, 'quickbooks')   => '#10b981',
                    str_contains($evtType, 'deleted')      => '#ef4444',
                    str_contains($evtType, 'created')      => '#f06b21',
                    default                                 => '#6b7280',
                };
            @endphp
            <div style="display:flex;align-items:flex-start;gap:12px;padding:11px 0;border-bottom:1px solid var(--border)">
                <div style="width:32px;height:32px;border-radius:8px;background:{{ $iconColor }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="{{ $iconColor }}" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;color:var(--text-1)">{{ $log->display_event }}</div>
                    @if($log->display_description !== $log->display_event)
                    <div style="font-size:12.5px;color:var(--text-2);margin-top:1px">{{ $log->display_description }}</div>
                    @endif
                </div>
                <div style="font-size:11.5px;color:var(--text-3);white-space:nowrap;flex-shrink:0">
                    {{ $log->created_at->format(active_date_format() . ' g:i A') }}
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Edit Company Modal (admin client detail view) --}}
<div class="modal-overlay" id="modal-show-edit-co" onclick="if(event.target===this)closeShowEditCoModal()">
    <div class="modal-box" style="width:560px;max-height:90vh;overflow-y:auto;padding-bottom:0">
        <button class="modal-close" onclick="closeShowEditCoModal()">&times;</button>
        <div class="modal-title">Edit Company</div>
        <div class="modal-sub">Update the details for this company.</div>
        <form method="POST" id="modal-show-edit-co-form" novalidate>
            @csrf
            <input type="hidden" name="_from_client_id" value="{{ $client->id }}">

            <div class="mf-field">
                <label class="mf-label">Company Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="company_name" id="show-edit-co-name" class="mf-inp" placeholder="Acme Inc." maxlength="100">
                <div id="show-edit-err-co-name" class="co-err" style="display:none"></div>
            </div>

            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Industry Type</label>
                    <select name="industry_type" id="show-edit-co-industry" class="mf-sel" style="height:38px">
                        <option value="">Select Industry Type</option>
                        @foreach(['Technology','Healthcare','Finance','Manufacturing','Retail','Real Estate','Construction','Transportation','Energy','Agriculture','Education','Consulting','Legal Services','Entertainment','Automotive','Other'] as $ind)
                        <option value="{{ $ind }}">{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Entity Type</label>
                    <select name="entity_type" id="show-edit-co-entity" class="mf-sel" style="height:38px">
                        <option value="">Select Entity Type</option>
                        @foreach(['LLC','C-Corporation','S-Corporation','Sole Proprietorship','Partnership','LLP','Non-Profit','Trust','Other'] as $ent)
                        <option value="{{ $ent }}">{{ $ent }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mf-foot" style="position:sticky;bottom:0;background:var(--card-bg,#fff);padding:16px 0;border-top:1px solid var(--border);margin-top:20px">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeShowEditCoModal()">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="validateAndSubmitShowEditCo()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Client Modal --}}
<div class="modal-overlay" id="modal-edit-client" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:480px">
        <button class="modal-close" onclick="document.getElementById('modal-edit-client').classList.remove('open')">&times;</button>
        <div class="modal-title">Edit Client</div>
        <div class="modal-sub">Update the details for this client.</div>
        <form id="edit-client-form" method="POST">
            @csrf
            <input type="hidden" id="edit-client-id">
            <div class="mf-field" style="margin-top:4px">
                <label class="mf-label">Full Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" id="edit-client-name" class="mf-inp" required maxlength="100">
                <div id="err-edit-name" class="ac-err" style="display:none"></div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Email</label>
                <input type="email" id="edit-client-email" class="mf-inp" disabled style="opacity:.6;cursor:not-allowed">
                <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Email address cannot be changed.</div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Phone Number</label>
                <input type="tel" name="phone" id="edit-client-phone" class="mf-inp" placeholder="10-digit number" maxlength="10" inputmode="numeric">
                <div id="err-edit-phone" class="ac-err" style="display:none"></div>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-edit-client').classList.remove('open')">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="validateAndSubmitEditClient()">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditClientModal(clientId, name, email, phone) {
    document.getElementById('edit-client-id').value = clientId;
    document.getElementById('edit-client-name').value = name;
    document.getElementById('edit-client-email').value = email;
    document.getElementById('edit-client-phone').value = (phone || '').replace(/\D/g, '').slice(0, 10);
    document.getElementById('edit-client-form').action = '{{ route("clients.update", ":id") }}'.replace(':id', clientId);
    // Clear prior errors
    var nameEl  = document.getElementById('edit-client-name');
    var nameErr = document.getElementById('err-edit-name');
    var phoneEl = document.getElementById('edit-client-phone');
    var errEl   = document.getElementById('err-edit-phone');
    if (nameEl)  nameEl.classList.remove('ac-invalid');
    if (nameErr) { nameErr.textContent = ''; nameErr.style.display = 'none'; }
    if (phoneEl) phoneEl.classList.remove('ac-invalid');
    if (errEl)   { errEl.textContent = ''; errEl.style.display = 'none'; }
    document.getElementById('modal-edit-client').classList.add('open');
}

function validateAndSubmitEditClient() {
    var ok = true;

    // Full Name
    var nameEl  = document.getElementById('edit-client-name');
    var nameErr = document.getElementById('err-edit-name');
    var nameVal = nameEl ? nameEl.value.trim() : '';
    if (nameEl)  nameEl.classList.remove('ac-invalid');
    if (nameErr) { nameErr.textContent = ''; nameErr.style.display = 'none'; }
    if (!nameVal) {
        if (nameEl)  nameEl.classList.add('ac-invalid');
        if (nameErr) { nameErr.textContent = 'Full Name is required.'; nameErr.style.display = 'flex'; }
        ok = false;
    } else if (nameVal.length < 2 || nameVal.length > 100 || !/^[a-zA-Z\s'\-]+$/.test(nameVal)) {
        if (nameEl)  nameEl.classList.add('ac-invalid');
        if (nameErr) { nameErr.textContent = 'Only letters, spaces, hyphens and apostrophes are allowed.'; nameErr.style.display = 'flex'; }
        ok = false;
    }

    // Phone Number
    var phoneEl  = document.getElementById('edit-client-phone');
    var errEl    = document.getElementById('err-edit-phone');
    var rawPhone = phoneEl ? phoneEl.value.replace(/\D/g, '') : '';
    if (phoneEl) phoneEl.classList.remove('ac-invalid');
    if (errEl)   { errEl.textContent = ''; errEl.style.display = 'none'; }
    if (rawPhone && rawPhone.length !== 10) {
        if (phoneEl) phoneEl.classList.add('ac-invalid');
        if (errEl)   { errEl.textContent = 'Phone number must be exactly 10 digits.'; errEl.style.display = 'flex'; }
        ok = false;
    } else if (rawPhone && /^(\d)\1{9}$/.test(rawPhone)) {
        if (phoneEl) phoneEl.classList.add('ac-invalid');
        if (errEl)   { errEl.textContent = 'Please enter a valid phone number.'; errEl.style.display = 'flex'; }
        ok = false;
    }

    if (ok) document.getElementById('edit-client-form').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    var tabParam = new URLSearchParams(window.location.search).get('tab');
    if (tabParam && document.getElementById('tab-' + tabParam)) {
        document.querySelectorAll('#clientTabBar .tab-btn').forEach(function(b) {
            var match = b.getAttribute('onclick') || '';
            if (match.indexOf("'" + tabParam + "'") !== -1) b.click();
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Full Name: strip disallowed chars in real-time
    var nameInp = document.getElementById('edit-client-name');
    if (nameInp) {
        nameInp.addEventListener('input', function() {
            var pos = this.selectionStart;
            var cleaned = this.value.replace(/[^a-zA-Z\s'\-]/g, '');
            if (cleaned !== this.value) {
                this.value = cleaned;
                this.setSelectionRange(Math.max(0, pos - 1), Math.max(0, pos - 1));
            }
            this.classList.remove('ac-invalid');
            var err = document.getElementById('err-edit-name');
            if (err) { err.textContent = ''; err.style.display = 'none'; }
        });
    }
    // Phone: digits only
    var ph = document.getElementById('edit-client-phone');
    if (ph) {
        ph.addEventListener('input', function() {
            var cleaned = this.value.replace(/\D/g, '');
            if (cleaned !== this.value) this.value = cleaned;
            this.classList.remove('ac-invalid');
            var err = document.getElementById('err-edit-phone');
            if (err) { err.textContent = ''; err.style.display = 'none'; }
        });
    }
});

function switchTab(tabName, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.style.display = 'none'; });
    document.querySelectorAll('#clientTabBar .tab-btn').forEach(function(b) { b.classList.remove('active'); });
    var panel = document.getElementById('tab-' + tabName);
    if (panel) panel.style.display = 'block';
    btn.classList.add('active');
}
function saveNotes() {
    var notes = document.getElementById('clientNotes').value;
    fetch('{{ url("clients/" . $client->id . "/notes") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: notes })
    }).then(function(r) {
        if (r.ok) {
            document.getElementById('saveNotesBtn').style.display = 'none';
        }
    });
}

function _showCoErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = msg ? 'flex' : 'none';
}
function _showCoMark(id, hasErr) {
    var el = document.getElementById(id);
    if (el) el.classList.toggle('co-invalid', hasErr);
}

function openShowEditCoModal(id, name, email, industry, entity) {
    var updateUrl = '{{ route("my-companies.update", ":id") }}'.replace(':id', id);
    document.getElementById('modal-show-edit-co-form').action = updateUrl;
    document.getElementById('show-edit-co-name').value  = name;

    var indSel = document.getElementById('show-edit-co-industry');
    for (var i = 0; i < indSel.options.length; i++) {
        indSel.options[i].selected = indSel.options[i].value === industry;
    }
    var entSel = document.getElementById('show-edit-co-entity');
    for (var i = 0; i < entSel.options.length; i++) {
        entSel.options[i].selected = entSel.options[i].value === entity;
    }

    ['show-edit-err-co-name'].forEach(function(eid) { _showCoErr(eid, ''); });
    ['show-edit-co-name'].forEach(function(fid) { _showCoMark(fid, false); });

    document.getElementById('modal-show-edit-co').classList.add('open');
    setTimeout(function() { document.getElementById('show-edit-co-name').focus(); }, 80);
}

function closeShowEditCoModal() {
    document.getElementById('modal-show-edit-co').classList.remove('open');
}

function validateAndSubmitShowEditCo() {
    var emailRx = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,24}$/;
    var ok = true;

    var nameVal = (document.getElementById('show-edit-co-name').value || '').trim();
    if (!nameVal) {
        _showCoErr('show-edit-err-co-name', 'Company Name is required.');
        _showCoMark('show-edit-co-name', true);
        ok = false;
    } else {
        _showCoErr('show-edit-err-co-name', '');
        _showCoMark('show-edit-co-name', false);
    }

    if (ok) { document.getElementById('modal-show-edit-co-form').submit(); }
}

// Real-time validation clearing
(function() {
    var emailRx = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,24}$/;
    document.addEventListener('DOMContentLoaded', function() {
        var nameEl = document.getElementById('show-edit-co-name');
        if (nameEl) {
            nameEl.addEventListener('input', function() {
                if (this.value.trim()) { _showCoErr('show-edit-err-co-name', ''); _showCoMark('show-edit-co-name', false); }
            });
        }
    });
})();

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
</script>

@endsection
