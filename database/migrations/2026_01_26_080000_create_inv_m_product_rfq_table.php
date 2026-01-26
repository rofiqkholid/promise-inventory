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
        Schema::create('inv_m_product_rfq', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unique();
            $table->unsignedBigInteger('material_spec_id')->nullable();
            
            $table->decimal('thickness', 10, 4)->nullable()->default(0);
            $table->decimal('width', 10, 4)->nullable()->default(0);
            $table->decimal('length', 10, 4)->nullable()->default(0);
            $table->decimal('length_2', 10, 4)->nullable()->default(0);
            $table->decimal('pitch', 10, 4)->nullable()->default(0);
            
            $table->decimal('density', 10, 6)->nullable()->default(0);
            $table->decimal('weight_kg', 10, 6)->nullable()->default(0);
            
            $table->text('remark')->nullable();
            $table->timestamps();

            // Foreign Keys
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // $table->foreign('material_spec_id')->references('id')->on('inv_m_material_spec')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_m_product_rfq');
    }
};
