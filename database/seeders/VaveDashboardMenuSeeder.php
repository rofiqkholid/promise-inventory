<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaveDashboardMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Add Menu Item
        $menuId = DB::table('inv_m_menus')->insertGetId([
            'title' => 'VAVE Dashboard',
            'route' => 'inventory.vaveDashboard.index',
            'icon' => 'fa-solid fa-chart-line',
            'order' => 3, // Set order after VA/VE Analysis (which is 2)
            'is_active' => 1,
            'parent_id' => 153, // MATERIAL INVENTORY Header
            'type' => 'menu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Assign to Admin Role (assuming admin role id exists or find by code)
        $adminRole = DB::table('inv_m_roles')->where('code', 'admin')->first();
        if ($adminRole) {
            DB::table('inv_role_menus')->insert([
                'role_id' => $adminRole->id,
                'menu_id' => $menuId,
            ]);
        }

        // 3. Optional: Assign to other roles if needed (approver, checker, viewer)
        $otherRoles = DB::table('inv_m_roles')->whereIn('code', ['approver', 'checker', 'viewer'])->get();
        foreach ($otherRoles as $role) {
            DB::table('inv_role_menus')->insert([
                'role_id' => $role->id,
                'menu_id' => $menuId,
            ]);
        }
    }
}
