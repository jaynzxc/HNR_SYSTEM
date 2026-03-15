<?php
/**
 * Diagnose and Fix Loyalty Rewards Image Issues
 * This script will check and fix image display problems in loyalty rewards
 */

require_once 'config/database.php';
require_once 'models/User.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

echo "<h1>Diagnose Loyalty Rewards Image Issues</h1>";

// Step 1: Check database for url_image column and data
echo "<h2>Step 1: Check Database Image Data</h2>";

try {
    $sql = "SELECT reward_id, reward_name, url_image FROM loyalty_rewards ORDER BY reward_id";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rewards)) {
        echo "<p style='color: red;'>✗ No rewards found in database</p>";
        echo "<p><a href='add_reward_images.php'>Run add_reward_images.php first</a></p>";
        exit;
    }
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Reward ID</th><th>Reward Name</th><th>URL Image</th><th>Image Status</th><th>File Exists</th></tr>";
    
    foreach ($rewards as $reward) {
        $imagePath = $reward['url_image'] ?? '';
        $imageStatus = !empty($imagePath) ? 'Has Image' : 'No Image';
        $fileExists = '';
        
        if (!empty($imagePath)) {
            $fullPath = __DIR__ . '/' . $imagePath;
            $fileExists = file_exists($fullPath) ? 
                '<span style="color: green;">✓ Exists</span>' : 
                '<span style="color: red;">✗ Missing</span>';
        }
        
        echo "<tr>";
        echo "<td>{$reward['reward_id']}</td>";
        echo "<td>" . htmlspecialchars($reward['reward_name']) . "</td>";
        echo "<td>" . ($imagePath ? htmlspecialchars($imagePath) : 'None') . "</td>";
        echo "<td>{$imageStatus}</td>";
        echo "<td>{$fileExists}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Step 2: Check Loyalty directory
echo "<h2>Step 2: Check Loyalty Directory</h2>";

$loyaltyDir = __DIR__ . '/Loyalty';
if (is_dir($loyaltyDir)) {
    echo "<p style='color: green;'>✓ Loyalty directory exists</p>";
    
    $imageFiles = scandir($loyaltyDir);
    $imageFiles = array_diff($imageFiles, ['.', '..']);
    
    echo "<p>Images found:</p>";
    echo "<ul>";
    foreach ($imageFiles as $file) {
        $filePath = $loyaltyDir . '/' . $file;
        $fileSize = filesize($filePath);
        echo "<li>" . htmlspecialchars($file) . " (" . number_format($fileSize) . " bytes)</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Loyalty directory not found</p>";
}

// Step 3: Fix common image path issues
echo "<h2>Step 3: Fix Image Path Issues</h2>";

$fixedCount = 0;
foreach ($rewards as $reward) {
    $currentPath = $reward['url_image'] ?? '';
    
    if (empty($currentPath)) {
        continue; // Skip rewards without images
    }
    
    // Check if path needs fixing
    $needsFix = false;
    $newPath = $currentPath;
    
    // Fix 1: Remove leading slash if present
    if (strpos($currentPath, '/') === 0) {
        $newPath = ltrim($currentPath, '/');
        $needsFix = true;
    }
    
    // Fix 2: Ensure correct directory structure
    if (!strpos($currentPath, 'Loyalty/')) {
        $filename = basename($currentPath);
        $newPath = 'Loyalty/' . $filename;
        $needsFix = true;
    }
    
    // Apply fix if needed
    if ($needsFix) {
        $sql = "UPDATE loyalty_rewards SET url_image = ? WHERE reward_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$newPath, $reward['reward_id']]);
        
        if ($result) {
            echo "<p style='color: green;'>✓ Fixed path for reward {$reward['reward_id']}: {$currentPath} → {$newPath}</p>";
            $fixedCount++;
        } else {
            echo "<p style='color: red;'>✗ Failed to fix path for reward {$reward['reward_id']}</p>";
        }
    }
}

if ($fixedCount === 0) {
    echo "<p style='color: blue;'>ℹ No path fixes needed</p>";
}

// Step 4: Test image display
echo "<h2>Step 4: Test Image Display</h2>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;'>";
foreach ($rewards as $reward) {
    $imagePath = $reward['url_image'] ?? '';
    
    echo "<div style='border: 1px solid #ccc; padding: 10px; text-align: center;'>";
    echo "<h4>" . htmlspecialchars($reward['reward_name']) . "</h4>";
    
    if (!empty($imagePath)) {
        echo "<img src='{$imagePath}' alt='" . htmlspecialchars($reward['reward_name']) . "' 
                     style='width: 150px; height: 100px; object-fit: cover; border: 1px solid #ddd;'
                     onerror=\"this.style.border='2px solid red'; this.alt='Image failed to load';\">";
        echo "<p style='font-size: 12px; margin-top: 5px;'>" . htmlspecialchars($imagePath) . "</p>";
    } else {
        echo "<div style='width: 150px; height: 100px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;'>";
        echo "<span style='color: #999;'>No Image</span>";
        echo "</div>";
    }
    
    echo "</div>";
}
echo "</div>";

// Step 5: Create missing rewards with images
echo "<h2>Step 5: Ensure All Rewards Have Images</h2>";

$imagesInDir = [];
if (is_dir($loyaltyDir)) {
    $imageFiles = scandir($loyaltyDir);
    foreach ($imageFiles as $file) {
        if ($file !== '.' && $file !== '..') {
            $imagesInDir[] = $file;
        }
    }
}

$rewardsWithoutImages = [];
foreach ($rewards as $reward) {
    if (empty($reward['url_image'])) {
        $rewardsWithoutImages[] = $reward;
    }
}

if (!empty($rewardsWithoutImages) && !empty($imagesInDir)) {
    echo "<p>Assigning images to rewards without images...</p>";
    
    $sampleRewards = [
        ['reward_name' => 'Free Coffee or Tea', 'image' => 'Loyalty/Coffee or Tea.jpg'],
        ['reward_name' => 'Complimentary Breakfast', 'image' => 'Loyalty/Complimentary_Breakfast.jpg'],
        ['reward_name' => '10% Discount', 'image' => 'Loyalty/Discount.jpg'],
        ['reward_name' => 'Free Halo-Halo', 'image' => 'Loyalty/Halohalo.jpg'],
        ['reward_name' => 'Welcome Drinks', 'image' => 'Loyalty/Welcome_drinks.jpg']
    ];
    
    foreach ($rewardsWithoutImages as $reward) {
        $rewardName = strtolower($reward['reward_name']);
        $assignedImage = null;
        
        // Try to match with sample rewards
        foreach ($sampleRewards as $sample) {
            if (strpos($rewardName, strtolower($sample['reward_name'])) !== false) {
                $assignedImage = $sample['image'];
                break;
            }
        }
        
        if ($assignedImage) {
            $sql = "UPDATE loyalty_rewards SET url_image = ? WHERE reward_id = ?";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$assignedImage, $reward['reward_id']]);
            
            if ($result) {
                echo "<p style='color: green;'>✓ Assigned image to: " . htmlspecialchars($reward['reward_name']) . "</p>";
            }
        }
    }
}

echo "<h2>Summary</h2>";
echo "<p><strong>Database Check:</strong> " . count($rewards) . " rewards found</p>";
echo "<p><strong>Directory Check:</strong> " . count($imageFiles ?? []) . " images available</p>";
echo "<p><strong>Path Fixes:</strong> {$fixedCount} paths corrected</p>";
echo "<p><strong>Image Display:</strong> Test images shown above</p>";

echo "<p><a href='loyalty_rewards.php'>Test Loyalty Rewards Page</a></p>";
?>
