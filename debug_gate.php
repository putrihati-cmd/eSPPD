<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

echo "Debugging approve-spd Gate\n";
echo str_repeat("=", 70) . "\n\n";

// Test each role
$testNips = [
    '196803201990031003' => 'Kaprodi',
    '195811081988031004' => 'Wadek',
    '195508151985031005' => 'Dekan',
];

foreach ($testNips as $nip => $role_name) {
    $user = User::where('nip', $nip)->first();
    if (!$user) {
        echo "✗ User $nip not found\n";
        continue;
    }

    echo "\nTesting $role_name (NIP: $nip)\n";
    echo str_repeat("-", 50) . "\n";

    // Directly call the gate
    Auth::guard('web')->login($user);

    echo "  User role: {$user->role}\n";
    echo "  User role_level: {$user->role_level}\n";
    echo "  isApprover(): " . ($user->isApprover() ? "YES" : "NO") . "\n";

    // Test the gate
    $canApprove = Auth::user()->can('approve-spd');
    echo "  Auth::user()->can('approve-spd'): " . ($canApprove ? "YES" : "NO") . "\n";

    // Test raw gate call
    $gateResult = Gate::allows('approve-spd');
    echo "  Gate::allows('approve-spd'): " . ($gateResult ? "YES" : "NO") . "\n";

    Auth::guard('web')->logout();
}
