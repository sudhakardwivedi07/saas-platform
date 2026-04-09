<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SuperAdmin') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; --font: 'Inter', sans-serif; }
        body  { font-family: var(--font); }
        .sa-sidebar { background: var(--primary); }
        .sa-nav-link { display:flex; align-items:center; gap:.75rem; padding:.6rem 1rem; color:rgba(255,255,255,.7); border-radius:.5rem; transition:all .15s; font-size:.875rem; text-decoration:none; }
        .sa-nav-link:hover, .sa-nav-link.active { background:rgba(255,255,255,.12); color:#fff; }
        .sa-nav-link .material-icons { font-size:1.25rem; }
        .stat-card { background:#fff; border-radius:.75rem; border:1px solid #e2e8f0; padding:1.25rem 1.5rem; }
        .stat-number { font-size:1.75rem; font-weight:700; color:#1e293b; }
        .stat-label  { font-size:.8rem; color:#64748b; text-transform:uppercase; letter-spacing:.05em; }
        .badge { display:inline-flex; align-items:center; padding:.2rem .6rem; border-radius:999px; font-size:.75rem; font-weight:500; }
        .badge-green  { background:#dcfce7; color:#16a34a; }
        .badge-red    { background:#fee2e2; color:#dc2626; }
        .badge-yellow { background:#fef9c3; color:#ca8a04; }
        .badge-gray   { background:#f1f5f9; color:#64748b; }
        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { padding:.75rem 1rem; text-align:left; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#64748b; border-bottom:2px solid #e2e8f0; }
        .data-table td { padding:.875rem 1rem; border-bottom:1px solid #f1f5f9; font-size:.875rem; color:#374151; vertical-align:middle; }
        .data-table tr:hover td { background:#f8fafc; }
        .btn { display:inline-flex; align-items:center; gap:.375rem; padding:.5rem 1rem; border-radius:.5rem; font-size:.875rem; font-weight:500; cursor:pointer; border:none; transition:all .15s; text-decoration:none; }
        .btn-primary { background:var(--accent); color:#fff; }
        .btn-primary:hover { background:#2563eb; }
        .btn-danger  { background:#ef4444; color:#fff; }
        .btn-danger:hover { background:#dc2626; }
        .btn-secondary { background:#f1f5f9; color:#374151; border:1px solid #e2e8f0; }
        .btn-secondary:hover { background:#e2e8f0; }
        .card { background:#fff; border-radius:.75rem; border:1px solid #e2e8f0; }
        .card-header { padding:1.25rem 1.5rem; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; }
        .card-header h3 { font-size:1rem; font-weight:600; color:#1e293b; }
        .card-body { padding:1.5rem; }
        .form-group { margin-bottom:1.25rem; }
        .form-label { display:block; font-size:.875rem; font-weight:500; color:#374151; margin-bottom:.375rem; }
        .form-control { width:100%; padding:.625rem .875rem; border:1.5px solid #e2e8f0; border-radius:.5rem; font-size:.875rem; color:#1e293b; background:#f8fafc; transition:border-color .2s; }
        .form-control:focus { outline:none; border-color:var(--accent); background:#fff; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100">
<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside class="sa-sidebar w-60 flex-shrink-0 flex flex-col text-white">
        <div class="px-5 py-5 border-b border-white/10">
            <p class="text-xs text-white/40 uppercase tracking-widest mb-1">SuperAdmin</p>
            <p class="font-bold text-lg">{{ config('app.name') }}</p>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <a href="{{ route('superadmin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <span class="material-icons">dashboard</span> Dashboard
            </a>

            <p class="px-3 pt-3 pb-1 text-xs text-white/30 uppercase tracking-widest">Management</p>

            <a href="{{ route('superadmin.companies.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.companies.*') ? 'active' : '' }}">
                <span class="material-icons">business</span> Companies
            </a>
            <a href="{{ route('superadmin.users.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                <span class="material-icons">people</span> Users
            </a>
            <a href="{{ route('superadmin.roles.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.roles.*') ? 'active' : '' }}">
                <span class="material-icons">admin_panel_settings</span> Roles & Permissions
            </a>

            <p class="px-3 pt-3 pb-1 text-xs text-white/30 uppercase tracking-widest">Billing</p>

            <a href="{{ route('superadmin.packages.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.packages.*') ? 'active' : '' }}">
                <span class="material-icons">inventory_2</span> Packages
            </a>
            <a href="{{ route('superadmin.payment_settings') }}" class="sa-nav-link {{ request()->routeIs('superadmin.payment_settings*') ? 'active' : '' }}">
                <span class="material-icons">payment</span> Payment Gateway
            </a>

            <p class="px-3 pt-3 pb-1 text-xs text-white/30 uppercase tracking-widest">Content</p>

            <a href="{{ route('superadmin.email-templates.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.email-templates.*') ? 'active' : '' }}">
                <span class="material-icons">email</span> Email Templates
            </a>

            <p class="px-3 pt-3 pb-1 text-xs text-white/30 uppercase tracking-widest">System</p>

            <a href="{{ route('superadmin.settings') }}" class="sa-nav-link {{ request()->routeIs('superadmin.settings*') ? 'active' : '' }}">
                <span class="material-icons">settings</span> Global Settings
            </a>
        </nav>

        <div class="px-4 py-4 border-t border-white/10">
            <p class="text-xs text-white/50 truncate">{{ auth()->user()?->email }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="sa-nav-link w-full text-left">
                    <span class="material-icons">logout</span> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-slate-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
            <h1 class="text-base font-semibold text-slate-800">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span class="material-icons text-base">person</span>
                {{ auth()->user()?->name }}
            </div>
        </header>

        {{-- Alerts --}}
        <div class="px-6 pt-4 space-y-2">
            @foreach(['success','error','info'] as $t)
                @if(session($t))
                <div class="px-4 py-3 rounded-lg text-sm flex items-center gap-2
                    {{ $t==='success' ? 'bg-green-50 border border-green-200 text-green-800' :
                       ($t==='error'  ? 'bg-red-50 border border-red-200 text-red-800'       :
                                        'bg-blue-50 border border-blue-200 text-blue-800') }}">
                    <span class="material-icons text-base">{{ $t==='success' ? 'check_circle' : ($t==='error' ? 'error' : 'info') }}</span>
                    {{ session($t) }}
                </div>
                @endif
            @endforeach
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </div>
    </main>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
function showToast(msg, type='success') {
    const colors = {success:'bg-green-600', error:'bg-red-600', info:'bg-blue-600'};
    const t = document.createElement('div');
    t.className = `fixed bottom-4 right-4 z-50 text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
    t.innerHTML = `<span class="material-icons text-lg">${type==='success'?'check_circle':'error'}</span>${msg}`;
    document.body.appendChild(t);
    setTimeout(()=>t.remove(), 4000);
}
async function apiCall(url, method='POST', body={}) {
    const r = await fetch(url, {method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:JSON.stringify(body)});
    return r.json();
}
</script>
</body>
</html>
