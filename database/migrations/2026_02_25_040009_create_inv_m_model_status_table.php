<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_m_model_status', function (Blueprint $table) {
            $table->integer('model_id')->primary();
            $table->string('project_status', 20)->default('Project');
            $table->timestamps();
            $table->foreign('model_id')->references('id')->on('models')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('inv_m_model_status'); }
};
