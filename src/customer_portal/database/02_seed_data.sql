-- =============================================
-- LÙCAS HOTEL & RESTAURANT CUSTOMER PORTAL
-- Seed Data for Customer Portal System
-- Created: March 12, 2026
-- =============================================

-- =============================================
-- 1. USERS SEED DATA (Role-Based)
-- =============================================

-- Main customer user: Mia Cruz (from my_profile.html)
INSERT INTO users (
    user_id, first_name, last_name, email, phone, alternative_phone, 
    date_of_birth, gender, nationality, street_address, city, postal_code, 
    country, preferred_language, email_verified, phone_verified, user_role,
    membership_tier, loyalty_points, join_date, profile_photo_url, account_status
) VALUES (
    1, 
    'Mia', 
    'Cruz', 
    'mia.cruz@email.com', 
    '+63 917 555 1234', 
    NULL, 
    '1994-05-12', 
    'female', 
    'Filipino', 
    '15 B. Gonzales St., Barangay San Antonio', 
    'Makati', 
    '1203', 
    'Philippines', 
    'English', 
    TRUE, 
    TRUE, 
    'customer',
    'gold', 
    1240, 
    '2024-03-15', 
    NULL, 
    'active'
);

-- Admin user
INSERT INTO users (
    first_name, last_name, email, phone, password_hash, email_verified, phone_verified, user_role,
    membership_tier, loyalty_points, join_date, account_status
) VALUES (
    'Admin', 
    'User', 
    'admin@lucas.stay', 
    '+63 912 345 6789', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    TRUE, 
    TRUE, 
    'admin',
    'platinum', 
    10000, 
    '2024-01-01', 
    'active'
);

-- Restaurant Manager
INSERT INTO users (
    first_name, last_name, email, phone, password_hash, email_verified, phone_verified, user_role,
    membership_tier, loyalty_points, join_date, account_status
) VALUES (
    'Restaurant', 
    'Manager', 
    'manager@lucas.stay', 
    '+63 923 456 7890', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: manager123
    TRUE, 
    TRUE, 
    'restaurant_manager',
    'gold', 
    5000, 
    '2024-01-01', 
    'active'
);

-- Hotel Manager
INSERT INTO users (
    first_name, last_name, email, phone, password_hash, email_verified, phone_verified, user_role,
    membership_tier, loyalty_points, join_date, account_status
) VALUES (
    'Hotel', 
    'Manager', 
    'hotel@lucas.stay', 
    '+63 934 567 8901', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: hotel123
    TRUE, 
    TRUE, 
    'hotel_manager',
    'gold', 
    5000, 
    '2024-01-01', 
    'active'
);

-- Additional demo customers
INSERT INTO users (
    first_name, last_name, email, phone, date_of_birth, gender, 
    nationality, city, country, preferred_language, email_verified, 
    phone_verified, user_role, membership_tier, loyalty_points, join_date, account_status
) VALUES 
(
    'Juan', 
    'Mateo', 
    'juan.mateo@email.com', 
    '+63 912 345 6789', 
    '1985-08-22', 
    'male', 
    'Filipino', 
    'Quezon City', 
    'Philippines', 
    'English', 
    TRUE, 
    TRUE, 
    'customer',
    'silver', 
    680, 
    '2023-11-10', 
    'active'
),
(
    'Sofia', 
    'Reyes', 
    'sofia.reyes@email.com', 
    '+63 923 456 7890', 
    '1992-03-15', 
    'female', 
    'Filipino', 
    'Manila', 
    'Philippines', 
    'English', 
    TRUE, 
    FALSE, 
    'customer',
    'member', 
    150, 
    '2024-01-20', 
    'active'
),
(
    'Carlos', 
    'Santos', 
    'carlos.santos@email.com', 
    '+63 934 567 8901', 
    '1988-11-30', 
    'male', 
    'Filipino', 
    'Cebu City', 
    'Philippines', 
    'English', 
    TRUE, 
    TRUE, 
    'customer',
    'platinum', 
    2150, 
    '2023-06-05', 
    'active'
);

-- =============================================
-- 2. HOTEL ROOMS SEED DATA
-- =============================================

