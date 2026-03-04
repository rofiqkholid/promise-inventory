<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_t_sto_detail', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('event_id');
            $table->integer('product_detail_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->float('system_qty_snapshot');
            $table->float('real_qty_input');
            $table->float('diff_qty');
            $table->integer('auditor_id')->nullable();
            $table->string('note')->nullable();
            $table->string('remark', 100)->nullable();
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->boolean('is_adjusted')->default(false);
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('inv_t_sto_event')->onDelete('cascade');
            $table->foreign('product_detail_id')->references('id')->on('inv_t_product_detail')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('inv_m_locations')->onDelete('set null');
            $table->foreign('reason_id')->references('id')->on('inv_m_sto_reasons')->onDelete('set null');
            $table->foreign('auditor_id')->references('id')->on('users')->onDelete('no action');
        });
    }
    public function down(): void { Schema::dropIfExists('inv_t_sto_detail'); }
};
