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

{{-- ── Tabs ─────────────────────────────────────────────────────────── --}}
<div style="display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:20px">
    <button type="button" id="tab-individual-btn" onclick="switchTab('individual')"
        class="add-firm-tab active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Individual Firm
    </button>
    <button type="button" id="tab-bulk-btn" onclick="switchTab('bulk')"
        class="add-firm-tab">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Bulk Upload
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     TAB 1 — INDIVIDUAL FIRM
══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-individual">
<form id="createForm" novalidate>
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
                        <div style="font-size:14px;font-weight:600">Firm Details</div>
                        <div style="font-size:12px;color:var(--text-3)">Basic information about the firm</div>
                    </div>
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Firm Name <span style="color:var(--red)">*</span></label>
                        <input name="company_name" id="company_name" class="f-inp" placeholder="Acme Corp" required
                               maxlength="100" minlength="2"
                               oninput="this.value=this.value.replace(/[^a-zA-Z0-9 .&_-]/g,'');autoFillSubdomain(this.value)">
                        <div class="field-error-msg" id="err-company_name"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Brand / App Name</label>
                        <input name="app_name" class="f-inp" placeholder="Leave blank to use firm name">
                    </div>
                    <div class="f-group">
                        <label class="f-label">Firm Email <span style="color:var(--red)">*</span></label>
                        <input name="company_email" id="company_email" type="email" class="f-inp" placeholder="billing@acme.com" required>
                        <div class="field-error-msg" id="err-company_email"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Phone <span style="color:var(--red)">*</span></label>
                        <input name="company_phone" id="company_phone" class="f-inp"
                               placeholder="10-digit phone number" required
                               maxlength="10" inputmode="numeric"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <div class="field-error-msg" id="err-company_phone"></div>
                    </div>
                </div>

                {{-- ── Access Method (Domain Type) ───────────────────────── --}}
                <div style="margin-bottom:14px">
                    <label class="f-label" style="margin-bottom:8px;display:block">
                        Access Method <span style="color:var(--red)">*</span>
                    </label>
                    <input type="hidden" name="domain_type" id="domain_type" value="subdomain">
                    <div style="display:flex;gap:10px">
                        <label class="domain-type-opt selected" id="dt-subdomain-label" onclick="domainTypeChanged('subdomain')" style="cursor:pointer">
                            <input type="radio" name="_domain_type_radio" value="subdomain" checked style="accent-color:var(--orange);flex-shrink:0">
                            <div>
                                <div style="font-size:13px;font-weight:600;margin-bottom:2px">Subdomain</div>
                                <div style="font-size:11.5px;color:var(--text-3)">acme.{{ config('tenancy.root_domain','yourdomain.com') }}</div>
                            </div>
                        </label>
                        <label class="domain-type-opt" id="dt-domain-label" onclick="domainTypeChanged('domain')" style="cursor:pointer">
                            <input type="radio" name="_domain_type_radio" value="domain" style="accent-color:var(--orange);flex-shrink:0">
                            <div>
                                <div style="font-size:13px;font-weight:600;margin-bottom:2px">Custom Domain</div>
                                <div style="font-size:11.5px;color:var(--text-3)">app.acmecorp.com</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-2col">
                    {{-- Subdomain field (shown when domain_type = subdomain) --}}
                    <div class="f-group" id="subdomain-group">
                        <label class="f-label">Subdomain <span style="color:var(--red)">*</span></label>
                        <div style="display:flex;align-items:stretch;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:#fff">
                            <input name="subdomain" id="subdomain" class="f-inp"
                                   placeholder="acme"
                                   maxlength="50"
                                   style="border:none;border-radius:0;flex:1;outline:none"
                                   oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'');this.dataset.manual='1';updatePreview()">
                            <span style="padding:0 12px;display:flex;align-items:center;background:var(--bg);border-left:1.5px solid var(--border);font-size:12px;color:var(--text-3);white-space:nowrap;flex-shrink:0">
                                .{{ config('tenancy.root_domain','yourdomain.com') }}
                            </span>
                        </div>
                        <div class="field-error-msg" id="err-subdomain"></div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Letters, numbers and hyphens only. Cannot be changed later.</div>
                    </div>

                    {{-- Custom domain field (shown when domain_type = domain) --}}
                    <div class="f-group" id="custom-domain-group" style="display:none">
                        <label class="f-label">Custom Domain <span style="color:var(--red)">*</span></label>
                        <input name="custom_domain" id="custom_domain" class="f-inp"
                               placeholder="app.acmecorp.com"
                               maxlength="253"
                               oninput="this.value=this.value.toLowerCase().replace(/\s/g,'');updatePreview()">
                        <div class="field-error-msg" id="err-custom_domain"></div>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:4px">Enter the exact domain visitors will use (no https).</div>
                    </div>

                    <div class="f-group">
                        <label class="f-label">Status <span style="color:var(--red)">*</span></label>
                        <select name="status" id="status" class="f-sel">
                            <option value="">-- Select Status --</option>
                            <option value="active">Active</option>
                            <option value="trial">Trial</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <div class="field-error-msg" id="err-status"></div>
                    </div>
                </div>

                {{-- DNS Setup Instructions (shown when domain_type = domain) --}}
                <div id="dns-instructions-group" style="display:none;margin-bottom:4px">
                    <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:16px 18px">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span style="font-size:13px;font-weight:600;color:#92400e">DNS Configuration Required</span>
                        </div>
                        <p style="font-size:12.5px;color:#78350f;margin-bottom:12px;line-height:1.6">
                            After creating the firm, the client must add these DNS records at their domain registrar to point their domain to this server. The domain will only work once both records are in place.
                        </p>
                        <div style="display:flex;flex-direction:column;gap:8px">
                            {{-- A Record --}}
                            <div style="background:#fff;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap">
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <span style="font-size:10px;font-weight:700;text-transform:uppercase;background:#f59e0b;color:#fff;padding:2px 7px;border-radius:4px">A Record</span>
                                        <span style="font-size:12.5px;font-weight:500;color:#1e293b">Point domain to this server</span>
                                    </div>
                                </div>
                                <div style="margin-top:8px;display:grid;grid-template-columns:70px 1fr;gap:4px 10px;font-size:12px">
                                    <span style="color:#64748b;font-weight:600">Name:</span>
                                    <code id="dns-a-name" style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">@</code>
                                    <span style="color:#64748b;font-weight:600">Type:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">A</code>
                                    <span style="color:#64748b;font-weight:600">Value:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">{{ env('SERVER_IP', request()->server('SERVER_ADDR', 'YOUR_SERVER_IP')) }}</code>
                                </div>
                            </div>
                            {{-- TXT Verification Record --}}
                            <div style="background:#fff;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;background:#8b5cf6;color:#fff;padding:2px 7px;border-radius:4px">TXT Record</span>
                                    <span style="font-size:12.5px;font-weight:500;color:#1e293b">Domain ownership verification</span>
                                </div>
                                <div style="margin-top:8px;display:grid;grid-template-columns:70px 1fr;gap:4px 10px;font-size:12px">
                                    <span style="color:#64748b;font-weight:600">Name:</span>
                                    <code id="dns-txt-name" style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">_saas-verify</code>
                                    <span style="color:#64748b;font-weight:600">Type:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">TXT</code>
                                    <span style="color:#64748b;font-weight:600">Value:</span>
                                    <code style="background:#f8fafc;padding:2px 7px;border-radius:4px;font-size:12px;color:#0f172a">Generated after firm is created</code>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:10px;font-size:11.5px;color:#92400e;line-height:1.5">
                            <strong>Note:</strong> DNS changes can take up to 48 hours to propagate. You can trigger domain verification from the firm's detail page once the records are live. SSL/HTTPS must be configured separately on your web server (e.g. Let's Encrypt via Certbot).
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
                        <div style="font-size:12px;color:var(--text-3)">Select a subscription plan for this firm</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px" id="pkg-grid">
                    <label class="pkg-option {{ !old('package_id') ? 'selected' : '' }}">
                        <input type="radio" name="package_id" value="" data-price="0" {{ !old('package_id') ? 'checked' : '' }}>
                        <div class="pkg-option-body">
                            <div style="font-size:13px;font-weight:600;margin-bottom:2px">No Plan</div>
                            <div style="font-size:11.5px;color:var(--text-3)">Assign later</div>
                        </div>
                    </label>
                    @foreach($packages as $pkg)
                    <label class="pkg-option {{ old('package_id') == $pkg->id ? 'selected' : '' }}" id="pkg-{{ $pkg->id }}">
                        <input type="radio" name="package_id" value="{{ $pkg->id }}" data-price="{{ $pkg->monthly_price }}" data-annual="{{ $pkg->annual_price ?? 0 }}" {{ old('package_id') == $pkg->id ? 'checked' : '' }}>
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

                {{-- Billing cycle (shown only when a paid plan is selected) --}}
                <div id="billing-cycle-wrap" style="display:none;margin-top:14px;padding:14px;background:var(--bg);border-radius:10px;border:1.5px solid var(--border)">
                    <div style="font-size:12.5px;font-weight:600;color:var(--text-2);margin-bottom:10px">Billing Cycle</div>
                    <div style="display:flex;gap:10px">
                        <label style="flex:1;display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;background:#fff;transition:border-color .13s" id="bc-monthly-label">
                            <input type="radio" name="billing_cycle" value="monthly" checked style="accent-color:var(--orange)" onchange="bcChanged()">
                            <div>
                                <div style="font-size:13px;font-weight:500">Monthly</div>
                                <div style="font-size:11px;color:var(--text-3)">Billed each month</div>
                            </div>
                        </label>
                        <label style="flex:1;display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;background:#fff;transition:border-color .13s" id="bc-annual-label">
                            <input type="radio" name="billing_cycle" value="annual" style="accent-color:var(--orange)" onchange="bcChanged()">
                            <div>
                                <div style="font-size:13px;font-weight:500">Annual <span style="font-size:10.5px;color:var(--green);font-weight:600">Save ~17%</span></div>
                                <div style="font-size:11px;color:var(--text-3)">Billed once per year</div>
                            </div>
                        </label>
                    </div>
                    <div style="margin-top:10px;font-size:12px;color:var(--text-3);padding:8px 10px;background:var(--blue3);border-radius:6px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" width="13" height="13" style="vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        After creating the firm, you'll be redirected to Stripe to complete payment.
                    </div>
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
                        <input name="admin_name" id="admin_name" class="f-inp" placeholder="John Smith" required autocomplete="off">
                        <div class="field-error-msg" id="err-admin_name"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Email Address <span style="color:var(--red)">*</span></label>
                        <input name="admin_email" id="admin_email" type="email" class="f-inp" placeholder="john@acme.com" required autocomplete="off">
                        <div class="field-error-msg" id="err-admin_email"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Password <span style="color:var(--red)">*</span></label>
                        <div style="position:relative">
                            <input name="admin_password" id="admin_password" type="password" class="f-inp"
                                   placeholder="Min. 8 characters" required minlength="8" style="padding-right:40px" autocomplete="new-password">
                            <button type="button" onclick="togglePw()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-3);cursor:pointer;padding:4px">
                                <svg id="pw-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="field-error-msg" id="err-admin_password"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Confirm Password <span style="color:var(--red)">*</span></label>
                        <input name="admin_password_confirmation" id="admin_pw_confirm" type="password" class="f-inp"
                               placeholder="Re-enter password" required minlength="8" autocomplete="new-password">
                        <div id="pw-match-msg" style="font-size:11.5px;margin-top:4px;display:none"></div>
                        <div class="field-error-msg" id="err-admin_password_confirmation"></div>
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
                    <div style="min-width:0">
                        <div id="preview-name" style="font-size:14px;font-weight:600;color:var(--text-1)">Firm Name</div>
                        <div id="preview-sub" style="font-size:11.5px;color:var(--text-3);word-break:break-all">subdomain.{{ config('tenancy.root_domain','yourdomain.com') }}</div>
                        <div id="preview-domain-badge" style="display:none;margin-top:3px">
                            <span style="font-size:10px;font-weight:600;background:#f5f3ff;color:#7c3aed;padding:1px 7px;border-radius:4px">Custom Domain</span>
                        </div>
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
                <div style="display:flex;flex-direction:column;gap:10px" id="tips-subdomain">
                    @foreach([
                        ['var(--blue)',   'Subdomain is permanent — choose carefully.'],
                        ['var(--green)',  'A trial plan lets the firm explore before paying.'],
                        ['var(--orange)', 'The admin email is used for all notifications.'],
                        ['var(--purple)', 'You can always edit details after creation.'],
                    ] as [$color, $tip])
                    <div style="display:flex;align-items:flex-start;gap:9px">
                        <div style="width:7px;height:7px;border-radius:50%;background:{{ $color }};flex-shrink:0;margin-top:5px"></div>
                        <div style="font-size:12.5px;color:var(--text-2);line-height:1.5">{{ $tip }}</div>
                    </div>
                    @endforeach
                </div>
                <div style="display:none;flex-direction:column;gap:10px" id="tips-domain">
                    @foreach([
                        ['var(--purple)', 'Custom domain must be owned by the firm.'],
                        ['var(--orange)', 'Add the A and TXT records shown in the form.'],
                        ['var(--blue)',   'Domain verification can be triggered after creation.'],
                        ['var(--green)',  'SSL must be configured on the server (Let\'s Encrypt).'],
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
                <div style="display:flex;flex-direction:column;gap:9px" id="next-subdomain">
                    @foreach(['Firm dashboard is created','Admin user receives login email','Subdomain goes live immediately','Billing cycle starts after trial'] as $i => $step)
                    <div style="display:flex;align-items:flex-start;gap:9px">
                        <div style="width:20px;height:20px;border-radius:50%;background:rgba(255,255,255,.15);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $i+1 }}</div>
                        <div style="font-size:12px;color:rgba(255,255,255,.8);line-height:1.5;margin-top:2px">{{ $step }}</div>
                    </div>
                    @endforeach
                </div>
                <div style="display:none;flex-direction:column;gap:9px" id="next-domain">
                    @foreach(['Firm record created in the system','Admin user receives login email','Client adds DNS A + TXT records','You verify ownership from the firm detail page','Custom domain goes live after DNS propagates'] as $i => $step)
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
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     TAB 2 — BULK UPLOAD
══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-bulk" style="display:none">
    <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

        {{-- LEFT --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            {{-- Upload card --}}
            <div class="acc-card" style="margin-bottom:0">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border)">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--blue3);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="1.8" width="16" height="16"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600">Upload CSV File</div>
                        <div style="font-size:12px;color:var(--text-3)">Import multiple firms at once from a CSV file</div>
                    </div>
                </div>

                {{-- Drop zone --}}
                <div id="drop-zone"
                     style="border:2px dashed var(--border);border-radius:12px;padding:48px 24px;text-align:center;cursor:pointer;transition:all .15s;background:var(--bg)"
                     onclick="document.getElementById('csv-file-input').click()"
                     ondragover="bulkDragOver(event)"
                     ondragleave="bulkDragLeave(event)"
                     ondrop="bulkDrop(event)">
                    <div style="margin-bottom:14px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.5" width="40" height="40"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                    </div>
                    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:6px">Drag &amp; drop your CSV file here</div>
                    <div style="font-size:13px;color:var(--text-3)">or <span style="color:var(--orange);font-weight:500;text-decoration:underline;cursor:pointer">click to browse</span> &nbsp;·&nbsp; .csv only &nbsp;·&nbsp; max 5 MB</div>
                    <input type="file" id="csv-file-input" accept=".csv,text/csv" style="display:none" onchange="bulkFileSelected(this)">
                </div>

                {{-- File selected indicator --}}
                <div id="bulk-file-info" style="display:none;margin-top:12px;padding:12px 14px;background:var(--blue3);border-radius:8px;display:none;align-items:center;gap:10px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <div style="flex:1;min-width:0">
                        <div id="bulk-file-name" style="font-size:13px;font-weight:600;color:var(--blue);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"></div>
                        <div id="bulk-file-size" style="font-size:11.5px;color:var(--text-3)"></div>
                    </div>
                    <button type="button" onclick="bulkClearFile()" style="background:none;border:none;color:var(--text-3);cursor:pointer;padding:4px;flex-shrink:0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Validation errors --}}
                <div id="bulk-file-error" style="display:none;margin-top:10px;padding:10px 14px;background:#fff5f5;border:1px solid #fecaca;border-radius:8px;font-size:12.5px;color:var(--red)"></div>

                {{-- Required columns --}}
                <div style="margin-top:20px">
                    <div style="font-size:12.5px;font-weight:600;color:var(--text-2);margin-bottom:10px">Required CSV columns</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        @foreach(['firm_name','contact_name','email','phone','plan','subdomain'] as $col)
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:var(--bg);border:1.5px solid var(--border);border-radius:6px;font-size:12px;font-weight:500;color:var(--text-2);font-family:monospace">{{ $col }}</span>
                        @endforeach
                    </div>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:8px">
                        <span style="color:var(--red)">*</span> firm_name, contact_name, email, subdomain are required per row. phone and plan can be left blank.
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex;align-items:center;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                    <button type="button" onclick="downloadSampleCsv()" class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.11"/></svg>
                        Download Sample CSV
                    </button>
                    <div style="flex:1"></div>
                    <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="button" id="bulk-submit-btn" onclick="bulkSubmit()" class="btn btn-primary" style="min-width:150px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                        Upload &amp; Process
                    </button>
                </div>
            </div>

            {{-- Results panel (shown after processing) --}}
            <div id="bulk-results" style="display:none">
                <div class="acc-card" style="margin-bottom:0">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--border)">
                        <div style="font-size:14px;font-weight:600">Upload Results</div>
                        <div style="display:flex;gap:8px">
                            <span id="res-success-badge" class="badge b-green" style="font-size:12px"></span>
                            <span id="res-error-badge" class="badge b-red" style="font-size:12px;display:none"></span>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div id="res-summary" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">
                        <div style="padding:14px;background:var(--bg);border-radius:8px;text-align:center">
                            <div id="res-total" style="font-size:22px;font-weight:700;color:var(--text-1)">0</div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">Total Rows</div>
                        </div>
                        <div style="padding:14px;background:#f0fdf4;border-radius:8px;text-align:center">
                            <div id="res-ok" style="font-size:22px;font-weight:700;color:var(--green)">0</div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">Created</div>
                        </div>
                        <div style="padding:14px;background:#fff5f5;border-radius:8px;text-align:center">
                            <div id="res-fail" style="font-size:22px;font-weight:700;color:var(--red)">0</div>
                            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">Failed</div>
                        </div>
                    </div>

                    {{-- Error rows table --}}
                    <div id="res-errors-wrap" style="display:none">
                        <div style="font-size:12.5px;font-weight:600;color:var(--text-2);margin-bottom:8px">Failed Rows</div>
                        <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden">
                            <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                                <thead>
                                    <tr style="background:var(--bg)">
                                        <th style="padding:8px 12px;text-align:left;font-weight:600;color:var(--text-2);border-bottom:1px solid var(--border)">Row</th>
                                        <th style="padding:8px 12px;text-align:left;font-weight:600;color:var(--text-2);border-bottom:1px solid var(--border)">Firm</th>
                                        <th style="padding:8px 12px;text-align:left;font-weight:600;color:var(--text-2);border-bottom:1px solid var(--border)">Errors</th>
                                    </tr>
                                </thead>
                                <tbody id="res-errors-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div style="margin-top:14px;display:flex;justify-content:flex-end;gap:10px">
                        <button type="button" onclick="bulkReset()" class="btn btn-ghost btn-sm">Upload Another</button>
                        <a href="{{ route('superadmin.companies.index') }}" class="btn btn-primary btn-sm">View All Firms</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Tips --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            <div class="acc-card" style="margin-bottom:0">
                <div style="font-size:13px;font-weight:600;margin-bottom:12px">CSV Format Guide</div>
                <div style="display:flex;flex-direction:column;gap:10px">
                    @foreach([
                        ['var(--blue)',   'firm_name — Full firm name (required)'],
                        ['var(--purple)', 'contact_name — Admin user\'s full name (required)'],
                        ['var(--green)',  'email — Admin user\'s email address (required)'],
                        ['var(--orange)', 'phone — Company phone number (optional)'],
                        ['var(--teal,var(--blue))', 'plan — Exact plan name e.g. "Starter" (optional)'],
                        ['var(--red)',    'subdomain — URL slug, e.g. acme (required)'],
                    ] as [$color, $tip])
                    <div style="display:flex;align-items:flex-start;gap:9px">
                        <div style="width:7px;height:7px;border-radius:50%;background:{{ $color }};flex-shrink:0;margin-top:5px"></div>
                        <div style="font-size:12.5px;color:var(--text-2);line-height:1.5">{{ $tip }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="acc-card" style="margin-bottom:0">
                <div style="font-size:13px;font-weight:600;margin-bottom:10px">Validation Rules</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @foreach([
                        'Duplicate subdomains in the file will be skipped',
                        'Email must be a valid format',
                        'Subdomain: letters, numbers, hyphens only',
                        'Plan name must match an existing active plan',
                        'Each row creates one firm + one admin user',
                        'A random password is set — user should reset it',
                    ] as $rule)
                    <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:var(--text-2)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5" width="13" height="13" style="flex-shrink:0;margin-top:2px"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ $rule }}
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="acc-card" style="margin-bottom:0;background:linear-gradient(135deg,#0a2540 0%,var(--blue) 100%);border:none">
                <div style="font-size:13px;font-weight:600;color:#fff;margin-bottom:10px">Bulk Upload Notes</div>
                <div style="display:flex;flex-direction:column;gap:9px">
                    @foreach(['Max 5 MB file size','Valid rows are created even if others fail','Results show per-row error details','Download sample CSV to see correct format'] as $i => $step)
                    <div style="display:flex;align-items:flex-start;gap:9px">
                        <div style="width:20px;height:20px;border-radius:50%;background:rgba(255,255,255,.15);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $i+1 }}</div>
                        <div style="font-size:12px;color:rgba(255,255,255,.8);line-height:1.5;margin-top:2px">{{ $step }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

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

.add-firm-tab {
    display:inline-flex;align-items:center;gap:7px;
    padding:10px 18px;font-size:13.5px;font-weight:500;
    border:none;border-bottom:3px solid transparent;
    background:none;cursor:pointer;color:var(--text-3);
    margin-bottom:-2px;transition:all .13s;
}
.add-firm-tab:hover { color:var(--text-1); }
.add-firm-tab.active { color:var(--orange);border-bottom-color:var(--orange);font-weight:600; }

#drop-zone.drag-over {
    border-color:var(--orange);background:#fff9f6;
}
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
// ── Tab switching ────────────────────────────────────────────────────────
function switchTab(tab) {
    document.getElementById('tab-individual').style.display = tab === 'individual' ? '' : 'none';
    document.getElementById('tab-bulk').style.display       = tab === 'bulk'       ? '' : 'none';
    document.getElementById('tab-individual-btn').classList.toggle('active', tab === 'individual');
    document.getElementById('tab-bulk-btn').classList.toggle('active', tab === 'bulk');
}

// ── Domain type toggle ───────────────────────────────────────────────────
function domainTypeChanged(type) {
    document.getElementById('domain_type').value = type;
    const isSubdomain = type === 'subdomain';

    // Toggle fields
    document.getElementById('subdomain-group').style.display        = isSubdomain ? '' : 'none';
    document.getElementById('custom-domain-group').style.display    = isSubdomain ? 'none' : '';
    document.getElementById('dns-instructions-group').style.display = isSubdomain ? 'none' : '';

    // Toggle sidebar tips & next steps
    document.getElementById('tips-subdomain').style.display = isSubdomain ? 'flex' : 'none';
    document.getElementById('tips-domain').style.display    = isSubdomain ? 'none' : 'flex';
    document.getElementById('next-subdomain').style.display = isSubdomain ? 'flex' : 'none';
    document.getElementById('next-domain').style.display    = isSubdomain ? 'none' : 'flex';

    // Style option cards
    document.getElementById('dt-subdomain-label').classList.toggle('selected', isSubdomain);
    document.getElementById('dt-domain-label').classList.toggle('selected', !isSubdomain);

    // Sync radio buttons
    document.querySelectorAll('[name=_domain_type_radio]').forEach(r => { r.checked = r.value === type; });

    updatePreview();
}

// ── Individual form ──────────────────────────────────────────────────────
function autoFillSubdomain(val) {
    const sub = document.getElementById('subdomain');
    if (!sub.dataset.manual) {
        sub.value = val.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
    }
    updatePreview();
}

function bcChanged() {
    // Visual highlight for selected billing cycle label
    ['monthly','annual'].forEach(function(v) {
        const lbl = document.getElementById('bc-' + v + '-label');
        const inp = lbl?.querySelector('input');
        if (lbl) lbl.style.borderColor = inp?.checked ? 'var(--orange)' : 'var(--border)';
    });
}

document.querySelectorAll('.pkg-option input[type=radio]').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('.pkg-option').forEach(c => c.classList.remove('selected'));
        this.closest('.pkg-option').classList.add('selected');
        const label = this.closest('.pkg-option').querySelector('[style*="font-weight:600"]');
        document.getElementById('preview-plan').textContent = label ? label.textContent : 'No Plan';

        // Show/hide billing cycle based on whether plan is paid
        const price = parseFloat(this.dataset.price || 0);
        const isPaid = this.value && price > 0;
        document.getElementById('billing-cycle-wrap').style.display = isPaid ? '' : 'none';
    });
});

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

