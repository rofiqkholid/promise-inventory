<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->string('action_status', 50)->nullable()->after('product_status_remark');
        });

        // Trigger to auto-reset action_status when stock is safe (current_stock_pcs >= min_stock)
        DB::unprepared("
            CREATE TRIGGER tr_ProductDetail_ActionStatus_Reset
            ON inv_t_product_detail
            AFTER UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;
                
                -- Check if current_stock_pcs or min_stock was updated
                IF UPDATE(current_stock_pcs) OR UPDATE(min_stock)
                BEGIN
                    UPDATE pd
                    SET pd.action_status = NULL
                    FROM inv_t_product_detail pd
                    JOIN inserted i ON pd.id = i.id
                    WHERE i.current_stock_pcs >= i.min_stock
                      AND i.min_stock > 0
                      AND pd.action_status IS NOT NULL;
                END
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS tr_ProductDetail_ActionStatus_Reset");

        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->dropColumn('action_status');
        });
    }
};
