<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_role_menus', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('menu_id');
            $table->foreign('role_id')->references('id')->on('inv_m_roles')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('inv_m_menus')->onDelete('cascade');
            $table->primary(['role_id', 'menu_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('inv_role_menus'); }
};
