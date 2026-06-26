<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reset Password</title>
    <link href="{{ asset(config('assets.font_inter')) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset(config('assets.sweetalert2_css')) }}">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f8fafc }
        .pw-wrap { position:relative }
        .pw-wrap .form-control { padding-right:44px }
        .pw-eye { position:absolute; right:0; top:0; width:44px; height:44px; display:flex; align-items:center; justify-content:center; background:none; border:none; cursor:pointer; color:#9ca3af; transition:color .14s; padding:0 }
        .pw-eye:hover { color:#6b7280 }
        .pw-eye svg { width:17px; height:17px; stroke:currentColor; stroke-width:2; fill:none; pointer-events:none }
    </style>
</head>
<body>
<div style="width:100%;max-width:26rem;padding:1rem">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-1">Set new password</h2>
            <p class="text-sm text-gray-500 mb-5">Choose a strong password for your account.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{ $email }}" readonly style="opacity:.6">
                </div>

                {{-- New Password --}}
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password"
                               class="form-control {{ $errors->has('password') ? 'error' : '' }}"
                               minlength="8" required autofocus>
                        <button type="button" class="pw-eye" id="pw-toggle-1" aria-label="Show password" tabindex="-1">
                            <svg id="eye-show-1" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eye-hide-1" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Confirm New Password --}}
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control {{ $errors->has('password') ? 'error' : '' }}"
                               required>
                        <button type="button" class="pw-eye" id="pw-toggle-2" aria-label="Show password" tabindex="-1">
                            <svg id="eye-show-2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eye-hide-2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Server-side mismatch error lands here --}}
                    @error('password')
                        <p class="form-error" id="confirm-error">{{ $message }}</p>
                    @else
                        <p class="form-error" id="confirm-error" style="display:none">Passwords do not match.</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset(config('assets.sweetalert2_js')) }}"></script>
<script>
(function () {
    // ── Eye toggles ──────────────────────────────────────────────────────────
    function initEye(toggleId, inputId, showId, hideId) {
        var btn   = document.getElementById(toggleId);
        var input = document.getElementById(inputId);
        var show  = document.getElementById(showId);
        var hide  = document.getElementById(hideId);
        btn.addEventListener('click', function () {
            var isHidden = input.type === 'password';
            input.type        = isHidden ? 'text'     : 'password';
            show.style.display = isHidden ? 'none'    : '';
            hide.style.display = isHidden ? ''        : 'none';
            btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    }
    initEye('pw-toggle-1', 'password',             'eye-show-1', 'eye-hide-1');
    initEye('pw-toggle-2', 'password_confirmation', 'eye-show-2', 'eye-hide-2');

    // ── Real-time confirm-password mismatch ──────────────────────────────────
    var pwInput      = document.getElementById('password');
    var confirmInput = document.getElementById('password_confirmation');
    var errorEl      = document.getElementById('confirm-error');

    function checkMatch() {
        if (confirmInput.value === '') {
            errorEl.style.display = 'none';
            confirmInput.classList.remove('error');
            return;
        }
        var mismatch = pwInput.value !== confirmInput.value;
        errorEl.style.display = mismatch ? '' : 'none';
        confirmInput.classList.toggle('error', mismatch);
    }

    confirmInput.addEventListener('input', checkMatch);
    pwInput.addEventListener('input', checkMatch);

    // ── Block form submit when passwords don't match ─────────────────────────
    var form = confirmInput.closest('form');
    form.addEventListener('submit', function (e) {
        checkMatch();
        if (pwInput.value !== confirmInput.value) {
            e.preventDefault();
            confirmInput.focus();
        }
    });

    @if(session('password_reset_success'))
    // ── Password reset success alert ─────────────────────────────────────────
    Swal.fire({
        icon:              'success',
        title:             'Password Changed!',
        text:              'Your password has been reset successfully. You can now sign in with your new password.',
        confirmButtonText: 'Go to Login',
        confirmButtonColor: '#2563eb',
        allowOutsideClick: false,
        allowEscapeKey:    false,
    }).then(function () {
        window.location.href = '{{ route('login') }}';
    });
    @endif
}());
</script>
</body>
</html>
