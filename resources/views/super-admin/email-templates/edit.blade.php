@extends('layouts.superadmin.app')
@section('title', 'Edit Email Template')
@section('page-title', 'Edit Email Template')

@section('content')
<div class="ph">
    <div>
        <h1>{{ ucwords(str_replace('_',' ',$template->slug)) }}</h1>
        <p>Edit global email template</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.email-templates.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div class="acc-card">
    <div class="acc-title">Template Editor</div>
    <form id="tplForm">
        @csrf
        <div class="f-group">
            <label class="f-label">Subject *</label>
            <input name="subject" value="{{ $template->subject }}" class="f-inp" required>
        </div>
        <div class="f-group">
            <label class="f-label">Body *</label>
            <div style="font-size:11.5px;color:var(--text-3);margin-bottom:6px">Use placeholders like <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{name}}</code>, <code style="background:var(--bg);padding:1px 5px;border-radius:4px">@{{app_name}}</code></div>
            <textarea name="body" rows="16" class="f-textarea" style="font-family:monospace;font-size:13px" required>{{ $template->body }}</textarea>
        </div>
        <div style="display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">Save Template</button>
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
    const res = await apiCall('{{ route("superadmin.email-templates.update", $template->slug) }}', 'POST', body);
    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
});
</script>
@endpush
