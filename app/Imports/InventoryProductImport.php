<?php

namespace App\Imports;

use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\MaterialSpec;
use App\Models\InventoryModel\Rank;
use App\Models\InventoryModel\Unit;
use App\Models\InventoryModel\Revision;
use App\Models\Products;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class InventoryProductImport implements ToCollection, WithHeadingRow
{
    private $errors = [];
    private $successCount = 0;

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +1 for 0-index, +1 for header

                $partNo = trim($row['part_no'] ?? '');
                $customerCode = trim($row['customer_code'] ?? '');
                $modelName = trim($row['model_name'] ?? '');
                $revisionCode = trim($row['revision_code'] ?? '');

                if (empty($partNo) || empty($customerCode) || empty($modelName) || empty($revisionCode)) {
                    // Skip empty rows or minimal rows
                    if (empty($partNo) && empty($customerCode) && empty($modelName)) continue;
                    
                    $this->errors[] = "Row {$rowNum}: Missing required fields (Part No, Customer Code, Model Name, or Revision Code).";
                    continue;
                }

                // 1. Resolve Customer ID
                $customer = DB::table('customers')->where('code', $customerCode)->first();
                if (!$customer) {
                    $this->errors[] = "Row {$rowNum}: Customer '{$customerCode}' not found.";
                    continue;
                }

                // 2. Resolve or Create Product ID
                $product = Products::where('part_no', $partNo)
                    ->where('customer_id', $customer->id)
                    ->first();
                if (!$product) {
                    // Create basic product if it does not exist
                    $product = Products::create([
                        'part_no' => $partNo,
                        'part_name' => trim($row['part_name'] ?? $partNo), // Fallback to part_no if part_name is not provided
                        'customer_id' => $customer->id,
                        // Not strictly defining model here as it's defined in detail, but DB structure might require it
                    ]);
                }

                // 3. Resolve Model ID
                $model = DB::table('models')
                    ->where('name', $modelName)
                    ->where('customer_id', $customer->id)
                    ->first();
                if (!$model) {
                    // Fallback to name search only if not found by customer
                    $model = DB::table('models')->where('name', $modelName)->first();
                    if (!$model) {
                        $this->errors[] = "Row {$rowNum}: Model '{$modelName}' not found.";
                        continue;
                    }
                }

                // 4. Resolve Revision ID
                $revision = Revision::where('code', $revisionCode)->where('is_active', 1)->first();
                if (!$revision) {
                    $this->errors[] = "Row {$rowNum}: Revision '{$revisionCode}' not found.";
                    continue;
                }

                // Optional resolves
                $materialSpecId = null;
                if (!empty(trim($row['material_spec_name'] ?? ''))) {
                    $ms = MaterialSpec::where('spec_name', trim($row['material_spec_name']))->first();
                    $ms ? ($materialSpecId = $ms->id) : ($this->errors[] = "Row {$rowNum}: Material Spec not found, but it was skipped.");
                }

                $unitId = null;
                if (!empty(trim($row['unit_name'] ?? ''))) {
                    $unit = Unit::where('name', trim($row['unit_name']))->first();
                    $unit ? ($unitId = $unit->id) : ($this->errors[] = "Row {$rowNum}: Unit not found.");
                }

                $rankId = null;
                if (!empty(trim($row['rank_code'] ?? ''))) {
                    $rank = Rank::where('code', trim($row['rank_code']))->first();
                    $rank ? ($rankId = $rank->id) : ($this->errors[] = "Row {$rowNum}: Rank not found.");
                }

                // Check if existing product detail with exactly same PK exists
                // We shouldn't overwrite unless intended, for now let's just create new or update if same revision and model
                $existingProductDetail = InventoryProduct::where('product_id', $product->id)
                    ->where('model_id', $model->id)
                    ->where('revision_id', $revision->id)
                    ->first();

                // Build Data array
                $thickness = empty($row['thickness']) ? 0 : floatval($row['thickness']);
                $width = empty($row['width']) ? 0 : floatval($row['width']);
                $length = empty($row['length']) ? 0 : floatval($row['length']);
                $length2 = empty($row['length_2']) ? 0 : floatval($row['length_2']);
                $pitch = empty($row['pitch']) ? 0 : floatval($row['pitch']);
                $density = empty(trim($row['density'] ?? '')) ? 7.85 : floatval($row['density']);

                // Calculate Weight (Kg)
                $weightKg = 0;
                if ($unit) {
                    $unitNameLower = strtolower($unit->name);
                    if (str_contains($unitNameLower, 'sheet')) {
                        $weightKg = ($thickness * $width * $length * $density) / 1000000;
                    } elseif (str_contains($unitNameLower, 'coil')) {
                        $weightKg = ($thickness * $width * $pitch * $density) / 1000000;
                    } elseif (str_contains($unitNameLower, 'trapezoid')) {
                        $weightKg = ($thickness * $width * (($length + $length2) / 2) * $density) / 1000000;
                    } else {
                        $weightKg = ($thickness * $width * $length * $density) / 1000000;
                    }
                }

                $data = [
                    'product_id' => $product->id,
                    'model_id' => $model->id,
                    'revision_id' => $revision->id,
                    'material_spec_id' => $materialSpecId,
                    'unit_id' => $unitId,
                    'rank_id' => $rankId,
                    'thickness' => $thickness,
                    'width' => $width,
                    'length' => $length,
                    'length_2' => $length2,
                    'pitch' => $pitch,
                    'density' => $density,
                    'weight_kg' => round($weightKg, 3),
                    'net_weight' => empty(trim($row['net_weight_kg'] ?? '')) ? 0 : floatval($row['net_weight_kg']),
                    'material_price' => empty(trim($row['material_price'] ?? '')) ? 20000 : floatval($row['material_price']),
                    'pcs_per_unit' => empty(trim($row['pcs_per_unit'] ?? '')) ? 1 : intval($row['pcs_per_unit']),
                    'unit_per_car' => empty(trim($row['unit_per_car'] ?? '')) ? 1 : intval($row['unit_per_car']),
                    'min_stock' => empty(trim($row['min_stock'] ?? '')) ? 0 : intval($row['min_stock']),
                    'remark' => trim($row['remark'] ?? ''),
                ];

                // Remove exact null validation issue if needed depending on eloquent defaults
                if ($existingProductDetail) {
                    $existingProductDetail->update($data);
                    $this->successCount++;
                } else {
                    InventoryProduct::create($data);
                    $this->successCount++;
                }
            }

            if (empty($this->errors)) {
                DB::commit();
            } else {
                DB::rollBack();
            }

        } catch (Exception $e) {
            DB::rollBack();
            $this->errors[] = "Critical Error: " . $e->getMessage();
        }
    }
}
