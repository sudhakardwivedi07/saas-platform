<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NavMenuSetting extends Model
{
    protected $table = 'nav_menu_settings';

    protected $fillable = ['nav_key', 'role_name', 'is_visible'];

    protected $casts = ['is_visible' => 'boolean'];

    /**
     * All nav items with their label, icon, and which roles can ever see them
     * (this is the maximum possible set — super admin can only hide within this set).
     */
    public static function navItems(): array
    {
        return [
            'dashboard'    => ['label' => 'Dashboard',            'roles' => ['admin', 'manager', 'client']],
            'clients'      => ['label' => 'Clients',              'roles' => ['admin', 'manager']],
            'my_companies' => ['label' => 'My Companies',         'roles' => ['client']],
            'billing'      => ['label' => 'Subscription & Usage', 'roles' => ['admin']],
            'settings'     => ['label' => 'Settings',             'roles' => ['admin', 'manager']],
            'my_financials'=> ['label' => 'My Financials',        'roles' => ['admin']],
            'insights'     => ['label' => 'Insights',             'roles' => ['admin', 'manager', 'client']],
            'quickbooks'   => ['label' => 'QuickBooks',           'roles' => ['admin', 'manager', 'client']],
            'users'        => ['label' => 'Users',                'roles' => ['admin', 'manager']],
            'finance'      => ['label' => 'Finance',              'roles' => ['admin']],
            'support'      => ['label' => 'Support',              'roles' => ['admin', 'manager']],
            'my_account'   => ['label' => 'My Account',           'roles' => ['admin', 'manager', 'client']],
        ];
    }

    /**
     * Returns a flat map of [nav_key => bool] for the given role.
     * Missing DB rows are treated as visible (default on).
     * Result is cached per-request.
     */
    public static function visibilityForRole(string $role): array
    {
        static $cache = [];

        if (isset($cache[$role])) {
            return $cache[$role];
        }

        $rows = static::where('role_name', $role)
            ->pluck('is_visible', 'nav_key')
            ->toArray();

        // Merge with defaults (true for each item that can be visible for this role)
        $defaults = collect(static::navItems())
            ->filter(fn($item) => in_array($role, $item['roles']))
            ->mapWithKeys(fn($item, $key) => [$key => true])
            ->toArray();

        $cache[$role] = array_merge($defaults, $rows);

        return $cache[$role];
    }

    /**
     * Check if a single nav key is visible for a role.
     */
    public static function isVisible(string $navKey, string $role): bool
    {
        $map = static::visibilityForRole($role);
        return $map[$navKey] ?? true;
    }

    /**
     * Persist super admin changes. Upserts a batch of [nav_key, role_name] => is_visible.
     */
    public static function saveSettings(array $settings): void
    {
        foreach ($settings as $navKey => $roles) {
            foreach (static::navItems() as $key => $item) {
                foreach ($item['roles'] as $role) {
                    static::updateOrCreate(
                        ['nav_key' => $key, 'role_name' => $role],
                        ['is_visible' => isset($settings[$key][$role]) && $settings[$key][$role]]
                    );
                }
            }
        }
    }
}
