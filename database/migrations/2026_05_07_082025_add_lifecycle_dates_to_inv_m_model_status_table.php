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
        Schema::table('inv_m_model_status', function (Blueprint $table) {
            if (!Schema::hasColumn('inv_m_model_status', 'regular_start_date')) {
                $table->date('regular_start_date')->nullable()->comment('Date when model becomes Regular');
            }
            if (!Schema::hasColumn('inv_m_model_status', 'regular_expired_date')) {
                $table->date('regular_expired_date')->nullable()->comment('Date when Regular model expires');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_model_status', function (Blueprint $table) {
            if (Schema::hasColumn('inv_m_model_status', 'regular_start_date')) {
                $table->dropColumn('regular_start_date');
            }
            if (Schema::hasColumn('inv_m_model_status', 'regular_expired_date')) {
                $table->dropColumn('regular_expired_date');
            }
        });
    }
};
