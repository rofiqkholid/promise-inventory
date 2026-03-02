<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_m_transaction_category', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('effect')->default(1);
            $table->string('remark', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('inv_m_transaction_category'); }
};
