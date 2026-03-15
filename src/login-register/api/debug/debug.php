<?php
/**
 * Debug Authentication API
 * Test file to identify 500 error issues
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Show errors for debugging

// Start output buffering
ob_start();

require_once '../../customer_portal/config/database.php';
require_once '../../customer_portal/models/User.php';
require_once '../../customer_portal/models/SessionManager.php';
require_once '../../customer_portal/helpers/api_helpers.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

echo json_encode([
    'debug' => 'API loaded successfully',
    'method' => $_SERVER['REQUEST_METHOD'],
    'path' => $_SERVER['REQUEST_URI'],
    'post_data' => file_get_contents('php://input'),
    'files_included' => get_included_files()
]);

ob_end_flush();
?>
