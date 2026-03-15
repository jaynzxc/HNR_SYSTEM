<?php
/**
 * Create Missing Payments Table
 * Run this script to create the payments table that's causing the fatal error
 */

require_once 'config/database.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

echo "<h1>Creating Missing Payments Table</h1>";

// Check if payments table already exists
$checkTable = $db->query("SHOW TABLES LIKE 'payments'");
if ($checkTable->rowCount() > 0) {
    echo "<p style='color: blue;'>Payments table already exists!</p>";
    
    // Show table structure
    $result = $db->query("DESCRIBE payments");
    echo "<h2>Current Table Structure:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<p>Creating payments table...</p>";
    
    // Create the payments table
    $sql = "CREATE TABLE payments (
        payment_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        payment_reference VARCHAR(50) UNIQUE NOT NULL,
        payment_type ENUM('hotel_booking', 'restaurant_reservation', 'food_order', 'loyalty_reward') NOT NULL,
        related_entity_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method_id INT NULL,
        status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
        payment_gateway VARCHAR(50) NULL,
        gateway_transaction_id VARCHAR(100) NULL,
        processing_fee DECIMAL(8,2) DEFAULT 0.00,
        discount_amount DECIMAL(8,2) DEFAULT 0.00,
        tax_amount DECIMAL(8,2) DEFAULT 0.00,
        total_amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'PHP',
        due_date TIMESTAMP NULL,
        payment_description TEXT NULL,
        notes TEXT NULL,
        paid_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (payment_method_id) REFERENCES payment_methods(payment_method_id),
        INDEX idx_user_id (user_id),
        INDEX idx_payment_type (payment_type),
        INDEX idx_status (status),
        INDEX idx_related_entity (payment_type, related_entity_id),
        INDEX idx_payment_reference (payment_reference),
        INDEX idx_due_date (due_date),
        INDEX idx_created_at (created_at)
    )";
    
    try {
        $db->exec($sql);
        echo "<p style='color: green;'>✓ Payments table created successfully!</p>";
        
        // Verify table was created
        $result = $db->query("DESCRIBE payments");
        echo "<h2>New Table Structure:</h2>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error creating table: " . $e->getMessage() . "</p>";
    }
}

// Test the User model method that was failing
echo "<h2>Testing User Model Method</h2>";
try {
    require_once 'models/User.php';
    $userModel = new User($database);
    
    // Test with a sample user ID (you may need to adjust this)
    $userId = 104; // From the error message
    $pendingPayments = $userModel->getPendingPayments($userId);
    
    echo "<p>✓ getPendingPayments() method works!</p>";
    echo "<p>Found " . count($pendingPayments) . " pending payments for user $userId</p>";
    
    if (!empty($pendingPayments)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Payment ID</th><th>Reference</th><th>Amount</th><th>Status</th><th>Due Date</th></tr>";
        foreach ($pendingPayments as $payment) {
            echo "<tr>";
            echo "<td>{$payment['payment_id']}</td>";
            echo "<td>{$payment['payment_reference']}</td>";
            echo "<td>₱" . number_format($payment['amount'], 2) . "</td>";
            echo "<td>{$payment['status']}</td>";
            echo "<td>{$payment['due_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing User model: " . $e->getMessage() . "</p>";
}

echo "<p><a href='payments.php'>Go to Payments Page</a> to test the fix!</p>";
?>
