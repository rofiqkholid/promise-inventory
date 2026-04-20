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
        // 1. tol_m_categories
        Schema::create('tol_m_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps(); // created_at, updated_at
        });

        // 2. tol_m_tool
        Schema::create('tol_m_tool', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 150);
            $table->string('brand', 100);
            $table->string('spec_code', 100)->nullable();
            $table->decimal('diameter', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->string('material_type', 50)->nullable();
            $table->string('hrc', 50)->nullable();
            $table->string('uom', 20);
            $table->integer('pcs_per_unit')->default(1);
            $table->timestamps();

            $table->foreign('category_id', 'fk_tol_tool_category')
                  ->references('id')->on('tol_m_categories')
                  ->onUpdate('cascade');
        });

        // 3. tol_m_inventories
        Schema::create('tol_m_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tool_id'); // Ensure it matches tool id datatype
            $table->enum('moving_status', ['Fast Moving', 'Slow Moving'])->default('Fast Moving');
            $table->string('location', 100)->nullable();
            $table->integer('stock_limit')->default(0);
            $table->integer('current_stock')->default(0);
            $table->decimal('price_per_unit', 15, 2)->default(0.00);
            $table->date('purchase_date')->nullable();
            $table->integer('std_lifetime_yrs')->nullable();
            $table->timestamps();

            $table->foreign('tool_id', 'fk_tol_inventories_tool')
                  ->references('id')->on('tol_m_tool')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tol_m_inventories');
        Schema::dropIfExists('tol_m_tool');
        Schema::dropIfExists('tol_m_categories');
    }
};
