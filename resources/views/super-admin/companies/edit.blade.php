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

@php $currentDomainType = $company->domain_type ?? 'subdomain'; @endphp

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
                               maxlength="100" minlength="2"
                               oninput="this.value=this.value.replace(/[^a-zA-Z0-9 .&_-]/g,'');updatePreview()">
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
                        <label class="f-label">Phone <span style="color:var(--red)">*</span></label>
                        <input type="text" name="company_phone" id="company_phone" class="f-inp"
                               value="{{ old('company_phone', $company->company_phone) }}"
                               placeholder="10-digit phone number" required
                               maxlength="10" inputmode="numeric"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <div class="field-error-msg" id="err-company_phone"></div>
                    </div>
                </div>

                {{-- ── Access Method (Domain Type) ───────────────────────── --}}
                <div style="margin-bottom:14px">
                    <label class="f-label" style="margin-bottom:8px;display:block">Access Method</label>
                    <input type="hidden" name="domain_type" id="domain_type" value="{{ $currentDomainType }}">
                    <div style="display:flex;gap:10px">
                        <label class="domain-type-opt {{ $currentDomainType === 'subdomain' ? 'selected' : '' }}"
                               id="dt-subdomain-label" onclick="domainTypeChanged('subdomain')" style="cursor:pointer">
                            <input type="radio" name="_domain_type_radio" value="subdomain"
                                   {{ $currentDomainType === 'subdomain' ? 'checked' : '' }}
                                   style="accent-color:var(--orange);flex-shrink:0">
                            <div>
                                <div style="font-size:13px;font-weight:600;margin-bottom:2px">Subdomain</div>
                                <div style="font-size:11.5px;color:var(--text-3)">{{ $company->subdomain ?? 'acme' }}.{{ config('tenancy.root_domain','yourdomain.com') }}</div>
                            </div>
                        </label>
                        <label class="domain-type-opt {{ $currentDomainType === 'domain' ? 'selected' : '' }}"
                               id="dt-domain-label" onclick="domainTypeChanged('domain')" style="cursor:pointer">
                            <input type="radio" name="_domain_type_radio" value="domain"
                                   {{ $currentDomainType === 'domain' ? 'checked' : '' }}
                                   style="accent-color:var(--orange);flex-shrink:0">
                            <div>
                                <div style="font-size:13px;font-weight:600;margin-bottom:2px">Custom Domain</div>
                                <div style="font-size:11.5px;color:var(--text-3)">{{ $company->custom_domain ?: 'app.acmecorp.com' }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-2col">
                    {{-- Read-only subdomain (shown when domain_type = subdomain) --}}
                    <div class="f-group" id="subdomain-group" style="{{ $currentDomainType === 'domain' ? 'display:none' : '' }}">
                        <label class="f-label">Subdomain</label>
                        <div style="display:flex;align-items:stretch;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg)">
                            <input value="{{ $company->subdomain }}" class="f-inp" disabled
                                   style="border:none;border-radius:0;flex:1;outline:none;background:var(--bg);color:var(--text-3)">
                            <span style="padding:0 12px;display:flex;align-items:center;background:var(--bg);border-left:1.5px solid var(--border);font-size:12px;color:var(--text-3);white-space:nowrap">
                                .{{ config('tenancy.root_domain','yourdomain.com') }}
                            </span>
                        </div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Subdomain cannot be changed after creation.</div>
                    </div>

                    {{-- Custom domain input (shown when domain_type = domain) --}}
                    <div class="f-group" id="custom-domain-group" style="{{ $currentDomainType !== 'domain' ? 'display:none' : '' }}">
                        <label class="f-label">Custom Domain <span style="color:var(--red)">*</span></label>
                        <input type="text" name="custom_domain" id="custom_domain" class="f-inp"
                               value="{{ old('custom_domain', $company->custom_domain) }}"
                               placeholder="app.acmecorp.com" maxlength="253"
                               oninput="this.value=this.value.toLowerCase().replace(/\s/g,'');updatePreview()">
                        <div class="field-error-msg" id="err-custom_domain"></div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Enter the exact domain visitors will use (no https).</div>
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

                {{-- DNS Setup Instructions (shown when domain_type = domain) --}}
                <div id="dns-instructions-group" style="{{ $currentDomainType !== 'domain' ? 'display:none' : '' }};margin-bottom:4px">
                    <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:16px 18px">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span style="font-size:13px;font-weight:600;color:#92400e">DNS Configuration Required</span>
                        </div>
                        <p style="font-size:12.5px;color:#78350f;margin-bottom:12px;line-height:1.6">
                            The client must add these records at their domain registrar. The domain will only work once both records are in place.
                        </p>
                        <div style="display:flex;flex-direction:column;gap:8px">
                            {{-- A Record --}}
                            <div style="background:#fff;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;background:#f59e0b;color:#fff;padding:2px 7px;border-radius:4px">A Record</span>
                                    <span style="font-size:12.5px;font-weight:500;color:#1e293b">Point domain to this server</span>
                                </div>
                                <div style="display:grid;grid-template-columns:70px 1fr;gap:4px 10px;font-size:12px">
                                    <span style="color:#64748b;font-weight:600">Name:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">@</code>
                                    <span style="color:#64748b;font-weight:600">Type:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">A</code>
                                    <span style="color:#64748b;font-weight:600">Value:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">{{ env('SERVER_IP', request()->server('SERVER_ADDR', 'YOUR_SERVER_IP')) }}</code>
                                </div>
                            </div>
                            {{-- TXT Verification Record --}}
                            <div style="background:#fff;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;background:#8b5cf6;color:#fff;padding:2px 7px;border-radius:4px">TXT Record</span>
                                    <span style="font-size:12.5px;font-weight:500;color:#1e293b">Domain ownership verification</span>
                                </div>
                                <div style="display:grid;grid-template-columns:70px 1fr;gap:4px 10px;font-size:12px">
                                    <span style="color:#64748b;font-weight:600">Name:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">_saas-verify</code>
                                    <span style="color:#64748b;font-weight:600">Type:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">TXT</code>
                                    <span style="color:#64748b;font-weight:600">Value:</span>
                                    @if($company->domain_verification_token)
                                    <code id="txt-token-val" style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a;word-break:break-all">saas-verify={{ $company->domain_verification_token }}</code>
                                    @else
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:var(--text-3)">Generated after saving</code>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:10px;font-size:11.5px;color:#92400e;line-height:1.5">
                            <strong>Note:</strong> DNS changes can take up to 48 hours to propagate. Use the "Verify Domain" button in Quick Actions once the records are live. SSL must be configured separately on your web server.
                        </div>
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
                    <div style="min-width:0">
                        <div id="preview-name" style="font-size:14px;font-weight:600">{{ $company->company_name }}</div>
                        <div id="preview-url" style="font-size:11.5px;color:var(--text-3);word-break:break-all">
                            @if($currentDomainType === 'domain' && $company->custom_domain)
                                {{ $company->custom_domain }}
                            @else
                                {{ $company->subdomain }}.{{ config('tenancy.root_domain') }}
                            @endif
                        </div>
                        @if($currentDomainType === 'domain')
                        <div id="preview-domain-badge" style="margin-top:3px">
                            <span style="font-size:10px;font-weight:600;background:#f5f3ff;color:#7c3aed;padding:1px 7px;border-radius:4px">Custom Domain</span>
                        </div>
                        @else
                        <div id="preview-domain-badge" style="display:none;margin-top:3px">
                            <span style="font-size:10px;font-weight:600;background:#f5f3ff;color:#7c3aed;padding:1px 7px;border-radius:4px">Custom Domain</span>
                        </div>
                        @endif
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

            {{-- Domain Verification Status (shown when domain_type = domain) --}}
            <div id="domain-verify-card" class="acc-card" style="margin-bottom:0;{{ $currentDomainType !== 'domain' ? 'display:none' : '' }}">
                <div style="font-size:13px;font-weight:600;margin-bottom:12px">Domain Verification</div>
                @if($company->domain_verified_at)
                <div style="display:flex;align-items:center;gap:9px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:10px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
                    <div>
                        <div style="font-size:12.5px;font-weight:600;color:#166534">Verified</div>
                        <div style="font-size:11px;color:#166534;opacity:.8">{{ $company->domain_verified_at->format('M d, Y') }}</div>
                    </div>
                </div>
                @else
                <div style="display:flex;align-items:center;gap:9px;padding:10px 14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;margin-bottom:10px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#c2410c" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <div style="font-size:12.5px;font-weight:600;color:#c2410c">Not Verified</div>
                        <div style="font-size:11px;color:#c2410c;opacity:.8">Add the TXT record then verify</div>
                    </div>
                </div>
                @if($company->domain_verification_token)
                <button type="button" id="verify-domain-btn" onclick="verifyDomain()"
                        class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-bottom:8px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Check DNS &amp; Verify
                </button>
                @endif
                @endif
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
.f-inp.is-invalid { border-color:#dc2626 !important; }
.f-sel.is-invalid { border-color:#dc2626 !important; }
.domain-type-opt {
    flex:1; display:flex; align-items:flex-start; gap:10px;
    border:2px solid var(--border); border-radius:10px;
    padding:12px 14px; cursor:pointer; transition:all .13s;
    background:#fff;
}
.domain-type-opt:hover { border-color:var(--orange); }
.domain-type-opt.selected { border-color:var(--orange); background:#fff9f6; }
</style>
@endpush

@push('scripts')
<script>
const rootDomain = '{{ config('tenancy.root_domain','yourdomain.com') }}';
const companySubdomain = '{{ $company->subdomain }}';

// ── Package selection ────────────────────────────────────────────────────
document.querySelectorAll('.pkg-option input[type=radio]').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('.pkg-option').forEach(c => c.classList.remove('selected'));
        this.closest('.pkg-option').classList.add('selected');
    });
});

