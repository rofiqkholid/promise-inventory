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
        Schema::table('tol_t_sto_slow', function (Blueprint $table) {
            $table->decimal('physical_rate', 5, 2)->after('physical_check')->default(100);
            $table->dropColumn(['qty_checked', 'qty_ok', 'qty_nok']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_t_sto_slow', function (Blueprint $table) {
            $table->dropColumn('physical_rate');
            $table->integer('qty_checked')->default(1);
            $table->integer('qty_ok')->default(1);
            $table->integer('qty_nok')->default(0);
        });
    }
};
