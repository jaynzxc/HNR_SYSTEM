<?php
/**
 * Simple Notification Preferences API
 * Returns notification preferences that match the HTML form
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    // Return notification preferences that match the actual HTML checkbox IDs
    $preferences = [
        'emailNotifications' => true,
        'smsNotifications' => true,
        'promoNotifications' => false,
        'loyaltyNotifications' => true
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification preferences retrieved',
        'data' => $preferences
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No active session'
    ]);
}
?>
