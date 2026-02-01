<?php
echo "=== PRODUCTION PAGE ACCESSIBILITY TEST ===\n\n";

$urls = [
    'https://esppd.infiatin.cloud/login' => 'Login Page',
    'https://esppd.infiatin.cloud/about' => 'About Page',
    'https://esppd.infiatin.cloud/guide' => 'Guide Page',
];

foreach ($urls as $url => $name) {
    echo "Testing: $name\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode == 200) {
        echo "  Status: OK (HTTP 200)\n";
    } else {
        echo "  Status: FAILED (HTTP $httpCode)\n";
    }
    echo "\n";
}

echo "=== TEST COMPLETE ===\n";
