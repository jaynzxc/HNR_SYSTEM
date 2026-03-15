<?php
/**
 * Simple API Test
 * Check if API routing is working
 */

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'API is working',
    'request_uri' => $_SERVER['REQUEST_URI'],
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'script_name' => $_SERVER['SCRIPT_NAME'],
    'php_self' => $_SERVER['PHP_SELF'],
    'query_string' => $_SERVER['QUERY_STRING']
]);
?>
