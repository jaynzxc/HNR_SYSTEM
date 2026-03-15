<?php
// Test the login API response
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

// Simulate login request
$testData = [
    'email' => 'mia.cruz@email.com',
    'password' => 'customer123'
];

echo "<h1>Login API Response Test</h1>";
echo "<h2>Test Data:</h2>";
echo "<pre>";
print_r($testData);
echo "</pre>";

echo "<h2>API Call Test:</h2>";

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
echo "<pre>";
echo htmlspecialchars($response);
echo "</pre>";

echo "<h3>Parsed JSON:</h3>";
$parsed = json_decode($response, true);
if ($parsed) {
    echo "<pre>";
    print_r($parsed);
    echo "</pre>";
    
    echo "<h3>Redirect URL Check:</h3>";
    if (isset($parsed['data']['redirect_to'])) {
        echo "<p style='color: green;'>✅ redirect_to found: " . $parsed['data']['redirect_to'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ redirect_to not found in data</p>";
        echo "<p>Available keys in data: " . implode(', ', array_keys($parsed['data'] ?? [])) . "</p>";
    }
    
    if (isset($parsed['redirect_to'])) {
        echo "<p style='color: green;'>✅ redirect_to found at root: " . $parsed['redirect_to'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Failed to parse JSON</p>";
}
?>
