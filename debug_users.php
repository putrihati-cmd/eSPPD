<?php
// Simple debug script to check what NIP format we have
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "\n=== Current Users in Database ===\n\n";
$users = User::where('email', 'LIKE', '%@uinsaizu%')->limit(5)->get();
foreach ($users as $u) {
    echo "Name: {$u->name}\n";
    echo "Email: {$u->email}\n";
    echo "NIP: " . ($u->nip ?: "NULL") . "\n";
    echo "NIP length: " . (strlen($u->nip ?? '') ?? 0) . "\n";
    echo "is_password_reset: " . ($u->is_password_reset ? 'TRUE' : 'FALSE') . "\n";
    echo "---\n";
}

echo "\nTotal users: " . User::count() . "\n";
?>
