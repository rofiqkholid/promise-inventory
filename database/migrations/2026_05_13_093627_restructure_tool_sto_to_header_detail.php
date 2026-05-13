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
        // 1. Create the Header table
        Schema::create('tol_t_sto_events', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->date('period_start');
            $table->date('period_end')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->integer('user_id'); // Created by
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('description')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
        });

        // 2. Update Fast STO Details
        DB::statement("
            DECLARE @sql NVARCHAR(MAX) = '';
            SELECT @sql += 'ALTER TABLE tol_t_sto_fast DROP CONSTRAINT ' + fk.name + ';'
            FROM sys.foreign_keys fk
            JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
            JOIN sys.columns c ON fkc.parent_object_id = c.object_id AND fkc.parent_column_id = c.column_id
            WHERE fk.parent_object_id = OBJECT_ID('tol_t_sto_fast')
            AND c.name IN ('conducted_by', 'approved_by');
            IF @sql <> '' EXEC sp_executesql @sql;
        ");

        DB::statement("
            DECLARE @TableName NVARCHAR(255) = 'tol_t_sto_fast';
            DECLARE @ColumnName NVARCHAR(255) = 'status';
            DECLARE @ConstraintName NVARCHAR(255);
            DECLARE @Sql NVARCHAR(MAX);
            DECLARE ConstraintCursor CURSOR FOR
            SELECT d.name FROM sys.default_constraints d
            INNER JOIN sys.columns c ON d.parent_column_id = c.column_id AND d.parent_object_id = c.object_id
            WHERE d.parent_object_id = OBJECT_ID(@TableName) AND c.name = @ColumnName
            UNION
            SELECT cc.name FROM sys.check_constraints cc
            WHERE cc.parent_object_id = OBJECT_ID(@TableName) AND cc.definition LIKE '%' + @ColumnName + '%';
            OPEN ConstraintCursor;
            FETCH NEXT FROM ConstraintCursor INTO @ConstraintName;
            WHILE @@FETCH_STATUS = 0
            BEGIN
                 SET @Sql = 'ALTER TABLE ' + @TableName + ' DROP CONSTRAINT ' + @ConstraintName;
                EXEC(@Sql);
                FETCH NEXT FROM ConstraintCursor INTO @ConstraintName;
            END;
            CLOSE ConstraintCursor;
            DEALLOCATE ConstraintCursor;

            -- Drop Indexes
            DECLARE @IdxName NVARCHAR(255);
            DECLARE IndexCursor CURSOR FOR
            SELECT i.name
            FROM sys.indexes i
            JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
            JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
            WHERE i.object_id = OBJECT_ID(@TableName)
            AND c.name IN ('sto_date', 'status', 'conducted_by', 'approved_by')
            AND i.is_primary_key = 0;
            OPEN IndexCursor;
            FETCH NEXT FROM IndexCursor INTO @IdxName;
            WHILE @@FETCH_STATUS = 0
            BEGIN
                SET @Sql = 'DROP INDEX ' + @IdxName + ' ON ' + @TableName;
                EXEC(@Sql);
                FETCH NEXT FROM IndexCursor INTO @IdxName;
            END;
            CLOSE IndexCursor;
            DEALLOCATE IndexCursor;
        ");

        Schema::table('tol_t_sto_fast', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->foreign('event_id')->references('id')->on('tol_t_sto_events')->onDelete('cascade');
            $table->dropColumn(['sto_date', 'status', 'conducted_by', 'approved_by']);
        });

        // 3. Update Slow STO Details
        DB::statement("
            DECLARE @sql NVARCHAR(MAX) = '';
            SELECT @sql += 'ALTER TABLE tol_t_sto_slow DROP CONSTRAINT ' + fk.name + ';'
            FROM sys.foreign_keys fk
            JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
            JOIN sys.columns c ON fkc.parent_object_id = c.object_id AND fkc.parent_column_id = c.column_id
            WHERE fk.parent_object_id = OBJECT_ID('tol_t_sto_slow')
            AND c.name IN ('conducted_by', 'approved_by');
            IF @sql <> '' EXEC sp_executesql @sql;
        ");

        DB::statement("
            DECLARE @TableName NVARCHAR(255) = 'tol_t_sto_slow';
            DECLARE @ColumnName NVARCHAR(255) = 'status';
            DECLARE @ConstraintName NVARCHAR(255);
            DECLARE @Sql NVARCHAR(MAX);
            DECLARE ConstraintCursor CURSOR FOR
            SELECT d.name FROM sys.default_constraints d
            INNER JOIN sys.columns c ON d.parent_column_id = c.column_id AND d.parent_object_id = c.object_id
            WHERE d.parent_object_id = OBJECT_ID(@TableName) AND c.name = @ColumnName
            UNION
            SELECT cc.name FROM sys.check_constraints cc
            WHERE cc.parent_object_id = OBJECT_ID(@TableName) AND cc.definition LIKE '%' + @ColumnName + '%';
            OPEN ConstraintCursor;
            FETCH NEXT FROM ConstraintCursor INTO @ConstraintName;
            WHILE @@FETCH_STATUS = 0
            BEGIN
                 SET @Sql = 'ALTER TABLE ' + @TableName + ' DROP CONSTRAINT ' + @ConstraintName;
                EXEC(@Sql);
                FETCH NEXT FROM ConstraintCursor INTO @ConstraintName;
            END;
            CLOSE ConstraintCursor;
            DEALLOCATE ConstraintCursor;

            -- Drop Indexes
            DECLARE @IdxNameSlow NVARCHAR(255);
            DECLARE IndexCursorSlow CURSOR FOR
            SELECT i.name
            FROM sys.indexes i
            JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
            JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
            WHERE i.object_id = OBJECT_ID(@TableName)
            AND c.name IN ('sto_date', 'status', 'conducted_by', 'approved_by')
            AND i.is_primary_key = 0;
            OPEN IndexCursorSlow;
            FETCH NEXT FROM IndexCursorSlow INTO @IdxNameSlow;
            WHILE @@FETCH_STATUS = 0
            BEGIN
                SET @Sql = 'DROP INDEX ' + @IdxNameSlow + ' ON ' + @TableName;
                EXEC(@Sql);
                FETCH NEXT FROM IndexCursorSlow INTO @IdxNameSlow;
            END;
            CLOSE IndexCursorSlow;
            DEALLOCATE IndexCursorSlow;
        ");

        Schema::table('tol_t_sto_slow', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->foreign('event_id')->references('id')->on('tol_t_sto_events')->onDelete('cascade');
            $table->dropColumn(['sto_date', 'status', 'conducted_by', 'approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tol_t_sto_events');
        // Note: Full reversal would require re-adding columns and migrating data back
    }
};
