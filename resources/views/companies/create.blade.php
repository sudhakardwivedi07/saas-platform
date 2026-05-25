@extends('layouts.app')
@section('title', 'Add New Company')
@section('page-title', 'Add New Company')

@push('styles')
<style>
.co-err {
    font-size: 11.5px; color: var(--red); margin-top: 4px;
    display: flex; align-items: center; gap: 4px;
}
.co-err::before {
    content: '';
    display: inline-block; width: 12px; height: 12px; flex-shrink: 0;
    background: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='2' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cline x1='12' y1='8' x2='12' y2='12'/%3E%3Cline x1='12' y1='16' x2='12.01' y2='16'/%3E%3C/svg%3E") center/contain no-repeat;
}
.mf-inp.co-invalid, .mf-sel.co-invalid { border-color: var(--red) !important; }
.subdomain-wrap.co-invalid { border-color: var(--red) !important; }
.create-co-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
    align-items: start;
}
.step-num {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--blue);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 8px;
}
.section-divider {
    height: 1px;
    background: var(--border);
    margin: 22px -22px;
}
.pkg-card {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 14px 16px;
    cursor: pointer;
    transition: border-color .13s, background .13s;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.pkg-card:hover { border-color: var(--blue); }
.pkg-card.selected {
    border-color: var(--blue);
    background: rgba(26,106,168,.05);
}
.pkg-card input[type=radio] { margin-top: 3px; flex-shrink: 0; accent-color: var(--blue); }

.subdomain-wrap {
    display: flex;
    align-items: stretch;
    border: 1px solid var(--border);
    border-radius: 9px;
    overflow: hidden;
    background: #fff;
}
.subdomain-wrap input {
    flex: 1;
    border: none !important;
    border-radius: 0 !important;
    background: transparent;
    padding: 9px 12px;
    font-size: 13.5px;
    color: var(--text-1);
    outline: none;
}
.subdomain-suffix {
    display: flex;
    align-items: center;
    padding: 0 13px;
    background: var(--bg);
    border-left: 1px solid var(--border);
    font-size: 13px;
    color: var(--text-2);
    white-space: nowrap;
    flex-shrink: 0;
    font-weight: 500;
}

.payment-notice {
    display: none;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 9px;
    padding: 12px 14px;
    font-size: 13px;
    color: #92400e;
    gap: 10px;
    align-items: flex-start;
    margin-top: 4px;
}
.payment-notice.show { display: flex; }
</style>
@endpush

@section('content')

{{-- Back link --}}
<div style="margin-bottom:18px">
    <a href="{{ route('my-companies.index') }}" style="display:inline-flex;align-items:center;gap:5px;font-size:13px;color:var(--text-2);text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Clients
    </a>
</div>

{{-- Error banner --}}
@if($errors->any())
<div class="info-banner" style="background:rgba(239,68,68,.06);border-color:var(--red);margin-bottom:16px">
    <svg viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" width="15" height="15"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        @foreach($errors->all() as $error)
        <div style="font-size:13px;color:var(--red)">{{ $error }}</div>
        @endforeach
    </div>
</div>
@endif

<form method="POST" action="{{ route('my-companies.store') }}" id="create-company-form" novalidate>
@csrf

@php $isClientUserFlow = auth()->user()->hasRole('client'); @endphp

<div class="create-co-layout">

    {{-- ── Left column: form ──────────────────────────────── --}}
    <div>

        {{-- Section 1: Company Info --}}
        <div class="acc-card" style="margin-bottom:16px">
            <div style="display:flex;align-items:center;margin-bottom:18px">
                <span class="step-num">1</span>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">Company Information</div>
            </div>

            <div class="mf-field">
                <label class="mf-label">Company Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="company_name" id="company_name" class="mf-inp"
                       placeholder="Acme Inc." value="{{ old('company_name') }}" maxlength="100">
                @error('company_name')
                <div style="font-size:11.5px;color:var(--red);margin-top:4px">{{ $message }}</div>
                @enderror
                <div id="err-co-name" class="co-err" style="display:none"></div>
            </div>

            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Industry Type <span style="color:var(--red)">*</span></label>
                    <select name="industry_type" id="co-industry" class="mf-sel" style="height:38px">
                        <option value="" {{ old('industry_type') ? '' : 'selected' }}>Select industry</option>
                        @foreach(['Technology','Healthcare','Finance','Manufacturing','Retail','Real Estate','Construction','Transportation','Energy','Agriculture','Education','Consulting','Legal Services','Entertainment','Automotive','Other'] as $ind)
                        <option value="{{ $ind }}" {{ old('industry_type') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                        @endforeach
                    </select>
                    <div id="err-co-industry" class="co-err" style="display:none"></div>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Entity Type <span style="color:var(--red)">*</span></label>
                    <select name="entity_type" id="co-entity" class="mf-sel" style="height:38px">
                        <option value="" {{ old('entity_type') ? '' : 'selected' }}>Select entity type</option>
                        @foreach(['LLC','C-Corporation','S-Corporation','Sole Proprietorship','Partnership','LLP','Non-Profit','Trust','Other'] as $ent)
                        <option value="{{ $ent }}" {{ old('entity_type') === $ent ? 'selected' : '' }}>{{ $ent }}</option>
                        @endforeach
                    </select>
                    <div id="err-co-entity" class="co-err" style="display:none"></div>
                </div>
            </div>

            <div class="mf-field">
                <label class="mf-label">State / Jurisdiction</label>
                <input type="text" name="state" class="mf-inp"
                       placeholder="e.g. Delaware, California" value="{{ old('state') }}">
            </div>

            <div class="mf-field">
                <label class="mf-label">Subdomain <span style="color:var(--red)">*</span></label>
                <div class="subdomain-wrap">
                    <input type="text" name="subdomain" id="subdomain"
                           placeholder="acme" value="{{ old('subdomain') }}"
                           pattern="[a-zA-Z0-9\-]+" required>
                    <div class="subdomain-suffix">.{{ config('tenancy.root_domain', 'yourdomain.com') }}</div>
                </div>
                @error('subdomain')
                <div style="font-size:11.5px;color:var(--red);margin-top:4px">{{ $message }}</div>
                @enderror
                <div id="err-co-subdomain" class="co-err" style="display:none"></div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Only letters, numbers and hyphens. Cannot be changed later.</div>
            </div>

            <div class="mf-field">
                <label class="mf-label">Company Email <span style="color:var(--red)">*</span></label>
                <input type="email" name="company_email" id="co-email" class="mf-inp"
                       placeholder="info@acme.com" value="{{ old('company_email') }}" maxlength="100">
                @error('company_email')
                <div style="font-size:11.5px;color:var(--red);margin-top:4px">{{ $message }}</div>
                @enderror
                <div id="err-co-email" class="co-err" style="display:none"></div>
            </div>
        </div>

        @if(!$isClientUserFlow)
        {{-- Section 2: Admin Account --}}
        <div class="acc-card" style="margin-bottom:16px">
            <div style="display:flex;align-items:center;margin-bottom:6px">
                <span class="step-num">2</span>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">Company Admin Account</div>
            </div>

            {{-- Admin type toggle --}}
            <div style="display:flex;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:3px;gap:3px;margin-bottom:18px;margin-left:32px">
                <button type="button" id="btn-admin-new"
                    onclick="setAdminType('new')"
                    style="flex:1;padding:7px 14px;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;transition:all .12s;background:var(--orange);color:#fff">
                    New Admin
                </button>
                <button type="button" id="btn-admin-existing"
                    onclick="setAdminType('existing')"
                    style="flex:1;padding:7px 14px;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;transition:all .12s;background:transparent;color:var(--text-2)">
                    Existing Client
                </button>
            </div>

            <input type="hidden" name="admin_type" id="admin_type" value="{{ old('admin_type', 'new') }}">

            {{-- Panel: New Admin --}}
            <div id="panel-admin-new">
                <div style="font-size:12.5px;color:var(--text-3);margin-bottom:14px;padding-left:0">
                    Create login credentials for this company's administrator.
                </div>
                <div class="mf-field">
                    <label class="mf-label">Admin Full Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="admin_name" id="co-admin-name" class="mf-inp"
                           placeholder="Jane Smith" value="{{ old('admin_name') }}" maxlength="100">
                    @error('admin_name')
                    <div style="font-size:11.5px;color:var(--red);margin-top:4px">{{ $message }}</div>
                    @enderror
                    <div id="err-co-admin-name" class="co-err" style="display:none"></div>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Admin Email <span style="color:var(--red)">*</span></label>
                    <input type="email" name="admin_email" id="co-admin-email" class="mf-inp"
                           placeholder="jane@acme.com" value="{{ old('admin_email') }}" maxlength="100">
                    @error('admin_email')
                    <div style="font-size:11.5px;color:var(--red);margin-top:4px">{{ $message }}</div>
                    @enderror
                    <div id="err-co-admin-email" class="co-err" style="display:none"></div>
                </div>
                <div class="form-2col">
                    <div class="mf-field">
                        <label class="mf-label">Password <span style="color:var(--red)">*</span></label>
                        <input type="password" name="admin_password" id="co-admin-pwd" class="mf-inp"
                               placeholder="Min. 8 characters" autocomplete="new-password">
                        @error('admin_password')
                        <div style="font-size:11.5px;color:var(--red);margin-top:4px">{{ $message }}</div>
                        @enderror
                        <div id="err-co-admin-pwd" class="co-err" style="display:none"></div>
                    </div>
                    <div class="mf-field">
                        <label class="mf-label">Confirm Password <span style="color:var(--red)">*</span></label>
                        <input type="password" name="admin_password_confirmation" id="co-admin-confirm" class="mf-inp"
                               placeholder="Repeat password" autocomplete="new-password">
                        <div id="err-co-admin-confirm" class="co-err" style="display:none"></div>
                    </div>
                </div>
            </div>

            {{-- Panel: Existing Client --}}
            <div id="panel-admin-existing" style="display:none">
                <div style="font-size:12.5px;color:var(--text-3);margin-bottom:14px">
                    Assign an existing client as the administrator for this company.
                </div>
                @if(($existingClients ?? collect())->isEmpty())
                <div style="padding:16px;background:var(--bg);border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text-3);text-align:center">
                    No existing clients found. Add clients first from the Clients menu.
                </div>
                @else
                <div class="mf-field">
                    <label class="mf-label">Select Client <span style="color:var(--red)">*</span></label>
                    <select name="existing_client_id" id="existing_client_id" class="mf-sel" style="height:38px">
                        <option value="">— Select a client —</option>
                        @foreach($existingClients ?? [] as $client)
                        <option value="{{ $client->id }}"
                            {{ old('existing_client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }} ({{ $client->email }})
                        </option>
                        @endforeach
                    </select>
                    @error('existing_client_id')
                    <div style="font-size:11.5px;color:var(--red);margin-top:4px">{{ $message }}</div>
                    @enderror
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(!$isClientUserFlow)
        {{-- Section 3: Choose a Plan --}}
        <div class="acc-card">
            <div style="display:flex;align-items:center;margin-bottom:6px">
                <span class="step-num">3</span>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">Choose a Plan</div>
            </div>
            <div style="font-size:12.5px;color:var(--text-3);margin-bottom:18px;padding-left:32px">
                These are your own plans, separate from the SuperAdmin's global plans.
            </div>

            @if($packages->isEmpty())
            <div style="text-align:center;padding:36px 20px;border:1.5px dashed var(--border);border-radius:10px">
                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <div style="font-size:14px;font-weight:500;color:var(--text-2)">No plans configured yet</div>
                <div style="font-size:12.5px;color:var(--text-3);margin-top:4px;margin-bottom:14px">Create at least one plan before adding companies.</div>
                <a href="{{ route('plans.create') }}" class="btn btn-ghost btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create a Plan First
                </a>
            </div>
            @else
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach($packages as $pkg)
                @php $isFree = $pkg->is_free || ($pkg->monthly_price ?? 0) == 0; @endphp
                <label class="pkg-card {{ !old('package_id') && $loop->first || old('package_id') == $pkg->id ? 'selected' : '' }}"
                       onclick="selectPkg(this, {{ $pkg->id }}, {{ $pkg->monthly_price ?? 0 }})">
                    <input type="radio" name="package_id" value="{{ $pkg->id }}"
                           {{ !old('package_id') && $loop->first || old('package_id') == $pkg->id ? 'checked' : '' }}>
                    <div style="flex:1">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                            <div>
                                <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">
                                    {{ $pkg->name }}
                                    @if($pkg->is_recommended ?? false)
                                    <span class="badge b-orange" style="font-size:10px;margin-left:6px">Popular</span>
                                    @endif
                                    @if($isFree)
                                    <span class="badge b-green" style="font-size:10px;margin-left:6px">Free</span>
                                    @endif
                                </div>
                                @if($pkg->description)
                                <div style="font-size:12px;color:var(--text-3);margin-top:2px">{{ $pkg->description }}</div>
                                @endif
                                @if($pkg->is_trial_allowed && !$isFree)
                                <div style="font-size:11.5px;color:var(--green);margin-top:4px;font-weight:500">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                                    {{ $pkg->trial_days }}-day free trial included
                                </div>
                                @endif
                                @if($pkg->insights_per_month || $pkg->max_companies)
                                <div style="font-size:11px;color:var(--text-3);margin-top:3px">
                                    @if($pkg->insights_per_month) {{ $pkg->insights_per_month }} insights/month @endif
                                    @if($pkg->insights_per_month && $pkg->max_companies) · @endif
                                    @if($pkg->max_companies) Up to {{ $pkg->max_companies }} companies @endif
                                </div>
                                @endif
                            </div>
                            <div style="text-align:right;flex-shrink:0">
                                @if($isFree)
                                <span style="font-size:20px;font-weight:700;color:var(--green)">Free</span>
                                @else
                                <span style="font-size:20px;font-weight:700;color:var(--text-1)">${{ number_format($pkg->monthly_price ?? 0, 0) }}</span>
                                <span style="font-size:12px;color:var(--text-3)">/mo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Payment notice --}}
            <div class="payment-notice" id="payment-notice" style="margin-top:14px">
                <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2" width="16" height="16" style="flex-shrink:0;margin-top:1px"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <div>
                    <strong>Payment required.</strong> You'll be redirected to our secure payment page to complete your subscription. The company will be activated immediately after payment.
                </div>
            </div>
            @endif

            {{-- Submit --}}
            <div style="margin-top:20px">
                <button type="submit" id="submit-btn" class="btn btn-primary"
                        style="width:100%;justify-content:center;padding:11px 20px;font-size:14px"
                        {{ $packages->isEmpty() ? 'disabled' : '' }}>
                    @if($packages->isEmpty())
                        Create a Plan First
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        <span id="submit-label">Create Company</span>
                    @endif
                </button>
            </div>
        </div>
        @else
        <div style="margin-top:16px">
            <button type="submit" id="submit-btn" class="btn btn-primary"
                    style="width:100%;justify-content:center;padding:11px 20px;font-size:14px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                Create Company
            </button>
        </div>
        @endif

    </div>

    {{-- ── Right column: summary sidebar ─────────────────── --}}
    <div style="position:sticky;top:20px;display:flex;flex-direction:column;gap:14px">

        {{-- Live Preview --}}
        <div class="acc-card">
            <div style="font-size:13px;font-weight:600;color:var(--text-1);margin-bottom:14px;display:flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Company Preview
            </div>
            <div style="text-align:center;padding:16px 0">
                <div id="preview-avatar" style="width:48px;height:48px;border-radius:12px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff;margin:0 auto 10px">?</div>
                <div id="preview-name" style="font-size:14px;font-weight:600;color:var(--text-1)">Company Name</div>
                <div id="preview-subdomain" style="font-size:12px;color:var(--text-3);margin-top:2px">subdomain.{{ config('tenancy.root_domain', 'yourdomain.com') }}</div>
                <div id="preview-email" style="font-size:11.5px;color:var(--text-3);margin-top:2px"></div>
            </div>
            <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:4px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <span style="font-size:11.5px;color:var(--text-3)">Selected Plan</span>
                    <span id="preview-plan" class="badge b-blue" style="font-size:11px">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:11.5px;color:var(--text-3)">Monthly Price</span>
                    <span id="preview-price" style="font-size:13px;font-weight:600;color:var(--text-1)">—</span>
                </div>
            </div>
        </div>

        {{-- Tips --}}
        <div style="background:linear-gradient(135deg,#1a3a5c,#1a6aa8);border-radius:12px;padding:18px;color:#fff">
            <div style="font-size:13px;font-weight:600;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Quick Tips
            </div>
            <ul style="font-size:12px;color:rgba(255,255,255,.75);line-height:1.7;padding-left:14px;margin:0">
                <li>The subdomain is permanent and cannot be changed.</li>
                <li>The admin will receive a welcome email with login details.</li>
                <li>Paid plans redirect to Stripe for secure payment.</li>
                <li>Free plans activate instantly.</li>
            </ul>
        </div>

    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
// Live preview update
function updatePreview() {
    const name      = document.getElementById('company_name').value;
    const subdomain = document.getElementById('subdomain').value;
    const email     = document.querySelector('[name=company_email]')?.value || '';

    const initials = name.trim()
        ? name.trim().split(/\s+/).slice(0,2).map(w => w[0].toUpperCase()).join('')
        : '?';

    document.getElementById('preview-avatar').textContent     = initials;
    document.getElementById('preview-name').textContent       = name || 'Company Name';
    document.getElementById('preview-subdomain').textContent  = (subdomain || 'subdomain') + '.{{ config('tenancy.root_domain', 'yourdomain.com') }}';
    document.getElementById('preview-email').textContent      = email;
}

document.getElementById('company_name')?.addEventListener('input', function() {
    // Auto-fill subdomain
    const sub = document.getElementById('subdomain');
    if (!sub.dataset.touched) {
        sub.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
    updatePreview();
});

document.getElementById('subdomain')?.addEventListener('input', function() {
    this.dataset.touched = 'true';
    this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
    updatePreview();
});

document.querySelector('[name=company_email]')?.addEventListener('input', updatePreview);

// Plan selection
function selectPkg(label, pkgId, price) {
    document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input[type=radio]').checked = true;

    const notice = document.getElementById('payment-notice');
    const lbl    = document.getElementById('submit-label');
    const planBadge  = document.getElementById('preview-plan');
    const priceBadge = document.getElementById('preview-price');

    const planName = label.querySelector('[style*="font-weight:600"]')?.textContent?.trim() || 'Plan';

    if (planBadge) planBadge.textContent = planName;
    if (priceBadge) priceBadge.textContent = price > 0 ? '$' + price + '/mo' : 'Free';

    if (price > 0) {
        notice?.classList.add('show');
        if (lbl) lbl.textContent = 'Continue to Payment';
    } else {
        notice?.classList.remove('show');
        if (lbl) lbl.textContent = 'Create Company';
    }
}

function setAdminType(type) {
    var isNew = type === 'new';
    document.getElementById('admin_type').value = type;
    document.getElementById('panel-admin-new').style.display = isNew ? '' : 'none';
    document.getElementById('panel-admin-existing').style.display = isNew ? 'none' : '';

    ['admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation'].forEach(function(n) {
        var el = document.querySelector('[name="' + n + '"]');
        if (el) el.required = isNew;
    });
    var existingEl = document.getElementById('existing_client_id');
    if (existingEl) existingEl.required = !isNew;

    var btnNew = document.getElementById('btn-admin-new');
    var btnExisting = document.getElementById('btn-admin-existing');
    if (btnNew) {
        btnNew.style.background = isNew ? 'var(--orange)' : 'transparent';
        btnNew.style.color = isNew ? '#fff' : 'var(--text-2)';
    }
    if (btnExisting) {
        btnExisting.style.background = isNew ? 'transparent' : 'var(--orange)';
        btnExisting.style.color = isNew ? 'var(--text-2)' : '#fff';
    }
}

// ── Company Form Validation ──────────────────────────────────
function coErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'flex' : 'none';
}
function coMark(inputId, invalid) {
    var el = document.getElementById(inputId);
    if (!el) return;
    if (invalid) el.classList.add('co-invalid');
    else el.classList.remove('co-invalid');
}

function validateCompanyForm(e) {
    e.preventDefault();
    var ok = true;
    var emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Company Name
    var nameEl = document.getElementById('company_name');
    var nameVal = nameEl ? nameEl.value.trim() : '';
    if (!nameVal) {
        coErr('err-co-name', 'Company name is required.'); coMark('company_name', true); ok = false;
    } else if (nameVal.length < 2) {
        coErr('err-co-name', 'Minimum 2 characters required.'); coMark('company_name', true); ok = false;
    } else {
        coErr('err-co-name', ''); coMark('company_name', false);
    }

    // Industry Type
    var indEl = document.getElementById('co-industry');
    if (!indEl || !indEl.value) {
        coErr('err-co-industry', 'Please select an industry.'); coMark('co-industry', true); ok = false;
    } else {
        coErr('err-co-industry', ''); coMark('co-industry', false);
    }

    // Entity Type
    var entEl = document.getElementById('co-entity');
    if (!entEl || !entEl.value) {
        coErr('err-co-entity', 'Please select an entity type.'); coMark('co-entity', true); ok = false;
    } else {
        coErr('err-co-entity', ''); coMark('co-entity', false);
    }

    // Subdomain
    var subEl = document.getElementById('subdomain');
    var subVal = subEl ? subEl.value.trim() : '';
    var subWrap = subEl ? subEl.closest('.subdomain-wrap') : null;
    if (!subVal) {
        coErr('err-co-subdomain', 'Subdomain is required.');
        if (subWrap) subWrap.classList.add('co-invalid'); ok = false;
    } else if (!/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?$/.test(subVal)) {
        coErr('err-co-subdomain', 'Use only lowercase letters, numbers and hyphens. Cannot start or end with a hyphen.');
        if (subWrap) subWrap.classList.add('co-invalid'); ok = false;
    } else {
        coErr('err-co-subdomain', '');
        if (subWrap) subWrap.classList.remove('co-invalid');
    }

    // Company Email
    var coEmailEl = document.getElementById('co-email');
    var coEmailVal = coEmailEl ? coEmailEl.value.trim() : '';
    if (!coEmailVal) {
        coErr('err-co-email', 'Company email is required.'); coMark('co-email', true); ok = false;
    } else if (!emailRx.test(coEmailVal)) {
        coErr('err-co-email', 'Enter a valid email address.'); coMark('co-email', true); ok = false;
    } else {
        coErr('err-co-email', ''); coMark('co-email', false);
    }

    // Admin section
    var adminTypeEl = document.getElementById('admin_type');
    if (adminTypeEl) {
        if (adminTypeEl.value === 'new') {
            // Admin Full Name
            var adminNameEl = document.getElementById('co-admin-name');
            var adminNameVal = adminNameEl ? adminNameEl.value.trim() : '';
            if (!adminNameVal) {
                coErr('err-co-admin-name', 'Admin full name is required.'); coMark('co-admin-name', true); ok = false;
            } else if (adminNameVal.length < 2) {
                coErr('err-co-admin-name', 'Minimum 2 characters required.'); coMark('co-admin-name', true); ok = false;
            } else if (!/^[a-zA-Z\s]+$/.test(adminNameVal)) {
                coErr('err-co-admin-name', 'Name must contain only letters and spaces.'); coMark('co-admin-name', true); ok = false;
            } else {
                coErr('err-co-admin-name', ''); coMark('co-admin-name', false);
            }

            // Admin Email
            var adminEmailEl = document.getElementById('co-admin-email');
            var adminEmailVal = adminEmailEl ? adminEmailEl.value.trim() : '';
            if (!adminEmailVal) {
                coErr('err-co-admin-email', 'Admin email is required.'); coMark('co-admin-email', true); ok = false;
            } else if (!emailRx.test(adminEmailVal)) {
                coErr('err-co-admin-email', 'Enter a valid email address.'); coMark('co-admin-email', true); ok = false;
            } else {
                coErr('err-co-admin-email', ''); coMark('co-admin-email', false);
            }

            // Password
            var pwdEl = document.getElementById('co-admin-pwd');
            var pwdVal = pwdEl ? pwdEl.value : '';
            if (!pwdVal) {
                coErr('err-co-admin-pwd', 'Password is required.'); coMark('co-admin-pwd', true); ok = false;
            } else if (pwdVal.length < 8) {
                coErr('err-co-admin-pwd', 'Password must be at least 8 characters.'); coMark('co-admin-pwd', true); ok = false;
            } else {
                coErr('err-co-admin-pwd', ''); coMark('co-admin-pwd', false);
            }

            // Confirm Password
            var confirmEl = document.getElementById('co-admin-confirm');
            var confirmVal = confirmEl ? confirmEl.value : '';
            if (!confirmVal) {
                coErr('err-co-admin-confirm', 'Please confirm the password.'); coMark('co-admin-confirm', true); ok = false;
            } else if (confirmVal !== pwdVal) {
                coErr('err-co-admin-confirm', 'Passwords do not match.'); coMark('co-admin-confirm', true); ok = false;
            } else {
                coErr('err-co-admin-confirm', ''); coMark('co-admin-confirm', false);
            }
        } else {
            // Existing client
            var existingEl = document.getElementById('existing_client_id');
            if (existingEl && !existingEl.value) {
                existingEl.classList.add('co-invalid');
                ok = false;
                Swal.fire({ icon: 'warning', title: 'Please select an existing client.', confirmButtonColor: '#1a6aa8' });
            } else if (existingEl) {
                existingEl.classList.remove('co-invalid');
            }
        }
    }

    if (ok) {
        document.getElementById('create-company-form').submit();
    }
}

// Real-time: restrict admin name to letters + spaces
(function() {
    var el = document.getElementById('co-admin-name');
    if (!el) return;
    el.addEventListener('input', function() {
        var pos = this.selectionStart;
        var cleaned = this.value.replace(/[^a-zA-Z\s]/g, '');
        if (cleaned !== this.value) {
            this.value = cleaned;
            this.setSelectionRange(Math.max(0, pos - 1), Math.max(0, pos - 1));
        }
    });
})();

// Attach validation to form submit
(function() {
    var form = document.getElementById('create-company-form');
    if (form) form.addEventListener('submit', validateCompanyForm);
})();

// Init on load
(function() {
    updatePreview();
    var adminTypeEl = document.getElementById('admin_type');
    if (adminTypeEl) setAdminType(adminTypeEl.value || 'new');
    const checked = document.querySelector('input[name=package_id]:checked');
    if (checked) {
        const card  = checked.closest('.pkg-card');
        const priceEl = card?.querySelector('[style*="font-weight:700"]');
        const rawTxt  = priceEl?.textContent || '0';
        const price   = parseFloat(rawTxt.replace(/[^0-9.]/g, '')) || 0;
        const planName = card?.querySelector('[style*="font-weight:600"]')?.textContent?.trim() || '—';

        const planBadge  = document.getElementById('preview-plan');
        const priceBadge = document.getElementById('preview-price');
        if (planBadge)  planBadge.textContent  = planName;
        if (priceBadge) priceBadge.textContent = price > 0 ? '$' + price + '/mo' : 'Free';

        if (price > 0) {
            document.getElementById('payment-notice')?.classList.add('show');
            const lbl = document.getElementById('submit-label');
            if (lbl) lbl.textContent = 'Continue to Payment';
        }
    }
})();
</script>
@endpush
