@extends('layouts.app')
@section('title', 'New Email Template')
@section('page-title', 'New Email Template')

@php
$phButtons = ['{{name}}','{{email}}','{{app_name}}','{{company}}','{{login_url}}','{{billing_url}}','{{support_email}}','{{date}}'];
$phList = [
    ['{{name}}',          "Recipient's full name"],
    ['{{email}}',         "Recipient's email"],
    ['{{app_name}}',      "Your app's display name"],
    ['{{company}}',       'Company name'],
    ['{{login_url}}',     'Link to login page'],
    ['{{billing_url}}',   'Link to billing page'],
    ['{{support_email}}', 'Support email address'],
    ['{{date}}',          'Current date (Y-m-d)'],
    ['{{amount}}',        'Payment amount (payment emails)'],
    ['{{code}}',          '2FA verification code'],
];
@endphp

@push('styles')
<style>
.et-layout { display:grid; grid-template-columns:1fr 280px; gap:14px; align-items:start; }
@media(max-width:900px) { .et-layout { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="ph">
    <div class="ph-left">
        <h1>New Email Template</h1>
        <p>Create a custom email template for your tenants</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('email-templates.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="et-layout">
    <div>
        <div class="acc-card">
            <form id="create-form">
                @csrf
                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">Template Name *</label>
                        <input type="text" name="name" class="f-inp" placeholder="Welcome Email" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Slug *</label>
                        <input type="text" name="slug" id="slug-field" class="f-inp" style="font-family:monospace"
                               placeholder="welcome_email" pattern="[a-z0-9_]+" required>
                        <div style="font-size:11.5px;color:var(--text-3);margin-top:3px">Lowercase, numbers, underscores only</div>
                    </div>
                </div>

                <div class="f-group">
                    <label class="f-label">Subject Line *</label>
                    <input type="text" name="subject" class="f-inp" placeholder="Welcome to &#123;&#123;app_name&#125;&#125;!" required>
                </div>

                <div class="form-2col">
                    <div class="f-group">
                        <label class="f-label">From Name</label>
                        <input type="text" name="from_name" class="f-inp" placeholder="Leave blank for default">
                    </div>
                    <div class="f-group">
                        <label class="f-label">From Email</label>
                        <input type="email" name="from_email" class="f-inp" placeholder="Leave blank for default">
                    </div>
                </div>

                <div class="f-group">
                    <label class="f-label">Email Body (HTML) *</label>
                    <div style="border:1px solid var(--border);border-radius:9px;overflow:hidden">
                        <div style="background:var(--bg);border-bottom:1px solid var(--border);padding:8px 10px;display:flex;gap:6px;flex-wrap:wrap">
                            @foreach($phButtons as $ph)
                            <button type="button" onclick="insertPh('{{ $ph }}')"
                                    class="btn btn-ghost btn-xs" style="font-family:monospace">
                                {{ $ph }}
                            </button>
                            @endforeach
                        </div>
                        <textarea id="body-editor" name="body" rows="16"
                                  style="width:100%;padding:14px;border:0;font-family:monospace;font-size:13px;resize:vertical;outline:none;background:#fff"
                                  required></textarea>
                    </div>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <a href="{{ route('email-templates.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Template</button>
                </div>
            </form>
        </div>
    </div>

    <div>
        <div class="acc-card">
            <div style="font-size:13px;font-weight:600;margin-bottom:12px">Available Placeholders</div>
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($phList as [$ph, $desc])
                <div style="display:flex;align-items:flex-start;gap:8px">
                    <code onclick="insertPh('{{ $ph }}')" style="font-size:11px;background:var(--bg);padding:2px 6px;border-radius:5px;color:var(--blue);flex-shrink:0;cursor:pointer">{{ $ph }}</code>
                    <span style="font-size:12px;color:var(--text-2)">{{ $desc }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="acc-card">
            <div style="font-size:13px;font-weight:600;margin-bottom:10px">Tips</div>
            <div style="font-size:12.5px;color:var(--text-2);line-height:1.7">
                <div>• Use a descriptive slug</div>
                <div>• HTML is fully supported in the body</div>
                <div>• Test your template after saving</div>
                <div>• Click any placeholder to insert it</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const editor = document.getElementById('body-editor');

document.querySelector('[name=name]').addEventListener('input', function() {
    const slugField = document.getElementById('slug-field');
    if (!slugField.dataset.manual) {
        slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
    }
});
document.getElementById('slug-field').addEventListener('input', function() { this.dataset.manual = '1'; });

function insertPh(ph) {
    const start = editor.selectionStart;
    const end   = editor.selectionEnd;
    editor.value = editor.value.substring(0,start) + ph + editor.value.substring(end);
    editor.selectionStart = editor.selectionEnd = start + ph.length;
    editor.focus();
}

document.getElementById('create-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true; btn.textContent = 'Saving…';
    const res = await fetch('{{ route("email-templates.store") }}', {
        method: 'POST', body: new FormData(this),
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json'}
    }).then(r => r.json());
    if (res.status === 'success') {
        showToast(res.message);
        setTimeout(() => window.location = res.redirect, 1000);
    } else {
        showToast(res.message || 'Error saving template', 'error');
        btn.disabled = false; btn.textContent = 'Create Template';
    }
});
</script>
@endpush