INSERT INTO hotel_rooms (
    room_number, room_type, base_price_per_night, max_occupancy, 
    bed_configuration, amenities, room_status, floor_number, square_meters, description
) VALUES 
(
    '101', 
    'deluxe_twin', 
    4200.00, 
    2, 
    '2 Twin Beds', 
    JSON_ARRAY('mini_bar', 'air_conditioning', 'work_desk', 'safety_box'),
    'available', 
    1, 
    28, 
    'Comfortable deluxe room with twin beds, perfect for business travelers.'
),
(
    '102', 
    'deluxe_twin', 
    4200.00, 
    2, 
    '2 Twin Beds', 
    JSON_ARRAY('mini_bar', 'air_conditioning', 'work_desk', 'safety_box', 'city_view'),
    'available', 
    1, 
    28, 
    'Deluxe twin room with city view, ideal for friends or colleagues.'
),
(
    '201', 
    'ocean_suite', 
    6900.00, 
    3, 
    '1 King Bed + 1 Sofa Bed', 
    JSON_ARRAY('mini_bar', 'air_conditioning', 'balcony', 'ocean_view', 'living_area', 'safety_box'),
    'available', 
    2, 
    45, 
    'Spacious ocean suite with stunning sea views and separate living area.'
),
(
    '202', 
    'ocean_suite', 
    6900.00, 
    3, 
    '1 King Bed + 1 Sofa Bed', 
    JSON_ARRAY('mini_bar', 'air_conditioning', 'balcony', 'ocean_view', 'living_area', 'safety_box', 'jacuzzi'),
    'available', 
    2, 
    48, 
    'Luxurious ocean suite with private jacuzzi and panoramic ocean views.'
),
(
    '301', 
    'executive_suite', 
    8500.00, 
    4, 
    '1 King Bed + 2 Twin Beds', 
    JSON_ARRAY('mini_bar', 'air_conditioning', 'balcony', 'ocean_view', 'living_area', 'dining_area', 'kitchenette', 'safety_box'),
    'available', 
    3, 
    65, 
    'Executive suite with full amenities, perfect for extended stays.'
),
(
    '401', 
    'presidential_suite', 
    15000.00, 
    6, 
    '2 King Beds + 2 Queen Beds', 
    JSON_ARRAY('mini_bar', 'air_conditioning', 'balcony', 'ocean_view', 'living_area', 'dining_area', 'full_kitchen', 'safety_box', 'jacuzzi', 'butler_service'),
    'available', 
    4, 
    120, 
    'Ultimate luxury presidential suite with butler service and full kitchen.'
);

-- =============================================
-- 3. RESTAURANT TABLES SEED DATA
-- =============================================

INSERT INTO restaurant_tables (
    table_number, table_type, max_capacity, table_status, location_area
) VALUES 
('T1', '2_person', 2, 'available', 'main_dining'),
('T2', '2_person', 2, 'available', 'main_dining'),
('T3', '4_person', 4, 'available', 'main_dining'),
('T4', '4_person', 4, 'available', 'main_dining'),
('T5', '4_person', 4, 'available', 'main_dining'),
('T6', '6_person', 6, 'available', 'main_dining'),
('T7', '6_person', 6, 'available', 'main_dining'),
('T8', '8_person', 8, 'available', 'main_dining'),
('B1', 'booth', 4, 'available', 'terrace'),
('B2', 'booth', 4, 'available', 'terrace'),
('P1', 'private', 12, 'available', 'private_room'),
('P2', 'private', 20, 'available', 'private_room');

-- =============================================
-- 4. MENU CATEGORIES SEED DATA
-- =============================================

INSERT INTO menu_categories (
    category_name, category_description, display_order, is_active
) VALUES 
('Appetizers', 'Start your meal with our delicious appetizers', 1, TRUE),
('Mains', 'Main course dishes featuring local and international cuisine', 2, TRUE),
('Desserts', 'Sweet endings to your perfect meal', 3, TRUE),
('Beverages', 'Refreshing drinks and beverages', 4, TRUE),
('Soups', 'Warm and comforting soups', 5, TRUE),
('Salads', 'Fresh and healthy salad options', 6, TRUE);

-- =============================================
-- 5. MENU ITEMS SEED DATA
-- =============================================

INSERT INTO menu_items (
    category_id, item_name, item_description, price, item_status, 
    preparation_time_minutes, allergen_info, dietary_info, spicy_level, is_signature
) VALUES 
-- Appetizers
(1, 'Calamares', 'Deep-fried squid rings with garlic aioli', 280.00, 'available', 15, JSON_ARRAY('seafood', 'gluten'), JSON_ARRAY('gluten_free'), 'none', FALSE),
(1, 'Lumpiang Shanghai', 'Filipino spring rolls with sweet and sour sauce', 220.00, 'available', 12, JSON_ARRAY('meat', 'gluten'), JSON_ARRAY(), 'none', TRUE),
(1, 'Cheese Platter', 'Selection of local and imported cheeses', 380.00, 'available', 10, JSON_ARRAY('dairy'), JSON_ARRAY('vegetarian'), 'none', FALSE),

