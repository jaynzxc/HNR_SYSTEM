<?php
// Simple test to isolate the booking.php issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Test basic PHP functionality
    echo json_encode(['success' => true, 'message' => 'Basic PHP working']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
