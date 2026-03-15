<?php
/**
 * Current User Debug API
 * Check if user session and data loading works
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
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // Get session manager and user
    require_once '../models/SessionManager.php';
    $sessionManager = new SessionManager($db);
    $currentUser = $sessionManager->getCurrentUser();
    
    echo json_encode([
        'success' => true,
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'current_user' => $currentUser,
        'user_exists' => !empty($currentUser),
        'database_connected' => true,
        'debug_info' => [
            'session_status' => session_status(),
            'user_id_in_session' => $_SESSION['user_id'] ?? 'not_set',
            'session_table_check' => 'Check user_sessions table for session_id ' . session_id()
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'current_user' => null,
        'debug_info' => [
            'error_trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
