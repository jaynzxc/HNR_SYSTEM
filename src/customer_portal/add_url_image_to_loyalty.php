<?php
/**
 * Add url_image Column to loyalty_rewards Table
 * Run this script to add image support for loyalty rewards
 */

require_once 'config/database.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

echo "<h1>Adding url_image Column to loyalty_rewards Table</h1>";

// Check if url_image column already exists
$checkColumn = $db->query("SHOW COLUMNS FROM loyalty_rewards LIKE 'url_image'");
if ($checkColumn->rowCount() > 0) {
    echo "<p style='color: blue;'>url_image column already exists in loyalty_rewards table!</p>";
    
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
    echo "<p>Adding url_image column to loyalty_rewards table...</p>";
    
    try {
        // Add the url_image column
        $sql = "ALTER TABLE loyalty_rewards ADD COLUMN url_image VARCHAR(255) NULL AFTER reward_description";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ url_image column added successfully!</p>";
        
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
        
        // Add some sample image URLs for existing rewards
        echo "<h3>Adding Sample Images for Existing Rewards</h3>";
        
        $sampleImages = [
            'Free Coffee' => 'Menu Pics/Brewed Coffee.jpeg',
            'Free Dessert' => 'Menu Pics/Chocolate Cake.jpeg',
            'Room Upgrade' => 'Menu Pics/Crispy Pata.jpeg',
            'Free Appetizer' => 'Menu Pics/Lumping Shanghai.jpeg',
            'Discount Voucher' => 'Menu Pics/Garlic Rice.jpeg'
        ];
        
        foreach ($sampleImages as $rewardName => $imageUrl) {
            $sql = "UPDATE loyalty_rewards SET url_image = ? WHERE reward_name LIKE ?";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$imageUrl, "%{$rewardName}%"]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✓ Updated image for reward: $rewardName</p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error adding column: " . $e->getMessage() . "</p>";
    }
}

// Test the updated loyalty rewards functionality
echo "<h2>Testing Updated Loyalty Rewards</h2>";
try {
    require_once 'models/User.php';
    $userModel = new User($database);
    
    // Test getAvailableRewards method
    $availableRewards = $userModel->getAvailableRewards();
    
    echo "<p>✓ getAvailableRewards() method works!</p>";
    echo "<p>Found " . count($availableRewards) . " available rewards</p>";
    
    if (!empty($availableRewards)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Reward ID</th><th>Name</th><th>Points Cost</th><th>Status</th><th>Image URL</th></tr>";
        foreach ($availableRewards as $reward) {
            echo "<tr>";
            echo "<td>{$reward['reward_id']}</td>";
            echo "<td>{$reward['reward_name']}</td>";
            echo "<td>{$reward['points_cost']}</td>";
            echo "<td>{$reward['reward_status']}</td>";
            echo "<td>" . ($reward['url_image'] ?? 'No image') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No rewards found (table might be empty).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error testing User model: " . $e->getMessage() . "</p>";
}

echo "<p><a href='loyalty_rewards.php'>Go to Loyalty Rewards Page</a> to see the images!</p>";
?>
