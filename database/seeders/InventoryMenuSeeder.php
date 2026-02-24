<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer']
            ],
            [
                'title' => 'Master Data',
                'route' => 'inventory.master',
                'icon' => 'fa-solid fa-database',
                'order' => 2,
                'roles' => ['admin', 'approver']
            ],
            [
                'title' => 'Product',
                'route' => 'inventory.product',
                'icon' => 'fa-solid fa-box',
                'order' => 3,
                'roles' => ['admin', 'approver', 'checker', 'operator', 'viewer']
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

        foreach ($menus as $menu) {
            // Create Menu
            $m = \App\Models\InventoryModel\Menu::updateOrCreate(
                ['route' => $menu['route']],
                [
                    'title' => $menu['title'],
                    'icon' => $menu['icon'],
                    'order' => $menu['order'],
                    'is_active' => true
                ]
            );

            // Assign to Roles
            $roles = \App\Models\InventoryModel\InvRole::whereIn('code', $menu['roles'])->get();
            $m->roles()->sync($roles->pluck('id'));
        }
    }
}
