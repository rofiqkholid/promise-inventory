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
        Schema::table('inv_m_product_rfq', function (Blueprint $table) {
            $table->integer('unit_id')->nullable();
            
            // Foreign Key
            // $table->foreign('unit_id')->references('id')->on('inv_m_unit')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_product_rfq', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'pitch']);
        });
    }
};
