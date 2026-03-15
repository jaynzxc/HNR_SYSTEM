<?php
// Check directory structure for booking API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Booking API Directory Check</h1>";

echo "<h2>Current Directory:</h2>";
echo "<p>" . __DIR__ . "</p>";

echo "<h2>Parent Directory:</h2>";
$parent = dirname(__DIR__);
echo "<p>" . $parent . "</p>";

echo "<h2>Grandparent Directory:</h2>";
$grandparent = dirname(dirname(__DIR__));
echo "<p>" . $grandparent . "</p>";

echo "<h2>Checking if config exists:</h2>";
$configPath = '../config/database.php';
if (file_exists($configPath)) {
    echo "<p style='color: green;'>✅ Found: $configPath</p>";
} else {
    echo "<p style='color: red;'>❌ Missing: $configPath</p>";
    
    // Try alternative paths
    $altPath = '../../customer_portal/config/database.php';
    if (file_exists($altPath)) {
        echo "<p style='color: orange;'>🔄 Alternative path works: $altPath</p>";
    }
}

echo "<h2>Contents of parent directory:</h2>";
echo "<pre>";
print_r(scandir(dirname(__DIR__)));
echo "</pre>";
?>
