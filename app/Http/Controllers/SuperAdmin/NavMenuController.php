<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use App\Models\NavMenuSetting;
use Illuminate\Http\Request;

class NavMenuController extends AccountBaseController
{
    public function index()
    {
        $navItems = NavMenuSetting::navItems();
        $roles    = ['admin', 'manager', 'client'];

        // Build current state: [nav_key][role_name] => bool
        $current = [];
        foreach ($navItems as $key => $item) {
            foreach ($item['roles'] as $role) {
                $current[$key][$role] = NavMenuSetting::isVisible($key, $role);
            }
        }

        return view('super-admin.nav-menu.index', compact('navItems', 'roles', 'current'));
    }

    public function update(Request $request)
    {
        $navItems = NavMenuSetting::navItems();
        $roles    = ['admin', 'manager', 'client'];

        foreach ($navItems as $key => $item) {
            foreach ($item['roles'] as $role) {
                $isVisible = (bool) $request->input("nav.{$key}.{$role}", false);
                NavMenuSetting::updateOrCreate(
                    ['nav_key' => $key, 'role_name' => $role],
                    ['is_visible' => $isVisible]
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Menu visibility saved successfully.']);
    }
}
