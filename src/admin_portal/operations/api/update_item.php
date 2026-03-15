<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory/api/update_item.php
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

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

$inventory = new Inventory();
$result = $inventory->updateItem($data['id'], $data);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update item']);
}
?>