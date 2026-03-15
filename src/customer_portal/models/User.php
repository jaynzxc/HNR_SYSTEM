<?php
/**
 * User Model
 * Handles all user-related database operations
 */

class User {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ? AND account_status = 'active'");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND account_status = 'active'");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Get user by email and password (for login)
     */
    public function getUserByEmailAndPassword($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND account_status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     */
    public function updateUser($userId, $data) {
        $sql = "UPDATE users SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, alternative_phone = ?,
                date_of_birth = ?, gender = ?, nationality = ?, street_address = ?,
                city = ?, postal_code = ?, country = ?, preferred_language = ?,
                updated_at = NOW()
                WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['alternative_phone'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['nationality'] ?? null,
            $data['street_address'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? null,
            $data['preferred_language'] ?? 'English',
            $userId
        ]);
    }
    
    /**
     * Update loyalty points
     */
    public function updateLoyaltyPoints($userId, $points) {
        $stmt = $this->db->prepare("UPDATE users SET loyalty_points = ?, updated_at = NOW() WHERE user_id = ?");
        return $stmt->execute([$points, $userId]);
    }
    
    /**
     * Get user's notification preferences
     */
    public function getNotificationPreferences($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_notification_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $preferences = [];
        
        while ($row = $stmt->fetch()) {
            $preferences[$row['notification_category']] = $row;
        }
        
        return $preferences;
    }
    
    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences($userId, $preferences) {
        $sql = "INSERT INTO user_notification_preferences 
                (user_id, notification_category, email_enabled, sms_enabled, in_app_enabled, frequency_preference)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                email_enabled = VALUES(email_enabled),
                sms_enabled = VALUES(sms_enabled),
                in_app_enabled = VALUES(in_app_enabled),
                frequency_preference = VALUES(frequency_preference),
                updated_at = NOW()";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($preferences as $category => $pref) {
            $stmt->execute([
                $userId,
                $category,
                $pref['email_enabled'] ?? true,
                $pref['sms_enabled'] ?? true,
                $pref['in_app_enabled'] ?? true,
                $pref['frequency_preference'] ?? 'immediate'
            ]);
        }
        
        return true;
    }
    
    /**
     * Get user's payment methods
     */
    public function getPaymentMethods($userId) {
        $stmt = $this->db->prepare("SELECT * FROM payment_methods WHERE user_id = ? AND is_active = TRUE ORDER BY is_default DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's hotel bookings
     */
    public function getHotelBookings($userId, $limit = 10) {
        $sql = "SELECT hb.*, hr.room_number, hr.room_type 
                FROM hotel_bookings hb
                JOIN hotel_rooms hr ON hb.room_id = hr.room_id
                WHERE hb.user_id = ?
                ORDER BY hb.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's restaurant reservations
     */
    public function getRestaurantReservations($userId, $limit = 10) {
        $sql = "SELECT rr.*, rt.table_number 
                FROM restaurant_reservations rr
                LEFT JOIN restaurant_tables rt ON rr.table_id = rt.table_id
                WHERE rr.user_id = ?
                ORDER BY rr.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's transactions
     */
    public function getTransactions($userId, $limit = 20) {
        $sql = "SELECT t.*, pm.method_type, pm.method_nickname
                FROM transactions t
                LEFT JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's notifications
     */
    public function getNotifications($userId, $unreadOnly = false, $limit = 50) {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND is_read = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadNotificationCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get user's points history
     */
    public function getPointsHistory($userId, $limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM points_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get available loyalty rewards
     */
    public function getLoyaltyRewards($userTier = 'member') {
        $sql = "SELECT * FROM loyalty_rewards 
                WHERE reward_status = 'available' 
                AND (tier_requirement = ? OR tier_requirement = 'member')
                AND (valid_until IS NULL OR valid_until >= CURDATE())
                ORDER BY points_cost ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userTier]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's reward redemptions
     */
    public function getRewardRedemptions($userId, $limit = 10) {
        $sql = "SELECT urr.*, lr.reward_name, lr.reward_type
                FROM user_reward_redemptions urr
                JOIN loyalty_rewards lr ON urr.reward_id = lr.reward_id
                WHERE urr.user_id = ?
                ORDER BY urr.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add user review
     */
    public function addReview($userId, $data) {
        $sql = "INSERT INTO user_reviews 
                (user_id, review_type, related_entity_id, rating, review_title, review_text)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $data['review_type'],
            $data['related_entity_id'] ?? null,
            $data['rating'],
            $data['review_title'] ?? null,
            $data['review_text'] ?? null
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                date_of_birth = ?, 
                gender = ?, 
                nationality = ?, 
                alternative_phone = ?, 
                street_address = ?, 
                city = ?, 
                postal_code = ?, 
                country = ?, 
                preferred_language = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['nationality'] ?? null,
            $data['alternative_phone'] ?? null,
            $data['street_address'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? null,
            $data['preferred_language'] ?? null,
            $userId
        ]);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$hashedPassword, $userId]);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($email, $password) {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
        return false;
    }
    
    /**
     * Get user bookings
     */
    public function getUserBookings($userId, $limit = 10) {
        $sql = "SELECT hb.*, hr.room_number, hr.room_type 
                  FROM hotel_bookings hb 
                  LEFT JOIN hotel_rooms hr ON hb.room_id = hr.room_id 
                  WHERE hb.user_id = ? 
                  ORDER BY hb.created_at DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user reservations
     */
    public function getUserReservations($userId, $limit = 10) {
        $sql = "SELECT * FROM restaurant_reservations 
                  WHERE user_id = ? 
                  ORDER BY reservation_date DESC, reservation_time DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $limit = 10) {
        $sql = "SELECT * FROM food_orders 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10) {
        $sql = "SELECT * FROM notifications 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get menu items
     */
    public function getMenuItems($limit = 10) {
        $sql = "SELECT mi.item_id, mi.item_name, mi.item_description, mi.price, mi.item_status, 
                       mi.preparation_time_minutes, mi.spicy_level, mi.is_signature, mi.image_url,
                       mc.category_name as category 
                FROM menu_items mi 
                LEFT JOIN menu_categories mc ON mi.category_id = mc.category_id 
                WHERE mi.item_status = 'available'
                ORDER BY mi.item_name ASC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available rooms
     */
    public function getAvailableRooms($limit = 10) {
        $sql = "SELECT * FROM hotel_rooms ORDER BY room_number ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user payment methods
     */
    public function getUserPaymentMethods($userId) {
        $sql = "SELECT * FROM payment_methods WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create hotel booking
     */
    public function createHotelBooking($userId, $data) {
        // Generate booking reference
        $bookingReference = 'HB' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        
        // Calculate total amount (room price * nights)
        $checkIn = new DateTime($data['check_in_date']);
        $checkOut = new DateTime($data['check_out_date']);
        $nights = $checkIn->diff($checkOut)->days;
        
        // Get room price (simplified - in real app, you'd query room table)
        $roomPrice = $this->getRoomPrice($data['room_id']);
        $totalAmount = $roomPrice * $nights;
        
        $sql = "INSERT INTO hotel_bookings (user_id, room_id, booking_reference, check_in_date, check_out_date, number_of_guests, total_amount, deposit_amount, booking_status, payment_status, special_requests, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'pending', ?, CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            $userId,
            $data['room_id'],
            $bookingReference,
            $data['check_in_date'],
            $data['check_out_date'],
            $data['number_of_guests'],
            $totalAmount,
            0.00,
            $data['special_requests']
        ])) {
            // Store booking reference in data array for return
            $data['booking_reference'] = $bookingReference;
            return true;
        }
        return false;
    }
    
    /**
     * Get room price (helper method)
     */
    private function getRoomPrice($roomId) {
        $sql = "SELECT base_price_per_night FROM hotel_rooms WHERE room_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        return $room ? $room['base_price_per_night'] : 0;
    }
    
    /**
     * Get recent booking for confirmation page
     */
    public function getRecentBooking($userId) {
        $sql = "SELECT hb.*, hr.room_type, hr.base_price_per_night 
                FROM hotel_bookings hb 
                JOIN hotel_rooms hr ON hb.room_id = hr.room_id 
                WHERE hb.user_id = ? 
                ORDER BY hb.booking_id DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Authenticate user
     */
    public function authenticateUser($email, $password) {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }
    
    /**
     * Create user
     */
    public function createUser($data) {
        // Debug: Log the data being received
        error_log("User data received: " . print_r($data, true));
        
        $sql = "INSERT INTO users (first_name, last_name, email, phone, alternative_phone, date_of_birth, gender, nationality, street_address, city, postal_code, country, password_hash, user_role, membership_tier, loyalty_points, account_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['alternative_phone'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['nationality'] ?? null,
            $data['street_address'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? null,
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['user_role'] ?? 'customer',
            $data['membership_tier'] ?? 'Basic',
            $data['loyalty_points'] ?? 0
        ]);
        
        // Debug: Log the result
        error_log("Create user result: " . ($result ? 'success' : 'failed'));
        
        return $result;
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Get available rewards
     */
    public function getAvailableRewards() {
        $sql = "SELECT * FROM loyalty_rewards WHERE is_active = 1 ORDER BY points_cost ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user redemptions
     */
    public function getUserRedemptions($userId, $limit = 10) {
        $sql = "SELECT lr.*, r.reward_name, r.reward_description 
                FROM loyalty_redemptions lr 
                JOIN loyalty_rewards r ON lr.reward_id = r.reward_id 
                WHERE lr.user_id = ? 
                ORDER BY lr.redemption_date DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get tier benefits
     */
    public function getTierBenefits($tier) {
        $benefits = [
            'member' => [
                '1 point per ₱100 spent',
                'Birthday recognition',
                'Exclusive member rates'
            ],
            'silver' => [
                '1.5 points per ₱100 spent',
                'Birthday recognition + 100 points',
                'Exclusive member rates',
                'Priority check-in',
                'Welcome drink'
            ],
            'gold' => [
                '2 points per ₱100 spent',
                'Birthday recognition + 200 points',
                'Exclusive member rates',
                'Priority check-in',
                'Welcome drink + appetizer',
                'Room upgrade (subject to availability)',
                'Late checkout'
            ],
            'platinum' => [
                '3 points per ₱100 spent',
                'Birthday recognition + 500 points',
                'Exclusive member rates',
                'Priority check-in',
                'Welcome drink + appetizer + dessert',
                'Guaranteed room upgrade',
                'Late checkout + early check-in',
                'Companion card',
                'Personal concierge'
            ]
        ];
        return $benefits[$tier] ?? $benefits['member'];
    }
    
    /**
     * Redeem reward
     */
    public function redeemReward($userId, $rewardId, $pointsCost) {
        $this->db->beginTransaction();
        
        try {
            // Check if user has enough points
            $sql = "SELECT loyalty_points FROM users WHERE user_id = ? FOR UPDATE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || $user['loyalty_points'] < $pointsCost) {
                $this->db->rollBack();
                return false;
            }
            
            // Deduct points
            $sql = "UPDATE users SET loyalty_points = loyalty_points - ? WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pointsCost, $userId]);
            
            // Create redemption record
            $sql = "INSERT INTO loyalty_redemptions (user_id, reward_id, points_used, redemption_date, status) 
                    VALUES (?, ?, ?, CURRENT_TIMESTAMP, 'completed')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $rewardId, $pointsCost]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Cancel hotel booking
     */
    public function cancelHotelBooking($bookingId, $userId) {
        $sql = "UPDATE hotel_bookings SET booking_status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                WHERE booking_id = ? AND user_id = ? AND booking_status != 'cancelled'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$bookingId, $userId]);
    }
    
    /**
     * Cancel restaurant reservation
     */
    public function cancelRestaurantReservation($reservationId, $userId) {
        $sql = "UPDATE restaurant_reservations SET reservation_status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                WHERE reservation_id = ? AND user_id = ? AND reservation_status != 'cancelled'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reservationId, $userId]);
    }
    
    /**
     * Modify restaurant reservation
     */
    public function modifyRestaurantReservation($reservationId, $userId, $data) {
        $sql = "UPDATE restaurant_reservations 
                SET reservation_date = ?, reservation_time = ?, number_of_guests = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE reservation_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['reservation_date'],
            $data['reservation_time'],
            $data['number_of_guests'],
            $reservationId,
            $userId
        ]);
    }
    
    /**
     * Create restaurant reservation
     */
    public function createRestaurantReservation($userId, $data) {
        // Generate reservation reference
        $reservationReference = 'RS' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        
        $sql = "INSERT INTO restaurant_reservations (user_id, table_id, reservation_reference, reservation_date, reservation_time, number_of_guests, reservation_status, special_requests, occasion_type, deposit_amount, deposit_paid, points_earned, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, 0.00, FALSE, 0, CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $data['table_id'] ?? null,
            $reservationReference,
            $data['reservation_date'],
            $data['reservation_time'],
            $data['number_of_guests'],
            $data['special_requests'] ?? '',
            $data['occasion_type'] ?? null
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId, $userId) {
        $sql = "UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE notification_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificationId, $userId]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead($userId) {
        $sql = "UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $userId) {
        $sql = "DELETE FROM notifications WHERE notification_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificationId, $userId]);
    }
    
    /**
     * Create notification
     */
    public function createNotification($userId, $title, $message, $type = 'system', $actionUrl = null, $actionText = null) {
        $sql = "INSERT INTO notifications (user_id, title, message, notification_type, action_url, action_text, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $title,
            $message,
            $type,
            $actionUrl,
            $actionText
        ]);
    }
    
    /**
     * Get user reviews
     */
    public function getUserReviews($userId, $limit = 10) {
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.user_id = ? 
                ORDER BY r.created_at DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all reviews
     */
    public function getAllReviews($limit = 50) {
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                ORDER BY r.created_at DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get food orders
     */
    public function getFoodOrders($userId, $limit = 10) {
        $sql = "SELECT fo.*, 
                       GROUP_CONCAT(CONCAT(foi.quantity, 'x ', mi.item_name) SEPARATOR ', ') as items_summary,
                       COUNT(foi.order_item_id) as item_count
                FROM food_orders fo 
                LEFT JOIN food_order_items foi ON fo.order_id = foi.order_id 
                LEFT JOIN menu_items mi ON foi.item_id = mi.item_id 
                WHERE fo.user_id = ? 
                GROUP BY fo.order_id
                ORDER BY fo.created_at DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create food order
     */
    public function createFoodOrder($userId, $orderData) {
        error_log("Starting createFoodOrder for user $userId");
        error_log("Order data: " . print_r($orderData, true));
        
        $this->db->beginTransaction();
        
        try {
            // Generate order reference
            $orderReference = 'FO' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            error_log("Generated order reference: $orderReference");
            
            // Calculate total amount
            $totalAmount = 0;
            $orderItems = [];
            
            foreach ($orderData['items'] as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $totalAmount += $subtotal;
                $orderItems[] = [
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal
                ];
                error_log("Item: {$item['item_id']} - Qty: {$item['quantity']} - Price: {$item['price']} - Subtotal: $subtotal");
            }
            
            error_log("Total amount calculated: $totalAmount");
            
            // Create order record
            $sql = "INSERT INTO food_orders (user_id, order_reference, order_type, order_status, total_amount, delivery_address, special_instructions, created_at) 
                    VALUES (?, ?, 'takeaway', 'confirmed', ?, ?, ?, CURRENT_TIMESTAMP)";
            error_log("Order SQL: $sql");
            
            $stmt = $this->db->prepare($sql);
            $orderResult = $stmt->execute([
                $userId,
                $orderReference,
                $totalAmount,
                $orderData['delivery_address'] ?? '',
                $orderData['special_instructions'] ?? ''
            ]);
            
            error_log("Order insert result: " . ($orderResult ? 'SUCCESS' : 'FAILED'));
            
            if (!$orderResult) {
                throw new Exception("Failed to insert order record");
            }
            
            $orderId = $this->db->lastInsertId();
            error_log("Generated order ID: $orderId");
            
            // Add order items
            foreach ($orderItems as $item) {
                $sql = "INSERT INTO food_order_items (order_id, item_id, quantity, unit_price, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                error_log("Item SQL: $sql for order $orderId, item {$item['item_id']}");
                
                $stmt = $this->db->prepare($sql);
                $itemResult = $stmt->execute([$orderId, $item['item_id'], $item['quantity'], $item['price'], $item['subtotal']]);
                
                error_log("Item insert result: " . ($itemResult ? 'SUCCESS' : 'FAILED'));
                
                if (!$itemResult) {
                    throw new Exception("Failed to insert order item for item {$item['item_id']}");
                }
            }
            
            $this->db->commit();
            error_log("Transaction committed successfully");
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to create food order: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Add payment method
     */
    public function addPaymentMethod($userId, $data) {
        $sql = "INSERT INTO payment_methods (user_id, card_type, card_number, cardholder_name, expiry_date, cvv, billing_address, is_default, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $data['card_type'],
            $data['card_number'], // In production, this should be tokenized
            $data['cardholder_name'],
            $data['expiry_date'],
            $data['cvv'], // In production, this should not be stored
            $data['billing_address'] ?? ''
        ]);
    }
    
    /**
     * Get pending payments
     */
    public function getPendingPayments($userId) {
        $sql = "SELECT * FROM payments WHERE user_id = ? AND status = 'pending' ORDER BY due_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payment history
     */
    public function getPaymentHistory($userId, $limit = 20) {
        $sql = "SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Process payment
     */
    public function processPayment($userId, $paymentId, $amount) {
        $this->db->beginTransaction();
        
        try {
            // Update payment status
            $sql = "UPDATE payments SET status = 'completed', payment_date = CURRENT_TIMESTAMP, amount = ? 
                    WHERE payment_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$amount, $paymentId, $userId]);
            
            if ($result) {
                // Create notification
                $this->createNotification($userId, 'Payment Processed', "Your payment of ₱{$amount} has been processed successfully.", 'payment');
                
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>
