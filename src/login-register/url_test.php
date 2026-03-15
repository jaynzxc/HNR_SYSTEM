<?php
// Test what URL the browser is actually trying to access
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . '/hotel_resto_system/src/';

echo "<h1>URL Resolution Test</h1>";
echo "<p>Protocol: $protocol</p>";
echo "<p>Host: $host</p>";
echo "<p>Base URL: $baseUrl</p>";

$testUrls = [
    'customer_portal' => $baseUrl . 'customer_portal/index.html',
    'admin_portal' => $baseUrl . 'admin_portal/dashboard.html',
    'restaurant_portal' => $baseUrl . 'restaurant_portal/dashboard.html',
    'hotel_portal' => $baseUrl . 'hotel_portal/dashboard.html'
];

echo "<h2>Test URLs:</h2>";
foreach ($testUrls as $name => $url) {
    echo "<p><strong>$name:</strong> $url</p>";
}

echo "<h2>File Existence Check:</h2>";
$basePath = $_SERVER['DOCUMENT_ROOT'] . '/hotel_resto_system/src/';
$files = [
    'customer_portal/index.html' => $basePath . 'customer_portal/index.html',
    'admin_portal/dashboard.html' => $basePath . 'admin_portal/dashboard.html',
    'restaurant_portal/dashboard.html' => $basePath . 'restaurant_portal/dashboard.html',
    'hotel_portal/dashboard.html' => $basePath . 'hotel_portal/dashboard.html'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✅ $name exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $name not found at: $path</p>";
    }
}

echo "<h2>Current Working Directory:</h2>";
echo "<p>" . getcwd() . "</p>";
echo "<p>" . __DIR__ . "</p>";
?>
