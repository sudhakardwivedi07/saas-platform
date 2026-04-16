@extends('layouts.superadmin.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="ph">
    <div>
        <h1>Roles &amp; Permissions</h1>
        <p>Manage access control roles</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Role
        </a>
    </div>
</div>

<div class="tc">
    <div class="tc-head">
        <div style="font-size:14px;font-weight:600">Roles <span style="color:var(--text-3);font-weight:400">({{ $roles->count() }})</span></div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Role Name</th>
                <th>Permissions</th>
                <th>Users</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
            <tr>
                <td style="font-weight:600">{{ $role->name }}</td>
                <td><span class="badge b-gray">{{ $role->permissions_count }} permissions</span></td>
                <td><span class="badge b-blue">{{ $role->users_count }} users</span></td>
                <td>
                    <div class="dm-wrap" data-menu="role-{{ $role->id }}">
                        <button class="dot-menu" onclick="toggleMenu('role-{{ $role->id }}')">···</button>
                        <div class="dm-popup" id="role-{{ $role->id }}">
                            <a href="{{ route('superadmin.roles.edit', $role) }}" class="dm-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Role
                            </a>
                            @unless(in_array($role->name, ['super_admin','client_admin']))
                            <div class="dm-div"></div>
                            <form method="POST" action="{{ route('superadmin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dm-item dm-danger" style="width:100%;text-align:left;background:none;border:none;font-family:inherit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
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
                <td colspan="4" style="text-align:center;color:var(--text-3);padding:40px">No roles found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
