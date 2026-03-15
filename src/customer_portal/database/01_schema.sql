-- =============================================
-- LÙCAS HOTEL & RESTAURANT CUSTOMER PORTAL DATABASE
-- =============================================
-- Database Schema for Customer Portal System
-- Created: March 12, 2026

-- Create Database (if needed)
-- CREATE DATABASE lucas_customer_portal;
-- USE lucas_customer_portal;

-- =============================================
-- 1. USERS TABLE
-- =============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    alternative_phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'prefer_not_to_say'),
    nationality VARCHAR(100),
    street_address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    preferred_language VARCHAR(50) DEFAULT 'English',
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    user_role ENUM('customer', 'admin', 'restaurant_manager', 'hotel_manager') DEFAULT 'customer',
    membership_tier ENUM('member', 'silver', 'gold', 'platinum') DEFAULT 'member',
    loyalty_points INT DEFAULT 0,
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    profile_photo_url TEXT,
    account_status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (user_role),
    INDEX idx_status (account_status),
    INDEX idx_membership (membership_tier),
    INDEX idx_loyalty_points (loyalty_points)
);

-- =============================================
-- 2. HOTEL ROOMS TABLE
-- =============================================
CREATE TABLE hotel_rooms (
    room_id INT PRIMARY KEY AUTO_INCREMENT,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type ENUM('deluxe_twin', 'ocean_suite', 'executive_suite', 'presidential_suite', 'standard_room') NOT NULL,
    base_price_per_night DECIMAL(10,2) NOT NULL,
    max_occupancy INT NOT NULL,
    bed_configuration VARCHAR(100) NULL,
    amenities JSON NULL, -- e.g., ["mini_bar", "balcony", "ocean_view"]
    room_status ENUM('available', 'occupied', 'maintenance', 'out_of_order') DEFAULT 'available',
    floor_number INT NULL,
    square_meters INT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_room_type (room_type),
    INDEX idx_room_status (room_status),
    INDEX idx_price (base_price_per_night)
);

-- =============================================
-- 3. HOTEL BOOKINGS TABLE
-- =============================================
CREATE TABLE hotel_bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    number_of_guests INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    deposit_amount DECIMAL(10,2) DEFAULT 0.00,
    booking_status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show') DEFAULT 'pending',
    payment_status ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',
    special_requests TEXT NULL,
    guest_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES hotel_rooms(room_id),
    INDEX idx_user_id (user_id),
    INDEX idx_booking_dates (check_in_date, check_out_date),
    INDEX idx_booking_status (booking_status),
    INDEX idx_payment_status (payment_status)
);

-- =============================================
-- 4. RESTAURANT TABLES
-- =============================================
CREATE TABLE restaurant_tables (
    table_id INT PRIMARY KEY AUTO_INCREMENT,
    table_number VARCHAR(10) UNIQUE NOT NULL,
    table_type ENUM('2_person', '4_person', '6_person', '8_person', 'booth', 'private') NOT NULL,
    max_capacity INT NOT NULL,
    table_status ENUM('available', 'reserved', 'occupied', 'maintenance') DEFAULT 'available',
    location_area VARCHAR(50) NULL, -- e.g., 'main_dining', 'terrace', 'private_room'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_table_status (table_status),
    INDEX idx_table_type (table_type)
);

-- =============================================
-- 5. RESTAURANT RESERVATIONS
-- =============================================
CREATE TABLE restaurant_reservations (
    reservation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    table_id INT NULL,
    reservation_reference VARCHAR(20) UNIQUE NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    number_of_guests INT NOT NULL,
    reservation_status ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    special_requests TEXT NULL,
    occasion_type VARCHAR(50) NULL, -- e.g., 'birthday', 'anniversary', 'business'
    deposit_amount DECIMAL(10,2) DEFAULT 0.00,
    deposit_paid BOOLEAN DEFAULT FALSE,
    points_earned INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(table_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reservation_datetime (reservation_date, reservation_time),
    INDEX idx_reservation_status (reservation_status)
);

-- =============================================
-- 6. MENU CATEGORIES
-- =============================================
CREATE TABLE menu_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    category_description TEXT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_category_order (display_order),
    INDEX idx_active (is_active)
);

