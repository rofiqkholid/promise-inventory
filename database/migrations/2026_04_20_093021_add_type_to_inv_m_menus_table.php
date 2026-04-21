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
        // Add type column to menus table for better UI structure (headers, dividers, etc.)
        if (!Schema::hasColumn('inv_m_menus', 'type')) {
            Schema::table('inv_m_menus', function (Blueprint $table) {
                $table->string('type', 50)->default('menu')->after('icon')->comment('menu, header, divider');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('inv_m_menus', 'type')) {
            Schema::table('inv_m_menus', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
