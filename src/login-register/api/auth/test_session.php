<?php
// Test the exact auth.php check-session endpoint logic
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Test the exact same includes as auth.php
    require_once '../../../customer_portal/config/database.php';
    require_once '../../../customer_portal/models/User.php';
    require_once '../../../customer_portal/models/SessionManager.php';
    require_once '../../../customer_portal/helpers/api_helpers.php';
    
    echo json_encode(['success' => true, 'step' => 'includes_loaded']);
    
    // Test database initialization
    $database = new Database();
    $db = $database->getConnection();
    echo json_encode(['success' => true, 'step' => 'database_connected']);
    
    // Test session manager
    $sessionManager = new SessionManager($db);
    echo json_encode(['success' => true, 'step' => 'session_manager_created']);
    
    // Test session check
    $currentUser = $sessionManager->getCurrentUser();
    echo json_encode(['success' => true, 'step' => 'session_checked', 'user' => $currentUser]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>
