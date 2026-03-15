<?php
/**
 * Add Corresponding Images to Loyalty Rewards
 * This script will update loyalty_rewards with images from the Loyalty directory
 */

require_once 'config/database.php';
require_once 'models/User.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

echo "<h1>Add Corresponding Images to Loyalty Rewards</h1>";

// Get current loyalty rewards
$sql = "SELECT * FROM loyalty_rewards ORDER BY reward_id";
$stmt = $db->prepare($sql);
$stmt->execute();
$rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Current Loyalty Rewards:</h2>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Reward Name</th><th>Current Image</th><th>Matched Image</th><th>Action</th></tr>";

// Available images in Loyalty directory
$availableImages = [
    'Coffee or Tea.jpg' => 'Loyalty/Coffee or Tea.jpg',
    'Complimentary_Breakfast.jpg' => 'Loyalty/Complimentary_Breakfast.jpg',
    'Discount.jpg' => 'Loyalty/Discount.jpg',
    'Halohalo.jpg' => 'Loyalty/Halohalo.jpg',
    'Welcome_drinks.jpg' => 'Loyalty/Welcome_drinks.jpg'
];

$imageMapping = [];
$updatedCount = 0;

foreach ($rewards as $reward) {
    $rewardId = $reward['reward_id'];
    $rewardName = strtolower($reward['reward_name']);
    $currentImage = $reward['url_image'] ?? 'None';
    $matchedImage = 'No match';
    $action = 'No action';
    
    // Try to find matching image
    foreach ($availableImages as $imageName => $imagePath) {
        $imageNameLower = strtolower(str_replace('_', ' ', $imageName));
        
        // Check for partial matches
        if (strpos($rewardName, 'coffee') !== false || strpos($rewardName, 'tea') !== false) {
            if (strpos($imageNameLower, 'coffee') !== false || strpos($imageNameLower, 'tea') !== false) {
                $matchedImage = $imageName;
                $imagePath = $availableImages[$imageName];
                $action = 'Update';
                break;
            }
        } elseif (strpos($rewardName, 'breakfast') !== false) {
            if (strpos($imageNameLower, 'breakfast') !== false) {
                $matchedImage = $imageName;
                $imagePath = $availableImages[$imageName];
                $action = 'Update';
                break;
            }
        } elseif (strpos($rewardName, 'discount') !== false) {
            if (strpos($imageNameLower, 'discount') !== false) {
                $matchedImage = $imageName;
                $imagePath = $availableImages[$imageName];
                $action = 'Update';
                break;
            }
        } elseif (strpos($rewardName, 'halo') !== false || strpos($rewardName, 'halohalo') !== false) {
            if (strpos($imageNameLower, 'halo') !== false) {
                $matchedImage = $imageName;
                $imagePath = $availableImages[$imageName];
                $action = 'Update';
                break;
            }
        } elseif (strpos($rewardName, 'drink') !== false || strpos($rewardName, 'welcome') !== false) {
            if (strpos($imageNameLower, 'drink') !== false || strpos($imageNameLower, 'welcome') !== false) {
                $matchedImage = $imageName;
                $imagePath = $availableImages[$imageName];
                $action = 'Update';
                break;
            }
        }
    }
    
    // Update database if we found a match
    if ($action === 'Update' && $currentImage === 'None') {
        $sql = "UPDATE loyalty_rewards SET url_image = ? WHERE reward_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$imagePath, $rewardId]);
        
        if ($result) {
            $currentImage = $imagePath;
            $action = '<span style="color: green;">✓ Updated</span>';
            $updatedCount++;
        } else {
            $action = '<span style="color: red;">✗ Failed</span>';
        }
    } elseif ($currentImage !== 'None') {
        $action = '<span style="color: blue;">Already has image</span>';
    }
    
    echo "<tr>";
    echo "<td>{$rewardId}</td>";
    echo "<td>" . htmlspecialchars($reward['reward_name']) . "</td>";
    echo "<td>{$currentImage}</td>";
    echo "<td>{$matchedImage}</td>";
    echo "<td>{$action}</td>";
    echo "</tr>";
    
    $imageMapping[$rewardId] = [
        'reward_name' => $reward['reward_name'],
        'image_path' => $currentImage !== 'None' ? $currentImage : null,
        'action' => $action
    ];
}

