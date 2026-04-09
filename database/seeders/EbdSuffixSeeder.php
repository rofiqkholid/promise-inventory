<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryModel\VaveBaseSuffix;
use Illuminate\Support\Facades\DB;

class EbdSuffixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample for MMKI (1)
        VaveBaseSuffix::updateOrCreate(
            ['customer_id' => 1, 'name' => 'SQ'],
            ['remark' => 'Status Quo']
        );
        VaveBaseSuffix::updateOrCreate(
            ['customer_id' => 1, 'name' => 'Tech Review'],
            ['remark' => 'Technical Review by Engineering']
        );
        VaveBaseSuffix::updateOrCreate(
            ['customer_id' => 1, 'name' => 'Go Mfg'],
            ['remark' => '']
        );
        VaveBaseSuffix::updateOrCreate(
            ['customer_id' => 1, 'name' => 'Ichigenka'],
            ['remark' => '']
        );

    }
}
