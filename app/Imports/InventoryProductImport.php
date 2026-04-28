<?php

namespace App\Imports;

use App\Models\InventoryModel\Material\InventoryProduct;
use App\Models\InventoryModel\Material\MaterialSpec;
use App\Models\InventoryModel\Material\Rank;
use App\Models\InventoryModel\Material\Unit;
use App\Models\InventoryModel\Material\Revision;
use App\Models\Products;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Exception;

class InventoryProductImport implements ToCollection, WithStartRow, WithMultipleSheets
{
    private $customerId;
    private $modelId;
    private $sheetName;
    private $errors = [];
    private $successLog = [
        'created' => [],
        'updated' => [],
        'unchangedCount' => 0
    ];

    public function __construct($customerId, $modelId, $sheetName)
    {
        $this->customerId = $customerId;
        $this->modelId = $modelId;
        $this->sheetName = strval($sheetName);
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

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
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
                
                // If Part No is empty, we skip this row
                if (empty($partNo)) continue;

                // Track row-specific errors for grouping
                $rowErrors = [];

                // Rank: Index 5 (Column F) - This rank applies to all revisions in this row
                $rankName = trim($row[5] ?? '');
                $rank = null;
                if (!empty($rankName)) {
                    $rank = Rank::whereRaw('UPPER(code) = ?', [strtoupper($rankName)])->first();
                    if (!$rank) $rowErrors[] = "Rank '{$rankName}' not found.";
                }

                // Resolve Product Base (Strict Check)
                $product = Products::where('part_no', $partNo)
                    ->where('customer_id', $this->customerId)
                    ->first();
                
                if (!$product) {
                    $this->errors[] = "Row {$rowNum}: Part No '{$partNo}' not found in Global Product Master for this Customer.";
                    continue;
                }

                $maxCols = $row->count();
                for ($col = 8; $col < $maxCols; $col += 17) {
                    $revisionCode = trim($row[$col] ?? '');
                    if (empty($revisionCode)) break;

                    $revErrors = [];

                    // 1. Resolve Revision ID
                    $revision = Revision::whereRaw('UPPER(code) = ?', [strtoupper($revisionCode)])
                        ->where('is_active', 1)
                        ->first();
                    
                    if (!$revision) {
                        $revErrors[] = "Revision '{$revisionCode}' not found.";
                    }

                    // 2. Map Column Values
                    $msName = trim($row[$col + 1] ?? '');
                    $unitName = trim($row[$col + 2] ?? '');
                    $density = $this->parseNumeric($row[$col + 3] ?? 7.85);
                    $thickness = $this->parseNumeric($row[$col + 4] ?? 0);
                    $width = $this->parseNumeric($row[$col + 5] ?? 0);
                    $length = $this->parseNumeric($row[$col + 6] ?? 0);
                    $length2 = $this->parseNumeric($row[$col + 7] ?? 0);
                    $pitch = $this->parseNumeric($row[$col + 8] ?? 0);
                    $weightInput = $this->parseNumeric($row[$col + 9] ?? 0);
                    $netWeight = $this->parseNumeric($row[$col + 10] ?? 0);
                    $pcsPerPitch = (int)($row[$col + 11] ?? 0);
                    $pcsPerUnit = (int)($row[$col + 12] ?? 1);
                    $unitPerCar = (int)($row[$col + 13] ?? 1);
                    $minStockInput = (int)($row[$col + 14] ?? 0);
                    $priceInput = $this->parseNumeric($row[$col + 15] ?? 0);
                    $remark = trim($row[$col + 16] ?? '');

                    // 3. Resolve Relationships
                    $ms = !empty($msName) ? MaterialSpec::where('spec_name', $msName)->first() : null;
                    if (!empty($msName) && !$ms) $revErrors[] = "Material Spec '{$msName}' not found.";

                    $unit = !empty($unitName) ? Unit::where('name', 'like', "%{$unitName}%")->first() : null;
                    if (!empty($unitName) && !$unit) $revErrors[] = "Unit '{$unitName}' (or similar) not found.";

                    // If there are errors for this specific revision, collect them and skip processing this revision
                    if (!empty($rowErrors) || !empty($revErrors)) {
                        $combined = array_unique(array_merge($rowErrors, $revErrors));
                        $this->errors[] = "Row {$rowNum} [{$partNo}|{$revisionCode}]: " . implode(", ", $combined);
                        continue;
                    }

                    // 4. Fallback Calculations
                    $finalWeight = $weightInput;
                    if ($weightInput <= 0) {
                        $finalWeight = InventoryProduct::calculateWeight(
                            $unit ? $unit->name : '',
                            $thickness,
                            $width,
                            $length,
                            $length2,
                            $pitch,
                            $density,
                            $pcsPerUnit,
                            $pcsPerPitch
                        );
                    }

                    $finalMinStock = $minStockInput;
                    if ($minStockInput <= 0) {
                        $finalMinStock = InventoryProduct::calculateMinStock($unitPerCar);
                    }

                    $finalPrice = $priceInput;
                    if ($priceInput <= 0) {
                        $finalPrice = 20000;
                    }

                    $data = [
                        'product_id' => $product->id,
                        'model_id' => $this->modelId,
                        'revision_id' => $revision->id,
                        'material_spec_id' => $ms ? $ms->id : null,
                        'unit_id' => $unit ? $unit->id : null,
                        'rank_id' => $rank ? $rank->id : null,
                        'thickness' => $thickness,
                        'width' => $width,
                        'length' => $length,
                        'length_2' => $length2,
                        'pitch' => $pitch,
                        'density' => $density,
                        'weight_kg' => round($finalWeight, 4),
                        'net_weight' => round($netWeight, 4),
                        'pcs_per_pitch' => $pcsPerPitch,
                        'pcs_per_unit' => $pcsPerUnit ?: 1,
                        'unit_per_car' => $unitPerCar ?: 1,
                        'min_stock' => $finalMinStock,
                        'material_price' => $finalPrice,
                        'remark' => $remark,
                        'is_active' => 1
                    ];

                    $existing = InventoryProduct::where([
                        'product_id' => $product->id,
                        'model_id' => $this->modelId,
                        'revision_id' => $revision->id
                    ])->first();

                    if ($existing) {
                        // Detect what changed
                        $changes = [];
                        foreach ($data as $key => $value) {
                            if ($existing->{$key} != $value) {
                                $changes[] = $key;
                            }
                        }
                        
                        if (!empty($changes)) {
                            $existing->update($data);
                            $this->successLog['updated'][] = "{$partNo} rev {$revisionCode} (Updated: " . implode(', ', $changes) . ")";
                        } else {
                            $this->successLog['unchangedCount']++;
                        }
                    } else {
                        InventoryProduct::create($data);
                        $this->successLog['created'][] = "{$partNo} rev {$revisionCode}";
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
