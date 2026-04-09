<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $branding ? (company()?->app_name ?? config('app.name')) : ($setting?->global_app_name ?? config('app.name')) }}</title>

    @if($branding?->favicon)
        <link rel="icon" href="{{ asset('storage/' . $branding->favicon) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: {{ $branding?->primary_color ?? $setting?->primary_color ?? '#2563eb' }};
            --font: '{{ $branding?->font_family ?? 'Inter' }}', sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font);
            min-height: 100vh;
            display: flex;
        }

        /* Left panel — branding / illustration */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('{{ $branding?->login_background ? asset("storage/" . $branding->login_background) : "" }}') center/cover no-repeat;
            opacity: 0.15;
        }
        .left-panel-content { position: relative; text-align: center; }
        .left-panel img.logo { height: 56px; margin-bottom: 2rem; object-fit: contain; }
        .left-panel h1 { font-size: 2rem; font-weight: 700; margin-bottom: 1rem; }
        .left-panel p  { font-size: 1rem; opacity: .85; max-width: 360px; line-height: 1.6; }

        /* Right panel — form */
        .right-panel {
            width: 460px;
            flex-shrink: 0;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
        }
        .form-container { width: 100%; max-width: 380px; }
        .form-container h2 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: .4rem; }
        .form-container .subtitle { color: #64748b; font-size: .875rem; margin-bottom: 2rem; }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: .875rem; font-weight: 500; color: #374151; margin-bottom: .375rem; }
        .form-group input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: .95rem;
            color: #1e293b;
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent);
            background: #fff;
        }
        .form-group input.error { border-color: #ef4444; }
        .error-text { color: #ef4444; font-size: .8rem; margin-top: .25rem; display: block; }

        .form-footer { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .form-footer label { display: flex; align-items: center; gap: .5rem; font-size: .875rem; color: #64748b; cursor: pointer; }
        .form-footer a { font-size: .875rem; color: var(--primary); text-decoration: none; font-weight: 500; }
        .form-footer a:hover { text-decoration: underline; }

        .btn-submit {
            width: 100%;
            padding: .8rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
        }
        .btn-submit:hover  { opacity: .9; }
        .btn-submit:active { transform: scale(.99); }

        .divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; color: #94a3b8; font-size: .8rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        .register-link { text-align: center; margin-top: 1.5rem; font-size: .875rem; color: #64748b; }
        .register-link a { color: var(--primary); font-weight: 600; text-decoration: none; }

        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }

        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

    {{-- Left Branding Panel --}}
    <div class="left-panel">
        <div class="left-panel-content">
            @if($branding?->logo)
                <img src="{{ asset('storage/' . $branding->logo) }}" class="logo" alt="Logo">
            @endif
            <h1>{{ $branding?->login_heading ?? 'Welcome back' }}</h1>
            <p>{{ $branding?->login_subheading ?? 'Sign in to your account to continue.' }}</p>
        </div>
    </div>

    {{-- Right Form Panel --}}
    <div class="right-panel">
        <div class="form-container">
            <h2>Sign In</h2>
            <p class="subtitle">Enter your credentials to access your account</p>

            {{-- Error messages --}}
            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="you@company.com"
                           class="{{ $errors->has('email') ? 'error' : '' }}"
                           autocomplete="email" autofocus required>
                    @error('email') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           class="{{ $errors->has('password') ? 'error' : '' }}"
                           autocomplete="current-password" required>
                    @error('password') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="form-footer">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            @if($setting?->allow_registration)
                <div class="register-link">
                    Don't have an account? <a href="{{ route('register') }}">Create one free</a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
