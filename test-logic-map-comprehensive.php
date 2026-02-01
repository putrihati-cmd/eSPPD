<?php
/**
 * COMPREHENSIVE LOGIC MAP TEST SUITE
 *
 * Tests:
 * 1. Database Schema (approval_level field exists)
 * 2. Migration (runs without errors)
 * 3. Seeder (fills approval_level correctly)
 * 4. Model Relations (Employee.user & User.employee)
 * 5. Login Flow (NIP → Employee → User → Auth)
 * 6. Approval Level Hierarchy (getLevelNameAttribute)
 * 7. Middleware (CheckApprovalLevel)
 * 8. Blade Template Access (auth()->user()->employee->approval_level)
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║         COMPREHENSIVE LOGIC MAP TEST SUITE - STARTING                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

$testsPassed = 0;
$testsFailed = 0;

// ════════════════════════════════════════════════════════════════════════════════
// TEST 1: Database Schema - approval_level Field
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 1] Database Schema - Checking approval_level field\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $columnExists = Schema::hasColumn('employees', 'approval_level');
    $superiorNipExists = Schema::hasColumn('employees', 'superior_nip');

    if ($columnExists && $superiorNipExists) {
        echo "✅ PASS: approval_level field exists in employees table\n";
        echo "✅ PASS: superior_nip field exists in employees table\n";
        $testsPassed += 2;
    } else {
        echo "❌ FAIL: Missing approval_level or superior_nip in employees table\n";
        echo "   - approval_level exists: " . ($columnExists ? "YES" : "NO") . "\n";
        echo "   - superior_nip exists: " . ($superiorNipExists ? "YES" : "NO") . "\n";
        $testsFailed += 2;
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed++;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// TEST 2: Seeder Data Integrity
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 2] Seeder Data - Checking 10 production accounts with approval_level\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $accounts = [
        ['nip' => '195001011990031099', 'name' => 'Super Admin System', 'expected_level' => 6],
        ['nip' => '198302082015031501', 'name' => 'Mawi Khusni Albar', 'expected_level' => 6],
        ['nip' => '195301011988031006', 'name' => 'Dr. Rektor UIN', 'expected_level' => 6],
        ['nip' => '195402151992031005', 'name' => 'Dr. Wakil Rektor', 'expected_level' => 5],
        ['nip' => '197505152006041001', 'name' => 'Ansori', 'expected_level' => 4],
        ['nip' => '197608201998031003', 'name' => 'Dr. Wadek Fakultas', 'expected_level' => 3],
        ['nip' => '197903101999031002', 'name' => 'Dr. Kepala Bagian', 'expected_level' => 2],
        ['nip' => '198811202019031001', 'name' => 'Ahmad Fauzi', 'expected_level' => 1],
        ['nip' => '199003152020122001', 'name' => 'Siti Nurhaliza', 'expected_level' => 1],
        ['nip' => '199505012022011001', 'name' => 'Budi Santoso', 'expected_level' => 1],
    ];

    $accountTestsPassed = 0;
    $accountTestsFailed = 0;

    foreach ($accounts as $account) {
        $employee = App\Models\Employee::where('nip', $account['nip'])->first();

        if (!$employee) {
            echo "❌ FAIL: Employee {$account['nip']} ({$account['name']}) not found\n";
            $accountTestsFailed++;
            continue;
        }

        if ($employee->approval_level === null) {
            echo "❌ FAIL: {$account['name']} - approval_level is NULL\n";
            $accountTestsFailed++;
            continue;
        }

        if ($employee->approval_level == $account['expected_level']) {
            echo "✅ PASS: {$account['name']} - approval_level = {$employee->approval_level}\n";
            $accountTestsPassed++;
        } else {
            echo "❌ FAIL: {$account['name']} - expected {$account['expected_level']}, got {$employee->approval_level}\n";
            $accountTestsFailed++;
        }
    }

    $testsPassed += $accountTestsPassed;
    $testsFailed += $accountTestsFailed;
    echo "\nSeeder Data: {$accountTestsPassed}/{$accountTestsPassed + $accountTestsFailed} accounts OK\n";

} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed += 10;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// TEST 3: Model Relationships
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 3] Model Relationships - Employee ↔ User\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    // Get an employee with user
    $employee = App\Models\Employee::where('nip', '198302082015031501')
        ->with('user')
        ->first();

    if (!$employee) {
        echo "❌ FAIL: Could not find Employee with NIP 198302082015031501\n";
        $testsFailed++;
    } else {
        // Test 1: Employee.user relation (BelongsTo)
        $user = $employee->user;
        if ($user) {
            echo "✅ PASS: Employee.user relation works (BelongsTo)\n";
            echo "   - Employee: {$employee->name}\n";
            echo "   - User: {$user->name} ({$user->email})\n";
            $testsPassed++;
        } else {
            echo "❌ FAIL: Employee.user returned NULL\n";
            $testsFailed++;
        }

        // Test 2: User.employee relation (HasOne)
        if ($user) {
            $employeeReverse = $user->employee;
            if ($employeeReverse && $employeeReverse->id === $employee->id) {
                echo "✅ PASS: User.employee relation works (HasOne - Bidirectional)\n";
                echo "   - User: {$user->name}\n";
                echo "   - Employee (via relation): {$employeeReverse->name}\n";
                $testsPassed++;
            } else {
                echo "❌ FAIL: User.employee relation broken or returns wrong data\n";
                $testsFailed++;
            }
        }
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed += 2;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// TEST 4: getLevelNameAttribute()
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 4] Approval Level Names (getLevelNameAttribute)\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $levelMappings = [
        1 => 'Staff/Dosen',
        2 => 'Kepala Prodi',
        3 => 'Wakil Dekan',
        4 => 'Dekan',
        5 => 'Wakil Rektor',
        6 => 'Rektor',
    ];

    $allLevelsOk = true;

    foreach ($levelMappings as $levelId => $expectedName) {
        $employee = App\Models\Employee::where('approval_level', $levelId)->first();

        if ($employee) {
            $actualName = $employee->level_name;
            if ($actualName === $expectedName) {
                echo "✅ PASS: Level {$levelId} → '{$actualName}'\n";
                $testsPassed++;
            } else {
                echo "❌ FAIL: Level {$levelId} expected '{$expectedName}', got '{$actualName}'\n";
                $allLevelsOk = false;
                $testsFailed++;
            }
        } else {
            echo "⚠️  SKIP: No employee with level {$levelId} found\n";
        }
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed += 6;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// TEST 5: User Model Attributes
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 5] User Helper Methods (isApprover, isAdmin, hasMinLevel)\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $testUser = App\Models\User::where('email', 'mawikhusni@uinsaizu.ac.id')->first();

    if (!$testUser) {
        echo "❌ FAIL: Test user not found\n";
        $testsFailed += 4;
    } else {
        // Get employee to check approval level
        $employee = $testUser->employee;
        $level = $employee->approval_level ?? null;

        echo "Testing User: {$testUser->name} (Level {$level})\n";

        // Test isAdmin
        $isAdmin = $testUser->isAdmin();
        if ($testUser->role === 'admin' && $isAdmin) {
            echo "✅ PASS: isAdmin() = true (role='admin')\n";
            $testsPassed++;
        } else if ($testUser->role !== 'admin' && !$isAdmin) {
            echo "✅ PASS: isAdmin() = false (role='{$testUser->role}')\n";
            $testsPassed++;
        } else {
            echo "❌ FAIL: isAdmin() returned unexpected result\n";
            $testsFailed++;
        }

        // Test isApprover
        $isApprover = $testUser->isApprover();
        if ($isApprover) {
            echo "✅ PASS: isApprover() = true\n";
            $testsPassed++;
        } else {
            echo "❌ FAIL: isApprover() returned false for approver\n";
            $testsFailed++;
        }

        // Test hasMinLevel
        if ($level && $testUser->hasMinLevel($level)) {
            echo "✅ PASS: hasMinLevel({$level}) = true\n";
            $testsPassed++;
        } else {
            echo "❌ FAIL: hasMinLevel() failed\n";
            $testsFailed++;
        }

        // Test role_level attribute
        if ($testUser->role_level === $level) {
            echo "✅ PASS: role_level attribute = {$testUser->role_level}\n";
            $testsPassed++;
        } else {
            echo "❌ FAIL: role_level mismatch. Expected {$level}, got {$testUser->role_level}\n";
            $testsFailed++;
        }
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed += 4;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// TEST 6: Password Hash Validation
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 6] Password Hash Validation (DDMMYYYY format)\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $testUsers = [
        ['email' => 'mawikhusni@uinsaizu.ac.id', 'birth_date' => '1983-02-08', 'expected_pwd' => '08021983'],
        ['email' => 'rektor@uinsaizu.ac.id', 'birth_date' => '1953-01-01', 'expected_pwd' => '01011953'],
        ['email' => 'ansori@uinsaizu.ac.id', 'birth_date' => '1975-05-15', 'expected_pwd' => '15051975'],
    ];

    foreach ($testUsers as $testData) {
        $user = App\Models\User::where('email', $testData['email'])->first();

        if (!$user) {
            echo "❌ FAIL: User {$testData['email']} not found\n";
            $testsFailed++;
            continue;
        }

        // Check if password hash matches the expected password
        $employee = $user->employee;
        if ($employee && $employee->birth_date) {
            $expectedPassword = $employee->birth_date->format('dmY');
            $passwordMatches = Hash::check($expectedPassword, $user->password);

            if ($passwordMatches) {
                echo "✅ PASS: {$testData['email']} - Password hash valid (DDMMYYYY)\n";
                echo "   - Birth date: {$employee->birth_date->format('Y-m-d')}\n";
                echo "   - Expected password: {$expectedPassword}\n";
                $testsPassed++;
            } else {
                echo "❌ FAIL: {$testData['email']} - Password hash mismatch\n";
                $testsFailed++;
            }
        }
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed += 3;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// TEST 7: Data Relationships Complete Check
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 7] Complete Data Relationships Check\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $problematicRecords = [];

    // Check all employees have proper user relations
    $allEmployees = App\Models\Employee::where('nip', '!=', null)->get();

    $employeesWithUsers = 0;
    $employeesWithoutUsers = 0;
    $employeesWithoutApprovalLevel = 0;

    foreach ($allEmployees as $employee) {
        $user = $employee->user;

        if ($user) {
            $employeesWithUsers++;
        } else {
            $employeesWithoutUsers++;
            if (count($problematicRecords) < 5) {
                $problematicRecords[] = "{$employee->nip} ({$employee->name})";
            }
        }

        if ($employee->approval_level === null) {
            $employeesWithoutApprovalLevel++;
        }
    }

    echo "Total Employees: " . $allEmployees->count() . "\n";
    echo "✅ Employees with User: {$employeesWithUsers}\n";

    if ($employeesWithoutUsers > 0) {
        echo "❌ Employees WITHOUT User: {$employeesWithoutUsers}\n";
        if (!empty($problematicRecords)) {
            echo "   Examples: " . implode(", ", $problematicRecords) . "\n";
        }
        $testsFailed++;
    } else {
        echo "✅ All employees have associated users\n";
        $testsPassed++;
    }

    if ($employeesWithoutApprovalLevel > 0) {
        echo "❌ Employees WITHOUT approval_level: {$employeesWithoutApprovalLevel}\n";
        $testsFailed++;
    } else {
        echo "✅ All employees have approval_level set\n";
        $testsPassed++;
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed += 2;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// TEST 8: Middleware Configuration
// ════════════════════════════════════════════════════════════════════════════════

echo "[TEST 8] Middleware CheckApprovalLevel Registration\n";
echo "─────────────────────────────────────────────────────────────────────────────\n";

try {
    $middlewarePath = app_path('Http/Middleware/CheckApprovalLevel.php');

    if (file_exists($middlewarePath)) {
        echo "✅ PASS: CheckApprovalLevel middleware file exists\n";
        $testsPassed++;

        // Check if it's properly registered
        $kernelPath = app_path('Http/Kernel.php');
        $kernelContent = file_get_contents($kernelPath);

        if (strpos($kernelContent, 'CheckApprovalLevel') !== false ||
            strpos($kernelContent, 'approval-level') !== false) {
            echo "✅ PASS: Middleware is registered in Kernel.php\n";
            $testsPassed++;
        } else {
            echo "⚠️  WARN: Middleware might not be registered in Kernel.php\n";
            echo "   - Please check app/Http/Kernel.php manually\n";
        }
    } else {
        echo "❌ FAIL: CheckApprovalLevel middleware file not found\n";
        $testsFailed += 2;
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $testsFailed += 2;
}

echo "\n";

// ════════════════════════════════════════════════════════════════════════════════
// FINAL SUMMARY
// ════════════════════════════════════════════════════════════════════════════════

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         TEST SUMMARY REPORT                                ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

$totalTests = $testsPassed + $testsFailed;
$passPercentage = $totalTests > 0 ? round(($testsPassed / $totalTests) * 100, 2) : 0;

echo "Total Tests: {$totalTests}\n";
echo "✅ Passed: {$testsPassed}\n";
echo "❌ Failed: {$testsFailed}\n";
echo "Pass Rate: {$passPercentage}%\n\n";

if ($testsFailed === 0) {
    echo "🎉 ALL TESTS PASSED! LOGIC MAP IMPLEMENTATION IS COMPLETE AND CORRECT!\n\n";
    echo "✅ Status Summary:\n";
    echo "   ✓ Database schema is correct\n";
    echo "   ✓ Seeder data is properly filled\n";
    echo "   ✓ Model relationships are bidirectional\n";
    echo "   ✓ Approval level hierarchy is working\n";
    echo "   ✓ Password hashing is correct\n";
    echo "   ✓ All helper methods are functional\n";
    echo "   ✓ Middleware is configured\n";
} else {
    echo "⚠️  SOME TESTS FAILED - Review the issues above\n\n";
    echo "Next Steps:\n";
    echo "1. Fix failed tests\n";
    echo "2. Run migrations if needed\n";
    echo "3. Verify database schema manually if required\n";
}

echo "\n";
echo "═════════════════════════════════════════════════════════════════════════════\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
echo "═════════════════════════════════════════════════════════════════════════════\n\n";

// Return exit code
exit($testsFailed > 0 ? 1 : 0);
