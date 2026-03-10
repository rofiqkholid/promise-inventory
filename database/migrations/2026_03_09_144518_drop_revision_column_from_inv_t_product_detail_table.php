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
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            // Drop column if exists
            if (Schema::hasColumn('inv_t_product_detail', 'revision')) {
                // MS SQL Server specific: drop constraints first if any
                $dbType = DB::getDriverName();
                if ($dbType === 'sqlsrv') {
                    $this->dropColumnConstraints('inv_t_product_detail', 'revision');
                }
                $table->dropColumn('revision');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_t_product_detail', function (Blueprint $table) {
            $table->string('revision', 20)->nullable();
        });
    }

    /**
     * Drop constraints for a column in MS SQL Server
     */
    private function dropColumnConstraints($table, $column)
    {
        // Drop default constraints
        $defaults = DB::select("
            SELECT name 
            FROM sys.default_constraints 
            WHERE parent_object_id = OBJECT_ID('$table') 
            AND parent_column_id = COLUMNPROPERTY(OBJECT_ID('$table'), '$column', 'ColumnId')
        ");

        foreach ($defaults as $d) {
            DB::statement("ALTER TABLE [$table] DROP CONSTRAINT [{$d->name}]");
        }

        // Drop indexes
        $indexes = DB::select("
            SELECT i.name
            FROM sys.indexes i
            JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
            WHERE i.object_id = OBJECT_ID('$table')
            AND ic.column_id = COLUMNPROPERTY(OBJECT_ID('$table'), '$column', 'ColumnId')
        ");

        foreach ($indexes as $idx) {
            DB::statement("DROP INDEX [{$idx->name}] ON [$table]");
        }
    }
};
