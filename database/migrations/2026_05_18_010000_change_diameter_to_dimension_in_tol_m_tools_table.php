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
        Schema::table('tol_m_tools', function (Blueprint $table) {
            // Convert diameter column to string first to support characters
            $table->string('diameter', 100)->nullable()->change();
            
            // Rename diameter column to dimension
            $table->renameColumn('diameter', 'dimension');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_m_tools', function (Blueprint $table) {
            // Rename dimension back to diameter
            $table->renameColumn('dimension', 'diameter');
            
            // Change type back to decimal
            $table->decimal('diameter', 8, 3)->nullable()->change();
        });
    }
};
