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
        Schema::table('inv_m_vave_base', function (Blueprint $table) {
            $table->integer('pcs_per_pitch')->default(1)->after('pcs_per_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_vave_base', function (Blueprint $table) {
            $table->dropColumn('pcs_per_pitch');
        });
    }
};
