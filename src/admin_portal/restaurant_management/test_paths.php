<?php
// test_paths.php
echo "<h1>Path Testing</h1>";

echo "<h2>Current Directory Information:</h2>";
echo "Current script: " . __FILE__ . "<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Server name: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

echo "<h2>Directory Structure Check:</h2>";
$paths_to_check = [
    'config/database.php' => __DIR__ . '/config/database.php',
    '../config/database.php' => __DIR__ . '/../config/database.php',
    '../../config/database.php' => __DIR__ . '/../../config/database.php',
    'models/MenuModel.php' => __DIR__ . '/models/MenuModel.php',
    '../models/MenuModel.php' => __DIR__ . '/../models/MenuModel.php',
    '../../models/MenuModel.php' => __DIR__ . '/../../models/MenuModel.php',
    'api/menu_api.php' => __DIR__ . '/api/menu_api.php',
    '../api/menu_api.php' => __DIR__ . '/../api/menu_api.php'
];

foreach($paths_to_check as $name => $path) {
    if(file_exists($path)) {
        echo "✅ Found: $name<br>";
    } else {
        echo "❌ Missing: $name at $path<br>";
    }
}

echo "<h2>Suggested Base Path:</h2>";
echo "For includes from admin pages, use: " . dirname(__DIR__) . "<br>";
?>