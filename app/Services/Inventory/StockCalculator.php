<?php

namespace App\Services\Inventory;

class StockCalculator
{
    /**
     * Centralized logic to calculate Pcs from Qty (Weight/Unit).
     * 
     * @param float $qty
     * @param float $weightKg
     * @param int $pcsPerUnit
     * @param string $unitName
     * @param float $topMm
     * @param float $endMm
     * @param float $pitch
     * @param int $pcsPerPitch
     * @param float $grossCoil
     * @return int
     */
    public static function calculatePcs($qty, $weightKg, $pcsPerUnit, $unitName, $topMm = 0, $endMm = 0, $pitch = 0, $pcsPerPitch = 1, $grossCoil = 0)
    {
        $qty = (float)$qty;
        $grossCoil = (float)$grossCoil;
        $pitch = (float)$pitch;
        $weightKg = (float)$weightKg;
        $unitName = strtolower($unitName ?? '');

        // Standard logic for non-coil or missing critical data
        if (!str_contains($unitName, 'coil') || $grossCoil <= 0 || $weightKg <= 0) {
            return (int) floor($qty * (float)($pcsPerUnit ?: 1));
        }

        // Fallback to ratio-based calculation if detailed mm dimensions (pitch) are missing
        if ($pitch <= 0) {
            return (int) floor(($qty / $grossCoil) * (float)($pcsPerUnit ?: 1));
        }

        // Accurate Coil Logic (Yield based)
        // 1. Calculate weight of 1mm
        $weightPerMm = $weightKg / $pitch;
        
        // 2. Calculate scrap weight
        $scrapKg = ((float)$topMm + (float)$endMm) * $weightPerMm;
        
        // 3. Yield Ratio based on Master Data
        $yieldRatio = max(0, ($grossCoil - $scrapKg) / $grossCoil);
        
        // 4. Net Qty
        $netQty = $qty * $yieldRatio;
        
        // 6. Final PCS = floor(Net Qty / Weight per Pitch) * Pcs per Pitch
        return (int) (floor($netQty / $weightKg) * (float)($pcsPerPitch ?: 1));
    }
}