-- =============================================
-- 7. MENU ITEMS
-- =============================================
CREATE TABLE menu_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    item_name VARCHAR(200) NOT NULL,
    item_description TEXT NULL,
    price DECIMAL(8,2) NOT NULL,
    item_status ENUM('available', 'unavailable', 'seasonal') DEFAULT 'available',
    preparation_time_minutes INT NULL,
    allergen_info JSON NULL, -- e.g., ["nuts", "dairy", "gluten"]
    dietary_info JSON NULL, -- e.g., ["vegetarian", "vegan", "gluten_free"]
    image_url VARCHAR(500) NULL,
    spicy_level ENUM('none', 'mild', 'medium', 'hot', 'extra_hot') DEFAULT 'none',
    is_signature BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES menu_categories(category_id),
    INDEX idx_category_id (category_id),
    INDEX idx_item_status (item_status),
    INDEX idx_price (price)
);

-- =============================================
-- 8. FOOD ORDERS
-- =============================================
CREATE TABLE food_orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_reference VARCHAR(20) UNIQUE NOT NULL,
    order_type ENUM('dine_in', 'takeaway', 'room_delivery') NOT NULL,
    order_status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    points_earned INT DEFAULT 0,
    points_used INT DEFAULT 0,
    delivery_room_number VARCHAR(10) NULL,
    delivery_table_number VARCHAR(10) NULL,
    estimated_ready_time TIMESTAMP NULL,
    actual_delivery_time TIMESTAMP NULL,
    special_instructions TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_order_status (order_status),
    INDEX idx_order_type (order_type),
    INDEX idx_created_at (created_at)
);

-- =============================================
-- 9. FOOD ORDER ITEMS
-- =============================================
CREATE TABLE food_order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(8,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    special_instructions TEXT NULL,
    item_status ENUM('ordered', 'preparing', 'ready', 'served', 'cancelled') DEFAULT 'ordered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES food_orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id),
    INDEX idx_order_id (order_id),
    INDEX idx_item_id (item_id)
);

-- =============================================
-- 10. PAYMENT METHODS
-- =============================================
CREATE TABLE payment_methods (
    payment_method_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    method_type ENUM('credit_card', 'debit_card', 'gcash', 'maya', 'bank_transfer', 'cash') NOT NULL,
    method_nickname VARCHAR(100) NULL, -- e.g., "Personal GCash", "Company Credit Card"
    provider_name VARCHAR(100) NULL, -- e.g., "GCash", "Visa", "Mastercard"
    account_number_encrypted VARCHAR(255) NULL, -- encrypted account number
    expiry_date VARCHAR(10) NULL, -- MM/YY format
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_method_type (method_type),
    INDEX idx_is_default (is_default)
);

-- =============================================
-- 11. TRANSACTIONS
-- =============================================
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    transaction_reference VARCHAR(30) UNIQUE NOT NULL,
    transaction_type ENUM('payment', 'refund', 'deposit', 'points_earn', 'points_redeem') NOT NULL,
    related_entity_type ENUM('hotel_booking', 'restaurant_reservation', 'food_order', 'loyalty_reward', 'payment') NULL,
    related_entity_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method_id INT NULL,
    transaction_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    points_earned INT DEFAULT 0,
    points_used INT DEFAULT 0,
    processing_fee DECIMAL(8,2) DEFAULT 0.00,
    transaction_description TEXT NULL,
    external_transaction_id VARCHAR(100) NULL, -- for payment gateway reference
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(payment_method_id),
    INDEX idx_user_id (user_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_status (transaction_status),
    INDEX idx_created_at (created_at),
    INDEX idx_related_entity (related_entity_type, related_entity_id)
);

