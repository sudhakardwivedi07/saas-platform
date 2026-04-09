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

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <h3>Create Email Template</h3>
                <a href="{{ route('email-templates.index') }}" class="btn btn-secondary btn-sm">← Back</a>
            </div>
            <div class="card-body">
                <form id="create-form">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Template Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Welcome Email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug (unique identifier) *</label>
                            <input type="text" name="slug" id="slug-field" class="form-control font-mono"
                                   placeholder="welcome_email" pattern="[a-z0-9_]+" required>
                            <p class="text-xs text-gray-400 mt-1">Lowercase letters, numbers and underscores only</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject Line *</label>
                        <input type="text" name="subject" class="form-control" placeholder="Welcome to @{{app_name}}!" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">From Name</label>
                            <input type="text" name="from_name" class="form-control" placeholder="Leave blank for default">
                        </div>
                        <div class="form-group">
                            <label class="form-label">From Email</label>
                            <input type="email" name="from_email" class="form-control" placeholder="Leave blank for default">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Body (HTML) *</label>
                        <div style="border:1.5px solid #e2e8f0;border-radius:.5rem;overflow:hidden">
                            <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:.5rem .75rem;display:flex;gap:.5rem;flex-wrap:wrap">
                                @foreach($phButtons as $ph)
                                <button type="button" onclick="insertPh('{{ $ph }}')"
                                        class="btn btn-ghost btn-sm font-mono text-xs" style="padding:.25rem .5rem">
                                    {{ $ph }}
                                </button>
                                @endforeach
                            </div>
                            <textarea id="body-editor" name="body" rows="16"
                                      style="width:100%;padding:1rem;border:0;font-family:monospace;font-size:.85rem;resize:vertical;outline:none;background:#fff"
                                      required></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn btn-primary">
                            <span class="material-icons text-lg">save</span> Create Template
                        </button>
                        <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card">
            <div class="card-header"><h3>Available Placeholders</h3></div>
            <div class="card-body space-y-3">
                @foreach($phList as [$ph, $desc])
                <div class="flex items-start gap-2">
                    <code class="text-xs bg-gray-100 px-2 py-0.5 rounded text-blue-600 flex-shrink-0 cursor-pointer hover:bg-blue-100"
                          onclick="insertPh('{{ $ph }}')">{{ $ph }}</code>
                    <span class="text-xs text-gray-500">{{ $desc }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Tips</h3></div>
            <div class="card-body text-sm text-gray-500 space-y-2">
                <p>• Use a descriptive slug like <code class="bg-gray-100 px-1 rounded">order_confirmation</code></p>
                <p>• The slug is used in code to send this template</p>
                <p>• HTML is fully supported in the body</p>
                <p>• Test your template after saving</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const editor = document.getElementById('body-editor');

// Auto-generate slug from name
document.querySelector('[name=name]').addEventListener('input', function() {
    const slugField = document.getElementById('slug-field');
    if (!slugField.dataset.manual) {
        slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
    }
});
document.getElementById('slug-field').addEventListener('input', function() {
    this.dataset.manual = '1';
});

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
    btn.disabled = true; btn.innerHTML = '<span class="material-icons text-lg">hourglass_empty</span> Saving…';
    const fd  = new FormData(this);
    const res = await fetch('{{ route("email-templates.store") }}', {
        method: 'POST', body: fd,
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json'}
    }).then(r => r.json());
    if (res.status === 'success') {
        showToast(res.message);
        setTimeout(() => window.location = res.redirect, 1000);
    } else {
        showToast(res.message || 'Error saving template', 'error');
        btn.disabled = false; btn.innerHTML = '<span class="material-icons text-lg">save</span> Create Template';
    }
});
</script>
@endpush
