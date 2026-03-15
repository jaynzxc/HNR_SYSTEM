<?php
// api/orders_api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/OrderModel.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();
$order = new OrderModel($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch($method) {
        case 'GET':
            if($action === 'stats') {
                $data = $order->getStatistics();
                sendResponse(true, $data, 'Order statistics retrieved');
            }
            elseif($action === 'types') {
                $data = $order->getOrderTypes();
                sendResponse(true, $data, 'Order types retrieved');
            }
            elseif($action === 'orders') {
                $filters = [
                    'status' => $_GET['status'] ?? null,
                    'date' => $_GET['date'] ?? null,
                    'type' => $_GET['type'] ?? null
                ];
                $data = $order->getOrders(array_filter($filters));
                sendResponse(true, $data, 'Orders retrieved');
            }
            elseif($action === 'order' && isset($_GET['id'])) {
                $data = $order->getOrder($_GET['id']);
                if($data) {
                    $data['items'] = $order->getOrderItems($_GET['id']);
                    sendResponse(true, $data, 'Order retrieved');
                } else {
                    sendResponse(false, null, 'Order not found', 404);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if($action === 'create') {
                $missing = validateRequired($input, ['order_type_id', 'items']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $order->createOrder($input);
                if($result) {
                    sendResponse(true, ['order_id' => $result], 'Order created');
                } else {
                    sendResponse(false, null, 'Failed to create order', 500);
                }
            }
            elseif($action === 'update_status') {
                $missing = validateRequired($input, ['order_id', 'status']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $order->updateOrderStatus($input['order_id'], $input['status']);
                if($result) {
                    sendResponse(true, null, 'Order status updated');
                } else {
                    sendResponse(false, null, 'Failed to update order status', 500);
                }
            }
            elseif($action === 'process_payment') {
                $missing = validateRequired($input, ['order_id', 'amount', 'payment_method_id']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $order->processPayment($input['order_id'], $input);
                if($result) {
                    sendResponse(true, null, 'Payment processed');
                } else {
                    sendResponse(false, null, 'Failed to process payment', 500);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        default:
            sendResponse(false, null, 'Method not allowed', 405);
    }
} catch(Exception $e) {
    sendResponse(false, null, 'Server error: ' . $e->getMessage(), 500);
}
?>