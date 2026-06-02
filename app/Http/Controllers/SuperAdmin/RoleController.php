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
        $this->permissions = $this->groupedPermissions();
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
        $this->permissions = $this->groupedPermissions();
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
        abort_403(in_array($role->name, ['super_admin', 'admin']));
        $role->delete();
        return Reply::success('Role deleted.');
    }

    private function groupedPermissions(): \Illuminate\Support\Collection
    {
        return Permission::orderBy('name')->get()->groupBy(function ($p) {
            return match (true) {
                // Tab-visibility groups — checked first to avoid partial matches
                str_starts_with($p->name, 'view_settings_')  => 'Settings Tabs',
                str_starts_with($p->name, 'view_account_')   => 'My Account Tabs',
                str_contains($p->name, '_users')             => 'Users',
                str_contains($p->name, '_documents') ||
                    str_contains($p->name, 'upload_documents') ||
                    str_contains($p->name, 'download_documents') => 'Documents',
                str_contains($p->name, '_quickbooks') ||
                    str_contains($p->name, 'connect_quickbooks')  => 'QuickBooks',
                str_contains($p->name, '_email_templates') ||
                    str_contains($p->name, 'add_email_templates') => 'Email Templates',
                str_contains($p->name, '_settings') ||
                    str_contains($p->name, 'edit_branding')       => 'Settings',
                str_contains($p->name, '_billing') ||
                    str_contains($p->name, 'manage_payment_gateway') => 'Billing',
                str_contains($p->name, '_reports')             => 'Reports',
                str_contains($p->name, '_company') ||
                    str_contains($p->name, 'add_company') ||
                    str_contains($p->name, '_plan') ||
                    str_contains($p->name, 'add_plan')          => 'Companies & Plans',
                default                                         => 'Other',
            };
        });
    }
}