-- Mains
(2, 'Sinigang na Baboy', 'Tamarind soup with pork and vegetables', 320.00, 'available', 25, JSON_ARRAY(), JSON_ARRAY('gluten_free'), 'none', TRUE),
(2, 'Sizzling Sisig', 'Sizzling chopped pork with onion and egg', 290.00, 'available', 20, JSON_ARRAY('meat'), JSON_ARRAY(), 'medium', TRUE),
(2, 'Crispy Pata', 'Deep-fried pork knuckle with soy-vinegar dip', 550.00, 'available', 30, JSON_ARRAY('meat'), JSON_ARRAY(), 'none', TRUE),
(2, 'Garlic Rice', 'Fried rice with garlic, plain', 50.00, 'available', 8, JSON_ARRAY('gluten'), JSON_ARRAY('vegetarian'), 'none', FALSE),
(2, 'Grilled Salmon', 'Atlantic salmon with lemon butter sauce', 680.00, 'available', 22, JSON_ARRAY('fish'), JSON_ARRAY('gluten_free'), 'none', FALSE),
(2, 'Beef Steak', 'Filipino-style beef steak with onions', 450.00, 'available', 25, JSON_ARRAY('meat'), JSON_ARRAY(), 'none', FALSE),

-- Desserts
(3, 'Halo-Halo', 'Shaved ice with mixed fruits, leche flan, and ube', 150.00, 'available', 10, JSON_ARRAY('dairy'), JSON_ARRAY('vegetarian'), 'none', TRUE),
(3, 'Leche Flan', 'Creamy caramel custard', 120.00, 'available', 5, JSON_ARRAY('dairy', 'egg'), JSON_ARRAY('vegetarian'), 'none', TRUE),
(3, 'Chocolate Cake', 'Rich chocolate cake with ganache', 180.00, 'available', 8, JSON_ARRAY('dairy', 'gluten', 'egg'), JSON_ARRAY(), 'none', FALSE),

-- Beverages
(4, 'Fresh Buko Juice', 'Fresh coconut juice with pulp', 90.00, 'available', 5, JSON_ARRAY(), JSON_ARRAY('vegan', 'gluten_free'), 'none', FALSE),
(4, 'Brewed Coffee', 'Locally brewed coffee', 120.00, 'available', 3, JSON_ARRAY(), JSON_ARRAY('vegan', 'gluten_free'), 'none', FALSE),
(4, 'Iced Tea', 'Freshly brewed iced tea', 80.00, 'available', 3, JSON_ARRAY(), JSON_ARRAY('vegan', 'gluten_free'), 'none', FALSE),
(4, 'Mango Shake', 'Fresh mango smoothie', 140.00, 'available', 5, JSON_ARRAY('dairy'), JSON_ARRAY('vegetarian'), 'none', FALSE),

-- Soups
(5, 'Chicken Tinola', 'Chicken soup with ginger and vegetables', 250.00, 'available', 20, JSON_ARRAY(), JSON_ARRAY('gluten_free'), 'none', FALSE),
(5, 'Beef Nilaga', 'Beef soup with vegetables', 280.00, 'available', 25, JSON_ARRAY('meat'), JSON_ARRAY('gluten_free'), 'none', FALSE),

-- Salads
(6, 'Garden Salad', 'Mixed greens with vinaigrette dressing', 180.00, 'available', 10, JSON_ARRAY(), JSON_ARRAY('vegan', 'gluten_free'), 'none', FALSE),
(6, 'Caesar Salad', 'Romaine lettuce with Caesar dressing and croutons', 220.00, 'available', 12, JSON_ARRAY('dairy', 'gluten', 'egg'), JSON_ARRAY('vegetarian'), 'none', FALSE);

-- =============================================
-- 6. LOYALTY REWARDS SEED DATA
-- =============================================

