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

class TransactionHistoryExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $transactions;
    private $rowNum = 0;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            ['No', 'Trans. Date', 'Timestamp', 'Model', 'Part No', 'Part Name', 'Category', 'Origin / Destination', 'Qty', 'PIC', 'Remarks']
        ];
    }

    public function map($item): array
    {
        $this->rowNum++;

        $partNo = ($item->product->product->part_no ?? '-') .
                  ($item->product->revision ? '-' . $item->product->revision->code : '');

        $originDest = collect([
            $item->coilCenter?->code,
            $item->supplier?->code,
            $item->destination?->code ? '(To: ' . $item->destination->code . ')' : null
        ])->filter()->implode(' ');

        return [
            $this->rowNum,
            optional($item->transaction_date)->format('Y-m-d') ?? '-',
            $item->updated_at ? $item->updated_at->format('d M Y H:i:s') : '-',
            $item->product->model->name ?? 'No Model',
            $partNo,
            $item->product->product->part_name ?? '-',
            $item->transactionCategory->code ?? '-',
            $originDest ?: '-',
            $item->qty ?? 0,
            $item->user->name ?? '-',
            $item->remark ?? '-'
        ];
    }

    public function title(): string
    {
        return 'Transaction History';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 14,  // Trans Date
            'C' => 20,  // Timestamp
            'D' => 16,  // Model
            'E' => 22,  // Part No
            'F' => 30,  // Part Name
            'G' => 15,  // Category
            'H' => 22,  // Origin / Destination
            'I' => 10,  // Qty
            'J' => 18,  // PIC
            'K' => 30,  // Remarks
        ];
    }
}
