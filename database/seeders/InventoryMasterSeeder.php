<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryModel\CoilCenter;
use App\Models\InventoryModel\MaterialSpec;
use App\Models\InventoryModel\Rank;
use App\Models\InventoryModel\Supplier;
use App\Models\InventoryModel\TransactionCategory;
use App\Models\InventoryModel\Unit;

class InventoryMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Coil Center
        $coilCenters = [
            ['code' => 'POSCO'],
            ['code' => 'SCI'],
            ['code' => 'JSSI'],
            ['code' => 'MOSSI'],
            ['code' => 'USC'],
        ];
        foreach ($coilCenters as $item) {
            CoilCenter::updateOrCreate(['code' => $item['code']], $item);
        }

        // 2. Rank
        $ranks = [
            ['code' => '1-A', 'limit_value' => 330, 'description' => 'SMU 1 - Formability A (Base 280 + 50)'],
            ['code' => '1-B', 'limit_value' => 280, 'description' => 'SMU 1 - Formability B (Base 280 + 0)'],
            ['code' => '1-C', 'limit_value' => 230, 'description' => 'SMU 1 - Formability C (Base 280 - 50)'],
            ['code' => '2-A', 'limit_value' => 400, 'description' => 'SMU 2 - Formability A (Base 350 + 50)'],
            ['code' => '2-B', 'limit_value' => 350, 'description' => 'SMU 2 - Formability B (Base 350 + 0)'],
            ['code' => '2-C', 'limit_value' => 300, 'description' => 'SMU 2 - Formability C (Base 350 - 50)'],
            ['code' => '3-A', 'limit_value' => 470, 'description' => 'SMU 3 - Formability A (Base 420 + 50)'],
            ['code' => '3-B', 'limit_value' => 420, 'description' => 'SMU 3 - Formability B (Base 420 + 0)'],
            ['code' => '3-C', 'limit_value' => 370, 'description' => 'SMU 3 - Formability C (Base 420 - 50)'],
            ['code' => '4-A', 'limit_value' => 550, 'description' => 'SMU 4 - Formability A (Base 500 + 50)'],
            ['code' => '4-B', 'limit_value' => 500, 'description' => 'SMU 4 - Formability B (Base 500 + 0)'],
            ['code' => '4-C', 'limit_value' => 450, 'description' => 'SMU 4 - Formability C (Base 500 - 50)'],
            ['code' => '5-A', 'limit_value' => 620, 'description' => 'SMU 5 - Formability A (Base 570 + 50)'],
            ['code' => '5-B', 'limit_value' => 570, 'description' => 'SMU 5 - Formability B (Base 570 + 0)'],
            ['code' => '5-C', 'limit_value' => 520, 'description' => 'SMU 5 - Formability C (Base 570 - 50)'],
            ['code' => '6-A', 'limit_value' => 690, 'description' => 'SMU 6 - Formability A (Base 640 + 50)'],
            ['code' => '6-B', 'limit_value' => 640, 'description' => 'SMU 6 - Formability B (Base 640 + 0)'],
            ['code' => '6-C', 'limit_value' => 590, 'description' => 'SMU 6 - Formability C (Base 640 - 50)'],
            ['code' => '7-A', 'limit_value' => 760, 'description' => 'SMU 7 - Formability A (Base 710 + 50)'],
            ['code' => '7-B', 'limit_value' => 710, 'description' => 'SMU 7 - Formability B (Base 710 + 0)'],
            ['code' => '7-C', 'limit_value' => 660, 'description' => 'SMU 7 - Formability C (Base 710 - 50)'],
            ['code' => '8-A', 'limit_value' => 550, 'description' => 'SMU 8 - Formability A (Base 500 + 50)'],
            ['code' => '8-B', 'limit_value' => 500, 'description' => 'SMU 8 - Formability B (Base 500 + 0)'],
            ['code' => '8-C', 'limit_value' => 450, 'description' => 'SMU 8 - Formability C (Base 500 - 50)'],
            ['code' => '9-A', 'limit_value' => 72, 'description' => 'SMU 9 - Formability A (Base 22 + 50)'],
            ['code' => '9-B', 'limit_value' => 22, 'description' => 'SMU 9 - Formability B (Base 22 + 0)'],
            ['code' => '9-C', 'limit_value' => -28, 'description' => 'SMU 9 - Formability C (Base 22 - 50)'],
            ['code' => '10-A', 'limit_value' => 110, 'description' => 'SMU 10 - Formability A (Base 60 + 50)'],
            ['code' => '10-B', 'limit_value' => 60, 'description' => 'SMU 10 - Formability B (Base 60 + 0)'],
            ['code' => '10-C', 'limit_value' => 10, 'description' => 'SMU 10 - Formability C (Base 60 - 50)'],
            ['code' => '11-A', 'limit_value' => 150, 'description' => 'SMU 11 - Formability A (Base 100 + 50)'],
            ['code' => '11-B', 'limit_value' => 100, 'description' => 'SMU 11 - Formability B (Base 100 + 0)'],
            ['code' => '11-C', 'limit_value' => 50, 'description' => 'SMU 11 - Formability C (Base 100 - 50)'],
            ['code' => '12-A', 'limit_value' => 190, 'description' => 'SMU 12 - Formability A (Base 140 + 50)'],
            ['code' => '12-B', 'limit_value' => 140, 'description' => 'SMU 12 - Formability B (Base 140 + 0)'],
            ['code' => '12-C', 'limit_value' => 90, 'description' => 'SMU 12 - Formability C (Base 140 - 50)'],
        ];
        foreach ($ranks as $item) {
            Rank::updateOrCreate(['code' => $item['code']], $item);
        }

        // 3. Supplier
        $suppliers = [
            ['code' => 'QIANYUAN'],
            ['code' => 'SAI'],
            ['code' => 'DENAPELA'],
            ['code' => 'INDOMURAYAMA'],
            ['code' => 'JIANKE'],
            ['code' => 'OSI'],
            ['code' => 'SERAYU'],
            ['code' => 'TCF'],
            ['code' => 'YOSKA'],
        ];
        foreach ($suppliers as $item) {
            Supplier::updateOrCreate(['code' => $item['code']], $item);
        }

        // 4. Transaction Category
        $categories = [
            ['code' => 'IN', 'name' => 'Incomming', 'effect' => 1],
            ['code' => 'OUT-TRIAL', 'name' => 'Out Trial', 'effect' => -1],
            ['code' => 'OUT-EVENT', 'name' => 'Out Event', 'effect' => -1],
            ['code' => 'OUT-PP', 'name' => 'Out Pre Production', 'effect' => -1],
            ['code' => 'OUT-OTHER', 'name' => 'Out Other', 'effect' => -1],
        ];
        foreach ($categories as $item) {
            TransactionCategory::updateOrCreate(['code' => $item['code']], $item);
        }

        // 5. Unit
        $units = [
            ['code' => 'SHT', 'name' => 'Sheet'],
            ['code' => 'COIL', 'name' => 'Coil'],
            ['code' => 'TRAP', 'name' => 'Trapezoid'],
        ];
        foreach ($units as $item) {
            Unit::updateOrCreate(['code' => $item['code']], $item);
        }

        // 6. Material Spec
        $specs = [
            ['spec_name' => 'MJSC440W-OD', 'coating_type' => 'Non-GA'],
            ['spec_name' => 'MJSC980Y-OD', 'coating_type' => 'Non-GA'],
            ['spec_name' => 'MJAC270C-OD-45/45', 'coating_type' => 'GA'],
            ['spec_name' => 'MJAC270D-OD-45/45', 'coating_type' => 'GA'],
            ['spec_name' => 'MJAC440W-OD-45/45', 'coating_type' => 'GA'],
            ['spec_name' => 'MJSC270C-OD', 'coating_type' => 'Non-GA'],
            ['spec_name' => 'MJSC270D-OD', 'coating_type' => 'Non-GA'],
            ['spec_name' => 'MJSC590R-OD', 'coating_type' => 'Non-GA'],
            ['spec_name' => 'MJSH440W-OP', 'coating_type' => 'Non-GA'],
            ['spec_name' => 'MJSH590R-OP', 'coating_type' => 'Non-GA'],
        ];
        foreach ($specs as $item) {
            MaterialSpec::updateOrCreate(['spec_name' => $item['spec_name']], $item);
        }
    }
}
