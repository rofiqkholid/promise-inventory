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
        // Add category to tool locations
        Schema::table('tol_m_locations', function (Blueprint $table) {
            $table->enum('category', ['storage', 'machine', 'subcont'])->default('storage')->after('name');
        });

        // Restructure transactions: replace destination_id with to_location_id
        Schema::table('tol_t_transactions', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropColumn('destination_id');
            
            $table->unsignedBigInteger('to_location_id')->nullable()->after('location_id');
            $table->foreign('to_location_id')->references('id')->on('tol_m_locations')->onDelete('set null');
        });

        // Drop the temporary destinations table
        Schema::dropIfExists('tol_m_destinations');
    }

    public function down(): void
    {
        Schema::create('tol_m_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tol_t_transactions', function (Blueprint $table) {
            $table->dropForeign(['to_location_id']);
            $table->dropColumn('to_location_id');
            
            $table->unsignedBigInteger('destination_id')->nullable()->after('location_id');
            $table->foreign('destination_id')->references('id')->on('tol_m_destinations')->onDelete('set null');
        });

        Schema::table('tol_m_locations', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
