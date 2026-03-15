<?php
/**
 * User API Endpoints
 * Handles all user-related API requests
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/SessionManager.php';
require_once '../helpers/api_helpers.php';

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
$pathParts = explode('/', trim($path, '/');

// Get endpoint from query parameter or URL path
$endpoint = $_GET['endpoint'] ?? end($pathParts);

try {
    switch ($endpoint) {
        case 'profile':
            handleProfile($method, $userModel, $currentUser);
            break;
            
        case 'update-profile':
            handleUpdateProfile($method, $userModel, $currentUser);
            break;
            
        case 'notifications':
            handleNotifications($method, $userModel, $currentUser);
            break;
            
        case 'mark-notification-read':
            handleMarkNotificationRead($method, $userModel, $currentUser);
            break;
            
        case 'payment-methods':
            handlePaymentMethods($method, $userModel, $currentUser);
            break;
            
        case 'add-payment-method':
            handleAddPaymentMethod($method, $userModel, $currentUser);
            break;
            
        case 'bookings':
            handleBookings($method, $userModel, $currentUser);
            break;
            
        case 'reservations':
            handleReservations($method, $userModel, $currentUser);
            break;
            
        case 'orders':
            handleOrders($method, $userModel, $currentUser);
            break;
            
        case 'transactions':
            handleTransactions($method, $userModel, $currentUser);
            break;
            
        case 'loyalty-rewards':
            handleLoyaltyRewards($method, $userModel, $currentUser);
            break;
            
        case 'redeem-reward':
            handleRedeemReward($method, $userModel, $currentUser);
            break;
            
        case 'points-history':
            handlePointsHistory($method, $userModel, $currentUser);
            break;
            
        case 'reviews':
            handleReviews($method, $userModel, $currentUser);
            break;
            
        case 'add-review':
            handleAddReview($method, $userModel, $currentUser);
            break;
            
        case 'notification-preferences':
            handleNotificationPreferences($method, $userModel, $currentUser);
            break;
            
        case 'change-password':
            handleChangePassword($method, $userModel, $currentUser);
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
 * Get user profile
 */
function handleProfile($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $profileData = [
        'user_id' => $currentUser['user_id'],
        'first_name' => $currentUser['first_name'],
        'last_name' => $currentUser['last_name'],
        'full_name' => $currentUser['first_name'] . ' ' . $currentUser['last_name'],
        'initials' => getUserInitials($currentUser['first_name'], $currentUser['last_name']),
        'email' => $currentUser['email'],
        'phone' => $currentUser['phone'],
        'alternative_phone' => $currentUser['alternative_phone'],
        'date_of_birth' => $currentUser['date_of_birth'],
        'gender' => $currentUser['gender'],
        'nationality' => $currentUser['nationality'],
        'street_address' => $currentUser['street_address'],
        'city' => $currentUser['city'],
        'postal_code' => $currentUser['postal_code'],
        'country' => $currentUser['country'],
        'preferred_language' => $currentUser['preferred_language'],
        'membership_tier' => $currentUser['membership_tier'],
        'loyalty_points' => $currentUser['loyalty_points'],
        'join_date' => $currentUser['join_date'],
        'email_verified' => $currentUser['email_verified'],
        'phone_verified' => $currentUser['phone_verified'],
        'profile_photo_url' => $currentUser['profile_photo_url']
    ];
    
    successResponse('Profile retrieved successfully', $profileData);
}

/**
 * Update user profile
 */
function handleUpdateProfile($method, $userModel, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['first_name', 'last_name', 'email', 'phone']);
    
    if (!validateEmail($input['email'])) {
        errorResponse('Invalid email format');
    }
    
    if (!validatePhone($input['phone'])) {
        errorResponse('Invalid phone format');
    }
    
    // Check if email is already taken by another user
    $existingUser = $userModel->getUserByEmail($input['email']);
    if ($existingUser && $existingUser['user_id'] != $currentUser['user_id']) {
        errorResponse('Email already exists');
    }
    
    $result = $userModel->updateProfile($currentUser['user_id'], $input);
    
    if ($result) {
        // Log activity
        global $db;
        logActivity($db, $currentUser['user_id'], 'update_profile', 'users', $currentUser['user_id'], $currentUser, $input);
        
        successResponse('Profile updated successfully');
    } else {
        errorResponse('Failed to update profile');
    }
}

