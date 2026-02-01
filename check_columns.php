<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;

echo "===== USERS TABLE COLUMNS =====\n";
$columns = DB::select("
    SELECT column_name, data_type
    FROM information_schema.columns
    WHERE table_name = 'users'
    ORDER BY ordinal_position
");

foreach ($columns as $col) {
    echo "  - {$col->column_name} ({$col->data_type})\n";
}

echo "\n===== EMPLOYEES TABLE COLUMNS =====\n";
$columns = DB::select("
    SELECT column_name, data_type
    FROM information_schema.columns
    WHERE table_name = 'employees'
    ORDER BY ordinal_position
");

if (count($columns) > 0) {
    foreach ($columns as $col) {
        echo "  - {$col->column_name} ({$col->data_type})\n";
    }
} else {
    echo "  (Table is empty or doesn't exist)\n";
}

echo "\n===== SAMPLE USERS DATA =====\n";
$users = DB::select("SELECT id, nip, name FROM users LIMIT 3");
foreach ($users as $user) {
    echo "  ID: {$user->id} | NIP: {$user->nip} | Name: {$user->name}\n";
}
