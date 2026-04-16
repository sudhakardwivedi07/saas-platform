@extends('layouts.superadmin.app')
@section('title', 'Global Settings')
@section('page-title', 'Global Settings')

@section('content')
<div class="ph">
    <div>
        <h1>Global Settings</h1>
        <p>Platform-wide configuration and feature toggles</p>
    </div>
</div>

<div class="acc-card" style="max-width:680px">
    <div class="acc-title">Platform Settings</div>
    <form id="settingsForm">
        @csrf
        <div class="form-2col">
            <div class="f-group" style="grid-column:span 2">
                <label class="f-label">Platform Name *</label>
                <input name="global_app_name" value="{{ $setting?->global_app_name }}" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Contact Email *</label>
                <input name="company_email" type="email" value="{{ $setting?->company_email }}" class="f-inp" required>
            </div>
            <div class="f-group">
                <label class="f-label">Primary Color</label>
                <input name="primary_color" type="color" value="{{ $setting?->primary_color ?? '#2563eb' }}" class="f-inp" style="height:42px;padding:4px 8px">
            </div>
            <div class="f-group">
                <label class="f-label">Timezone</label>
                <select name="timezone" class="f-sel">
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ ($setting?->timezone ?? 'UTC') === $tz ? 'selected':'' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <div class="f-group">
                <label class="f-label">Locale</label>
                <select name="locale" class="f-sel">
                    @foreach(['en'=>'English','es'=>'Spanish','fr'=>'French','de'=>'German','ar'=>'Arabic'] as $code=>$name)
                        <option value="{{ $code }}" {{ ($setting?->locale ?? 'en') === $code ? 'selected':'' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="f-group">
                <label class="f-label">Date Format</label>
                <select name="date_format" class="f-sel">
                    @foreach(['Y-m-d'=>'YYYY-MM-DD','d/m/Y'=>'DD/MM/YYYY','m/d/Y'=>'MM/DD/YYYY'] as $v=>$l)
                        <option value="{{ $v }}" {{ ($setting?->date_format) === $v ? 'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="f-group">
                <label class="f-label">Currency</label>
                <select name="currency_code" class="f-sel">
                    @foreach(['USD'=>'USD - US Dollar','EUR'=>'EUR - Euro','GBP'=>'GBP - British Pound','INR'=>'INR - Indian Rupee'] as $v=>$l)
                        <option value="{{ $v }}" {{ ($setting?->currency_code) === $v ? 'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin:18px 0"></div>
        <div style="font-size:13px;font-weight:600;margin-bottom:12px">Feature Toggles</div>
        <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px">
            @foreach([
                ['allow_registration','Allow Public Registration','Users can self-register without an invite'],
                ['email_verification','Require Email Verification','New users must verify email before access'],
                ['maintenance_mode','Maintenance Mode','Show maintenance page to all tenants'],
            ] as [$field, $label, $hint])
            <label style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer">
                <div>
                    <div style="font-size:13px;font-weight:500">{{ $label }}</div>
                    <div style="font-size:12px;color:var(--text-3)">{{ $hint }}</div>
                </div>
                <input type="checkbox" name="{{ $field }}" value="1" style="accent-color:var(--orange);width:16px;height:16px"
                    {{ $setting?->$field ? 'checked':'' }}>
            </label>
            @endforeach
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const fd = new FormData(this);
    const body = Object.fromEntries(fd.entries());
    ['allow_registration','email_verification','maintenance_mode'].forEach(f => body[f] = fd.has(f) ? 1 : 0);
    const res = await apiCall('{{ route("superadmin.settings.update") }}', 'POST', body);
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
});
</script>
@endpush
