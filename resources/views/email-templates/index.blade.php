@extends('layouts.app')
@section('title','Email Templates')
@section('page-title','Email Templates')

@section('content')
<div class="ph">
    <div class="ph-left">
        <h1>Email Templates</h1>
        <p>Customize the emails your customers receive. Supports <code style="background:var(--bg);padding:1px 5px;border-radius:4px">&#123;&#123;placeholders&#125;&#125;</code> replaced dynamically.</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('email-templates.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Template
        </a>
    </div>
</div>

@php
$templateLabels = [
    'user_welcome'          => ['label' => 'Welcome Email',         'color' => 'var(--blue)',   'icon' => 'wave'],
    'password_reset'        => ['label' => 'Password Reset',        'color' => 'var(--orange)', 'icon' => 'lock'],
    'payment_success'       => ['label' => 'Payment Successful',    'color' => 'var(--green)',  'icon' => 'check'],
    'payment_failed'        => ['label' => 'Payment Failed',        'color' => 'var(--red)',    'icon' => 'alert'],
    'subscription_canceled' => ['label' => 'Subscription Canceled', 'color' => 'var(--text-3)','icon' => 'cancel'],
    'two_factor_code'       => ['label' => 'Two-Factor Code',       'color' => 'var(--purple)', 'icon' => 'shield'],
    'document_shared'       => ['label' => 'Document Shared',       'color' => 'var(--purple)', 'icon' => 'file'],
    'trial_ending'          => ['label' => 'Trial Ending Reminder', 'color' => 'var(--amber)',  'icon' => 'clock'],
];
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
    @foreach($templates as $slug => $group)
    @php
        $info = $templateLabels[$slug] ?? ['label' => Str::title(str_replace('_',' ',$slug)), 'color' => 'var(--text-3)', 'icon' => 'mail'];
        $hasCustom = $group->firstWhere('company_id', '!=', null) !== null;
    @endphp
    <div class="card" style="margin-bottom:0;transition:box-shadow .18s" onmouseover="this.style.boxShadow='var(--sh-md)'" onmouseout="this.style.boxShadow='var(--sh-sm)'">
        <div style="display:flex;align-items:flex-start;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;background:{{ $info['color'] }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg viewBox="0 0 24 24" fill="none" stroke="{{ $info['color'] }}" stroke-width="1.8" width="18" height="18">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px">
                    <span style="font-size:13.5px;font-weight:600">{{ $info['label'] }}</span>
                    @if($hasCustom)
                    <span class="badge b-blue" style="font-size:10.5px">Customized</span>
                    @else
                    <span class="badge b-gray" style="font-size:10.5px">Default</span>
                    @endif
                </div>
                <div style="font-size:11.5px;color:var(--text-3);font-family:monospace;margin-bottom:10px">{{ $slug }}</div>
                <div style="display:flex;gap:7px;flex-wrap:wrap">
                    <a href="{{ route('email-templates.edit', $slug) }}" class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </a>
                    <button onclick="sendTest('{{ $slug }}')" class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Test
                    </button>
                    @if($hasCustom)
                    <form method="POST" action="{{ route('email-templates.destroy', $slug) }}"
                          onsubmit="return confirm('Remove custom version and revert to global default?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-sm" style="color:var(--red)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                            Revert
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Test Email Modal --}}
<div id="test-modal" class="modal-overlay">
    <div class="modal-box" style="width:400px">
        <button class="modal-close" onclick="document.getElementById('test-modal').classList.remove('open')">×</button>
        <div class="modal-title">Send Test Email</div>
        <div class="modal-sub">Preview how this template looks in an inbox</div>
        <input type="hidden" id="test-slug">
        <div class="mf-field">
            <label class="mf-label">Send to</label>
            <input type="email" id="test-email" class="mf-inp" placeholder="you@example.com"
                   value="{{ auth()->user()->email }}">
        </div>
        <div class="mf-foot">
            <button onclick="document.getElementById('test-modal').classList.remove('open')" class="btn btn-ghost">Cancel</button>
            <button onclick="submitTest()" class="btn btn-primary">Send Test</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function sendTest(slug) {
    document.getElementById('test-slug').value = slug;
    document.getElementById('test-modal').classList.add('open');
}
async function submitTest() {
    const slug  = document.getElementById('test-slug').value;
    const email = document.getElementById('test-email').value;
    if (!email) { showToast('Enter an email address', 'error'); return; }
    const json = await fetch('{{ route("email-templates.test", ":slug") }}'.replace(':slug', slug), {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({to_email:email})
    }).then(r=>r.json());
    showToast(json.message, json.status === 'success' ? 'success' : 'error');
    if (json.status==='success') document.getElementById('test-modal').classList.remove('open');
}
</script>
@endpush
