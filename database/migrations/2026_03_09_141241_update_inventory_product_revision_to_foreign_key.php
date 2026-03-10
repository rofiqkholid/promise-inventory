<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('inv_t_product_detail', 'revision_id')) {
            Schema::table('inv_t_product_detail', function (Blueprint $table) {
                $table->integer('revision_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inv_t_product_detail', 'revision_id')) {
            Schema::table('inv_t_product_detail', function (Blueprint $table) {
                $table->dropColumn('revision_id');
            });
        }
    }
};
