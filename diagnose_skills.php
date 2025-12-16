#!/usr/bin/env php
<?php

/**
 * Skills Table Diagnostic Script
 * 
 * This script checks the current state of the skills table
 * and helps identify why subcategory_id is null
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n";
echo "========================================\n";
echo "Skills Table Diagnostic\n";
echo "========================================\n\n";

// Check if skills table exists
if (!Schema::hasTable('skills')) {
    echo "❌ ERROR: 'skills' table does not exist!\n\n";
    exit(1);
}

// Get column information
echo "📋 Table Structure:\n";
echo "-------------------\n";
$columns = DB::select("DESCRIBE skills");
foreach ($columns as $column) {
    $null = $column->Null === 'YES' ? 'nullable' : 'required';
    echo "  - {$column->Field} ({$column->Type}) [{$null}]\n";
}
echo "\n";

// Check if subcategory_id column exists
if (!Schema::hasColumn('skills', 'subcategory_id')) {
    echo "⚠️  WARNING: 'subcategory_id' column does not exist in skills table!\n";
    echo "   You need to add this column via migration.\n\n";
    exit(1);
}

// Count total skills
$totalSkills = DB::table('skills')->count();
echo "📊 Statistics:\n";
echo "---------------\n";
echo "  Total skills: {$totalSkills}\n";

// Count skills with category_id
$withCategory = DB::table('skills')->whereNotNull('category_id')->count();
echo "  With category_id: {$withCategory}\n";

// Count skills with subcategory_id
$withSubcategory = DB::table('skills')->whereNotNull('subcategory_id')->count();
echo "  With subcategory_id: {$withSubcategory}\n";

// Count skills without subcategory_id
$withoutSubcategory = DB::table('skills')->whereNull('subcategory_id')->count();
echo "  Without subcategory_id: {$withoutSubcategory}\n\n";

if ($withSubcategory === 0) {
    echo "❌ PROBLEM FOUND: No skills have subcategory_id set!\n";
    echo "   This is why your query returns null.\n\n";
    
    echo "🔧 SOLUTION: You need to either:\n";
    echo "   1. Run a skill seeder that assigns subcategories\n";
    echo "   2. Manually assign subcategories to existing skills\n";
    echo "   3. Update existing skills to have subcategory_id\n\n";
}

// Show sample skills
echo "📝 Sample Skills (first 10):\n";
echo "----------------------------\n";
$sampleSkills = DB::table('skills')
    ->select('id', 'name', 'category_id', 'subcategory_id')
    ->limit(10)
    ->get();

if ($sampleSkills->isEmpty()) {
    echo "  No skills found in database!\n\n";
} else {
    foreach ($sampleSkills as $skill) {
        $catId = $skill->category_id ?? 'NULL';
        $subId = $skill->subcategory_id ?? 'NULL';
        echo "  - {$skill->name}\n";
        echo "    Category: {$catId} | Subcategory: {$subId}\n";
    }
    echo "\n";
}

// Count subcategories
$totalSubcategories = DB::table('subcategories')->count();
echo "📊 Subcategories Available: {$totalSubcategories}\n";

if ($totalSubcategories > 0) {
    echo "\n📋 Sample Subcategories:\n";
    echo "------------------------\n";
    $subcats = DB::table('subcategories')
        ->select('id', 'name', 'category_id')
        ->limit(10)
        ->get();
    
    foreach ($subcats as $sub) {
        echo "  - {$sub->name} (Category ID: {$sub->category_id})\n";
    }
}

echo "\n";
echo "========================================\n";
echo "Diagnostic Complete\n";
echo "========================================\n\n";