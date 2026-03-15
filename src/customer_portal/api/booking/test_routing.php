<?php
// Test the exact booking.php endpoint routing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate the exact same environment as booking.php
$_GET['endpoint'] = 'hotel-rooms';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/booking/booking.php?endpoint=hotel-rooms';

// Start output buffering like booking.php
ob_start();

// Set headers like booking.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

echo "<h1>Testing Booking API Routing</h1>";

try {
    // Include the exact same files as booking.php
    require_once '../../config/database.php';
    require_once '../../models/User.php';
    require_once '../../models/SessionManager.php';
    require_once '../../helpers/api_helpers.php';
    
    echo "<p>✅ All includes loaded</p>";
    
    // Initialize session and database like booking.php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $sessionManager = new SessionManager($db);
    $userModel = new User($db);
    
    echo "<p>✅ Database and session initialized</p>";
    
    // Get current user like booking.php
    $currentUser = $sessionManager->getCurrentUser();
    
    echo "<p>✅ Current user retrieved: " . ($currentUser ? 'Yes' : 'No') . "</p>";
    
    // Get request method and path like booking.php
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // Get endpoint from query parameter or URL path like booking.php
    $endpoint = $_GET['endpoint'] ?? end($pathParts);
    
    echo "<p>✅ Method: $method, Endpoint: $endpoint</p>";
    
    // Test the switch statement logic
    echo "<h2>Testing Switch Logic:</h2>";
    
    switch ($endpoint) {
        case 'hotel-rooms':
            echo "<p>✅ Matched 'hotel-rooms' case</p>";
            
            // Call the actual function
            if (function_exists('handleHotelRooms')) {
                echo "<p>✅ handleHotelRooms function exists</p>";
                
                // Test the function call
                $checkIn = $_GET['check_in'] ?? null;
                $checkOut = $_GET['check_out'] ?? null;
                $roomType = $_GET['room_type'] ?? null;
                
                echo "<p>✅ Parameters: checkIn=$checkIn, checkOut=$checkOut, roomType=$roomType</p>";
                
                $rooms = getAvailableRooms($db, $checkIn, $checkOut);
                echo "<p>✅ getAvailableRooms returned " . count($rooms) . " rooms</p>";
                
                // Filter by room type if specified
                if ($roomType) {
                    $rooms = array_filter($rooms, function($room) use ($roomType) {
                        return strtolower($room['name']) === strtolower($roomType);
                    });
                }
                
                echo "<p>✅ Final rooms count: " . count($rooms) . "</p>";
                
                // Test successResponse
                successResponse('Available rooms retrieved successfully', array_values($rooms));
                echo "<p>✅ successResponse called</p>";
                
            } else {
                echo "<p>❌ handleHotelRooms function missing</p>";
            }
            break;
            
        default:
            echo "<p>❌ No matching case for endpoint: $endpoint</p>";
            errorResponse('Endpoint not found', 404);
    }
    
} catch (Exception $e) {
    echo "<p>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}

ob_end_flush();
?>