// ── Domain type toggle ───────────────────────────────────────────────────
function domainTypeChanged(type) {
    document.getElementById('domain_type').value = type;
    const isSubdomain = type === 'subdomain';

    document.getElementById('subdomain-group').style.display        = isSubdomain ? '' : 'none';
    document.getElementById('custom-domain-group').style.display    = isSubdomain ? 'none' : '';
    document.getElementById('dns-instructions-group').style.display = isSubdomain ? 'none' : '';
    document.getElementById('domain-verify-card').style.display     = isSubdomain ? 'none' : '';

    document.getElementById('dt-subdomain-label').classList.toggle('selected', isSubdomain);
    document.getElementById('dt-domain-label').classList.toggle('selected', !isSubdomain);
    document.querySelectorAll('[name=_domain_type_radio]').forEach(r => { r.checked = r.value === type; });

    updatePreview();
}

// ── Preview ──────────────────────────────────────────────────────────────
function updatePreview() {
    const name     = document.getElementById('company_name').value || '{{ $company->company_name }}';
    const initials = name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) || '??';
    const isCustom = document.getElementById('domain_type').value === 'domain';

    document.getElementById('preview-av').textContent   = initials;
    document.getElementById('preview-name').textContent = name;

    const badge = document.getElementById('preview-domain-badge');
    if (isCustom) {
        const cd = document.getElementById('custom_domain').value || '{{ $company->custom_domain }}';
        document.getElementById('preview-url').textContent = cd || 'yourdomain.com';
        badge.style.display = 'block';
    } else {
        document.getElementById('preview-url').textContent = companySubdomain + '.' + rootDomain;
        badge.style.display = 'none';
    }
}