function updatePreview() {
    const name     = document.getElementById('company_name').value || 'Company Name';
    const initials = name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) || '??';
    const colors   = ['#0C84BC','#10b981','#F06B21','#7c3aed','#0d9488','#e11d48'];
    const color    = colors[name.charCodeAt(0) % colors.length] || '#0C84BC';
    const isCustom = document.getElementById('domain_type').value === 'domain';

    document.getElementById('preview-av').textContent       = initials;
    document.getElementById('preview-av').style.background  = color;
    document.getElementById('preview-name').textContent     = name;

    if (isCustom) {
        const cd = document.getElementById('custom_domain').value || 'yourdomain.com';
        document.getElementById('preview-sub').textContent              = cd;
        document.getElementById('preview-domain-badge').style.display  = 'block';
    } else {
        const sub = document.getElementById('subdomain').value || 'subdomain';
        document.getElementById('preview-sub').textContent              = sub + '.{{ config('tenancy.root_domain','yourdomain.com') }}';
        document.getElementById('preview-domain-badge').style.display  = 'none';
    }
}

function togglePw() {
    const inp = document.getElementById('admin_password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

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

function validateCreateForm() {
    let valid = true;
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function showErr(errId, inputId, msg) {
        const errEl = document.getElementById(errId);
        if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
        const inp = document.getElementById(inputId);
        if (inp) inp.classList.add('is-invalid');
        valid = false;
    }

    // Clear previous errors
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
    } else if (companyEmail.length < 5 || companyEmail.length > 100 || !emailAllowedRe.test(companyEmail) || !emailFormatRe.test(companyEmail)) {
        showErr('err-company_email', 'company_email', 'Please enter a valid email address.');
    }

    const companyPhone = (document.getElementById('company_phone').value || '').trim();
    if (!companyPhone) {
        showErr('err-company_phone', 'company_phone', 'Phone number is required.');
    } else if (!/^[0-9]+$/.test(companyPhone)) {
        showErr('err-company_phone', 'company_phone', 'Please enter a valid phone number.');
    } else if (companyPhone.length !== 10) {
        showErr('err-company_phone', 'company_phone', 'Phone number must be 10 digits.');
    }

    const domainType = document.getElementById('domain_type').value;
    if (domainType === 'subdomain') {
        const subdomain = (document.getElementById('subdomain').value || '').trim();
        if (!subdomain) {
            showErr('err-subdomain', 'subdomain', 'Subdomain is required.');
        } else if (subdomain.length < 3 || subdomain.length > 50 || !/^[a-z0-9-]+$/.test(subdomain)) {
            showErr('err-subdomain', 'subdomain', 'Only lowercase letters, numbers, and hyphens are allowed.');
        }
    } else {
        const customDomain = (document.getElementById('custom_domain').value || '').trim();
        const domainRe = /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/;
        if (!customDomain) {
            showErr('err-custom_domain', 'custom_domain', 'Custom domain is required.');
        } else if (!domainRe.test(customDomain)) {
            showErr('err-custom_domain', 'custom_domain', 'Please enter a valid domain (e.g. app.acmecorp.com).');
        }
    }

    const status = (document.getElementById('status').value || '').trim();
    if (!status) showErr('err-status', 'status', 'Please select a status.');

    const adminName = (document.getElementById('admin_name').value || '').trim();
    if (!adminName) showErr('err-admin_name', 'admin_name', 'Admin full name is required.');

    const adminEmail = (document.getElementById('admin_email').value || '').trim();
    if (!adminEmail) showErr('err-admin_email', 'admin_email', 'Admin email address is required.');
    else if (!emailRe.test(adminEmail)) showErr('err-admin_email', 'admin_email', 'Please enter a valid email address.');

    const pw  = (document.getElementById('admin_password').value || '');
    if (!pw) showErr('err-admin_password', 'admin_password', 'Password is required.');
    else if (pw.length < 8) showErr('err-admin_password', 'admin_password', 'Password must be at least 8 characters.');

    const pwc = (document.getElementById('admin_pw_confirm').value || '');
    if (!pwc) showErr('err-admin_password_confirmation', 'admin_pw_confirm', 'Please confirm your password.');
    else if (pw && pwc !== pw) showErr('err-admin_password_confirmation', 'admin_pw_confirm', 'Passwords do not match.');

    return valid;
}

// Clear error on input
['company_name','company_email','company_phone','subdomain','custom_domain','admin_name','admin_email','admin_password','admin_pw_confirm'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        const errMap = { admin_pw_confirm: 'err-admin_password_confirmation', custom_domain: 'err-custom_domain' };
        const errId = errMap[id] || ('err-' + id);
        const errEl = document.getElementById(errId);
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
    });
});
document.getElementById('status').addEventListener('change', function() {
    this.classList.remove('is-invalid');
    const errEl = document.getElementById('err-status');
    if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
});

