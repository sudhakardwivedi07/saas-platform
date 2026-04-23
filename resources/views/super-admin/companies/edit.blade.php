@extends('layouts.superadmin.app')
@section('title', 'Edit Firm')
@section('page-title', 'Edit: ' . $company->company_name)

@section('content')
<div class="ph">
    <div>
        <h1>Edit Firm</h1>
        <p>{{ $company->company_name }}</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.companies.show', $company) }}" class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Firm
        </a>
    </div>
</div>

<form id="company-form" method="POST" action="{{ route('superadmin.companies.update', $company) }}" novalidate>
    @csrf @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

        {{-- ── LEFT ─────────────────────────────────────────── --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            {{-- Company Details --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border)">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--blue3);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8" width="16" height="16"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600">Firm Details</div>
                        <div style="font-size:12px;color:var(--text-3)">Update firm information</div>
                    </div>
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Firm Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="company_name" id="company_name" class="f-inp"
                               value="{{ old('company_name', $company->company_name) }}" required
                               oninput="updatePreview()">
                        <div class="field-error-msg" id="err-company_name"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Brand / App Name</label>
                        <input type="text" name="app_name" class="f-inp"
                               value="{{ old('app_name', $company->app_name) }}"
                               placeholder="Leave blank to use firm name">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Firm Email <span style="color:var(--red)">*</span></label>
                        <input type="email" name="company_email" id="company_email" class="f-inp"
                               value="{{ old('company_email', $company->company_email) }}">
                        <div class="field-error-msg" id="err-company_email"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Phone</label>
                        <input type="text" name="company_phone" class="f-inp"
                               value="{{ old('company_phone', $company->company_phone) }}"
                               placeholder="+1 (555) 000-0000">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Subdomain <span style="color:var(--red)">*</span></label>
                        <div style="display:flex;align-items:stretch;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg)">
                            <input value="{{ $company->subdomain }}" class="f-inp"
                                   disabled style="border:none;border-radius:0;flex:1;outline:none;background:var(--bg);color:var(--text-3)">
                            <span style="padding:0 12px;display:flex;align-items:center;background:var(--bg);border-left:1.5px solid var(--border);font-size:12px;color:var(--text-3);white-space:nowrap">
                                .{{ config('tenancy.root_domain','yourdomain.com') }}
                            </span>
                        </div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Subdomain cannot be changed after creation.</div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Status <span style="color:var(--red)">*</span></label>
                        <select name="status" id="status" class="f-sel" onchange="updateStatusPreview(this.value)">
                            <option value="">-- Select Status --</option>
                            @foreach(['active','trial','inactive','suspended'] as $s)
                            <option value="{{ $s }}" {{ $company->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <div class="field-error-msg" id="err-status"></div>
                    </div>
                </div>
            </div>

            {{-- Plan Selection --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border)">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--orange-l);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="1.8" width="16" height="16"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600">Plan Assignment</div>
                        <div style="font-size:12px;color:var(--text-3)">Change the subscription plan</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
                    <label class="pkg-option {{ !$company->package_id ? 'selected' : '' }}">
                        <input type="radio" name="package_id" value="" {{ !$company->package_id ? 'checked' : '' }}>
                        <div class="pkg-option-body">
                            <div style="font-size:13px;font-weight:600;margin-bottom:2px">No Plan</div>
                            <div style="font-size:11.5px;color:var(--text-3)">Unassigned</div>
                        </div>
                    </label>
                    @foreach($packages as $pkg)
                    <label class="pkg-option {{ $company->package_id == $pkg->id ? 'selected' : '' }}">
                        <input type="radio" name="package_id" value="{{ $pkg->id }}"
                               {{ $company->package_id == $pkg->id ? 'checked' : '' }}>
                        <div class="pkg-option-body">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                                <div style="font-size:13px;font-weight:600">{{ $pkg->name }}</div>
                                <div style="font-size:15px;font-weight:700;color:var(--orange)">${{ number_format($pkg->monthly_price,0) }}<span style="font-size:10px;font-weight:400;color:var(--text-3)">/mo</span></div>
                            </div>
                            @if($pkg->is_trial_allowed)
                            <div style="font-size:11px;color:var(--green);margin-top:4px">✓ {{ $pkg->trial_days }}-day free trial</div>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:16px 22px;background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh)">
                <a href="{{ route('superadmin.companies.show', $company) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- ── RIGHT: SIDEBAR ───────────────────────────────── --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            {{-- Firm summary --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="font-size:13px;font-weight:600;margin-bottom:14px">Firm Summary</div>
                <div style="display:flex;align-items:center;gap:12px;padding:14px;background:var(--bg);border-radius:10px;margin-bottom:14px">
                    @php $initials = strtoupper(substr($company->company_name,0,1)) . strtoupper(substr(explode(' ',$company->company_name)[1] ?? $company->company_name,0,1)); @endphp
                    <div id="preview-av" style="width:42px;height:42px;border-radius:10px;background:var(--blue);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:700;flex-shrink:0">{{ $initials }}</div>
                    <div>
                        <div id="preview-name" style="font-size:14px;font-weight:600">{{ $company->company_name }}</div>
                        <div style="font-size:11.5px;color:var(--text-3)">{{ $company->subdomain }}.{{ config('tenancy.root_domain') }}</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:9px">
                    <div style="display:flex;justify-content:space-between;font-size:12.5px">
                        <span style="color:var(--text-3)">Status</span>
                        @php $sb = match($company->status) { 'active'=>['Active','b-green'], 'trial'=>['Trial','b-amber'], 'suspended'=>['Suspended','b-red'], default=>['Inactive','b-gray'] }; @endphp
                        <span id="preview-status" class="badge {{ $sb[1] }}" style="font-size:11px">{{ $sb[0] }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12.5px">
                        <span style="color:var(--text-3)">Users</span>
                        <span style="font-weight:500">{{ $company->users()->count() }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12.5px">
                        <span style="color:var(--text-3)">Joined</span>
                        <span style="font-weight:500">{{ $company->created_at?->format('M d, Y') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12.5px">
                        <span style="color:var(--text-3)">Plan</span>
                        <span style="font-weight:500">{{ $company->package?->name ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="font-size:13px;font-weight:600;margin-bottom:12px">Quick Actions</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <a href="{{ route('superadmin.companies.show', $company) }}" class="btn btn-ghost btn-sm" style="justify-content:flex-start">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Full Profile
                    </a>
                    <form method="POST" action="{{ route('superadmin.companies.impersonate', $company) }}" style="display:contents">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm" style="justify-content:flex-start">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                            Login as Firm
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('styles')
<style>
.pkg-option {
    display:flex; align-items:flex-start; gap:10px;
    border:2px solid var(--border); border-radius:10px;
    padding:12px 14px; cursor:pointer; transition:all .13s;
}
.pkg-option:hover { border-color: var(--orange); }
.pkg-option.selected { border-color:var(--orange); background:#fff9f6; }
.pkg-option input[type=radio] { accent-color:var(--orange); flex-shrink:0; margin-top:2px; }
.pkg-option-body { flex:1; }
.field-error-msg {
    font-size:11.5px;
    color:#dc2626;
    margin-top:4px;
    display:none;
}
.f-inp.is-invalid {
    border-color:#dc2626 !important;
}
.f-sel.is-invalid {
    border-color:#dc2626 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.pkg-option input[type=radio]').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('.pkg-option').forEach(c => c.classList.remove('selected'));
        this.closest('.pkg-option').classList.add('selected');
    });
});

function updatePreview() {
    const name = document.getElementById('company_name').value || '{{ $company->company_name }}';
    const initials = name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) || '??';
    document.getElementById('preview-av').textContent  = initials;
    document.getElementById('preview-name').textContent = name;
}

function updateStatusPreview(val) {
    const map = { active:['Active','b-green'], trial:['Trial','b-amber'], inactive:['Inactive','b-gray'], suspended:['Suspended','b-red'] };
    const [label, cls] = map[val] || ['Active','b-green'];
    const el = document.getElementById('preview-status');
    el.textContent = label; el.className = 'badge ' + cls;
}

function validateEditForm() {
    let valid = true;
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function showErr(errId, inputId, msg) {
        const errEl = document.getElementById(errId);
        if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
        const inp = document.getElementById(inputId);
        if (inp) inp.classList.add('is-invalid');
        valid = false;
    }

    document.querySelectorAll('.field-error-msg').forEach(el => { el.style.display = 'none'; el.textContent = ''; });
    document.querySelectorAll('.f-inp.is-invalid, .f-sel.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const companyName = (document.getElementById('company_name').value || '').trim();
    if (!companyName) showErr('err-company_name', 'company_name', 'Firm name is required.');

    const companyEmail = (document.getElementById('company_email').value || '').trim();
    if (!companyEmail) showErr('err-company_email', 'company_email', 'Firm email is required.');
    else if (!emailRe.test(companyEmail)) showErr('err-company_email', 'company_email', 'Please enter a valid email address.');

    const status = (document.getElementById('status').value || '').trim();
    if (!status) showErr('err-status', 'status', 'Please select a status.');

    return valid;
}

['company_name', 'company_email'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        const errEl = document.getElementById('err-' + id);
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
    });
});
document.getElementById('status').addEventListener('change', function() {
    this.classList.remove('is-invalid');
    const errEl = document.getElementById('err-status');
    if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
});

document.getElementById('company-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validateEditForm()) return;
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    const r = await fetch(e.target.action, {
        method: 'POST', body: new FormData(e.target),
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    const j = await r.json();
    showToast(j.message, j.status === 'success' ? 'success' : 'error');
    if (j.status === 'success') setTimeout(() => window.location = '{{ route('superadmin.companies.index') }}', 900);
    else btn.disabled = false;
});
</script>
@endpush
