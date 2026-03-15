<?php
/**
 * Booking API Endpoints
 * Handles hotel booking and restaurant reservation requests
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Display errors temporarily

require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/SessionManager.php';
require_once '../../helpers/api_helpers.php';

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
$pathParts = explode('/', trim($path, '/'));

// Get endpoint from query parameter or URL path
$endpoint = $_GET['endpoint'] ?? end($pathParts);

/**
 * Get available hotel rooms
 */
function handleHotelRooms($method, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $checkIn = $_GET['check_in'] ?? null;
    $checkOut = $_GET['check_out'] ?? null;
    $roomType = $_GET['room_type'] ?? null;
    
    $rooms = getAvailableRooms($GLOBALS['db'], $checkIn, $checkOut);
    
    // Filter by room type if specified
    if ($roomType) {
        $rooms = array_filter($rooms, function($room) use ($roomType) {
            return $room['room_type'] === $roomType;
        });
    }
    
    successResponse('Available rooms retrieved successfully', array_values($rooms));
}

try {
    switch ($endpoint) {
        case 'hotel-rooms':
            handleHotelRooms($method, $currentUser);
            break;
            
        case 'create-hotel-booking':
            handleCreateHotelBooking($method, $currentUser, $db);
            break;
            
        case 'restaurant-tables':
            handleRestaurantTables($method, $currentUser);
            break;
            
        case 'create-restaurant-reservation':
            handleCreateRestaurantReservation($method, $currentUser, $db);
            break;
            
        case 'menu-categories':
            handleMenuCategories($method, $currentUser);
            break;
            
        case 'menu-items':
            handleMenuItems($method, $currentUser);
            break;
            
        case 'create-food-order':
            handleCreateFoodOrder($method, $currentUser, $db);
            break;
            
        case 'waiting-list':
            handleWaitingList($method, $currentUser);
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
 * Get available hotel rooms
 */
function handleHotelRooms($method, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $checkIn = $_GET['check_in'] ?? null;
    $checkOut = $_GET['check_out'] ?? null;
    $roomType = $_GET['room_type'] ?? null;
    
    $rooms = getAvailableRooms($GLOBALS['db'], $checkIn, $checkOut);
    
    // Filter by room type if specified
    if ($roomType) {
        $rooms = array_filter($rooms, function($room) use ($roomType) {
            return $room['room_type'] === $roomType;
        });
    }
    
    successResponse('Available rooms retrieved successfully', array_values($rooms));
}

/**
 * Create hotel booking
 */
function handleCreateHotelBooking($method, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['room_id', 'check_in_date', 'check_out_date', 'number_of_guests']);
    
    // Validate dates
    $checkIn = new DateTime($input['check_in_date']);
    $checkOut = new DateTime($input['check_out_date']);
    $today = new DateTime();
    
    if ($checkIn < $today) {
        errorResponse('Check-in date cannot be in the past');
    }
    
    if ($checkOut <= $checkIn) {
        errorResponse('Check-out date must be after check-in date');
    }
    
    // Get room details
    $stmt = $GLOBALS['db']->prepare("SELECT * FROM hotel_rooms WHERE room_id = ? AND room_status = 'available'");
    $stmt->execute([$input['room_id']]);
    $room = $stmt->fetch();
    
    if (!$room) {
        errorResponse('Room not available');
    }
    
    // Check room availability for dates
    $stmt = $GLOBALS['db']->prepare("SELECT COUNT(*) as conflicts FROM hotel_bookings 
                                    WHERE room_id = ? AND booking_status IN ('confirmed', 'checked_in')
                                    AND (
                                        (check_in_date <= ? AND check_out_date > ?) OR
                                        (check_in_date < ? AND check_out_date >= ?)
                                    )");
    $stmt->execute([
        $input['room_id'],
        $input['check_out_date'], $input['check_in_date'],
        $input['check_out_date'], $input['check_in_date']
    ]);
    $conflicts = $stmt->fetch()['conflicts'];
    
    if ($conflicts > 0) {
        errorResponse('Room not available for selected dates');
    }
    
    // Calculate total amount
    $nights = $checkIn->diff($checkOut)->days;
    $totalAmount = $nights * $room['base_price_per_night'];
    $depositAmount = $totalAmount * 0.3; // 30% deposit
    
    $GLOBALS['db']->beginTransaction();
    
    try {
        // Create booking
        $bookingReference = generateBookingReference();
        $stmt = $GLOBALS['db']->prepare("INSERT INTO hotel_bookings 
                                        (user_id, room_id, booking_reference, check_in_date, check_out_date, 
                                         number_of_guests, total_amount, deposit_amount, booking_status, payment_status, special_requests)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'pending', ?)");
        
        $stmt->execute([
            $currentUser['user_id'],
            $input['room_id'],
            $bookingReference,
            $input['check_in_date'],
            $input['check_out_date'],
            $input['number_of_guests'],
            $totalAmount,
            $depositAmount,
            $input['special_requests'] ?? null
        ]);
        
        $bookingId = $GLOBALS['db']->lastInsertId();
        
        // Create transaction record
        $transactionReference = 'TXN' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $stmt = $GLOBALS['db']->prepare("INSERT INTO transactions 
                                        (user_id, transaction_reference, transaction_type, related_entity_type, 
                                         related_entity_id, amount, transaction_status, points_earned, transaction_description)
                                        VALUES (?, ?, 'payment', 'hotel_booking', ?, ?, 'pending', ?, ?)");
        
        $pointsEarned = calculateLoyaltyPoints($totalAmount, 'hotel_stay');
        $stmt->execute([
            $currentUser['user_id'],
            $transactionReference,
            $bookingId,
            $depositAmount,
            $pointsEarned,
            "Hotel booking deposit - {$room['room_number']}"
        ]);
        
        // Send notification
        sendNotification(
            $GLOBALS['db'],
            $currentUser['user_id'],
            'booking',
            'Hotel Booking Confirmed',
            "Your hotel booking {$bookingReference} has been confirmed. Check-in: {$input['check_in_date']}",
            'my_reservation.php',
            'hotel_booking',
            $bookingId
        );
        
        $GLOBALS['db']->commit();
        
        successResponse('Hotel booking created successfully', [
            'booking_id' => $bookingId,
            'booking_reference' => $bookingReference,
            'total_amount' => $totalAmount,
            'deposit_amount' => $depositAmount,
            'transaction_reference' => $transactionReference
        ]);
        
    } catch (Exception $e) {
        $GLOBALS['db']->rollback();
        errorResponse('Failed to create booking: ' . $e->getMessage());
    }
}

/**
 * Get available restaurant tables
 */
function handleRestaurantTables($method, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $capacity = $_GET['capacity'] ?? null;
    $date = $_GET['date'] ?? date('Y-m-d');
    $time = $_GET['time'] ?? null;
    
    $tables = getAvailableTables($GLOBALS['db'], $capacity);
    
    // Filter tables that are already reserved for the same time
    if ($date && $time) {
        $stmt = $GLOBALS['db']->prepare("SELECT DISTINCT table_id FROM restaurant_reservations 
                                        WHERE reservation_date = ? AND reservation_time = ? 
                                        AND reservation_status IN ('confirmed', 'seated')");
        $stmt->execute([$date, $time]);
        $reservedTableIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        if (!empty($reservedTableIds)) {
            $tables = array_filter($tables, function($table) use ($reservedTableIds) {
                return !in_array($table['table_id'], $reservedTableIds);
            });
        }
    }
    
    successResponse('Available tables retrieved successfully', array_values($tables));
}

/**
 * Create restaurant reservation
 */
function handleCreateRestaurantReservation($method, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['reservation_date', 'reservation_time', 'number_of_guests']);
    
    // Validate date and time
    $reservationDateTime = new DateTime($input['reservation_date'] . ' ' . $input['reservation_time']);
    $now = new DateTime();
    
    if ($reservationDateTime < $now) {
        errorResponse('Reservation time cannot be in the past');
    }
    
    // Calculate deposit (₱100 per guest)
    $depositAmount = $input['number_of_guests'] * 100;
    
    // Find suitable table if not specified
    $tableId = $input['table_id'] ?? null;
    if (!$tableId) {
        $tables = getAvailableTables($GLOBALS['db'], $input['number_of_guests']);
        if (empty($tables)) {
            errorResponse('No suitable tables available for the requested party size');
        }
        $tableId = $tables[0]['table_id'];
    }
    
    $GLOBALS['db']->beginTransaction();
    
    try {
        // Create reservation
        $reservationReference = generateReservationReference();
        $stmt = $GLOBALS['db']->prepare("INSERT INTO restaurant_reservations 
                                        (user_id, table_id, reservation_reference, reservation_date, reservation_time, 
                                         number_of_guests, reservation_status, special_requests, deposit_amount, deposit_paid)
                                        VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, FALSE)");
        
        $stmt->execute([
            $currentUser['user_id'],
            $tableId,
            $reservationReference,
            $input['reservation_date'],
            $input['reservation_time'],
            $input['number_of_guests'],
            $input['special_requests'] ?? null,
            $depositAmount
        ]);
        
        $reservationId = $GLOBALS['db']->lastInsertId();
        
        // Create transaction record for deposit
        $transactionReference = 'TXN' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $stmt = $GLOBALS['db']->prepare("INSERT INTO transactions 
                                        (user_id, transaction_reference, transaction_type, related_entity_type, 
                                         related_entity_id, amount, transaction_status, points_earned, transaction_description)
                                        VALUES (?, ?, 'payment', 'restaurant_reservation', ?, ?, 'pending', ?, ?)");
        
        $pointsEarned = calculateLoyaltyPoints($depositAmount, 'dining');
        $stmt->execute([
            $currentUser['user_id'],
            $transactionReference,
            $reservationId,
            $depositAmount,
            $pointsEarned,
            "Restaurant reservation deposit - {$reservationReference}"
        ]);
        
        // Send notification
        sendNotification(
            $GLOBALS['db'],
            $currentUser['user_id'],
            'booking',
            'Restaurant Reservation Confirmed',
            "Your restaurant reservation {$reservationReference} has been confirmed for {$input['reservation_date']} at {$input['reservation_time']}",
            'my_reservation.php',
            'restaurant_reservation',
            $reservationId
        );
        
        $GLOBALS['db']->commit();
        
        successResponse('Restaurant reservation created successfully', [
            'reservation_id' => $reservationId,
            'reservation_reference' => $reservationReference,
            'table_id' => $tableId,
            'deposit_amount' => $depositAmount,
            'transaction_reference' => $transactionReference
        ]);
        
    } catch (Exception $e) {
        $GLOBALS['db']->rollback();
        errorResponse('Failed to create reservation: ' . $e->getMessage());
    }
}

/**
 * Get menu categories
 */
function handleMenuCategories($method, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $stmt = $GLOBALS['db']->prepare("SELECT * FROM menu_categories WHERE is_active = TRUE ORDER BY display_order");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    successResponse('Menu categories retrieved successfully', $categories);
}

/**
 * Get menu items
 */
function handleMenuItems($method, $currentUser) {
    if ($method !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $categoryId = $_GET['category_id'] ?? null;
    $items = getMenuItems($GLOBALS['db'], $categoryId);
    
    successResponse('Menu items retrieved successfully', $items);
}

/**
 * Create food order
 */
function handleCreateFoodOrder($method, $currentUser) {
    if ($method !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    validateRequired($input, ['order_type', 'items']);
    
    if (empty($input['items']) || !is_array($input['items'])) {
        errorResponse('Order items are required');
    }
    
    $GLOBALS['db']->beginTransaction();
    
    try {
        // Calculate total amount
        $totalAmount = 0;
        $orderItems = [];
        
        foreach ($input['items'] as $item) {
            validateRequired($item, ['item_id', 'quantity']);
            
            // Get menu item details
            $stmt = $GLOBALS['db']->prepare("SELECT * FROM menu_items WHERE item_id = ? AND item_status = 'available'");
            $stmt->execute([$item['item_id']]);
            $menuItem = $stmt->fetch();
            
            if (!$menuItem) {
                throw new Exception("Menu item {$item['item_id']} not available");
            }
            
            $subtotal = $menuItem['price'] * $item['quantity'];
            $totalAmount += $subtotal;
            
            $orderItems[] = [
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem['price'],
                'subtotal' => $subtotal,
                'special_instructions' => $item['special_instructions'] ?? null
            ];
        }
        
        // Create order
        $orderReference = generateOrderReference();
        $estimatedReadyTime = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        $stmt = $GLOBALS['db']->prepare("INSERT INTO food_orders 
                                        (user_id, order_reference, order_type, order_status, total_amount, 
                                         delivery_room_number, delivery_table_number, special_instructions, estimated_ready_time)
                                        VALUES (?, ?, ?, 'confirmed', ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $currentUser['user_id'],
            $orderReference,
            $input['order_type'],
            $totalAmount,
            $input['delivery_room_number'] ?? null,
            $input['delivery_table_number'] ?? null,
            $input['special_instructions'] ?? null,
            $estimatedReadyTime
        ]);
        
        $orderId = $GLOBALS['db']->lastInsertId();
        
        // Add order items
        $stmt = $GLOBALS['db']->prepare("INSERT INTO food_order_items 
                                        (order_id, item_id, quantity, unit_price, subtotal, special_instructions)
                                        VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($orderItems as $item) {
            $stmt->execute([
                $orderId,
                $item['item_id'],
                $item['quantity'],
                $item['unit_price'],
                $item['subtotal'],
                $item['special_instructions']
            ]);
        }
        
        // Create transaction record
        $transactionReference = 'TXN' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $pointsEarned = calculateLoyaltyPoints($totalAmount, 'dining');
        
        $stmt = $GLOBALS['db']->prepare("INSERT INTO transactions 
                                        (user_id, transaction_reference, transaction_type, related_entity_type, 
                                         related_entity_id, amount, transaction_status, points_earned, transaction_description)
                                        VALUES (?, ?, 'payment', 'food_order', ?, ?, 'pending', ?, ?)");
        
        $stmt->execute([
            $currentUser['user_id'],
            $transactionReference,
            $orderId,
            $totalAmount,
            $pointsEarned,
            "Food order - {$orderReference}"
        ]);
        
        // Send notification
        sendNotification(
            $GLOBALS['db'],
            $currentUser['user_id'],
            'booking',
            'Food Order Confirmed',
            "Your food order {$orderReference} has been confirmed. Estimated ready time: " . 
            date('g:i A', strtotime($estimatedReadyTime)),
            'order_food.php',
            'food_order',
            $orderId
        );
        
        $GLOBALS['db']->commit();
        
        successResponse('Food order created successfully', [
            'order_id' => $orderId,
            'order_reference' => $orderReference,
            'total_amount' => $totalAmount,
            'estimated_ready_time' => $estimatedReadyTime,
            'transaction_reference' => $transactionReference,
            'items' => $orderItems
        ]);
        
    } catch (Exception $e) {
        $GLOBALS['db']->rollback();
        errorResponse('Failed to create order: ' . $e->getMessage());
    }
}

/**
 * Handle waiting list
 */
function handleWaitingList($method, $currentUser) {
    if (!$currentUser) {
        errorResponse('Unauthorized', 401);
    }
    
    if ($method === 'GET') {
        // Get waiting list entries for current user
        $stmt = $GLOBALS['db']->prepare("SELECT * FROM waiting_list WHERE user_id = ? AND waiting_status = 'waiting' ORDER BY created_at");
        $stmt->execute([$currentUser['user_id']]);
        $waitingList = $stmt->fetchAll();
        
        successResponse('Waiting list retrieved successfully', $waitingList);
        
    } elseif ($method === 'POST') {
        // Add to waiting list
        $input = json_decode(file_get_contents('php://input'), true);
        validateRequired($input, ['waiting_type', 'party_size']);
        
        $stmt = $GLOBALS['db']->prepare("INSERT INTO waiting_list 
                                        (user_id, waiting_type, party_size, estimated_wait_time_minutes, 
                                         priority_level, contact_method, special_requests, waiting_status)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, 'waiting')");
        
        $result = $stmt->execute([
            $currentUser['user_id'],
            $input['waiting_type'],
            $input['party_size'],
            $input['estimated_wait_time_minutes'] ?? 30,
            $input['priority_level'] ?? 'normal',
            $input['contact_method'] ?? 'sms',
            $input['special_requests'] ?? null
        ]);
        
        if ($result) {
            successResponse('Added to waiting list successfully');
        } else {
            errorResponse('Failed to add to waiting list');
        }
        
    } else {
        errorResponse('Method not allowed', 405);
    }
}

/**
 * Get available hotel rooms
 */
function getAvailableRooms($db, $checkIn = null, $checkOut = null) {
    // For now, return static room data
    // In a real implementation, this would query the database for available rooms
    return [
        [
            'id' => '201',
            'name' => 'Deluxe Twin',
            'price' => 4200,
            'beds' => '2 single beds',
            'view' => 'city view',
            'amenity' => 'free WiFi',
            'available' => true
        ],
        [
            'id' => '202',
            'name' => 'Ocean Suite',
            'price' => 6900,
            'beds' => '1 king bed',
            'view' => 'ocean view',
            'amenity' => 'jacuzzi',
            'available' => true
        ],
        [
            'id' => '203',
            'name' => 'Superior Double',
            'price' => 3500,
            'beds' => 'double bed',
            'view' => 'city view',
            'amenity' => '',
            'available' => true
        ]
    ];
}
?>
