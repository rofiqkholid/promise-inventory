<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_t_product_detail', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('product_id')->nullable();
            $table->integer('model_id')->nullable();
            $table->integer('material_spec_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('rank_id')->nullable();
            $table->string('revision')->nullable();
            $table->float('thickness')->nullable()->default(0);
            $table->float('width')->nullable()->default(0);
            $table->float('length')->nullable()->default(0);
            $table->float('length_2')->nullable()->default(0);
            $table->float('pitch')->nullable()->default(0);
            $table->integer('pcs_per_unit')->default(0);
            $table->integer('unit_per_car')->default(0);
            $table->integer('min_stock')->default(0);
            $table->float('current_stock_qty')->default(0);
            $table->float('trial_usage_qty')->default(0);
            $table->float('density')->nullable()->default(0);
            $table->float('weight_kg')->nullable()->default(0);
            $table->float('net_weight')->nullable()->default(0);
            $table->decimal('material_price', 15, 2)->nullable()->default(20000);
            $table->boolean('is_active')->default(true);
            $table->string('remark', 100)->nullable();
            $table->string('product_status')->nullable();
            $table->string('product_status_remark')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('no action');
            $table->foreign('model_id')->references('id')->on('models')->onDelete('no action');
            $table->foreign('material_spec_id')->references('id')->on('inv_m_material_spec')->onDelete('no action');
            $table->foreign('unit_id')->references('id')->on('inv_m_unit')->onDelete('no action');
            $table->foreign('rank_id')->references('id')->on('inv_m_rank')->onDelete('no action');
        });
    }
    public function down(): void { Schema::dropIfExists('inv_t_product_detail'); }
};
