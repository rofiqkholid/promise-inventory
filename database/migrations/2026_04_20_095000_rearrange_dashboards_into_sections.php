<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Move Material Dashboard (id: 1) under Material Header (id: 1021)
        DB::table('inv_m_menus')
            ->where('id', 1)
            ->update([
                'parent_id' => 1021,
                'order' => 0,
                'updated_at' => now()
            ]);

        // 2. Create Tool Dashboard under Tool Header (id: 1022)
        $toolDashboardId = DB::table('inv_m_menus')->insertGetId([
            'title' => 'Dashboard',
            'route' => 'inventory.tool.dashboard',
            'icon' => 'fa-solid fa-chart-line',
            'type' => 'menu',
            'order' => 0,
            'is_active' => 1,
            'parent_id' => 1022,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Assign to role 'admin'
        $adminRole = DB::table('inv_m_roles')->where('code', 'admin')->first();
        if ($adminRole) {
            DB::table('inv_role_menus')->insert([
                'role_id' => $adminRole->id,
                'menu_id' => $toolDashboardId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Tool Dashboard
        DB::table('inv_m_menus')->where('route', 'inventory.tool.dashboard')->delete();

        // Move Material Dashboard back to root
        DB::table('inv_m_menus')
            ->where('id', 1)
            ->update([
                'parent_id' => null,
                'order' => 1,
                'updated_at' => now()
            ]);
    }
};
