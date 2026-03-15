<?php
// Debug script to check what's causing the 500 errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>API Debug Test</h1>";

// Test 1: Check if required files exist
echo "<h2>Checking Required Files:</h2>";

$files = [
    '../../../customer_portal/config/database.php',
    '../../../customer_portal/models/User.php', 
    '../../../customer_portal/models/SessionManager.php',
    '../../../customer_portal/helpers/api_helpers.php'
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
    require_once '../../../customer_portal/config/database.php';
    echo "<p style='color: green;'>✅ Database config loaded</p>";
    
    if (class_exists('Database')) {
        echo "<p style='color: green;'>✅ Database class available</p>";
    } else {
        echo "<p style='color: red;'>❌ Database class not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 3: Check session
echo "<h2>Session Test:</h2>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "<p style='color: green;'>✅ Session started</p>";
} else {
    echo "<p style='color: green;'>✅ Session already active</p>";
}

echo "<h2>Current Directory:</h2>";
echo "<p>" . __DIR__ . "</p>";

echo "<h2>File Structure:</h2>";
$dir = __DIR__;
echo "<pre>";
print_r(scandir($dir));
echo "</pre>";
?>
