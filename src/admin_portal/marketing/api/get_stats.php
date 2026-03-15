<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\api\get_stats.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Platform.php';
require_once __DIR__ . '/../models/Order.php';

$platform = new Platform();
$order = new Order();

// Get platform stats
$platformStats = $platform->getStats();

// Get today's order stats
$orderStats = $order->getTodayStats();

// Get recent orders
$recentOrders = $order->getRecent(5);

// Get connected platforms
$connectedPlatforms = $platform->getAll();

$response = [
    'success' => true,
    'data' => [
        'stats' => [
            'connected_platforms' => $platformStats['connected_platforms'] ?? 0,
            'today_orders' => $orderStats['total_orders'] ?? 0,
            'today_revenue' => number_format($orderStats['total_revenue'] ?? 0, 2),
            'total_commission' => number_format($orderStats['total_commission'] ?? 0, 2),
            'avg_order_value' => number_format($orderStats['avg_order_value'] ?? 0, 2)
        ],
        'platforms' => $connectedPlatforms,
        'recent_orders' => $recentOrders,
        'commission_summary' => $orderStats['total_commission'] ?? 0
    ]
];

echo json_encode($response);
?>