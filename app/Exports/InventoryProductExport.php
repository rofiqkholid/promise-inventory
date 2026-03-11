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

class InventoryProductExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'Inventory Product Master';
    }

    public function headings(): array
    {
        return [
            ['Inventory Product Master Data'],
            ['Generated at: ' . date('d M Y H:i')],
            [],
            [
                'Product Identification', // A (1)
                '', '', '', '', '',       // B-F (2-6)
                'Physical Specifications', // G (7)
                '', '', '', '', '', '', '', '', '', '', '', // H-R (8-18)
                'Inventory & Settings', // S (19)
                '', '', '',             // T-V (20-22)
                'Status & Administration' // W (23)
            ],
            [
                'No',              // A
                'Customer',        // B
                'Model',           // C
                'Part No',         // D
                'Part Name',       // E
                'Revision',        // F
                'Material Spec',   // G
                'Coating Type',    // H
                'Thickness',       // I
                'Width',           // J
                'Length',          // K
                'Length 2',        // L
                'Pitch',           // M
                'Density',         // N
                'Weight (Kg)',     // O
                'Net Weight (Kg)', // P
                'Unit',            // Q
                'Rank',            // R
                'Pcs/Unit',        // S
                'Unit/Car',        // T
                'Min Stock',       // U
                'Material Price',  // V
                'Project Status',  // W
                'Status Override', // X
                'Status Remark',   // Y
                'Remark',          // Z
                'Updated At'       // AA
            ]
        ];
    }

    public function map($row): array
    {
        static $no = 1;
        return [
            $no++,                                          // A: No
            $row->customer_code,                            // B: Customer
            $row->model_name,                               // C: Model
            $row->part_no,                                  // D: Part No
            $row->part_name,                                // E: Part Name
            $row->revision_code ?: '-',                     // F: Revision
            $row->material_spec_name ?: '-',                // G: Spec
            $row->coating_type ?: '-',                      // H: Coating
            floatval($row->thickness),                      // I: T
            floatval($row->width),                          // J: W
            floatval($row->length),                         // K: L
            floatval($row->length_2),                       // L: L2
            floatval($row->pitch),                          // M: P
            floatval($row->density),                        // N: Density
            floatval($row->weight_kg),                      // O: Weight
            floatval($row->net_weight),                     // P: Net Weight
            $row->unit_name ?: '-',                         // Q: Unit
            $row->rank_code ?: '-',                         // R: Rank
            intval($row->pcs_per_unit),                     // S: Pcs/Unit
            intval($row->unit_per_car),                     // T: Unit/Car
            intval($row->min_stock),                        // U: Min Stock
            floatval($row->material_price),                 // V: Price
            $row->model_project_status ?: 'Project',        // W: Project Status
            $row->product_status ?: '-',                    // X: Status Override
            $row->product_status_remark ?: '-',             // Y: Status Remark
            $row->remark ?: '-',                            // Z: Remark
            $row->updated_at ? \Carbon\Carbon::parse($row->updated_at)->format('d/m/Y H:i') : '-' // AA: Updated
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge Group Headers (Row 4)
        $sheet->mergeCells('A4:F4');   // Identification
        $sheet->mergeCells('G4:R4');   // Physical Specs
        $sheet->mergeCells('S4:V4');   // Inventory & Settings
        $sheet->mergeCells('W4:AA4');  // Status & Admin

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
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
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 12,  // Customer
            'C' => 15,  // Model
            'D' => 25,  // Part No
            'E' => 35,  // Part Name
            'F' => 10,  // Revision
            'G' => 20,  // Material Spec
            'H' => 15,  // Coating
            'I' => 10,  // Thickness
            'J' => 10,  // Width
            'K' => 10,  // Length
            'L' => 10,  // Length 2
            'M' => 10,  // Pitch
            'N' => 10,  // Density
            'O' => 12,  // Weight
            'P' => 12,  // Net Weight
            'Q' => 12,  // Unit
            'R' => 8,   // Rank
            'S' => 10,  // Pcs/Unit
            'T' => 10,  // Unit/Car
            'U' => 12,  // Min Stock
            'V' => 15,  // Price
            'W' => 15,  // Project Status
            'X' => 20,  // Status Override
            'Y' => 15,  // Status Remark
            'Z' => 30,  // Remark
            'AA' => 18, // Updated At
        ];
    }
}
