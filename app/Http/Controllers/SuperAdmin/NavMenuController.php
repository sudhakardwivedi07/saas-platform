<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use App\Models\NavItem;
use App\Models\NavItemRole;
use Illuminate\Http\Request;

class NavMenuController extends AccountBaseController
{
    private const ROLES = ['admin', 'manager', 'client'];

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $navItems = NavItem::allForAdmin();
        $roles    = self::ROLES;

        return view('super-admin.nav-menu.index', compact('navItems', 'roles'));
    }

    // ── Save visibility toggles ───────────────────────────────────────────────

    public function update(Request $request)
    {
        $navKeys = NavItem::pluck('nav_key')->toArray();

        foreach ($navKeys as $key) {
            foreach (self::ROLES as $role) {
                $nir = NavItemRole::where('nav_key', $key)
                                  ->where('role_name', $role)
                                  ->first();
                if ($nir) {
                    $nir->update([
                        'is_visible' => (bool) $request->input("nav.{$key}.{$role}", false),
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Menu visibility saved successfully.']);
    }

    // ── Create a new nav item ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'nav_key'  => 'required|alpha_dash|max:50|unique:nav_items,nav_key',
            'label'    => 'required|string|max:100',
            'icon_svg' => 'nullable|string',
            'roles'    => 'required|array|min:1',
            'roles.*.role_name'           => 'required|in:admin,manager,client',
            'roles.*.route_name'          => 'required|string|max:150',
            'roles.*.active_route_pattern'=> 'nullable|string|max:250',
            'roles.*.required_permission' => 'nullable|string|max:100',
        ]);

        $maxSort = NavItem::max('sort_order') ?? 0;

        $item = NavItem::create([
            'nav_key'    => $request->nav_key,
            'label'      => $request->label,
            'icon_svg'   => $request->icon_svg ?: $this->defaultIcon(),
            'sort_order' => $maxSort + 1,
            'is_active'  => true,
        ]);

        foreach ($request->roles as $roleCfg) {
            NavItemRole::create([
                'nav_key'               => $item->nav_key,
                'role_name'             => $roleCfg['role_name'],
                'route_name'            => $roleCfg['route_name'],
                'active_route_pattern'  => $roleCfg['active_route_pattern'] ?? '',
                'required_permission'   => $roleCfg['required_permission'] ?? null,
                'is_visible'            => true,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Menu item '{$item->label}' created successfully.",
        ]);
    }

    // ── Update label, icon, and role assignments ──────────────────────────────

    public function updateItem(Request $request, string $navKey)
    {
        $item = NavItem::where('nav_key', $navKey)->firstOrFail();

        $request->validate([
            'label'                            => 'required|string|max:100',
            'icon_svg'                         => 'nullable|string',
            'roles'                            => 'sometimes|array',
            'roles.*.role_name'                => 'required|in:admin,manager,client',
            'roles.*.enabled'                  => 'required|boolean',
            'roles.*.route_name'               => 'required_if:roles.*.enabled,true|nullable|string|max:150',
            'roles.*.active_route_pattern'     => 'nullable|string|max:250',
            'roles.*.required_permission'      => 'nullable|string|max:100',
        ]);

        // Update basic fields
        $item->update([
            'label'    => $request->label,
            'icon_svg' => $request->icon_svg ?: $item->icon_svg,
        ]);

        // Sync role assignments if provided
        if ($request->has('roles')) {
            foreach ($request->roles as $roleCfg) {
                $role    = $roleCfg['role_name'];
                $enabled = (bool) $roleCfg['enabled'];

                if ($enabled) {
                    NavItemRole::updateOrCreate(
                        ['nav_key' => $navKey, 'role_name' => $role],
                        [
                            'route_name'           => $roleCfg['route_name'],
                            'active_route_pattern' => $roleCfg['active_route_pattern'] ?? '',
                            'required_permission'  => $roleCfg['required_permission'] ?? null,
                        ]
                    );
                } else {
                    // Remove this role's assignment
                    NavItemRole::where('nav_key', $navKey)->where('role_name', $role)->delete();
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Item updated successfully.']);
    }

    // ── Toggle active/inactive ────────────────────────────────────────────────

    public function toggleActive(string $navKey)
    {
        $item = NavItem::where('nav_key', $navKey)->firstOrFail();
        $item->update(['is_active' => !$item->is_active]);

        $state = $item->is_active ? 'enabled' : 'disabled';

        return response()->json([
            'status'    => 'success',
            'message'   => "'{$item->label}' {$state}.",
            'is_active' => $item->is_active,
        ]);
    }

    // ── Delete a nav item ─────────────────────────────────────────────────────

    public function destroy(string $navKey)
    {
        $item = NavItem::where('nav_key', $navKey)->firstOrFail();
        $label = $item->label;
        $item->delete(); // cascades to nav_item_roles

        return response()->json(['status' => 'success', 'message' => "'{$label}' deleted."]);
    }

    // ── Reorder ───────────────────────────────────────────────────────────────

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $sort => $navKey) {
            NavItem::where('nav_key', $navKey)->update(['sort_order' => (int) $sort + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Order saved.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function defaultIcon(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    }
}
