#!/usr/bin/env php
<?php

require __DIR__ . '/../bootstrap/app.php';

$kernel = app(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=== DASHBOARD TEST ===\n\n";

// Test 1: Database connection
echo "Test 1: Database Connection\n";
try {
    $user = \App\Models\User::first();
    echo "✅ Database connected. Found user: " . $user->name . "\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: SPD count
echo "\nTest 2: SPD Count (This Month)\n";
$thisMonth = \Carbon\Carbon::now()->month;
$thisYear = \Carbon\Carbon::now()->year;

$count = \App\Models\Spd::whereMonth('created_at', $thisMonth)
    ->whereYear('created_at', $thisYear)
    ->where('user_id', $user->id)
    ->count();

echo "✅ SPDs this month: " . $count . "\n";

// Test 3: Pending approvals
echo "\nTest 3: Pending Approvals\n";
$pending = \App\Models\Spd::where('status', 'pending_approval')
    ->where('approver_id', $user->id)
    ->count();

echo "✅ Pending approvals: " . $pending . "\n";

// Test 4: Approved this month
echo "\nTest 4: Approved (This Month)\n";
$approved = \App\Models\Spd::where('status', 'approved')
    ->whereMonth('updated_at', $thisMonth)
    ->whereYear('updated_at', $thisYear)
    ->where('approver_id', $user->id)
    ->count();

echo "✅ Approved this month: " . $approved . "\n";

// Test 5: Recent SPDs
echo "\nTest 5: Recent SPDs\n";
$recent = \App\Models\Spd::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

echo "✅ Retrieved " . $recent->count() . " recent SPDs\n";
foreach ($recent as $spd) {
    echo "  - " . $spd->destination . " (" . $spd->status . ")\n";
}

// Test 6: Role check
echo "\nTest 6: User Role\n";
$role = $user->role->name ?? 'unknown';
echo "✅ User role: " . $role . "\n";

// Test 7: Component exists
echo "\nTest 7: Component Exists\n";
$componentPath = 'app/Livewire/DashboardEnhanced.php';
if (file_exists(base_path($componentPath))) {
    echo "✅ Component file exists: " . $componentPath . "\n";
} else {
    echo "❌ Component file missing!\n";
}

// Test 8: View exists
echo "\nTest 8: View Exists\n";
$viewPath = 'resources/views/livewire/dashboard-enhanced.blade.php';
if (file_exists(base_path($viewPath))) {
    echo "✅ View file exists: " . $viewPath . "\n";
} else {
    echo "❌ View file missing!\n";
}

echo "\n=== ALL TESTS COMPLETE ===\n\n";
