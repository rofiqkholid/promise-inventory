<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryModel\Menu;
use App\Models\InventoryModel\InvRole;

class InventoryMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'fa-solid fa-chart-pie',
                'order' => 1,
                'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer'],
            ],
            [
                'title' => 'Master Data',
                'route' => 'inventory.master.master.index',
                'icon' => 'fa-solid fa-database',
                'order' => 2,
                'roles' => ['admin', 'approver'],
                'children' => [
                    [
                        'title' => 'Product',
                        'route' => 'inventory.master.product.index',
                        'icon' => 'fa-solid fa-box',
                        'order' => 1,
                        'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer'],
                    ],
                    [
                        'title' => 'Coil Center',
                        'route' => 'inventory.master.coilCenter.index',
                        'icon' => 'fa-solid fa-industry',
                        'order' => 2,
                        'roles' => ['admin', 'approver'],
                    ],
                    [
                        'title' => 'Material Spec',
                        'route' => 'inventory.master.materialSpec.index',
                        'icon' => 'fa-solid fa-layer-group',
                        'order' => 3,
                        'roles' => ['admin', 'approver'],
                    ],
                    [
                        'title' => 'Unit',
                        'route' => 'inventory.master.unit.index',
                        'icon' => 'fa-solid fa-ruler',
                        'order' => 4,
                        'roles' => ['admin', 'approver'],
                    ],
                    [
                        'title' => 'Rank',
                        'route' => 'inventory.master.rank.index',
                        'icon' => 'fa-solid fa-ranking-star',
                        'order' => 5,
                        'roles' => ['admin', 'approver'],
                    ],
                    [
                        'title' => 'Model Config',
                        'route' => 'inventory.master.modelConfig.index',
                        'icon' => 'fa-solid fa-sliders',
                        'order' => 6,
                        'roles' => ['admin', 'approver'],
                    ],
                    [
                        'title' => 'Supplier',
                        'route' => 'inventory.master.supplier.index',
                        'icon' => 'fa-solid fa-truck-field',
                        'order' => 7,
                        'roles' => ['admin', 'approver'],
                    ],
                    [
                        'title' => 'Transaction Category',
                        'route' => 'inventory.master.transactionCategory.index',
                        'icon' => 'fa-solid fa-tags',
                        'order' => 8,
                        'roles' => ['admin', 'approver'],
                    ],
                ]
            ],
            [
                'title' => 'VA/VE Analysis',
                'route' => 'inventory.vave.index',
                'icon' => 'fa-solid fa-vial',
                'order' => 4,
                'roles' => ['admin', 'approver', 'checker', 'viewer']
            ],
            [
                'title' => 'Inventory Transaction',
                'route' => 'inventory.transaction',
                'icon' => 'fa-solid fa-right-left',
                'order' => 5,
                'roles' => ['admin', 'approver', 'operator']
            ],
            [
                'title' => 'Stock Monitoring',
                'route' => 'inventory.stockMonitoring',
                'icon' => 'fa-solid fa-chart-line',
                'order' => 6,
                'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer']
            ],
            [
                'title' => 'Auto PR',
                'route' => 'inventory.autoPr.index',
                'icon' => 'fa-solid fa-receipt',
                'order' => 7,
                'roles' => ['admin', 'approver', 'checker']
            ],
            [
                'title' => 'Stock Opname (STO)',
                'route' => 'inventory.sto.index',
                'icon' => 'fa-solid fa-clipboard-check',
                'order' => 8,
                'roles' => ['admin', 'approver', 'checker', 'operator']
            ],
            [
                'title' => 'Transaction History',
                'route' => 'transactionHistory',
                'icon' => 'fa-solid fa-clock-rotate-left',
                'order' => 9,
                'roles' => ['admin', 'approver', 'checker']
            ],
            [
                'title' => 'User Access',
                'route' => 'inventory.userAccess.index',
                'icon' => 'fa-solid fa-users-gear',
                'order' => 10,
                'roles' => ['admin', 'approver']
            ],
        ];

        $this->seedMenus($menus);
    }

    private function seedMenus(array $menus, $parentId = null)
    {
        foreach ($menus as $menuData) {
            $children = $menuData['children'] ?? [];
            $roles = $menuData['roles'] ?? [];
            unset($menuData['children'], $menuData['roles']);

            $menuData['parent_id'] = $parentId;
            $menuData['is_active'] = true;

            $menu = Menu::updateOrCreate(
                ['route' => $menuData['route']],
                $menuData
            );

            // Sync Roles
            $roleModels = InvRole::whereIn('code', $roles)->get();
            $menu->roles()->sync($roleModels->pluck('id'));

            if (!empty($children)) {
                $this->seedMenus($children, $menu->id);
            }
        }
    }
}
