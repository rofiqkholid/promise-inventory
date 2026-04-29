<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryModel\Material\InventoryProduct;
use Illuminate\Support\Facades\DB;

class SyncStock extends Command
{
    protected $signature = 'inventory:sync-stock';
    protected $description = 'Synchronize current_stock_qty with Transaction History and STO Gap';

    public function handle()
    {
        $products = InventoryProduct::where('is_active', 1)->get();
        $count = 0;

        $this->info("Starting synchronization for " . $products->count() . " products...");

        foreach ($products as $p) {
            // 1. Find the latest ADJUSTED STO for this product
            $latestSto = DB::table('inv_t_sto_detail')
                ->where('product_detail_id', $p->id)
                ->where('is_adjusted', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            $baseQty = 0;
            $txQuery = DB::table('inv_t_inventory_transaction as t')
                ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                ->where('t.product_detail_id', $p->id);

            if ($latestSto) {
                // If STO exists, start from the physical count and only add transactions AFTER that STO
                $baseQty = (float)$latestSto->real_qty_input;
                $txQuery->where('t.created_at', '>', $latestSto->created_at);
            }

            $txBalance = $txQuery->select(DB::raw('SUM(t.qty * tc.effect) as balance'))->value('balance') ?? 0;

            // 2. Total Current Stock = Physical STO + Post-STO Transactions
            $newQty = $baseQty + (float)$txBalance;

            // 3. Recalculate Trial Usage (Always cumulative from history as it's a budget tracker)
            $trialQty = DB::table('inv_t_inventory_transaction as t')
                ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                ->where('t.product_detail_id', $p->id)
                ->where('tc.code', 'OUT-TRIAL')
                ->sum('t.qty') ?? 0;

            // 4. Calculate PCS
            $unitName = DB::table('inv_m_unit')->where('id', $p->unit_id)->value('name');
            $newPcs = InventoryProduct::calculatePcs($newQty, $p->weight_kg, $p->pcs_per_unit, $unitName, $p->top_coil, $p->end_coil, $p->pitch, $p->pcs_per_pitch, $p->gross_coil);
            $trialPcs = InventoryProduct::calculatePcs($trialQty, $p->weight_kg, $p->pcs_per_unit, $unitName, $p->top_coil, $p->end_coil, $p->pitch, $p->pcs_per_pitch, $p->gross_coil);

            $updated = false;
            // Check if current stock needs update
            if (abs((float)$p->current_stock_qty - (float)$newQty) > 0.0001) {
                $p->current_stock_qty = $newQty;
                $updated = true;
            }

            if ($p->current_stock_pcs != $newPcs) {
                $p->current_stock_pcs = $newPcs;
                $updated = true;
            }

            // Check if trial usage needs update
            if (abs((float)$p->trial_usage_qty - (float)$trialQty) > 0.0001) {
                $p->trial_usage_qty = $trialQty;
                $updated = true;
            }

            if ($p->trial_usage_pcs != $trialPcs) {
                $p->trial_usage_pcs = $trialPcs;
                $updated = true;
            }

            if ($updated) {
                $p->save();
                $this->line("Product ID {$p->id}: Stock {$newQty} ({$newPcs} pcs), Trial {$trialQty} ({$trialPcs} pcs)");
                $count++;
            }
        }

        $this->info("Synchronization complete. Adjusted {$count} products.");
    }
}
