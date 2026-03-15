<?php
// Test if helper functions are loaded
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    require_once '../../config/database.php';
    require_once '../../models/User.php';
    require_once '../../models/SessionManager.php';
    require_once '../../helpers/api_helpers.php';
    
    echo json_encode(['success' => true, 'step' => 'includes_loaded']);
    
    // Test if helper functions exist
    if (function_exists('jsonResponse')) {
        echo json_encode(['success' => true, 'step' => 'jsonResponse_exists']);
    } else {
        echo json_encode(['success' => false, 'error' => 'jsonResponse function not found']);
    }
    
    if (function_exists('errorResponse')) {
        echo json_encode(['success' => true, 'step' => 'errorResponse_exists']);
    } else {
        echo json_encode(['success' => false, 'error' => 'errorResponse function not found']);
    }
    
    if (function_exists('successResponse')) {
        echo json_encode(['success' => true, 'step' => 'successResponse_exists']);
    } else {
        echo json_encode(['success' => false, 'error' => 'successResponse function not found']);
    }
    
    if (function_exists('getAvailableTables')) {
        echo json_encode(['success' => true, 'step' => 'getAvailableTables_exists']);
    } else {
        echo json_encode(['success' => false, 'error' => 'getAvailableTables function not found']);
    }
    
    if (function_exists('getMenuItems')) {
        echo json_encode(['success' => true, 'step' => 'getMenuItems_exists']);
    } else {
        echo json_encode(['success' => false, 'error' => 'getMenuItems function not found']);
    }
    
    if (function_exists('validateRequired')) {
        echo json_encode(['success' => true, 'step' => 'validateRequired_exists']);
    } else {
        echo json_encode(['success' => false, 'error' => 'validateRequired function not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>
