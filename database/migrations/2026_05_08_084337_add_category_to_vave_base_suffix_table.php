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
        Schema::table('inv_m_vave_base_suffix', function (Blueprint $table) {
            $table->string('category', 10)->after('id')->nullable()->comment('EBD or SQ');
            $table->dropForeign('inv_m_ebd_status_customer_id_foreign');
            $table->dropColumn('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_vave_base_suffix', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->after('id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->dropColumn('category');
        });
    }
};
