<?php
/**
 * Create Missing loyalty_redemptions Table
 * Run this script to fix the database error
 */

require_once 'config/database.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

echo "<h1>Creating Missing loyalty_redemptions Table</h1>";

// Check if loyalty_redemptions table already exists
$checkTable = $db->query("SHOW TABLES LIKE 'loyalty_redemptions'");
if ($checkTable->rowCount() > 0) {
    echo "<p style='color: blue;'>loyalty_redemptions table already exists!</p>";
    
    // Show current table structure
    $result = $db->query("DESCRIBE loyalty_redemptions");
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
    echo "<p>Creating loyalty_redemptions table...</p>";
    
    try {
        // Create the loyalty_redemptions table
        $sql = "CREATE TABLE loyalty_redemptions (
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
        )";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ loyalty_redemptions table created successfully!</p>";
        
        // Show created table structure
        $result = $db->query("DESCRIBE loyalty_redemptions");
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
    
    // Test getUserRedemptions method
    $userId = 104; // From the error message
    $userRedemptions = $userModel->getUserRedemptions($userId, 10);
    
    echo "<p>✓ getUserRedemptions() method works!</p>";
    echo "<p>Found " . count($userRedemptions) . " redemptions for user $userId</p>";
    
    if (!empty($userRedemptions)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Redemption ID</th><th>Reference</th><th>Points Used</th><th>Status</th><th>Redemption Date</th></tr>";
        foreach ($userRedemptions as $redemption) {
            echo "<tr>";
            echo "<td>{$redemption['redemption_id']}</td>";
            echo "<td>{$redemption['redemption_reference']}</td>";
            echo "<td>{$redemption['points_used']}</td>";
            echo "<td>{$redemption['redemption_status']}</td>";
            echo "<td>{$redemption['redemption_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No redemptions found (table might be empty).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing User model: " . $e->getMessage() . "</p>";
}

echo "<p><a href='loyalty_rewards.php'>Go to Loyalty Rewards Page</a> to test the fix!</p>";
?>
