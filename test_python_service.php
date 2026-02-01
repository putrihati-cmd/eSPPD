<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PythonDocumentService;
use App\Models\Spd;

$service = new PythonDocumentService();
$spd = Spd::first();

if (!$spd) {
    die("No SPD found in database. Seed first.\n");
}

echo "Testing Python Document Service connectivity...\n";
echo "SPD ID: " . $spd->id . "\n";
echo "SPD Number: " . $spd->spd_number . "\n";

try {
    $content = $service->getSptPdf($spd);
    if ($content) {
        echo "✅ Success! Received " . strlen($content) . " bytes of PDF content.\n";
        // Check if starts with PDF magic number
        if (strpos($content, '%PDF-') === 0) {
            echo "✅ Verified: Content is a valid PDF file.\n";
        } else {
            echo "❌ Warning: Content does not appear to be a PDF. Length: " . strlen($content) . "\n";
            echo "First 100 bytes: " . substr($content, 0, 100) . "\n";
        }
    } else {
        echo "❌ Failed to receive content from Python service.\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
