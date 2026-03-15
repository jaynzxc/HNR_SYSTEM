<?php
/**
 * Database Setup Script
 * This script will create/upgrade the database schema and insert sample data
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Database connection: SUCCESS\n";
    
    // Read and execute schema
    $schema = file_get_contents('database/01_schema.sql');
    if ($schema === false) {
        throw new Exception("Could not read schema file");
    }
    
    // Split schema into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $db->exec($statement);
                echo "✓ Schema statement executed\n";
            } catch (PDOException $e) {
                // Ignore errors for existing tables/structures
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate column name') === false) {
                    echo "⚠ Schema warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n✓ Database schema updated\n";
    
    // Check if menu items exist
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM menu_items");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "No menu items found, inserting sample data...\n";
        
        // Read and execute sample data
        $sampleData = file_get_contents('database/02_sample_data.sql');
        if ($sampleData === false) {
            throw new Exception("Could not read sample data file");
        }
        
        $statements = array_filter(array_map('trim', explode(';', $sampleData)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $db->exec($statement);
                    echo "✓ Sample data inserted\n";
                } catch (PDOException $e) {
                    echo "⚠ Sample data warning: " . $e->getMessage() . "\n";
                }
            }
        }
    } else {
        echo "✓ Menu items already exist ({$result['count']} items)\n";
    }
    
    // Verify tables exist
    echo "\nVerifying tables:\n";
    $tables = ['food_orders', 'food_order_items', 'menu_items', 'users', 'menu_categories'];
    foreach ($tables as $table) {
        $stmt = $db->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        $result = $stmt->fetch();
        echo $table . ': ' . ($result ? '✓ EXISTS' : '✗ MISSING') . "\n";
    }
    
    // Check menu items
    $stmt = $db->prepare('SELECT item_id, item_name, price FROM menu_items LIMIT 5');
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample menu items:\n";
    foreach ($items as $item) {
        echo "ID: {$item['item_id']}, Name: {$item['item_name']}, Price: ₱{$item['price']}\n";
    }
    
    echo "\n✓ Database setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
