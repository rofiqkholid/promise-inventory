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
        Schema::create('inv_m_revision', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('group_name')->default('Standard');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial data
        DB::table('inv_m_revision')->insert([
            ['code' => 'R', 'group_name' => 'Standard', 'sort_order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'R1', 'group_name' => 'Standard', 'sort_order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'R2', 'group_name' => 'Standard', 'sort_order' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'R3', 'group_name' => 'Standard', 'sort_order' => 40, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'RC', 'group_name' => 'Correction', 'sort_order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'RC1', 'group_name' => 'Correction', 'sort_order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'RC2', 'group_name' => 'Correction', 'sort_order' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_m_revision');
    }
};
