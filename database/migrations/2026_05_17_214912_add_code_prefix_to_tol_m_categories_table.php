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
        Schema::table('tol_m_categories', function (Blueprint $table) {
            $table->string('code_prefix', 10)->nullable()->after('moving_type')
                  ->comment('Prefix for auto generating ID numbers (e.g. ARB)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_m_categories', function (Blueprint $table) {
            $table->dropColumn('code_prefix');
        });
    }
};
