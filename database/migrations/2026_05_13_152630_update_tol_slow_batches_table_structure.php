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
        Schema::table('tol_t_slow_batches', function (Blueprint $table) {
            $table->renameColumn('batch_no', 'id_number');
            $table->decimal('physical_rate', 5, 2)->default(100.00)->after('purchase_price')->comment('Current physical condition rate in %');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_t_slow_batches', function (Blueprint $table) {
            $table->renameColumn('id_number', 'batch_no');
            $table->dropColumn('physical_rate');
        });
    }
};
