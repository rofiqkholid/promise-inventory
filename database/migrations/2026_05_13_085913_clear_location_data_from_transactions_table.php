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
        // 1. Make location_id nullable in relevant tables
        Schema::table('tol_t_fast_stock', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->change();
        });
        Schema::table('tol_t_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->change();
        });
        Schema::table('tol_t_slow_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->change();
        });
        Schema::table('tol_t_sto_fast', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->change();
        });

        // 2. Clear location data in transactions & batches (Simple)
        DB::table('tol_t_transactions')->update(['location_id' => null]);
        DB::table('tol_t_slow_batches')->update(['location_id' => null]);
        DB::table('tol_t_sto_fast')->update(['location_id' => null]);

        // 3. Merge Fast Stock records (One tool = One stock record now)
        $mergedStock = DB::table('tol_t_fast_stock')
            ->select('tool_id', DB::raw('SUM(current_qty) as total_qty'), DB::raw('MAX(last_updated_at) as last_updated'))
            ->groupBy('tool_id')
            ->get();

        // Clear existing fast stock records
        // We use delete() instead of truncate() to avoid FK issues during migration if any
        DB::table('tol_t_fast_stock')->delete();

        // Insert merged records with NULL location
        foreach ($mergedStock as $stock) {
            DB::table('tol_t_fast_stock')->insert([
                'tool_id'         => $stock->tool_id,
                'location_id'     => null,
                'current_qty'     => $stock->total_qty,
                'last_updated_at' => $stock->last_updated,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Reverting this fully is not possible without original location data
        Schema::table('tol_t_fast_stock', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable(false)->change();
        });
    }
};
