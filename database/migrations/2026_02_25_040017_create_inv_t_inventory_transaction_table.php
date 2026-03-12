<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_t_inventory_transaction', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('product_detail_id');
            $table->date('transaction_date');
            $table->float('qty');
            $table->integer('transaction_category_id');
            $table->integer('user_id');
            $table->integer('coil_center_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('destination_id')->nullable();
            $table->string('remark', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('product_detail_id')->references('id')->on('inv_t_product_detail')->onDelete('cascade');
            $table->foreign('transaction_category_id')->references('id')->on('inv_m_transaction_category');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('coil_center_id')->references('id')->on('inv_m_coil_center');
            $table->foreign('supplier_id')->references('id')->on('inv_m_supplier')->onDelete('no action');
            $table->foreign('destination_id')->references('id')->on('inv_m_supplier')->onDelete('no action');
        });
    }
    public function down(): void { Schema::dropIfExists('inv_t_inventory_transaction'); }
};
