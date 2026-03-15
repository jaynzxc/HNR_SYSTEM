<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory/api/get_items.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../includes/Inventory.php';

$inventory = new Inventory();

$category = isset($_GET['category']) ? $_GET['category'] : null;
$lowStockOnly = isset($_GET['low_stock']) && $_GET['low_stock'] == 'true';
$search = isset($_GET['search']) ? $_GET['search'] : null;

$items = $inventory->getItems($category, $lowStockOnly, $search);
echo json_encode(['success' => true, 'data' => $items]);
?>