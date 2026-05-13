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
        Schema::create('tol_m_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add destination_id to transactions table
        Schema::table('tol_t_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('destination_id')->nullable()->after('location_id');
            $table->foreign('destination_id')->references('id')->on('tol_m_destinations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_t_transactions', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropColumn('destination_id');
        });
        Schema::dropIfExists('tol_m_destinations');
    }
};
