<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory/api/adjust_stock.php
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

if (!isset($data['item_id']) || !isset($data['new_quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID and new quantity are required']);
    exit;
}

$inventory = new Inventory();
$notes = isset($data['notes']) ? $data['notes'] : 'Manual adjustment';

$result = $inventory->adjustStock($data['item_id'], $data['new_quantity'], $notes);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Stock adjusted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to adjust stock']);
}
?>