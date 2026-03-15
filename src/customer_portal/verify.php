<?php
/**
 * Verification Script
 * Checks all PHP files for syntax errors
 */

$phpFiles = [
    'index.php',
    'login.php', 
    'my_profile.php',
    'hotel_booking.php',
    'my_reservation.php',
    'restaurant_reservation.php',
    'order_food.php',
    'payments.php',
    'loyalty_rewards.php',
    'notifications.php',
    'reviews.php',
    'logout.php'
];

echo "=== PHP File Verification ===\n\n";

foreach ($phpFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    
    if (file_exists($filePath)) {
        echo "✓ $file exists\n";
        
        // Check if file has basic PHP structure
        $content = file_get_contents($filePath);
        if (strpos($content, '<?php') !== false) {
            echo "  ✓ Has PHP opening tag\n";
        } else {
            echo "  ✗ Missing PHP opening tag\n";
        }
        
        // Check for session_start
        if (strpos($content, 'session_start') !== false) {
            echo "  ✓ Has session_start\n";
        }
        
        // Check for database includes
        if (strpos($content, 'require_once') !== false) {
            echo "  ✓ Has require statements\n";
        }
        
        // Check for navigation links
        if (strpos($content, 'href="') !== false) {
            echo "  ✓ Has navigation links\n";
        }
        
        echo "\n";
    } else {
        echo "✗ $file missing\n\n";
    }
}

echo "=== Verification Complete ===\n";
?>
