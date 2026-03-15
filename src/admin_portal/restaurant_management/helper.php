<?php
// includes/helpers.php

// Generate unique order number
function generateOrderNumber($prefix = 'OR') {
    return $prefix . date('Ymd') . rand(1000, 9999);
}

// Generate unique reservation number
function generateReservationNumber() {
    return 'RES' . date('Ymd') . rand(100, 999);
}

// Generate unique ticket number
function generateTicketNumber() {
    return 'KOT' . date('Ymd') . rand(100, 999);
}

// Format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Calculate preparation time based on items
function calculatePrepTime($items) {
    $totalTime = 0;
    foreach($items as $item) {
        $totalTime += ($item['preparation_time'] ?? 10) * $item['quantity'];
    }
    return ceil($totalTime / count($items));
}

// Send JSON response
function jsonResponse($success, $data = null, $message = '', $statusCode = 200) {
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

// Validate date range
function validateDateRange($startDate, $endDate) {
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    return $start && $end && $end >= $start;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>