-- =============================================
-- 12. LOYALTY REWARDS
-- =============================================
CREATE TABLE loyalty_rewards (
    reward_id INT PRIMARY KEY AUTO_INCREMENT,
    reward_name VARCHAR(200) NOT NULL,
    reward_description TEXT NULL,
    reward_type ENUM('free_item', 'discount', 'upgrade', 'service', 'experience') NOT NULL,
    points_cost INT NOT NULL,
    monetary_value DECIMAL(8,2) NULL,
    tier_requirement ENUM('member', 'silver', 'gold', 'platinum') DEFAULT 'member',
    reward_status ENUM('available', 'unavailable', 'seasonal') DEFAULT 'available',
    redemption_instructions TEXT NULL,
    terms_conditions TEXT NULL,
    valid_from DATE NULL,
    valid_until DATE NULL,
    usage_limit_per_user INT NULL,
    total_usage_limit INT NULL,
    current_usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_reward_type (reward_type),
    INDEX idx_points_cost (points_cost),
    INDEX idx_tier_requirement (tier_requirement),
    INDEX idx_reward_status (reward_status)
);

-- =============================================
-- 13. USER REWARD REDEMPTIONS
-- =============================================
CREATE TABLE user_reward_redemptions (
    redemption_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    reward_id INT NOT NULL,
    redemption_reference VARCHAR(20) UNIQUE NOT NULL,
    points_used INT NOT NULL,
    redemption_status ENUM('pending', 'confirmed', 'used', 'expired', 'cancelled') DEFAULT 'pending',
    redemption_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP NULL,
    usage_date TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES loyalty_rewards(reward_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reward_id (reward_id),
    INDEX idx_redemption_status (redemption_status),
    INDEX idx_redemption_date (redemption_date)
);

-- =============================================
-- 14. NOTIFICATIONS
-- =============================================
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL, -- NULL for system-wide notifications
    notification_type ENUM('booking', 'payment', 'loyalty', 'promo', 'system', 'reminder', 'review') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    priority_level ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    action_url VARCHAR(500) NULL, -- link to relevant page
    action_text VARCHAR(100) NULL, -- button text
    expires_at TIMESTAMP NULL,
    sent_via JSON NULL, -- e.g., ["email", "sms", "in_app"]
    related_entity_type ENUM('hotel_booking', 'restaurant_reservation', 'food_order', 'loyalty_reward', 'payment') NULL,
    related_entity_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_related_entity (related_entity_type, related_entity_id)
);

-- =============================================
-- 15. USER NOTIFICATION PREFERENCES
-- =============================================
CREATE TABLE user_notification_preferences (
    preference_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_category ENUM('booking_confirmations', 'reservation_reminders', 'payment_updates', 'loyalty_updates', 'promotional_offers', 'system_announcements') NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT TRUE,
    in_app_enabled BOOLEAN DEFAULT TRUE,
    frequency_preference ENUM('immediate', 'daily_digest', 'weekly_digest', 'never') DEFAULT 'immediate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, notification_category),
    INDEX idx_user_id (user_id)
);

-- =============================================
-- 16. USER REVIEWS
-- =============================================
CREATE TABLE user_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    review_type ENUM('hotel_stay', 'dining_experience', 'food_item', 'service') NOT NULL,
    related_entity_id INT NULL, -- ID of the reviewed item
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(200) NULL,
    review_text TEXT NULL,
    review_status ENUM('pending', 'approved', 'rejected', 'hidden') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    staff_response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_review_type (review_type),
    INDEX idx_rating (rating),
    INDEX idx_review_status (review_status),
    INDEX idx_created_at (created_at)
);

-- =============================================
-- 17. POINTS HISTORY
-- =============================================
CREATE TABLE points_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    points_change INT NOT NULL, -- positive for earned, negative for spent
    points_balance_after INT NOT NULL,
    transaction_type ENUM('earn', 'redeem', 'expire', 'adjust') NOT NULL,
    source_type ENUM('hotel_stay', 'dining', 'promo', 'reward_redemption', 'manual_adjust', 'signup_bonus') NOT NULL,
    source_id INT NULL, -- ID of related booking, order, etc.
    description VARCHAR(500) NOT NULL,
    expires_at TIMESTAMP NULL, -- for points that have expiration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_source_type (source_type),
    INDEX idx_created_at (created_at),
    INDEX idx_expires_at (expires_at)
);

