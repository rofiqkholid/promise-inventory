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
        // 1. Rename the master status/suffix table
        Schema::rename('inv_m_ebd_status', 'inv_m_vave_base_suffix');
        
        // 2. Rename the main base table
        Schema::rename('inv_m_product_rfq', 'inv_m_vave_base');

        // 3. Rename columns in the base table for consistency
        Schema::table('inv_m_vave_base', function (Blueprint $table) {
            $table->renameColumn('rfq_name', 'base_name');
            $table->renameColumn('ebd_status_id', 'vave_base_suffix_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_vave_base', function (Blueprint $table) {
            $table->renameColumn('vave_base_suffix_id', 'ebd_status_id');
            $table->renameColumn('base_name', 'rfq_name');
        });

        Schema::rename('inv_m_vave_base', 'inv_m_product_rfq');
        Schema::rename('inv_m_vave_base_suffix', 'inv_m_ebd_status');
    }
};