function updateStatusPreview(val) {
    const map = { active:['Active','b-green'], trial:['Trial','b-amber'], inactive:['Inactive','b-gray'], suspended:['Suspended','b-red'] };
    const [label, cls] = map[val] || ['Active','b-green'];
    const el = document.getElementById('preview-status');
    el.textContent = label; el.className = 'badge ' + cls;
}

// ── Domain verification ──────────────────────────────────────────────────
async function verifyDomain() {
    const btn = document.getElementById('verify-domain-btn');
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg> Checking DNS…';

    try {
        const r = await fetch('{{ route('superadmin.companies.verify-domain', $company) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        });
        const j = await r.json();
        showToast(j.message, j.status === 'success' ? 'success' : 'error');
        if (j.status === 'success') setTimeout(() => location.reload(), 1200);
        else { btn.disabled = false; btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Check DNS &amp; Verify'; }
    } catch {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Check DNS &amp; Verify';
    }
}

// ── Validation ───────────────────────────────────────────────────────────
function validateEditForm() {
    let valid = true;

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
    if (!companyName) {
        showErr('err-company_name', 'company_name', 'Firm Name is required.');
    } else if (companyName.length < 2 || companyName.length > 100 || !/^[a-zA-Z0-9 .&_-]+$/.test(companyName)) {
        showErr('err-company_name', 'company_name', 'Please enter a valid Firm Name.');
    }

    const companyEmail = (document.getElementById('company_email').value || '').trim();
    const emailAllowedRe = /^[a-zA-Z0-9@._-]+$/;
    const emailFormatRe  = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/;
    if (!companyEmail) {
        showErr('err-company_email', 'company_email', 'Email is required.');
    } else if (!emailAllowedRe.test(companyEmail) || !emailFormatRe.test(companyEmail)) {
        showErr('err-company_email', 'company_email', 'Please enter a valid email address.');
    }

    const companyPhone = (document.getElementById('company_phone').value || '').trim();
    if (!companyPhone) {
        showErr('err-company_phone', 'company_phone', 'Phone number is required.');
    } else if (!/^[0-9]{10}$/.test(companyPhone)) {
        showErr('err-company_phone', 'company_phone', 'Phone number must be exactly 10 digits.');
    }

    const domainType = document.getElementById('domain_type').value;
    if (domainType === 'domain') {
        const cd = (document.getElementById('custom_domain').value || '').trim();
        const domainRe = /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/;
        if (!cd) {
            showErr('err-custom_domain', 'custom_domain', 'Custom domain is required.');
        } else if (!domainRe.test(cd)) {
            showErr('err-custom_domain', 'custom_domain', 'Please enter a valid domain (e.g. app.acmecorp.com).');
        }
    }

    const status = (document.getElementById('status').value || '').trim();
    if (!status) showErr('err-status', 'status', 'Please select a status.');

    return valid;
}

// Clear errors on input
['company_name', 'company_email', 'company_phone', 'custom_domain'].forEach(function(id) {
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

// ── Form submit ──────────────────────────────────────────────────────────
document.getElementById('company-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validateEditForm()) return;
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    try {
        const r = await fetch(e.target.action, {
            method: 'POST', body: new FormData(e.target),
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        const j = await r.json();
        if (r.status === 422 && j.errors) {
            Object.entries(j.errors).forEach(([field, messages]) => {
                const errEl = document.getElementById('err-' + field);
                const inp   = document.getElementById(field);
                if (errEl) { errEl.textContent = Array.isArray(messages) ? messages[0] : messages; errEl.style.display = 'block'; }
                if (inp)   inp.classList.add('is-invalid');
            });
            btn.disabled = false;
        } else {
            showToast(j.message, j.status === 'success' ? 'success' : 'error');
            if (j.status === 'success') setTimeout(() => window.location = '{{ route('superadmin.companies.index') }}', 900);
            else btn.disabled = false;
        }
    } catch (ex) {
        showToast('An error occurred. Please try again.', 'error');
        btn.disabled = false;
    }
});
</script>
<style>
@keyframes spin { from { transform:rotate(0deg) } to { transform:rotate(360deg) } }
</style>
@endpush
