<?php

/**
 * Automated Login Testing for All 461 Production Users
 * Tests: Password hash validity, role presence, is_password_reset flag
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
echo "║     AUTOMATED LOGIN TEST FOR 461 PRODUCTION USERS                  ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

$users = User::orderBy('nip')->get();
$totalUsers = $users->count();
$validPasswords = 0;
$invalidPasswords = 0;
$missingRoles = 0;
$passedTests = 0;

echo "Testing {$totalUsers} users...\n\n";

$sampleResults = [];
$errors = [];

foreach ($users as $i => $user) {
    try {
        // Extract DDMMYYYY from NIP
        $nip = $user->nip;
        if (strlen($nip) < 6) {
            $errors[] = "Invalid NIP format: {$nip}";
            continue;
        }

        $day = substr($nip, 2, 2);
        $month = substr($nip, 4, 2);
        $year = substr($nip, 0, 2);
        $ddmmyyyy = $day . $month . $year;

        // Test 1: Password hash is valid
        if (Hash::check($ddmmyyyy, $user->password)) {
            $validPasswords++;

            // Test 2: Role exists
            if (!empty($user->role)) {
                // Test 3: is_password_reset flag is set
                if ($user->is_password_reset !== null) {
                    $passedTests++;

                    // Save sample result
                    if (count($sampleResults) < 10) {
                        $sampleResults[] = [
                            'name' => $user->name,
                            'nip' => $nip,
                            'email' => $user->email,
                            'password' => $ddmmyyyy,
                            'role' => $user->role,
                            'force_change' => !$user->is_password_reset,
                        ];
                    }
                } else {
                    $errors[] = "Missing is_password_reset: {$nip}";
                }
            } else {
                $missingRoles++;
                $errors[] = "Missing role: {$nip}";
            }
        } else {
            $invalidPasswords++;
            $errors[] = "Password hash mismatch: {$nip}";
        }
    } catch (\Exception $e) {
        $errors[] = "Exception for {$user->nip}: " . $e->getMessage();
    }

    // Progress indicator
    if (($i + 1) % 100 === 0) {
        echo "  Processed: {$i}/{$totalUsers}\n";
    }
}

// Display results
echo "\n" . str_repeat("═", 70) . "\n";
echo "TEST RESULTS:\n";
echo str_repeat("═", 70) . "\n";
echo "Total Users Tested: {$totalUsers}\n";
echo "✅ Valid Passwords: {$validPasswords}\n";
echo "❌ Invalid Passwords: {$invalidPasswords}\n";
echo "❌ Missing Roles: {$missingRoles}\n";
echo "✅ Passed All Tests: {$passedTests}\n";

if ($passedTests === $totalUsers) {
    echo "\n🎉 SUCCESS! All 461 users are ready for login testing!\n";
} else {
    echo "\n⚠️  " . ($totalUsers - $passedTests) . " users have issues\n";
}

// Show samples
echo "\n" . str_repeat("─", 70) . "\n";
echo "SAMPLE VALID ACCOUNTS (First 10):\n";
echo str_repeat("─", 70) . "\n";

foreach ($sampleResults as $i => $sample) {
    echo "\n[" . ($i + 1) . "] {$sample['name']}\n";
    echo "    NIP: {$sample['nip']}\n";
    echo "    Email: {$sample['email']}\n";
    echo "    Password: {$sample['password']}\n";
    echo "    Role: {$sample['role']}\n";
    echo "    Force Change: " . ($sample['force_change'] ? "Yes" : "No") . "\n";
}

// Show errors if any
if (!empty($errors)) {
    echo "\n" . str_repeat("─", 70) . "\n";
    echo "ERRORS/WARNINGS:\n";
    echo str_repeat("─", 70) . "\n";

    $uniqueErrors = array_unique($errors);
    $showErrors = array_slice($uniqueErrors, 0, 20);

    foreach ($showErrors as $error) {
        echo "⚠️  {$error}\n";
    }

    if (count($uniqueErrors) > 20) {
        echo "\n... and " . (count($uniqueErrors) - 20) . " more errors\n";
    }
}

echo "\n" . str_repeat("═", 70) . "\n";
echo "LOGIN TEST URL: https://esppd.infiatin.cloud/login\n";
echo "Use any NIP + DDMMYYYY password from samples above\n";
echo str_repeat("═", 70) . "\n\n";
