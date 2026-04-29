<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->integer('current_stock_pcs')->default(0)->after('current_stock_qty');
            $table->integer('trial_usage_pcs')->default(0)->after('trial_usage_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->dropColumn(['current_stock_pcs', 'trial_usage_pcs']);
        });
    }
};
