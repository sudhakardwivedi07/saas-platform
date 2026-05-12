<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavItemRole extends Model
{
    protected $table    = 'nav_item_roles';
    protected $fillable = [
        'nav_key', 'role_name', 'route_name',
        'active_route_pattern', 'required_permission', 'is_visible',
    ];
    protected $casts = ['is_visible' => 'boolean'];

    public function navItem()
    {
        return $this->belongsTo(NavItem::class, 'nav_key', 'nav_key');
    }
}
