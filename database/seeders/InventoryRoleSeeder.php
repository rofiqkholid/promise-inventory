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
            ['name' => 'Viewer', 'code' => 'viewer', 'description' => 'Read-only access'],
        ];

        foreach ($roles as $role) {
            \App\Models\InventoryModel\InvRole::updateOrCreate(
                ['code' => $role['code']],
                ['name' => $role['name'], 'description' => $role['description']]
            );
        }
    }
}
