<?php
/**
 * Session Debug API
 * Check session status and data
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    echo "Starting session...\n";
    session_start();
} else {
    echo "Session already active: " . session_id() . "\n";
}

header('Content-Type: application/json');

// Check session data
echo json_encode([
    'session_id' => session_id(),
    'session_status' => session_status(),
    'session_data' => $_SESSION,
    'cookie_data' => $_COOKIE,
    'post_data' => $_POST,
    'get_data' => $_GET
]);
?>
