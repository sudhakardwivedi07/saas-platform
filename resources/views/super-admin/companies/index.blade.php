@extends('layouts.superadmin.app')
@section('title', 'Firms')
@section('page-title', 'Firms')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div>
        <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0 0 2px;">Firms</h1>
        <p style="font-size:.85rem;color:#64748b;margin:0;">All registered companies on the platform</p>
    </div>
    <a href="{{ route('superadmin.companies.create') }}" class="btn btn-primary">+ Add Firm</a>
</div>

{{-- Table Card --}}
<div class="card" style="margin-top:1.25rem;">

    {{-- Toolbar --}}
    <div class="tc-head">
        <form method="GET" action="{{ route('superadmin.companies.index') }}" class="toolbar" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;flex:1;">
            <div class="srch-wrap">
                <svg class="srch-ico" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="srch-inp" type="text" name="search" value="{{ request('search') }}" placeholder="Search firms…">
            </div>
            <select class="sel" name="status">
                <option value="">All Statuses</option>
                <option value="active"    {{ request('status')==='active'    ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="trial"     {{ request('status')==='trial'     ? 'selected' : '' }}>Trial</option>
                <option value="inactive"  {{ request('status')==='inactive'  ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('superadmin.companies.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </form>
        <button class="btn btn-ghost btn-sm" type="button">Export</button>
    </div>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table class="tc">
            <thead>
                <tr>
                    <th>Firm</th>
                    <th>Plan</th>
                    <th>MRR</th>
                    <th>Users</th>
                    <th>Health</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                @php
                    $statusBadge = match($company->status) {
                        'active'    => 'b-green',
                        'suspended' => 'b-red',
                        'trial'     => 'b-amber',
                        default     => 'b-gray',
                    };
                    $healthDot = match($company->status) {
                        'active'    => 'b-green',
                        'suspended' => 'b-red',
                        'trial'     => 'b-amber',
                        default     => 'b-gray',
                    };
                    $initials = strtoupper(substr($company->company_name, 0, 2));
                @endphp
                <tr>
                    {{-- Firm --}}
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div class="firm-av">{{ $initials }}</div>
                            <div>
                                <div style="font-weight:600;color:#0f172a;font-size:.9rem;">{{ $company->company_name }}</div>
                                <div style="font-size:.775rem;color:#94a3b8;">{{ $company->company_email }}</div>
                            </div>
                        </div>
                    </td>
                    {{-- Plan --}}
                    <td style="color:#475569;font-size:.875rem;">
                        {{ $company->package?->name ?? '—' }}
                    </td>
                    {{-- MRR --}}
                    <td style="color:#475569;font-size:.875rem;">$0</td>
                    {{-- Users --}}
                    <td style="color:#475569;font-size:.875rem;">
                        {{ $company->users()->count() }}
                    </td>
                    {{-- Health dot --}}
                    <td>
                        <span class="bdot {{ $healthDot }}"></span>
                    </td>
                    {{-- Status badge --}}
                    <td>
                        <span class="badge {{ $statusBadge }}">{{ ucfirst($company->status) }}</span>
                    </td>
                    {{-- Joined --}}
                    <td style="color:#94a3b8;font-size:.8rem;">
                        {{ $company->created_at->format('M d, Y') }}
                    </td>
                    {{-- Actions dot menu --}}
                    <td>
                        <div class="dm-wrap">
                            <button class="dot-menu" type="button" onclick="toggleMenu(event, 'menu-{{ $company->id }}')">
                                <span></span><span></span><span></span>
                            </button>
                            <div class="dm-popup" id="menu-{{ $company->id }}">
                                <a class="dm-item dm-hi" href="{{ route('superadmin.companies.show', $company) }}">
                                    View Firm
                                </a>
                                <a class="dm-item" href="{{ route('superadmin.companies.edit', $company) }}">
                                    Edit
                                </a>
                                <div class="dm-div"></div>
                                <form method="POST" action="{{ route('superadmin.companies.impersonate', $company) }}">
                                    @csrf
                                    <button class="dm-item" type="submit">Login As</button>
                                </form>
                                @if($company->status === 'suspended')
                                <form method="POST" action="{{ route('superadmin.companies.activate', $company) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="dm-item" type="submit">Activate</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('superadmin.companies.suspend', $company) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="dm-item" type="submit">Suspend</button>
                                </form>
                                @endif
                                <div class="dm-div"></div>
                                <form method="POST" action="{{ route('superadmin.companies.destroy', $company) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($company->company_name) }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="dm-item dm-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
                        <div style="font-size:2rem;margin-bottom:.5rem;">🏢</div>
                        No firms found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($companies->hasPages())
    <div style="padding:.85rem 1.25rem;border-top:1px solid var(--border);">
        {{ $companies->withQueryString()->links('components.pagination') }}
    </div>
    @endif

</div>

<script>
function toggleMenu(e, id) {
    e.stopPropagation();
    document.querySelectorAll('.dm-popup').forEach(p => {
        if (p.id !== id) p.classList.remove('open');
    });
    document.getElementById(id).classList.toggle('open');
}
document.addEventListener('click', () => {
    document.querySelectorAll('.dm-popup').forEach(p => p.classList.remove('open'));
});
</script>

@endsection
