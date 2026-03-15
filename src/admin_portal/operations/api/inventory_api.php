<?php
// file: HNR_SYSTEM/src/admin_portal/operations/api/inventory_api.php

// Turn off error display - we'll handle errors ourselves
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Include the Inventory class
    $inventoryPath = __DIR__ . '/../inventory/includes/Inventory.php';
    
    if (!file_exists($inventoryPath)) {
        throw new Exception('Inventory class not found at: ' . $inventoryPath);
    }
    
    require_once $inventoryPath;
    
    // Initialize Inventory class
    $inventory = new Inventory();
    
    // Get action from query string
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if (!$action) {
        throw new Exception('No action specified');
    }
    
    // Clear any output buffered content
    ob_clean();
    
    switch ($action) {
        case 'get_stats':
            $stats = $inventory->getDashboardStats();
            $reorderItems = $inventory->getItemsToReorder();
            $suppliers = $inventory->getSuppliers();
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'reorder_items' => $reorderItems,
                'suppliers' => $suppliers
            ]);
            break;
            
        case 'get_items':
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $lowStockOnly = isset($_GET['low_stock']) && $_GET['low_stock'] == 'true';
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            
            if ($id) {
                $item = $inventory->getItem($id);
                echo json_encode(['success' => true, 'data' => $item ? [$item] : []]);
            } else {
                $items = $inventory->getItems($category, $lowStockOnly, $search);
                echo json_encode(['success' => true, 'data' => $items]);
            }
            break;
            
        case 'add_item':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed. Use POST.');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON data');
            }
            
            // Validate required fields
            $required = ['name', 'category', 'quantity', 'unit'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            // Prepare data
            $data = [
                'name' => $input['name'],
                'category' => ucfirst($input['category']),
                'quantity' => intval($input['quantity']),
                'unit' => $input['unit'],
                'reorder_level' => isset($input['reorder_level']) ? intval($input['reorder_level']) : 10,
                'sku' => isset($input['sku']) ? $input['sku'] : ''
            ];
            
            $result = $inventory->addItem($data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Item added successfully', 'id' => $result]);
            } else {
                throw new Exception('Failed to add item to database');
            }
            break;
            
        case 'update_item':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed. Use POST.');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                throw new Exception('Item ID is required');
            }
            
            $result = $inventory->updateItem($input['id'], $input);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
            } else {
                throw new Exception('Failed to update item');
            }
            break;
            
        case 'delete_item':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed. Use POST.');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                throw new Exception('Item ID is required');
            }
            
            $result = $inventory->deleteItem($input['id']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
            } else {
                throw new Exception('Failed to delete item');
            }
            break;
            
        case 'adjust_stock':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed. Use POST.');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['item_id']) || !isset($input['new_quantity'])) {
                throw new Exception('Item ID and new quantity are required');
            }
            
            $result = $inventory->adjustStock(
                $input['item_id'],
                intval($input['new_quantity']),
                isset($input['notes']) ? $input['notes'] : 'Manual adjustment'
            );
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Stock adjusted successfully']);
            } else {
                throw new Exception('Failed to adjust stock');
            }
            break;
            
        case 'create_po':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed. Use POST.');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['supplier_id']) || !isset($input['items'])) {
                throw new Exception('Supplier ID and items are required');
            }
            
            $result = $inventory->createPurchaseOrder($input['supplier_id'], $input['items']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Purchase order created successfully', 'po_number' => $result]);
            } else {
                throw new Exception('Failed to create purchase order');
            }
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    // Clear any output that might have been generated
    ob_clean();
    
    // Return error as JSON
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

// End output buffering and send output
ob_end_flush();
?>