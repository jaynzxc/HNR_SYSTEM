<?php
/**
 * Submit Review API
 * Handles review submissions from dashboard
 */

session_start();
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/SessionManager.php';

header('Content-Type: application/json');

// Check if user is logged in
$sessionManager = new SessionManager($database);
$currentUser = $sessionManager->getCurrentUser();

if (!$currentUser) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
}

// Initialize database and user model
$database = new Database();
$db = $database->getConnection();
$userModel = new User($database);

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['review_type']) || !isset($input['rating']) || !isset($input['review_text'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields'
        ]);
        exit;
    }
    
    if (empty($input['review_text'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Review text cannot be empty'
        ]);
        exit;
    }
    
    // Validate rating
    if ($input['rating'] < 1 || $input['rating'] > 5) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid rating'
        ]);
        exit;
    }
    
    try {
        // Add review to database
        $result = $userModel->addReview($currentUser['user_id'], [
            'review_type' => $input['review_type'],
            'rating' => $input['rating'],
            'review_text' => $input['review_text']
        ]);
        
        if ($result) {
            // Award loyalty points for review
            $pointsEarned = 50; // Fixed points for review
            $sql = "UPDATE users SET loyalty_points = loyalty_points + ? WHERE user_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$pointsEarned, $currentUser['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Review submitted successfully',
                'points_earned' => $pointsEarned
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to submit review'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}
?>
