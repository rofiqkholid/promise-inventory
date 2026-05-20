<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionFieldsToTolTFastStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tol_t_fast_stock', function (Blueprint $table) {
            $table->string('action_status', 50)->nullable()->after('current_qty');
            $table->string('action_remark', 255)->nullable()->after('action_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tol_t_fast_stock', function (Blueprint $table) {
            $table->dropColumn('action_status');
            $table->dropColumn('action_remark');
        });
    }
}