INSERT INTO loyalty_rewards (
    reward_name, reward_description, reward_type, points_cost, monetary_value, 
    tier_requirement, reward_status, redemption_instructions, terms_conditions, 
    valid_from, valid_until, usage_limit_per_user, total_usage_limit
) VALUES 
(
    'Free Coffee / Tea', 
    'Any hot beverage at Azure Lounge', 
    'free_item', 
    240, 
    120.00, 
    'member', 
    'available', 
    'Present this reward at Azure Lounge to claim your free hot beverage.', 
    'Valid for any regular coffee or tea. Not applicable for specialty drinks.', 
    '2024-01-01', 
    '2025-12-31', 
    5, 
    1000
),
(
    'Complimentary Breakfast', 
    'For one person at Azure Restaurant', 
    'free_item', 
    480, 
    350.00, 
    'member', 
    'available', 
    'Show this reward at Azure Restaurant breakfast buffet.', 
    'Valid for breakfast buffet only. Cannot be combined with other promotions.', 
    '2024-01-01', 
    '2025-12-31', 
    3, 
    500
),
(
    'Late Check-out (2pm)', 
    'Subject to availability', 
    'service', 
    600, 
    0.00, 
    'silver', 
    'available', 
    'Request late check-out at front desk when checking in.', 
    'Subject to room availability. Must be requested 24 hours in advance.', 
    '2024-01-01', 
    '2025-12-31', 
    2, 
    200
),
(
    'Welcome Drink (2 pax)', 
    'Signature cocktail or mocktail', 
    'free_item', 
    360, 
    280.00, 
    'member', 
    'available', 
    'Present at Azure Restaurant or bar to claim welcome drinks.', 
    'Valid for any signature cocktail or mocktail. Alcoholic options for 18+ only.', 
    '2024-01-01', 
    '2025-12-31', 
    4, 
    800
),
(
    'Room Upgrade (next stay)', 
    'Deluxe to suite (subject to availability)', 
    'upgrade', 
    1200, 
    0.00, 
    'gold', 
    'available', 
    'Request upgrade when making next booking.', 
    'Upgrade from deluxe room to suite, subject to availability. One category upgrade only.', 
    '2024-01-01', 
    '2025-12-31', 
    1, 
    100
),
(
    '₱500 Discount', 
    'On any hotel booking', 
    'discount', 
    800, 
    500.00, 
    'silver', 
    'available', 
    'Apply discount code at checkout when booking hotel room.', 
    'Valid for minimum booking of ₱2,000. Cannot be combined with other discounts.', 
    '2024-01-01', 
    '2025-12-31', 
    3, 
    300
),
(
    'Free Halo-Halo', 
    'Signature Filipino dessert', 
    'free_item', 
    150, 
    150.00, 
    'member', 
    'available', 
    'Present this reward at Azure Restaurant to claim free Halo-Halo.', 
    'Valid for regular Halo-Halo only. No substitutions allowed.', 
    '2024-01-01', 
    '2025-12-31', 
    10, 
    2000
);

-- =============================================
-- 7. PAYMENT METHODS SEED DATA
-- =============================================

INSERT INTO payment_methods (
    user_id, method_type, method_nickname, provider_name, 
    account_number_encrypted, expiry_date, is_default, is_active
) VALUES 
-- Mia Cruz's payment methods
(1, 'gcash', 'Personal GCash', 'GCash', '****1234', NULL, TRUE, TRUE),
(1, 'credit_card', 'Company Credit Card', 'Visa', '****5678', '12/25', FALSE, TRUE),

-- Juan Mateo's payment methods
(2, 'gcash', 'Main GCash', 'GCash', '****9876', NULL, TRUE, TRUE),
(2, 'maya', 'Maya Wallet', 'Maya', '****5432', NULL, FALSE, TRUE),

-- Sofia Reyes's payment methods
(3, 'cash', 'Cash Payment', 'Cash', NULL, NULL, TRUE, TRUE),

-- Carlos Santos's payment methods
(4, 'credit_card', 'Premium Credit Card', 'Mastercard', '****1111', '09/26', TRUE, TRUE),
(4, 'gcash', 'Backup GCash', 'GCash', '****2222', NULL, FALSE, TRUE);

-- =============================================
-- 8. USER NOTIFICATION PREFERENCES SEED DATA
-- =============================================

INSERT INTO user_notification_preferences (
    user_id, notification_category, email_enabled, sms_enabled, 
    in_app_enabled, frequency_preference
) VALUES 
-- Mia Cruz's preferences
(1, 'booking_confirmations', TRUE, TRUE, TRUE, 'immediate'),
(1, 'reservation_reminders', TRUE, TRUE, TRUE, 'immediate'),
(1, 'payment_updates', TRUE, FALSE, TRUE, 'immediate'),
(1, 'loyalty_updates', TRUE, TRUE, TRUE, 'immediate'),
(1, 'promotional_offers', FALSE, FALSE, TRUE, 'weekly_digest'),
(1, 'system_announcements', TRUE, FALSE, TRUE, 'immediate'),

