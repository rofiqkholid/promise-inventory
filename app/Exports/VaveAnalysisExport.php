<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VaveAnalysisExport implements FromView, WithTitle, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $this->prepareData($data);
    }

    protected function prepareData($data)
    {
        $rfqs = $data['rfqs'];
        $revisions = $data['revisions'];
        $product = $data['product'];
        $selectedBaseId = $data['selected_base_id'] ?? null;
        $selectedActualId = $data['selected_actual_id'] ?? null;

        // Find active RFQ as base, fallback to first, or use selected
        if ($selectedBaseId) {
            $baseRfq = $rfqs->firstWhere('hash_id', $selectedBaseId) ?? $rfqs->first();
        } else {
            $baseRfq = $rfqs->where('is_active', 1)->first() ?? $rfqs->first();
        }
        
        // The user requested to only show the chosen baseline and omit baseline history in exports.
        $rfqHistory = collect();

        if ($selectedActualId) {
            // Revisions uses 'inv_t_product_detail' joined with 'inv_m_revision', 'revision' relation has the code
            $latestRevIndex = $revisions->search(function($rev) use ($selectedActualId) {
                return $rev->revision && $rev->revision->code == $selectedActualId;
            });

            if ($latestRevIndex !== false) {
                $latestRev = $revisions->pull($latestRevIndex);
                $revisions->prepend($latestRev);
                $revisions = $revisions->values();
            } else {
                $latestRev = $revisions->first();
            }
        } else {
            $latestRev = $revisions->first();
        }
        
        $baseW = (float)($baseRfq->weight_kg ?? 0);
        $actW = (float)($latestRev->weight_kg ?? 0);
        $deltaW = $actW - $baseW;
        $isSaving = $deltaW <= 0;

        $deltaT = (float)($latestRev->thickness ?? 0) - (float)($baseRfq->thickness ?? 0);
        $deltaWi = (float)($latestRev->width ?? 0) - (float)($baseRfq->width ?? 0);
        $deltaL = (float)($latestRev->length ?? 0) - (float)($baseRfq->length ?? 0);
        $deltaL2 = (float)($latestRev->length_2 ?? 0) - (float)($baseRfq->length_2 ?? 0);
        $deltaP = (float)($latestRev->pitch ?? 0) - (float)($baseRfq->pitch ?? 0);
        $deltaD = (float)($latestRev->density ?? 0) - (float)($baseRfq->density ?? 0);
        $deltaNW = (float)($latestRev->net_weight ?? 0) - (float)($baseRfq->net_weight ?? 0);
        
        $baseScrap = $baseW - (float)($baseRfq->net_weight ?? 0);
        $actScrap = $actW - (float)($latestRev->net_weight ?? 0);
        $deltaScrap = $actScrap - $baseScrap;

        $impactPct = abs($baseW - $actW) / ($baseW ?: 1) * 100;
        $statusText = $isSaving ? "Saving Achievement: " . number_format($impactPct, 2) . "% Reduction" : "Loss Detected: " . number_format($impactPct, 2) . "% Increase";
        $statusBg = $isSaving ? '#dcfce7' : '#fee2e2';
        $statusColor = $isSaving ? '#166534' : '#991b1b';

        // Budomari helper
        $calcBud = function($i) {
            $g = (float)($i->weight_kg ?? 0);
            $n = (float)($i->net_weight ?? 0);
            return ($g > 0 && $n > 0) ? ($n / $g) * 100 : 0;
        };

        $baseBud = $calcBud($baseRfq);
        $actBud = $calcBud($latestRev);
        $deltaBud = $actBud - $baseBud;

        $historyRfqCount = $rfqHistory->count();
        $historyRevCount = max(0, $revisions->count() - 1);
        $totalCols = 4 + $historyRfqCount + $historyRevCount;

        // Helper to format dimension string
        $fmtDim = function($i) {
            if (!$i) return '-';
            $t = (float)($i->thickness ?? 0);
            $w = (float)($i->width ?? 0);
            $l = (float)($i->length ?? 0);
            $l2 = (float)($i->length_2 ?? 0);
            $p = (float)($i->pitch ?? 0);
            
            $dim = "$t x $w";
            if ($p > 0) {
                $dim .= " x $p (P)";
            } elseif ($l2 > 0) {
                $dim .= " x ($l + $l2)/2";
            } else {
                $dim .= " x $l";
            }
            return $dim;
        };

        // Attach formatted dimensions
        $baseRfq->fmt_dimension = $fmtDim($baseRfq);
        foreach($rfqHistory as $r) {
            $r->fmt_dimension = $fmtDim($r);
        }
        foreach($revisions as $rev) {
            $rev->fmt_dimension = $fmtDim($rev);
        }

        return [
            'product' => $product,
            'baseRfq' => $baseRfq,
            'rfqHistory' => $rfqHistory,
            'revisions' => $revisions,
            'latestRev' => $latestRev,
            'baseW' => $baseW,
            'actW' => $actW,
            'deltaW' => $deltaW,
            'deltaT' => $deltaT,
            'deltaWi' => $deltaWi,
            'deltaL' => $deltaL,
            'deltaL2' => $deltaL2,
            'deltaP' => $deltaP,
            'deltaD' => $deltaD,
            'deltaNW' => $deltaNW,
            'baseScrap' => $baseScrap,
            'actScrap' => $actScrap,
            'deltaScrap' => $deltaScrap,
            'baseBud' => $baseBud,
            'actBud' => $actBud,
            'deltaBud' => $deltaBud,
            'isSaving' => $isSaving,
            'totalCols' => $totalCols,
            'impactPct' => $impactPct,
            'statusText' => $statusText,
            'statusBg' => $statusBg,
            'statusColor' => $statusColor,
        ];
    }

    public function view(): View
    {
        return view('exports.vave_analysis', $this->data);
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 45, // Parameter column (increased)
            'B' => 22, // Plan column
            'C' => 22, // Actual column
            'D' => 22, // Variance column
        ];

        // Dynamically set widths for all history columns based on totalCols
        $totalCols = $this->data['totalCols'];
        for ($i = 5; $i <= $totalCols; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $widths[$colLetter] = 20; // Consistent width for history columns
        }

        return $widths;
    }

    public function title(): string
    {
        return 'VAVE Analysis - ' . $this->data['product']->part_no;
    }

    public function styles(Worksheet $sheet)
    {
        $totalCols = $this->data['totalCols'];
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Center all data from Column B onwards
        $sheet->getStyle('B4:' . $lastCol . '50')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:' . $lastCol . '50')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return [
            // Title & Subtitle centered across the table width
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            // Primary Header row
            4 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF3F4F6']
                ]
            ],
            // Secondary Header row (Sub-labels)
            5 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF9FAFB']
                ]
            ],
        ];
    }
}
