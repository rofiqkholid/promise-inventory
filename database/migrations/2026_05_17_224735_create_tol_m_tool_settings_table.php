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
        Schema::create('tol_m_tool_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tol_m_tools')->onDelete('cascade');
            $table->string('material_category', 100)->nullable();
            $table->integer('spindle_speed')->nullable();
            $table->integer('table_feed')->nullable();
            $table->double('depth_of_cut')->nullable();
            $table->string('step_over', 50)->nullable();
            $table->boolean('cnc_small_plant_b')->default(false);
            $table->boolean('cnc_big_hartford_plant_f')->default(false);
            $table->string('status', 50)->default('USE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tol_m_tool_settings');
    }
};