-- Juan Mateo's preferences
(2, 'booking_confirmations', TRUE, TRUE, TRUE, 'immediate'),
(2, 'reservation_reminders', TRUE, TRUE, TRUE, 'immediate'),
(2, 'payment_updates', TRUE, FALSE, TRUE, 'immediate'),
(2, 'loyalty_updates', TRUE, TRUE, TRUE, 'immediate'),
(2, 'promotional_offers', TRUE, FALSE, TRUE, 'daily_digest'),
(2, 'system_announcements', TRUE, FALSE, TRUE, 'immediate'),

-- Sofia Reyes's preferences
(3, 'booking_confirmations', TRUE, TRUE, TRUE, 'immediate'),
(3, 'reservation_reminders', TRUE, TRUE, TRUE, 'immediate'),
(3, 'payment_updates', TRUE, FALSE, TRUE, 'immediate'),
(3, 'loyalty_updates', FALSE, FALSE, TRUE, 'weekly_digest'),
(3, 'promotional_offers', FALSE, FALSE, TRUE, 'never'),
(3, 'system_announcements', TRUE, FALSE, TRUE, 'immediate'),

-- Carlos Santos's preferences
(4, 'booking_confirmations', TRUE, TRUE, TRUE, 'immediate'),
(4, 'reservation_reminders', TRUE, TRUE, TRUE, 'immediate'),
(4, 'payment_updates', TRUE, TRUE, TRUE, 'immediate'),
(4, 'loyalty_updates', TRUE, TRUE, TRUE, 'immediate'),
(4, 'promotional_offers', TRUE, TRUE, TRUE, 'immediate'),
(4, 'system_announcements', TRUE, TRUE, TRUE, 'immediate');

-- =============================================
-- 9. SAMPLE TRANSACTIONS SEED DATA
-- =============================================

INSERT INTO transactions (
    user_id, transaction_reference, transaction_type, related_entity_type, 
    related_entity_id, amount, payment_method_id, transaction_status, 
    points_earned, transaction_description, created_at
) VALUES 
-- Mia Cruz's transactions
(1, 'TXN20240315001', 'payment', 'hotel_booking', 1, 4200.00, 1, 'completed', 210, 'Hotel booking payment - Room 101', '2024-03-15 10:30:00'),
(1, 'TXN20240316001', 'payment', 'restaurant_reservation', 1, 200.00, 1, 'completed', 10, 'Restaurant reservation deposit', '2024-03-16 19:45:00'),
(1, 'TXN20240317001', 'payment', 'food_order', 1, 660.00, 1, 'completed', 33, 'Food order - Sinigang, Sisig, Rice', '2024-03-17 20:15:00'),
(1, 'TXN20240318001', 'points_earn', NULL, NULL, 0.00, NULL, 'completed', 0, 'Welcome bonus points', '2024-03-18 09:00:00'),
(1, 'TXN20240319001', 'points_redeem', 'loyalty_reward', 1, 0.00, NULL, 'completed', 0, 'Redeemed Free Coffee', '2024-03-19 15:30:00'),

-- Juan Mateo's transactions
(2, 'TXN20231110001', 'payment', 'hotel_booking', 2, 6900.00, 3, 'completed', 345, 'Hotel booking payment - Ocean Suite', '2023-11-10 14:20:00'),
(2, 'TXN20231111001', 'payment', 'food_order', 2, 450.00, 3, 'completed', 22, 'Food order - Grilled Salmon, Salad', '2023-11-11 19:00:00'),

-- Sofia Reyes's transactions
(3, 'TXN20240120001', 'payment', 'restaurant_reservation', 2, 150.00, 5, 'completed', 7, 'Restaurant reservation deposit', '2024-01-20 18:30:00'),

-- Carlos Santos's transactions
(4, 'TXN20230605001', 'payment', 'hotel_booking', 3, 15000.00, 6, 'completed', 750, 'Hotel booking payment - Presidential Suite', '2023-06-05 16:45:00'),
(4, 'TXN20230606001', 'payment', 'food_order', 3, 980.00, 6, 'completed', 49, 'Food order - multiple items', '2023-06-06 20:00:00');

-- =============================================
-- 10. SAMPLE BOOKINGS SEED DATA
-- =============================================

INSERT INTO hotel_bookings (
    user_id, room_id, booking_reference, check_in_date, check_out_date, 
    number_of_guests, total_amount, deposit_amount, booking_status, 
    payment_status, special_requests
) VALUES 
-- Mia Cruz's bookings
(1, 1, 'HBK20240315001', '2024-03-15', '2024-03-17', 2, 8400.00, 2000.00, 'completed', 'paid', 'Late check-in requested'),
(1, 2, 'HBK20240410001', '2024-04-10', '2024-04-12', 2, 8400.00, 0.00, 'confirmed', 'paid', 'Early check-in if possible'),

