<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'code' => 'admin', 'description' => 'Full access to all system features'],
            ['name' => 'Approver', 'code' => 'approver', 'description' => 'Can approve data and manage access (Manager)'],
            ['name' => 'Checker', 'code' => 'checker', 'description' => 'Can verify data (Supervisor/Leader)'],
            ['name' => 'Operator', 'code' => 'operator', 'description' => 'Can input data (Staff)'],
            ['name' => 'PIC', 'code' => 'pic', 'description' => 'Can create and submit STO events (Person In Charge)'],
            ['name' => 'Supervisor', 'code' => 'supervisor', 'description' => 'Intermediate access with edit/delete permissions'],
            ['name' => 'Viewer', 'code' => 'viewer', 'description' => 'Read-only access'],
            ['name' => 'Checker Tool', 'code' => 'checker_tool', 'description' => 'Can verify tool stock opname'],
            ['name' => 'Approver Tool', 'code' => 'approver_tool', 'description' => 'Can finalize tool stock opname'],
            ['name' => 'Operator Tool', 'code' => 'operator_tool', 'description' => 'Can fill STO tool, perform transactions, and manage master data'],
        ];

        foreach ($roles as $role) {
            \App\Models\InventoryModel\InvRole::updateOrCreate(
                ['code' => $role['code']],
                ['name' => $role['name'], 'description' => $role['description']]
            );
        }
    }
}
