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

<div class="acc-card">
    <div class="acc-title">Template Details</div>
    <form id="tplForm">
        @csrf

        <div class="f-group">
            <label class="f-label">Template Name <span style="color:red">*</span></label>
            <input name="name" value="{{ $template->name }}" class="f-inp">
            <div class="f-error" id="err-name"></div>
        </div>

        <div class="f-group">
            <label class="f-label">Subject <span style="color:red">*</span></label>
            <input name="subject" value="{{ $template->subject }}" class="f-inp">
            <div class="f-error" id="err-subject"></div>
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
            <label class="f-label">Body <span style="color:red">*</span></label>
            <div style="font-size:11.5px;color:var(--text-3);margin-bottom:6px">
                Use placeholders like
                <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{name}}</code>,
                <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{app_name}}</code>
            </div>
            <textarea name="body" rows="18" class="f-textarea" style="font-family:monospace;font-size:13px">{{ $template->body }}</textarea>
            <div class="f-error" id="err-body"></div>
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
<style>
.f-error { color: red; font-size: 12px; margin-top: 4px; min-height: 16px; }
.f-inp.is-invalid, .f-textarea.is-invalid { border-color: red !important; }
</style>
<script>
(function () {
    var form = document.getElementById('tplForm');

    function showError(fieldName, message) {
        var el = document.getElementById('err-' + fieldName);
        var input = form.querySelector('[name="' + fieldName + '"]');
        if (el) el.textContent = message;
        if (input) input.classList.add('is-invalid');
    }

    function clearError(fieldName) {
        var el = document.getElementById('err-' + fieldName);
        var input = form.querySelector('[name="' + fieldName + '"]');
        if (el) el.textContent = '';
        if (input) input.classList.remove('is-invalid');
    }

    function validateForm() {
        var valid = true;
        var nameVal = form.querySelector('[name="name"]').value.trim();
        var subjectVal = form.querySelector('[name="subject"]').value.trim();
        var bodyVal = form.querySelector('[name="body"]').value.trim();

        if (!nameVal) { showError('name', 'Template Name is required.'); valid = false; } else { clearError('name'); }
        if (!subjectVal) { showError('subject', 'Subject is required.'); valid = false; } else { clearError('subject'); }
        if (!bodyVal) { showError('body', 'Body is required.'); valid = false; } else { clearError('body'); }

        return valid;
    }

    ['name', 'subject', 'body'].forEach(function (field) {
        var el = form.querySelector('[name="' + field + '"]');
        if (el) el.addEventListener('input', function () { clearError(field); });
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!validateForm()) return;

        var btn = this.querySelector('[type=submit]');
        btn.disabled = true;

        var body = Object.fromEntries(new FormData(this).entries());
        var res  = await apiCall('{{ route("superadmin.email-templates.update", $template->slug) }}', 'POST', body);

        showToast(res.message, res.status === 'success' ? 'success' : 'error');
        if (res.status === 'success' && res.redirect) {
            window.location.href = res.redirect;
            return;
        }
        btn.disabled = false;
    });
}());
</script>
@endpush