-- Juan Mateo's bookings
(2, 3, 'HBK20231110001', '2023-11-10', '2023-11-12', 3, 13800.00, 3000.00, 'completed', 'paid', 'Anniversary celebration'),

-- Carlos Santos's bookings
(4, 6, 'HBK20230605001', '2023-06-05', '2023-06-07', 6, 30000.00, 5000.00, 'completed', 'paid', 'Business meeting arrangement');

-- =============================================
-- 11. SAMPLE RESTAURANT RESERVATIONS SEED DATA
-- =============================================

INSERT INTO restaurant_reservations (
    user_id, table_id, reservation_reference, reservation_date, 
    reservation_time, number_of_guests, reservation_status, 
    special_requests, deposit_amount, deposit_paid, points_earned
) VALUES 
-- Mia Cruz's reservations
(1, 3, 'RSV20240316001', '2024-03-16', '19:30:00', 4, 'completed', 'Birthday celebration', 200.00, TRUE, 10),
(1, NULL, 'RSV20240320001', '2024-03-20', '20:00:00', 2, 'confirmed', 'Anniversary dinner', 0.00, FALSE, 0),

-- Juan Mateo's reservations
(2, 4, 'RSV20231111001', '2023-11-11', '19:00:00', 2, 'completed', 'Business dinner', 150.00, TRUE, 7),

-- Sofia Reyes's reservations
(3, NULL, 'RSV20240120001', '2024-01-20', '18:30:00', 3, 'completed', 'Family dinner', 150.00, TRUE, 7);

-- =============================================
-- 12. SAMPLE FOOD ORDERS SEED DATA
-- =============================================

INSERT INTO food_orders (
    user_id, order_reference, order_type, order_status, total_amount, 
    points_earned, delivery_room_number, special_instructions, created_at
) VALUES 
-- Mia Cruz's orders
(1, 'ORD20240317001', 'dine_in', 'completed', 660.00, 33, NULL, 'Extra spicy sisig please', '2024-03-17 20:15:00'),
(1, 'ORD20240318001', 'room_delivery', 'completed', 280.00, 14, '101', 'No ice in drinks', '2024-03-18 22:30:00'),

-- Juan Mateo's orders
(2, 'ORD20231111001', 'dine_in', 'completed', 450.00, 22, NULL, 'Well-done salmon please', '2023-11-11 19:00:00'),

-- Carlos Santos's orders
(4, 'ORD20230606001', 'takeaway', 'completed', 980.00, 49, NULL, 'Separate containers requested', '2023-06-06 20:00:00');

-- =============================================
-- 13. FOOD ORDER ITEMS SEED DATA
-- =============================================

INSERT INTO food_order_items (
    order_id, item_id, quantity, unit_price, subtotal, special_instructions, item_status
) VALUES 
-- Order ORD20240317001 items (Mia Cruz - Sinigang, Sisig, Rice)
(1, 8, 1, 320.00, 320.00, NULL, 'served'),
(1, 9, 1, 290.00, 290.00, 'Extra spicy', 'served'),
(1, 11, 1, 50.00, 50.00, NULL, 'served'),

-- Order ORD20240318001 items (Mia Cruz - Calamares, Coffee)
(2, 7, 1, 280.00, 280.00, NULL, 'served'),

-- Order ORD20231111001 items (Juan Mateo - Salmon, Salad)
(3, 12, 1, 680.00, 680.00, 'Well-done', 'served'),
(3, 18, 1, 180.00, 180.00, NULL, 'served'),

-- Order ORD20230606001 items (Carlos Santos - multiple items)
(4, 8, 2, 320.00, 640.00, NULL, 'served'),
(4, 9, 1, 290.00, 290.00, NULL, 'served'),
(4, 15, 1, 50.00, 50.00, NULL, 'served');

-- =============================================
-- 14. SAMPLE NOTIFICATIONS SEED DATA
-- =============================================

INSERT INTO notifications (
    user_id, notification_type, title, message, is_read, priority_level, 
    action_url, action_text, related_entity_type, related_entity_id, created_at
) VALUES 
-- Mia Cruz's notifications
(1, 'booking', 'Hotel Booking Confirmed', 'Your hotel booking HBK20240315001 has been confirmed. Check-in: March 15, 2024', FALSE, 'high', 'my_reservation.html', 'View Booking', 'hotel_booking', 1, '2024-03-15 10:35:00'),
(1, 'loyalty', 'Points Earned', 'You earned 33 points from your recent food order!', FALSE, 'medium', 'loyalty_rewards.html', 'View Rewards', 'food_order', 1, '2024-03-17 20:20:00'),
(1, 'promo', 'Special Weekend Offer', 'Get 20% off on all weekend dining reservations this month!', FALSE, 'low', 'restaurant_reservation.html', 'Book Now', NULL, NULL, '2024-03-18 09:00:00'),

