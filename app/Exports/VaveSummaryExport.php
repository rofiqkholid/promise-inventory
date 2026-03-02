<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class VaveSummaryExport implements FromView, WithTitle, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.vave_summary', [
            'products' => $this->data
        ]);
    }


    public function title(): string
    {
        return 'VAVE Summary Report';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:V')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('J:S')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:V2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('T:V')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ]
            ],
        ];
    }
}