document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!validateCreateForm()) return;

    const pw  = document.getElementById('admin_password').value;
    const pwc = document.getElementById('admin_pw_confirm').value;
    if (pw !== pwc) { showToast('Passwords do not match', 'error'); return; }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg> Creating…';

    const body = Object.fromEntries(new FormData(this).entries());
    const res  = await apiCall('{{ route("superadmin.companies.store") }}', 'POST', body);

    if (res.status === 'success') {
        if (res.checkout_url) {
            // Paid plan selected — redirect to Stripe checkout
            showToast('Firm created! Redirecting to payment…');
            setTimeout(() => window.location.href = res.checkout_url, 900);
        } else {
            showToast('Firm created successfully!');
            setTimeout(() => window.location = res.redirect, 900);
        }
    } else {
        if (res.errors) {
            Object.entries(res.errors).forEach(([field, messages]) => {
                const errEl = document.getElementById('err-' + field);
                const inp   = document.getElementById(field);
                if (errEl) { errEl.textContent = Array.isArray(messages) ? messages[0] : messages; errEl.style.display = 'block'; }
                if (inp)   inp.classList.add('is-invalid');
            });
        } else {
            showToast(res.message || 'Error creating firm', 'error');
        }
        btn.disabled = false;
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg> Create Firm';
    }
});

