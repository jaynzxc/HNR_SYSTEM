<?php
// Test redirect paths from login-register directory
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Redirect Path Test</h1>";

echo "<h2>Current Directory:</h2>";
echo "<p>" . __DIR__ . "</p>";

echo "<h2>Parent Directory:</h2>";
$parent = dirname(__DIR__);
echo "<p>" . $parent . "</p>";

echo "<h2>Customer Portal Path:</h2>";
$customerPortalPath = dirname(__DIR__) . '/customer_portal/index.html';
echo "<p>" . $customerPortalPath . "</p>";

echo "<h2>File Exists Check:</h2>";
if (file_exists($customerPortalPath)) {
    echo "<p style='color: green;'>✅ Customer portal index.html exists</p>";
} else {
    echo "<p style='color: red;'>❌ Customer portal index.html not found</p>";
}

echo "<h2>Alternative Paths:</h2>";
$altPaths = [
    '../customer_portal/index.html',
    '../../customer_portal/index.html',
    './customer_portal/index.html',
    '/hotel_resto_system/src/customer_portal/index.html'
];

foreach ($altPaths as $path) {
    $fullPath = realpath(__DIR__ . '/' . $path);
    if ($fullPath) {
        echo "<p style='color: green;'>✅ $path → $fullPath</p>";
    } else {
        echo "<p style='color: red;'>❌ $path → Not found</p>";
    }
}

echo "<h2>Directory Contents:</h2>";
echo "<h3>Current Directory:</h3>";
echo "<pre>";
print_r(scandir(__DIR__));
echo "</pre>";

echo "<h3>Parent Directory:</h3>";
echo "<pre>";
print_r(scandir(dirname(__DIR__)));
echo "</pre>";
?>
