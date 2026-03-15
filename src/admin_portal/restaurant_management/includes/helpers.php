<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\includes\helpers.php
function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

function validateRequired($data, $fields) {
    $missing = [];
    foreach($fields as $field) {
        if(!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    return $missing;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateOrderNumber() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

function generateTicketNumber() {
    return 'KOT' . date('Ymd') . rand(100, 999);
}

function generateReservationNumber() {
    return 'RES' . date('Ymd') . rand(100, 999);
}

function generateCustomerCode() {
    return 'CUST' . date('Ymd') . rand(100, 999);
}

function generateStaffCode() {
    return 'STF' . date('Ymd') . rand(100, 999);
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function calculateAge($birthday) {
    $birthDate = new DateTime($birthday);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return round($diff / 60) . ' minutes ago';
    if ($diff < 86400) return round($diff / 3600) . ' hours ago';
    if ($diff < 2592000) return round($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}
?>