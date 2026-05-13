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
        Schema::create('tol_m_sketches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('image_path');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('tol_m_categories')->onDelete('cascade');
        });

        // Also add sketch_id to tool master (specifications)
        Schema::table('tol_m_tools', function (Blueprint $table) {
            $table->unsignedBigInteger('sketch_id')->nullable()->after('category_id');
            $table->foreign('sketch_id')->references('id')->on('tol_m_sketches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tol_m_tools', function (Blueprint $table) {
            $table->dropForeign(['sketch_id']);
            $table->dropColumn('sketch_id');
        });
        Schema::dropIfExists('tol_m_sketches');
    }
};
