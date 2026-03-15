<?php
/**
 * Fix Restaurant Reservation Payment Processing
 * This script addresses common issues with restaurant reservation payment flow
 */

require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/SessionManager.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();
$userModel = new User($database);

echo "<h1>Restaurant Reservation Payment Processing Fix</h1>";

// Check 1: Verify restaurant_reservations table structure
echo "<h2>1. Checking restaurant_reservations Table Structure</h2>";
try {
    $result = $db->query("DESCRIBE restaurant_reservations");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for required fields
    $requiredFields = ['reservation_id', 'user_id', 'reservation_status', 'deposit_amount', 'deposit_paid'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        $checkField = $db->query("SHOW COLUMNS FROM restaurant_reservations LIKE '$field'");
        if ($checkField->rowCount() == 0) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        echo "<p style='color: red;'>✗ Missing fields: " . implode(', ', $missingFields) . "</p>";
        
        // Add missing fields
        foreach ($missingFields as $field) {
            switch ($field) {
                case 'deposit_amount':
                    $sql = "ALTER TABLE restaurant_reservations ADD COLUMN deposit_amount DECIMAL(10,2) DEFAULT 0.00 AFTER occasion_type";
                    break;
                case 'deposit_paid':
                    $sql = "ALTER TABLE restaurant_reservations ADD COLUMN deposit_paid BOOLEAN DEFAULT FALSE AFTER deposit_amount";
                    break;
            }
            
            try {
                $db->exec($sql);
                echo "<p style='color: green;'>✓ Added missing field: $field</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Failed to add $field: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✓ All required fields present</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error checking table structure: " . $e->getMessage() . "</p>";
}

// Check 2: Verify payments table structure
echo "<h2>2. Checking payments Table Structure</h2>";
try {
    $result = $db->query("SHOW TABLES LIKE 'payments'");
    if ($result->rowCount() > 0) {
        echo "<p style='color: green;'>✓ payments table exists</p>";
        
        $result = $db->query("DESCRIBE payments");
        $paymentFields = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $paymentFields[] = $row['Field'];
        }
        
        $requiredPaymentFields = ['payment_id', 'payment_type', 'related_entity_id', 'amount', 'status'];
        $missingPaymentFields = [];
        
        foreach ($requiredPaymentFields as $field) {
            if (!in_array($field, $paymentFields)) {
                $missingPaymentFields[] = $field;
            }
        }
        
        if (!empty($missingPaymentFields)) {
            echo "<p style='color: red;'>✗ Missing payment fields: " . implode(', ', $missingPaymentFields) . "</p>";
        } else {
            echo "<p style='color: green;'>✓ All required payment fields present</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ payments table does not exist</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error checking payments table: " . $e->getMessage() . "</p>";
}

// Check 3: Test restaurant reservation creation
echo "<h2>3. Testing Restaurant Reservation Creation</h2>";
try {
    // Create a test reservation
    $testReservationData = [
        'reservation_date' => date('Y-m-d', strtotime('+7 days')),
        'reservation_time' => '19:00:00',
        'number_of_guests' => 4,
        'special_requests' => 'Test reservation for payment processing'
    ];
    
    // Get a test user (user_id = 1 or create one)
    $testUserId = 1;
    $checkUser = $db->query("SELECT user_id FROM users WHERE user_id = $testUserId");
    if ($checkUser->rowCount() == 0) {
        echo "<p style='color: amber;'>⚠ Test user not found, using existing user</p>";
        $getUser = $db->query("SELECT user_id FROM users LIMIT 1");
        if ($getUser->rowCount() > 0) {
            $testUserId = $getUser->fetch(PDO::FETCH_ASSOC)['user_id'];
        } else {
            echo "<p style='color: red;'>✗ No users found in database</p>";
            exit;
        }
    }
    
    $result = $userModel->createRestaurantReservation($testUserId, $testReservationData);
    
    if ($result) {
        $reservationId = $db->lastInsertId();
        echo "<p style='color: green;'>✓ Test reservation created successfully (ID: $reservationId)</p>";
        
        // Test payment processing
        echo "<h3>Testing Payment Processing</h3>";
        
        $reservationFee = 500;
        $description = "Restaurant Reservation - Table for {$testReservationData['number_of_guests']} guests";
        $paymentReference = 'PAY' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        
        // Create payment record
        $sql = "INSERT INTO payments (user_id, payment_reference, payment_type, related_entity_id, amount, payment_method_id, status, payment_gateway, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, CURRENT_TIMESTAMP)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $testUserId,
            $paymentReference,
            'restaurant_reservation',
            $reservationId,
            $reservationFee,
            null,
            'cash'
        ]);
        
        if ($result) {
            $paymentId = $db->lastInsertId();
            echo "<p style='color: green;'>✓ Payment record created successfully (ID: $paymentId)</p>";
            
            // Test payment processing
            $transactionId = 'CASH' . time();
            $sql = "UPDATE payments SET status = 'completed', paid_at = CURRENT_TIMESTAMP, gateway_transaction_id = ? WHERE payment_id = ?";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$transactionId, $paymentId]);
            
            if ($result) {
                echo "<p style='color: green;'>✓ Payment processed successfully</p>";
                
                // Update reservation status
                $sql = "UPDATE restaurant_reservations SET reservation_status = 'confirmed', deposit_paid = TRUE, deposit_amount = ? WHERE reservation_id = ?";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([$reservationFee, $reservationId]);
                
                if ($result) {
                    echo "<p style='color: green;'>✓ Reservation status updated to confirmed</p>";
                    
                    // Verify the final state
                    $sql = "SELECT rr.*, p.payment_reference, p.amount, p.status as payment_status 
                            FROM restaurant_reservations rr 
                            JOIN payments p ON p.related_entity_id = rr.reservation_id AND p.payment_type = 'restaurant_reservation'
                            WHERE rr.reservation_id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$reservationId]);
                    $finalState = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($finalState) {
                        echo "<h4>Final Reservation State:</h4>";
                        echo "<table border='1' style='border-collapse: collapse;'>";
                        echo "<tr><th>Reservation ID</th><th>Status</th><th>Deposit Paid</th><th>Payment Ref</th><th>Amount</th><th>Payment Status</th></tr>";
                        echo "<tr>";
                        echo "<td>{$finalState['reservation_id']}</td>";
                        echo "<td>{$finalState['reservation_status']}</td>";
                        echo "<td>" . ($finalState['deposit_paid'] ? 'Yes' : 'No') . "</td>";
                        echo "<td>{$finalState['payment_reference']}</td>";
                        echo "<td>₱" . number_format($finalState['amount'], 2) . "</td>";
                        echo "<td>{$finalState['payment_status']}</td>";
                        echo "</tr>";
                        echo "</table>";
                        
                        // Clean up test data
                        $db->exec("DELETE FROM payments WHERE payment_id = $paymentId");
                        $db->exec("DELETE FROM restaurant_reservations WHERE reservation_id = $reservationId");
                        echo "<p style='color: blue;'>🧹 Test data cleaned up</p>";
                    }
                } else {
                    echo "<p style='color: red;'>✗ Failed to update reservation status</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Failed to process payment</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Failed to create payment record</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Failed to create test reservation</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing reservation creation: " . $e->getMessage() . "</p>";
}

// Check 4: Verify payment success page
echo "<h2>4. Checking Payment Success Page</h2>";
if (file_exists('payment_success.php')) {
    echo "<p style='color: green;'>✓ payment_success.php exists</p>";
} else {
    echo "<p style='color: red;'>✗ payment_success.php missing</p>";
}

// Check 5: Verify payment processing page
echo "<h2>5. Checking Payment Processing Page</h2>";
if (file_exists('payment_process.php')) {
    echo "<p style='color: green;'>✓ payment_process.php exists</p>";
} else {
    echo "<p style='color: red;'>✗ payment_process.php missing</p>";
}

echo "<h2>Summary</h2>";
echo "<p><a href='restaurant_reservation.php'>Test Restaurant Reservation</a></p>";
echo "<p><a href='payment_process.php'>Test Payment Processing</a></p>";
echo "<p><a href='payment_success.php'>Test Payment Success</a></p>";
?>
