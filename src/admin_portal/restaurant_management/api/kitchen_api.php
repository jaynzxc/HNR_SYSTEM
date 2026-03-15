<?php
// api/kitchen_api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/KitchenModel.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();
$kitchen = new KitchenModel($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch($method) {
        case 'GET':
            if($action === 'stats') {
                $data = $kitchen->getStatistics();
                sendResponse(true, $data, 'Kitchen statistics retrieved');
            }
            elseif($action === 'stations') {
                $data = $kitchen->getStations();
                sendResponse(true, $data, 'Kitchen stations retrieved');
            }
            elseif($action === 'tickets') {
                $filters = [
                    'status' => $_GET['status'] ?? null,
                    'station' => $_GET['station'] ?? null,
                    'priority' => $_GET['priority'] ?? null
                ];
                $data = $kitchen->getTickets(array_filter($filters));
                sendResponse(true, $data, 'Kitchen tickets retrieved');
            }
            elseif($action === 'ticket' && isset($_GET['id'])) {
                $data = $kitchen->getTicket($_GET['id']);
                if($data) {
                    sendResponse(true, $data, 'Ticket retrieved');
                } else {
                    sendResponse(false, null, 'Ticket not found', 404);
                }
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if($action === 'create_ticket') {
                $missing = validateRequired($input, ['order_id']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $kitchen->createTicket($input['order_id'], $input['station_id'] ?? null);
                if($result) {
                    sendResponse(true, ['ticket_id' => $result], 'Kitchen ticket created');
                } else {
                    sendResponse(false, null, 'Failed to create ticket', 500);
                }
            }
            elseif($action === 'update_status') {
                $missing = validateRequired($input, ['ticket_id', 'status']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $kitchen->updateTicketStatus($input['ticket_id'], $input['status']);
                if($result) {
                    sendResponse(true, null, 'Ticket status updated');
                } else {
                    sendResponse(false, null, 'Failed to update ticket status', 500);
                }
            }
            elseif($action === 'update_item_status') {
                $missing = validateRequired($input, ['item_id', 'status']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $kitchen->updateTicketItemStatus($input['item_id'], $input['status']);
                if($result) {
                    sendResponse(true, null, 'Item status updated');
                } else {
                    sendResponse(false, null, 'Failed to update item status', 500);
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