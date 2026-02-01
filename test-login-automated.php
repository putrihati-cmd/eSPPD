<?php

/**
 * Automated Login Flow Test
 * Testing all production accounts with forced password change
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "=== AUTOMATED LOGIN FLOW TEST ===\n\n";

$testAccounts = [
    [
        'name' => 'Admin - Mawi Khusni',
        'email' => 'mawikhusni@uinsaizu.ac.id',
        'nip' => '198302082015031501',
        'password' => '08021983',
        'role' => 'admin',
    ],
    [
        'name' => 'Dekan - Ansori',
        'email' => 'ansori@uinsaizu.ac.id',
        'nip' => '197505152006041001',
        'password' => '15051975',
        'role' => 'dekan',
    ],
    [
        'name' => 'Dosen - Ahmad Fauzi',
        'email' => 'ahmadfauzi@uinsaizu.ac.id',
        'nip' => '198811202019031001',
        'password' => '20111988',
        'role' => 'dosen',
    ],
    [
        'name' => 'Dosen - Siti Nurhaliza',
        'email' => 'sitinurhaliza@uinsaizu.ac.id',
        'nip' => '199003152020122001',
        'password' => '15031990',
        'role' => 'dosen',
    ],
    [
        'name' => 'Dosen - Budi Santoso',
        'email' => 'budisantoso@uinsaizu.ac.id',
        'nip' => '199505012022011001',
        'password' => '01051995',
        'role' => 'dosen',
    ],
];

$testResults = [];

foreach ($testAccounts as $account) {
    echo "Testing: {$account['name']}\n";
    echo "  NIP: {$account['nip']}\n";
    echo "  Password: {$account['password']}\n";

    $user = User::where('email', $account['email'])->first();

    if (!$user) {
        echo "  ❌ User not found in database\n\n";
        $testResults[$account['name']] = 'FAILED: User not found';
        continue;
    }

    // Check password hash validity
    if (!Hash::check($account['password'], $user->password)) {
        echo "  ❌ Password hash mismatch - password may not be DDMMYYYY format\n";
        echo "     Expected: {$account['password']}\n";
        echo "     Stored hash: {$user->password}\n\n";
        $testResults[$account['name']] = 'FAILED: Password mismatch';
        continue;
    }

    // Check is_password_reset flag
    if ($user->is_password_reset) {
        echo "  ⚠️ WARNING: is_password_reset = true (user won't be forced to change password)\n";
        echo "     This user can login directly to dashboard\n\n";
        $testResults[$account['name']] = 'WARNING: No forced password change';
    } else {
        echo "  ✅ is_password_reset = false (will be forced to change password on first login)\n\n";
        $testResults[$account['name']] = 'PASSED: Ready for first login';
    }
}

echo "=== TEST SUMMARY ===\n";
foreach ($testResults as $account => $result) {
    echo "{$account}: {$result}\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Open browser to http://localhost:8000/login\n";
echo "2. Use any of the accounts above with:\n";
echo "   - NIP (18 digits)\n";
echo "   - Password (DDMMYYYY format from birth_date)\n";
echo "3. Expected flow:\n";
echo "   - Auth success\n";
echo "   - Redirect to /auth/force-change-password\n";
echo "   - Enter new password (8+ chars, uppercase, number, special char)\n";
echo "   - Confirm password change\n";
echo "   - Redirect to dashboard\n";
echo "4. Verify dashboard shows role-specific content\n";
