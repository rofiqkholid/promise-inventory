<?php

namespace App\Observers;

use App\Models\InventoryModel\Tool\TolFastStock;
use App\Models\InventoryModel\Tool\TolTool;

class TolFastStockObserver
{
    /**
     * Handle the TolFastStock "saved" event.
     */
    public function saved(TolFastStock $stock): void
    {
        $this->syncTotalQty($stock->tool_id);
    }

    /**
     * Handle the TolFastStock "deleted" event.
     */
    public function deleted(TolFastStock $stock): void
    {
        $this->syncTotalQty($stock->tool_id);
    }

    /**
     * Synchronize the cached total stock and auto-cleanup warning action plan if stock levels are healthy.
     */
    private function syncTotalQty(?int $toolId): void
    {
        if (!$toolId) return;

        // Sum current quantity across all locations
        $totalQty = TolFastStock::where('tool_id', $toolId)->sum('current_qty') ?? 0;

        $tool = TolTool::find($toolId);
        if ($tool) {
            $updateData = ['total_qty' => $totalQty];

            // Auto-cleanup Action Plan if total_qty goes above warning threshold
            $qtyMin = $tool->qty_min ?? 0;
            $limitStock = ($qtyMin > 0 ? $qtyMin * 1.5 : 5);

            if ($totalQty > $limitStock) {
                $updateData['action_status'] = null;
                $updateData['action_remark'] = null;
            }

            // Using update() to bypass other observers and directly update the database record
            $tool->update($updateData);
        }
    }
}
