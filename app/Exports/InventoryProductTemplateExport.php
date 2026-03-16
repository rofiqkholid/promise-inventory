<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryProductTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Part No *', 
            'Customer *', 
            'Model *', 
            'Revision *', 
            'Material Spec', 
            'Unit', 
            'Rank', 
            'Thickness', 
            'Width', 
            'Length', 
            'Length 2', 
            'Pitch', 
            'Density', 
            'Net Weight (Kg)',
            'Material Price', 
            'Pcs per Unit', 
            'Unit per Car', 
            'Remark'
        ];
    }

    public function array(): array
    {
        return [
            // Sample data for guide
            [
                'PART-12345',
                'CUSTA',
                'MODEL-X',
                'R',
                'SPCC',
                'Sheet',
                'A',
                '1.2',
                '1000',
                '2000',
                '0',
                '0',
                '7.85',
                '48.500',
                '20000',
                '1',
                '1',
                'Sample data description'
            ],
            [
                'PART-67890',
                'CUSTB',
                'MODEL-Y',
                'R1',
                'SPHC',
                'Coil',
                'B',
                '2.0',
                '1219',
                '0',
                '0',
                '1500',
                '7.85',
                '95.000',
                '21000',
                '2',
                '1',
                ''
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0070C0']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ],
            'A:Q' => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                foreach (range('A', 'Q') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
