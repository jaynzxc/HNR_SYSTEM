<?php
/**
 * Add Missing payment_date Column
 * Run this script to add the payment_date column that's causing the error
 */

require_once 'config/database.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

echo "<h1>Adding Missing payment_date Column</h1>";

// Check if payment_date column already exists
$checkColumn = $db->query("SHOW COLUMNS FROM payments LIKE 'payment_date'");
if ($checkColumn->rowCount() > 0) {
    echo "<p style='color: blue;'>payment_date column already exists!</p>";
    
    // Show current table structure
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
    echo "<p>Adding payment_date column...</p>";
    
    try {
        // Add the payment_date column
        $sql = "ALTER TABLE payments ADD COLUMN payment_date TIMESTAMP NULL AFTER due_date";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ payment_date column added successfully!</p>";
        
        // Add index for payment_date
        $sql = "ALTER TABLE payments ADD INDEX idx_payment_date (payment_date)";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ payment_date index added successfully!</p>";
        
        // Show updated table structure
        $result = $db->query("DESCRIBE payments");
        echo "<h2>Updated Table Structure:</h2>";
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
        echo "<p style='color: red;'>✗ Error adding column: " . $e->getMessage() . "</p>";
    }
}

// Test the User model method that was failing
echo "<h2>Testing User Model Method</h2>";
try {
    require_once 'models/User.php';
    $userModel = new User($database);
    
    // Test with the same user ID from the error
    $userId = 104;
    $paymentHistory = $userModel->getPaymentHistory($userId, 20);
    
    echo "<p>✓ getPaymentHistory() method works!</p>";
    echo "<p>Found " . count($paymentHistory) . " payment records for user $userId</p>";
    
    if (!empty($paymentHistory)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Payment ID</th><th>Reference</th><th>Amount</th><th>Status</th><th>Payment Date</th><th>Created At</th></tr>";
        foreach ($paymentHistory as $payment) {
            echo "<tr>";
            echo "<td>{$payment['payment_id']}</td>";
            echo "<td>{$payment['payment_reference']}</td>";
            echo "<td>₱" . number_format($payment['amount'], 2) . "</td>";
            echo "<td>{$payment['status']}</td>";
            echo "<td>{$payment['payment_date']}</td>";
            echo "<td>{$payment['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No payment records found (expected for new table).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing User model: " . $e->getMessage() . "</p>";
}

echo "<p><a href='payments.php'>Go to Payments Page</a> to test the fix!</p>";
?>
