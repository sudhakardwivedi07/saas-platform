@extends('layouts.app')
@section('title', 'Edit Company')
@section('page-title', 'Edit Company')

@push('styles')
<style>
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
.pkg-card {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 14px 16px;
    transition: border-color .13s, background .13s;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.pkg-card.selected {
    border-color: var(--blue);
    background: rgba(26,106,168,.05);
}
.pkg-card input[type=radio] { margin-top: 3px; flex-shrink: 0; accent-color: var(--blue); }
</style>
@endpush

@section('content')
<div style="margin-bottom:18px">
    <a href="{{ route('my-companies.index') }}" style="display:inline-flex;align-items:center;gap:5px;font-size:13px;color:var(--text-2);text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
        Back to My Companies
    </a>
</div>

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

<form method="POST" action="{{ route('my-companies.update', $company->id) }}">
@csrf
@php $isClientUserFlow = $isClientUserFlow ?? auth()->user()->hasRole('user'); @endphp
@php $adminUser = $companyAdminUser ?? $company->adminUser; @endphp
@php
    $industryOptions = ['Technology','Healthcare','Finance','Manufacturing','Retail','Real Estate','Construction','Transportation','Energy','Agriculture','Education','Consulting','Legal Services','Entertainment','Automotive','Other'];
    $entityOptions = ['LLC','C-Corporation','S-Corporation','Sole Proprietorship','Partnership','LLP','Non-Profit','Trust','Other'];
    $selectedIndustry = (string) old('industry_type', $company->industry_type);
    $selectedEntity = (string) old('entity_type', $company->entity_type);
    $hasIndustryInOptions = collect($industryOptions)->contains(fn ($opt) => strcasecmp($selectedIndustry, $opt) === 0);
    $hasEntityInOptions = collect($entityOptions)->contains(fn ($opt) => strcasecmp($selectedEntity, $opt) === 0);
@endphp

<div class="create-co-layout">
    <div>
        <div class="acc-card" style="margin-bottom:16px">
            <div style="display:flex;align-items:center;margin-bottom:18px">
                <span class="step-num">1</span>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">Company Information</div>
            </div>

            <div class="mf-field">
                <label class="mf-label">Company Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="company_name" id="company_name" class="mf-inp" required value="{{ old('company_name', $company->company_name) }}">
            </div>

            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Industry Type</label>
                    <select name="industry_type" class="mf-sel" style="height:38px">
                        <option value="">Select industry</option>
                        @if($selectedIndustry !== '' && !$hasIndustryInOptions)
                        <option value="{{ $selectedIndustry }}" selected>{{ $selectedIndustry }}</option>
                        @endif
                        @foreach($industryOptions as $ind)
                        <option value="{{ $ind }}" {{ strcasecmp($selectedIndustry, (string) $ind) === 0 ? 'selected' : '' }}>{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Entity Type</label>
                    <select name="entity_type" class="mf-sel" style="height:38px">
                        <option value="">Select entity type</option>
                        @if($selectedEntity !== '' && !$hasEntityInOptions)
                        <option value="{{ $selectedEntity }}" selected>{{ $selectedEntity }}</option>
                        @endif
                        @foreach($entityOptions as $ent)
                        <option value="{{ $ent }}" {{ strcasecmp($selectedEntity, (string) $ent) === 0 ? 'selected' : '' }}>{{ $ent }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mf-field">
                <label class="mf-label">State / Jurisdiction</label>
                <input type="text" name="state" class="mf-inp" value="{{ old('state', $company->state) }}">
            </div>

            <div class="mf-field">
                <label class="mf-label">Subdomain</label>
                <div class="subdomain-wrap">
                    <input type="text" value="{{ $company->subdomain }}" disabled>
                    <div class="subdomain-suffix">.{{ config('tenancy.root_domain', 'yourdomain.com') }}</div>
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:5px">Subdomain cannot be changed.</div>
            </div>

            <div class="mf-field">
                <label class="mf-label">Company Email <span style="color:var(--red)">*</span></label>
                <input type="email" name="company_email" id="company_email" class="mf-inp" required value="{{ old('company_email', $company->company_email) }}">
            </div>

            @if($isClientUserFlow)
            <div style="display:flex;align-items:center;gap:10px;margin-top:18px">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('my-companies.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
            @endif
        </div>

        @if(!$isClientUserFlow)
        <div class="acc-card" style="margin-bottom:16px;opacity:.88">
            <div style="display:flex;align-items:center;margin-bottom:6px">
                <span class="step-num">2</span>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">Company Admin Account</div>
            </div>
            <div style="font-size:12.5px;color:var(--text-3);margin-bottom:18px;padding-left:32px">
                This section is shown for reference and cannot be edited from this page.
            </div>

            <div class="mf-field">
                <label class="mf-label">Admin Full Name</label>
                <input type="text" class="mf-inp" value="{{ $adminUser?->name ?? '—' }}" disabled>
            </div>

            <div class="mf-field">
                <label class="mf-label">Admin Email</label>
                <input type="email" class="mf-inp" value="{{ $adminUser?->email ?? '—' }}" disabled>
            </div>

            <div class="form-2col">
                <div class="mf-field">
                    <label class="mf-label">Password</label>
                    <input type="password" class="mf-inp" value="********" disabled>
                </div>
                <div class="mf-field">
                    <label class="mf-label">Confirm Password</label>
                    <input type="password" class="mf-inp" value="********" disabled>
                </div>
            </div>
        </div>

        <div class="acc-card">
            <div style="display:flex;align-items:center;margin-bottom:6px">
                <span class="step-num">3</span>
                <div style="font-size:15px;font-weight:600;color:var(--text-1)">Choose a Plan</div>
            </div>
            <div style="font-size:12.5px;color:var(--text-3);margin-bottom:18px;padding-left:32px">
                Plan selection is locked on edit. Current assigned plan is highlighted.
            </div>

            @if(($packages ?? collect())->isEmpty())
            <div style="text-align:center;padding:24px 20px;border:1.5px dashed var(--border);border-radius:10px;color:var(--text-3);font-size:12.5px">
                No plans available.
            </div>
            @else
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach($packages as $pkg)
                @php $isSelectedPlan = (int) $company->client_package_id === (int) $pkg->id; @endphp
                <label class="pkg-card {{ $isSelectedPlan ? 'selected' : '' }}" style="cursor:not-allowed;opacity:.88">
                    <input type="radio" disabled {{ $isSelectedPlan ? 'checked' : '' }}>
                    <div style="flex:1">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
                            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">{{ $pkg->name }}</div>
                            <div style="font-size:12px;color:var(--text-2)">
                                @if(($pkg->is_free ?? false) || ((float) ($pkg->monthly_price ?? 0) <= 0))
                                    Free
                                @else
                                    ${{ number_format($pkg->monthly_price ?? 0, 0) }}/mo
                                @endif
                            </div>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @endif

            <div style="display:flex;align-items:center;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('my-companies.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
        @endif
    </div>

    <div style="position:sticky;top:20px;display:flex;flex-direction:column;gap:14px">
        <div class="acc-card">
            <div style="font-size:13px;font-weight:600;color:var(--text-1);margin-bottom:14px;display:flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Company Preview
            </div>
            <div style="text-align:center;padding:16px 0">
                <div id="preview-avatar" style="width:48px;height:48px;border-radius:12px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff;margin:0 auto 10px">?</div>
                <div id="preview-name" style="font-size:14px;font-weight:600;color:var(--text-1)">Company Name</div>
                <div id="preview-subdomain" style="font-size:12px;color:var(--text-3);margin-top:2px">{{ $company->subdomain }}.{{ config('tenancy.root_domain', 'yourdomain.com') }}</div>
                <div id="preview-email" style="font-size:11.5px;color:var(--text-3);margin-top:2px"></div>
            </div>
        </div>

        <div style="background:linear-gradient(135deg,#1a3a5c,#1a6aa8);border-radius:12px;padding:18px;color:#fff">
            <div style="font-size:13px;font-weight:600;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Quick Tips
            </div>
            <ul style="font-size:12px;color:rgba(255,255,255,.75);line-height:1.7;padding-left:14px;margin:0">
                <li>Only company details are editable here.</li>
                <li>Subdomain is permanent and cannot be changed.</li>
                <li>Use Save Changes to update immediately.</li>
            </ul>
        </div>
    </div>
</div>
</form>

@endsection

@push('scripts')
<script>
function updatePreview() {
    const name = document.getElementById('company_name')?.value || '';
    const email = document.getElementById('company_email')?.value || '';
    const initials = name.trim()
        ? name.trim().split(/\s+/).slice(0,2).map(w => w[0].toUpperCase()).join('')
        : '?';

    document.getElementById('preview-avatar').textContent = initials;
    document.getElementById('preview-name').textContent = name || 'Company Name';
    document.getElementById('preview-email').textContent = email;
}

document.getElementById('company_name')?.addEventListener('input', updatePreview);
document.getElementById('company_email')?.addEventListener('input', updatePreview);
updatePreview();
</script>
@endpush
