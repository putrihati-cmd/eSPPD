#!/usr/bin/env php
<?php

// Load Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "\n╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║        LOGIC MAP COMPREHENSIVE TEST EXECUTION                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

// TEST 1: Migration status
echo "[TEST 1] Migration Status\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$migrationExists = DB::table('migrations')
    ->where('migration', '2026_02_01_000001_add_approval_level_to_employees')
    ->exists();

echo "✓ Approval Level Migration Status: " . ($migrationExists ? "MIGRATED\n" : "NOT YET MIGRATED\n");

// TEST 2: Schema columns
echo "\n[TEST 2] Database Schema\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$approvalLevelExists = Schema::hasColumn('employees', 'approval_level');
$superiorNipExists = Schema::hasColumn('employees', 'superior_nip');

echo "✓ approval_level column: " . ($approvalLevelExists ? "EXISTS\n" : "MISSING ❌\n");
echo "✓ superior_nip column: " . ($superiorNipExists ? "EXISTS\n" : "MISSING ❌\n");

// TEST 3: Seeder Data
echo "\n[TEST 3] Seeder Data - 10 Production Accounts\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$testAccounts = [
    ['nip' => '195001011990031099', 'name' => 'Super Admin System', 'exp_level' => 6],
    ['nip' => '198302082015031501', 'name' => 'Mawi Khusni Albar', 'exp_level' => 6],
    ['nip' => '195301011988031006', 'name' => 'Dr. Rektor UIN', 'exp_level' => 6],
    ['nip' => '195402151992031005', 'name' => 'Dr. Wakil Rektor', 'exp_level' => 5],
    ['nip' => '197505152006041001', 'name' => 'Ansori', 'exp_level' => 4],
    ['nip' => '197608201998031003', 'name' => 'Dr. Wadek', 'exp_level' => 3],
    ['nip' => '197903101999031002', 'name' => 'Dr. Kepala Bagian', 'exp_level' => 2],
    ['nip' => '198811202019031001', 'name' => 'Ahmad Fauzi', 'exp_level' => 1],
    ['nip' => '199003152020122001', 'name' => 'Siti Nurhaliza', 'exp_level' => 1],
    ['nip' => '199505012022011001', 'name' => 'Budi Santoso', 'exp_level' => 1],
];

$passCount = 0;
foreach ($testAccounts as $acc) {
    $emp = \App\Models\Employee::where('nip', $acc['nip'])->first();
    if ($emp) {
        if ($emp->approval_level == $acc['exp_level']) {
            echo "✓ {$acc['name']}: Level {$emp->approval_level}\n";
            $passCount++;
        } else {
            echo "✗ {$acc['name']}: Level {$emp->approval_level} (expected {$acc['exp_level']})\n";
        }
    } else {
        echo "✗ {$acc['name']}: NOT FOUND IN DATABASE\n";
    }
}

echo "\nResult: $passCount/10 accounts with correct approval_level\n";

// TEST 4: Relations
echo "\n[TEST 4] Model Relationships\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$testEmp = \App\Models\Employee::where('nip', '198302082015031501')->with('user')->first();
if ($testEmp && $testEmp->user) {
    echo "✓ Employee.user (BelongsTo) relation works\n";
    echo "  - Employee: {$testEmp->name}\n";
    echo "  - User: {$testEmp->user->name} ({$testEmp->user->email})\n";
} else {
    echo "✗ Employee.user relation BROKEN\n";
}

$testUser = \App\Models\User::where('email', 'mawikhusni@uinsaizu.ac.id')->first();
if ($testUser && $testUser->employee) {
    echo "✓ User.employee (HasOne) relation works\n";
    echo "  - User: {$testUser->name}\n";
    echo "  - Employee: {$testUser->employee->name} (Level {$testUser->employee->approval_level})\n";
} else {
    echo "✗ User.employee relation BROKEN\n";
}

// TEST 5: Level Names
echo "\n[TEST 5] Approval Level Names\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$levelNames = [
    1 => 'Staff/Dosen',
    2 => 'Kepala Prodi',
    3 => 'Wakil Dekan',
    4 => 'Dekan',
    5 => 'Wakil Rektor',
    6 => 'Rektor',
];

foreach ($levelNames as $level => $expectedName) {
    $emp = \App\Models\Employee::where('approval_level', $level)->first();
    if ($emp) {
        $actualName = $emp->level_name;
        if ($actualName === $expectedName) {
            echo "✓ Level $level: '$actualName'\n";
        } else {
            echo "✗ Level $level: got '$actualName' (expected '$expectedName')\n";
        }
    }
}

// TEST 6: Password Hashes
echo "\n[TEST 6] Password Hash Validation\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$pwdTestUsers = [
    ['email' => 'mawikhusni@uinsaizu.ac.id', 'expected_pwd' => '08021983'],
    ['email' => 'rektor@uinsaizu.ac.id', 'expected_pwd' => '01011953'],
    ['email' => 'ansori@uinsaizu.ac.id', 'expected_pwd' => '15051975'],
];

foreach ($pwdTestUsers as $pwdTest) {
    $user = \App\Models\User::where('email', $pwdTest['email'])->first();
    if ($user && $user->employee) {
        $actualPwd = $user->employee->birth_date->format('dmY');
        $hashMatches = Hash::check($actualPwd, $user->password);
        if ($hashMatches) {
            echo "✓ {$user->name}: Password hash valid (DDMMYYYY)\n";
        } else {
            echo "✗ {$user->name}: Password hash INVALID\n";
        }
    }
}

// TEST 7: User Helper Methods
echo "\n[TEST 7] User Helper Methods\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

if ($testUser) {
    echo "Testing: {$testUser->name} (Role: {$testUser->role})\n";
    echo "✓ isAdmin(): " . ($testUser->isAdmin() ? "true" : "false") . "\n";
    echo "✓ isApprover(): " . ($testUser->isApprover() ? "true" : "false") . "\n";
    echo "✓ role_level: {$testUser->role_level}\n";
    echo "✓ hasMinLevel(1): " . ($testUser->hasMinLevel(1) ? "true" : "false") . "\n";
}

// TEST 8: Middleware File
echo "\n[TEST 8] Middleware Configuration\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$middlewarePath = base_path('app/Http/Middleware/CheckApprovalLevel.php');
if (file_exists($middlewarePath)) {
    echo "✓ CheckApprovalLevel middleware file exists\n";
} else {
    echo "✗ CheckApprovalLevel middleware file NOT FOUND\n";
}

echo "\n╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST EXECUTION COMPLETE                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Summary:\n";
echo "- Database schema: " . ($approvalLevelExists && $superiorNipExists ? "✓ OK\n" : "✗ ISSUE\n");
echo "- Seeder data: " . ($passCount === 10 ? "✓ OK\n" : "✗ ISSUES\n");
echo "- Relations: " . ($testEmp && $testEmp->user && $testUser && $testUser->employee ? "✓ OK\n" : "✗ ISSUES\n");
echo "- Middleware: " . (file_exists($middlewarePath) ? "✓ OK\n" : "✗ NOT FOUND\n");

echo "\n";
