<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ORDER BY table_name");

echo "=== DATABASE STATISTICS ===\n\n";
echo "Database: " . DB::connection()->getDatabaseName() . "\n\n";

echo "TABLE                          | ROWS\n";
echo str_repeat("-", 50) . "\n";

foreach ($tables as $table) {
    $tableName = $table->table_name;
    try {
        $count = DB::table($tableName)->count();
        echo str_pad($tableName, 30) . " | " . $count . "\n";
    } catch (\Exception $e) {
        echo str_pad($tableName, 30) . " | ERROR\n";
    }
}