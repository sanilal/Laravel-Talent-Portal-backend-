<?php

/**
 * Simple script to check database table structures
 * Run with: php check-tables.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "==================================\n";
echo "DATABASE SCHEMA CHECKER\n";
echo "==================================\n\n";

$tables = ['users', 'talent_profiles', 'recruiter_profiles', 'projects', 'skills', 'categories'];

foreach ($tables as $table) {
    echo "Table: $table\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    try {
        $columns = DB::select("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns 
            WHERE table_name = ? 
            ORDER BY ordinal_position
        ", [$table]);
        
        if (empty($columns)) {
            echo "⚠️  Table not found or has no columns\n\n";
            continue;
        }
        
        foreach ($columns as $col) {
            $nullable = $col->is_nullable === 'YES' ? '(nullable)' : '(required)';
            echo sprintf("  %-25s %-15s %s\n", 
                $col->column_name, 
                $col->data_type,
                $nullable
            );
        }
        
    } catch (\Exception $e) {
        echo "❌ Error checking table: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "==================================\n";
echo "Schema check complete!\n";
echo "==================================\n";