<?php

namespace Database\Seeders;

use App\Models\NavItem;
use App\Models\NavItemRole;
use Illuminate\Database\Seeder;

class NavItemSeeder extends Seeder
{
    /**
     * Seed the nav_items and nav_item_roles tables.
     * Uses updateOrCreate so this is safe to run multiple times.
     */
    public function run(): void
    {
        $items = self::definitions();

        foreach ($items as $sort => $def) {
            $item = NavItem::updateOrCreate(
                ['nav_key' => $def['nav_key']],
                [
                    'label'      => $def['label'],
                    'icon_svg'   => $def['icon_svg'],
                    'sort_order' => $sort + 1,
                    'is_active'  => true,
                ]
            );

            foreach ($def['roles'] as $role => $cfg) {
                NavItemRole::updateOrCreate(
                    ['nav_key' => $item->nav_key, 'role_name' => $role],
                    [
                        'route_name'            => $cfg['route'],
                        'active_route_pattern'  => $cfg['active'] ?? '',
                        'required_permission'   => $cfg['permission'] ?? null,
                        'is_visible'            => $cfg['visible'] ?? true,
                    ]
                );
            }
        }
    }

    // ── Item definitions ──────────────────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'nav_key'  => 'dashboard',
                'label'    => 'Dashboard',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'dashboard', 'active' => 'dashboard'],
                    'manager' => ['route' => 'dashboard', 'active' => 'dashboard'],
                    'client'  => ['route' => 'dashboard', 'active' => 'dashboard'],
                ],
            ],
            [
                'nav_key'  => 'clients',
                'label'    => 'Clients',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'clients.index', 'active' => 'clients.*'],
                    'manager' => ['route' => 'clients.index', 'active' => 'clients.*'],
                ],
            ],
            [
                'nav_key'  => 'my_companies',
                'label'    => 'My Companies',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>',
                'roles' => [
                    'client' => ['route' => 'my-companies.index', 'active' => 'my-companies.*'],
                ],
            ],
            [
                'nav_key'  => 'billing',
                'label'    => 'Subscription & Usage',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>',
                'roles' => [
                    // Subscription is surfaced as a tab in My Account — hidden from sidebar.
                    'admin' => ['route' => 'billing.index', 'active' => 'billing.*', 'permission' => 'view_billing', 'visible' => false],
                ],
            ],
            [
                'nav_key'  => 'my_financials',
                'label'    => 'My Financials',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
                'roles' => [
                    'admin' => ['route' => 'my-financials.index', 'active' => 'my-financials.*'],
                ],
            ],
            [
                'nav_key'  => 'insights',
                'label'    => 'Insights',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'partner-insights.index', 'active' => 'partner-insights.*'],
                    'manager' => ['route' => 'partner-insights.index', 'active' => 'partner-insights.*'],
                    'client'  => ['route' => 'insights.index',         'active' => 'insights.*'],
                ],
            ],
            [
                'nav_key'  => 'quickbooks',
                'label'    => 'Connections',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'quickbooks.index', 'active' => 'quickbooks.*,firm-quickbooks.*'],
                    'manager' => ['route' => 'quickbooks.index', 'active' => 'quickbooks.*,firm-quickbooks.*'],
                    'client'  => ['route' => 'quickbooks.index', 'active' => 'quickbooks.*', 'permission' => 'view_quickbooks'],
                ],
            ],
            [
                'nav_key'  => 'users',
                'label'    => 'Users',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'users.index', 'active' => 'users.*', 'permission' => 'view_users'],
                    'manager' => ['route' => 'users.index', 'active' => 'users.*', 'permission' => 'view_users'],
                ],
            ],
            [
                'nav_key'  => 'finance',
                'label'    => 'Finance',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
                'roles' => [
                    'admin' => ['route' => 'finance.index', 'active' => 'finance.*'],
                ],
            ],
            [
                'nav_key'  => 'support',
                'label'    => 'Support',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'support.index', 'active' => 'support.*'],
                    'manager' => ['route' => 'support.index', 'active' => 'support.*'],
                ],
            ],
            [
                'nav_key'  => 'my_account',
                'label'    => 'My Account',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'account.index', 'active' => 'account.*,activity.*'],
                    'manager' => ['route' => 'account.index', 'active' => 'account.*,activity.*'],
                    'client'  => ['route' => 'account.index', 'active' => 'account.*,activity.*'],
                ],
            ],
            [
                'nav_key'  => 'settings',
                'label'    => 'Settings',
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
                'roles' => [
                    'admin'   => ['route' => 'settings.index', 'active' => 'settings.*', 'permission' => 'view_settings'],
                    'manager' => ['route' => 'settings.index', 'active' => 'settings.*', 'permission' => 'view_settings'],
                ],
            ],
        ];
    }
}