/**
 * Get notifications
 */
function handleNotifications($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $unreadOnly = isset($_GET['unread']) && $_GET['unread'] === 'true';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    $notifications = $userModel->getNotifications($currentUser['user_id'], $unreadOnly, $limit);
    
    successResponse('Notifications retrieved successfully', $notifications);
}

/**
 * Mark notification as read
 */
function handleMarkNotificationRead($method, $userModel, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['notification_id']);
    
    $result = $userModel->markNotificationRead($input['notification_id'], $currentUser['user_id']);
    
    if ($result) {
        successResponse('Notification marked as read');
    } else {
        errorResponse('Failed to mark notification as read');
    }
}

/**
 * Get payment methods
 */
function handlePaymentMethods($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $paymentMethods = $userModel->getPaymentMethods($currentUser['user_id']);
    
    successResponse('Payment methods retrieved successfully', $paymentMethods);
}

/**
 * Add payment method
 */
function handleAddPaymentMethod($method, $userModel, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['method_type', 'provider_name']);
    
    $result = $userModel->addPaymentMethod($currentUser['user_id'], $input);
    
    if ($result) {
        successResponse('Payment method added successfully');
    } else {
        errorResponse('Failed to add payment method');
    }
}

/**
 * Get hotel bookings
 */
function handleBookings($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $bookings = $userModel->getHotelBookings($currentUser['user_id'], $limit);
    
    successResponse('Bookings retrieved successfully', $bookings);
}

/**
 * Get restaurant reservations
 */
function handleReservations($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $reservations = $userModel->getRestaurantReservations($currentUser['user_id'], $limit);
    
    successResponse('Reservations retrieved successfully', $reservations);
}

/**
 * Get food orders
 */
function handleOrders($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $orders = $userModel->getFoodOrders($currentUser['user_id'], $limit);
    
    successResponse('Orders retrieved successfully', $orders);
}

/**
 * Get transactions
 */
function handleTransactions($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $transactions = $userModel->getTransactions($currentUser['user_id'], $limit);
    
    successResponse('Transactions retrieved successfully', $transactions);
}

/**
 * Get loyalty rewards
 */
function handleLoyaltyRewards($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $rewards = $userModel->getLoyaltyRewards($currentUser['membership_tier']);
    
    successResponse('Loyalty rewards retrieved successfully', $rewards);
}

/**
 * Redeem reward
 */
