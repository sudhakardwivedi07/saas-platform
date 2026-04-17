@extends('layouts.superadmin.app')
@section('title', 'Edit Email Template')
@section('page-title', 'Edit Email Template')

@section('content')
<div class="ph">
    <div>
        <h1>{{ $template->name }}</h1>
        <p>Edit global email template</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.email-templates.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:760px">
    <div class="acc-title">Template Details</div>
    <form id="tplForm">
        @csrf

        <div class="f-group">
            <label class="f-label">Template Name *</label>
            <input name="name" value="{{ $template->name }}" class="f-inp" required>
        </div>

        <div class="f-group">
            <label class="f-label">Trigger Event</label>
            <input name="trigger_event" value="{{ $template->trigger_event }}" class="f-inp" placeholder="e.g. New firm created">
        </div>

        <div class="f-group">
            <label class="f-label">Subject *</label>
            <input name="subject" value="{{ $template->subject }}" class="f-inp" required>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:12px">Sender (optional — overrides SMTP defaults)</div>

        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">From Name</label>
                <input name="from_name" value="{{ $template->from_name }}" class="f-inp" placeholder="CFO Insights">
            </div>
            <div class="f-group">
                <label class="f-label">From Email</label>
                <input type="email" name="from_email" value="{{ $template->from_email }}" class="f-inp" placeholder="noreply@yourdomain.com">
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>

        <div class="f-group">
            <label class="f-label">Body *</label>
            <div style="font-size:11.5px;color:var(--text-3);margin-bottom:6px">
                Use placeholders like
                <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{name}}</code>,
                <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{app_name}}</code>
            </div>
            <textarea name="body" rows="18" class="f-textarea" style="font-family:monospace;font-size:13px" required>{{ $template->body }}</textarea>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }} style="accent-color:var(--orange)">
                <span>Active</span>
            </label>
            <div style="display:flex;gap:10px">
                <a href="{{ route('superadmin.email-templates.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Template</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('tplForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;

    const body = Object.fromEntries(new FormData(this).entries());
    const res  = await apiCall('{{ route("superadmin.email-templates.update", $template->slug) }}', 'POST', body);

    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
});
</script>
@endpush
