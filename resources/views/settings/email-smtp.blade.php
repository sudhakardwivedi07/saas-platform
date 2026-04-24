@extends('layouts.app')
@section('title','Email Settings')
@section('page-title','Email / SMTP Settings')

@section('content')
<div class="max-w-2xl">
<form id="smtp-form" data-ajax-form method="POST" action="{{ route('settings.email.update') }}">
    @csrf
    <div class="card">
        <div class="card-header">
            <h3>Outgoing Email Configuration</h3>
            <p class="text-xs text-gray-400">Overrides platform defaults for emails sent from your account.</p>
        </div>
        <div class="card-body space-y-5">

            <div class="form-group">
                <label class="form-label">Email Driver</label>
                <select name="driver" id="driver-select" class="form-control" onchange="toggleDriverFields(this.value)">
                    @foreach(['smtp'=>'SMTP','mailgun'=>'Mailgun (API)','sendgrid'=>'SendGrid (API)','ses'=>'Amazon SES','log'=>'Log (testing only)'] as $val => $label)
                    <option value="{{ $val }}" {{ ($smtpSetting?->driver ?? 'smtp') === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- SMTP fields --}}
            <div id="smtp-fields" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="host" class="form-control"
                               value="{{ $smtpSetting?->host }}" placeholder="smtp.mailgun.org">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port</label>
                        <input type="number" name="port" class="form-control"
                               value="{{ $smtpSetting?->port ?? 587 }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                               value="{{ $smtpSetting?->username }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Leave blank to keep current" autocomplete="new-password">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Encryption</label>
                    <select name="encryption" class="form-control">
                        <option value="tls"  {{ $smtpSetting?->encryption === 'tls'  ? 'selected' : '' }}>TLS</option>
                        <option value="ssl"  {{ $smtpSetting?->encryption === 'ssl'  ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ $smtpSetting?->encryption === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
            </div>

            {{-- API key (Mailgun / Sendgrid) --}}
            <div id="api-fields" class="hidden">
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <input type="password" name="api_key" class="form-control font-mono"
                           placeholder="Leave blank to keep current" autocomplete="new-password">
                </div>
            </div>

            <hr class="border-gray-100">

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">From Name <span>*</span></label>
                    <input type="text" name="from_name" class="form-control"
                           value="{{ $smtpSetting?->from_name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">From Email <span>*</span></label>
                    <input type="email" name="from_email" class="form-control"
                           value="{{ $smtpSetting?->from_email }}" required>
                </div>
            </div>

            <label class="form-check">
                <input type="checkbox" name="is_active" value="1"
                       {{ $smtpSetting?->is_active ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Enable custom SMTP (disabling uses platform default)</span>
            </label>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons text-lg">save</span> Save Settings
                </button>
                <button type="button" onclick="sendTestEmail()" class="btn btn-secondary">
                    <span class="material-icons text-lg">send</span> Send Test Email
                </button>
            </div>
        </div>
    </div>
</form>
</div>

@endsection

@push('scripts')
<script>
function toggleDriverFields(driver) {
    const isSmtp = driver === 'smtp';
    document.getElementById('smtp-fields').classList.toggle('hidden', !isSmtp);
    document.getElementById('api-fields').classList.toggle('hidden', isSmtp || driver === 'log' || driver === 'ses');
}
toggleDriverFields(document.getElementById('driver-select').value);

async function sendTestEmail() {
    const email = prompt('Send test email to:', '{{ auth()->user()->email }}');
    if (!email) return;
    const json = await fetch('{{ route("email-templates.test", "user_welcome") }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
        body:JSON.stringify({to_email: email})
    }).then(r=>r.json());
    handleReply(json);
}
</script>
@endpush
