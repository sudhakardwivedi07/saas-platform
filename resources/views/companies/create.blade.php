@extends('layouts.app')

@section('title', 'Add New Company')
@section('page-title', 'Add New Company')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Back --}}
    <a href="{{ route('my-companies.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <span class="material-icons text-base">arrow_back</span>
        Back to My Companies
    </a>

    @if($errors->any())
    <div class="alert alert-error bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('my-companies.store') }}" id="create-company-form">
        @csrf

        <div class="card">
            <div class="card-body space-y-6">

                {{-- Section 1: Company Info --}}
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-blue-600">1</span>
                        Company Information
                    </h3>
                    <div class="space-y-4">
                        <div class="form-group">
                            <label class="form-label">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" id="company_name"
                                   class="form-control {{ $errors->has('company_name') ? 'border-red-400' : '' }}"
                                   value="{{ old('company_name') }}" placeholder="Acme Inc." required>
                            @error('company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Subdomain <span class="text-red-500">*</span></label>
                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                <input type="text" name="subdomain" id="subdomain"
                                       class="flex-1 px-3 py-2 bg-transparent outline-none text-gray-800 {{ $errors->has('subdomain') ? 'border-red-400' : '' }}"
                                       value="{{ old('subdomain') }}" placeholder="acme"
                                       pattern="[a-zA-Z0-9\-]+" required>
                                <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm border-l border-gray-200 whitespace-nowrap">
                                    .{{ config('tenancy.root_domain') }}
                                </span>
                            </div>
                            @error('subdomain') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-400 mt-1">Only letters, numbers and hyphens. Cannot be changed later.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Company Email <span class="text-red-500">*</span></label>
                            <input type="email" name="company_email"
                                   class="form-control {{ $errors->has('company_email') ? 'border-red-400' : '' }}"
                                   value="{{ old('company_email') }}" placeholder="info@acme.com" required>
                            @error('company_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Section 2: Admin Account --}}
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-blue-600">2</span>
                        Company Admin Account
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 -mt-2">
                        Create login credentials for this company's administrator.
                    </p>
                    <div class="space-y-4">
                        <div class="form-group">
                            <label class="form-label">Admin Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="admin_name"
                                   class="form-control {{ $errors->has('admin_name') ? 'border-red-400' : '' }}"
                                   value="{{ old('admin_name') }}" required>
                            @error('admin_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admin Email <span class="text-red-500">*</span></label>
                            <input type="email" name="admin_email"
                                   class="form-control {{ $errors->has('admin_email') ? 'border-red-400' : '' }}"
                                   value="{{ old('admin_email') }}" required>
                            @error('admin_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="admin_password"
                                       class="form-control {{ $errors->has('admin_password') ? 'border-red-400' : '' }}"
                                       minlength="8" required>
                                @error('admin_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                                <input type="password" name="admin_password_confirmation"
                                       class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Section 3: Plan --}}
                <div>
                    <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-blue-600">3</span>
                        Choose a Plan
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 ml-9">
                        These are your own plans, separate from the SuperAdmin's global plans.
                    </p>

                    @if($packages->isEmpty())
                    {{-- No client plans defined yet --}}
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center">
                        <span class="material-icons text-4xl text-gray-300">loyalty</span>
                        <h4 class="font-semibold text-gray-600 mt-2">No plans configured yet</h4>
                        <p class="text-sm text-gray-400 mt-1">
                            You need to create at least one plan before adding companies.
                        </p>
                        <a href="{{ route('plans.create') }}"
                           class="btn btn-outline mt-4 inline-flex items-center gap-1 text-sm">
                            <span class="material-icons text-base">add</span>
                            Create a Plan first
                        </a>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($packages as $pkg)
                        <label class="flex items-start gap-4 border-2 rounded-xl p-4 cursor-pointer transition-all pkg-card
                                      {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}"
                               onclick="selectPkg(this, {{ $pkg->id }}, {{ $pkg->monthly_price }})">
                            <input type="radio" name="package_id" value="{{ $pkg->id }}"
                                   {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'checked' : '' }}
                                   class="mt-1 accent-blue-600">
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ $pkg->name }}
                                            @if($pkg->is_recommended)
                                            <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">★ Recommended</span>
                                            @endif
                                            @if($pkg->is_free)
                                            <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Free</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $pkg->description }}</p>
                                    </div>
                                    <div class="text-right ml-4 flex-shrink-0">
                                        @if($pkg->is_free || $pkg->monthly_price == 0)
                                        <span class="text-2xl font-bold text-green-600">Free</span>
                                        @else
                                        <span class="text-2xl font-bold text-gray-800 pkg-price">${{ number_format($pkg->monthly_price, 0) }}</span>
                                        <span class="text-xs text-gray-400">/mo</span>
                                        @endif
                                    </div>
                                </div>
                                @if($pkg->is_trial_allowed && !$pkg->is_free)
                                <p class="text-xs text-green-600 mt-1 font-medium">
                                    <span class="material-icons text-xs align-middle">check_circle</span>
                                    {{ $pkg->trial_days }}-day free trial included
                                </p>
                                @endif
                                @if($pkg->max_users || $pkg->max_storage_gb)
                                <p class="text-xs text-gray-400 mt-1">
                                    @if($pkg->max_users) Up to {{ $pkg->max_users }} users @endif
                                    @if($pkg->max_users && $pkg->max_storage_gb) · @endif
                                    @if($pkg->max_storage_gb) {{ $pkg->max_storage_gb }}GB storage @endif
                                </p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Payment notice --}}
                <div id="payment-notice" class="hidden bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800 flex items-start gap-2">
                    <span class="material-icons text-amber-500 text-lg flex-shrink-0 mt-0.5">credit_card</span>
                    <div>
                        <strong>Payment required.</strong>
                        You'll be redirected to our secure payment page to complete your subscription.
                        The company will be activated immediately after payment.
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" id="submit-btn"
                        class="btn btn-primary btn-block btn-lg"
                        {{ $packages->isEmpty() ? 'disabled' : '' }}>
                    {{ $packages->isEmpty() ? 'Create a Plan First' : 'Create Company →' }}
                </button>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function selectPkg(label, pkgId, price) {
    document.querySelectorAll('.pkg-card').forEach(c => {
        c.classList.remove('border-blue-500', 'bg-blue-50');
        c.classList.add('border-gray-200');
    });
    label.classList.add('border-blue-500', 'bg-blue-50');
    label.classList.remove('border-gray-200');

    const notice = document.getElementById('payment-notice');
    const btn    = document.getElementById('submit-btn');

    if (price > 0) {
        notice.classList.remove('hidden');
        btn.textContent = 'Continue to Payment →';
    } else {
        notice.classList.add('hidden');
        btn.textContent = 'Create Company →';
    }
}

// Auto-slugify subdomain from company name
document.getElementById('company_name')?.addEventListener('input', function () {
    const sub = document.getElementById('subdomain');
    if (!sub.dataset.touched) {
        sub.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});
document.getElementById('subdomain')?.addEventListener('input', function () {
    this.dataset.touched = 'true';
    this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
});

// Init button state
(function () {
    const checked = document.querySelector('input[name=package_id]:checked');
    if (checked) {
        const card  = checked.closest('.pkg-card');
        const price = parseFloat(card?.querySelector('.text-2xl')?.textContent?.replace('$','') || '0');
        if (price > 0) {
            document.getElementById('payment-notice')?.classList.remove('hidden');
            const btn = document.getElementById('submit-btn');
            if (btn) btn.textContent = 'Continue to Payment →';
        }
    }
})();
</script>
@endpush
