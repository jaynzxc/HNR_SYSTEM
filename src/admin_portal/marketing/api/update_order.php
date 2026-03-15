<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\api\update_order.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Order.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id']) || !isset($data['status'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Order ID and status are required'
        ]);
        exit;
    }
    
    $order = new Order();
    $result = $order->updateStatus($data['order_id'], $data['status']);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Order status updated successfully' : 'Failed to update order status'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>