<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inv_t_sto_event', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code')->unique();
            $table->string('name');
            $table->date('period_start');
            $table->date('period_end')->nullable();
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
    }
    public function down(): void { Schema::dropIfExists('inv_t_sto_event'); }
};