-- Juan Mateo's notifications
(2, 'booking', 'Ocean Suite Available', 'Your preferred ocean suite is available for your dates!', TRUE, 'medium', 'hotel_booking.html', 'Book Now', NULL, NULL, '2023-11-09 14:00:00'),
(2, 'payment', 'Payment Successful', 'Your payment of ₱6,900 for booking HBK20231110001 has been processed.', TRUE, 'high', 'payments.html', 'View Receipt', 'hotel_booking', 2, '2023-11-10 14:25:00'),

-- Sofia Reyes's notifications
(3, 'reminder', 'Reservation Tomorrow', 'Don\'t forget about your restaurant reservation tomorrow at 6:30 PM.', FALSE, 'medium', 'restaurant_reservation.html', 'View Details', 'restaurant_reservation', 2, '2024-01-19 18:00:00'),
(3, 'loyalty', 'Welcome to Lùcas', 'Welcome! You earned 50 bonus points for joining our loyalty program.', TRUE, 'high', 'loyalty_rewards.html', 'View Points', NULL, NULL, '2024-01-20 09:00:00'),

-- Carlos Santos's notifications
(4, 'system', 'Platinum Benefits', 'As a Platinum member, enjoy exclusive benefits and priority service.', TRUE, 'medium', 'loyalty_rewards.html', 'Learn More', NULL, NULL, '2023-06-05 16:50:00'),
(4, 'promo', 'VIP Event Invitation', 'You\'re invited to our exclusive wine tasting event this Friday.', FALSE, 'high', NULL, 'RSVP Now', NULL, NULL, '2023-06-06 10:00:00');

-- =============================================
-- 15. SAMPLE POINTS HISTORY SEED DATA
-- =============================================

