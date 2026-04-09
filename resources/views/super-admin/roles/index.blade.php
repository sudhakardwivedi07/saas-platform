@extends('layouts.superadmin.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary">
        <span class="material-icons text-sm">add</span> New Role
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Roles <span class="text-slate-400 font-normal">({{ $roles->count() }})</span></h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td class="font-semibold text-slate-800">{{ $role->name }}</td>
                    <td><span class="badge badge-gray">{{ $role->permissions_count }} permissions</span></td>
                    <td><span class="badge badge-green">{{ $role->users_count }} users</span></td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('superadmin.roles.edit', $role) }}" class="btn btn-secondary text-xs py-1 px-2">
                                <span class="material-icons text-sm">edit</span>
                            </a>
                            @unless(in_array($role->name, ['super_admin','client_admin']))
                            <form method="POST" action="{{ route('superadmin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger text-xs py-1 px-2"><span class="material-icons text-sm">delete</span></button>
                            </form>
                            @endunless
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-12 text-slate-400">No roles found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
