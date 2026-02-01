<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "Checking RBAC Configuration\n";
echo str_repeat("=", 70) . "\n\n";

// Check roles
echo "ROLES IN DATABASE:\n";
$roles = Role::orderBy('level')->get();
foreach ($roles as $role) {
    echo "  {$role->name} (Level {$role->level}): " . $role->label . "\n";
}

echo "\n\nTEST ACCOUNTS WITH DETAILS:\n";
$users = User::whereIn('nip', [
    '197505051999031001',
    '196803201990031003',
    '195811081988031004',
    '195508151985031005',
    '194508170000000000'
])->get();

foreach ($users as $user) {
    $role = $user->roleModel;
    echo "\n{$user->name}:\n";
    echo "  NIP: {$user->nip}\n";
    echo "  Role Name: {$user->role}\n";
    echo "  Role ID: {$user->role_id}\n";
    echo "  Role Level: {$user->role_level}\n";
    echo "  Role Model: " . ($role ? $role->name : "NOT FOUND") . "\n";
    echo "  Is Approver (isApprover()): " . ($user->isApprover() ? "YES" : "NO") . "\n";
    echo "  Permissions count: " . ($role ? $role->permissions()->count() : "0") . "\n";
}

echo "\n\nPERMISSION ASSIGNMENTS:\n";
$rolePerms = \DB::table('role_permissions')->get();
echo "Total role_permissions entries: " . $rolePerms->count() . "\n";

if ($rolePerms->count() > 0) {
    echo "\nFirst 10 role_permissions:\n";
    foreach ($rolePerms->take(10) as $perm) {
        $role = Role::find($perm->role_id);
        $permission = \App\Models\Permission::find($perm->permission_id);
        echo "  Role: " . ($role ? $role->name : "?") . " → Permission: " . ($permission ? $permission->name : "?") . "\n";
    }
}
?>
