<?php
/**
 * Test Actual Login Form Call
 * Simulate the exact call the login form makes
 */

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../customer_portal/config/database.php';
require_once '../../customer_portal/models/User.php';
require_once '../../customer_portal/models/SessionManager.php';
require_once '../../customer_portal/helpers/api_helpers.php';

// Start output buffering
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

// Initialize session and database
try {
    // Start PHP session first
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager($db);
    $userModel = new User($db);
} catch (Exception $e) {
    ob_end_clean();
    jsonResponse(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
}

// Get current user
$currentUser = $sessionManager->getCurrentUser();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Get endpoint from query parameter or URL path
$endpoint = $_GET['endpoint'] ?? end($pathParts);

try {
    if ($method === 'POST' && $endpoint === 'login') {
        // Simulate the login request
        $input = json_decode(file_get_contents('php://input'), true);
        
        echo json_encode([
            'step' => 'Simulating login form request',
            'method' => $method,
            'endpoint' => $endpoint,
            'input_received' => $input,
            'raw_input' => file_get_contents('php://input'),
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
        ]);
        
        if ($input && isset($input['email']) && isset($input['password'])) {
            // Authenticate user
            $user = $sessionManager->login($input['email'], $input['password']);
            
            if ($user) {
                // Determine redirect based on role
                $redirectUrl = getRedirectUrl($user['user_role']);
                
                successResponse('Login successful', [
                    'user_id' => $user['user_id'],
                    'email' => $user['email'],
                    'user_role' => $user['user_role'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'membership_tier' => $user['membership_tier'],
                    'loyalty_points' => $user['loyalty_points'],
                    'redirect_to' => $redirectUrl
                ]);
            } else {
                errorResponse('Invalid email or password', 401);
            }
        } else {
            errorResponse('Missing required fields: email, password');
        }
    } else {
        errorResponse('Invalid request', 400);
    }
} catch (Exception $e) {
    ob_end_clean();
    errorResponse('Server error: ' . $e->getMessage(), 500);
}

// Clean output buffer and send response
ob_end_flush();

function getRedirectUrl($role) {
    $baseUrl = '/hotel_resto_system/src/customer_portal/';
    
    switch ($role) {
        case 'admin':
            return '/hotel_resto_system/src/admin_portal/dashboard.html';
        case 'restaurant_manager':
            return '/hotel_resto_system/src/restaurant_portal/dashboard.html';
        case 'hotel_manager':
            return '/hotel_resto_system/src/hotel_portal/dashboard.html';
        case 'customer':
        default:
            return $baseUrl . 'index.html';
    }
}
?>
