<?php

namespace App\Exports;

use App\Models\InventoryModel\StoEvent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StoExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $stoEvent;
    protected $rowNumber = 1;

    public function __construct(StoEvent $stoEvent)
    {
        $this->stoEvent = $stoEvent;
    }

    public function collection()
    {
        return $this->stoEvent->details;
    }

    public function headings(): array
    {
        return [
            ['STO Event Report'],
            ['Code', $this->stoEvent->code],
            ['Name', $this->stoEvent->name],
            ['Period', $this->stoEvent->period_start->format('d M Y') . ' - ' . ($this->stoEvent->period_end ? $this->stoEvent->period_end->format('d M Y') : 'Ongoing')],
            ['Status', $this->stoEvent->status],
            ['PIC', $this->stoEvent->pic->name ?? '-'],
            [],
            ['Part No', 'Part Name', 'Revision', 'System Qty', 'Real Qty', 'Diff', 'Remark', 'Auditor', 'Time'],
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->product->product->part_no ?? '-',
            $detail->product->product->part_name ?? '-',
            $detail->product->revision ?? '-',
            $detail->system_qty_snapshot,
            $detail->real_qty_input,
            $detail->diff_qty,
            $detail->remark ?? '',
            $detail->auditor->name ?? '-',
            $detail->updated_at->format('d M Y H:i'),
        ];
    }

    public function title(): string
    {
        return 'STO ' . $this->stoEvent->code;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            8 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 10,
            'G' => 25,
            'H' => 15,
            'I' => 18,
        ];
    }
}
