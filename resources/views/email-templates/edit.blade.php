@extends('layouts.app')
@section('title','Edit Email Template')
@section('page-title','Edit: {{ $slug }}')

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
    ['{{plan_name}}',     'Subscription plan name'],
    ['{{period_end}}',    'Subscription period end date'],
    ['{{days_left}}',     'Trial days remaining'],
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
        <h1>{{ ucwords(str_replace('_',' ',$slug)) }}</h1>
        <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
            @if($template && $template->company_id)
            <span class="badge b-blue">Custom Version</span>
            @else
            <span class="badge b-gray">Global Default (editing creates custom copy)</span>
            @endif
        </div>
    </div>
    <div class="ph-actions">
        <a href="{{ route('email-templates.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="et-layout">
    <div class="acc-card">
        <form id="template-form" method="POST" action="{{ route('email-templates.update', $slug) }}">
            @csrf

            <div class="f-group">
                <label class="f-label">Template Name</label>
                <input type="text" name="name" class="f-inp"
                       value="{{ old('name', $template?->name ?? $slug) }}" required>
            </div>

            <div class="f-group">
                <label class="f-label">Subject Line *</label>
                <input type="text" name="subject" class="f-inp"
                       value="{{ old('subject', $template?->subject ?? '') }}" required>
                <div style="font-size:12px;color:var(--text-3);margin-top:3px">Supports placeholders e.g. <code style="background:var(--bg);padding:1px 4px;border-radius:4px">&#123;&#123;app_name&#125;&#125;</code></div>
            </div>

            <div class="form-2col">
                <div class="f-group">
                    <label class="f-label">From Name</label>
                    <input type="text" name="from_name" class="f-inp"
                           value="{{ old('from_name', $template?->from_name ?? '') }}"
                           placeholder="Leave blank to use default">
                </div>
                <div class="f-group">
                    <label class="f-label">From Email</label>
                    <input type="email" name="from_email" class="f-inp"
                           value="{{ old('from_email', $template?->from_email ?? '') }}"
                           placeholder="Leave blank to use default">
                </div>
            </div>

            <div class="f-group">
                <label class="f-label">Email Body (HTML) *</label>
                <div style="border:1px solid var(--border);border-radius:9px;overflow:hidden">
                    <div style="background:var(--bg);border-bottom:1px solid var(--border);padding:8px 10px;display:flex;gap:6px;flex-wrap:wrap">
                        @foreach($phButtons as $ph)
                        <button type="button" onclick="insertPlaceholder('{{ $ph }}')"
                                class="btn btn-ghost btn-xs" style="font-family:monospace">
                            {{ $ph }}
                        </button>
                        @endforeach
                    </div>
                    <textarea id="body-editor" name="body" rows="16"
                              style="width:100%;padding:14px;border:0;font-family:monospace;font-size:13px;resize:vertical;outline:none;background:#fff"
                              required>{{ old('body', $template?->body ?? '') }}</textarea>
                </div>
            </div>

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Save Template</button>
                <button type="button" onclick="previewTemplate()" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Preview
                </button>
                <a href="{{ route('email-templates.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    <div>
        <div class="acc-card">
            <div style="font-size:13px;font-weight:600;margin-bottom:12px">Available Placeholders</div>
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($phList as [$ph, $desc])
                <div style="display:flex;align-items:flex-start;gap:8px">
                    <code onclick="insertPlaceholder('{{ $ph }}')" style="font-size:11px;background:var(--bg);padding:2px 6px;border-radius:5px;color:var(--blue);flex-shrink:0;cursor:pointer">{{ $ph }}</code>
                    <span style="font-size:12px;color:var(--text-2)">{{ $desc }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="acc-card">
            <div style="font-size:13px;font-weight:600;margin-bottom:10px">Quick Snippets</div>
            <div style="display:flex;flex-direction:column;gap:7px">
                <button type="button" onclick="insertSnippet('btn')" class="btn btn-ghost btn-sm" style="justify-content:flex-start">Insert Button</button>
                <button type="button" onclick="insertSnippet('divider')" class="btn btn-ghost btn-sm" style="justify-content:flex-start">Insert Divider</button>
                <button type="button" onclick="insertSnippet('greeting')" class="btn btn-ghost btn-sm" style="justify-content:flex-start">Insert Greeting</button>
            </div>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div id="preview-modal" class="modal-overlay">
    <div class="modal-box" style="width:660px;max-height:88vh">
        <button class="modal-close" onclick="document.getElementById('preview-modal').classList.remove('open')">×</button>
        <div class="modal-title">Email Preview</div>
        <iframe id="preview-iframe" style="width:100%;border:0;min-height:440px;border-radius:8px;margin-top:12px"></iframe>
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
    btn:      `<p><a href="{{login_url}}" style="display:inline-block;background:#f06b21;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600">Click Here</a></p>`,
    divider:  `<hr style="border:0;border-top:1px solid #e2e8f0;margin:24px 0">`,
    greeting: `<p>Hi {{name}},</p>\n<p>Hope you're doing well!</p>`,
};
@endverbatim

function insertSnippet(key) {
    if (snippets[key]) insertPlaceholder(snippets[key]);
}

function previewTemplate() {
    const body = editor.value
        .replace(/\{\{name\}\}/g,    'John Doe')
        .replace(/\{\{email\}\}/g,   'john@example.com')
        .replace(/\{\{login_url\}\}/g,   '#')
        .replace(/\{\{billing_url\}\}/g, '#')
        .replace(/\{\{app_name\}\}/g, appName);
    const html = `<!DOCTYPE html><html><head><meta charset=UTF-8><style>body{font-family:DM Sans,Arial,sans-serif;background:#f0f4f8;margin:0;padding:20px}.w{max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}.hd{background:#f06b21;padding:22px 32px}.hd h1{color:#fff;margin:0;font-size:18px}.bd{padding:28px 32px;color:#1a202c;font-size:15px;line-height:1.6}</style></head><body><div class="w"><div class="hd"><h1>${appName}</h1></div><div class="bd">${body}</div></div></body></html>`;
    document.getElementById('preview-iframe').srcdoc = html;
    document.getElementById('preview-modal').classList.add('open');
}

document.getElementById('template-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const res = await fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
    }).then(r => r.json());
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
});
</script>
@endpush
