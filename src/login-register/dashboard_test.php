<?php
/**
 * Dashboard Access Test
 * Test if we can access the customer portal
 */

session_start();

echo "<h1>Dashboard Access Test</h1>";

echo "<h2>Current Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Attempting to access dashboard...</h2>";

// Try to include the dashboard files
try {
    echo "<p>Attempting to load database config...</p>";
    require_once '../customer_portal/config/database.php';
    echo "<p>✅ Database config loaded</p>";
    
    echo "<p>Attempting to create database connection...</p>";
    $database = new Database();
    echo "<p>✅ Database connection created</p>";
    
    echo "<p>Attempting to load User model...</p>";
    require_once '../customer_portal/models/User.php';
    echo "<p>✅ User model loaded</p>";
    
    echo "<p>Attempting to load SessionManager...</p>";
    require_once '../customer_portal/models/SessionManager.php';
    echo "<p>✅ SessionManager loaded</p>";
    
    echo "<p>Attempting to create SessionManager...</p>";
    $sessionManager = new SessionManager($database);
    echo "<p>✅ SessionManager created</p>";
    
    echo "<p>Attempting to get current user...</p>";
    $currentUser = $sessionManager->getCurrentUser();
    echo "<p>✅ Current user retrieved</p>";
    
    echo "<h2>Current User Data:</h2>";
    echo "<pre>";
    print_r($currentUser);
    echo "</pre>";
    
    if ($currentUser) {
        echo "<p style='color: green;'>✅ User is authenticated!</p>";
        echo "<p><a href='../customer_portal/index.php'>Go to Dashboard</a></p>";
    } else {
        echo "<p style='color: red;'>❌ User is not authenticated!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Manual Links:</h2>";
echo "<p><a href='../customer_portal/index.php'>Direct to Dashboard</a></p>";
echo "<p><a href='login_form.php'>Back to Login</a></p>";
echo "<p><a href='debug.php'>Back to Debug</a></p>";
?>
