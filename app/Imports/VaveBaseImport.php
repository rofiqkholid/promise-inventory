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

class VaveBaseImport implements WithMultipleSheets
{
    private $sheetName;
    private $customerId;
    private $modelId;
    private $isRegular;
    private $errors = [];
    private $successLog = [];

    public function __construct($sheetName, $customerId = null, $modelId = null, $isRegular = false)
    {
        $this->sheetName = strval($sheetName);
        $this->customerId = $customerId;
        $this->modelId = $modelId;
        $this->isRegular = $isRegular;
    }

    public function sheets(): array
    {
        $sheetImport = new VaveBaseImportSheet($this->customerId, $this->modelId, $this->isRegular);
        
        // Store reference to collect logs later
        $this->sheetImport = $sheetImport;

        return [
            $this->sheetName => $sheetImport,
        ];
    }

    public function getErrors()
    {
        return $this->sheetImport ? $this->sheetImport->getErrors() : [];
    }

    public function getSuccessLog()
    {
        return $this->sheetImport ? $this->sheetImport->getSuccessLog() : [
            'created' => [],
            'updated' => [],
            'unchangedCount' => 0
        ];
    }
}

class VaveBaseImportSheet implements ToCollection, WithStartRow
{
    private $customerId;
    private $modelId;
    private $isRegular;
    private $processedProducts = [];
    private $errors = [];
    private $successLog = [
        'created' => [],
        'updated' => [],
        'unchangedCount' => 0
    ];

    public function __construct($customerId, $modelId, $isRegular = false)
    {
        $this->customerId = $customerId;
        $this->modelId = $modelId;
        $this->isRegular = $isRegular;
    }

    public function startRow(): int
    {
        return 7;
    }

    public function getErrors() { return $this->errors; }
    public function getSuccessLog() { return $this->successLog; }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            $skippedEmptyPartNo = 0;
            $skippedEmptyEbdVersion = 0;
            $skippedDueToPrefix = 0;
            $processedAny = false;

            foreach ($rows as $index => $row) {
                $rowNum = $index + 7;

                // Part No: Index 2 (Column C)
                $partNo = trim($row[2] ?? '');
                
                // If Column C is empty, check Column B (Index 1) just in case they shifted
                if (empty($partNo) && !empty(trim($row[1] ?? '')) && !is_numeric(trim($row[1] ?? ''))) {
                    $partNo = trim($row[1] ?? '');
                }

                if (empty($partNo)) {
                    $skippedEmptyPartNo++;
                    continue;
                }

                // Resolve Product
                $productQuery = Products::where('part_no', $partNo);
                if (!empty($this->customerId)) {
                    $productQuery->where('customer_id', $this->customerId);
                }
                
                $product = $productQuery->first();
                if (!$product) {
                    $this->errors[] = "Row {$rowNum}: Part No '{$partNo}' not found in Product Master for the selected customer. Please make sure the Part No exists in Product Master Data (Data Master Product) or create it first.";
                    continue;
                }

                $maxCols = $row->count();
                $foundEbdInRow = false;
                // EBD data starts at Index 4 (Column E), block size is 16 columns
                for ($col = 4; $col < $maxCols; $col += 16) {
                    $baseName = trim($row[$col] ?? '');
                    if (empty($baseName)) continue; // Try next block instead of break

                    // Strict Module Filtering
                    if ($this->isRegular) {
                        // In Regular mode, we ONLY read baselines starting with SQ.
                        // We DO NOT convert EBD to SQ. We simply ignore EBD data.
                        if (stripos($baseName, 'SQ') !== 0) {
                            $skippedDueToPrefix++;
                            continue;
                        }
                    } else {
                        // In Project mode, we ONLY read baselines starting with EBD.
                        // We ignore any SQ data present in the file.
                        if (stripos($baseName, 'EBD') !== 0) {
                            $skippedDueToPrefix++;
                            continue;
                        }
                    }

                    $foundEbdInRow = true;
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
                    $pcsPerPitch = (int)($row[$col + 12] ?? 0);
                    $pcsPerUnit = (int)($row[$col + 13] ?? 0);
                    $price = $this->parseNumeric($row[$col + 14] ?? 0);
                    $remark = trim($row[$col + 15] ?? '');
                    $weight = $weightKg;

                    // Calculation logic
                    if ($weight <= 0) {
                        $unitLower = strtolower($unitName);
                        if (str_contains($unitLower, 'sheet')) {
                            $weight = (($thickness * $width * $length * $density) / 1000000) / max(1, $pcsPerUnit);
                        } elseif (str_contains($unitLower, 'coil')) {
                            $weight = (($thickness * $width * $pitch * $density) / 1000000) / max(1, $pcsPerPitch);
                        } elseif (str_contains($unitLower, 'trapezoid')) {
                            $avgL = ($length + $length2) / 2;
                            $weight = (($thickness * $width * $avgL * $density) / 1000000) / max(1, $pcsPerUnit);
                        } else {
                            $weight = (($thickness * $width * $length * $density) / 1000000) / max(1, $pcsPerUnit);
                        }
                    }

                    // Relationships
                    $suffix = !empty($suffixName) ? VaveBaseSuffix::where('name', $suffixName)->where('customer_id', $product->customer_id)->first() : null;
                    if (!empty($suffixName) && !$suffix) {
                        $vaveErrors[] = "EBD Suffix '{$suffixName}' not found.";
                    }

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
                        'effective_from' => (int) date('Y'),
                        'is_active' => 1,
                    ];

                    $existing = VaveBase::where(['product_id' => $product->id, 'base_name' => $baseName])->first();

                    if ($existing) {
                        // Detect what changed (exclude is_active and effective_from from smart comparison)
                        $changes = [];
                        $comparableFields = array_diff_key($data, array_flip(['is_active', 'effective_from']));
                        
                        foreach ($comparableFields as $key => $value) {
                            $oldVal = $existing->{$key};
                            
                            // Specific check for numeric fields to handle precision
                            if (is_numeric($value) && is_numeric($oldVal)) {
                                if (round((float)$oldVal, 4) != round((float)$value, 4)) {
                                    $changes[] = $key;
                                }
                            } else if ($oldVal != $value) {
                                $changes[] = $key;
                            }
                        }

                        if (!empty($changes)) {
                            // Jika sudah ada, jangan timpa effective_from kecuali masih kosong
                            if (!empty($existing->effective_from)) {
                                unset($data['effective_from']);
                            }

                            // Jangan ubah status is_active yang sudah ada saat update
                            unset($data['is_active']);

                            $existing->update($data);
                            $this->successLog['updated'][] = "{$partNo} [{$baseName}] (Updated: " . implode(', ', $changes) . ")";
                        } else {
                            $this->successLog['unchangedCount']++;
                        }
                    } else {
                        // Data baru tetap dibuat aktif secara default
                        VaveBase::create($data);
                        $this->successLog['created'][] = "{$partNo} [{$baseName}]";
                    }
                    $processedAny = true;
                }

