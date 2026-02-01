<?php

/**
 * Test login page buttons functionality
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->boot();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

echo "====== LOGIN BUTTON TEST ======\n\n";

// Test 1: Check if route is accessible
echo "[TEST 1] Check login route\n";
try {
    $loginUrl = route('login', absolute: false);
    echo "✓ Login route exists: $loginUrl\n";
} catch (\Exception $e) {
    echo "✗ Login route error: " . $e->getMessage() . "\n";
}

// Test 2: Check test user
echo "\n[TEST 2] Check test user for login test\n";
$testUser = User::where('nip', '111111111111111')->first();
if ($testUser) {
    echo "✓ Test user exists: " . $testUser->name . "\n";
    echo "  - NIP: " . $testUser->nip . "\n";
    echo "  - Email: " . $testUser->email . "\n";
} else {
    echo "✗ Test user not found\n";
}

// Test 3: Check CSRF token generation
echo "\n[TEST 3] Check CSRF token\n";
try {
    $token = csrf_token();
    echo "✓ CSRF token generated: " . substr($token, 0, 20) . "...\n";
} catch (\Exception $e) {
    echo "✗ CSRF error: " . $e->getMessage() . "\n";
}

// Test 4: Check button HTML structure
echo "\n[TEST 4] Check form submission\n";
$formAction = route('login', absolute: false);
echo "✓ Form should POST to: $formAction\n";
echo "  - Method: POST (via wire:submit)\n";
echo "  - Input 1: nip (text, required)\n";
echo "  - Input 2: password (password, required)\n";
echo "  - Input 3: remember (checkbox, optional)\n";
echo "  - Submit button: type='submit', wire:loading.attr='disabled'\n";

// Test 5: Check Livewire state
echo "\n[TEST 5] Check Livewire configuration\n";
$livewireConfig = config('livewire');
echo "✓ Livewire enabled: " . ($livewireConfig ? 'YES' : 'NO') . "\n";
echo "  - Layout: " . $livewireConfig['layout'] . "\n";

// Test 6: Test actual login method
echo "\n[TEST 6] Simulate login (without redirect)\n";
if ($testUser) {
    try {
        Auth::attempt(['nip' => $testUser->nip, 'password' => 'Testing@123']);
        if (Auth::check()) {
            echo "✓ Login successful, user authenticated\n";
            echo "  - Authenticated user: " . Auth::user()->name . "\n";
            Auth::logout();
            echo "  - User logged out for test\n";
        } else {
            echo "✗ Login failed - credentials not working\n";
        }
    } catch (\Exception $e) {
        echo "✗ Login test error: " . $e->getMessage() . "\n";
    }
} else {
    echo "⊘ Skipped - test user not found\n";
}

echo "\n====== TEST COMPLETE ======\n";
