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
        $headers = [
            'No',
            'Customer',
            'Part No',
            'Part Name',
            'Model',
            'Project Status',
            'Status Remark',
            'Thickness',
            'Width',
            'Length',
            'Length 2',
            'Pitch',
            'Weight (kg)',
            'Spec Name',
            'Coating Type',
            'Rank',
            'Remark',
            'Balance (PCS)',
            'Balance (Unit)',
            'Unit Code',
        ];

        foreach ($this->categories as $cat) {
            $headers[] = $cat->code;
        }

        $headers[] = 'STO GAP';

        return [
            ['Stock Monitoring Report'],
            ['Generated at: ' . date('d M Y H:i')],
            [],
            $headers
        ];
    }

    public function map($row): array
    {
        static $no = 1;
        $mapped = [
            $no++,
            $row->customer_code ?? '-',
            $row->part_no . ($row->revision ? ' - ' . $row->revision : ''),
            $row->part_name ?? '-',
            $row->model_name ?? '-',
            $row->product_status ?: ($row->model_project_status ?? 'Project'),
            $row->product_status_remark ?? '-',
            floatval($row->thickness),
            floatval($row->width),
            floatval($row->length),
            floatval($row->length_2),
            floatval($row->pitch),
            floatval($row->weight_kg),
            $row->spec_name ?? '-',
            $row->coating_type ?? '-',
            $row->rank_code ?? '-',
            $row->remark ?? '-',
            number_format(floatval($row->current_stock_qty) * ($row->pcs_per_unit ?? 1), 0, '.', ''),
            number_format(floatval($row->current_stock_qty), 0, '.', ''),
            $row->unit_code ?? '-',
        ];

        foreach ($this->categories as $cat) {
            $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
            $mapped[] = $row->$alias ? floatval($row->$alias) * ($row->pcs_per_unit ?? 1) : 0;
        }

        $mapped[] = $row->sto_gap ? floatval($row->sto_gap) * ($row->pcs_per_unit ?? 1) : 0;

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->getColumnLetter(19 + count($this->categories) + 1);
        
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EEEEEE']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 12,  // Customer
            'C' => 25,  // Part No
            'D' => 40,  // Part Name
            'E' => 15,  // Model
            'F' => 15,  // Project Status
            'G' => 10,  // T
            'H' => 10,  // W
            'I' => 10,  // L
            'J' => 10,  // L2
            'K' => 10,  // P
            'L' => 12,  // Weight
            'M' => 20,  // Spec
            'N' => 15,  // Coating
            'O' => 8,   // Rank
            'P' => 20,  // Remark
            'Q' => 15,  // Balance PCS
            'R' => 15,  // Balance Unit
            'S' => 10,  // Unit Code
        ];

        return $widths;
    }

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
