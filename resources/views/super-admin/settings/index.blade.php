@extends('layouts.superadmin.app')
@section('title', 'Global Settings')
@section('page-title', 'Global Settings')

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header"><h3>Platform Settings</h3></div>
        <div class="card-body">
            <form id="settingsForm">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group col-span-2">
                        <label class="form-label">Platform Name *</label>
                        <input name="global_app_name" value="{{ $setting?->global_app_name }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Email *</label>
                        <input name="company_email" type="email" value="{{ $setting?->company_email }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Primary Color</label>
                        <input name="primary_color" type="color" value="{{ $setting?->primary_color ?? '#2563eb' }}" class="form-control h-10 p-1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Timezone</label>
                        <select name="timezone" class="form-control">
                            @foreach(timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" {{ ($setting?->timezone ?? 'UTC') === $tz ? 'selected':'' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Locale</label>
                        <select name="locale" class="form-control">
                            @foreach(['en'=>'English','es'=>'Spanish','fr'=>'French','de'=>'German','ar'=>'Arabic'] as $code=>$name)
                                <option value="{{ $code }}" {{ ($setting?->locale ?? 'en') === $code ? 'selected':'' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Format</label>
                        <select name="date_format" class="form-control">
                            @foreach(['Y-m-d'=>'YYYY-MM-DD','d/m/Y'=>'DD/MM/YYYY','m/d/Y'=>'MM/DD/YYYY'] as $v=>$l)
                                <option value="{{ $v }}" {{ ($setting?->date_format) === $v ? 'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Currency</label>
                        <select name="currency_code" class="form-control">
                            @foreach(['USD'=>'USD - US Dollar','EUR'=>'EUR - Euro','GBP'=>'GBP - British Pound','INR'=>'INR - Indian Rupee'] as $v=>$l)
                                <option value="{{ $v }}" {{ ($setting?->currency_code) === $v ? 'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4 border-slate-100">
                <p class="text-sm font-semibold text-slate-600 mb-3">Feature Toggles</p>
                <div class="space-y-3">
                    @foreach([
                        ['allow_registration','Allow Public Registration','Users can self-register'],
                        ['email_verification','Require Email Verification','New users must verify email'],
                        ['maintenance_mode','Maintenance Mode','Show maintenance page to tenants'],
                    ] as [$field, $label, $hint])
                    <label class="flex items-center justify-between p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $label }}</p>
                            <p class="text-xs text-slate-400">{{ $hint }}</p>
                        </div>
                        <input type="checkbox" name="{{ $field }}" value="1" class="rounded"
                            {{ $setting?->$field ? 'checked':'' }}>
                    </label>
                    @endforeach
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
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
