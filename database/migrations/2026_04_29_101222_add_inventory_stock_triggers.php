<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define the PCS calculation formula as a T-SQL snippet
        $pcsFormula = "
            CASE 
                WHEN LOWER(u.name) LIKE '%coil%' AND pd.gross_coil > 0 AND pd.weight_kg > 0 AND pd.pitch > 0
                THEN 
                    FLOOR(
                        (target_qty * ((pd.gross_coil - (ISNULL(pd.top_coil, 0) + ISNULL(pd.end_coil, 0)) * (pd.weight_kg / pd.pitch)) / pd.gross_coil)) 
                        / pd.weight_kg
                    ) * ISNULL(pd.pcs_per_pitch, 1)
                WHEN LOWER(u.name) LIKE '%coil%' AND pd.gross_coil > 0 AND pd.weight_kg > 0 -- Fallback if pitch is 0
                THEN 
                    FLOOR(target_qty / pd.gross_coil) * ISNULL(pd.pcs_per_unit, 1)
                ELSE 
                    FLOOR(target_qty * ISNULL(pd.pcs_per_unit, 1))
            END";

        // 1. Trigger for Inventory Transactions
        DB::unprepared("
            CREATE TRIGGER tr_InventoryTransaction_Stock
            ON inv_t_inventory_transaction
            AFTER INSERT, UPDATE, DELETE
            AS
            BEGIN
                SET NOCOUNT ON;
                
                -- Handle Qty changes
                IF EXISTS (SELECT * FROM deleted)
                BEGIN
                    UPDATE pd
                    SET pd.current_stock_qty = pd.current_stock_qty - (d.qty * tc.effect),
                        pd.trial_usage_qty = pd.trial_usage_qty - CASE WHEN tc.code = 'OUT-TRIAL' THEN d.qty ELSE 0 END
                    FROM inv_t_product_detail pd
                    JOIN deleted d ON pd.id = d.product_detail_id
                    JOIN inv_m_transaction_category tc ON d.transaction_category_id = tc.id;
                END

                IF EXISTS (SELECT * FROM inserted)
                BEGIN
                    UPDATE pd
                    SET pd.current_stock_qty = pd.current_stock_qty + (i.qty * tc.effect),
                        pd.trial_usage_qty = pd.trial_usage_qty + CASE WHEN tc.code = 'OUT-TRIAL' THEN i.qty ELSE 0 END
                    FROM inv_t_product_detail pd
                    JOIN inserted i ON pd.id = i.product_detail_id
                    JOIN inv_m_transaction_category tc ON i.transaction_category_id = tc.id;
                END

                -- Recalculate PCS for affected products
                UPDATE pd
                SET 
                    pd.current_stock_pcs = " . str_replace('target_qty', 'pd.current_stock_qty', $pcsFormula) . ",
                    pd.trial_usage_pcs = " . str_replace('target_qty', 'pd.trial_usage_qty', $pcsFormula) . "
                FROM inv_t_product_detail pd
                JOIN inv_m_unit u ON u.id = pd.unit_id
                WHERE pd.id IN (SELECT product_detail_id FROM inserted UNION SELECT product_detail_id FROM deleted);
            END
        ");

        // 2. Trigger for STO Details
        DB::unprepared("
            CREATE TRIGGER tr_StoDetail_Stock
            ON inv_t_sto_detail
            AFTER INSERT, UPDATE, DELETE
            AS
            BEGIN
                SET NOCOUNT ON;

                IF EXISTS (SELECT * FROM deleted WHERE is_adjusted = 1)
                BEGIN
                    UPDATE pd
                    SET pd.current_stock_qty = pd.current_stock_qty - d.diff_qty
                    FROM inv_t_product_detail pd
                    JOIN deleted d ON pd.id = d.product_detail_id
                    WHERE d.is_adjusted = 1;
                END

                IF EXISTS (SELECT * FROM inserted WHERE is_adjusted = 1)
                BEGIN
                    UPDATE pd
                    SET pd.current_stock_qty = pd.current_stock_qty + i.diff_qty
                    FROM inv_t_product_detail pd
                    JOIN inserted i ON pd.id = i.product_detail_id
                    WHERE i.is_adjusted = 1;
                END

                -- Recalculate PCS
                UPDATE pd
                SET pd.current_stock_pcs = " . str_replace('target_qty', 'pd.current_stock_qty', $pcsFormula) . "
                FROM inv_t_product_detail pd
                JOIN inv_m_unit u ON u.id = pd.unit_id
                WHERE pd.id IN (SELECT product_detail_id FROM inserted UNION SELECT product_detail_id FROM deleted);
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS tr_InventoryTransaction_Stock");
        DB::unprepared("DROP TRIGGER IF EXISTS tr_StoDetail_Stock");
    }
};
