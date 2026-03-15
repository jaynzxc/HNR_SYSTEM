<?php
/**
 * Debug Login Response
 * Check what the login API is actually returning
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

// Test login with debug info
try {
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager($db);
    $userModel = new User($db);
    
    // Test with a known user
    $testEmail = 'mia.cruz@email.com';
    $testPassword = 'customer123';
    
    echo json_encode([
        'step' => 'Starting login test',
        'email' => $testEmail,
        'user_found' => false,
        'login_result' => null,
        'response_data' => null
    ]);
    
    // Check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND account_status = 'active'");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($testPassword, $user['password_hash'])) {
        echo json_encode([
            'step' => 'User authenticated',
            'user_found' => true,
            'user_data' => $user,
            'user_role' => $user['user_role']
        ]);
        
        // Test login function
        $loginResult = $sessionManager->login($testEmail, $testPassword);
        
        if ($loginResult) {
            echo json_encode([
                'step' => 'Login successful',
                'login_result' => $loginResult,
                'redirect_url' => getRedirectUrl($loginResult['user_role'])
            ]);
            
            // Test full response
            $response = [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $loginResult['user_id'],
                    'email' => $loginResult['email'],
                    'user_role' => $loginResult['user_role'],
                    'first_name' => $loginResult['first_name'],
                    'last_name' => $loginResult['last_name'],
                    'membership_tier' => $loginResult['membership_tier'],
                    'loyalty_points' => $loginResult['loyalty_points'],
                    'redirect_to' => getRedirectUrl($loginResult['user_role'])
                ]
            ];
            
            echo json_encode([
                'step' => 'Full response prepared',
                'response_data' => $response
            ]);
        } else {
            echo json_encode([
                'step' => 'Login failed',
                'login_result' => false
            ]);
        }
    } else {
        echo json_encode([
            'step' => 'User not found or password incorrect',
            'user_found' => false
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'step' => 'Error occurred',
        'error' => $e->getMessage()
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
