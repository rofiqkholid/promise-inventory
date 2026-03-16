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
        // Remove the unique constraint on user_id in inv_user_roles table
        // We do this by dropping the index if it exists. 
        // In many setups, unique('user_id') creates an index named 'inv_user_roles_user_id_unique'
        Schema::table('inv_user_roles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_user_roles', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }
};
