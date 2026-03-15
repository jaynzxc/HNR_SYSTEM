<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\online_ordering_integration.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fix the include paths
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Platform.php';
require_once __DIR__ . '/models/Order.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Initialize variables with default values
$connectedPlatforms = [];
$recentOrders = [];
$todayStats = [
    'total_orders' => 0,
    'total_revenue' => 0,
    'total_commission' => 0,
    'avg_order_value' => 0
];
$platformStats = [
    'connected_platforms' => 0,
    'total_platforms' => 0,
    'avg_commission' => 0
];

try {
    // Check if Platform class exists
    if (class_exists('Platform')) {
        $platformModel = new Platform($db);
        $connectedPlatforms = $platformModel->getAll();
        $platformStats = $platformModel->getStats();
        echo "<!-- Platform class loaded successfully -->\n";
    } else {
        echo "<!-- Warning: Platform class not found -->\n";
    }

    // Check if Order class exists
    if (class_exists('Order')) {
        $orderModel = new Order($db);
        $recentOrders = $orderModel->getRecent(10);
        $todayStats = $orderModel->getTodayStats();
        echo "<!-- Order class loaded successfully -->\n";
    } else {
        echo "<!-- Warning: Order class not found -->\n";
    }

    // Get available integrations
    try {
        $availQuery = "SELECT * FROM available_integrations WHERE is_active = 1";
        $availStmt = $db->prepare($availQuery);
        $availStmt->execute();
        $availableIntegrations = $availStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $availableIntegrations = [];
    }

    // Get API settings
    try {
        $apiQuery = "SELECT a.*, p.platform_name, p.webhook_url 
                    FROM api_settings a 
                    JOIN connected_platforms p ON a.platform_id = p.id 
                    WHERE a.is_active = 1";
        $apiStmt = $db->prepare($apiQuery);
        $apiStmt->execute();
        $apiSettings = $apiStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $apiSettings = [];
    }

} catch (Exception $e) {
    echo "<!-- Error: " . $e->getMessage() . " -->\n";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Online Ordering Integration</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .transition-side { transition: all 0.2s ease; }
        .dropdown-arrow { transition: transform 0.2s; }
        details[open] .dropdown-arrow { transform: rotate(90deg); }
        details > summary { list-style: none; }
        details summary::-webkit-details-marker { display: none; }
    </style>
</head>
<body class="bg-white font-sans antialiased">

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- SIDEBAR (keep your existing sidebar code) -->
        <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
            <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
                <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
                <span class="font-semibold text-lg tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.admin</span></span>
            </div>

            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">A</div>
                <div>
                    <p class="font-medium text-sm">Andreo Reyes</p>
                    <p class="text-xs text-slate-500">general manager</p>
                </div>
            </div>

            <nav class="p-4 space-y-2 text-sm">
                <!-- Dashboard -->
                <a href="../Dashboard.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition">
                    <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>
                    <span>Dashboard</span>
                </a>

                <!-- HOTEL MANAGEMENT GROUP -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
                        <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
                        <span class="font-medium">HOTEL MANAGEMENT</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
                        <a href="../Hotel_management/Front_Desk_Reception.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
                        <a href="../Hotel_management/Room_Management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
                        <a href="../Hotel_management/Reservations_n_Booking.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
                        <a href="../Hotel_management/housekeeping_n_maintenance.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
                        <a href="../Hotel_management/events_n_conference.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
                    </div>
                </details>

                <!-- RESTAURANT MANAGEMENT GROUP -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
                        <i class="fa-solid fa-utensils w-5 text-slate-400 group-open:text-amber-600"></i>
                        <span class="font-medium">RESTAURANT MANAGEMENT</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
                        <a href="../restaurant_&_management/table_reservation.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-clock w-4"></i> Table Reservation</a>
                        <a href="../restaurant_&_management/menu_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bars w-4"></i> Menu Management</a>
                        <a href="../restaurant_&_management/orders_POS.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cash-register w-4"></i> Orders / POS</a>
                        <a href="../restaurant_&_management/kitchen_orders.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-fire w-4"></i> Kitchen Orders (KOT)</a>
                        <a href="../restaurant_&_management/wait_staff_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-user w-4"></i> Wait Staff Management</a>
                    </div>
                </details>

                <!-- CUSTOMER MANAGEMENT GROUP -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
                        <i class="fa-regular fa-address-book w-5 text-slate-400 group-open:text-amber-600"></i>
                        <span class="font-medium">CUSTOMER MANAGEMENT</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
                        <a href="../Customer_management/guest_relationship.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-handshake w-4"></i> Guest Relationship (CRM)</a>
                        <a href="../Customer_management/loyalty_rewards.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-star w-4"></i> Loyalty & Rewards</a>
                        <a href="../Customer_management/customer_feedback&reviews.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-pen-to-square w-4"></i> Customer Feedback & Reviews</a>
                    </div>
                </details>

                <!-- OPERATIONS GROUP -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
                        <i class="fa-solid fa-gears w-5 text-slate-400 group-open:text-amber-600"></i>
                        <span class="font-medium">OPERATIONS</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
                        <a href="../Operations/inventory_&_stocks.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-boxes w-4"></i> Inventory & Stock</a>
                        <a href="../Operations/billing_and_payments.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
                        <a href="../Operations/payment_gateway.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-wifi w-4"></i> Payment Gateway</a>
                    </div>
                </details>

                <!-- MARKETING - open with Online Ordering Integration highlighted -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
                        <i class="fa-solid fa-megaphone w-5 text-amber-600"></i>
                        <span class="font-medium">MARKETING</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
                        <a href="../Marketing/hotelmarketing_&_promotions.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-gem w-4 text-slate-400"></i> Hotel Marketing & Promotions</a>
                        <a href="online_ordering_integration.php" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium"><i class="fa-solid fa-cart-shopping w-4 text-amber-600"></i> Online Ordering Integration</a>
                    </div>
                </details>

                <!-- REPORTS & ANALYTICS -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
                        <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
                        <span class="font-medium">REPORTS & ANALYTICS</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
                        <a href="../reports_&_analytics/sales_report.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
                        <a href="../reports_&_analytics/booking_reports.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
                        <a href="../reports_&_analytics/analytics_dashboard.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
                    </div>
                </details>

                <!-- SYSTEM -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
                        <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
                        <span class="font-medium">SYSTEM</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
                        <a href="../System/channel_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
                        <a href="../System/door_lock_integration.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
                        <a href="../System/settings.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-sliders w-4"></i> Settings</a>
                    </div>
                </details>

                <!-- logout -->
                <div class="border-t border-slate-200 pt-3 mt-3">
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
                        <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">
            <!-- header -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Online Ordering Integration</h1>
                    <p class="text-sm text-slate-500 mt-0.5">manage third-party delivery platforms and online ordering channels</p>
                </div>
                <div class="flex gap-3 text-sm">
                    <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
                        <i class="fa-regular fa-calendar text-slate-400"></i> 
                        <?php echo date('F j, Y'); ?>
                    </span>
                    <button onclick="refreshData()" class="bg-white border rounded-full px-4 py-2 shadow-sm hover:bg-amber-50">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Connected platforms</p>
                    <p class="text-2xl font-semibold" id="connected-platforms">
                        <?php echo $platformStats['connected_platforms'] ?? 0; ?>
                    </p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Today's online orders</p>
                    <p class="text-2xl font-semibold" id="today-orders">
                        <?php echo $todayStats['total_orders'] ?? 0; ?>
                    </p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Revenue (online)</p>
                    <p class="text-2xl font-semibold" id="today-revenue">
                        ₱<?php echo number_format($todayStats['total_revenue'] ?? 0, 2); ?>
                    </p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Commission fees</p>
                    <p class="text-2xl font-semibold text-amber-600" id="total-commission">
                        ₱<?php echo number_format($todayStats['total_commission'] ?? 0, 2); ?>
                    </p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-xs text-slate-500">Avg. order value</p>
                    <p class="text-2xl font-semibold" id="avg-order">
                        ₱<?php echo number_format($todayStats['avg_order_value'] ?? 0, 2); ?>
                    </p>
                </div>
            </div>

            <!-- CONNECTED PLATFORMS -->
            <h2 class="font-semibold text-lg mb-3 flex items-center gap-2">
                <i class="fa-solid fa-plug text-amber-600"></i> connected platforms
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <?php if (!empty($connectedPlatforms)): ?>
                    <?php foreach ($connectedPlatforms as $platform): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-full bg-<?php echo $platform['bg_color'] ?? 'amber-100'; ?> flex items-center justify-center text-<?php echo str_replace('100', '600', $platform['bg_color'] ?? 'amber-100'); ?> text-xl">
                                <i class="fa-solid fa-<?php 
                                    echo $platform['platform_name'] == 'Foodpanda' ? 'bag-shopping' : 
                                        ($platform['platform_name'] == 'GrabFood' ? 'motorcycle' : 
                                        ($platform['platform_name'] == 'Lalamove' ? 'truck' : 
                                        ($platform['icon_class'] ?? 'globe'))); 
                                ?>"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold"><?php echo $platform['platform_name']; ?></h3>
                                <p class="text-xs text-slate-500"><?php echo ucfirst($platform['platform_type'] ?? 'delivery'); ?></p>
                            </div>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-500">Status</span>
                            <span class="bg-<?php echo $platform['status'] == 'connected' ? 'green' : 'yellow'; ?>-100 text-<?php echo $platform['status'] == 'connected' ? 'green' : 'yellow'; ?>-700 px-2 py-0.5 rounded-full text-xs">
                                <?php echo $platform['status']; ?>
                            </span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-500">Commission</span>
                            <span><?php echo $platform['commission_rate']; ?>%</span>
                        </div>
                        <button onclick="managePlatform(<?php echo $platform['id']; ?>)" 
                                class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">
                            manage
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-4 text-center py-8 text-slate-500">
                        No platforms found. Please check database connection.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Continue with the rest of your HTML... -->
            <!-- ... rest of your content ... -->

        </main>
    </div>

    <script>
    function refreshData() {
        fetch('api/get_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('connected-platforms').textContent = data.data.stats.connected_platforms;
                    document.getElementById('today-orders').textContent = data.data.stats.today_orders;
                    document.getElementById('today-revenue').textContent = '₱' + data.data.stats.today_revenue;
                    document.getElementById('total-commission').textContent = '₱' + data.data.stats.total_commission;
                    document.getElementById('avg-order').textContent = '₱' + data.data.stats.avg_order_value;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function managePlatform(platformId) {
        window.location.href = 'platform_edit.php?id=' + platformId;
    }

    function updateOrderStatus(orderId) {
        const status = prompt('Enter new status (pending, preparing, picked_up, delivered, cancelled):');
        if (status) {
            fetch('api/update_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({order_id: orderId, status: status})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated!');
                    location.reload();
                } else {
                    alert('Failed to update order status');
                }
            });
        }
    }

    function syncOrders() {
        alert('Syncing orders from all platforms...');
    }

    setInterval(refreshData, 30000);
    </script>
</body>
</html>