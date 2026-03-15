<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory/api/create_po.php
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

if (!isset($data['supplier_id']) || !isset($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Supplier ID and items are required']);
    exit;
}

$inventory = new Inventory();
$result = $inventory->createPurchaseOrder($data['supplier_id'], $data['items']);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Purchase order created successfully', 'po_number' => $result]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create purchase order']);
}
?>