@extends('layouts.app')
@section('title','Email Templates')
@section('page-title','Email Templates')

@section('content')

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">
        Customize the emails your customers receive. Each template supports <code>@{{placeholders}}</code> that are replaced dynamically.
    </p>
    <a href="{{ route('email-templates.create') }}" class="btn btn-primary flex-shrink-0 ml-4">
        <span class="material-icons text-lg">add</span> New Template
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @php
    $templateLabels = [
        'user_welcome'          => ['icon' => 'waving_hand',    'label' => 'Welcome Email',             'color' => 'text-blue-500'],
        'password_reset'        => ['icon' => 'lock_reset',     'label' => 'Password Reset',             'color' => 'text-orange-500'],
        'payment_success'       => ['icon' => 'check_circle',   'label' => 'Payment Successful',         'color' => 'text-green-500'],
        'payment_failed'        => ['icon' => 'error',          'label' => 'Payment Failed',             'color' => 'text-red-500'],
        'subscription_canceled' => ['icon' => 'cancel',         'label' => 'Subscription Canceled',      'color' => 'text-gray-500'],
        'two_factor_code'       => ['icon' => 'shield',         'label' => 'Two-Factor Code',            'color' => 'text-indigo-500'],
        'document_shared'       => ['icon' => 'folder_shared',  'label' => 'Document Shared',            'color' => 'text-purple-500'],
        'trial_ending'          => ['icon' => 'hourglass_empty','label' => 'Trial Ending Reminder',      'color' => 'text-amber-500'],
    ];
    @endphp

    @foreach($templates as $slug => $group)
    @php
        $info = $templateLabels[$slug] ?? ['icon' => 'email', 'label' => Str::title(str_replace('_',' ',$slug)), 'color' => 'text-gray-500'];
        $hasCustom = $group->firstWhere('company_id', '!=', null) !== null;
    @endphp
    <div class="card hover:shadow-lg transition">
        <div class="card-body flex items-start gap-4">
            <span class="material-icons text-3xl {{ $info['color'] }} flex-shrink-0 mt-1">{{ $info['icon'] }}</span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold text-gray-800">{{ $info['label'] }}</h3>
                    @if($hasCustom)
                    <span class="badge badge-blue text-xs">Customized</span>
                    @else
                    <span class="badge badge-gray text-xs">Default</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 font-mono mb-3">{{ $slug }}</p>
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('email-templates.edit', $slug) }}" class="btn btn-secondary btn-sm">
                        <span class="material-icons text-sm">edit</span> Edit
                    </a>
                    <button onclick="sendTest('{{ $slug }}')" class="btn btn-ghost btn-sm">
                        <span class="material-icons text-sm">send</span> Test
                    </button>
                    @if($hasCustom)
                    <form method="POST" action="{{ route('email-templates.destroy', $slug) }}"
                          onsubmit="return confirm('Remove custom version and revert to global default?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-sm text-red-400 hover:text-red-600">
                            <span class="material-icons text-sm">restore</span>
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
<div id="test-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Send Test Email</h3>
        <input type="hidden" id="test-slug">
        <div class="form-group mb-4">
            <label class="form-label">Send to</label>
            <input type="email" id="test-email" class="form-control" placeholder="you@example.com"
                   value="{{ auth()->user()->email }}">
        </div>
        <div class="flex gap-3">
            <button onclick="submitTest()" class="btn btn-primary flex-1">Send Test</button>
            <button onclick="closeModal('test-modal')" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function sendTest(slug) {
    document.getElementById('test-slug').value = slug;
    openModal('test-modal');
}
async function submitTest() {
    const slug  = document.getElementById('test-slug').value;
    const email = document.getElementById('test-email').value;
    if (!email) { showToast('Enter an email address', 'error'); return; }
    const json = await fetch(`/email-templates/${slug}/test`, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({to_email:email})
    }).then(r=>r.json());
    handleReply(json);
    if (json.status==='success') closeModal('test-modal');
}
</script>
@endpush
