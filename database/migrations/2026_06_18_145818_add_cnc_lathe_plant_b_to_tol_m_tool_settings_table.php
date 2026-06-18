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
        Schema::table('tol_m_tool_settings', function (Blueprint $table) {
            $table->boolean('cnc_lathe_plant_b')->default(false)->after('cnc_small_plant_b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_m_tool_settings', function (Blueprint $table) {
            $table->dropColumn('cnc_lathe_plant_b');
        });
    }
};
