<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ALL CHECK CONSTRAINTS ===\n\n";

$constraints = DB::select("
    SELECT
        tc.table_name,
        cc.column_name,
        con.conname as constraint_name,
        pg_get_constraintdef(con.oid) as definition
    FROM pg_constraint con
    JOIN pg_class rel ON rel.oid = con.conrelid
    JOIN information_schema.table_constraints tc ON tc.constraint_name = con.conname
    LEFT JOIN information_schema.constraint_column_usage cc ON cc.constraint_name = con.conname
    WHERE con.contype = 'c'
    AND tc.table_schema = 'public'
    ORDER BY tc.table_name, cc.column_name
");

$currentTable = '';
foreach ($constraints as $constraint) {
    if ($currentTable !== $constraint->table_name) {
        $currentTable = $constraint->table_name;
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "TABLE: {$currentTable}\n";
        echo str_repeat("=", 60) . "\n";
    }
    
    echo "\nColumn: {$constraint->column_name}\n";
    echo "Constraint: {$constraint->constraint_name}\n";
    echo "Definition: {$constraint->definition}\n";
    
    // Try to extract allowed values
    if (preg_match('/CHECK \(\(\([^)]+\)::text = ANY/', $constraint->definition)) {
        preg_match_all("/'([^']+)'/", $constraint->definition, $matches);
        if (!empty($matches[1])) {
            echo "Allowed Values: " . implode(', ', $matches[1]) . "\n";
        }
    }
}