echo "</table>";

echo "<h2>Summary:</h2>";
echo "<p>Total rewards found: " . count($rewards) . "</p>";
echo "<p>Rewards updated: {$updatedCount}</p>";
echo "<p>Available images: " . count($availableImages) . "</p>";

// Show the final results
echo "<h2>Final Results:</h2>";
$sql = "SELECT reward_id, reward_name, url_image FROM loyalty_rewards ORDER BY reward_id";
$stmt = $db->prepare($sql);
$stmt->execute();
$finalRewards = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Reward ID</th><th>Reward Name</th><th>Image URL</th><th>Status</th></tr>";

foreach ($finalRewards as $reward) {
    $status = $reward['url_image'] ? 
        '<span style="color: green;">✓ Has image</span>' : 
        '<span style="color: red;">✗ No image</span>';
    
    echo "<tr>";
    echo "<td>{$reward['reward_id']}</td>";
    echo "<td>" . htmlspecialchars($reward['reward_name']) . "</td>";
    echo "<td>" . ($reward['url_image'] ?? 'None') . "</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}

echo "</table>";

// Create sample rewards if table is empty
if (empty($rewards)) {
    echo "<h2>Creating Sample Rewards with Images</h2>";
    
    $sampleRewards = [
        [
            'reward_name' => 'Free Coffee or Tea',
            'reward_description' => 'Enjoy a complimentary coffee or tea with your meal',
            'reward_type' => 'free_item',
            'points_cost' => 50,
            'monetary_value' => 100.00,
            'tier_requirement' => 'member',
            'url_image' => 'Loyalty/Coffee or Tea.jpg'
        ],
        [
            'reward_name' => 'Complimentary Breakfast',
            'reward_description' => 'Start your day with our delicious breakfast spread',
            'reward_type' => 'free_item',
            'points_cost' => 150,
            'monetary_value' => 300.00,
            'tier_requirement' => 'silver',
            'url_image' => 'Loyalty/Complimentary_Breakfast.jpg'
        ],
        [
            'reward_name' => '10% Discount',
            'reward_description' => 'Get 10% off your total bill',
            'reward_type' => 'discount',
            'points_cost' => 75,
            'monetary_value' => 0.00,
            'tier_requirement' => 'member',
            'url_image' => 'Loyalty/Discount.jpg'
        ],
        [
            'reward_name' => 'Free Halo-Halo',
            'reward_description' => 'Enjoy our famous Halo-Halo dessert on us',
            'reward_type' => 'free_item',
            'points_cost' => 100,
            'monetary_value' => 150.00,
            'tier_requirement' => 'gold',
            'url_image' => 'Loyalty/Halohalo.jpg'
        ],
        [
            'reward_name' => 'Welcome Drinks',
            'reward_description' => 'Complimentary welcome drinks for new members',
            'reward_type' => 'free_item',
            'points_cost' => 25,
            'monetary_value' => 50.00,
            'tier_requirement' => 'member',
            'url_image' => 'Loyalty/Welcome_drinks.jpg'
        ]
    ];
    
    foreach ($sampleRewards as $reward) {
        $sql = "INSERT INTO loyalty_rewards (reward_name, reward_description, reward_type, points_cost, monetary_value, tier_requirement, url_image, reward_status, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'available', TRUE)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $reward['reward_name'],
            $reward['reward_description'],
            $reward['reward_type'],
            $reward['points_cost'],
            $reward['monetary_value'],
            $reward['tier_requirement'],
            $reward['url_image']
        ]);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Created: " . htmlspecialchars($reward['reward_name']) . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create: " . htmlspecialchars($reward['reward_name']) . "</p>";
        }
    }
}

echo "<p><a href='loyalty_rewards.php'>View Loyalty Rewards Page</a> to see the updated rewards with images!</p>";
?>
