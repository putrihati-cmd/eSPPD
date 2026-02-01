<?php
$urls = [
    'https://esppd.infiatin.cloud/login' => 'Login',
    'https://esppd.infiatin.cloud/about' => 'About',
    'https://esppd.infiatin.cloud/guide' => 'Guide',
];

echo "=== PRODUCTION PAGE TESTS ===\n\n";
foreach ($urls as $url => $name) {
    echo "Testing: $name ($url)\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "  HTTP Status: $code\n";

    if ($code == 200) {
        echo "  Status: OK\n";
    } else {
        echo "  Status: FAILED\n";
    }
    echo "\n";
}
