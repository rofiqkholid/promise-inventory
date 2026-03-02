<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_m_product_rfq', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('product_id')->nullable();
            $table->string('rfq_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('material_spec_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->float('thickness')->default(0)->nullable();
            $table->float('width')->default(0)->nullable();
            $table->float('length')->default(0)->nullable();
            $table->float('length_2')->default(0)->nullable();
            $table->float('pitch')->default(0)->nullable();
            $table->float('density')->default(0)->nullable();
            $table->float('weight_kg')->default(0);
            $table->float('net_weight')->default(0)->nullable();
            $table->decimal('material_price', 15, 2)->default(20000)->nullable();
            $table->string('remark', 100)->nullable();
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('material_spec_id')->references('id')->on('inv_m_material_spec')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('inv_m_unit')->onDelete('set null');
        });
    }
    public function down(): void { Schema::dropIfExists('inv_m_product_rfq'); }
};
