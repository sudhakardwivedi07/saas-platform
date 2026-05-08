<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends AccountBaseController
{
    protected array $systemRoles = ['admin', 'super_admin'];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:60|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(Reply::successWithData('Role created.', ['role_id' => $role->id]));
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, $this->systemRoles)) {
            return response()->json(Reply::error('System roles cannot be modified.'));
        }

        $request->validate([
            'name'        => 'required|string|max:60|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return response()->json(Reply::success('Role updated.'));
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, $this->systemRoles)) {
            return response()->json(Reply::error('System roles cannot be deleted.'));
        }

        $role->delete();

        return response()->json(Reply::success('Role deleted.'));
    }
}
