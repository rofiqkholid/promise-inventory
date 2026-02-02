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

        // 1. Master Tables
        
        // Units
        Schema::create('inv_m_unit', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('remark', 100)->nullable();
            $table->timestamps();
        });

        // Material Specs
        Schema::create('inv_m_material_spec', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('spec_name')->unique(); // Matches model 'spec_name'
            $table->string('coating_type')->nullable();
            $table->string('remark', 100)->nullable();
            $table->timestamps();
        });

        // Ranks
        Schema::create('inv_m_rank', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code')->unique();
            $table->string('description')->nullable(); // Matches model
            $table->integer('limit_value')->default(0); // Matches model
            $table->string('remark', 100)->nullable();
            $table->timestamps();
        });

        // Coil Centers
        Schema::create('inv_m_coil_center', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable(); // Added
            $table->string('phone')->nullable(); // Added
            $table->text('address')->nullable(); // Matches model
            $table->string('remark', 100)->nullable();
            $table->timestamps();
        });

        // Suppliers (also used for Destinations)
        Schema::create('inv_m_supplier', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('promise_supp_id')->nullable(); // External ID reference
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('remark', 100)->nullable();
            $table->timestamps();
        });

        // Transaction Categories
        Schema::create('inv_m_transaction_category', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('effect')->default(1); // 1 = Add Stock, -1 = Deduct Stock
            $table->string('remark', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Roles
        Schema::create('inv_m_roles', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('remark', 100)->nullable();
            $table->timestamps();
        });

        // Menus
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

            // Foreign key for self-reference (parent_id)
             $table->foreign('parent_id')->references('id')->on('inv_m_menus')->onDelete('no action');
        });

        // Role Menus (Pivot)
        Schema::create('inv_role_menus', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('menu_id');
            
            $table->foreign('role_id')->references('id')->on('inv_m_roles')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('inv_m_menus')->onDelete('cascade');
            $table->primary(['role_id', 'menu_id']);
        });

        // User Menus (Custom Access)
        Schema::create('inv_user_menus', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id'); 
            $table->integer('menu_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('inv_m_menus')->onDelete('cascade');
        });

        // User Roles (App Roles Assignment)
        Schema::create('inv_user_roles', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id')->unique(); // One role per user context if unique, or remove unique if multiple roles allowed. Previous migration had it unique.
            $table->integer('role_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('inv_m_roles')->onDelete('cascade');
        });

        // VAVE / RFQ Master (Product Baseline)
        Schema::create('inv_m_product_rfq', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('product_id')->nullable(); 
            $table->string('rfq_name')->nullable(); // e.g. Baseline, RFQ-01
            $table->boolean('is_active')->default(false); // Current active baseline
            
            $table->integer('material_spec_id')->nullable();
            $table->integer('unit_id')->nullable();
            
            // Dimensions (Float to match system standard)
            $table->float('thickness')->default(0);
            $table->float('width')->default(0);
            $table->float('length')->default(0);
            $table->float('length_2')->default(0);
            $table->float('pitch')->default(0);
            
            // Weights & Price
            $table->float('density')->default(0);
            $table->float('weight_kg')->default(0);
            $table->float('net_weight')->default(0);
            $table->decimal('material_price', 15, 2)->default(20000); // 15,2 for currency precision
            
            $table->string('remark', 100)->nullable();
            $table->timestamps();

            // Foreign Keys
            // Note: product_id is not unique here to allow multiple baselines per product
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('material_spec_id')->references('id')->on('inv_m_material_spec')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('inv_m_unit')->onDelete('set null');
        });


        // 2. Transaction Tables

        // Product Detail (Inventory Product)
        Schema::create('inv_t_product_detail', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('product_id')->nullable(); 
            $table->integer('material_spec_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('rank_id')->nullable();
            
            $table->string('revision')->nullable();
            
            // Dimensions & Specs
            $table->float('thickness')->nullable()->default(0);
            $table->float('width')->nullable()->default(0);
            $table->float('length')->nullable()->default(0);
            $table->float('length_2')->nullable()->default(0);
            $table->float('pitch')->nullable()->default(0);
            
            // Quantities & Calculations
            $table->integer('pcs_per_unit')->default(0);
            $table->integer('unit_per_car')->default(0);
            
            // Stock Info
            $table->integer('min_stock')->default(0);
            $table->float('current_stock_qty')->default(0);
            $table->float('trial_usage_qty')->default(0);
            
            // Weight & Price
            $table->float('density')->nullable()->default(0);
            $table->float('weight_kg')->nullable()->default(0);
            $table->float('net_weight')->nullable()->default(0);
            $table->decimal('material_price', 15, 2)->nullable()->default(20000);

            $table->boolean('is_active')->default(true);
            $table->string('remark', 100)->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('no action');
            $table->foreign('material_spec_id')->references('id')->on('inv_m_material_spec')->onDelete('no action');
            $table->foreign('unit_id')->references('id')->on('inv_m_unit')->onDelete('no action');
            $table->foreign('rank_id')->references('id')->on('inv_m_rank')->onDelete('no action');
        });

        // Inventory Transaction
        Schema::create('inv_t_inventory_transaction', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('product_detail_id');
            $table->date('transaction_date');
            $table->float('qty');
            $table->integer('transaction_category_id');
            $table->integer('user_id'); 
            
            $table->integer('coil_center_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('destination_id')->nullable(); // For Delivery/Usage

            $table->string('remark', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            // UPDATED_AT is null in model logic usually

            // Foreign Keys
            $table->foreign('product_detail_id')->references('id')->on('inv_t_product_detail')->onDelete('cascade');
            $table->foreign('transaction_category_id')->references('id')->on('inv_m_transaction_category');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('coil_center_id')->references('id')->on('inv_m_coil_center');
            $table->foreign('supplier_id')->references('id')->on('inv_m_supplier')->onDelete('no action');
            $table->foreign('destination_id')->references('id')->on('inv_m_supplier')->onDelete('no action'); 
        });

        // STO (Stock Take Opname) Event
        Schema::create('inv_t_sto_event', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code')->unique();
            $table->string('name');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status'); 
            
            $table->integer('user_id'); 
            $table->integer('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('description')->nullable();
            $table->string('rejection_note', 100)->nullable(); 
            $table->string('remark', 100)->nullable(); 

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('checked_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('no action');
        });

        // STO Detail
        Schema::create('inv_t_sto_detail', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('event_id');
            $table->integer('product_detail_id');
            
            $table->float('system_qty_snapshot');
            $table->float('real_qty_input')->nullable();
            $table->float('diff_qty')->nullable();
            
            $table->integer('auditor_id');
            $table->boolean('is_adjusted')->default(false);
            $table->string('remark', 100)->nullable();
            
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('inv_t_sto_event')->onDelete('cascade');
            $table->foreign('product_detail_id')->references('id')->on('inv_t_product_detail')->onDelete('cascade');
            $table->foreign('auditor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_t_sto_detail');
        Schema::dropIfExists('inv_t_sto_event');
        Schema::dropIfExists('inv_t_inventory_transaction');
        Schema::dropIfExists('inv_t_product_detail');
        Schema::dropIfExists('inv_m_product_rfq');
        Schema::dropIfExists('inv_user_roles');
        Schema::dropIfExists('inv_user_menus');
        Schema::dropIfExists('inv_role_menus');
        Schema::dropIfExists('inv_m_menus');
        Schema::dropIfExists('inv_m_roles');
        Schema::dropIfExists('inv_m_transaction_category');
        Schema::dropIfExists('inv_m_supplier');
        Schema::dropIfExists('inv_m_coil_center');
        Schema::dropIfExists('inv_m_rank');
        Schema::dropIfExists('inv_m_material_spec');
        Schema::dropIfExists('inv_m_unit');
    }
};
