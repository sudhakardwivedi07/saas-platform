<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — {{ $setting?->global_app_name ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <style>
:root {
  --primary: {{ $setting?->primary_color ?? '#f06b21' }};
  --primary-dark: #d05519;
  --blue: #1a6aa8;
  --bg: #f0f4f8;
  --border: #e2e8f0;
  --text-1: #1a202c;
  --text-2: #6b7280;
  --text-3: #9ca3af;
  --green: #10b981;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
body {
    font-family: 'DM Sans', -apple-system, sans-serif;
    background: linear-gradient(160deg, #0d3d66 0%, #1a6aa8 50%, var(--primary) 100%);
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 32px 16px 48px;
}
.wrap { width: 100%; max-width: 640px }
.brand { text-align: center; margin-bottom: 24px }
.brand-name {
    font-size: 28px; font-weight: 700; color: #fff; letter-spacing: -.3px;
}
.brand-sub { font-size: 14px; color: rgba(255,255,255,.7); margin-top: 5px }
.reg-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    overflow: hidden;
}
.rc-body { padding: 32px 36px }
.sec-head {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 16px; margin-top: 20px;
    font-size: 15px; font-weight: 600; color: var(--text-1);
}
.sec-num {
    width: 26px; height: 26px; border-radius: 50%;
    background: var(--primary); color: #fff;
    font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sec-div { height: 1px; background: var(--border); margin: 22px 0 }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px }
.f-group { margin-bottom: 14px }
.f-label { display: block; font-size: 13px; font-weight: 500; color: var(--text-1); margin-bottom: 5px }
.f-inp {
    width: 100%; height: 40px; border: 1.5px solid var(--border);
    border-radius: 9px; padding: 0 12px; font-size: 14px;
    font-family: inherit; color: var(--text-1); outline: none;
    transition: border-color .14s; background: #fff;
}
.f-inp:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(26,106,168,.07) }
.f-inp.error { border-color: #ef4444 }
.f-sel {
    width: 100%; height: 40px; border: 1.5px solid var(--border);
    border-radius: 9px; padding: 0 12px; font-size: 14px;
    font-family: inherit; color: var(--text-1); background: #fff; outline: none;
}
.f-err { font-size: 12px; color: #ef4444; margin-top: 4px }
.f-hint { font-size: 12px; color: var(--text-3); margin-top: 3px }
.subdomain-wrap {
    display: flex; border: 1.5px solid var(--border);
    border-radius: 9px; overflow: hidden;
}
.subdomain-wrap input {
    flex: 1; height: 40px; border: none; padding: 0 12px;
    font-size: 14px; font-family: inherit; color: var(--text-1); outline: none;
}
.subdomain-sfx {
    padding: 0 12px; height: 40px; display: flex; align-items: center;
    background: var(--bg); border-left: 1.5px solid var(--border);
    font-size: 13px; color: var(--text-2); white-space: nowrap;
}
/* Plan cards */
.pkg-card {
    border: 2px solid var(--border); border-radius: 10px;
    padding: 14px 16px; cursor: pointer; transition: all .14s;
    display: flex; gap: 12px; margin-bottom: 10px;
}
.pkg-card:hover { border-color: var(--primary) }
.pkg-card.selected { border-color: var(--primary); background: #fff9f6 }
.pkg-card input[type=radio] { accent-color: var(--primary); margin-top: 2px; flex-shrink: 0 }
.pkg-price { font-size: 22px; font-weight: 700; color: var(--text-1) }
.pkg-trial { font-size: 12px; color: var(--green); font-weight: 500; margin-top: 4px }
/* Submit */
.submit-btn {
    width: 100%; height: 46px; background: var(--primary); color: #fff;
    border: none; border-radius: 10px; font-size: 15px; font-weight: 600;
    font-family: inherit; cursor: pointer; transition: background .14s;
    margin-top: 10px;
}
.submit-btn:hover { background: var(--primary-dark) }
.sign-in-link { text-align: center; font-size: 13px; color: var(--text-2); margin-top: 14px }
.sign-in-link a { color: var(--primary); font-weight: 600 }
/* Error alert */
.err-alert {
    background: #fff5f5; border: 1px solid #fca5a5; border-radius: 9px;
    padding: 12px 14px; margin-bottom: 18px; font-size: 13px; color: #991b1b;
}
.err-alert ul { padding-left: 16px; margin-top: 4px }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        @if(isset($setting) && $setting?->logo)
            <img src="{{ asset('storage/'.$setting->logo) }}" alt="{{ $setting->global_app_name }}" style="max-height:44px;filter:brightness(0) invert(1);display:block;margin:0 auto 10px">
        @else
            <div class="brand-name">{{ $setting?->global_app_name ?? config('app.name') }}</div>
        @endif
        <div class="brand-sub">Create your account and start your free trial</div>
    </div>

    <div class="reg-card">
        <div class="rc-body">

            @if($errors->any())
            <div class="err-alert">
                <strong>Please fix the following errors:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}">
                @csrf

                {{-- Section 1: Company --}}
                <div class="sec-head">
                    <span class="sec-num">1</span>
                    Your Company
                </div>
                <div class="grid-2">
                    <div class="f-group" style="grid-column:span 2">
                        <label class="f-label">Company Name *</label>
                        <input type="text" name="company_name" class="f-inp {{ $errors->has('company_name') ? 'error' : '' }}"
                               value="{{ old('company_name') }}" placeholder="Acme Inc." required>
                        @error('company_name') <div class="f-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="f-group" style="grid-column:span 2">
                        <label class="f-label">Subdomain *</label>
                        <div class="subdomain-wrap">
                            <input type="text" name="subdomain" id="subdomain"
                                   value="{{ old('subdomain') }}" placeholder="acme"
                                   pattern="[a-zA-Z0-9\-]+" required>
                            <span class="subdomain-sfx">.{{ config('tenancy.root_domain') }}</span>
                        </div>
                        @error('subdomain') <div class="f-err">{{ $message }}</div> @enderror
                        <div class="f-hint">Only letters, numbers, and hyphens. Cannot be changed later.</div>
                    </div>
                </div>

                <div class="sec-div"></div>

                {{-- Section 2: Admin Account --}}
                <div class="sec-head">
                    <span class="sec-num">2</span>
                    Admin Account
                </div>
                <div class="grid-2">
                    <div class="f-group" style="grid-column:span 2">
                        <label class="f-label">Full Name *</label>
                        <input type="text" name="name" class="f-inp {{ $errors->has('name') ? 'error' : '' }}"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="f-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="f-group" style="grid-column:span 2">
                        <label class="f-label">Email Address *</label>
                        <input type="email" name="email" class="f-inp {{ $errors->has('email') ? 'error' : '' }}"
                               value="{{ old('email') }}" required>
                        @error('email') <div class="f-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="f-group">
                        <label class="f-label">Password *</label>
                        <input type="password" name="password" class="f-inp {{ $errors->has('password') ? 'error' : '' }}"
                               minlength="8" required>
                        @error('password') <div class="f-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="f-group">
                        <label class="f-label">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="f-inp" required>
                    </div>
                </div>

                @if($packages->count() > 0)
                <div class="sec-div"></div>

                {{-- Section 3: Plan --}}
                <div class="sec-head">
                    <span class="sec-num">3</span>
                    Choose Your Plan
                </div>
                <div style="font-size:13px;color:var(--text-2);margin-bottom:14px">All plans include a free trial. Cancel anytime.</div>

                @foreach($packages as $pkg)
                <label class="pkg-card {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'selected' : '' }}"
                       onclick="document.querySelectorAll('.pkg-card').forEach(c=>c.classList.remove('selected'));this.classList.add('selected')">
                    <input type="radio" name="package_id" value="{{ $pkg->id }}"
                           {{ old('package_id') == $pkg->id || (!old('package_id') && $loop->first) ? 'checked' : '' }}>
                    <div style="flex:1">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                            <div>
                                <span style="font-size:14px;font-weight:600">{{ $pkg->name }}</span>
                                @if($pkg->is_recommended)
                                <span style="display:inline-block;background:#fff3e8;color:var(--primary);font-size:11px;font-weight:600;padding:1px 8px;border-radius:999px;margin-left:6px">Recommended</span>
                                @endif
                                @if($pkg->description)
                                <div style="font-size:13px;color:var(--text-2);margin-top:2px">{{ $pkg->description }}</div>
                                @endif
                            </div>
                            <div style="text-align:right;flex-shrink:0">
                                <span class="pkg-price">${{ number_format($pkg->monthly_price, 0) }}</span>
                                <span style="font-size:12px;color:var(--text-3)">/mo</span>
                            </div>
                        </div>
                        @if($pkg->is_trial_allowed)
                        <div class="pkg-trial">✓ {{ $pkg->trial_days }}-day free trial included</div>
                        @endif
                    </div>
                </label>
                @endforeach
                @endif

                <button type="submit" id="submit-btn" class="submit-btn">Create Account →</button>

                <div class="sign-in-link">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
                </div>
            </form>
        </div>
    </div>
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

// Update submit button text based on plan price
function updateBtn() {
    const selected = document.querySelector('input[name=package_id]:checked');
    const btn = document.getElementById('submit-btn');
    if (!btn) return;
    const priceEl = selected?.closest('label')?.querySelector('.pkg-price');
    const price = priceEl ? parseFloat(priceEl.textContent.replace('$','') || 0) : 0;
    btn.textContent = price > 0 ? 'Continue to Payment →' : 'Create Account →';
}
document.querySelectorAll('input[name=package_id]').forEach(r => r.addEventListener('change', updateBtn));
updateBtn();
</script>
</body>
</html>
