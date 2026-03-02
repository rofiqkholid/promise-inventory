<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_m_roles', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('remark', 100)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inv_m_roles'); }
};
