<?php
/**
 * Simple User API
 * Bypasses complex routing to get user data working
 */

session_start();
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No active session'
    ]);
}
?>
