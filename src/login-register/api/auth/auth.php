<?php
/**
 * Authentication API
 * Handles user registration, login, and role-based routing
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

require_once '../../../customer_portal/config/database.php';
require_once '../../../customer_portal/models/User.php';
require_once '../../../customer_portal/models/SessionManager.php';
require_once '../../../customer_portal/helpers/api_helpers.php';

// Start output buffering to catch any warnings/errors
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

// Initialize database and session manager
try {
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager($db);
    $userModel = new User($db);
} catch (Exception $e) {
    ob_end_clean();
    jsonResponse(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Get endpoint (last part of path)
$endpoint = end($pathParts);

try {
    switch ($endpoint) {
        case 'register':
            handleRegister($method, $userModel);
            break;
            
        case 'login':
            handleLogin($method, $sessionManager, $userModel);
            break;
            
        case 'logout':
            handleLogout($method, $sessionManager);
            break;
            
        case 'check-session':
            handleCheckSession($method, $sessionManager);
            break;
            
        default:
            ob_end_clean();
            errorResponse('Endpoint not found', 404);
    }
} catch (Exception $e) {
    ob_end_clean();
    errorResponse('Server error: ' . $e->getMessage(), 500);
}

// Clean output buffer and send response
ob_end_flush();

/**
 * Handle user registration
 */
function handleRegister($method, $userModel) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['first_name', 'last_name', 'email', 'password', 'confirm_password']);
    
    // Validate passwords match
    if ($input['password'] !== $input['confirm_password']) {
        errorResponse('Passwords do not match');
    }
    
    // Validate email
    if (!validateEmail($input['email'])) {
        errorResponse('Invalid email format');
    }
    
    // Validate password strength
    if (strlen($input['password']) < 8) {
        errorResponse('Password must be at least 8 characters long');
    }
    
    // Check if email already exists
    $existingUser = $userModel->getUserByEmail($input['email']);
    if ($existingUser) {
        errorResponse('Email already registered');
    }
    
    // Hash password
    $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Create user data
    $userData = [
        'first_name' => sanitizeInput($input['first_name']),
        'last_name' => sanitizeInput($input['last_name']),
        'email' => sanitizeInput($input['email']),
        'password_hash' => $passwordHash,
        'phone' => sanitizeInput($input['phone'] ?? ''),
        'user_role' => 'customer', // All registrations are customers by default
        'email_verified' => false,
        'phone_verified' => false,
        'membership_tier' => 'member',
        'loyalty_points' => 50, // Welcome bonus points
    ];
    
    // Add optional fields
    if (!empty($input['date_of_birth'])) {
        $userData['date_of_birth'] = $input['date_of_birth'];
    }
    if (!empty($input['gender'])) {
        $userData['gender'] = $input['gender'];
    }
    if (!empty($input['nationality'])) {
        $userData['nationality'] = sanitizeInput($input['nationality']);
    }
    if (!empty($input['street_address'])) {
        $userData['street_address'] = sanitizeInput($input['street_address']);
    }
    if (!empty($input['city'])) {
        $userData['city'] = sanitizeInput($input['city']);
    }
    if (!empty($input['postal_code'])) {
        $userData['postal_code'] = sanitizeInput($input['postal_code']);
    }
    if (!empty($input['country'])) {
        $userData['country'] = sanitizeInput($input['country']);
    }
    
    // Create user
    $result = $userModel->createUser($userData);
    
    if ($result) {
        // Get the created user
        $newUser = $userModel->getUserByEmail($input['email']);
        
        // Add welcome bonus to points history
        global $db;
        $stmt = $db->prepare("INSERT INTO points_history 
                              (user_id, points_change, points_balance_after, transaction_type, source_type, description) 
                              VALUES (?, ?, ?, 'earn', 'signup_bonus', ?)");
        $stmt->execute([
            $newUser['user_id'],
            50,
            50,
            'Welcome bonus points for joining'
        ]);
        
        successResponse('Registration successful', [
            'user_id' => $newUser['user_id'],
            'email' => $newUser['email'],
            'user_role' => $newUser['user_role'],
            'redirect_to' => '../customer_portal/index.html'
        ]);
    } else {
        errorResponse('Registration failed');
    }
}

/**
 * Handle user login
 */
function handleLogin($method, $sessionManager, $userModel) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['email', 'password']);
    
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
}

/**
 * Handle user logout
 */
function handleLogout($method, $sessionManager) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $result = $sessionManager->logout();
    
    if ($result) {
        successResponse('Logout successful', [
            'redirect_to' => '/hotel_resto_system/src/login-register/login_form.html'
        ]);
    } else {
        errorResponse('Logout failed');
    }
}

/**
 * Handle session check
 */
function handleCheckSession($method, $sessionManager) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $currentUser = $sessionManager->getCurrentUser();
    
    if ($currentUser) {
        successResponse('Session active', [
            'user_id' => $currentUser['user_id'],
            'email' => $currentUser['email'],
            'user_role' => $currentUser['user_role'],
            'first_name' => $currentUser['first_name'],
            'last_name' => $currentUser['last_name'],
            'membership_tier' => $currentUser['membership_tier'],
            'loyalty_points' => $currentUser['loyalty_points']
        ]);
    } else {
        errorResponse('No active session', 401);
    }
}

/**
 * Get redirect URL based on user role
 */
function getRedirectUrl($role) {
    $baseUrl = '../customer_portal/';
    
    switch ($role) {
        case 'admin':
            return '../admin_portal/dashboard.html';
        case 'restaurant_manager':
            return '../restaurant_portal/dashboard.html';
        case 'hotel_manager':
            return '../hotel_portal/dashboard.html';
        case 'customer':
        default:
            return $baseUrl . 'index.html';
    }
}
?>
