<?php

namespace App\Services\Inventory;

class StockCalculator
{
    /**
     * Centralized logic to calculate Pcs from Qty (Weight/Unit).
     */
    public static function calculatePcs($qty, $weightKg, $pcsPerUnit, $unitName, $topMm = 0, $endMm = 0, $pitch = 0, $pcsPerPitch = 1, $grossCoil = 0)
    {
        $qty = (float)$qty;
        $grossCoil = (float)$grossCoil;
        $pitch = (float)$pitch;
        $weightKg = (float)$weightKg;
        $unitName = strtolower($unitName ?? '');

        // Safety check for Coils: If critical data (grossCoil or weightKg) is missing, 
        // return 0 instead of falling back to KG-to-PCS 1:1 ratio which causes massive inaccuracy.
        if (strpos($unitName, 'coil') !== false) {
            if ($grossCoil <= 0 || $weightKg <= 0) {
                return 0;
            }
        }

        // Standard logic for non-coil
        if (strpos($unitName, 'coil') === false) {
            return (int) floor($qty * (float)($pcsPerUnit ?: 1));
        }

        // Fallback to ratio-based calculation if detailed mm dimensions (pitch) are missing
        if ($pitch <= 0) {
            return (int) floor(($qty / $grossCoil) * (float)($pcsPerUnit ?: 1));
        }

        // Accurate Coil Logic (Yield based)
        $weightPerMm = $weightKg / $pitch;
        $scrapKg = ((float)$topMm + (float)$endMm) * $weightPerMm;
        $yieldRatio = max(0, ($grossCoil - $scrapKg) / $grossCoil);
        $netQty = $qty * $yieldRatio;
        
        return (int) (floor($netQty / $weightKg) * (float)($pcsPerPitch ?: 1));
    }

    /**
     * Centralized logic to calculate monetary value (Amount) from Qty.
     * Principle: Total Price = Total Weight (KG) * Price per KG.
     */
    public static function calculateAmount($qty, $materialPrice, $weightKg = 0, $pcsPerUnit = 1, $unitName = '')
    {
        $qty = (float)$qty;
        $materialPrice = (float)$materialPrice;
        $weightKg = (float)$weightKg;
        $pcsPerUnit = (int)($pcsPerUnit ?: 1);
        $unitName = strtolower($unitName ?? '');

        // For Coils, the $qty input is already in KG (Total Weight)
        if (strpos($unitName, 'coil') !== false) {
            return $qty * $materialPrice;
        }

        // For Others, $qty is the "Unit Count" (e.g. 10 Packs). 
        $totalPcs = $qty * $pcsPerUnit;
        $totalWeight = $totalPcs * $weightKg;

        return $totalWeight * $materialPrice;
    }
}
