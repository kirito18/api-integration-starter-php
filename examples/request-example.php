<?php
declare(strict_types=1);

// Example script calling the local server
$url = 'http://127.0.0.1:8000/proxy';

$payload = [
    'demo' => true,
    'message' => 'Hello from request-example.php',
    'ts' => gmdate('c'),
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
]);

$resp = curl_exec($ch);
if ($resp === false) {
    echo "cURL error: " . curl_error($ch) . PHP_EOL;
    exit(1);
}

$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: {$code}\n";
echo $resp . "\n";