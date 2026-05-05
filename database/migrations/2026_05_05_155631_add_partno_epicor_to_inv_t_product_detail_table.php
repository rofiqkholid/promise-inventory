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
            $table->string('partno_epicor', 20)->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->dropColumn('partno_epicor');
        });
    }
};
