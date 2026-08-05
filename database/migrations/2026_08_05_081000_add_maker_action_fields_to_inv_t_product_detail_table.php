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
            if (!Schema::hasColumn('inv_t_product_detail', 'maker_action_status')) {
                $table->string('maker_action_status', 50)->nullable()->after('action_remark');
            }
            if (!Schema::hasColumn('inv_t_product_detail', 'maker_action_remark')) {
                $table->string('maker_action_remark', 255)->nullable()->after('maker_action_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->dropColumn(['maker_action_status', 'maker_action_remark']);
        });
    }
};
