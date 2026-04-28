<?php

namespace App\Imports;

use App\Models\InventoryModel\Material\VaveBase;
use App\Models\InventoryModel\Material\VaveBaseSuffix;
use App\Models\InventoryModel\Material\MaterialSpec;
use App\Models\InventoryModel\Material\Unit;
use App\Models\Products;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Exception;

class VaveBaseImport implements ToCollection, WithStartRow, WithMultipleSheets
{
    private $sheetName;
    private $customerId;
    private $modelId;
    private $processedProducts = [];
    private $errors = [];
    private $successLog = [
        'created' => [],
        'updated' => [],
        'unchangedCount' => 0
    ];

    public function __construct($sheetName, $customerId = null, $modelId = null)
    {
        $this->sheetName = strval($sheetName);
        $this->customerId = $customerId;
        $this->modelId = $modelId;
    }

    public function sheets(): array
    {
        return [
            $this->sheetName => $this,
        ];
    }

    public function startRow(): int
    {
        return 7;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessLog()
    {
        return $this->successLog;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 7;

                // Part No: Index 2 (Column C)
                $partNo = trim($row[2] ?? '');
                if (empty($partNo)) continue;

                // Model: Index 0 (Column A)
                $modelName = trim($row[0] ?? '');

                // Resolve Product (Search by part_no, and strict filter by UI customer/model if available)
                $productQuery = Products::where('part_no', $partNo);
                
                if (!empty($this->customerId)) {
                    $productQuery->where('customer_id', $this->customerId);
                }
                if (!empty($this->modelId)) {
                    $productQuery->where('model_id', $this->modelId);
                } elseif (!empty($modelName)) {
                    // Fallback to Excel Model column if UI filters were not set
                    $productQuery->whereHas('model', function($q) use ($modelName) {
                        $q->where('name', $modelName);
                    });
                }
                
                $product = $productQuery->first();
                
                if (!$product) {
                    $this->errors[] = "Row {$rowNum}: Part No '{$partNo}' not found in Product Master.";
                    continue;
                }

                $maxCols = $row->count();
                // EBD data starts at Index 4 (Column E), block size is 16 columns
                for ($col = 4; $col < $maxCols; $col += 16) {
                    $baseName = trim($row[$col] ?? '');
                    if (empty($baseName)) break;

                    $vaveErrors = [];

                    // Mapping Column Values
                    $suffixName = trim($row[$col + 1] ?? '');
                    $msName = trim($row[$col + 2] ?? '');
                    $unitName = trim($row[$col + 3] ?? '');
                    $density = $this->parseNumeric($row[$col + 4] ?? 7.85);
                    $thickness = $this->parseNumeric($row[$col + 5] ?? 0);
                    $width = $this->parseNumeric($row[$col + 6] ?? 0);
                    $length = $this->parseNumeric($row[$col + 7] ?? 0);
                    $length2 = $this->parseNumeric($row[$col + 8] ?? 0);
                    $pitch = $this->parseNumeric($row[$col + 9] ?? 0);
                    $weightKg = $this->parseNumeric($row[$col + 10] ?? 0);
                    $netWeight = $this->parseNumeric($row[$col + 11] ?? 0);
                    $pcsPerPitch = max(1, (int)($row[$col + 12] ?? 1));
                    $pcsPerUnit = max(1, (int)($row[$col + 13] ?? 1));
                    $price = $this->parseNumeric($row[$col + 14] ?? 0);
                    $remark = trim($row[$col + 15] ?? '');
                    $weight = $weightKg;

                    // Automatic Weight Calculation if zero or not provided (Mirroring JS logic in index.blade.php)
                    if ($weight <= 0) {
                        $unitLower = strtolower($unitName);
                        if (str_contains($unitLower, 'sheet')) {
                            // Formula: (t x w x l x d / 1,000,000) / pcs_per_unit
                            $weight = (($thickness * $width * $length * $density) / 1000000) / $pcsPerUnit;
                        } elseif (str_contains($unitLower, 'coil')) {
                            // Formula: (t x w x pitch x d / 1,000,000) / pcs_per_pitch
                            $weight = (($thickness * $width * $pitch * $density) / 1000000) / $pcsPerPitch;
                        } elseif (str_contains($unitLower, 'trapezoid')) {
                            $avgL = ($length + $length2) / 2;
                            // Formula: (t x w x avgL x d / 1,000,000) / pcs_per_unit
                            $weight = (($thickness * $width * $avgL * $density) / 1000000) / $pcsPerUnit;
                        } else {
                            $weight = (($thickness * $width * $length * $density) / 1000000) / $pcsPerUnit;
                        }
                    }

                    // Resolve Relationships
                    $suffix = !empty($suffixName) ? VaveBaseSuffix::where('name', $suffixName)->where('customer_id', $product->customer_id)->first() : null;
                    if (!empty($suffixName) && !$suffix) $vaveErrors[] = "EBD Suffix '{$suffixName}' not found for customer '" . ($product->customer->code ?? 'Unknown') . "'.";

                    $ms = !empty($msName) ? MaterialSpec::where('spec_name', $msName)->first() : null;
                    if (!empty($msName) && !$ms) $vaveErrors[] = "Material Spec '{$msName}' not found.";

                    $unit = !empty($unitName) ? Unit::where('name', 'like', "%{$unitName}%")->first() : null;
                    if (!empty($unitName) && !$unit) $vaveErrors[] = "Unit '{$unitName}' not found.";

                    if (!empty($vaveErrors)) {
                        $this->errors[] = "Row {$rowNum} [{$partNo}|{$baseName}]: " . implode(", ", $vaveErrors);
                        continue;
                    }

                    $data = [
                        'product_id' => $product->id,
                        'base_name' => $baseName,
                        'vave_base_suffix_id' => $suffix ? $suffix->id : null,
                        'material_spec_id' => $ms ? $ms->id : null,
                        'unit_id' => $unit ? $unit->id : null,
                        'density' => round($density, 4),
                        'thickness' => round($thickness, 4),
                        'width' => round($width, 4),
                        'length' => round($length, 4),
                        'length_2' => round($length2, 4),
                        'pitch' => round($pitch, 4),
                        'pcs_per_unit' => $pcsPerUnit,
                        'pcs_per_pitch' => $pcsPerPitch,
                        'weight_kg' => round($weight, 4),
                        'net_weight' => round($netWeight, 4),
                        'material_price' => round($price, 4),
                        'remark' => $remark,
                        'is_active' => 1,
                    ];

                    $existing = VaveBase::where([
                        'product_id' => $product->id,
                        'base_name' => $baseName
                    ])->first();

                    // Deactivate others for this product ONLY ONCE per product per import
                    // to ensure the imported bases from this file are the ones that end up active.
                    if (!in_array($product->id, $this->processedProducts)) {
                        VaveBase::where('product_id', $product->id)->update(['is_active' => 0]);
                        $this->processedProducts[] = $product->id;
                    }

                    if ($existing) {
                        $changes = [];
                        foreach ($data as $key => $value) {
                            if ($existing->{$key} != $value) {
                                $changes[] = $key;
                            }
                        }
                        
                        if (!empty($changes)) {
                            $existing->update($data);
                            $this->successLog['updated'][] = "{$partNo} [{$baseName}] (Updated: " . implode(', ', $changes) . ")";
                        } else {
                            // Force active state to 1 in DB even if no other data changes,
                            // because it might have been deactivated by a previous row's raw query.
                            $existing->is_active = 1;
                            $existing->save();
                            
                            // If Eloquent still skips because it thinks it's already 1, do a raw update
                            VaveBase::where('id', $existing->id)->update(['is_active' => 1]);
                            
                            $this->successLog['unchangedCount']++;
                        }
                    } else {
                        VaveBase::create($data);
                        $this->successLog['created'][] = "{$partNo} [{$baseName}]";
                    }
                }
            }

            if (!empty($this->errors)) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->errors[] = "Critical Processing Error: " . $e->getMessage();
        }
    }

    private function parseNumeric($value)
    {
        if (is_numeric($value)) return (float)$value;
        if (empty($value)) return 0;
        $normalized = str_replace(',', '.', $value);
        return floatval($normalized);
    }
}
