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
        Schema::table('inv_m_rank', function (Blueprint $table) {
            $table->string('process_type', 50)->nullable()->after('code');
        });

        // Auto-populate based on existing code patterns (1-4: Draw, 5-8: Blank, 9-12: Full Progressive)
        $ranks = \Illuminate\Support\Facades\DB::table('inv_m_rank')->get();
        foreach ($ranks as $rank) {
            preg_match('/^(\d+)/', $rank->code, $matches);
            if (isset($matches[1])) {
                $base = (int)$matches[1];
                $type = null;
                if ($base >= 1 && $base <= 4) $type = 'Draw';
                elseif ($base >= 5 && $base <= 8) $type = 'Blank';
                elseif ($base >= 9 && $base <= 12) $type = 'Full Progressive';
                
                if ($type) {
                    \Illuminate\Support\Facades\DB::table('inv_m_rank')
                        ->where('id', $rank->id)
                        ->update(['process_type' => $type]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_m_rank', function (Blueprint $table) {
            $table->dropColumn('process_type');
        });
    }
};
