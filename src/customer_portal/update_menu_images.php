<?php
/**
 * Update Menu Items with Images Script
 * This script will update the image_url field for menu items based on available images
 */

require_once 'config/database.php';
require_once 'models/User.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();
$userModel = new User($database);

// Get current menu items
$menuItems = $userModel->getMenuItems(100);

echo "Current menu items in database:\n";
foreach ($menuItems as $item) {
    echo "ID: {$item['item_id']} - {$item['item_name']} (Category: " . ($item['category'] ?? 'Unknown') . ")\n";
}

echo "\nAvailable images in Menu Pics directory:\n";
$imageDir = 'Menu Pics/';
$images = [];
if (is_dir($imageDir)) {
    $files = scandir($imageDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $images[] = $file;
            echo "- $file\n";
        }
    }
}

// Create mapping of menu items to images
$imageMapping = [
    'Beef Nilaga' => 'Beef Nilaga.jpeg',
    'Beef Steak' => 'Beef Steak.jpg',
    'Brewed Coffee' => 'Brewed Coffee.jpeg',
    'Buko Juice' => 'Buko Juice.jpeg',
    'Caesar Salad' => 'Caesar Salad.jpeg',
    'Calamares' => 'Calamares.jpeg',
    'Cheese Platter' => 'Cheese Platter.jpeg',
    'Chicken Tinola' => 'Chicken Tinola.jpeg',
    'Chocolate Cake' => 'Chocolate Cake.jpeg',
    'Crispy Pata' => 'Crispy Pata.jpeg',
    'Garden Salad' => 'Garden Salad.jpeg',
    'Garlic Rice' => 'Garlic Rice.jpeg',
    'Grilled Salmon' => 'Grilled Salmon.jpeg',
    'Halo-Halo' => 'Halo-Halo.jpeg',
    'Iced Tea' => 'Iced Tea.jpeg',
    'Leche Flan' => 'Leche Flan.jpeg',
    'Lumpia Shanghai' => 'Lumping Shanghai.jpeg',
    'Mango Shake' => 'Mango Shake.jpeg',
    'Sinigang na Baboy' => 'Sinigang na Baboy.jpeg',
    'Sizzling Sisig' => 'Sizzling Sisig.jpeg'
];

echo "\nUpdating menu items with image URLs...\n";

// Update each menu item with corresponding image
$updatedCount = 0;
foreach ($menuItems as $item) {
    $itemName = $item['item_name'];
    $imageUrl = null;
    
    // Check for exact match
    if (isset($imageMapping[$itemName])) {
        $imageUrl = $imageDir . $imageMapping[$itemName];
    } else {
        // Try to find partial match
        foreach ($imageMapping as $menuItem => $imageFile) {
            if (stripos($menuItem, $itemName) !== false || stripos($itemName, $menuItem) !== false) {
                $imageUrl = $imageDir . $imageFile;
                break;
            }
        }
    }
    
    if ($imageUrl) {
        // Update the database
        $sql = "UPDATE menu_items SET image_url = ? WHERE item_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$imageUrl, $item['item_id']]);
        
        if ($result) {
            echo "✓ Updated '{$itemName}' with image: {$imageUrl}\n";
            $updatedCount++;
        } else {
            echo "✗ Failed to update '{$itemName}'\n";
        }
    } else {
        echo "- No image found for '{$itemName}'\n";
    }
}

echo "\nUpdate complete! Updated {$updatedCount} menu items with images.\n";

// Verify the updates
echo "\nVerifying updates:\n";
$updatedItems = $userModel->getMenuItems(100);
foreach ($updatedItems as $item) {
    $imageStatus = $item['image_url'] ? "✓ Has image" : "- No image";
    echo "ID: {$item['item_id']} - {$item['item_name']} - {$imageStatus}\n";
    if ($item['image_url']) {
        echo "  Image: {$item['image_url']}\n";
    }
}
?>
