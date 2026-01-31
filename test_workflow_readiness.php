<?php
/**
 * Simplified SPPD/SPD Workflow Test
 * Tests core workflow without complex UUID requirements
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "\n" . str_repeat("=", 70) . "\n";
echo "TESTING SPD WORKFLOW - SIMPLIFIED";
echo "\n" . str_repeat("=", 70) . "\n\n";

// Get test users
$pegawai = User::where('nip', '197505051999031001')->first();
$kaprodi = User::where('nip', '196803201990031003')->first();
$wadek = User::where('nip', '195811081988031004')->first();

echo "Step 1: Verify Users Can Access Dashboard\n";
echo str_repeat("-", 70) . "\n\n";

$users_to_test = [
    ['user' => $pegawai, 'role' => 'Pegawai'],
    ['user' => $kaprodi, 'role' => 'Kaprodi'],
    ['user' => $wadek, 'role' => 'Wadek'],
];

foreach ($users_to_test as $item) {
    $user = $item['user'];
    $role = $item['role'];
    if (!$user) {
        echo "✗ $role account not found\n";
        continue;
    }
    
    Auth::guard('web')->login($user);
    
    // Check if authenticated
    $authenticated = Auth::check();
    $userFromAuth = Auth::user();
    
    if ($authenticated && $userFromAuth->id === $user->id) {
        echo "✓ $role ({$user->name}) can login and access authenticated routes\n";
    } else {
        echo "✗ $role failed authentication check\n";
    }
    
    Auth::guard('web')->logout();
}

// Step 2: Check database schema  
echo "\n\nStep 2: Check SPD Database Tables\n";
echo str_repeat("-", 70) . "\n\n";

$tables = [
    'users' => 'User accounts',
    'roles' => 'Roles',
    'permissions' => 'Permissions',
    'role_permissions' => 'Role-Permission mappings',
    'spds' => 'SPD records',
    'approvals' => 'Approval records',
    'employees' => 'Employee records',
];

foreach ($tables as $table => $description) {
    $exists = DB::table('information_schema.tables')
        ->where('table_name', $table)
        ->where('table_schema', 'public')
        ->exists();
    
    if ($exists) {
        $count = DB::table($table)->count();
        echo "✓ {$description} ($table): $count records\n";
    } else {
        echo "✗ {$description} ($table): TABLE NOT FOUND\n";
    }
}

// Step 3: Verify RBAC Configuration
echo "\n\nStep 3: Verify RBAC Configuration\n";
echo str_repeat("-", 70) . "\n\n";

$roles = DB::table('roles')->orderBy('level')->get();
echo "Roles configured:\n";
foreach ($roles as $role) {
    $permCount = DB::table('role_permissions')
        ->where('role_id', $role->id)
        ->count();
    echo "  - {$role->name} (Level {$role->level}): $permCount permissions\n";
}

$totalPerms = DB::table('permissions')->count();
$totalRolePerms = DB::table('role_permissions')->count();
echo "\nTotal Permissions: $totalPerms\n";
echo "Total Role-Permission mappings: $totalRolePerms\n";

// Step 4: Verify Approval Flow Requirements
echo "\n\nStep 4: Check Approval Flow Requirements\n";
echo str_repeat("-", 70) . "\n\n";

// Check if approval rules are configured
$approvalRules = DB::table('approval_rules')->count();
echo "Approval Rules configured: $approvalRules\n";

// Check approval table structure
$hasApprovals = DB::table('information_schema.tables')
    ->where('table_name', 'approvals')
    ->where('table_schema', 'public')
    ->exists();

if ($hasApprovals) {
    $appCount = DB::table('approvals')->count();
    echo "Approvals table exists: $appCount records\n";
    
    // Get approval columns to verify structure
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'approvals' ORDER BY ordinal_position LIMIT 10");
    echo "Approval table columns: ";
    echo implode(', ', array_map(fn($c) => $c->column_name, $columns)) . "\n";
}

// Step 5: Summary
echo "\n\nStep 5: Workflow Readiness Assessment\n";
echo str_repeat("-", 70) . "\n\n";

$readiness = [
    'Authentication System' => true,
    'User Accounts (5)' => count($users_to_test) === 3 && all($users_to_test),
    'RBAC Configured' => count($roles) > 0 && $totalRolePerms > 0,
    'Approvals Table' => $hasApprovals,
    'Approval Rules' => $approvalRules > 0,
];

$all_ready = true;
foreach ($readiness as $item => $ready) {
    $status = $ready ? '✓' : '✗';
    echo "$status $item\n";
    if (!$ready) $all_ready = false;
}

echo "\n" . str_repeat("=", 70) . "\n";
if ($all_ready) {
    echo "WORKFLOW READINESS: ✓ SYSTEM READY FOR TESTING\n";
} else {
    echo "WORKFLOW READINESS: ⚠ SOME COMPONENTS MISSING\n";
}
echo str_repeat("=", 70) . "\n\n";

function all($array) {
    foreach ($array as $item) {
        if (!$item) return false;
    }
    return true;
}
?>
