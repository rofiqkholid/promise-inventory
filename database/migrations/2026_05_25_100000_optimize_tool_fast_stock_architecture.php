<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 1. Add new columns to tol_m_tools
        Schema::table('tol_m_tools', function (Blueprint $table) {
            $table->string('action_status', 50)->nullable()->after('limit_stock');
            $table->string('action_remark', 255)->nullable()->after('action_status');
            $table->integer('total_qty')->default(0)->after('action_remark');
        });

        // 2. Data Migration: Transfer action plan & compute cached total stock
        $tools = DB::table('tol_m_tools')->get();
        foreach ($tools as $tool) {
            // Compute total stock quantity across all locations
            $totalQty = DB::table('tol_t_fast_stock')
                ->where('tool_id', $tool->id)
                ->sum('current_qty') ?? 0;

            // Find the action status & remark. Prioritize the default location, or get the first non-null record
            $actionRecord = DB::table('tol_t_fast_stock')
                ->where('tool_id', $tool->id)
                ->where(function ($query) {
                    $query->whereNotNull('action_status')
                          ->orWhereNotNull('action_remark');
                })
                ->orderByRaw("CASE WHEN location_id = ? THEN 0 ELSE 1 END", [$tool->location_id])
                ->first();

            $actionStatus = $actionRecord ? $actionRecord->action_status : null;
            $actionRemark = $actionRecord ? $actionRecord->action_remark : null;

            DB::table('tol_m_tools')
                ->where('id', $tool->id)
                ->update([
                    'total_qty'     => $totalQty,
                    'action_status' => $actionStatus,
                    'action_remark' => $actionRemark,
                ]);
        }

        // 3. Drop legacy columns from tol_t_fast_stock
        Schema::table('tol_t_fast_stock', function (Blueprint $table) {
            $table->dropColumn(['action_status', 'action_remark']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // 1. Add legacy columns back to tol_t_fast_stock
        Schema::table('tol_t_fast_stock', function (Blueprint $table) {
            $table->string('action_status', 50)->nullable()->after('current_qty');
            $table->string('action_remark', 255)->nullable()->after('action_status');
        });

        // 2. Rollback data: Move action plans back to the primary location stock record
        $tools = DB::table('tol_m_tools')->get();
        foreach ($tools as $tool) {
            if ($tool->action_status || $tool->action_remark) {
                // Find or create primary stock record
                $primaryStock = DB::table('tol_t_fast_stock')
                    ->where('tool_id', $tool->id)
                    ->where('location_id', $tool->location_id)
                    ->first();

                if (!$primaryStock) {
                    $primaryStock = DB::table('tol_t_fast_stock')
                        ->where('tool_id', $tool->id)
                        ->first();
                }

                if ($primaryStock) {
                    DB::table('tol_t_fast_stock')
                        ->where('id', $primaryStock->id)
                        ->update([
                            'action_status' => $tool->action_status,
                            'action_remark' => $tool->action_remark,
                        ]);
                } else if ($tool->location_id) {
                    // Create primary stock record if location_id exists
                    DB::table('tol_t_fast_stock')->insert([
                        'tool_id'         => $tool->id,
                        'location_id'     => $tool->location_id,
                        'current_qty'     => 0,
                        'action_status'   => $tool->action_status,
                        'action_remark'   => $tool->action_remark,
                        'last_updated_at' => now(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }

        // 3. Drop columns from tol_m_tools
        Schema::table('tol_m_tools', function (Blueprint $table) {
            $table->dropColumn(['action_status', 'action_remark', 'total_qty']);
        });
    }
};
