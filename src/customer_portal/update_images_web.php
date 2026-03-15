<?php
/**
 * Update Menu Images - Web Interface
 * Run this script through your web browser to update menu items with images
 */

require_once 'config/database.php';
require_once 'models/User.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();
$userModel = new User($database);

// Get current menu items
$menuItems = $userModel->getMenuItems(100);

// Image mapping
$imageMapping = [
    'Beef Nilaga' => 'Menu Pics/Beef Nilaga.jpeg',
    'Beef Steak' => 'Menu Pics/Beef Steak.jpg',
    'Brewed Coffee' => 'Menu Pics/Brewed Coffee.jpeg',
    'Buko Juice' => 'Menu Pics/Buko Juice.jpeg',
    'Caesar Salad' => 'Menu Pics/Caesar Salad.jpeg',
    'Calamares' => 'Menu Pics/Calamares.jpeg',
    'Cheese Platter' => 'Menu Pics/Cheese Platter.jpeg',
    'Chicken Tinola' => 'Menu Pics/Chicken Tinola.jpeg',
    'Chocolate Cake' => 'Menu Pics/Chocolate Cake.jpeg',
    'Crispy Pata' => 'Menu Pics/Crispy Pata.jpeg',
    'Garden Salad' => 'Menu Pics/Garden Salad.jpeg',
    'Garlic Rice' => 'Menu Pics/Garlic Rice.jpeg',
    'Grilled Salmon' => 'Menu Pics/Grilled Salmon.jpeg',
    'Halo-Halo' => 'Menu Pics/Halo-Halo.jpeg',
    'Iced Tea' => 'Menu Pics/Iced Tea.jpeg',
    'Leche Flan' => 'Menu Pics/Leche Flan.jpeg',
    'Lumpia Shanghai' => 'Menu Pics/Lumping Shanghai.jpeg',
    'Mango Shake' => 'Menu Pics/Mango Shake.jpeg',
    'Sinigang na Baboy' => 'Menu Pics/Sinigang na Baboy.jpeg',
    'Sizzling Sisig' => 'Menu Pics/Sizzling Sisig.jpeg'
];

echo "<h1>Update Menu Items with Images</h1>";
echo "<h2>Current Menu Items:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Item Name</th><th>Current Image</th><th>Action</th></tr>";

foreach ($menuItems as $item) {
    $itemName = $item['item_name'];
    $currentImage = $item['image_url'] ?? 'None';
    
    echo "<tr>";
    echo "<td>{$item['item_id']}</td>";
    echo "<td>{$itemName}</td>";
    echo "<td>{$currentImage}</td>";
    
    // Check if we have an image for this item
    $imageUrl = null;
    if (isset($imageMapping[$itemName])) {
        $imageUrl = $imageMapping[$itemName];
    } else {
        // Try fuzzy matching
        foreach ($imageMapping as $menuItem => $imageFile) {
            if (stripos($menuItem, $itemName) !== false || stripos($itemName, $menuItem) !== false) {
                $imageUrl = $imageFile;
                break;
            }
        }
    }
    
    if ($imageUrl && $currentImage === 'None') {
        // Update the database
        $sql = "UPDATE menu_items SET image_url = ? WHERE item_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$imageUrl, $item['item_id']]);
        
        if ($result) {
            echo "<td style='color: green;'>✓ Updated with: {$imageUrl}</td>";
        } else {
            echo "<td style='color: red;'>✗ Failed to update</td>";
        }
    } elseif ($currentImage !== 'None') {
        echo "<td style='color: blue;'>Already has image</td>";
    } else {
        echo "<td style='color: orange;'>No matching image found</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Show final results
echo "<h2>Final Results:</h2>";
$updatedItems = $userModel->getMenuItems(100);
$imageCount = 0;

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Item Name</th><th>Image URL</th></tr>";

foreach ($updatedItems as $item) {
    echo "<tr>";
    echo "<td>{$item['item_id']}</td>";
    echo "<td>{$item['item_name']}</td>";
    echo "<td>" . ($item['image_url'] ?? 'None') . "</td>";
    echo "</tr>";
    
    if ($item['image_url']) {
        $imageCount++;
    }
}

echo "</table>";
echo "<p><strong>Total items with images: {$imageCount} / " . count($updatedItems) . "</strong></p>";

echo "<p><a href='order_food.php'>Go to Order Food Page</a> to see the updated menu with images!</p>";
?>
