<?php

/**
 * Comprehensive Testing Script
 * Tests actual application functionality end-to-end
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Spd;
use Illuminate\Support\Facades\Auth;

echo "\n" . str_repeat("=", 70) . "\n";
echo "COMPREHENSIVE SYSTEM TEST";
echo "\n" . str_repeat("=", 70) . "\n\n";

// ===== TEST 1: VERIFY ALL TEST ACCOUNTS =====
echo "TEST 1: Verify All Test Accounts\n";
echo str_repeat("-", 70) . "\n\n";

$accounts = [
    ['nip' => '197505051999031001', 'name' => 'Iwan Setiawan', 'role' => 'employee'],
    ['nip' => '196803201990031003', 'name' => 'Bambang Sutrisno', 'role' => 'kabag'],
    ['nip' => '195811081988031004', 'name' => 'Maftuh Asnawi', 'role' => 'wadek'],
    ['nip' => '195508151985031005', 'name' => 'Suwito (Dekan)', 'role' => 'dekan'],
    ['nip' => '194508170000000000', 'name' => 'Admin e-SPPD', 'role' => 'admin'],
];

$allAccountsValid = true;
foreach ($accounts as $acc) {
    $user = User::where('nip', $acc['nip'])->first();
    if ($user && $user->role === $acc['role']) {
        echo "✓ {$acc['name']} ({$acc['role']}): Found\n";
    } else {
        echo "✗ {$acc['name']}: NOT FOUND or role mismatch\n";
        $allAccountsValid = false;
    }
}

echo "\nResult: " . ($allAccountsValid ? "ALL ACCOUNTS VALID\n" : "SOME ACCOUNTS MISSING\n");

// ===== TEST 2: TEST LOGIN FOR EACH ROLE =====
echo "\n\nTEST 2: Test Login for Each Role\n";
echo str_repeat("-", 70) . "\n\n";

$loginTests = [
    ['nip' => '197505051999031001', 'password' => 'Testing@123', 'role_name' => 'Pegawai'],
    ['nip' => '196803201990031003', 'password' => 'Testing@123', 'role_name' => 'Kaprodi'],
    ['nip' => '195811081988031004', 'password' => 'Testing@123', 'role_name' => 'Wadek'],
    ['nip' => '195508151985031005', 'password' => 'Testing@123', 'role_name' => 'Dekan'],
    ['nip' => '194508170000000000', 'password' => 'Admin@eSPPD2026', 'role_name' => 'Admin'],
];

foreach ($loginTests as $test) {
    $credentials = ['nip' => $test['nip'], 'password' => $test['password']];

    if (Auth::guard('web')->attempt($credentials, false)) {
        $user = Auth::guard('web')->user();
        echo "✓ {$test['role_name']} Login: SUCCESS (User: {$user->name})\n";
        Auth::guard('web')->logout();
    } else {
        echo "✗ {$test['role_name']} Login: FAILED\n";
    }
}

// ===== TEST 3: TEST RBAC & PERMISSIONS =====
echo "\n\nTEST 3: Test RBAC & Permissions\n";
echo str_repeat("-", 70) . "\n\n";

// Login as each user and check their permissions
$rbacTests = [
    ['nip' => '197505051999031001', 'role_name' => 'Pegawai', 'can_approve' => false],
    ['nip' => '196803201990031003', 'role_name' => 'Kaprodi', 'can_approve' => true],
    ['nip' => '195811081988031004', 'role_name' => 'Wadek', 'can_approve' => true],
    ['nip' => '195508151985031005', 'role_name' => 'Dekan', 'can_approve' => true],
];

foreach ($rbacTests as $test) {
    $user = User::where('nip', $test['nip'])->first();
    Auth::guard('web')->login($user);

    $canApprove = Auth::user()->can('approve-spd');
    $result = $canApprove === $test['can_approve'] ? "✓" : "✗";

    echo "$result {$test['role_name']}: can_approve_spd = " . ($canApprove ? "YES" : "NO") . "\n";

    Auth::guard('web')->logout();
}

// ===== TEST 4: TEST SPPD COUNT =====
echo "\n\nTEST 4: Test Database Connectivity\n";
echo str_repeat("-", 70) . "\n\n";

try {
    $spdCount = Spd::count();
    echo "✓ Database connected\n";
    echo "  Total SPDs in database: $spdCount\n";
} catch (\Exception $e) {
    echo "✗ Database error: {$e->getMessage()}\n";
}

// ===== TEST 5: TEST PAGES ACCESSIBILITY =====
echo "\n\nTEST 5: Test Application Routes\n";
echo str_repeat("-", 70) . "\n\n";

$routes = [
    '/login' => 'Login',
    '/dashboard' => 'Dashboard',
    '/spd' => 'SPD List',
];

$router = app('router');
$allRoutes = collect($router->getRoutes()->getIterator());

foreach ($routes as $path => $label) {
    $exists = $allRoutes->contains(function ($route) use ($path) {
        return $route->getPath() === $path || $route->getPath() === trim($path, '/');
    });

    echo ($exists ? "✓" : "✗") . " {$label} ($path): " . ($exists ? "REGISTERED" : "NOT FOUND") . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "TEST COMPLETE\n";
echo str_repeat("=", 70) . "\n\n";
