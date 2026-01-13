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
            $table->decimal('pitch', 10, 2)->nullable()->after('length_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->dropColumn('pitch');
        });
    }
};
