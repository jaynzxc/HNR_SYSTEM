<?php
/**
 * Add Missing is_active Column to loyalty_rewards Table
 * Run this script to fix the database error
 */

require_once 'config/database.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

echo "<h1>Adding Missing is_active Column to loyalty_rewards Table</h1>";

// Check if is_active column already exists
$checkColumn = $db->query("SHOW COLUMNS FROM loyalty_rewards LIKE 'is_active'");
if ($checkColumn->rowCount() > 0) {
    echo "<p style='color: blue;'>is_active column already exists in loyalty_rewards table!</p>";
    
    // Show current table structure
    $result = $db->query("DESCRIBE loyalty_rewards");
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
    echo "<p>Adding is_active column to loyalty_rewards table...</p>";
    
    try {
        // Add the is_active column
        $sql = "ALTER TABLE loyalty_rewards ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER reward_status";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ is_active column added successfully!</p>";
        
        // Add index for is_active
        $sql = "ALTER TABLE loyalty_rewards ADD INDEX idx_is_active (is_active)";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ is_active index added successfully!</p>";
        
        // Show updated table structure
        $result = $db->query("DESCRIBE loyalty_rewards");
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
    
    // Test getAvailableRewards method
    $availableRewards = $userModel->getAvailableRewards();
    
    echo "<p>✓ getAvailableRewards() method works!</p>";
    echo "<p>Found " . count($availableRewards) . " available rewards</p>";
    
    if (!empty($availableRewards)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Reward ID</th><th>Name</th><th>Points Cost</th><th>Status</th><th>Is Active</th></tr>";
        foreach ($availableRewards as $reward) {
            echo "<tr>";
            echo "<td>{$reward['reward_id']}</td>";
            echo "<td>{$reward['reward_name']}</td>";
            echo "<td>{$reward['points_cost']}</td>";
            echo "<td>{$reward['reward_status']}</td>";
            echo "<td>" . ($reward['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No rewards found (table might be empty).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing User model: " . $e->getMessage() . "</p>";
}

echo "<p><a href='loyalty_rewards.php'>Go to Loyalty Rewards Page</a> to test the fix!</p>";
?>
