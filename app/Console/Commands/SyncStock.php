<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryModel\InventoryProduct;
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
            $in = DB::table('inv_t_inventory_transaction as t')
                ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                ->where('t.product_detail_id', $p->id)
                ->where('tc.effect', 1)
                ->sum('t.qty');

            $out = DB::table('inv_t_inventory_transaction as t')
                ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                ->where('t.product_detail_id', $p->id)
                ->where('tc.effect', -1)
                ->sum('t.qty');

            $sto = DB::table('inv_t_sto_detail as sd')
                ->join('inv_t_sto_event as e', 'e.id', '=', 'sd.event_id')
                ->where('sd.product_detail_id', $p->id)
                ->orderBy('e.created_at', 'desc')
                ->value('sd.diff_qty') ?? 0;

            // Recalculate Trial Usage (is_trial = 1)
            $trialQty = DB::table('inv_t_inventory_transaction as t')
                ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                ->where('t.product_detail_id', $p->id)
                ->where('tc.is_trial', 1)
                ->sum('t.qty');

            $newQty = $in - $out + $sto;
            $needsSave = false;
            
            if (abs($p->current_stock_qty - $newQty) > 0.001) {
                $p->current_stock_qty = $newQty;
                $needsSave = true;
            }

            if (abs($p->trial_usage_qty - $trialQty) > 0.001) {
                $p->trial_usage_qty = $trialQty;
                $needsSave = true;
            }

            if ($needsSave) {
                $p->save();
                $this->line("Product ID {$p->id}: Stock {$newQty}, Trial {$trialQty}");
                $count++;
            }
        }

        $this->info("Synchronization complete. Adjusted {$count} products.");
    }
}