function handleRedeemReward($method, $userModel, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['reward_id']);
    
    global $db;
    
    // Check if user has sufficient points
    $stmt = $db->prepare("SELECT * FROM loyalty_rewards WHERE reward_id = ? AND reward_status = 'available'");
    $stmt->execute([$input['reward_id']]);
    $reward = $stmt->fetch();
    
    if (!$reward) {
        errorResponse('Reward not available');
    }
    
    if ($currentUser['loyalty_points'] < $reward['points_cost']) {
        errorResponse('Insufficient points');
    }
    
    // Check usage limits
    $stmt = $db->prepare("SELECT COUNT(*) as usage_count FROM user_reward_redemptions WHERE user_id = ? AND reward_id = ? AND redemption_status = 'used'");
    $stmt->execute([$currentUser['user_id'], $input['reward_id']]);
    $usageCount = $stmt->fetch()['usage_count'];
    
    if ($reward['usage_limit_per_user'] && $usageCount >= $reward['usage_limit_per_user']) {
        errorResponse('Usage limit exceeded for this reward');
    }
    
    $db->beginTransaction();
    
    try {
        // Deduct points
        $newBalance = $currentUser['loyalty_points'] - $reward['points_cost'];
        $stmt = $db->prepare("UPDATE users SET loyalty_points = ? WHERE user_id = ?");
        $stmt->execute([$newBalance, $currentUser['user_id']]);
        
        // Add to points history
        $stmt = $db->prepare("INSERT INTO points_history 
                            (user_id, points_change, points_balance_after, transaction_type, source_type, source_id, description) 
                            VALUES (?, ?, ?, 'redeem', 'reward_redemption', ?, ?)");
        $stmt->execute([
            $currentUser['user_id'], 
            -$reward['points_cost'], 
            $newBalance, 
            $input['reward_id'], 
            "Redeemed reward: {$reward['reward_name']}"
        ]);
        
        // Create redemption record
        $redemptionReference = generateReservationReference('RDM');
        $stmt = $db->prepare("INSERT INTO user_reward_redemptions 
                            (user_id, reward_id, redemption_reference, points_used, redemption_status) 
                            VALUES (?, ?, ?, ?, 'confirmed')");
        $stmt->execute([
            $currentUser['user_id'], 
            $input['reward_id'], 
            $redemptionReference, 
            $reward['points_cost']
        ]);
        
        // Send notification
        sendNotification(
            $db, 
            $currentUser['user_id'], 
            'loyalty', 
            'Reward Redeemed!', 
            "You have successfully redeemed {$reward['reward_name']}. Reference: {$redemptionReference}",
            'loyalty_rewards.php',
            'loyalty_reward',
            $input['reward_id']
        );
        
        $db->commit();
        
        successResponse('Reward redeemed successfully', [
            'redemption_reference' => $redemptionReference,
            'points_used' => $reward['points_cost'],
            'new_balance' => $newBalance
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        errorResponse('Failed to redeem reward: ' . $e->getMessage());
    }
}

/**
 * Get points history
 */
function handlePointsHistory($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $history = $userModel->getPointsHistory($currentUser['user_id'], $limit);
    
    successResponse('Points history retrieved successfully', $history);
}

/**
 * Get user reviews
 */
function handleReviews($method, $userModel, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $reviews = $userModel->getUserReviews($currentUser['user_id'], $limit);
    
    successResponse('Reviews retrieved successfully', $reviews);
}

/**
 * Add review
 */
function handleAddReview($method, $userModel, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    validateRequired($input, ['review_type', 'rating']);
    
    if ($input['rating'] < 1 || $input['rating'] > 5) {
        errorResponse('Rating must be between 1 and 5');
    }
    
    $result = $userModel->addReview($currentUser['user_id'], $input);
    
    if ($result) {
        // Award points for review
        global $db;
        $pointsEarned = calculateLoyaltyPoints(0, 'review'); // Fixed points for review
        addPoints($db, $currentUser['user_id'], $pointsEarned, 'Points earned for writing a review', 'review');
        
        successResponse('Review added successfully');
    } else {
        errorResponse('Failed to add review');
    }
}

/**
 * Get/update notification preferences
 */
function handleNotificationPreferences($method, $userModel, $currentUser) {
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    if ($method === 'GET') {
        $preferences = $userModel->getNotificationPreferences($currentUser['user_id']);
        successResponse('Notification preferences retrieved successfully', $preferences);
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = $userModel->updateNotificationPreferences($currentUser['user_id'], $input);
        
        if ($result) {
            successResponse('Notification preferences updated successfully');
        } else {
            errorResponse('Failed to update notification preferences');
        }
    } else {
        errorResponse('Method not allowed', 405);
    }
}

/**
 * Handle password change
 */
function handleChangePassword($method, $userModel, $currentUser) {
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['current_password', 'new_password']);
    
    // Verify current password
    if (!$userModel->verifyPassword($currentUser['email'], $input['current_password'])) {
        errorResponse('Current password is incorrect', 401);
    }
    
    // Validate new password
    if (strlen($input['new_password']) < 8) {
        errorResponse('Password must be at least 8 characters long');
    }
    
    if (!preg_match('/[A-Z]/', $input['new_password'])) {
        errorResponse('Password must contain at least one uppercase letter');
    }
    
    if (!preg_match('/[0-9]/', $input['new_password'])) {
        errorResponse('Password must contain at least one number');
    }
    
    // Update password
    $result = $userModel->updatePassword($currentUser['user_id'], $input['new_password']);
    
    if ($result) {
        successResponse('Password changed successfully');
    } else {
        errorResponse('Failed to change password');
    }
}
?>
