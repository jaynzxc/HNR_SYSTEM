<?php
/**
 * Debug Login Redirect
 * Test what URL is being returned for redirect
 */

require_once '../../customer_portal/config/database.php';
require_once '../../customer_portal/models/User.php';
require_once '../../customer_portal/models/SessionManager.php';
require_once '../../customer_portal/helpers/api_helpers.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Test the getRedirectUrl function
function getRedirectUrl($role) {
    switch ($role) {
        case 'admin':
            return '../admin_portal/dashboard.html';
        case 'restaurant_manager':
            return '../restaurant_portal/dashboard.html';
        case 'hotel_manager':
            return '../hotel_portal/dashboard.html';
        case 'customer':
        default:
            return '../customer_portal/index.html';
    }
}

// Test each role
$roles = ['customer', 'admin', 'restaurant_manager', 'hotel_manager'];
$redirects = [];

foreach ($roles as $role) {
    $redirects[$role] = getRedirectUrl($role);
}

echo json_encode([
    'current_path' => $_SERVER['REQUEST_URI'],
    'base_url' => dirname($_SERVER['REQUEST_URI']),
    'redirects' => $redirects,
    'full_urls' => array_map(function($url) {
        return dirname($_SERVER['REQUEST_URI']) . '/' . ltrim($url, './');
    }, $redirects),
    'debug_info' => [
        'login_form_location' => '/src/login-register/login_form.html',
        'customer_portal_location' => '/src/customer_portal/index.html',
        'expected_relative_path' => '../customer_portal/index.html'
    ]
]);
?>