// ── Bulk upload ──────────────────────────────────────────────────────────
let bulkFile = null;

function bulkDragOver(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.add('drag-over');
}
function bulkDragLeave(e) {
    document.getElementById('drop-zone').classList.remove('drag-over');
}
function bulkDrop(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.remove('drag-over');
    const f = e.dataTransfer.files[0];
    if (f) setBulkFile(f);
}
function bulkFileSelected(input) {
    if (input.files[0]) setBulkFile(input.files[0]);
}

function setBulkFile(f) {
    const errEl = document.getElementById('bulk-file-error');
    errEl.style.display = 'none';

    if (!f.name.toLowerCase().endsWith('.csv') && f.type !== 'text/csv') {
        errEl.textContent = 'Only .csv files are accepted.';
        errEl.style.display = 'block';
        return;
    }
    if (f.size > 5 * 1024 * 1024) {
        errEl.textContent = 'File size exceeds 5 MB limit.';
        errEl.style.display = 'block';
        return;
    }

    bulkFile = f;
    document.getElementById('bulk-file-name').textContent = f.name;
    document.getElementById('bulk-file-size').textContent = (f.size / 1024).toFixed(1) + ' KB';
    const info = document.getElementById('bulk-file-info');
    info.style.display = 'flex';

    // Client-side header validation
    const reader = new FileReader();
    reader.onload = function(ev) {
        const firstLine = ev.target.result.split('\n')[0];
        const cols = firstLine.split(',').map(c => c.trim().toLowerCase().replace(/^"|"$/g,''));
        const required = ['firm_name','contact_name','email','subdomain'];
        const missing = required.filter(r => !cols.includes(r));
        if (missing.length) {
            errEl.textContent = 'Missing required columns: ' + missing.join(', ');
            errEl.style.display = 'block';
            bulkFile = null;
            info.style.display = 'none';
        }
    };
    reader.readAsText(f);
}