-- =============================================
-- 18. WAITING LIST
-- =============================================
CREATE TABLE waiting_list (
    waiting_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    waiting_type ENUM('restaurant_table', 'hotel_room', 'service') NOT NULL,
    party_size INT NULL,
    estimated_wait_time_minutes INT NULL,
    priority_level ENUM('normal', 'vip', 'urgent') DEFAULT 'normal',
    contact_method ENUM('sms', 'email', 'phone_call') DEFAULT 'sms',
    special_requests TEXT NULL,
    waiting_status ENUM('waiting', 'notified', 'seated', 'cancelled', 'no_show') DEFAULT 'waiting',
    notified_at TIMESTAMP NULL,
    seated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_waiting_type (waiting_type),
    INDEX idx_waiting_status (waiting_status),
    INDEX idx_created_at (created_at)
);

-- =============================================
-- 19. SESSIONS (for authentication)
-- =============================================
CREATE TABLE user_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    session_data JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    INDEX idx_last_activity (last_activity)
);

-- =============================================
-- 20. AUDIT LOG
-- =============================================
CREATE TABLE audit_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action_type VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
);

-- =============================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- =============================================

-- Update user's updated_at timestamp
DELIMITER //
CREATE TRIGGER update_users_timestamp 
BEFORE UPDATE ON users 
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;

-- Update loyalty points when points history changes
DELIMITER //
CREATE TRIGGER update_user_points_after_history 
AFTER INSERT ON points_history 
FOR EACH ROW 
BEGIN
    UPDATE users 
    SET loyalty_points = NEW.points_balance_after 
    WHERE user_id = NEW.user_id;
END//
DELIMITER ;

-- =============================================
-- VIEWS FOR COMMON QUERIES
-- =============================================

-- User Dashboard View
CREATE VIEW user_dashboard_view AS
SELECT 
    u.user_id,
    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
    u.email,
    u.membership_tier,
    u.loyalty_points,
    u.profile_photo_url,
    COUNT(DISTINCT hb.booking_id) AS total_hotel_bookings,
    COUNT(DISTINCT rr.reservation_id) AS total_restaurant_reservations,
    COUNT(DISTINCT fo.order_id) AS total_food_orders,
    COUNT(DISTINCT n.notification_id) AS unread_notifications
FROM users u
LEFT JOIN hotel_bookings hb ON u.user_id = hb.user_id
LEFT JOIN restaurant_reservations rr ON u.user_id = rr.user_id  
LEFT JOIN food_orders fo ON u.user_id = fo.user_id
LEFT JOIN notifications n ON u.user_id = n.user_id AND n.is_read = FALSE
WHERE u.account_status = 'active'
GROUP BY u.user_id;

-- Active Hotel Bookings View
CREATE VIEW active_bookings_view AS
SELECT 
    hb.booking_id,
    hb.booking_reference,
    hb.user_id,
    CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
    hr.room_number,
    hr.room_type,
    hb.check_in_date,
    hb.check_out_date,
    hb.number_of_guests,
    hb.total_amount,
    hb.booking_status,
    hb.payment_status
FROM hotel_bookings hb
JOIN users u ON hb.user_id = u.user_id
JOIN hotel_rooms hr ON hb.room_id = hr.room_id
WHERE hb.booking_status IN ('pending', 'confirmed', 'checked_in');

-- Restaurant Reservations Today View
CREATE VIEW today_reservations_view AS
SELECT 
    rr.reservation_id,
    rr.reservation_reference,
    rr.user_id,
    CONCAT(u.first_name, ' ', u.last_name) AS guest_name,
    rr.reservation_date,
    rr.reservation_time,
    rr.number_of_guests,
    rt.table_number,
    rr.reservation_status,
    rr.special_requests
FROM restaurant_reservations rr
JOIN users u ON rr.user_id = u.user_id
LEFT JOIN restaurant_tables rt ON rr.table_id = rt.table_id
WHERE rr.reservation_date = CURDATE()
ORDER BY rr.reservation_time, rr.reservation_date;
