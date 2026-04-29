@extends('layouts.app')
@section('title', 'Edit Plan — ' . $plan->name)
@section('page-title', 'Edit Plan')

@section('content')

<a href="{{ route('settings.index') }}#tab-plans" class="back-btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Plans
</a>

<form method="POST" action="{{ route('plans.update', $plan->id) }}">
    @csrf

    <div class="acc-card">

        {{-- Card header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:10px">
            <div>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="font-size:15px;font-weight:600;color:var(--text-1)">{{ $plan->name }}</div>
                    <span class="badge {{ $plan->is_active ? 'b-green' : 'b-gray' }}">
                        <span class="badge-dot" style="background:{{ $plan->is_active ? 'var(--green)' : 'var(--text-3)' }}"></span>
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div style="font-size:12.5px;color:var(--text-3);margin-top:2px">Update pricing, limits, and features</div>
            </div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('settings.index') }}#tab-plans" class="btn btn-ghost btn-sm">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="ca-alert ca-alert-error" style="margin-bottom:18px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <div>
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        </div>
        @endif

        {{-- ── Section: Basic Info ───────────────────────────── --}}
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:14px">Basic Info</div>

        <div class="f-group">
            <label class="f-label">Plan Name <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" class="f-inp" value="{{ old('name', $plan->name) }}" required>
        </div>

        <div class="f-group">
            <label class="f-label">Description</label>
            <textarea name="description" class="f-textarea" rows="2">{{ old('description', $plan->description) }}</textarea>
        </div>

        {{-- ── Section: Pricing ─────────────────────────────── --}}
        <div style="border-top:1px solid var(--border);margin:20px 0 18px"></div>
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:14px">Pricing</div>

        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Monthly Price ($) <span style="color:var(--red)">*</span></label>
                <div style="position:relative">
                    <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text-3);font-weight:500">$</span>
                    <input type="number" name="monthly_price" id="monthly_price_input" step="0.01" min="0" class="f-inp" style="padding-left:22px{{ old('is_free', $plan->is_free) ? ';background:var(--bg)' : '' }}" value="{{ old('monthly_price', $plan->monthly_price) }}" required {{ old('is_free', $plan->is_free) ? 'readonly' : '' }}>
                </div>
            </div>
            <div class="f-group">
                <label class="f-label">Annual Price <span style="font-size:11px;color:var(--text-3);font-weight:400">(optional)</span></label>
                <div style="position:relative">
                    <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text-3);font-weight:500">$</span>
                    <input type="number" name="annual_price" step="0.01" min="0" class="f-inp" style="padding-left:22px" value="{{ old('annual_price', $plan->annual_price) }}" placeholder="Leave blank to disable">
                </div>
            </div>
        </div>

        {{-- ── Section: Limits ──────────────────────────────── --}}
        <div style="border-top:1px solid var(--border);margin:20px 0 18px"></div>
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:14px">Usage Limits</div>

        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">Insights per Month <span style="color:var(--red)">*</span></label>
                <input type="number" name="insights_per_month" min="1" class="f-inp" value="{{ old('insights_per_month', $plan->insights_per_month) }}" placeholder="e.g. 50" required>
            </div>
            <div class="f-group">
                <label class="f-label">Max Companies <span style="font-size:11px;color:var(--text-3);font-weight:400">(blank = unlimited)</span></label>
                <input type="number" name="max_companies" min="1" class="f-inp" value="{{ old('max_companies', $plan->max_companies) }}" placeholder="e.g. 10">
            </div>
        </div>

        {{-- ── Section: Options ─────────────────────────────── --}}
        <div style="border-top:1px solid var(--border);margin:20px 0 18px"></div>
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:14px">Options</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:4px">

            <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;background:#fff;transition:border-color .14s" onmouseenter="this.style.borderColor='var(--blue)'" onmouseleave="this.style.borderColor='var(--border)'">
                <input type="hidden" name="is_free" value="0">
                <input type="checkbox" name="is_free" value="1" id="chk_free" style="width:15px;height:15px;accent-color:var(--orange);flex-shrink:0;cursor:pointer" {{ old('is_free', $plan->is_free) ? 'checked' : '' }} onchange="toggleFree(this)">
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-1)">Free Plan</div>
                    <div style="font-size:11.5px;color:var(--text-3)">Set price to $0 and mark as free</div>
                </div>
            </label>

            <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;background:#fff;transition:border-color .14s" onmouseenter="this.style.borderColor='var(--blue)'" onmouseleave="this.style.borderColor='var(--border)'">
                <input type="hidden" name="is_recommended" value="0">
                <input type="checkbox" name="is_recommended" value="1" style="width:15px;height:15px;accent-color:var(--orange);flex-shrink:0;cursor:pointer" {{ old('is_recommended', $plan->is_recommended) ? 'checked' : '' }}>
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-1)">Recommended</div>
                    <div style="font-size:11.5px;color:var(--text-3)">Highlight this plan to users</div>
                </div>
            </label>

            <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;background:#fff;transition:border-color .14s" onmouseenter="this.style.borderColor='var(--blue)'" onmouseleave="this.style.borderColor='var(--border)'">
                <input type="hidden" name="is_trial_allowed" value="0">
                <input type="checkbox" name="is_trial_allowed" value="1" id="chk_trial" style="width:15px;height:15px;accent-color:var(--orange);flex-shrink:0;cursor:pointer" {{ old('is_trial_allowed', $plan->is_trial_allowed) ? 'checked' : '' }} onchange="document.getElementById('trial_days_group').style.display=this.checked?'block':'none'">
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-1)">Free Trial</div>
                    <div style="font-size:11.5px;color:var(--text-3)">Allow trial before payment</div>
                </div>
            </label>

            <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;background:#fff;transition:border-color .14s" onmouseenter="this.style.borderColor='var(--blue)'" onmouseleave="this.style.borderColor='var(--border)'">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" style="width:15px;height:15px;accent-color:var(--orange);flex-shrink:0;cursor:pointer" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-1)">Active</div>
                    <div style="font-size:11.5px;color:var(--text-3)">Visible when adding companies</div>
                </div>
            </label>

        </div>

        {{-- Trial days (conditional) --}}
        <div id="trial_days_group" style="{{ old('is_trial_allowed', $plan->is_trial_allowed) ? '' : 'display:none' }};margin-top:14px">
            <div class="f-group">
                <label class="f-label">Trial Period (days)</label>
                <input type="number" name="trial_days" min="1" max="365" class="f-inp" style="max-width:160px" value="{{ old('trial_days', $plan->trial_days ?: 14) }}">
                <div style="font-size:12px;color:var(--text-3);margin-top:5px">Number of days users can trial before payment is required.</div>
            </div>
        </div>

        {{-- ── Section: Features ────────────────────────────── --}}
        <div style="border-top:1px solid var(--border);margin:20px 0 18px"></div>
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3);margin-bottom:14px">Features</div>

        <div class="f-group">
            <label class="f-label">Feature List <span style="font-size:11px;color:var(--text-3);font-weight:400">(one per line)</span></label>
            @php $featuresText = old('features', $plan->features ? implode("\n", $plan->features) : ''); @endphp
            <textarea name="features" class="f-textarea" rows="5" style="font-family:monospace;font-size:13px" placeholder="Up to 10 users&#10;50GB storage&#10;Priority support">{{ $featuresText }}</textarea>
            <div style="font-size:12px;color:var(--text-3);margin-top:5px">Each line will be shown as a bullet point on the pricing page.</div>
        </div>

        {{-- ── Section: Sort ────────────────────────────────── --}}
        <div style="border-top:1px solid var(--border);margin:20px 0 18px"></div>

        <div class="f-group">
            <label class="f-label">Sort Order</label>
            <input type="number" name="sort" min="0" class="f-inp" style="max-width:160px" value="{{ old('sort', $plan->sort) }}">
            <div style="font-size:12px;color:var(--text-3);margin-top:5px">Lower number = shown first on the pricing page.</div>
        </div>

        {{-- ── Actions ──────────────────────────────────────── --}}
        <div style="display:flex;gap:10px;padding-top:8px;border-top:1px solid var(--border);margin-top:20px">
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
            <a href="{{ route('settings.index') }}#tab-plans" class="btn btn-ghost">Cancel</a>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
function toggleFree(chk) {
    const inp = document.getElementById('monthly_price_input');
    if (chk.checked) { inp.value = '0'; inp.setAttribute('readonly', true); inp.style.background = 'var(--bg)'; }
    else             { inp.removeAttribute('readonly'); inp.style.background = ''; }
}
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('chk_free')?.checked) toggleFree(document.getElementById('chk_free'));
});
</script>
@endpush
