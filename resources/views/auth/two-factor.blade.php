<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Two-Factor Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8fafc}
        .code-input{display:flex;gap:.5rem;justify-content:center}
        .code-input input{width:3rem;height:3.5rem;text-align:center;font-size:1.5rem;font-weight:700;border:2px solid #e2e8f0;border-radius:.5rem;background:#f8fafc;color:#1e293b}
        .code-input input:focus{outline:none;border-color:var(--primary,#2563eb);background:#fff}
    </style>
</head>
<body>
<div style="width:100%;max-width:24rem;padding:1rem">
    <div class="card">
        <div class="card-body text-center">
            <span class="material-icons" style="font-size:3rem;color:#3b82f6">shield</span>
            <h2 class="mt-2">Two-Factor Verification</h2>
            <p class="text-sm text-gray-500 mt-1 mb-6">Enter the 6-digit code sent to your email.</p>

            @if($errors->any())
            <div class="alert alert-error mb-4 text-left">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}" id="tfa-form">
                @csrf
                <input type="hidden" name="code" id="full-code">

                <div class="code-input mb-6" id="code-boxes">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                           class="digit" data-index="{{ $i }}" autocomplete="off">
                    @endfor
                </div>

                <button type="submit" class="btn btn-primary btn-block">Verify</button>
            </form>

            <p class="text-xs text-gray-400 mt-4">Didn't receive a code?
                <a href="{{ route('login') }}" class="text-blue-600">Try logging in again</a>
            </p>
        </div>
    </div>
</div>

<script>
const digits = document.querySelectorAll('.digit');
digits.forEach((input, i) => {
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !input.value && i > 0) digits[i-1].focus();
    });
    input.addEventListener('input', (e) => {
        input.value = input.value.replace(/[^0-9]/g, '').slice(-1);
        if (input.value && i < 5) digits[i+1].focus();
        if ([...digits].every(d => d.value)) {
            document.getElementById('full-code').value = [...digits].map(d=>d.value).join('');
            document.getElementById('tfa-form').submit();
        }
    });
    input.addEventListener('paste', (e) => {
        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        text.split('').forEach((ch, idx) => { if(digits[idx]) digits[idx].value = ch; });
        digits[Math.min(text.length, 5)].focus();
        e.preventDefault();
    });
});
digits[0].focus();
</script>
</body>
</html>