                if (!$foundEbdInRow && !empty($partNo)) {
                    $skippedEmptyEbdVersion++;
                }
            }

            if ($skippedDueToPrefix > 0) {
                $requiredPrefix = $this->isRegular ? 'SQ' : 'EBD';
                $moduleType = $this->isRegular ? 'Regular VAVE' : 'Project VAVE';
                $this->errors[] = "Skipped {$skippedDueToPrefix} versions because they did not start with '{$requiredPrefix}' (Required for {$moduleType} import).";
            }

            if (!$processedAny) {
                if ($skippedEmptyPartNo > 0) {
                    $this->errors[] = "Skipped {$skippedEmptyPartNo} rows because 'Part No' in Column C was empty.";
                }
                if ($skippedEmptyEbdVersion > 0) {
                    $this->errors[] = "Skipped {$skippedEmptyEbdVersion} rows because 'EBD Version' in Column E was empty or didn't match prefix requirements.";
                }
                
                // Diagnostic info: Show what's in the first row
                if (!$rows->isEmpty()) {
                    $firstRow = $rows->first();
                    $sample = [];
                    for ($i = 0; $i < 10; $i++) {
                        $val = trim($firstRow[$i] ?? 'NULL');
                        $colLetter = chr(65 + $i);
                        $sample[] = "{$colLetter}: '{$val}'";
                    }
                    $this->errors[] = "System sees this on Row 7: " . implode(", ", $sample);
                    $requiredPrefix = $this->isRegular ? 'SQ' : 'EBD';
                    $this->errors[] = "Please make sure 'Part No' is in Column C and 'EBD Version' in Column E starts with '{$requiredPrefix}'.";
                }

                if (count($rows) == 0) {
                    $this->errors[] = "The selected sheet appears to have no data rows after row 6.";
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->errors[] = "Critical Processing Error: " . $e->getMessage();
        }
    }

    private function parseNumeric($value)
    {
        if (is_numeric($value)) return (float)$value;
        if (empty($value)) return 0;
        return floatval(str_replace(',', '.', $value));
    }
}

