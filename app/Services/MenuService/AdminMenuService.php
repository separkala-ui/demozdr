<?php

declare(strict_types=1);

namespace App\Services\MenuService;

use App\Enums\Hooks\AdminFilterHook;
use App\Models\PettyCashLedger;
use App\Services\Content\ContentService;
use App\Support\Facades\Hook;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminMenuService
{
    /**
     * @var AdminMenuItem[][]
     */
    protected array $groups = [];

    /**
     * Add a menu item to the admin sidebar.
     *
     * @param  AdminMenuItem|array  $item  The menu item or configuration array
     * @param  string|null  $group  The group to add the item to
     *
     * @throws \InvalidArgumentException
     */
    public function addMenuItem(AdminMenuItem|array $item, ?string $group = null): void
    {
        $group = $group ?: __('Main');
        $menuItem = $this->createAdminMenuItem($item);
        if (! isset($this->groups[$group])) {
            $this->groups[$group] = [];
        }

        if ($menuItem->userHasPermission()) {
            $this->groups[$group][] = $menuItem;
        }
    }

    protected function createAdminMenuItem(AdminMenuItem|array $data): AdminMenuItem
    {
        if ($data instanceof AdminMenuItem) {
            return $data;
        }

        $menuItem = new AdminMenuItem();

        if (isset($data['children']) && is_array($data['children'])) {
            $data['children'] = array_map(
                function ($child) {
                    // Check if user is authenticated
                    $user = auth()->user();
                    if (! $user) {
                        return null;
                    }

                    // Handle permissions.
                    if (isset($child['permission'])) {
                        $child['permissions'] = $child['permission'];
                        unset($child['permission']);
                    }

                    $permissions = $child['permissions'] ?? [];
                    if (empty($permissions) || $user->hasAnyPermission((array) $permissions)) {
                        return $this->createAdminMenuItem($child);
                    }

                    return null;
                },
                $data['children']
            );

            // Filter out null values (items without permission).
            $data['children'] = array_filter($data['children']);
        }

        // Convert 'permission' to 'permissions' for consistency
        if (isset($data['permission'])) {
            $data['permissions'] = $data['permission'];
            unset($data['permission']);
        }

        // Handle route with params
        if (isset($data['route']) && isset($data['params'])) {
            $routeName = $data['route'];
            $params = $data['params'];

            if (is_array($params)) {
                $data['route'] = route($routeName, $params);
            } else {
                $data['route'] = route($routeName, [$params]);
            }
        }

        return $menuItem->setAttributes($data);
    }

    public function getMenu()
    {
        $this->addMenuItem([
            'label' => __('Dashboard'),
            'icon' => 'lucide:layout-dashboard',
            'route' => route('admin.dashboard'),
            'active' => Route::is('admin.dashboard'),
            'id' => 'dashboard',
            'priority' => 1,
            'permissions' => 'dashboard.view',
        ]);

        $this->registerPostTypesInMenu(null);

        $this->addMenuItem([
            'label' => __('Media Library'),
            'icon' => 'lucide:image',
            'route' => route('admin.media.index'),
            'active' => Route::is('admin.media.*'),
            'id' => 'media',
            'priority' => 35,
            'permissions' => 'media.view',
        ]);
        $this->addMenuItem([
            'label' => __('Modules'),
            'icon' => 'lucide:boxes',
            'route' => route('admin.modules.index'),
            'active' => Route::is('admin.modules.index'),
            'id' => 'modules',
            'priority' => 25,
            'permissions' => 'module.view',
        ], __('More'));

        $defaultLedgerId = null;
        $user = auth()->user();

        if ($user?->branch_id) {
            $defaultLedgerId = $user->branch_id;
        } elseif ($user && $user->hasRole(['Superadmin', 'Admin'])) {
            $defaultLedgerId = PettyCashLedger::query()->orderBy('branch_name')->value('id');
        }

        $chargeRoute = $defaultLedgerId
            ? route('admin.petty-cash.charge-request', $defaultLedgerId)
            : route('admin.petty-cash.index');

        $settlementRoute = $defaultLedgerId
            ? route('admin.petty-cash.settlement', $defaultLedgerId)
            : route('admin.petty-cash.index');

        $this->addMenuItem([
            'label' => __('تنخواه'),
            'icon' => 'lucide:wallet',
            'id' => 'petty-cash',
            'priority' => 30,
            'active' => request()->is('admin/petty-cash*'),
            'children' => [
                [
                    'label' => __('داشبورد تنخواه'),
                    'route' => route('admin.petty-cash.index', $defaultLedgerId ? ['ledger' => $defaultLedgerId] : []),
                    'active' => Route::is('admin.petty-cash.index'),
                    'priority' => 10,
                ],
                [
                    'label' => __('ثبت تراکنش جدید'),
                    'route' => $defaultLedgerId ? route('admin.petty-cash.transactions', $defaultLedgerId) : route('admin.petty-cash.index'),
                    'active' => Route::is('admin.petty-cash.transactions'),
                    'priority' => 15,
                ],
                [
                    'label' => __('درخواست شارژ'),
                    'route' => $chargeRoute,
                    'active' => Route::is('admin.petty-cash.charge-request'),
                    'priority' => 20,
                ],
                [
                    'label' => __('تسویه تنخواه'),
                    'route' => $settlementRoute,
                    'active' => Route::is('admin.petty-cash.settlement'),
                    'priority' => 30,
                ],
                [
                    'label' => __('اسناد بایگانی شده'),
                    'route' => route('admin.petty-cash.archives.index'),
                    'active' => Route::is('admin.petty-cash.archives.*'),
                    'priority' => 40,
                    'permissions' => ['petty_cash.archive.view'],
                ],
            ],
        ], __('Finance'));

        $this->addMenuItem([
            'label' => __('Monitoring'),
            'icon' => 'lucide:monitor',
            'id' => 'monitoring-submenu',
            'active' => Route::is('admin.action-logs.*'),
            'priority' => 50,
            'permissions' => ['pulse.view', 'actionlog.view'],
            'children' => [
                [
                    'label' => __('Action Logs'),
                    'route' => route('admin.action-logs.index'),
                    'active' => Route::is('admin.action-logs.index'),
                    'priority' => 10,
                    'permissions' => 'actionlog.view',
                ],
                [
                    'label' => __('Laravel Pulse'),
                    'route' => route('pulse'),
                    'active' => false,
                    'target' => '_blank',
                    'priority' => 20,
                    'permissions' => 'pulse.view',
                ],
            ],
        ], __('More'));

        $this->addMenuItem(
            [
                'label' => __('Access Control'),
                'icon' => 'lucide:key',
                'id' => 'access-control-submenu',
                'active' => Route::is('admin.roles.*') || Route::is('admin.permissions.*') || Route::is('admin.users.*'),
                'priority' => 30,
                'permissions' => ['role.create', 'role.view', 'role.edit', 'role.delete', 'user.create', 'user.view', 'user.edit', 'user.delete'],
                'children' => [
                    [
                        'label' => __('Users'),
                        'route' => route('admin.users.index'),
                        'active' => Route::is('admin.users.index') || Route::is('admin.users.create') || Route::is('admin.users.edit'),
                        'priority' => 10,
                        'permissions' => 'user.view',
                    ],
                    [
                        'label' => __('Roles'),
                        'route' => route('admin.roles.index'),
                        'active' => Route::is('admin.roles.index') || Route::is('admin.roles.create') || Route::is('admin.roles.edit'),
                        'priority' => 20,
                        'permissions' => 'role.view',
                    ],
                    [
                        'label' => __('Permissions'),
                        'route' => route('admin.permissions.index'),
                        'active' => Route::is('admin.permissions.index') || Route::is('admin.permissions.show'),
                        'priority' => 30,
                        'permissions' => 'role.view',
                    ],
                ],
            ],
            __('More')
        );

        $this->addMenuItem([
            'label' => __('Settings'),
            'icon' => 'lucide:settings',
            'id' => 'settings-submenu',
            'active' => Route::is('admin.settings.*') || Route::is('admin.translations.*'),
            'priority' => 40,
            'permissions' => ['settings.edit', 'translations.view'],
            'children' => [
                [
                    'label' => __('Settings'),
                    'route' => route('admin.settings.index'),
                    'active' => Route::is('admin.settings.index'),
                    'priority' => 20,
                    'permissions' => 'settings.edit',
                ],
                [
                    'label' => __('Translations'),
                    'route' => route('admin.translations.index'),
                    'active' => Route::is('admin.translations.*'),
                    'priority' => 10,
                    'permissions' => ['translations.view', 'translations.edit'],
                ],
            ],
        ], __('More'));

        // System Management - Alerts, Announcements & SMS (Superadmin only)
        if ($user && $user->hasRole('Superadmin')) {
            $this->addMenuItem([
                'label' => __('مدیریت سیستم'),
                'icon' => 'lucide:bell',
                'id' => 'system-management-submenu',
                'active' => Route::is('admin.alert-settings.*') || Route::is('admin.announcements.*') || Route::is('admin.sms-settings.*'),
                'priority' => 35,
                'children' => [
                    [
                        'label' => __('تنظیمات هشدارها'),
                        'route' => route('admin.alert-settings.index'),
                        'icon' => 'lucide:sliders',
                        'active' => Route::is('admin.alert-settings.*'),
                        'priority' => 10,
                    ],
                    [
                        'label' => __('مدیریت اطلاعیه‌ها'),
                        'route' => route('admin.announcements.index'),
                        'icon' => 'lucide:megaphone',
                        'active' => Route::is('admin.announcements.*'),
                        'priority' => 20,
                    ],
                    [
                        'label' => __('تنظیمات پیامک'),
                        'route' => route('admin.sms-settings.index'),
                        'icon' => 'lucide:message-square',
                        'active' => Route::is('admin.sms-settings.*'),
                        'priority' => 30,
                    ],
                ],
            ], __('More'));
        }

        $this->addMenuItem([
            'label' => __('Logout'),
            'icon' => 'lucide:log-out',
            'route' => route('admin.dashboard'),
            'active' => false,
            'id' => 'logout',
            'priority' => 10000,
            'html' => '
                <li>
                    <form method="POST" action="' . route('admin.logout.submit') . '">
                        ' . csrf_field() . '
                        <button type="submit" class="menu-item group w-full text-left menu-item-inactive text-gray-700 dark:text-white hover:text-gray-700">
                            <iconify-icon icon="lucide:log-out" class="menu-item-icon " width="16" height="16"></iconify-icon>
                            <span class="menu-item-text">' . __('Logout') . '</span>
                        </button>
                    </form>
                </li>
            ',
        ], __('More'));

        $this->groups = Hook::applyFilters(AdminFilterHook::ADMIN_MENU_GROUPS_BEFORE_SORTING, $this->groups);

        $this->sortMenuItemsByPriority();

        return $this->applyFiltersToMenuItems();
    }

    /**
     * Register post types in the menu
     * Move to main group if $group is null
     */
    protected function registerPostTypesInMenu(?string $group = 'Content'): void
    {
        $contentService = app(ContentService::class);
        $postTypes = $contentService->getPostTypes();

        if ($postTypes->isEmpty()) {
            return;
        }

        foreach ($postTypes as $typeName => $type) {
            // Skip if not showing in menu.
            if (isset($type->show_in_menu) && ! $type->show_in_menu) {
                continue;
            }

            // Create children menu items.
            $children = [
                [
                    'title' => __("All {$type->label}"),
                    'route' => 'admin.posts.index',
                    'params' => $typeName,
                    'active' => request()->is('admin/posts/' . $typeName) ||
                        (request()->is('admin/posts/' . $typeName . '/*') && ! request()->is('admin/posts/' . $typeName . '/create')),
                    'priority' => 10,
                    'permissions' => 'post.view',
                ],
                [
                    'title' => __('Add New'),
                    'route' => 'admin.posts.create',
                    'params' => $typeName,
                    'active' => request()->is('admin/posts/' . $typeName . '/create'),
                    'priority' => 20,
                    'permissions' => 'post.create',
                ],
            ];

            // Add taxonomies as children of this post type if this post type has them.
            if (! empty($type->taxonomies)) {
                $taxonomies = $contentService->getTaxonomies()
                    ->whereIn('name', $type->taxonomies);

                foreach ($taxonomies as $taxonomy) {
                    $children[] = [
                        'title' => __($taxonomy->label),
                        'route' => 'admin.terms.index',
                        'params' => $taxonomy->name,
                        'active' => request()->is('admin/terms/' . $taxonomy->name . '*'),
                        'priority' => 30 + $taxonomy->id, // Prioritize after standard items
                        'permissions' => 'term.view',
                    ];
                }
            }

            // Set up menu item with all children.
            $menuItem = [
                'title' => __($type->label),
                'icon' => get_post_type_icon($typeName),
                'id' => 'post-type-' . $typeName,
                'active' => request()->is('admin/posts/' . $typeName . '*') ||
                    (! empty($type->taxonomies) && $this->isCurrentTermBelongsToPostType($type->taxonomies)),
                'priority' => 10,
                'permissions' => 'post.view',
                'children' => $children,
            ];

            $this->addMenuItem($menuItem, $group ?: __('Main'));
        }
    }

    /**
     * Check if the current term route belongs to the given taxonomies
     */
    protected function isCurrentTermBelongsToPostType(array $taxonomies): bool
    {
        if (! request()->is('admin/terms/*')) {
            return false;
        }

        // Get the current taxonomy from the route
        $currentTaxonomy = request()->segment(3); // admin/terms/{taxonomy}

        return in_array($currentTaxonomy, $taxonomies);
    }

    protected function sortMenuItemsByPriority(): void
    {
        foreach ($this->groups as &$groupItems) {
            usort($groupItems, function ($a, $b) {
                return (int) $a->priority <=> (int) $b->priority;
            });
        }
    }

    protected function applyFiltersToMenuItems(): array
    {
        $result = [];
        foreach ($this->groups as $group => $items) {
            // Filter items by permission.
            $filteredItems = array_filter($items, function (AdminMenuItem $item) {
                return $item->userHasPermission();
            });

            // Apply filters that might add/modify menu items.
            $filteredItems = Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU->value . strtolower((string) $group), $filteredItems);

            // Only add the group if it has items after filtering.
            if (! empty($filteredItems)) {
                $result[$group] = $filteredItems;
            }
        }

        return $result;
    }

    public function shouldExpandSubmenu(AdminMenuItem $menuItem): bool
    {
        // If the parent menu item is active, expand the submenu.
        if ($menuItem->active) {
            return true;
        }

        // Check if any child menu item is active.
        foreach ($menuItem->children as $child) {
            if ($child->active) {
                return true;
            }
        }

        return false;
    }

    public function render(array $groupItems): string
    {
        $html = '';
        foreach ($groupItems as $menuItem) {
            $filterKey = $menuItem->id ?? Str::slug($menuItem->label) ?: '';
            $html .= Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_BEFORE->value . $filterKey, '');

            $html .= view('backend.layouts.partials.sidebar.menu-item', [
                'item' => $menuItem,
            ])->render();

            $html .= Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_AFTER->value . $filterKey, '');
        }

        return $html;
    }
}