function bulkClearFile() {
    bulkFile = null;
    document.getElementById('csv-file-input').value = '';
    document.getElementById('bulk-file-info').style.display = 'none';
    document.getElementById('bulk-file-error').style.display = 'none';
}

function bulkReset() {
    bulkClearFile();
    document.getElementById('bulk-results').style.display = 'none';
}

async function bulkSubmit() {
    const errEl = document.getElementById('bulk-file-error');
    errEl.style.display = 'none';

    if (!bulkFile) {
        errEl.textContent = 'Please select a CSV file first.';
        errEl.style.display = 'block';
        return;
    }

    const btn = document.getElementById('bulk-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg> Processing…';

    const fd = new FormData();
    fd.append('csv_file', bulkFile);
    fd.append('_token', document.querySelector('input[name=_token]') ? document.querySelector('input[name=_token]').value : '{{ csrf_token() }}');

    try {
        const resp = await fetch('{{ route("superadmin.companies.bulk-store") }}', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        const res = await resp.json();

        if (res.status === 'success') {
            showResults(res);
        } else {
            errEl.textContent = res.message || 'Upload failed. Please try again.';
            errEl.style.display = 'block';
        }
    } catch(ex) {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
    }

    btn.disabled = false;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg> Upload &amp; Process';
}

function showResults(res) {
    const results  = res.results || {};
    const ok       = results.success || 0;
    const errors   = results.errors  || [];
    const total    = ok + errors.length;

    document.getElementById('res-total').textContent = total;
    document.getElementById('res-ok').textContent    = ok;
    document.getElementById('res-fail').textContent  = errors.length;

    document.getElementById('res-success-badge').textContent = ok + ' created';
    const errBadge = document.getElementById('res-error-badge');
    if (errors.length) {
        errBadge.textContent = errors.length + ' failed';
        errBadge.style.display = '';
    } else {
        errBadge.style.display = 'none';
    }

    const errWrap = document.getElementById('res-errors-wrap');
    if (errors.length) {
        const tbody = document.getElementById('res-errors-body');
        tbody.innerHTML = errors.map(e =>
            `<tr style="border-bottom:1px solid var(--border)">
                <td style="padding:8px 12px;color:var(--text-3)">${e.row}</td>
                <td style="padding:8px 12px;font-weight:500">${escHtml(e.firm)}</td>
                <td style="padding:8px 12px;color:var(--red)">${e.errors.map(escHtml).join('<br>')}</td>
            </tr>`
        ).join('');
        errWrap.style.display = '';
    } else {
        errWrap.style.display = 'none';
    }

    document.getElementById('bulk-results').style.display = '';
    if (ok > 0) showToast(ok + ' firm(s) created successfully!');
    if (errors.length) showToast(errors.length + ' row(s) failed. See details below.', 'error');
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function downloadSampleCsv() {
    const rows = [
        ['firm_name','contact_name','email','phone','plan','subdomain'],
        ['Acme Corp','John Smith','john@acme.com','+1 555 0100','Starter','acme'],
        ['Beta LLC','Jane Doe','jane@beta.io','+1 555 0200','','beta-llc'],
    ];
    const csv = rows.map(r => r.map(c => '"'+c+'"').join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'sample_firms.csv';
    a.click();
}
</script>
<style>
@keyframes spin { from { transform:rotate(0deg) } to { transform:rotate(360deg) } }
</style>
@endpush
