<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== USER STATISTICS ===\n";
echo "Total Users: " . User::count() . "\n\n";

echo "Users by Role:\n";
$roleStats = User::selectRaw('role, COUNT(*) as total')
    ->groupBy('role')
    ->orderBy('total', 'desc')
    ->get();

foreach ($roleStats as $stat) {
    echo "  {$stat->role}: {$stat->total}\n";
}

echo "\n=== SAMPLE ACCOUNTS FOR TESTING ===\n";
echo "Production Accounts (from DatabaseSeeder):\n";
$prodUsers = User::whereIn('email', [
    'mawikhusni@uinsaizu.ac.id',
    'ansori@uinsaizu.ac.id',
    'ahmadfauzi@uinsaizu.ac.id',
    'sitinurhaliza@uinsaizu.ac.id',
    'budisantoso@uinsaizu.ac.id'
])->orderBy('role')->get();

foreach ($prodUsers as $user) {
    echo "  {$user->email} ({$user->role}) - is_password_reset: " . ($user->is_password_reset ? 'true' : 'false') . "\n";
}

echo "\nProduction Accounts (from DatabaseSeeder):\n";
$testUsers = User::whereIn('email', [
    'admin@esppd.test',
    'rektor@esppd.test',
    'warek@esppd.test',
    'dekan@esppd.test',
    'wadek@esppd.test',
    'kaprodi@esppd.test',
    'dosen@esppd.test'
])->orderBy('role')->get();

foreach ($testUsers as $user) {
    echo "  {$user->email} ({$user->role}) - is_password_reset: " . ($user->is_password_reset ? 'true' : 'false') . "\n";
}