INSERT INTO points_history (
    user_id, points_change, points_balance_after, transaction_type, 
    source_type, source_id, description, created_at
) VALUES 
-- Mia Cruz's points history
(1, 50, 50, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', '2024-03-15 09:00:00'),
(1, 210, 260, 'earn', 'hotel_stay', 1, 'Points earned from hotel booking', '2024-03-15 10:30:00'),
(1, 10, 270, 'earn', 'dining', 1, 'Points earned from restaurant reservation', '2024-03-16 19:45:00'),
(1, 33, 303, 'earn', 'dining', 1, 'Points earned from food order', '2024-03-17 20:15:00'),
(1, -240, 63, 'redeem', 'reward_redemption', 1, 'Points used for Free Coffee reward', '2024-03-19 15:30:00'),
(1, 1177, 1240, 'earn', 'manual_adjust', NULL, 'Points adjustment for loyalty tier upgrade', '2024-03-20 11:00:00'),

-- Juan Mateo's points history
(2, 25, 25, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', '2023-11-10 09:00:00'),
(2, 345, 370, 'earn', 'hotel_stay', 2, 'Points earned from hotel booking', '2023-11-10 14:20:00'),
(2, 22, 392, 'earn', 'dining', 2, 'Points earned from food order', '2023-11-11 19:00:00'),
(2, 288, 680, 'earn', 'promo', NULL, 'Promotional bonus points', '2023-11-15 10:00:00'),

-- Sofia Reyes's points history
(3, 50, 50, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', '2024-01-20 08:00:00'),
(3, 7, 57, 'earn', 'dining', 2, 'Points earned from restaurant reservation', '2024-01-20 18:30:00'),
(3, 93, 150, 'earn', 'manual_adjust', NULL, 'Welcome bonus adjustment', '2024-01-21 09:00:00'),

-- Carlos Santos's points history
(2, 100, 100, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', '2023-06-05 08:00:00'),
(4, 750, 850, 'earn', 'hotel_stay', 3, 'Points earned from presidential suite booking', '2023-06-05 16:45:00'),
(4, 49, 899, 'earn', 'dining', 3, 'Points earned from food order', '2023-06-06 20:00:00'),
(4, 1251, 2150, 'earn', 'manual_adjust', NULL, 'Platinum tier bonus points', '2023-06-10 11:00:00');

-- =============================================
-- 16. SAMPLE USER REVIEWS SEED DATA
-- =============================================

INSERT INTO user_reviews (
    user_id, review_type, related_entity_id, rating, review_title, 
    review_text, review_status, created_at
) VALUES 
-- Mia Cruz's reviews
(1, 'hotel_stay', 1, 5, 'Excellent Stay!', 'The deluxe twin room was perfect for our weekend getaway. Clean, comfortable, and great service!', 'approved', '2024-03-17 11:00:00'),
(1, 'dining_experience', 1, 4, 'Great Food', 'Sinigang was authentic and delicious. Sisig was perfectly spicy. Will definitely come back!', 'approved', '2024-03-18 10:30:00'),

-- Juan Mateo's reviews
(2, 'hotel_stay', 2, 5, 'Luxurious Experience', 'The ocean suite exceeded our expectations. The view was breathtaking and the amenities were top-notch.', 'approved', '2023-11-12 09:00:00'),
(2, 'dining_experience', 2, 5, 'Perfect Dinner', 'Grilled salmon was cooked to perfection. Great ambiance for our business dinner.', 'approved', '2023-11-12 18:00:00'),

-- Carlos Santos's reviews
(4, 'hotel_stay', 3, 5, 'Ultimate Luxury', 'Presidential suite was absolutely amazing! Butler service was impeccable. Worth every penny!', 'approved', '2023-06-07 10:00:00'),
(4, 'food_item', 8, 5, 'Best Sinigang Ever!', 'Authentic Filipino sinigang that reminds me of home. Perfect sourness and generous servings.', 'approved', '2023-06-08 14:00:00');

-- =============================================
-- 17. SAMPLE REWARD REDEMPTIONS SEED DATA
-- =============================================

INSERT INTO user_reward_redemptions (
    user_id, reward_id, redemption_reference, points_used, 
    redemption_status, usage_date, notes
) VALUES 
-- Mia Cruz's redemptions
(1, 1, 'RDM20240319001', 240, 'used', '2024-03-19 15:30:00', 'Claimed free coffee at Azure Lounge'),
(1, 7, 'RDM20240320001', 150, 'pending', NULL, 'Free Halo-Halo - pending redemption'),

-- Juan Mateo's redemptions
(2, 4, 'RDM20231115001', 360, 'used', '2023-11-15 19:00:00', 'Used welcome drinks for anniversary dinner'),

-- Carlos Santos's redemptions
(4, 5, 'RDM20230608001', 1200, 'used', '2023-06-08 14:00:00', 'Upgraded to ocean suite for next stay');

-- =============================================
-- 18. SAMPLE WAITING LIST SEED DATA
-- =============================================

INSERT INTO waiting_list (
    user_id, waiting_type, party_size, estimated_wait_time_minutes, 
    priority_level, contact_method, special_requests, waiting_status
) VALUES 
-- Current waiting list entries
(3, 'restaurant_table', 4, 20, 'normal', 'sms', 'Prefer window seat if available', 'waiting'),
(1, 'restaurant_table', 2, 15, 'vip', 'sms', 'Celebrating anniversary', 'notified');

-- =============================================
-- UPDATE AUTO_INCREMENT VALUES
-- =============================================

-- Set auto_increment starting values for clean development
ALTER TABLE users AUTO_INCREMENT = 100;
ALTER TABLE hotel_rooms AUTO_INCREMENT = 100;
ALTER TABLE hotel_bookings AUTO_INCREMENT = 1000;
ALTER TABLE restaurant_tables AUTO_INCREMENT = 100;
ALTER TABLE restaurant_reservations AUTO_INCREMENT = 1000;
ALTER TABLE menu_categories AUTO_INCREMENT = 100;
ALTER TABLE menu_items AUTO_INCREMENT = 100;
ALTER TABLE food_orders AUTO_INCREMENT = 1000;
ALTER TABLE food_order_items AUTO_INCREMENT = 1000;
ALTER TABLE payment_methods AUTO_INCREMENT = 100;
ALTER TABLE transactions AUTO_INCREMENT = 10000;
ALTER TABLE loyalty_rewards AUTO_INCREMENT = 100;
ALTER TABLE user_reward_redemptions AUTO_INCREMENT = 1000;
ALTER TABLE notifications AUTO_INCREMENT = 1000;
ALTER TABLE user_notification_preferences AUTO_INCREMENT = 100;
ALTER TABLE user_reviews AUTO_INCREMENT = 100;
ALTER TABLE points_history AUTO_INCREMENT = 1000;
ALTER TABLE waiting_list AUTO_INCREMENT = 100;
ALTER TABLE user_sessions AUTO_INCREMENT = 1000;
ALTER TABLE audit_log AUTO_INCREMENT = 1000;
