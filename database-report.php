<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DATABASE STRUCTURE REPORT ===\n\n";

// Database connection info
$connection = DB::connection()->getDatabaseName();
echo "Database: {$connection}\n";
echo "Driver: " . DB::connection()->getDriverName() . "\n\n";

// Get all tables
$tables = DB::select("
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_type = 'BASE TABLE'
    ORDER BY table_name
");

echo "=== TABLES (" . count($tables) . ") ===\n";
foreach ($tables as $table) {
    echo "- {$table->table_name}\n";
}
echo "\n";

// For each table, get structure and row count
foreach ($tables as $table) {
    $tableName = $table->table_name;
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "TABLE: {$tableName}\n";
    echo str_repeat("=", 80) . "\n";
    
    // Get row count
    try {
        $count = DB::table($tableName)->count();
        echo "Row Count: {$count}\n\n";
    } catch (\Exception $e) {
        echo "Row Count: ERROR - " . $e->getMessage() . "\n\n";
    }
    
    // Get column information
    $columns = DB::select("
        SELECT 
            column_name,
            data_type,
            character_maximum_length,
            is_nullable,
            column_default
        FROM information_schema.columns
        WHERE table_schema = 'public' 
        AND table_name = ?
        ORDER BY ordinal_position
    ", [$tableName]);
    
    echo "COLUMNS:\n";
    foreach ($columns as $col) {
        $type = $col->data_type;
        if ($col->character_maximum_length) {
            $type .= "({$col->character_maximum_length})";
        }
        $nullable = $col->is_nullable === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $col->column_default ? " DEFAULT {$col->column_default}" : '';
        
        echo "  - {$col->column_name}: {$type} {$nullable}{$default}\n";
    }
    
    // Get constraints
    $constraints = DB::select("
        SELECT
            con.conname as constraint_name,
            con.contype as constraint_type,
            pg_get_constraintdef(con.oid) as definition
        FROM pg_constraint con
        JOIN pg_class rel ON rel.oid = con.conrelid
        WHERE rel.relname = ?
    ", [$tableName]);
    
    if (!empty($constraints)) {
        echo "\nCONSTRAINTS:\n";
        foreach ($constraints as $constraint) {
            $type = [
                'p' => 'PRIMARY KEY',
                'f' => 'FOREIGN KEY',
                'u' => 'UNIQUE',
                'c' => 'CHECK'
            ][$constraint->constraint_type] ?? $constraint->constraint_type;
            
            echo "  - {$constraint->constraint_name} ({$type})\n";
            echo "    {$constraint->definition}\n";
        }
    }
    
    // Get indexes
    $indexes = DB::select("
        SELECT
            indexname,
            indexdef
        FROM pg_indexes
        WHERE tablename = ?
        AND schemaname = 'public'
    ", [$tableName]);
    
    if (!empty($indexes)) {
        echo "\nINDEXES:\n";
        foreach ($indexes as $index) {
            echo "  - {$index->indexname}\n";
        }
    }
    
    // Show sample data if table has rows
    if ($count > 0 && $count <= 10) {
        echo "\nSAMPLE DATA (all {$count} rows):\n";
        try {
            $samples = DB::table($tableName)->limit(10)->get();
            foreach ($samples as $i => $sample) {
                echo "\n  Row " . ($i + 1) . ":\n";
                foreach ((array)$sample as $key => $value) {
                    $displayValue = is_null($value) ? 'NULL' : 
                                   (is_string($value) && strlen($value) > 100 ? 
                                    substr($value, 0, 100) . '...' : $value);
                    echo "    {$key}: {$displayValue}\n";
                }
            }
        } catch (\Exception $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    } elseif ($count > 10) {
        echo "\nSAMPLE DATA (first 5 rows of {$count}):\n";
        try {
            $samples = DB::table($tableName)->limit(5)->get();
            foreach ($samples as $i => $sample) {
                echo "\n  Row " . ($i + 1) . ":\n";
                foreach ((array)$sample as $key => $value) {
                    $displayValue = is_null($value) ? 'NULL' : 
                                   (is_string($value) && strlen($value) > 100 ? 
                                    substr($value, 0, 100) . '...' : $value);
                    echo "    {$key}: {$displayValue}\n";
                }
            }
        } catch (\Exception $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n\n=== CHECK CONSTRAINTS DETAILS ===\n";
$checkConstraints = DB::select("
    SELECT
        tc.table_name,
        con.conname as constraint_name,
        pg_get_constraintdef(con.oid) as definition
    FROM pg_constraint con
    JOIN pg_class rel ON rel.oid = con.conrelid
    JOIN information_schema.table_constraints tc ON tc.constraint_name = con.conname
    WHERE con.contype = 'c'
    AND tc.table_schema = 'public'
    ORDER BY tc.table_name, con.conname
");

foreach ($checkConstraints as $constraint) {
    echo "\nTable: {$constraint->table_name}\n";
    echo "  Constraint: {$constraint->constraint_name}\n";
    echo "  Definition: {$constraint->definition}\n";
}

echo "\n\n=== REPORT COMPLETE ===\n";