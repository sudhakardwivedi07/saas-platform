{{-- ── Forgot Password ─────────────────────────────────────────── --}}
@php // resources/views/auth/passwords/forgot.blade.php @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8fafc}</style>
</head>
<body>
<div style="width:100%;max-width:26rem;padding:1rem">
    <div class="card">
        <div class="card-body">
            <div class="text-center mb-6">
                <span class="material-icons" style="font-size:3rem;color:#3b82f6">lock_reset</span>
                <h2 class="mt-2">Forgot your password?</h2>
                <p class="text-sm text-gray-500 mt-1">Enter your email and we'll send a reset link.</p>
            </div>

            @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if($errors->any())
            <div class="alert alert-error mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                           placeholder="you@company.com" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-4">
                <a href="{{ route('login') }}" class="text-blue-600">← Back to login</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
