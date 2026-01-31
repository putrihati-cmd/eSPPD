<?php
/**
 * Test Login Flow Script
 * Tests authentication for all 5 test accounts
 */

// Bootstrap Laravel
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "\n=== TESTING AUTHENTICATION FLOW ===\n\n";

// Test accounts
$testAccounts = [
    ['nip' => '197505051999031001', 'name' => 'Iwan Setiawan', 'password' => 'Testing@123', 'role' => 'pegawai'],
    ['nip' => '196803201990031003', 'name' => 'Bambang Sutrisno', 'password' => 'Testing@123', 'role' => 'kaprodi'],
    ['nip' => '195811081988031004', 'name' => 'Maftuh Asnawi', 'password' => 'Testing@123', 'role' => 'wadek'],
    ['nip' => '195508151985031005', 'name' => 'Suwito', 'password' => 'Testing@123', 'role' => 'dekan'],
    ['nip' => '194508170000000000', 'name' => 'Admin e-SPPD', 'password' => 'Admin@eSPPD2026', 'role' => 'admin'],
];

foreach ($testAccounts as $account) {
    echo "Testing: {$account['name']} (NIP: {$account['nip']}) - Role: {$account['role']}\n";
    
    // Check if user exists
    $user = User::where('nip', $account['nip'])->first();
    
    if (!$user) {
        echo "  ❌ User NOT found in database\n";
        continue;
    }
    
    echo "  ✓ User found: {$user->name} | Email: {$user->email} | Role: {$user->role}\n";
    
    // Verify password can be checked
    if (Hash::check($account['password'], $user->password)) {
        echo "  ✓ Password verification passed\n";
    } else {
        echo "  ✗ Password verification failed (password mismatch)\n";
    }
    
    // Verify role matches
    if ($user->role === $account['role']) {
        echo "  ✓ Role matches: {$user->role}\n";
    } else {
        echo "  ✗ Role mismatch: expected {$account['role']}, got {$user->role}\n";
    }
    
    // Test auth attempt
    $credentials = ['nip' => $account['nip'], 'password' => $account['password']];
    if (Auth::guard('web')->attempt($credentials, false)) {
        $authenticated = Auth::guard('web')->user();
        echo "  ✓ Auth::attempt() SUCCESS\n";
        echo "  ✓ Authenticated as: {$authenticated->name}\n";
        Auth::guard('web')->logout();
    } else {
        echo "  ✗ Auth::attempt() FAILED\n";
    }
    
    echo "\n";
}

echo "=== TEST COMPLETE ===\n";
?>
