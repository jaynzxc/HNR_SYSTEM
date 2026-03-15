<?php
// Debug script for booking API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Booking API Debug Test</h1>";

// Test 1: Check if required files exist
echo "<h2>Checking Required Files:</h2>";

$files = [
    '../../config/database.php',
    '../../models/User.php', 
    '../../models/SessionManager.php',
    '../../helpers/api_helpers.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ Found: $file</p>";
    } else {
        echo "<p style='color: red;'>❌ Missing: $file</p>";
    }
}

// Test 2: Try to include database config
echo "<h2>Testing Database Connection:</h2>";
try {
    require_once '../../config/database.php';
    echo "<p style='color: green;'>✅ Database config loaded</p>";
    
    if (class_exists('Database')) {
        echo "<p style='color: green;'>✅ Database class available</p>";
    } else {
        echo "<p style='color: red;'>❌ Database class not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Current Directory:</h2>";
echo "<p>" . __DIR__ . "</p>";

echo "<h2>Parent Directory Contents:</h2>";
$parent = dirname(__DIR__);
echo "<pre>";
print_r(scandir($parent));
echo "</pre>";
?>
