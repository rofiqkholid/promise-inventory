<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryModel\Menu;
use App\Models\InventoryModel\InvRole;

class InventoryMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Main Seeder for all Inventory Menus (Material & Tool)
     */
    public function run(): void
    {
        // 1. Clean up existing menu data
        $this->cleanup();

        // 2. Define Menu Structure
        $definitions = [
            // --- MATERIAL INVENTORY ---
            [
                'title' => 'MATERIAL INVENTORY',
                'route' => '#',
                'type'  => 'header',
                'order' => 2,
                'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer'],
                'children' => [
                    [
                        'title' => 'Material Dashboard',
                        'route' => 'dashboard',
                        'icon'  => 'fa-solid fa-chart-pie',
                        'order' => 0,
                        'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer'],
                    ],
                    [
                        'title' => 'Data Master',
                        'route' => '#',
                        'icon'  => 'fa-solid fa-database',
                        'order' => 1,
                        'roles' => ['admin', 'approver'],
                        'children' => [
                            ['title' => 'Product', 'route' => 'inventory.master.product.index', 'icon' => 'fa-solid fa-box', 'order' => 1, 'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer']],
                            ['title' => 'Coil Center', 'route' => 'inventory.master.coilCenter.index', 'icon' => 'fa-solid fa-industry', 'order' => 2, 'roles' => ['admin', 'approver']],
                            ['title' => 'Material Spec', 'route' => 'inventory.master.materialSpec.index', 'icon' => 'fa-solid fa-layer-group', 'order' => 3, 'roles' => ['admin', 'approver']],
                            ['title' => 'Unit', 'route' => 'inventory.master.unit.index', 'icon' => 'fa-solid fa-ruler', 'order' => 4, 'roles' => ['admin', 'approver']],
                            ['title' => 'Rank', 'route' => 'inventory.master.rank.index', 'icon' => 'fa-solid fa-ranking-star', 'order' => 5, 'roles' => ['admin', 'approver']],
                            ['title' => 'Model Config', 'route' => 'inventory.master.modelConfig.index', 'icon' => 'fa-solid fa-sliders', 'order' => 6, 'roles' => ['admin', 'approver']],
                            ['title' => 'Supplier', 'route' => 'inventory.master.supplier.index', 'icon' => 'fa-solid fa-truck-field', 'order' => 7, 'roles' => ['admin', 'approver']],
                            ['title' => 'Transaction Category', 'route' => 'inventory.master.transactionCategory.index', 'icon' => 'fa-solid fa-tags', 'order' => 8, 'roles' => ['admin', 'approver']],
                            ['title' => 'Location', 'route' => 'inventory.master.location.index', 'icon' => 'fa-solid fa-location-dot', 'order' => 9, 'roles' => ['admin', 'approver']],
                            ['title' => 'Revision', 'route' => 'inventory.master.revision.index', 'icon' => 'fa-solid fa-list-ol', 'order' => 10, 'roles' => ['admin', 'approver']],
                            ['title' => 'EBD Suffix', 'route' => 'inventory.master.vave-base-suffix.index', 'icon' => 'fa-solid fa-tags', 'order' => 11, 'roles' => ['admin', 'approver']],
                        ]
                    ],
                    ['title' => 'Transaction', 'route' => 'inventory.transaction', 'icon' => 'fa-solid fa-right-left', 'order' => 2, 'roles' => ['admin', 'approver', 'operator']],
                    ['title' => 'Transaction History', 'route' => 'transactionHistory', 'icon' => 'fa-solid fa-clock-rotate-left', 'order' => 3, 'roles' => ['admin', 'approver', 'checker']],
                    ['title' => 'Material Monitoring', 'route' => 'inventory.stockMonitoring', 'icon' => 'fa-solid fa-cubes', 'order' => 4, 'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer']],
                    ['title' => 'Purchase Requisition', 'route' => 'inventory.purchaseRequisition.index', 'icon' => 'fa-solid fa-receipt', 'order' => 5, 'roles' => ['admin', 'approver', 'checker']],
                    [
                        'title' => 'Stock Opname',
                        'route' => '#',
                        'icon'  => 'fa-solid fa-clipboard-check',
                        'order' => 6,
                        'roles' => ['admin', 'approver', 'checker', 'operator', 'pic', 'viewer'],
                        'children' => [
                            ['title' => 'STO Dashboard', 'route' => 'inventory.sto.dashboard', 'icon' => 'fa-solid fa-chart-pie', 'order' => 1, 'roles' => ['admin', 'approver', 'checker', 'viewer', 'pic', 'operator']],
                            ['title' => 'STO Events', 'route' => 'inventory.sto.index', 'icon' => 'fa-solid fa-list-check', 'order' => 2, 'roles' => ['admin', 'approver', 'checker', 'operator', 'pic']],
                        ]
                    ],
                    [
                        'title' => 'VA/VE',
                        'route' => '#',
                        'icon'  => 'fa-solid fa-vial',
                        'order' => 7,
                        'roles' => ['admin', 'approver', 'checker', 'viewer'],
                        'children' => [
                            [
                                'title' => 'Project',
                                'route' => '#',
                                'icon'  => 'fa-solid fa-folder-tree',
                                'order' => 1,
                                'roles' => ['admin', 'approver', 'checker', 'viewer'],
                                'children' => [
                                    ['title' => 'VA/VE Project Dashboard', 'route' => 'inventory.projectVaveDashboard.index', 'icon' => 'fa-solid fa-chart-line', 'order' => 1, 'roles' => ['admin', 'approver', 'checker', 'viewer']],
                                    ['title' => 'VA/VE Project Analyze', 'route' => 'inventory.projectVaveAnalysis.index', 'icon' => 'fa-solid fa-calculator', 'order' => 2, 'roles' => ['admin', 'approver', 'checker', 'viewer']],
                                ]
                            ],
                            [
                                'title' => 'Regular',
                                'route' => '#',
                                'icon'  => 'fa-solid fa-box-archive',
                                'order' => 2,
                                'roles' => ['admin', 'approver', 'checker', 'viewer'],
                                'children' => [
                                    ['title' => 'VA/VE Regular Dashboard', 'route' => 'inventory.regularVaveDashboard.index', 'icon' => 'fa-solid fa-chart-line', 'order' => 1, 'roles' => ['admin', 'approver', 'checker', 'viewer']],
                                    ['title' => 'VA/VE Regular Analyze', 'route' => 'inventory.regularVaveAnalysis.index', 'icon' => 'fa-solid fa-calculator', 'order' => 2, 'roles' => ['admin', 'approver', 'checker', 'viewer']],
                                ]
                            ],
                        ]
                    ],
                ]
            ],

            // --- TOOL INVENTORY ---
            [
                'title' => 'TOOL INVENTORY',
                'route' => '#',
                'type'  => 'header',
                'order' => 3,
                'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer'],
                'children' => [
                    ['title' => 'Dashboard', 'route' => 'inventory.tool.dashboard', 'icon' => 'fa-solid fa-gauge-high', 'order' => 0, 'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer']],
                    [
                        'title' => 'Master Data',
                        'route' => '#',
                        'icon'  => 'fa-solid fa-boxes-packing',
                        'order' => 1,
                        'roles' => ['admin', 'approver'],
                        'children' => [
                            ['title' => 'Tool Category', 'route' => 'inventory.tool.category.index', 'icon' => 'fa-solid fa-tags', 'order' => 1, 'roles' => ['admin', 'approver']],
                            ['title' => 'Tool Specification', 'route' => 'inventory.tool.master.index', 'icon' => 'fa-solid fa-wrench', 'order' => 2, 'roles' => ['admin', 'approver']],
                            ['title' => 'Tool Location', 'route' => 'inventory.tool.location.index', 'icon' => 'fa-solid fa-location-dot', 'order' => 3, 'roles' => ['admin', 'approver']],
                        ]
                    ],
                    [
                        'title' => 'Operational',
                        'route' => '#',
                        'icon'  => 'fa-solid fa-gears',
                        'order' => 2,
                        'roles' => ['admin', 'approver', 'checker', 'operator'],
                        'children' => [
                            ['title' => 'Fast Moving Stock', 'route' => 'inventory.tool.fast-stock.index', 'icon' => 'fa-solid fa-bolt', 'order' => 1, 'roles' => ['admin', 'approver', 'checker', 'operator']],
                            ['title' => 'Slow Moving Assets', 'route' => 'inventory.tool.slow-batch.index', 'icon' => 'fa-solid fa-cubes', 'order' => 2, 'roles' => ['admin', 'approver', 'checker', 'operator']],
                        ]
                    ],
                    [
                        'title' => 'Stock Opname (STO)',
                        'route' => '#',
                        'icon'  => 'fa-solid fa-clipboard-check',
                        'order' => 3,
                        'roles' => ['admin', 'approver', 'checker', 'operator'],
                        'children' => [
                            ['title' => 'STO Fast Moving', 'route' => 'inventory.tool.sto-fast.index', 'icon' => 'fa-solid fa-bolt', 'order' => 1, 'roles' => ['admin', 'approver', 'checker', 'operator']],
                            ['title' => 'STO Slow Moving', 'route' => 'inventory.tool.sto-slow.index', 'icon' => 'fa-solid fa-cubes', 'order' => 2, 'roles' => ['admin', 'approver', 'checker', 'operator']],
                        ]
                    ],
                ]
            ],

            // --- SYSTEM ---
            [
                'title' => 'SYSTEM & ACCESS',
                'route' => '#',
                'type'  => 'header',
                'order' => 4,
                'roles' => ['admin'],
                'children' => [
                    ['title' => 'User Access', 'route' => 'inventory.userAccess.index', 'icon' => 'fa-solid fa-users-gear', 'order' => 1, 'roles' => ['admin']],
                ]
            ],
        ];

        // 3. Process Seed
        $this->process($definitions);
    }

    private function cleanup()
    {
        // Disable constraints for SQL Server clean wipe
        DB::statement('EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT ALL"');
        DB::table('inv_role_menus')->delete();
        DB::table('inv_m_menus')->delete();
        DB::statement('EXEC sp_MSforeachtable "ALTER TABLE ? CHECK CONSTRAINT ALL"');
    }

    private function process(array $menus, $parentId = null)
    {
        foreach ($menus as $m) {
            $children = $m['children'] ?? [];
            $roles    = $m['roles'] ?? [];
            
            // Prepare record
            $record = [
                'title'     => $m['title'],
                'route'     => $m['route'],
                'icon'      => $m['icon'] ?? null,
                'type'      => $m['type'] ?? 'menu',
                'order'     => $m['order'] ?? 0,
                'parent_id' => $parentId,
                'is_active' => true,
            ];

            $menu = Menu::create($record);

            // Sync Roles if defined
            if (!empty($roles)) {
                $roleIds = InvRole::whereIn('code', $roles)->pluck('id');
                $menu->roles()->sync($roleIds);
            }

            // Recurse for children
            if (!empty($children)) {
                $this->process($children, $menu->id);
            }
        }
    }
}
