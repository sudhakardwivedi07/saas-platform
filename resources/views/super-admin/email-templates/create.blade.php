@extends('layouts.superadmin.app')
@section('title', 'Add Email Template')
@section('page-title', 'Add Email Template')

@section('content')
<div class="ph">
    <div>
        <h1>Add Email Template</h1>
        <p>Create a new global email template</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.email-templates.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card" style="max-width:760px">
    <div class="acc-title">Template Details</div>
    <form id="createTplForm">
        @csrf

        <div class="f-group">
            <label class="f-label">Template Name *</label>
            <input name="name" class="f-inp" placeholder="e.g. Firm Welcome" required>
        </div>

        <div class="f-group">
            <label class="f-label">Trigger Event</label>
            <input name="trigger_event" class="f-inp" placeholder="e.g. New firm created">
        </div>

        <div class="f-group">
            <label class="f-label">Subject *</label>
            <input name="subject" class="f-inp" placeholder="e.g. Welcome to @{{app_name}}!" required>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>
        <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:12px">Sender (optional — overrides SMTP defaults)</div>

        <div class="form-2col">
            <div class="f-group">
                <label class="f-label">From Name</label>
                <input name="from_name" class="f-inp" placeholder="CFO Insights">
            </div>
            <div class="f-group">
                <label class="f-label">From Email</label>
                <input type="email" name="from_email" class="f-inp" placeholder="noreply@yourdomain.com">
            </div>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>

        <div class="f-group">
            <label class="f-label">Body *</label>
            <div style="font-size:11.5px;color:var(--text-3);margin-bottom:6px">
                Use placeholders like
                <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{name}}</code>,
                <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{app_name}}</code>,
                <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{email}}</code>
            </div>
            <textarea name="body" rows="18" class="f-textarea" style="font-family:monospace;font-size:13px" required></textarea>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px">
            <a href="{{ route('superadmin.email-templates.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Template</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createTplForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Creating...';

    const body = Object.fromEntries(new FormData(this).entries());
    const res  = await apiCall('{{ route("superadmin.email-templates.store") }}', 'POST', body);

    if (res.status === 'success') {
        showToast(res.message, 'success');
        setTimeout(() => { window.location.href = res.redirect ?? '{{ route("superadmin.email-templates.index") }}'; }, 800);
    } else {
        showToast(res.message ?? 'Something went wrong.', 'error');
        btn.disabled = false;
        btn.textContent = 'Create Template';
    }
});
</script>
@endpush
