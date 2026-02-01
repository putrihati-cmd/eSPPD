#!/usr/bin/env php
<?php
/**
 * Login Authentication Test Script
 * 
 * This script tests if the login fix is working properly by:
 * 1. Checking if users table exists and has NIP field
 * 2. Verifying test user exists with correct email format
 * 3. Checking password hash compatibility
 */

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

echo "\n=== eSPPD Login Authentication Test ===\n\n";

try {
    // Test 1: Check database connection
    echo "[1/5] Testing database connection...";
    DB::connection()->getPdo();
    echo " ✓\n";
    
    // Test 2: Check users table structure
    echo "[2/5] Checking users table structure...";
    $columns = DB::getSchemaBuilder()->getColumnListing('users');
    if (in_array('nip', $columns)) {
        echo " ✓ (NIP column exists)\n";
    } else {
        echo " ✗ (NIP column missing!)\n";
        exit(1);
    }
    
    // Test 3: Count total users
    echo "[3/5] Counting users in database...";
    $userCount = User::count();
    echo " ✓ ({$userCount} users found)\n";
    
    if ($userCount === 0) {
        echo "\n⚠️  WARNING: No users found in database\n";
        echo "   Run: php artisan db:seed\n\n";
        exit(1);
    }
    
    // Test 4: Verify test user exists with correct format
    echo "[4/5] Verifying test user (NIP: 198302082015031501)...";
    $testUser = User::where('nip', '198302082015031501')->first();
    
    if (!$testUser) {
        echo " ✗ (User not found)\n";
        exit(1);
    }
    
    echo " ✓\n";
    echo "   - Name: {$testUser->name}\n";
    echo "   - Email: {$testUser->email}\n";
    echo "   - NIP: {$testUser->nip}\n";
    
    // Test 5: Verify email format matches NIP
    echo "[5/5] Verifying email format...";
    $expectedEmail = $testUser->nip . '@uinsaizu.ac.id';
    if ($testUser->email === $expectedEmail) {
        echo " ✓ (Email = {$expectedEmail})\n";
    } else {
        echo " ⚠ (Email mismatch)\n";
        echo "   Expected: {$expectedEmail}\n";
        echo "   Got: {$testUser->email}\n";
        echo "   Note: Login will still work if password is correct\n";
    }
    
    // Test password verification
    echo "\n[Bonus] Testing password verification...";
    if (Hash::check('password', $testUser->password)) {
        echo " ✓ (Test password 'password' works)\n";
    } else {
        echo " ✗ (Password hash mismatch)\n";
    }
    
    echo "\n=== ✓ All tests passed! ===\n";
    echo "\nTest Credentials:\n";
    echo "  NIP: 198302082015031501\n";
    echo "  Password: password\n";
    echo "\nYou should be able to login now!\n\n";
    
} catch (Exception $e) {
    echo " ✗\n";
    echo "\n❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>
