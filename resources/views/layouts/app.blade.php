<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="{{ auth()->user()?->dark_theme ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $appName ?? config('app.name'))</title>

    {{-- Favicon --}}
    @if($branding?->favicon)
        <link rel="icon" href="{{ asset('storage/' . $branding->favicon) }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    {{-- Base CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- White-label CSS variables --}}
    <style>
        :root {
            --primary:    {{ $branding->primary_color    ?? '#2563eb' }};
            --secondary:  {{ $branding->secondary_color  ?? '#64748b' }};
            --accent:     {{ $branding->accent_color     ?? '#0ea5e9' }};
            --font-family: '{{ $branding->font_family ?? 'Inter' }}', sans-serif;
        }
        body { font-family: var(--font-family); }
        .btn-primary  { background-color: var(--primary) !important; border-color: var(--primary) !important; }
        .text-primary { color: var(--primary) !important; }
        .bg-primary   { background-color: var(--primary) !important; }
        .border-primary { border-color: var(--primary) !important; }
        .sidebar-brand { background-color: var(--primary); }
        .nav-link.active { color: var(--primary); border-left: 3px solid var(--primary); }
        a { color: var(--primary); }
        a:hover { color: var(--accent); }
    </style>

    {{-- Tenant custom CSS --}}
    @if($branding?->custom_css)
    <style>{!! $branding->custom_css !!}</style>
    @endif

    @stack('styles')
</head>
<body class="bg-gray-50">

{{-- Impersonation Banner --}}
@if(session('impersonating_company'))
<div class="bg-amber-500 text-white text-center py-2 text-sm font-medium">
    ⚠️ You are viewing as <strong>{{ $tenantCompany?->company_name }}</strong>.
    <a href="{{ route('superadmin.stop_impersonation') }}" class="underline ml-2 font-bold">Exit ↩</a>
</div>
@endif

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
    <aside class="sidebar-brand w-64 flex-shrink-0 flex flex-col text-white" id="sidebar">
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            @if($branding?->logo)
                <img src="{{ asset('storage/' . $branding->logo) }}" alt="Logo" class="h-8 object-contain">
            @else
                <span class="text-xl font-bold">{{ $appName }}</span>
            @endif
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">dashboard</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('documents.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('documents.*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">folder</span>
                <span>Documents</span>
            </a>

            <a href="{{ route('quickbooks.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('quickbooks.*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">sync</span>
                <span>QuickBooks</span>
            </a>

            <a href="{{ route('email-templates.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('email-templates.*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">email</span>
                <span>Email Templates</span>
            </a>

            <a href="{{ route('billing.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('billing.*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">credit_card</span>
                <span>Billing</span>
            </a>

            @canany(['manage_company', 'add_company'])
            <a href="{{ route('my-companies.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('my-companies.*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">business</span>
                <span>My Companies</span>
            </a>
            @endcanany

            @can('view_plan')
            <a href="{{ route('plans.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('plans.*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">loyalty</span>
                <span>My Plans</span>
            </a>
            @endcan

            {{-- Settings group --}}
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-white/40">Settings</div>

            <a href="{{ route('settings.branding') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('settings.branding*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">palette</span>
                <span>Branding</span>
            </a>

            <a href="{{ route('settings.payment-gateway') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('settings.payment-gateway*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">credit_card</span>
                <span>Payment Gateway</span>
            </a>

            <a href="{{ route('settings.email') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('settings.email*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">alternate_email</span>
                <span>Email SMTP</span>
            </a>

            <a href="{{ route('settings.sms') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition
                      {{ request()->routeIs('settings.sms*') ? 'bg-white/20 text-white font-medium active' : '' }}">
                <span class="material-icons text-xl">sms</span>
                <span>SMS Settings</span>
            </a>
        </nav>

        {{-- User footer --}}
        <div class="px-4 py-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <img src="{{ $authUser->image_url }}" class="w-9 h-9 rounded-full object-cover" alt="Avatar">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ $authUser->name }}</p>
                    <p class="text-xs text-white/50 truncate">{{ $authUser->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-white/60 hover:text-white" title="Logout">
                        <span class="material-icons text-xl">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── Main Content ─────────────────────────────────────────────── --}}
    <main class="flex-1 flex flex-col overflow-hidden">

        {{-- Top Bar --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-4">
                <button id="sidebar-toggle" class="text-gray-500 hover:text-gray-700 md:hidden">
                    <span class="material-icons">menu</span>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-3">
                {{-- Notifications --}}
                <button class="relative text-gray-500 hover:text-gray-700">
                    <span class="material-icons">notifications</span>
                </button>

                {{-- Profile --}}
                <a href="{{ route('profile') }}" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                    <img src="{{ $authUser->image_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                    <span class="hidden md:inline font-medium">{{ $authUser->name }}</span>
                </a>
            </div>
        </header>

        {{-- Flash Messages --}}
        <div class="px-6 pt-4">
            @foreach (['success', 'error', 'info', 'warning'] as $type)
                @if(session($type))
                <div class="alert alert-{{ $type }} mb-3 px-4 py-3 rounded-lg flex items-center gap-2
                    {{ $type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : '' }}
                    {{ $type === 'error'   ? 'bg-red-50 border border-red-200 text-red-800' : '' }}
                    {{ $type === 'info'    ? 'bg-blue-50 border border-blue-200 text-blue-800' : '' }}
                    {{ $type === 'warning' ? 'bg-amber-50 border border-amber-200 text-amber-800' : '' }}">
                    <span class="material-icons text-lg">
                        {{ $type === 'success' ? 'check_circle' : ($type === 'error' ? 'error' : 'info') }}
                    </span>
                    {{ session($type) }}
                </div>
                @endif
            @endforeach
        </div>

        {{-- Page Content --}}
        <div class="flex-1 overflow-y-auto px-6 py-4">
            @yield('content')
        </div>
    </main>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')

<script>
// AJAX setup
document.addEventListener('DOMContentLoaded', () => {
    // Add CSRF to all fetch requests
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // Flash message auto-dismiss
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 4000);

    // Sidebar toggle
    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('hidden');
    });
});

// Global AJAX reply handler — mirrors reference project pattern
function handleReply(response) {
    if (response.status === 'success') {
        showToast(response.message || 'Done!', 'success');
        if (response.action === 'redirect') window.location.href = response.url;
        if (response.html) updateContent(response.html);
    } else {
        showToast(response.message || 'Something went wrong.', 'error');
        if (response.errors) showErrors(response.errors);
    }
}

function showToast(msg, type = 'success') {
    const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-blue-600' };
    const toast  = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 z-50 text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 ${colors[type]}`;
    toast.innerHTML = `<span class="material-icons text-lg">${type === 'success' ? 'check_circle' : 'error'}</span>${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
</script>
</body>
</html>
