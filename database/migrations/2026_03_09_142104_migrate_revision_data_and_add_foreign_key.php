<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing data from 'revision' string to 'revision_id' master ID
        $details = DB::table('inv_t_product_detail')->whereNotNull('revision')->get();
        foreach ($details as $detail) {
            $revisionMaster = DB::table('inv_m_revision')->where('code', $detail->revision)->first();
            if ($revisionMaster) {
                DB::table('inv_t_product_detail')
                    ->where('id', $detail->id)
                    ->update(['revision_id' => $revisionMaster->id]);
            }
        }

        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->foreign('revision_id')->references('id')->on('inv_m_revision')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->dropForeign(['revision_id']);
        });
    }
};
