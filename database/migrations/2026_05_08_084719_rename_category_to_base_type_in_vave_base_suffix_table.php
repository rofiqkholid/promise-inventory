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
        Schema::table('inv_m_vave_base_suffix', function (Blueprint $table) {
            $table->renameColumn('category', 'base_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_vave_base_suffix', function (Blueprint $table) {
            $table->renameColumn('base_type', 'category');
        });
    }
};
