<?php

/**
 * Verify All 8 Roles Have Accounts
 */
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
echo "║           PRODUCTION ACCOUNTS - ALL 8 ROLES VERIFICATION             ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

$allRoles = [
    'superadmin' => 'Super Administrator (level: 99)',
    'admin' => 'Admin Kepegawaian (level: 98)',
    'rektor' => 'Rektor (level: 6)',
    'warek' => 'Wakil Rektor (level: 5)',
    'dekan' => 'Dekan (level: 4)',
    'wadek' => 'Wakil Dekan (level: 3)',
    'kabag' => 'Kepala Bagian / Kaprodi (level: 2)',
    'dosen' => 'Dosen / Pegawai (level: 1)',
];

$accounts = [
    ['email' => 'superadmin@uinsaizu.ac.id', 'name' => 'Super Admin System', 'password' => '01011950', 'role' => 'superadmin'],
    ['email' => 'mawikhusni@uinsaizu.ac.id', 'name' => 'Mawi Khusni Albar', 'password' => '08021983', 'role' => 'admin'],
    ['email' => 'rektor@uinsaizu.ac.id', 'name' => 'Dr. Rektor UIN', 'password' => '01011953', 'role' => 'rektor'],
    ['email' => 'warek@uinsaizu.ac.id', 'name' => 'Dr. Wakil Rektor', 'password' => '15021954', 'role' => 'warek'],
    ['email' => 'ansori@uinsaizu.ac.id', 'name' => 'Ansori', 'password' => '15051975', 'role' => 'dekan'],
    ['email' => 'wadek@uinsaizu.ac.id', 'name' => 'Dr. Wadek Fakultas', 'password' => '20081976', 'role' => 'wadek'],
    ['email' => 'kaprodi@uinsaizu.ac.id', 'name' => 'Dr. Kepala Bagian', 'password' => '10031979', 'role' => 'kabag'],
    ['email' => 'ahmadfauzi@uinsaizu.ac.id', 'name' => 'Ahmad Fauzi', 'password' => '20111988', 'role' => 'dosen'],
    ['email' => 'sitinurhaliza@uinsaizu.ac.id', 'name' => 'Siti Nurhaliza', 'password' => '15031990', 'role' => 'dosen'],
    ['email' => 'budisantoso@uinsaizu.ac.id', 'name' => 'Budi Santoso', 'password' => '01051995', 'role' => 'dosen'],
];

$found = [];
$missing = [];

foreach ($accounts as $acc) {
    $user = User::where('email', $acc['email'])->first();
    $role = $allRoles[$acc['role']] ?? 'Unknown';

    if (!$user) {
        $missing[$acc['role']] = "❌ {$acc['name']} NOT FOUND";
    } else {
        $passwordOk = Hash::check($acc['password'], $user->password) ? "✓" : "✗";
        $found[$acc['role']] = [
            'user' => $user,
            'account' => $acc,
            'passwordOk' => $passwordOk,
            'roleLabel' => $role,
        ];
    }
}

// Display results
echo "ACCOUNTS FOUND:\n";
foreach ($allRoles as $roleName => $roleLabel) {
    if (isset($found[$roleName])) {
        $data = $found[$roleName];
        echo "\n✅ {$roleLabel}\n";
        echo "   Name: {$data['account']['name']}\n";
        echo "   Email: {$data['account']['email']}\n";
        echo "   NIP: {$data['user']->nip}\n";
        echo "   Password (DDMMYYYY): {$data['account']['password']} {$data['passwordOk']}\n";
        echo "   Force Change: " . ($data['user']->is_password_reset ? "No" : "Yes") . "\n";
    } else {
        echo "\n❌ {$roleLabel}\n";
        echo "   MISSING ACCOUNT\n";
    }
}

echo "\n" . str_repeat("─", 70) . "\n";
echo "SUMMARY:\n";
echo "Total Found: " . count($found) . " / " . count($allRoles) . "\n";
echo "Total Missing: " . count($missing) . "\n";

if (count($missing) === 0) {
    echo "\n🎉 ALL ROLES HAVE ACCOUNTS! Ready for testing.\n";
} else {
    echo "\n⚠️  Missing accounts:\n";
    foreach ($missing as $role => $msg) {
        echo "  - {$msg}\n";
    }
}

echo "\n";
