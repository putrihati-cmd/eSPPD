#!/usr/bin/env php
<?php
/**
 * Quick Test Script - Check LOGIC MAP Implementation
 * Run: php check-logic-map.php
 */

// Get database connection info from .env
$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') === false) {
            list($key, $val) = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
    }
}

// Connect to database
try {
    $host = $env['DB_HOST'] ?? 'localhost';
    $port = $env['DB_PORT'] ?? 5432;
    $db = $env['DB_DATABASE'] ?? 'esppd';
    $user = $env['DB_USERNAME'] ?? 'postgres';
    $pass = $env['DB_PASSWORD'] ?? '';

    // For PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "\n✓ Database connected successfully\n";
} catch (Exception $e) {
    echo "\n✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "Make sure your .env file is properly configured\n\n";
    exit(1);
}

echo "\n╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║        LOGIC MAP IMPLEMENTATION - QUICK VERIFICATION                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

// TEST 1: Check if approval_level column exists
echo "[TEST 1] Database Schema\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

try {
    $result = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='employees' AND column_name='approval_level'")->fetch();
    if ($result) {
        echo "✓ approval_level column exists\n";
    } else {
        echo "✗ approval_level column MISSING - Run migration first!\n";
        echo "  Command: php artisan migrate\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking column: " . $e->getMessage() . "\n";
}

try {
    $result = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='employees' AND column_name='superior_nip'")->fetch();
    if ($result) {
        echo "✓ superior_nip column exists\n";
    } else {
        echo "✗ superior_nip column MISSING\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking column: " . $e->getMessage() . "\n";
}

// TEST 2: Check seeder data
echo "\n[TEST 2] Seeder Data - 10 Production Accounts\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

$testNips = [
    '195001011990031099' => ['name' => 'Super Admin', 'level' => 6],
    '198302082015031501' => ['name' => 'Mawi Khusni', 'level' => 6],
    '195301011988031006' => ['name' => 'Rektor', 'level' => 6],
    '195402151992031005' => ['name' => 'Warek', 'level' => 5],
    '197505152006041001' => ['name' => 'Dekan', 'level' => 4],
    '197608201998031003' => ['name' => 'Wadek', 'level' => 3],
    '197903101999031002' => ['name' => 'Kaprodi', 'level' => 2],
    '198811202019031001' => ['name' => 'Dosen 1', 'level' => 1],
    '199003152020122001' => ['name' => 'Dosen 2', 'level' => 1],
    '199505012022011001' => ['name' => 'Dosen 3', 'level' => 1],
];

$passCount = 0;
foreach ($testNips as $nip => $data) {
    try {
        $result = $pdo->query("SELECT approval_level FROM employees WHERE nip='$nip'")->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $actualLevel = $result['approval_level'];
            if ($actualLevel == $data['level']) {
                echo "✓ {$data['name']}: level $actualLevel\n";
                $passCount++;
            } else {
                echo "✗ {$data['name']}: level $actualLevel (expected {$data['level']})\n";
            }
        } else {
            echo "✗ {$data['name']}: NOT FOUND in database\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\nResult: $passCount/10 accounts with correct approval_level\n";

// TEST 3: Check Employee-User relationships
echo "\n[TEST 3] Model Relationships\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

try {
    $result = $pdo->query("
        SELECT e.nip, e.name, e.approval_level, u.email, u.name as user_name, u.role
        FROM employees e
        LEFT JOIN users u ON e.user_id = u.id
        WHERE e.nip = '198302082015031501'
    ")->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['user_name']) {
        echo "✓ Employee → User relation works\n";
        echo "  Employee: {$result['name']} (NIP: {$result['nip']}, Level: {$result['approval_level']})\n";
        echo "  User: {$result['user_name']} ({$result['email']}, Role: {$result['role']})\n";
    } else {
        echo "✗ Employee-User relation issue (no user found)\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// TEST 4: Check password hashes
echo "\n[TEST 4] Password Hash Validation\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

try {
    $result = $pdo->query("
        SELECT u.email, e.birth_date, u.password
        FROM users u
        LEFT JOIN employees e ON u.employee_id = e.id
        WHERE u.email = 'mawikhusni@uinsaizu.ac.id'
    ")->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $birthDate = new DateTime($result['birth_date']);
        $expectedPwd = $birthDate->format('dmY');

        // Check if hash matches (using bcrypt)
        echo "User: {$result['email']}\n";
        echo "Birth date: {$result['birth_date']}\n";
        echo "Expected password (DDMMYYYY): $expectedPwd\n";
        echo "Password hash exists: " . (!empty($result['password']) ? "YES\n" : "NO\n");
        echo "Note: Hash validation requires Laravel's Hash facade, but password is set\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// TEST 5: All employees have approval_level
echo "\n[TEST 5] Data Integrity - All Employees\n";
echo "────────────────────────────────────────────────────────────────────────────\n";

try {
    $result = $pdo->query("SELECT COUNT(*) as total, COUNT(approval_level) as with_level FROM employees")->fetch(PDO::FETCH_ASSOC);
    $total = $result['total'];
    $withLevel = $result['with_level'];

    echo "Total employees: $total\n";
    echo "Employees with approval_level: $withLevel\n";

    if ($total == $withLevel) {
        echo "✓ All employees have approval_level set\n";
    } else {
        $missing = $total - $withLevel;
        echo "✗ $missing employees missing approval_level\n";

        $nullResult = $pdo->query("SELECT nip, name FROM employees WHERE approval_level IS NULL LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($nullResult)) {
            echo "  Examples:\n";
            foreach ($nullResult as $emp) {
                echo "    - {$emp['nip']} ({$emp['name']})\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST VERIFICATION COMPLETE                         ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Next Steps:\n";
echo "1. If migrations failed: php artisan migrate\n";
echo "2. If seeder missing data: php artisan db:seed --class=DatabaseSeeder\n";
echo "3. To test login: Visit /login and try NIP: 198302082015031501, Password: 08021983\n\n";
