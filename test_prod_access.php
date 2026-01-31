<?php
// Test production server access
foreach (['https://esppd.infiatin.cloud/login', 'https://esppd.infiatin.cloud/about', 'https://esppd.infiatin.cloud/guide'] as $url) {
    echo "Testing: $url\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "  HTTP Status: $httpCode";
    if ($error) echo " (Error: $error)";
    echo "\n";
}
?>