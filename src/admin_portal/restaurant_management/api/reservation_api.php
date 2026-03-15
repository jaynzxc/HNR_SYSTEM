<?php
// api/reservations_api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/ReservationModel.php';
require_once '../includes/helpers.php';

$database = new Database();
$db = $database->getConnection();
$reservation = new ReservationModel($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch($method) {
        case 'GET':
            if($action === 'stats') {
                $data = $reservation->getStatistics();
                sendResponse(true, $data, 'Reservation statistics retrieved');
            }
            elseif($action === 'tables') {
                $filters = [
                    'section' => $_GET['section'] ?? null,
                    'status' => $_GET['status'] ?? null,
                    'capacity' => $_GET['capacity'] ?? null
                ];
                $data = $reservation->getTables(array_filter($filters));
                sendResponse(true, $data, 'Tables retrieved');
            }
            elseif($action === 'reservations') {
                $filters = [
                    'date' => $_GET['date'] ?? date('Y-m-d'),
                    'status' => $_GET['status'] ?? null,
                    'search' => $_GET['search'] ?? null
                ];
                $data = $reservation->getReservations(array_filter($filters));
                sendResponse(true, $data, 'Reservations retrieved');
            }
            elseif($action === 'available_tables') {
                $missing = validateRequired($_GET, ['date', 'time', 'guests']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $data = $reservation->getAvailableTables($_GET['date'], $_GET['time'], $_GET['guests']);
                sendResponse(true, $data, 'Available tables retrieved');
            }
            elseif($action === 'waitlist') {
                $data = $reservation->getWaitlist();
                sendResponse(true, $data, 'Waitlist retrieved');
            }
            else {
                sendResponse(false, null, 'Invalid action', 400);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if($action === 'create') {
                $missing = validateRequired($input, ['guest_name', 'guest_phone', 'reservation_date', 'reservation_time', 'number_of_guests']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $reservation->createReservation($input);
                if($result) {
                    sendResponse(true, ['reservation_id' => $result], 'Reservation created');
                } else {
                    sendResponse(false, null, 'Failed to create reservation', 500);
                }
            }
            elseif($action === 'update_status') {
                $missing = validateRequired($input, ['reservation_id', 'status']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $reservation->updateReservationStatus($input['reservation_id'], $input['status']);
                if($result) {
                    sendResponse(true, null, 'Reservation status updated');
                } else {
                    sendResponse(false, null, 'Failed to update reservation', 500);
                }
            }
            elseif($action === 'add_to_waitlist') {
                $missing = validateRequired($input, ['guest_name', 'guest_phone', 'number_of_guests']);
                if(!empty($missing)) {
                    sendResponse(false, null, 'Missing fields: ' . implode(', ', $missing), 400);
                }
                $result = $reservation->addToWaitlist($input);
                if($result) {
                    sendResponse(true, null, 'Added to waitlist');
                } else {
                    sendResponse(false, null, 'Failed to add to waitlist', 500);
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