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
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->decimal('gross_coil', 10, 3)->nullable()->default(0)->after('density');
            $table->decimal('top_coil', 10, 3)->nullable()->default(0)->after('gross_coil');
            $table->decimal('end_coil', 10, 3)->nullable()->default(0)->after('top_coil');
            $table->decimal('net_coil', 10, 3)->nullable()->default(0)->after('end_coil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->dropColumn(['gross_coil', 'top_coil', 'end_coil', 'net_coil']);
        });
    }
};
