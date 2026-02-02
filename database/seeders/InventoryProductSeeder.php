<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryModel\InventoryProduct;

class InventoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'product_id' => 14, 'material_spec_id' => 1, 'unit_id' => 1, 'rank_id' => 5, 'revision' => 'R1',
                'thickness' => 0.6, 'width' => 154, 'length' => 1156, 'length_2' => null, 'pitch' => null,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 7.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 53, 'material_spec_id' => 6, 'unit_id' => 1, 'rank_id' => 15, 'revision' => 'R1',
                'thickness' => 0.7, 'width' => 214, 'length' => 1219, 'length_2' => null, 'pitch' => 37,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 15, 'material_spec_id' => 1, 'unit_id' => 1, 'rank_id' => 8, 'revision' => 'R1',
                'thickness' => 0.8, 'width' => 224, 'length' => 1180, 'length_2' => null, 'pitch' => null,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 16, 'material_spec_id' => 7, 'unit_id' => 1, 'rank_id' => 2, 'revision' => 'R1',
                'thickness' => 0.6, 'width' => 207, 'length' => 1141, 'length_2' => null, 'pitch' => null,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 17, 'material_spec_id' => 7, 'unit_id' => 1, 'rank_id' => 2, 'revision' => 'R1',
                'thickness' => 0.6, 'width' => 132, 'length' => 1115, 'length_2' => null, 'pitch' => null,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 8, 'material_spec_id' => 8, 'unit_id' => 1, 'rank_id' => 11, 'revision' => 'R1',
                'thickness' => 1.4, 'width' => 200, 'length' => 753, 'length_2' => null, 'pitch' => null,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 11, 'material_spec_id' => 1, 'unit_id' => 1, 'rank_id' => 18, 'revision' => 'R1',
                'thickness' => 1.4, 'width' => 344, 'length' => 1219, 'length_2' => null, 'pitch' => 210,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 10, 'material_spec_id' => 1, 'unit_id' => 1, 'rank_id' => 18, 'revision' => 'R1',
                'thickness' => 1.4, 'width' => 392, 'length' => 1219, 'length_2' => null, 'pitch' => 168,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 22, 'material_spec_id' => 4, 'unit_id' => 1, 'rank_id' => 13, 'revision' => 'R1',
                'thickness' => 1.4, 'width' => 396, 'length' => 1219, 'length_2' => null, 'pitch' => 393,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 23, 'material_spec_id' => 5, 'unit_id' => 1, 'rank_id' => 17, 'revision' => 'R1',
                'thickness' => 1.2, 'width' => 232, 'length' => 1219, 'length_2' => null, 'pitch' => 395,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 40, 'material_spec_id' => 5, 'unit_id' => 1, 'rank_id' => 16, 'revision' => 'R1',
                'thickness' => 1.2, 'width' => 337, 'length' => 1219, 'length_2' => null, 'pitch' => 414,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 42, 'material_spec_id' => 4, 'unit_id' => 1, 'rank_id' => 13, 'revision' => 'R1',
                'thickness' => 1.2, 'width' => 398, 'length' => 1219, 'length_2' => null, 'pitch' => 236,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 39, 'material_spec_id' => 4, 'unit_id' => 1, 'rank_id' => 15, 'revision' => 'R1',
                'thickness' => 1.2, 'width' => 202, 'length' => 1219, 'length_2' => null, 'pitch' => 180,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 45, 'material_spec_id' => 6, 'unit_id' => 1, 'rank_id' => 14, 'revision' => 'R1',
                'thickness' => 1.4, 'width' => 427, 'length' => 1219, 'length_2' => null, 'pitch' => 47,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 44, 'material_spec_id' => 6, 'unit_id' => 1, 'rank_id' => 14, 'revision' => 'R1',
                'thickness' => 1.6, 'width' => 442, 'length' => 1219, 'length_2' => null, 'pitch' => 45,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 46, 'material_spec_id' => 6, 'unit_id' => 1, 'rank_id' => 27, 'revision' => 'R1',
                'thickness' => 1.6, 'width' => 332, 'length' => 1219, 'length_2' => null, 'pitch' => 271,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 55, 'material_spec_id' => 7, 'unit_id' => 1, 'rank_id' => 14, 'revision' => 'R2',
                'thickness' => 0.8, 'width' => 602, 'length' => 1219, 'length_2' => null, 'pitch' => 210,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 56, 'material_spec_id' => 9, 'unit_id' => 1, 'rank_id' => 17, 'revision' => 'R2',
                'thickness' => 1.6, 'width' => 456, 'length' => 1219, 'length_2' => null, 'pitch' => 173,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 18, 'material_spec_id' => 6, 'unit_id' => null, 'rank_id' => 14, 'revision' => 'R2',
                'thickness' => 0.6, 'width' => 355, 'length' => 499, 'length_2' => null, 'pitch' => null,
                'pcs_per_unit' => 1, 'unit_per_car' => 1, 'min_stock' => 90, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 50, 'material_spec_id' => 10, 'unit_id' => 1, 'rank_id' => 20, 'revision' => 'R2',
                'thickness' => 2.6, 'width' => 245, 'length' => 1219, 'length_2' => null, 'pitch' => 264,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
            [
                'product_id' => 43, 'material_spec_id' => 6, 'unit_id' => 1, 'rank_id' => 26, 'revision' => 'R2',
                'thickness' => 1.2, 'width' => 496, 'length' => 1219, 'length_2' => null, 'pitch' => 107,
                'pcs_per_unit' => 2, 'unit_per_car' => 2, 'min_stock' => 180, 'current_stock_qty' => 0.00,
                'trial_usage_qty' => 0.00, 'density' => null, 'weight_kg' => null, 'net_weight' => null,
                'material_price' => null, 'is_active' => 1, 'remark' => null,
            ],
        ];

        foreach ($products as $item) {
            InventoryProduct::updateOrCreate(
                ['product_id' => $item['product_id'], 'revision' => $item['revision']],
                $item
            );
        }
    }
}
