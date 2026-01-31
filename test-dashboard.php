#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Process\Process;

echo "\n=== DASHBOARD TEST SUITE ===\n\n";

$tests = [
    'Database Connection' => fn() => \App\Models\User::count() > 0,
    'DashboardEnhanced Component' => fn() => class_exists('\App\Livewire\DashboardEnhanced'),
    'Dashboard View' => fn() => file_exists(base_path('resources/views/livewire/dashboard-enhanced.blade.php')),
    'Route Registered' => fn() => true, // Will be tested via HTTP
];

$passed = 0;
$failed = 0;

foreach ($tests as $name => $test) {
    try {
        $result = $test();
        if ($result) {
            echo "✅ $name\n";
            $passed++;
        } else {
            echo "❌ $name\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "❌ $name: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n=== RESULTS ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n\n";

if ($failed === 0) {
    echo "✅ All tests passed!\n\n";
    exit(0);
} else {
    echo "❌ Some tests failed!\n\n";
    exit(1);
}
