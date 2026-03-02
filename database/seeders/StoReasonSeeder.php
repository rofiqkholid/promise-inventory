<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            ['name' => 'Wrong Input at Production', 'category' => 'SHORTAGE'],
            ['name' => 'Material Lost / Scrapped without Record', 'category' => 'SHORTAGE'],
            ['name' => 'Supplier Delivery Shortage', 'category' => 'SHORTAGE'],
            ['name' => 'Found Unrecorded Material', 'category' => 'EXCESS'],
            ['name' => 'Production Return without Record', 'category' => 'EXCESS'],
            ['name' => 'System Double Input', 'category' => 'EXCESS'],
            ['name' => 'Wrong Identification / Tagging', 'category' => 'OTHERS'],
            ['name' => 'Location Displacement', 'category' => 'OTHERS'],
        ];

        foreach ($reasons as $reason) {
            \App\Models\InventoryModel\StoReason::updateOrCreate(
                ['name' => $reason['name']],
                ['category' => $reason['category']]
            );
        }
    }
}
