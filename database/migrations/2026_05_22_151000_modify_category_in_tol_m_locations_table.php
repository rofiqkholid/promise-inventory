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
        Schema::table('tol_m_locations', function (Blueprint $table) {
            $table->string('category', 50)->default('storage')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_m_locations', function (Blueprint $table) {
            $table->enum('category', ['storage', 'machine', 'subcont'])->default('storage')->change();
        });
    }
};
