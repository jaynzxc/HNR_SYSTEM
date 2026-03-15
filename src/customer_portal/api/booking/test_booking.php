<?php
// Test the exact booking.php logic
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Test the exact same includes as booking.php
    require_once '../../config/database.php';
    require_once '../../models/User.php';
    require_once '../../models/SessionManager.php';
    require_once '../../helpers/api_helpers.php';
    
    echo json_encode(['success' => true, 'step' => 'includes_loaded']);
    
    // Test session start
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    echo json_encode(['success' => true, 'step' => 'session_started']);
    
    // Test database initialization
    $database = new Database();
    $db = $database->getConnection();
    echo json_encode(['success' => true, 'step' => 'database_connected']);
    
    // Test session manager
    $sessionManager = new SessionManager($db);
    echo json_encode(['success' => true, 'step' => 'session_manager_created']);
    
    // Test get current user
    $currentUser = $sessionManager->getCurrentUser();
    echo json_encode(['success' => true, 'step' => 'user_retrieved', 'user' => $currentUser]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>
