<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add effective_from and effective_to year columns to inv_m_vave_base.
     *
     * effective_from : The first year this EBD baseline is used for analysis.
     * effective_to   : The last year this EBD baseline applies (NULL = still ongoing / no end date).
     *
     * Dashboard query: WHERE effective_from <= :year AND (effective_to IS NULL OR effective_to >= :year)
     */
    public function up(): void
    {
        Schema::table('inv_m_vave_base', function (Blueprint $table) {
            $table->smallInteger('effective_from')->nullable()->after('is_active')
                  ->comment('First year this EBD baseline is used for analysis');
            $table->smallInteger('effective_to')->nullable()->after('effective_from')
                  ->comment('Last year this EBD applies (NULL = still ongoing)');
        });

        // Migrate existing data: set effective_from = current year for all currently active records
        DB::table('inv_m_vave_base')
            ->where('is_active', 1)
            ->whereNull('effective_from')
            ->update(['effective_from' => (int) date('Y')]);
    }

    public function down(): void
    {
        Schema::table('inv_m_vave_base', function (Blueprint $table) {
            $table->dropColumn(['effective_from', 'effective_to']);
        });
    }
};
