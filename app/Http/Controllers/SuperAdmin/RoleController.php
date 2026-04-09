<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Roles & Permissions';
    }

    public function index()
    {
        $this->roles = Role::withCount('users', 'permissions')->orderBy('name')->get();
        return view('super-admin.roles.index', $this->data);
    }

    public function create()
    {
        $this->permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('_', $p->name, 2)[1] ?? 'general');
        return view('super-admin.roles.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return Reply::successWithData('Role created.', ['redirect' => route('superadmin.roles.index')]);
    }

    public function edit(int $id)
    {
        $this->role        = Role::with('permissions')->findOrFail($id);
        $this->permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('_', $p->name, 2)[1] ?? 'general');
        return view('super-admin.roles.edit', $this->data);
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name,' . $id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return Reply::success('Role updated.');
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);
        abort_403(in_array($role->name, ['super_admin', 'client_admin']));
        $role->delete();
        return Reply::success('Role deleted.');
    }
}
