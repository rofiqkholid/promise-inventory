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
        Schema::table('inv_t_sto_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('reason_id')->nullable()->after('remark');
            $table->foreign('reason_id')->references('id')->on('inv_m_sto_reasons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_sto_detail', function (Blueprint $table) {
            $table->dropForeign(['reason_id']);
            $table->dropColumn('reason_id');
        });
    }
};
