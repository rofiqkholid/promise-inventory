<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQL Server has check constraints for enum columns which prevent inserting new string values.
        // We need to dynamically find and drop any check constraints on the category column.
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = '';
                SELECT @sql += 'ALTER TABLE [tol_m_locations] DROP CONSTRAINT [' + name + '];'
                FROM sys.check_constraints
                WHERE parent_object_id = OBJECT_ID('tol_m_locations')
                  AND col_name(parent_object_id, parent_column_id) = 'category';
                IF @sql <> ''
                    EXEC sp_executesql @sql;
            ");
        }

        // Just to be absolutely sure, re-apply the string column type change
        Schema::table('tol_m_locations', function (Blueprint $table) {
            $table->string('category', 50)->default('storage')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or change back if necessary, but changing back to enum has issues in SQL Server without manual constraint recreation.
    }
};
