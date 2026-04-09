@extends('layouts.app')
@section('title','Edit Email Template')
@section('page-title','Edit: {{ $slug }}')

@php
{{-- Define placeholder arrays here so Blade does NOT parse the {{}} inside them --}}
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
    ['{{plan_name}}',     'Subscription plan name'],
    ['{{period_end}}',    'Subscription period end date'],
    ['{{days_left}}',     'Trial days remaining'],
    ['{{code}}',          '2FA verification code'],
];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Editor --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <h3>Template Editor</h3>
                @if($template && $template->company_id)
                <span class="badge badge-blue">Custom Version</span>
                @else
                <span class="badge badge-gray">Global Default (editing creates custom copy)</span>
                @endif
            </div>
            <div class="card-body">
                <form id="template-form" data-ajax-form method="POST"
                      action="{{ route('email-templates.update', $slug) }}" data-reload="false">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Template Name</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $template?->name ?? $slug) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject Line <span>*</span></label>
                        <input type="text" name="subject" class="form-control"
                               value="{{ old('subject', $template?->subject ?? '') }}" required>
                        <p class="form-text">Supports placeholders e.g. <code>@{{app_name}}</code></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">From Name</label>
                            <input type="text" name="from_name" class="form-control"
                                   value="{{ old('from_name', $template?->from_name ?? '') }}"
                                   placeholder="Leave blank to use default">
                        </div>
                        <div class="form-group">
                            <label class="form-label">From Email</label>
                            <input type="email" name="from_email" class="form-control"
                                   value="{{ old('from_email', $template?->from_email ?? '') }}"
                                   placeholder="Leave blank to use default">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Body (HTML) <span>*</span></label>
                        <div style="border:1.5px solid #e2e8f0;border-radius:.5rem;overflow:hidden">
                            <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:.5rem .75rem;display:flex;gap:.5rem;flex-wrap:wrap">
                                @foreach($phButtons as $ph)
                                <button type="button" onclick="insertPlaceholder('{{ $ph }}')"
                                        class="btn btn-ghost btn-sm font-mono text-xs" style="padding:.25rem .5rem">
                                    {{ $ph }}
                                </button>
                                @endforeach
                            </div>
                            <textarea id="body-editor" name="body" rows="16"
                                      style="width:100%;padding:1rem;border:0;font-family:monospace;font-size:.85rem;resize:vertical;outline:none;background:#fff"
                                      required>{{ old('body', $template?->body ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn btn-primary">
                            <span class="material-icons text-lg">save</span> Save Template
                        </button>
                        <a href="{{ route('email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="button" onclick="previewTemplate()" class="btn btn-ghost">
                            <span class="material-icons text-lg">visibility</span> Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar: placeholder reference --}}
    <div class="space-y-4">
        <div class="card">
            <div class="card-header"><h3>Available Placeholders</h3></div>
            <div class="card-body space-y-3">
                @foreach($phList as [$ph, $desc])
                <div class="flex items-start gap-2">
                    <code class="text-xs bg-gray-100 px-2 py-0.5 rounded text-blue-600 flex-shrink-0 cursor-pointer hover:bg-blue-100"
                          onclick="insertPlaceholder('{{ $ph }}')">{{ $ph }}</code>
                    <span class="text-xs text-gray-500">{{ $desc }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Quick HTML Snippets</h3></div>
            <div class="card-body space-y-2">
                <button type="button" onclick="insertSnippet('btn')" class="btn btn-secondary btn-sm w-full">
                    Insert Button
                </button>
                <button type="button" onclick="insertSnippet('divider')" class="btn btn-secondary btn-sm w-full">
                    Insert Divider
                </button>
                <button type="button" onclick="insertSnippet('greeting')" class="btn btn-secondary btn-sm w-full">
                    Insert Greeting
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Preview modal --}}
<div id="preview-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold">Email Preview</h3>
            <button onclick="closeModal('preview-modal')" class="text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto">
            <iframe id="preview-iframe" style="width:100%;border:0;min-height:500px"></iframe>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const editor  = document.getElementById('body-editor');
const appName = '{{ addslashes($appName) }}';

function insertPlaceholder(ph) {
    const start = editor.selectionStart;
    const end   = editor.selectionEnd;
    editor.value = editor.value.substring(0, start) + ph + editor.value.substring(end);
    editor.selectionStart = editor.selectionEnd = start + ph.length;
    editor.focus();
}

@verbatim
const snippets = {
    btn:      `<p><a href="{{login_url}}" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600">Click Here</a></p>`,
    divider:  `<hr style="border:0;border-top:1px solid #e2e8f0;margin:24px 0">`,
    greeting: `<p>Hi {{name}},</p>\n<p>Hope you're doing well!</p>`,
};

function insertSnippet(key) {
    const snippet = snippets[key];
    if (snippet) insertPlaceholder(snippet);
}

function previewTemplate() {
    const body = editor.value
        .replace(/\{\{name\}\}/g,    'John Doe')
        .replace(/\{\{email\}\}/g,   'john@example.com')
        .replace(/\{\{login_url\}\}/g,   '#')
        .replace(/\{\{billing_url\}\}/g, '#');
@endverbatim
    // app name comes from server
    const previewBody = body.replace(/\{\{app_name\}\}/g, appName);

    const html = `<!DOCTYPE html><html><head><meta charset=UTF-8><style>body{font-family:Inter,Arial,sans-serif;background:#f8fafc;margin:0;padding:20px}.wrapper{max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1)}.header{background:#2563eb;padding:24px 32px}.header h1{color:#fff;margin:0;font-size:20px}.body{padding:32px;color:#1e293b;font-size:15px;line-height:1.6}</style></head><body><div class="wrapper"><div class="header"><h1>${appName}</h1></div><div class="body">${previewBody}</div></div></body></html>`;

    const iframe = document.getElementById('preview-iframe');
    iframe.srcdoc = html;
    openModal('preview-modal');
}

// Handle AJAX form submission
document.getElementById('template-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const fd   = new FormData(this);
    const res  = await fetch(this.action, {
        method: 'POST',
        body: fd,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
    }).then(r => r.json());
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
});
</script>
@endpush
