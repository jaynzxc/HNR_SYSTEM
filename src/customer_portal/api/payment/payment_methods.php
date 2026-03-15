<?php
/**
 * Payment Methods API
 * Returns user payment methods
 */

session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    // Return empty payment methods array for now
    $paymentMethods = [];
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment methods retrieved',
        'data' => $paymentMethods
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No active session'
    ]);
}
?>
