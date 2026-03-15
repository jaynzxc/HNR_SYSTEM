<?php
// Simple API test without database dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple API Test</h1>";

// Test the login API directly
$testData = [
    'email' => 'roldantiu89@gmail.com',
    'password' => 'customer123'
];

echo "<h2>Testing Login API:</h2>";
echo "<p>Testing with: {$testData['email']}</p>";

// Use cURL to test the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/hotel_resto_system/src/login-register/api/auth/auth.php/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

echo "<h3>HTTP Status Code:</h3>";
echo "<p>$httpCode</p>";

echo "<h3>cURL Error:</h3>";
echo "<p>$curlError</p>";

echo "<h3>Raw Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h3>Parsed JSON:</h3>";
$parsed = json_decode($response, true);
if ($parsed) {
    echo "<pre>";
    print_r($parsed);
    echo "</pre>";
    
    if ($parsed['success']) {
        echo "<p style='color: green;'>✅ Login successful!</p>";
        echo "<p>Redirect URL: " . ($parsed['data']['redirect_to'] ?? 'Not found') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Login failed: " . ($parsed['error'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Failed to parse JSON</p>";
}

echo "<h2>Test Different Credentials:</h2>";

$testCredentials = [
    ['email' => 'mia.cruz@email.com', 'password' => 'customer123'],
    ['email' => 'admin@hotel.com', 'password' => 'admin123'],
    ['email' => 'test@test.com', 'password' => 'test123']
];

foreach ($testCredentials as $creds) {
    echo "<h3>Testing: {$creds['email']}</h3>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/hotel_resto_system/src/login-register/api/auth/auth.php/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($creds));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $parsed = json_decode($response, true);
    
    if ($parsed && $parsed['success']) {
        echo "<p style='color: green;'>✅ Success: {$creds['email']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed: {$creds['email']} - " . ($parsed['error'] ?? 'Unknown') . "</p>";
    }
}
?>
