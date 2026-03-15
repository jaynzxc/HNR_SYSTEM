<?php
// Direct test of the booking API with error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

try {
    echo json_encode(['status' => 'starting', 'step' => 'initialization']);
    
    // Test includes step by step
    require_once '../../config/database.php';
    echo json_encode(['status' => 'success', 'step' => 'database_config_loaded']);
    
    require_once '../../models/User.php';
    echo json_encode(['status' => 'success', 'step' => 'user_model_loaded']);
    
    require_once '../../models/SessionManager.php';
    echo json_encode(['status' => 'success', 'step' => 'session_manager_loaded']);
    
    require_once '../../helpers/api_helpers.php';
    echo json_encode(['status' => 'success', 'step' => 'api_helpers_loaded']);
    
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    echo json_encode(['status' => 'success', 'step' => 'database_connected']);
    
    // Test session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    echo json_encode(['status' => 'success', 'step' => 'session_started']);
    
    // Test session manager
    $sessionManager = new SessionManager($db);
    echo json_encode(['status' => 'success', 'step' => 'session_manager_created']);
    
    // Test user model
    $userModel = new User($db);
    echo json_encode(['status' => 'success', 'step' => 'user_model_created']);
    
    // Test get current user
    $currentUser = $sessionManager->getCurrentUser();
    echo json_encode(['status' => 'success', 'step' => 'current_user_retrieved', 'user' => $currentUser]);
    
    // Test getAvailableRooms function
    if (function_exists('getAvailableRooms')) {
        $rooms = getAvailableRooms($db);
        echo json_encode(['status' => 'success', 'step' => 'getAvailableRooms_works', 'rooms_count' => count($rooms)]);
    } else {
        echo json_encode(['status' => 'error', 'step' => 'getAvailableRooms_missing']);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'All tests passed!']);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
