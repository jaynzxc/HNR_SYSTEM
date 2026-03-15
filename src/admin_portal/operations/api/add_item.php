<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory/api/add_item.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/Inventory.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['name', 'category', 'quantity', 'unit'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "$field is required"]);
        exit;
    }
}

// Set default reorder level if not provided
if (!isset($data['reorder_level']) || empty($data['reorder_level'])) {
    $data['reorder_level'] = 10;
}

$inventory = new Inventory();
$result = $inventory->addItem($data);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Item added successfully', 'id' => $result]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add item']);
}
?>