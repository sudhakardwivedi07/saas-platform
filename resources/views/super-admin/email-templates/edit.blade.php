@extends('layouts.superadmin.app')
@section('title', 'Edit Email Template')
@section('page-title', 'Edit Email Template')

@section('content')
<div class="max-w-4xl">
    <div class="card">
        <div class="card-header">
            <h3>{{ ucwords(str_replace('_',' ',$template->slug)) }}</h3>
            <a href="{{ route('superadmin.email-templates.index') }}" class="btn btn-secondary text-xs">
                <span class="material-icons text-sm">arrow_back</span> Back
            </a>
        </div>
        <div class="card-body">
            <form id="tplForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Subject *</label>
                    <input name="subject" value="{{ $template->subject }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Body *</label>
                    <p class="text-xs text-slate-400 mb-1">Use placeholders like <code>@{{name}}</code>, <code>@{{app_name}}</code>, <code>@{{email}}</code></p>
                    <textarea name="body" rows="16" class="form-control font-mono text-sm" required>{{ $template->body }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Save Template</button>
                </div>
            </form>
        </div>
    </div>
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
