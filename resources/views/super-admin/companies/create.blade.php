@extends('layouts.superadmin.app')
@section('title', 'Add Firm')
@section('page-title', 'Add Firm')

@section('content')
<div class="ph">
    <div>
        <h1>Add New Firm</h1>
        <p>Onboard a new partner firm to the platform</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Firms
        </a>
    </div>
</div>

<form id="createForm">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

        {{-- ── LEFT: MAIN FORM ─────────────────────────────── --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            {{-- Company Details --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border)">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--blue3);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8" width="16" height="16"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600">Company Details</div>
                        <div style="font-size:12px;color:var(--text-3)">Basic information about the firm</div>
                    </div>
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Company Name <span style="color:var(--red)">*</span></label>
                        <input name="company_name" id="company_name" class="f-inp" placeholder="Acme Corp" required
                               oninput="autoFillSubdomain(this.value)">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Brand / App Name</label>
                        <input name="app_name" class="f-inp" placeholder="Leave blank to use company name">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Company Email <span style="color:var(--red)">*</span></label>
                        <input name="company_email" type="email" class="f-inp" placeholder="billing@acme.com" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Phone</label>
                        <input name="company_phone" class="f-inp" placeholder="+1 (555) 000-0000">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Subdomain <span style="color:var(--red)">*</span></label>
                        <div style="display:flex;align-items:stretch;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:#fff">
                            <input name="subdomain" id="subdomain" class="f-inp"
                                   placeholder="acme" required
                                   style="border:none;border-radius:0;flex:1;outline:none"
                                   oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'');this.dataset.manual='1'">
                            <span style="padding:0 12px;display:flex;align-items:center;background:var(--bg);border-left:1.5px solid var(--border);font-size:12px;color:var(--text-3);white-space:nowrap;flex-shrink:0">
                                .{{ config('tenancy.root_domain','yourdomain.com') }}
                            </span>
                        </div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Letters, numbers and hyphens only. Cannot be changed later.</div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Status</label>
                        <select name="status" class="f-sel">
                            <option value="active">Active</option>
                            <option value="trial">Trial</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
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
                        <div style="font-size:12px;color:var(--text-3)">Select a subscription plan for this firm</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
                    <label class="pkg-option {{ !old('package_id') ? 'selected' : '' }}">
                        <input type="radio" name="package_id" value="" {{ !old('package_id') ? 'checked' : '' }}>
                        <div class="pkg-option-body">
                            <div style="font-size:13px;font-weight:600;margin-bottom:2px">No Plan</div>
                            <div style="font-size:11.5px;color:var(--text-3)">Assign later</div>
                        </div>
                    </label>
                    @foreach($packages as $pkg)
                    <label class="pkg-option {{ old('package_id') == $pkg->id ? 'selected' : '' }}" id="pkg-{{ $pkg->id }}">
                        <input type="radio" name="package_id" value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'checked' : '' }}>
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

            {{-- Admin User --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border)">
                    <div style="width:32px;height:32px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="1.8" width="16" height="16"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600">Admin User</div>
                        <div style="font-size:12px;color:var(--text-3)">This user will manage the firm's dashboard</div>
                    </div>
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Full Name <span style="color:var(--red)">*</span></label>
                        <input name="admin_name" class="f-inp" placeholder="John Smith" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Email Address <span style="color:var(--red)">*</span></label>
                        <input name="admin_email" type="email" class="f-inp" placeholder="john@acme.com" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Password <span style="color:var(--red)">*</span></label>
                        <div style="position:relative">
                            <input name="admin_password" id="admin_password" type="password" class="f-inp"
                                   placeholder="Min. 8 characters" required minlength="8" style="padding-right:40px">
                            <button type="button" onclick="togglePw()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-3);cursor:pointer;padding:4px">
                                <svg id="pw-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Confirm Password <span style="color:var(--red)">*</span></label>
                        <input name="admin_password_confirmation" id="admin_pw_confirm" type="password" class="f-inp"
                               placeholder="Re-enter password" required minlength="8">
                        <div id="pw-match-msg" style="font-size:11.5px;margin-top:4px;display:none"></div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:16px 22px;background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh)">
                <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" id="submit-btn" class="btn btn-primary" style="min-width:140px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                    Create Firm
                </button>
            </div>
        </div>

        {{-- ── RIGHT: SIDEBAR ───────────────────────────────── --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            {{-- Preview card --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="font-size:13px;font-weight:600;margin-bottom:14px">Firm Preview</div>
                <div style="display:flex;align-items:center;gap:12px;padding:14px;background:var(--bg);border-radius:10px;margin-bottom:12px">
                    <div id="preview-av" style="width:42px;height:42px;border-radius:10px;background:var(--blue);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:700;flex-shrink:0">??</div>
                    <div>
                        <div id="preview-name" style="font-size:14px;font-weight:600;color:var(--text-1)">Company Name</div>
                        <div id="preview-sub" style="font-size:11.5px;color:var(--text-3)">subdomain.{{ config('tenancy.root_domain','yourdomain.com') }}</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <div style="display:flex;justify-content:space-between;font-size:12.5px">
                        <span style="color:var(--text-3)">Status</span>
                        <span id="preview-status" class="badge b-green" style="font-size:11px">Active</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12.5px">
                        <span style="color:var(--text-3)">Plan</span>
                        <span id="preview-plan" style="font-weight:500;color:var(--text-1)">No Plan</span>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="font-size:13px;font-weight:600;margin-bottom:12px">Onboarding Tips</div>
                <div style="display:flex;flex-direction:column;gap:10px">
                    @foreach([
                        ['var(--blue)', 'Subdomain is permanent — choose carefully.'],
                        ['var(--green)', 'A trial plan lets the firm explore before paying.'],
                        ['var(--orange)', 'The admin email is used for all notifications.'],
                        ['var(--purple)', 'You can always edit details after creation.'],
                    ] as [$color, $tip])
                    <div style="display:flex;align-items:flex-start;gap:9px">
                        <div style="width:7px;height:7px;border-radius:50%;background:{{ $color }};flex-shrink:0;margin-top:5px"></div>
                        <div style="font-size:12.5px;color:var(--text-2);line-height:1.5">{{ $tip }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- What happens next --}}
            <div class="acc-card" style="margin-bottom:0;background:linear-gradient(135deg,#0a2540 0%,var(--blue) 100%);border:none">
                <div style="font-size:13px;font-weight:600;color:#fff;margin-bottom:10px">What happens next?</div>
                <div style="display:flex;flex-direction:column;gap:9px">
                    @foreach(['Firm dashboard is created','Admin user receives login email','Subdomain goes live immediately','Billing cycle starts after trial'] as $i => $step)
                    <div style="display:flex;align-items:flex-start;gap:9px">
                        <div style="width:20px;height:20px;border-radius:50%;background:rgba(255,255,255,.15);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $i+1 }}</div>
                        <div style="font-size:12px;color:rgba(255,255,255,.8);line-height:1.5;margin-top:2px">{{ $step }}</div>
                    </div>
                    @endforeach
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
</style>
@endpush

@push('scripts')
<script>
// Auto-fill subdomain from company name
function autoFillSubdomain(val) {
    const sub = document.getElementById('subdomain');
    if (!sub.dataset.manual) {
        sub.value = val.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        updatePreview();
    }
    updatePreview();
}

// Package card selection
document.querySelectorAll('.pkg-option input[type=radio]').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('.pkg-option').forEach(c => c.classList.remove('selected'));
        this.closest('.pkg-option').classList.add('selected');
        const label = this.closest('.pkg-option').querySelector('[style*="font-weight:600"]');
        document.getElementById('preview-plan').textContent = label ? label.textContent : 'No Plan';
    });
});

