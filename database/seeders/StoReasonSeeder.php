<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryModel\Material\StoReason;

class StoReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            ['name' => 'Wrong Count', 'category' => 'OTHERS', 'is_active' => 1],
            ['name' => 'No Transaction', 'category' => 'OTHERS', 'is_active' => 1],
            ['name' => 'Wrong Label', 'category' => 'OTHERS', 'is_active' => 1],
            ['name' => 'Part Common (not use)', 'category' => 'OTHERS', 'is_active' => 1],
            ['name' => 'Quality Problem', 'category' => 'OTHERS', 'is_active' => 1],
            ['name' => 'Other', 'category' => 'OTHERS', 'is_active' => 1],
        ];

        // Delete any reasons not in our standard list
        StoReason::whereNotIn('name', array_column($reasons, 'name'))->delete();

        foreach ($reasons as $reason) {
            StoReason::updateOrCreate(
                ['name' => $reason['name']],
                [
                    'category' => $reason['category'],
                    'is_active' => $reason['is_active']
                ]
            );
        }
    }
}

