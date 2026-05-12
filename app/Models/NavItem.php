<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    protected $table    = 'nav_items';
    protected $fillable = ['nav_key', 'label', 'icon_svg', 'sort_order', 'is_active'];
    protected $casts    = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function roles()
    {
        return $this->hasMany(NavItemRole::class, 'nav_key', 'nav_key');
    }

    // ── Query helpers ─────────────────────────────────────────────────────────

    /**
     * All active items with their role rows, ordered for the super-admin grid.
     */
    public static function allWithRoles(): Collection
    {
        return static::with('roles')->where('is_active', true)->orderBy('sort_order')->get();
    }

    /**
     * All items (including inactive) for the super-admin management list.
     */
    public static function allForAdmin(): Collection
    {
        return static::with('roles')->orderBy('sort_order')->get();
    }

    /**
     * Return NavItemRole rows visible to $role for user $user.
     * Applies is_active, is_visible, and optional required_permission checks.
     * Per-request static cache — safe because roles never change mid-request.
     */
    public static function getForRole(string $role, $user): Collection
    {
        static $cache = [];
        $cacheKey = $role . '_' . ($user->id ?? 0);

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $rows = NavItemRole::with('navItem')
            ->join('nav_items', 'nav_items.nav_key', '=', 'nav_item_roles.nav_key')
            ->where('nav_items.is_active', true)
            ->where('nav_item_roles.role_name', $role)
            ->where('nav_item_roles.is_visible', true)
            ->orderBy('nav_items.sort_order')
            ->select('nav_item_roles.*')
            ->get()
            ->filter(function (NavItemRole $nir) use ($user) {
                if ($nir->required_permission && !$user->can($nir->required_permission)) {
                    return false;
                }
                return true;
            })
            ->values();

        $cache[$cacheKey] = $rows;
        return $rows;
    }
}
