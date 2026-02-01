<?php
$urls = [
    'https://esppd.infiatin.cloud/login' => 'Login',
    'https://esppd.infiatin.cloud/about' => 'About',
    'https://esppd.infiatin.cloud/guide' => 'Guide',
];

foreach ($urls as $url => $name) {
    echo "$name: ";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $code = curl_getinfo(curl_exec($ch), CURLINFO_HTTP_CODE);
    echo ($code == 200 ? "OK" : "FAILED ($code)") . "\n";
}
?>