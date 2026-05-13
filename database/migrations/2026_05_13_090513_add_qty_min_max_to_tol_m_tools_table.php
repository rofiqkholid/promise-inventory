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
            $table->integer('qty_min')->default(0)->after('price_per_unit');
            $table->integer('qty_max')->default(0)->after('qty_min');
        });

        // Migrate existing limit_stock to qty_min
        DB::table('tol_m_tools')->update(['qty_min' => DB::raw('limit_stock')]);
    }

    public function down(): void
    {
        Schema::table('tol_m_tools', function (Blueprint $table) {
            $table->dropColumn(['qty_min', 'qty_max']);
        });
    }
};
