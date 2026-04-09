<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — {{ $setting?->global_app_name ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root { --primary: {{ $setting?->primary_color ?? '#2563eb' }}; }
        body   { background: #f8fafc; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; padding: 2rem 1rem; }
        .wrap  { width: 100%; max-width: 640px; }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand h1 { font-size: 1.75rem; font-weight: 800; color: var(--primary); }
        .brand p  { color: #64748b; margin-top: .25rem; }
        .pkg-card { border: 2px solid #e2e8f0; border-radius: .75rem; padding: 1.25rem; cursor: pointer; transition: all .15s; }
        .pkg-card:hover  { border-color: var(--primary); }
        .pkg-card.selected { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 5%, #fff); }
        .pkg-card input[type=radio] { accent-color: var(--primary); }
        .pkg-price { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
        .step-indicator { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.5rem; }
        .step { width: 1.75rem; height: 1.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 600; }
        .step.done    { background: var(--primary); color: #fff; }
        .step.current { background: #fff; border: 2px solid var(--primary); color: var(--primary); }
        .step.pending { background: #f1f5f9; color: #94a3b8; }
        .step-line { flex: 1; height: 2px; background: #e2e8f0; }
        .step-line.done { background: var(--primary); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <h1>{{ $setting?->global_app_name ?? config('app.name') }}</h1>
        <p>Create your account and start your free trial</p>
    </div>

    @if($errors->any())
    <div class="alert alert-error mb-5">
        <ul class="space-y-1 list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        <div class="card">
            <div class="card-body space-y-5">

                {{-- Section: Company --}}
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-blue-600">1</span>
                        Your Company
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group col-span-2">
                            <label class="form-label">Company Name <span>*</span></label>
                            <input type="text" name="company_name" class="form-control {{ $errors->has('company_name') ? 'error' : '' }}"
                                   value="{{ old('company_name') }}" placeholder="Acme Inc." required>
                            @error('company_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group col-span-2">
                            <label class="form-label">Subdomain <span>*</span></label>
                            <div style="display:flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:.5rem;overflow:hidden;background:#f8fafc">
                                <input type="text" name="subdomain" id="subdomain"
                                       class="form-control" style="border:0;border-radius:0;flex:1"
                                       value="{{ old('subdomain') }}" placeholder="acme"
                                       pattern="[a-zA-Z0-9\-]+" required>
                                <span style="padding:.625rem .875rem;background:#f1f5f9;color:#64748b;font-size:.8rem;white-space:nowrap;border-left:1.5px solid #e2e8f0">
                                    .{{ config('tenancy.root_domain') }}
                                </span>
                            </div>
                            @error('subdomain') <p class="form-error">{{ $message }}</p> @enderror
                            <p class="form-text">Only letters, numbers, and hyphens. Cannot be changed later.</p>
                        </div>
                    </div>
                </div>

                <hr style="border:0;border-top:1px solid #f1f5f9">

                {{-- Section: Account --}}
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-blue-600">2</span>
                        Admin Account
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group col-span-2">
                            <label class="form-label">Full Name <span>*</span></label>
                            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'error' : '' }}"
                                   value="{{ old('name') }}" required>
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group col-span-2">
                            <label class="form-label">Email Address <span>*</span></label>
                            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'error' : '' }}"
                                   value="{{ old('email') }}" required>
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password <span>*</span></label>
                            <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'error' : '' }}"
                                   minlength="8" required>
                            @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password <span>*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>

                @if($packages->count() > 0)
                <hr style="border:0;border-top:1px solid #f1f5f9">

                {{-- Section: Plan --}}
                <div>
                    <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-blue-600">3</span>
                        Choose Your Plan
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 ml-9">All plans include a free trial. Cancel anytime.</p>

                    <div class="space-y-3">
                        @foreach($packages as $pkg)
                        <label class="pkg-card flex items-start gap-4 {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'selected' : '' }}"
                               onclick="document.querySelectorAll('.pkg-card').forEach(c=>c.classList.remove('selected'));this.classList.add('selected')">
                            <input type="radio" name="package_id" value="{{ $pkg->id }}"
                                   {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'checked' : '' }}
                                   class="mt-1">
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $pkg->name }}
                                            @if($pkg->is_recommended)
                                            <span class="badge badge-blue ml-2 text-xs">Recommended</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $pkg->description }}</p>
                                    </div>
                                    <div class="text-right ml-4 flex-shrink-0">
                                        <span class="pkg-price">${{ number_format($pkg->monthly_price, 0) }}</span>
                                        <span class="text-xs text-gray-400">/mo</span>
                                    </div>
                                </div>
                                @if($pkg->is_trial_allowed)
                                <p class="text-xs text-green-600 mt-1 font-medium">✓ {{ $pkg->trial_days }}-day free trial included</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <button type="submit" id="submit-btn" class="btn btn-primary btn-block btn-lg mt-2">
                    Create Account →
                </button>

                <p class="text-center text-sm text-gray-500">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-blue-600 font-medium">Sign in</a>
                </p>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-slugify subdomain from company name
document.querySelector('[name=company_name]').addEventListener('input', function() {
    const sub = document.getElementById('subdomain');
    if (!sub.dataset.touched) {
        sub.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});
document.getElementById('subdomain').addEventListener('input', function() {
    this.dataset.touched = 'true';
    this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
});
</script>
<script>
// Update button text based on selected plan
function updateSubmitBtn() {
    const selected = document.querySelector('input[name=package_id]:checked');
    const btn = document.getElementById('submit-btn');
    if (!btn) return;
    const price = selected ? parseFloat(selected.closest('label').querySelector('.pkg-price')?.textContent?.replace('$','') || 0) : 0;
    btn.textContent = price > 0 ? 'Continue to Payment →' : 'Create Account →';
}
document.querySelectorAll('input[name=package_id]').forEach(r => r.addEventListener('change', updateSubmitBtn));
updateSubmitBtn();
</script>
</body>
</html>
