<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockMonitoringExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $data;
    protected $categories;

    public function __construct($data, $categories)
    {
        $this->data = $data;
        $this->categories = $categories;
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'Stock Monitoring Report';
    }

    public function headings(): array
    {
        $catCount = count($this->categories);
        
        // Dynamic Group Headers (Row 4)
        $groupHeaders = [
            'Product Details', // A (1)
            '', '', '', '',    // B-E (2-5)
            'Physical Specifications', // F (6)
            '', '', '', '', '', '', '', '', '', // G-O (7-15)
            'Usage & Movement', // P (16)
        ];
        
        // Movement padding (catCount categories + 1 for STO GAP)
        for ($i = 0; $i < $catCount; $i++) $groupHeaders[] = '';
        
        $groupHeaders[] = 'Inventory & Valuation'; // Next group
        $groupHeaders[] = ''; $groupHeaders[] = ''; $groupHeaders[] = ''; $groupHeaders[] = ''; // Min, Bal PCS, Bal Unit, Price
        
        $groupHeaders[] = 'Status & Remarks'; // Final group
        $groupHeaders[] = ''; $groupHeaders[] = ''; $groupHeaders[] = '';

        // Actual Column Headers (Row 5)
        $headers = [
            'No',              // A
            'Customer',        // B
            'Model',           // C
            'Part No',         // D
            'Part Name',       // E
            'Spec Name',       // F
            'Coating Type',    // G
            'Rank',            // H
            'Unit',            // I
            'Thickness',       // J
            'Width',           // K
            'Length',          // L
            'Length 2',        // M
            'Pitch',           // N
            'Weight (kg)',     // O
        ];

        foreach ($this->categories as $cat) {
            $headers[] = $cat->code;
        }

        $headers[] = 'STO GAP';
        $headers[] = 'Min Stock (PCS)';
        $headers[] = 'Balance (PCS)';
        $headers[] = 'Balance (Unit)';
        $headers[] = 'Material Price';
        $headers[] = 'Amount';
        $headers[] = 'Stock Status';
        $headers[] = 'Project Status';
        $headers[] = 'Status Remark';
        $headers[] = 'Remark';

        return [
            ['Stock Monitoring Report'],
            ['Generated at: ' . date('d M Y H:i')],
            [],
            $groupHeaders,
            $headers
        ];
    }

    public function map($row): array
    {
        static $no = 1;
        $balancePcs = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs(
            $row->current_stock_qty, 
            $row->weight_kg, 
            $row->pcs_per_unit, 
            $row->unit_name,
            $row->top_coil, $row->end_coil, $row->pitch, $row->pcs_per_pitch, $row->gross_coil
        );
        $amount = floatval($row->weight_kg) * floatval($row->material_price) * $balancePcs;
        $stockStatus = \App\Models\InventoryModel\Material\InventoryProduct::calculateStockStatus(
            $balancePcs, 
            $row->min_stock, 
            $row->model_project_status,
            $row->product_status
        );

        $mapped = [
            $no++,                                          // A: No
            $row->customer_code ?? '-',                     // B: Customer
            $row->model_name ?? '-',                        // C: Model
            $row->part_no . ($row->revision ? ' - ' . $row->revision : ''), // D: Part No
            $row->part_name ?? '-',                         // E: Part Name
            $row->spec_name ?? '-',                         // F: Spec Name
            $row->coating_type ?? '-',                      // G: Coating Type
            $row->rank_code ?? '-',                         // H: Rank
            $row->unit_name ?? '-',                         // I: Unit
            floatval($row->thickness),                      // J: Thickness
            floatval($row->width),                          // K: Width
            floatval($row->length),                         // L: Length
            floatval($row->length_2),                       // M: Length 2
            floatval($row->pitch),                          // N: Pitch
            floatval($row->weight_kg),                      // O: Weight
        ];

        foreach ($this->categories as $cat) {
            $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
            $qtyVal = $row->$alias ? floatval($row->$alias) : 0;
            $mapped[] = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs(
                $qtyVal, 
                $row->weight_kg, 
                $row->pcs_per_unit, 
                $row->unit_name,
                $row->top_coil, $row->end_coil, $row->pitch, $row->pcs_per_pitch, $row->gross_coil
            );
        }

        $mapped[] = $row->sto_gap ? \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs(
            floatval($row->sto_gap), 
            $row->weight_kg, 
            $row->pcs_per_unit, 
            $row->unit_name,
            $row->top_coil, $row->end_coil, $row->pitch, $row->pcs_per_pitch, $row->gross_coil
        ) : 0; // STO GAP
        $mapped[] = number_format(floatval($row->min_stock), 0, '.', ''); // Min Stock
        $mapped[] = number_format($balancePcs, 0, '.', '');         // Balance (PCS)
        $mapped[] = number_format(floatval($row->current_stock_qty), 0, '.', ''); // Balance (Unit)
        $mapped[] = floatval($row->material_price ?? 0);             // Material Price
        $mapped[] = $amount; // Amount
        $mapped[] = $stockStatus; // Stock Status
        $mapped[] = $row->product_status ?: ($row->model_project_status ?? 'Project'); // Project Status
        $mapped[] = $row->product_status_remark ?? '-'; // Status Remark
        $mapped[] = $row->remark ?? '-'; // Remark

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        $catCount = count($this->categories);
        $lastColNum = 25 + $catCount;
        $lastCol = $this->getColumnLetter($lastColNum);
        
        // Merge Group Headers (Row 4)
        $sheet->mergeCells('A4:E4'); // Product Details
        $sheet->mergeCells('F4:O4'); // Physical Specs
        $sheet->mergeCells($this->getColumnLetter(16) . '4:' . $this->getColumnLetter(16 + $catCount) . '4'); // Movement
        $sheet->mergeCells($this->getColumnLetter(16 + $catCount + 1) . '4:' . $this->getColumnLetter(16 + $catCount + 5) . '4'); // Inventory
        $sheet->mergeCells($this->getColumnLetter(16 + $catCount + 6) . '4:' . $lastCol . '4'); // Status

        // Conditional Formatting for Stock Status Column
        $statusColNum = 16 + $catCount + 6;
        $statusCol = $this->getColumnLetter($statusColNum);
        $highestRow = $sheet->getHighestRow();

        for ($rowNum = 6; $rowNum <= $highestRow; $rowNum++) {
            $status = $sheet->getCell($statusCol . $rowNum)->getValue();
            $bgColor = null;
            $textColor = '000000';

            switch ($status) {
                case 'Safe':
                    $bgColor = '10B981'; // Emerald
                    $textColor = 'FFFFFF';
                    break;
                case 'Over':
                    $bgColor = '3B82F6'; // Blue
                    $textColor = 'FFFFFF';
                    break;
                case 'Warning':
                    $bgColor = 'F59E0B'; // Amber
                    $textColor = 'FFFFFF';
                    break;
                case 'Critical':
                    $bgColor = 'EF4444'; // Red
                    $textColor = 'FFFFFF';
                    break;
            }

            if ($bgColor) {
                $sheet->getStyle($statusCol . $rowNum)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $bgColor]
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
            }
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '475569'] // Slate-600
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ],
            5 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D1D5DB'] // Gray-300
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ],
        ];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 12,  // Customer
            'C' => 15,  // Model
            'D' => 25,  // Part No
            'E' => 45,  // Part Name
            'F' => 22,  // Spec
            'G' => 15,  // Coating
            'H' => 10,  // Rank
            'I' => 12,  // Unit
            'J' => 10,  // T
            'K' => 10,  // W
            'L' => 10,  // L
            'M' => 10,  // L2
            'N' => 10,  // P
            'O' => 12,  // Weight
        ];
        
        $catCount = count($this->categories);
        // Movement + STO GAP
        for ($i = 0; $i <= $catCount; $i++) {
             $widths[$this->getColumnLetter(16 + $i)] = 12;
        }
        
        $inventoryStart = 16 + $catCount + 1;
        $widths[$this->getColumnLetter($inventoryStart)] = 15;     // Min Stock
        $widths[$this->getColumnLetter($inventoryStart + 1)] = 15; // Bal PCS
        $widths[$this->getColumnLetter($inventoryStart + 2)] = 15; // Bal Unit
        $widths[$this->getColumnLetter($inventoryStart + 3)] = 15; // Price
        $widths[$this->getColumnLetter($inventoryStart + 4)] = 18; // Amount
        
        $statusStart = $inventoryStart + 5;
        $widths[$this->getColumnLetter($statusStart)] = 18;     // Stock Status
        $widths[$this->getColumnLetter($statusStart + 1)] = 18; // Project Status
        $widths[$this->getColumnLetter($statusStart + 2)] = 25; // Status Remark
        $widths[$this->getColumnLetter($statusStart + 3)] = 30; // Remark

        return $widths;
    }

    // (calculateStockStatus helper was redundant, better to use the centralized one in InventoryProduct)

    private function getColumnLetter($index)
    {
        $letter = '';
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letter = chr(65 + $remainder) . $letter;
            $index = intval(($index - $remainder) / 26);
        }
        return $letter;
    }
}
