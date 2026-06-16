<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\NavItemRole;
use Illuminate\Support\Facades\DB;
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
            'name'          => 'required|string|max:60|unique:roles,name',
            'alias'         => 'nullable|string|max:100',
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'alias' => $request->alias ?: null, 'guard_name' => 'web']);

        $role->syncPermissions($request->permissions);

        // Assign nav_item_roles using explicit visibility rules.
        // clients/finance require admin/manager roles so custom roles must never see them.
        $alwaysVisible  = ['dashboard', 'my_account', 'support'];
        $neverVisible   = ['my_financials', 'clients', 'finance'];
        $conditionalNav = [
            'users'      => ['view_users'],
            'billing'    => ['view_billing'],
            'insights'   => ['view_reports', 'view_quickbooks', 'connect_quickbooks', 'sync_quickbooks'],
            'quickbooks' => ['view_quickbooks', 'connect_quickbooks', 'sync_quickbooks'],
            'settings'   => ['view_settings', 'view_settings_email', 'view_settings_plans',
                             'view_settings_roles', 'view_settings_sms',
                             'view_settings_insight_limits', 'view_settings_payment',
                             'view_settings_quickbooks'],
        ];

        $adminItems = DB::table('nav_item_roles as nir')
            ->join('nav_items as ni', 'ni.nav_key', '=', 'nir.nav_key')
            ->where('nir.role_name', 'admin')
            ->where('ni.is_active', true)
            ->get(['nir.nav_key', 'nir.required_permission', 'nir.route_name', 'nir.active_route_pattern']);

        foreach ($adminItems as $item) {
            if (in_array($item->nav_key, $neverVisible)) {
                $visible = 0;
            } elseif (in_array($item->nav_key, $alwaysVisible)) {
                $visible = 1;
            } elseif (isset($conditionalNav[$item->nav_key])) {
                $visible = count(array_intersect($conditionalNav[$item->nav_key], $request->permissions)) > 0 ? 1 : 0;
            } else {
                $visible = 0;
            }

            NavItemRole::create([
                'nav_key'              => $item->nav_key,
                'role_name'            => $role->name,
                'is_visible'           => $visible,
                'required_permission'  => $item->required_permission,
                'route_name'           => $item->route_name,
                'active_route_pattern' => $item->active_route_pattern,
            ]);
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
            'alias'       => 'nullable|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name, 'alias' => $request->alias ?: null]);
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