// Status badge update
document.querySelector('[name=status]').addEventListener('change', function() {
    const map = {
        active:    ['Active',    'b-green'],
        trial:     ['Trial',     'b-amber'],
        inactive:  ['Inactive',  'b-gray'],
        suspended: ['Suspended', 'b-red'],
    };
    const [label, cls] = map[this.value] || ['Active','b-green'];
    const el = document.getElementById('preview-status');
    el.textContent = label;
    el.className = 'badge ' + cls;
});

// Live preview
function updatePreview() {
    const name = document.getElementById('company_name').value || 'Company Name';
    const sub  = document.getElementById('subdomain').value   || 'subdomain';
    const initials = name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) || '??';
    const colors = ['#0C84BC','#10b981','#F06B21','#7c3aed','#0d9488','#e11d48'];
    const color  = colors[name.charCodeAt(0) % colors.length] || '#0C84BC';

    document.getElementById('preview-av').textContent = initials;
    document.getElementById('preview-av').style.background = color;
    document.getElementById('preview-name').textContent = name;
    document.getElementById('preview-sub').textContent = sub + '.{{ config('tenancy.root_domain','yourdomain.com') }}';
}

document.getElementById('subdomain').addEventListener('input', updatePreview);

// Password toggle
function togglePw() {
    const inp = document.getElementById('admin_password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

// Password match check
document.getElementById('admin_pw_confirm').addEventListener('input', function() {
    const pw  = document.getElementById('admin_password').value;
    const msg = document.getElementById('pw-match-msg');
    if (!this.value) { msg.style.display='none'; return; }
    msg.style.display = 'block';
    if (this.value === pw) {
        msg.textContent = '✓ Passwords match';
        msg.style.color = 'var(--green)';
    } else {
        msg.textContent = '✗ Passwords do not match';
        msg.style.color = 'var(--red)';
    }
});

// Submit
document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const pw  = document.getElementById('admin_password').value;
    const pwc = document.getElementById('admin_pw_confirm').value;
    if (pw !== pwc) { showToast('Passwords do not match', 'error'); return; }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true; btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg> Creating…';

    const body = Object.fromEntries(new FormData(this).entries());
    const res  = await apiCall('{{ route("superadmin.companies.store") }}', 'POST', body);

    if (res.status === 'success') {
        showToast('Firm created successfully!');
        setTimeout(() => window.location = res.redirect, 900);
    } else {
        showToast(res.message || 'Error creating firm', 'error');
        btn.disabled = false;
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg> Create Firm';
    }
});
</script>
<style>
@keyframes spin { from { transform:rotate(0deg) } to { transform:rotate(360deg) } }
</style>
@endpush
