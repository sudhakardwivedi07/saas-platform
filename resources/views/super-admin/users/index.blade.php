@extends('layouts.superadmin.app')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')

<form method="GET" action="{{ route('superadmin.users.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="form-control w-64 py-2.5">
    <select name="company_id" class="form-control w-52 py-2.5">
        <option value="">All Companies</option>
        @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
        @endforeach
    </select>
    <select name="status" class="form-control w-36 py-2.5">
        <option value="">All Statuses</option>
        <option value="active"   {{ request('status')==='active'   ? 'selected':'' }}>Active</option>
        <option value="inactive" {{ request('status')==='inactive' ? 'selected':'' }}>Inactive</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary ml-auto">
        <span class="material-icons text-sm">person_add</span> Add User
    </a>
</form>

<div class="card">
    <div class="card-header">
        <h3>All Users <span class="text-slate-400 font-normal">({{ $users->total() }})</span></h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Company</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-slate-400">{{ $user->id }}</td>
                    <td>
                        <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                        <p class="text-xs text-slate-400">{{ $user->email }}</p>
                    </td>
                    <td>
                        @if($user->is_superadmin)
                            <span class="badge badge-yellow">SuperAdmin</span>
                        @else
                            {{ $user->company?->company_name ?? '—' }}
                        @endif
                    </td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="badge badge-gray">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <span class="badge {{ $user->status === 'active' ? 'badge-green' : 'badge-red' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>
                    <td class="text-slate-400 text-xs">{{ $user->last_login?->diffForHumans() ?? 'Never' }}</td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-secondary text-xs py-1 px-2">
                                <span class="material-icons text-sm">edit</span>
                            </a>
                            @unless($user->is_superadmin)
                            <form method="POST" action="{{ route('superadmin.users.toggle-status', $user->id) }}">
                                @csrf
                                <button class="btn {{ $user->status==='active'?'btn-secondary':'btn-primary' }} text-xs py-1 px-2"
                                        title="{{ $user->status==='active'?'Deactivate':'Activate' }}">
                                    <span class="material-icons text-sm">{{ $user->status==='active'?'block':'check_circle' }}</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger text-xs py-1 px-2"><span class="material-icons text-sm">delete</span></button>
                            </form>
                            @endunless
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-slate-400">
                        <span class="material-icons text-4xl block mb-2">people</span>
                        No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $users->withQueryString()->links('components.pagination') }}
    </div>
</div>
@endsection
