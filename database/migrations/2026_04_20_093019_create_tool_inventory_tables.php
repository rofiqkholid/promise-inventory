<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TOOL INVENTORY SYSTEM — Consolidated Migration
     * Includes all 8 tables for both Fast Moving and Slow Moving Tool Management.
     */
    public function up(): void
    {
        // 1. tol_m_categories
        // Categories with moving type (Fast/Slow)
        Schema::create('tol_m_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('moving_type', ['fast', 'slow'])->default('fast')
                  ->comment('Determines if tracking is qty-based (fast) or batch-based (slow)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. tol_m_tools
        // Master specification for tools.
        Schema::create('tol_m_tools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 150);
            $table->string('brand', 100);
            $table->string('spec_code', 100)->nullable()->comment('Unique spec code if available');
            $table->decimal('diameter', 8, 3)->nullable()->comment('mm');
            $table->decimal('length', 8, 3)->nullable()->comment('mm');
            $table->string('material_type', 50)->nullable()->comment('Carbide, HSS, etc.');
            $table->string('hrc', 20)->nullable();
            $table->string('uom', 20)->default('PCS');
            $table->integer('pcs_per_unit')->default(1);
            $table->decimal('price_per_unit', 15, 2)->default(0);
            $table->integer('limit_stock')->default(0)->comment('Min stock for Fast Moving alert');
            $table->integer('std_lifetime_yrs')->nullable()->comment('Std life for Slow Moving depreciation');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes & FK
            $table->index('spec_code');
            $table->foreign('category_id')->references('id')->on('tol_m_categories')->onUpdate('cascade');
        });

        // 3. tol_m_locations
        // Storage locations
        Schema::create('tol_m_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. tol_t_fast_stock
        // Current stock level for Fast Moving tools per location
        Schema::create('tol_t_fast_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tool_id');
            $table->unsignedBigInteger('location_id');
            $table->integer('current_qty')->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['tool_id', 'location_id']);
            $table->foreign('tool_id')->references('id')->on('tol_m_tools')->onUpdate('cascade');
            $table->foreign('location_id')->references('id')->on('tol_m_locations')->onUpdate('cascade');
        });

        // 5. tol_t_transactions
        // Transaction log for Fast Moving tools
        Schema::create('tol_t_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tool_id');
            $table->unsignedBigInteger('location_id');
            $table->enum('transaction_type', ['in', 'out', 'adjustment']);
            $table->integer('qty');
            $table->string('ref_doc', 100)->nullable();
            $table->text('note')->nullable();
            $table->integer('transacted_by'); // Match users.id type
            $table->timestamp('transacted_at');
            $table->timestamps();

            $table->index(['tool_id', 'location_id']);
            $table->index('transacted_at');
            $table->foreign('tool_id')->references('id')->on('tol_m_tools')->onUpdate('cascade');
            $table->foreign('location_id')->references('id')->on('tol_m_locations')->onUpdate('cascade');
            $table->foreign('transacted_by')->references('id')->on('users')->onUpdate('cascade');
        });

        // 6. tol_t_sto_fast
        // Stock Take records for Fast Moving tools
        Schema::create('tol_t_sto_fast', function (Blueprint $table) {
            $table->id();
            $table->date('sto_date');
            $table->unsignedBigInteger('tool_id');
            $table->unsignedBigInteger('location_id');
            $table->integer('system_qty');
            $table->integer('physical_qty');
            $table->integer('adjustment_qty')->default(0);
            $table->text('note')->nullable();
            $table->integer('conducted_by');
            $table->integer('approved_by')->nullable();
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamps();

            $table->index('sto_date');
            $table->foreign('tool_id')->references('id')->on('tol_m_tools')->onUpdate('cascade');
            $table->foreign('location_id')->references('id')->on('tol_m_locations')->onUpdate('cascade');
            $table->foreign('conducted_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onUpdate('cascade');
        });

        // 7. tol_t_slow_batches
        // Asset batches for Slow Moving tools (Depreciable)
        Schema::create('tol_t_slow_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no', 50)->unique();
            $table->unsignedBigInteger('tool_id');
            $table->unsignedBigInteger('location_id');
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 2);
            $table->integer('qty_purchased');
            $table->integer('qty_current');
            $table->integer('std_lifetime_yrs');
            $table->decimal('current_value', 15, 2)->default(0);
            $table->enum('status', ['active', 'nok', 'retired'])->default('active');
            $table->date('nok_date')->nullable();
            $table->text('nok_reason')->nullable();
            $table->integer('nok_by')->nullable();
            $table->timestamps();

            $table->index('purchase_date');
            $table->foreign('tool_id')->references('id')->on('tol_m_tools')->onUpdate('cascade');
            $table->foreign('location_id')->references('id')->on('tol_m_locations')->onUpdate('cascade');
            $table->foreign('nok_by')->references('id')->on('users')->onUpdate('cascade');
        });

        // 8. tol_t_sto_slow
        // Stock Take & Asset Valuation records for Slow Moving batches
        Schema::create('tol_t_sto_slow', function (Blueprint $table) {
            $table->id();
            $table->date('sto_date');
            $table->unsignedBigInteger('batch_id');
            $table->enum('physical_check', ['ok', 'nok']);
            $table->integer('qty_checked');
            $table->integer('qty_ok')->default(0);
            $table->integer('qty_nok')->default(0);
            $table->decimal('age_years', 5, 2);
            $table->decimal('remaining_value', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->integer('conducted_by');
            $table->integer('approved_by')->nullable();
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamps();

            $table->index('sto_date');
            $table->foreign('batch_id')->references('id')->on('tol_t_slow_batches')->onUpdate('cascade');
            $table->foreign('conducted_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tol_t_sto_slow');
        Schema::dropIfExists('tol_t_slow_batches');
        Schema::dropIfExists('tol_t_sto_fast');
        Schema::dropIfExists('tol_t_transactions');
        Schema::dropIfExists('tol_t_fast_stock');
        Schema::dropIfExists('tol_m_locations');
        Schema::dropIfExists('tol_m_tools');
        Schema::dropIfExists('tol_m_categories');
    }
};
