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
            $table->integer('ebd_status_id')->nullable()->after('rfq_name');
            $table->foreign('ebd_status_id')->references('id')->on('inv_m_ebd_status')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_product_rfq', function (Blueprint $table) {
            $table->dropForeign(['ebd_status_id']);
            $table->dropColumn('ebd_status_id');
        });
    }
};
