<?php
/**
 * Database Debug API
 * Test database connection and user data
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    // Test database connection
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'database_connected' => true
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'database_connected' => false
    ]);
}
?>
