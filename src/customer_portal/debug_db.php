<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Database connection: SUCCESS\n\n";
    
    // Check if tables exist
    $tables = ['food_orders', 'food_order_items', 'menu_items', 'users'];
    foreach ($tables as $table) {
        $stmt = $db->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        $result = $stmt->fetch();
        echo $table . ': ' . ($result ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    echo "\nChecking menu_items content:\n";
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM menu_items');
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Menu items count: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $db->prepare('SELECT * FROM menu_items LIMIT 5');
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample menu items:\n";
        foreach ($items as $item) {
            echo "ID: {$item['item_id']}, Name: {$item['item_name']}, Price: {$item['price']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
