<?php
/**
 * Test Login API Directly
 * Test what the login API actually returns
 */

require_once '../../customer_portal/config/database.php';
require_once '../../customer_portal/models/User.php';
require_once '../../customer_portal/models/SessionManager.php';
require_once '../../customer_portal/helpers/api_helpers.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Test the actual login endpoint
try {
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager($db);
    $userModel = new User($db);
    
    // Simulate POST request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Test login data
    $input = [
        'email' => 'mia.cruz@email.com',
        'password' => 'customer123'
    ];
    
    echo json_encode([
        'step' => 'Testing login API',
        'input_data' => $input
    ]);
    
    // Authenticate user
    $user = $sessionManager->login($input['email'], $input['password']);
    
    if ($user) {
        // Determine redirect based on role
        $redirectUrl = getRedirectUrl($user['user_role']);
        
        $response = [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'user_role' => $user['user_role'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'membership_tier' => $user['membership_tier'],
                'loyalty_points' => $user['loyalty_points'],
                'redirect_to' => $redirectUrl
            ]
        ];
        
        echo json_encode([
            'step' => 'Login successful',
            'user_data' => $user,
            'redirect_url' => $redirectUrl,
            'full_response' => $response
        ]);
        
    } else {
        echo json_encode([
            'step' => 'Login failed',
            'error' => 'Invalid email or password'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'step' => 'Error occurred',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

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
