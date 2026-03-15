<?php
/**
 * Debug Simple User API
 * Check if the simple user API is working correctly
 */

session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

echo json_encode([
    'step' => 'Debugging simple user API',
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'session_status' => session_status()
]);

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'step' => 'Session has user_id',
        'user_id_in_session' => $_SESSION['user_id']
    ]);
    
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'step' => 'User found in database',
            'user_data' => $user,
            'success' => true,
            'data' => $user
        ]);
    } else {
        echo json_encode([
            'step' => 'User not found in database',
            'user_id_searched' => $_SESSION['user_id'],
            'success' => false,
            'error' => 'User not found'
        ]);
    }
} else {
    echo json_encode([
        'step' => 'No user_id in session',
        'session_keys' => array_keys($_SESSION),
        'success' => false,
        'error' => 'No active session'
    ]);
}
?>
