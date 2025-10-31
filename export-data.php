<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE DATA EXPORT ===\n\n";

$tables = ['users', 'talent_profiles', 'recruiter_profiles', 'projects', 
           'applications', 'skills', 'categories', 'talent_skills'];

foreach ($tables as $tableName) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "TABLE: {$tableName}\n";
    echo str_repeat("=", 80) . "\n";
    
    try {
        $count = DB::table($tableName)->count();
        echo "Total Rows: {$count}\n\n";
        
        if ($count > 0) {
            $data = DB::table($tableName)->get();
            
            foreach ($data as $i => $row) {
                echo "Row " . ($i + 1) . ":\n";
                echo json_encode($row, JSON_PRETTY_PRINT) . "\n\n";
            }
        } else {
            echo "No data\n";
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}