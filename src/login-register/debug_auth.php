<?php
// Test user authentication and database
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Authentication Debug Test</h1>";

// Include required files
require_once '../../customer_portal/config/database.php';
require_once '../../customer_portal/models/User.php';
require_once '../../customer_portal/models/SessionManager.php';

try {
    // Initialize database
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    // Initialize models
    $userModel = new User($db);
    $sessionManager = new SessionManager($db);
    echo "<p style='color: green;'>✅ Models initialized</p>";
    
    // Test user lookup
    echo "<h2>Testing User Lookup:</h2>";
    
    $testEmails = [
        'roldantiu89@gmail.com',
        'mia.cruz@email.com',
        'admin@hotel.com',
        'customer@test.com'
    ];
    
    foreach ($testEmails as $email) {
        $user = $userModel->getUserByEmail($email);
        if ($user) {
            echo "<p style='color: green;'>✅ Found user: $email</p>";
            echo "<pre>";
            echo "User ID: " . $user['user_id'] . "\n";
            echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
            echo "Role: " . $user['user_role'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Status: " . $user['status'] . "\n";
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>❌ User not found: $email</p>";
        }
    }
    
    // Test authentication with known credentials
    echo "<h2>Testing Authentication:</h2>";
    
    $testCredentials = [
        ['email' => 'roldantiu89@gmail.com', 'password' => 'customer123'],
        ['email' => 'mia.cruz@email.com', 'password' => 'customer123'],
        ['email' => 'admin@hotel.com', 'password' => 'admin123']
    ];
    
    foreach ($testCredentials as $creds) {
        echo "<h3>Testing: {$creds['email']}</h3>";
        
        $user = $sessionManager->login($creds['email'], $creds['password']);
        if ($user) {
            echo "<p style='color: green;'>✅ Authentication successful</p>";
            echo "<pre>";
            echo "User ID: " . $user['user_id'] . "\n";
            echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
            echo "Role: " . $user['user_role'] . "\n";
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Authentication failed</p>";
        }
    }
    
    // Test session
    echo "<h2>Current Session:</h2>";
    $currentUser = $sessionManager->getCurrentUser();
    if ($currentUser) {
        echo "<p style='color: green;'>✅ Active session found</p>";
        echo "<pre>";
        echo "User ID: " . $currentUser['user_id'] . "\n";
        echo "Email: " . $currentUser['email'] . "\n";
        echo "Role: " . $currentUser['user_role'] . "\n";
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ No active session</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}
?>
