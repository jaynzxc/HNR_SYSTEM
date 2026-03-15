<?php
/**
 * API Helper Functions
 * Common functions for API responses and data handling
 */

/**
 * Return JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Return error response
 */
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['error' => $message], $statusCode);
}

/**
 * Return success response
 */
function successResponse($message, $data = null) {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response);
}

/**
 * Validate required fields
 */
function validateRequired($data, $requiredFields) {
    $missing = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        errorResponse('Missing required fields: ' . implode(', ', $missing));
    }
    
    return true;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Philippines format)
 */
function validatePhone($phone) {
    // Remove all non-digit characters
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid PH number (10-11 digits, starts with 09 or has country code)
    return (strlen($cleaned) >= 10 && strlen($cleaned) <= 11 && 
            (substr($cleaned, 0, 2) === '09' || substr($cleaned, 0, 3) === '639'));
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = '₱') {
    return $currency . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate age from birth date
 */
function calculateAge($birthDate) {
    $today = new DateTime();
    $dob = new DateTime($birthDate);
    return $today->diff($dob)->y;
}

/**
 * Generate booking reference
 */
function generateBookingReference($prefix = 'HBK') {
    return $prefix . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

/**
 * Generate order reference
 */
function generateOrderReference($prefix = 'ORD') {
    return $prefix . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

/**
 * Generate reservation reference
 */
function generateReservationReference($prefix = 'RSV') {
    return $prefix . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

/**
 * Calculate loyalty points earned
 */
function calculateLoyaltyPoints($amount, $type = 'dining') {
    $rates = [
        'hotel_stay' => 5,      // 5 points per ₱100
        'dining' => 3,          // 3 points per ₱100
        'promo' => 1,           // 1 point per ₱100
        'signup_bonus' => 50,   // Fixed bonus
    ];
    
    $rate = $rates[$type] ?? 1;
    return intval(floor($amount / 100) * $rate);
}

/**
 * Get membership tier from points
 */
function getMembershipTier($points) {
    if ($points >= 5000) return 'platinum';
    if ($points >= 2000) return 'gold';
    if ($points >= 1000) return 'silver';
    return 'member';
}

/**
 * Get points needed for next tier
 */
function getPointsToNextTier($currentPoints) {
    $tiers = [
        'member' => 1000,
        'silver' => 2000,
        'gold' => 5000,
        'platinum' => null
    ];
    
    $currentTier = getMembershipTier($currentPoints);
    
    if ($currentTier === 'platinum') {
        return 0; // Already at highest tier
    }
    
    $nextTierPoints = $tiers[$currentTier];
    return max(0, $nextTierPoints - $currentPoints);
}

/**
 * Send notification to user
 */
function sendNotification($db, $userId, $type, $title, $message, $actionUrl = null, $relatedEntityType = null, $relatedEntityId = null) {
    $stmt = $db->prepare("INSERT INTO notifications 
                        (user_id, notification_type, title, message, action_url, related_entity_type, related_entity_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $userId,
        $type,
        $title,
        $message,
        $actionUrl,
        $relatedEntityType,
        $relatedEntityId
    ]);
}

/**
 * Log user activity
 */
function logActivity($db, $userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    $stmt = $db->prepare("INSERT INTO audit_log 
                        (user_id, action_type, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $userId,
        $action,
        $tableName,
        $recordId,
        $oldValues ? json_encode($oldValues) : null,
        $newValues ? json_encode($newValues) : null,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Get user initials from name
 */
function getUserInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

/**
 * Check if user has sufficient points
 */
function hasSufficientPoints($db, $userId, $requiredPoints) {
    $stmt = $db->prepare("SELECT loyalty_points FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    return $user && $user['loyalty_points'] >= $requiredPoints;
}

/**
 * Deduct points from user
 */
function deductPoints($db, $userId, $points, $description, $sourceType = 'reward_redemption', $sourceId = null) {
    $db->beginTransaction();
    
    try {
        // Get current points
        $stmt = $db->prepare("SELECT loyalty_points FROM users WHERE user_id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || $user['loyalty_points'] < $points) {
            $db->rollback();
            return false;
        }
        
        $newBalance = $user['loyalty_points'] - $points;
        
        // Update user points
        $stmt = $db->prepare("UPDATE users SET loyalty_points = ? WHERE user_id = ?");
        $stmt->execute([$newBalance, $userId]);
        
        // Add to points history
        $stmt = $db->prepare("INSERT INTO points_history 
                            (user_id, points_change, points_balance_after, transaction_type, source_type, source_id, description) 
                            VALUES (?, ?, ?, 'redeem', ?, ?, ?)");
        $stmt->execute([$userId, -$points, $newBalance, $sourceType, $sourceId, $description]);
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

/**
 * Add points to user
 */
function addPoints($db, $userId, $points, $description, $sourceType = 'earn', $sourceId = null) {
    $db->beginTransaction();
    
    try {
        // Get current points
        $stmt = $db->prepare("SELECT loyalty_points FROM users WHERE user_id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $currentPoints = $user['loyalty_points'] ?? 0;
        $newBalance = $currentPoints + $points;
        
        // Update user points
        $stmt = $db->prepare("UPDATE users SET loyalty_points = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$newBalance, $userId]);
        
        // Add to points history
        $stmt = $db->prepare("INSERT INTO points_history 
                            (user_id, points_change, points_balance_after, transaction_type, source_type, source_id, description) 
                            VALUES (?, ?, ?, 'earn', ?, ?, ?)");
        $stmt->execute([$userId, $points, $newBalance, $sourceType, $sourceId, $description]);
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

/**
 * Get menu items by category
 */
function getMenuItems($db, $categoryId = null) {
    $sql = "SELECT mi.*, mc.category_name 
            FROM menu_items mi 
            JOIN menu_categories mc ON mi.category_id = mc.category_id 
            WHERE mi.item_status = 'available'";
    
    $params = [];
    if ($categoryId) {
        $sql .= " AND mi.category_id = ?";
        $params[] = $categoryId;
    }
    
    $sql .= " ORDER BY mc.display_order, mi.item_name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get available hotel rooms
 */
function getAvailableRooms($db, $checkIn = null, $checkOut = null) {
    $sql = "SELECT * FROM hotel_rooms WHERE room_status = 'available'";
    
    $params = [];
    if ($checkIn && $checkOut) {
        $sql .= " AND room_id NOT IN (
                    SELECT DISTINCT room_id 
                    FROM hotel_bookings 
                    WHERE booking_status IN ('confirmed', 'checked_in')
                    AND (
                        (check_in_date <= ? AND check_out_date > ?) OR
                        (check_in_date < ? AND check_out_date >= ?)
                    )
                )";
        $params = [$checkOut, $checkIn, $checkOut, $checkIn];
    }
    
    $sql .= " ORDER BY base_price_per_night ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get available restaurant tables
 */
function getAvailableTables($db, $capacity = null) {
    $sql = "SELECT * FROM restaurant_tables WHERE table_status = 'available'";
    
    $params = [];
    if ($capacity) {
        $sql .= " AND max_capacity >= ?";
        $params[] = $capacity;
    }
    
    $sql .= " ORDER BY max_capacity, table_number";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
?>
