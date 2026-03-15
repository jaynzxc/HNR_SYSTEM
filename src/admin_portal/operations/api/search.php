<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory/api/search.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../includes/Inventory.php';

if (!isset($_GET['q'])) {
    echo json_encode(['success' => false, 'message' => 'Search query required']);
    exit;
}

$inventory = new Inventory();
$results = $inventory->searchItems($_GET['q']);

echo json_encode(['success' => true, 'data' => $results]);
?>