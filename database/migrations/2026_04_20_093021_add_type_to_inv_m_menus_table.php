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
        Schema::table('inv_m_menus', function (Blueprint $table) {
            $table->string('type', 50)->default('menu')->after('icon')->comment('menu, header, divider');
        });

        // Seed initial headers
        // Material Inventory Header
        $materialHeaderId = DB::table('inv_m_menus')->insertGetId([
            'title' => 'MATERIAL INVENTORY',
            'route' => '#',
            'icon' => null,
            'type' => 'header',
            'order' => 1,
            'is_active' => 1,
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Move existing root menus under Material Inventory (except Dashboard, or maybe keep Dashboard at top)
        DB::table('inv_m_menus')
          ->whereNull('parent_id')
          ->where('id', '!=', $materialHeaderId)
          ->where('title', '!=', 'Dashboard') 
          ->update(['parent_id' => $materialHeaderId, 'updated_at' => now()]);

        // Tool Inventory Header
        $toolHeaderId = DB::table('inv_m_menus')->insertGetId([
            'title' => 'TOOL INVENTORY',
            'route' => '#',
            'icon' => null,
            'type' => 'header',
            'order' => 99,
            'is_active' => 1,
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Master Data under Tool Header
        $toolMasterDataId = DB::table('inv_m_menus')->insertGetId([
            'title' => 'Master Data',
            'route' => '#',
            'icon' => 'fa-solid fa-boxes-packing',
            'type' => 'menu',
            'order' => 1,
            'is_active' => 1,
            'parent_id' => $toolHeaderId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tool Category
        $toolCategoryId = DB::table('inv_m_menus')->insertGetId([
            'title' => 'Tool Category',
            'route' => 'inventory.tool.category.index',
            'icon' => 'fa-solid fa-tags',
            'type' => 'menu',
            'order' => 1,
            'is_active' => 1,
            'parent_id' => $toolMasterDataId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Master Tool Specification
        $toolSpecId = DB::table('inv_m_menus')->insertGetId([
            'title' => 'Tool Specification',
            'route' => 'inventory.tool.master.index',
            'icon' => 'fa-solid fa-wrench',
            'type' => 'menu',
            'order' => 2,
            'is_active' => 1,
            'parent_id' => $toolMasterDataId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign to role 'admin' dynamically if it exists
        $adminRole = DB::table('inv_m_roles')->where('code', 'admin')->first();
        if ($adminRole) {
            $menusToAssign = [$toolHeaderId, $toolMasterDataId, $toolCategoryId, $toolSpecId];
            foreach ($menusToAssign as $menuId) {
                DB::table('inv_role_menus')->insert([
                    'role_id' => $adminRole->id,
                    'menu_id' => $menuId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the inserted menus
        DB::table('inv_m_menus')->whereIn('title', ['TOOL INVENTORY', 'MATERIAL INVENTORY'])->delete();
        
        // Remove type column
        Schema::table('inv_m_menus', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
