@extends('layouts.superadmin.app')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="ph">
    <div>
        <h1>Users</h1>
        <p>Manage all platform users</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            Add User
        </a>
    </div>
</div>

<div class="tc">
    <div class="tc-head">
        <form method="GET" action="{{ route('superadmin.users.index') }}" class="toolbar">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="srch-inp">
            </div>
            <select name="company_id" class="sel">
                <option value="">All Companies</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                @endforeach
            </select>
            <select name="status" class="sel">
                <option value="">All Statuses</option>
                <option value="active"   {{ request('status')==='active'   ? 'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')==='inactive' ? 'selected':'' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
        </form>
        <span class="badge b-gray">{{ $users->total() }} users</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Company</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td style="color:var(--text-3)">{{ $user->id }}</td>
                <td>
                    <div style="font-weight:600">{{ $user->name }}</div>
                    <div style="font-size:11.5px;color:var(--text-3)">{{ $user->email }}</div>
                </td>
                <td>
                    @if($user->is_superadmin)
                        <span class="badge b-orange">SuperAdmin</span>
                    @else
                        {{ $user->company?->company_name ?? '—' }}
                    @endif
                </td>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge b-gray">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="badge {{ $user->status === 'active' ? 'b-green' : 'b-red' }}">
                        <span class="bdot" style="background:{{ $user->status === 'active' ? 'var(--green)' : 'var(--red)' }}"></span>
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td style="color:var(--text-3);font-size:12px">{{ $user->last_login?->diffForHumans() ?? 'Never' }}</td>
                <td>
                    <div class="dm-wrap" data-menu="user-{{ $user->id }}">
                        <button class="dot-menu" onclick="toggleMenu('user-{{ $user->id }}')">···</button>
                        <div class="dm-popup" id="user-{{ $user->id }}">
                            <a href="{{ route('superadmin.users.edit', $user) }}" class="dm-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit User
                            </a>
                            @unless($user->is_superadmin)
                            <form method="POST" action="{{ route('superadmin.users.toggle-status', $user->id) }}">
                                @csrf
                                <button type="submit" class="dm-item" style="width:100%;text-align:left;background:none;border:none;font-family:inherit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <div class="dm-div"></div>
                            <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dm-item dm-danger" style="width:100%;text-align:left;background:none;border:none;font-family:inherit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                    Delete
                                </button>
                            </form>
                            @endunless
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:var(--text-3);padding:40px">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">
        {{ $users->withQueryString()->links('components.pagination') }}
    </div>
    @endif
</div>
@endsection
