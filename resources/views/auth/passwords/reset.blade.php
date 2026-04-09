<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8fafc}</style>
</head>
<body>
<div style="width:100%;max-width:26rem;padding:1rem">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-1">Set new password</h2>
            <p class="text-sm text-gray-500 mb-5">Choose a strong password for your account.</p>

            @if($errors->any())
            <div class="alert alert-error mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{ $email }}" readonly style="opacity:.6">
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'error' : '' }}"
                           minlength="8" required autofocus>
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
