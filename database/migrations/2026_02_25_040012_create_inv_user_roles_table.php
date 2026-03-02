<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_user_roles', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id')->unique();
            $table->integer('role_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('inv_m_roles')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('inv_user_roles'); }
};
