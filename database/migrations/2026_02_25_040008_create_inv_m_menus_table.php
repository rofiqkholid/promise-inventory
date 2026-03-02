<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_m_menus', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('title');
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('parent_id')->nullable();
            $table->string('remark', 100)->nullable();
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('inv_m_menus')->onDelete('no action');
        });
    }
    public function down(): void { Schema::dropIfExists('inv_m_menus'); }
};
