<?php
// file: HNR_SYSTEM/src/admin_portal/operations/inventory/api/dashboard_stats.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/Inventory.php';

$inventory = new Inventory();

$stats = $inventory->getDashboardStats();
$reorderItems = $inventory->getItemsToReorder();
$suppliers = $inventory->getSuppliers();

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'reorder_items' => $reorderItems,
    'suppliers' => $suppliers
]);
?>