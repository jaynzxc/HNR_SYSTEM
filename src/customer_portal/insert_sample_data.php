<?php
// insert_sample_data.php - Insert sample menu data if none exists
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check if menu items exist
    $result = $db->query('SELECT COUNT(*) as count FROM menu_items');
    $row = $result->fetch(PDO::FETCH_ASSOC);

    echo "Current menu items: " . $row['count'] . PHP_EOL;

    if ($row['count'] == 0) {
        echo "Inserting sample menu data..." . PHP_EOL;

        // Insert categories
        $db->exec("INSERT INTO menu_categories (category_name, category_description, display_order, is_active) VALUES
            ('Mains', 'Main courses and signature dishes', 1, 1),
            ('Appetizers', 'Starters and small plates', 2, 1),
            ('Desserts', 'Sweet treats and desserts', 3, 1),
            ('Beverages', 'Drinks and beverages', 4, 1)");

        // Get category IDs
        $categories = [];
        $stmt = $db->query('SELECT category_id, category_name FROM menu_categories');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[$row['category_name']] = $row['category_id'];
        }

        // Insert menu items
        $items = [
            [$categories['Mains'], 'Sinigang na Baboy', 'Traditional Filipino sour soup with pork and vegetables', 320.00, 25, 'mild', 1],
            [$categories['Mains'], 'Sizzling Sisig', 'Chopped pork with onions, served sizzling hot', 290.00, 15, 'medium', 0],
            [$categories['Mains'], 'Crispy Pata', 'Deep-fried pork knuckle with garlic rice', 550.00, 35, 'none', 1],
            [$categories['Mains'], 'Garlic Rice', 'Fragrant garlic fried rice', 50.00, 10, 'none', 0],
            [$categories['Appetizers'], 'Calamares', 'Crispy fried squid rings with dipping sauce', 180.00, 15, 'none', 0],
            [$categories['Appetizers'], 'Tuna Pie', 'Creamy tuna pie with vegetables', 150.00, 12, 'none', 0],
            [$categories['Desserts'], 'Halo-Halo', 'Filipino shaved ice dessert with fruits and leche flan', 150.00, 5, 'none', 1],
            [$categories['Desserts'], 'Leche Flan', 'Caramel custard dessert', 120.00, 3, 'none', 0],
            [$categories['Beverages'], 'Fresh Buko Juice', 'Fresh coconut juice with pulp', 90.00, 3, 'none', 0],
            [$categories['Beverages'], 'Calamansi Juice', 'Filipino limeade', 70.00, 3, 'none', 0]
        ];

        $stmt = $db->prepare('INSERT INTO menu_items (category_id, item_name, item_description, price, preparation_time_minutes, spicy_level, is_signature) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ($items as $item) {
            $stmt->execute($item);
        }

        echo "Sample menu data inserted successfully!" . PHP_EOL;

        // Check count again
        $result = $db->query('SELECT COUNT(*) as count FROM menu_items');
        $row = $result->fetch(PDO::FETCH_ASSOC);
        echo "Menu items after insert: " . $row['count'] . PHP_EOL;
    } else {
        echo "Menu items already exist, no insertion needed." . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
?>
