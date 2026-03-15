<?php
/**
 * Login API Endpoint
 * Returns JSON responses for AJAX login requests
 */

// Suppress all output except JSON
error_reporting(0);
ini_set('display_errors', 0);

// Configure session for cross-domain access
session_start();
ini_set('session.cookie_domain', '.localhost');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

require_once '../customer_portal/config/database.php';
require_once '../customer_portal/models/User.php';
require_once '../customer_portal/models/SessionManager.php';

header('Content-Type: application/json');

// Get JSON input
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// Debug: Log API call
error_log("Login API called - Method: " . $_SERVER['REQUEST_METHOD']);
error_log("JSON input: " . $json_input);
error_log("Decoded data: " . print_r($data, true));

// Initialize database and user model
try {
    $database = new Database();
    $db = $database->getConnection();
    $userModel = new User($database);
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$response = ['success' => false, 'message' => '', 'redirect' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    error_log("Login attempt - Email: $email");
    error_log("Login attempt - Password length: " . strlen($password));
    
    if (empty($email) || empty($password)) {
        $response['message'] = 'Please fill in all fields';
        error_log("Login failed: Empty fields - Email: '$email', Password: '$password'");
    } else {
        try {
            // Attempt login
            $user = $userModel->authenticateUser($email, $password);
            error_log("Authentication result: " . ($user ? 'success' : 'failed'));
            
            if ($user) {
                // Create session
                session_start(); // Ensure session is started
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                error_log("Session created for user ID: " . $user['user_id']);
                error_log("Session data: " . print_r($_SESSION, true));
                
                // Update last login
                $userModel->updateLastLogin($user['user_id']);
                
                $response['success'] = true;
                $response['message'] = 'Login successful! Welcome back!';
                // Use absolute URL for redirect
                $redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . '/HOTEL_RESTO_SYSTEM/src/customer_portal/index.php';
                $response['redirect'] = $redirect_url;
                error_log("Login successful, redirect URL: " . $redirect_url);
            } else {
                $response['message'] = 'Invalid email or password';
                error_log("Login failed: Invalid credentials");
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $response['message'] = 'Login failed: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request method';
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
}

// Clean output buffer to prevent HTML contamination
ob_clean();
echo json_encode($response);
?>
