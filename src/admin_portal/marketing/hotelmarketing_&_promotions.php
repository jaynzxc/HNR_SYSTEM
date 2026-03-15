<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\hotelmarketing_&_promotions.php

// Fix the include paths - use __DIR__ to get current directory
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Campaign.php';
require_once __DIR__ . '/models/PromoCode.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$campaign = new Campaign($db);
$promoCode = new PromoCode($db);

// Get data
$campaigns = $campaign->getAll();
$stats = $campaign->getStats();
$activePromos = $promoCode->getActive();

// Calculate additional stats
$totalRevenue = $stats['total_revenue'] ?? 0;
$totalRedemptions = $stats['total_redemptions'] ?? 0;
$avgRedemptionValue = $totalRedemptions > 0 ? $totalRevenue / $totalRedemptions : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Marketing & Promotions · Admin</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .transition-side { transition: all 0.2s ease; }
        .dropdown-arrow { transition: transform 0.2s; }
        details[open] .dropdown-arrow { transform: rotate(90deg); }
        details > summary { list-style: none; }
        details summary::-webkit-details-marker { display: none; }
        .hover-scale:hover { transform: scale(1.02); }
        .progress-bar {
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- SIDEBAR -->
        <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
            <!-- ... (keep your existing sidebar code) ... -->
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
                        <a href="../Operations/billing_and_payments.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
                        <a href="../Operations/payment_gateway.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-wifi w-4"></i> Payment Gateway</a>
                    </div>
                </details>

                <!-- MARKETING - open with Hotel Marketing highlighted -->
                <details class="group" open>
                    <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
                        <i class="fa-solid fa-megaphone w-5 text-amber-600"></i>
                        <span class="font-medium">MARKETING</span>
                        <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
                    </summary>
                    <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
                        <a href="hotelmarketing_&_promotions.php" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium"><i class="fa-regular fa-gem w-4 text-amber-600"></i> Hotel Marketing & Promotions</a>
                        <a href="online_ordering_integration.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cart-shopping w-4 text-slate-400"></i> Online Ordering Integration</a>
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
            <!-- Header with Back Button -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-4">
                    <a href="../dashboard.html" 
                       class="group flex items-center gap-2 text-slate-600 hover:text-amber-600 transition-all bg-white px-4 py-2 rounded-xl shadow-sm hover:shadow-md border border-slate-200">
                        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                        <span class="hidden sm:inline">Back to Dashboard</span>
                    </a>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Hotel Marketing & Promotions</h1>
                        <p class="text-sm text-slate-500 mt-0.5">manage campaigns, discounts, and promotional offers</p>
                    </div>
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

            <!-- STATS CARDS (Dynamic) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-slate-500">Active campaigns</p>
                            <p class="text-2xl font-semibold" id="active-campaigns"><?php echo $stats['active_campaigns'] ?? 0; ?></p>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                            <i class="fa-regular fa-megaphone"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-slate-500">This month revenue</p>
                            <p class="text-2xl font-semibold">₱<?php echo number_format($totalRevenue, 1); ?>M</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <i class="fa-regular fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-slate-500">Redemptions</p>
                            <p class="text-2xl font-semibold"><?php echo number_format($totalRedemptions); ?></p>
                        </div>
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                            <i class="fa-regular fa-ticket"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-slate-500">Conversion rate</p>
                            <p class="text-2xl font-semibold">18.5%</p>
                        </div>
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                            <i class="fa-regular fa-percent"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-slate-500">Avg. ROI</p>
                            <p class="text-2xl font-semibold text-green-600"><?php echo number_format($stats['avg_roi'] ?? 0, 1); ?>%</p>
                        </div>
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                            <i class="fa-regular fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
                <div class="flex gap-2 flex-wrap">
                    <a href="campaign_edit.php" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition flex items-center gap-2">
                        <i class="fa-regular fa-plus"></i> new campaign
                    </a>
                    <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition flex items-center gap-2">
                        <i class="fa-regular fa-tag"></i> create promo
                    </button>
                    <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition flex items-center gap-2">
                        <i class="fa-regular fa-envelope"></i> email blast
                    </button>
                    <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition flex items-center gap-2">
                        <i class="fa-regular fa-chart-bar"></i> analytics
                    </button>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="searchCampaigns" placeholder="search campaigns..." 
                           class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>

            <!-- FILTER TABS -->
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
                <button class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm" data-filter="all">all campaigns</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="active">active</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="scheduled">scheduled</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="ended">ended</button>
                <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50" data-filter="draft">drafts</button>
            </div>

            <!-- CAMPAIGNS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8" id="campaigns-grid">
                <?php foreach ($campaigns as $camp): 
                    $progress = $camp['target_redemptions'] > 0 ? 
                        min(100, round(($camp['current_redemptions'] / $camp['target_redemptions']) * 100)) : 0;
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-700',
                        'scheduled' => 'bg-blue-100 text-blue-700',
                        'ended' => 'bg-slate-100 text-slate-600',
                        'draft' => 'bg-slate-100 text-slate-600'
                    ];
                    $statusColor = $statusColors[$camp['status']] ?? 'bg-slate-100 text-slate-600';
                ?>
                <div class="campaign-card bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition-all hover-scale" data-status="<?php echo $camp['status']; ?>">
                    <div class="flex justify-between items-start mb-3">
                        <span class="<?php echo $statusColor; ?> text-xs px-3 py-1 rounded-full flex items-center gap-1">
                            <i class="fa-regular fa-<?php echo $camp['status'] == 'active' ? 'play' : ($camp['status'] == 'scheduled' ? 'clock' : 'circle'); ?>"></i>
                            <?php echo ucfirst($camp['status']); ?>
                        </span>
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <i class="fa-regular fa-calendar"></i>
                            <?php 
                            if ($camp['status'] == 'ended') {
                                echo 'ended ' . date('M j', strtotime($camp['end_date']));
                            } elseif ($camp['status'] == 'scheduled') {
                                echo 'starts ' . date('M j', strtotime($camp['start_date']));
                            } else {
                                echo 'ends ' . date('M j', strtotime($camp['end_date']));
                            }
                            ?>
                        </span>
                    </div>
                    
                    <h3 class="font-semibold text-lg mb-1"><?php echo htmlspecialchars($camp['campaign_name']); ?></h3>
                    <p class="text-xs text-slate-500 mb-4"><?php echo htmlspecialchars($camp['description']); ?></p>
                    
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-500">Progress</span>
                            <span class="font-medium"><?php echo $camp['current_redemptions']; ?> / <?php echo $camp['target_redemptions']; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                        <div>
                            <p class="text-xs text-slate-500">Revenue</p>
                            <p class="font-semibold">₱<?php echo number_format($camp['revenue_generated']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">ROI</p>
                            <p class="font-semibold <?php echo $camp['roi'] > 0 ? 'text-green-600' : 'text-slate-600'; ?>">
                                <?php echo number_format($camp['roi'], 1); ?>%
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="campaign_edit.php?id=<?php echo $camp['id']; ?>" 
                           class="flex-1 border border-amber-600 text-amber-700 py-2 rounded-xl text-sm hover:bg-amber-50 transition flex items-center justify-center gap-1">
                            <i class="fa-regular fa-pen-to-square"></i> edit
                        </a>
                        <button onclick="viewCampaign(<?php echo $camp['id']; ?>)" 
                                class="flex-1 bg-amber-600 text-white py-2 rounded-xl text-sm hover:bg-amber-700 transition flex items-center justify-center gap-1">
                            <i class="fa-regular fa-eye"></i> view
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- BOTTOM: PROMO CODES & PERFORMANCE -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- active promo codes -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-semibold text-lg flex items-center gap-2">
                            <i class="fa-regular fa-ticket text-amber-600"></i> active promo codes
                        </h2>
                        <button onclick="addPromoCode()" class="text-sm text-amber-600 hover:text-amber-700 flex items-center gap-1">
                            <i class="fa-regular fa-plus"></i> add new
                        </button>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($activePromos as $promo): ?>
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                            <div>
                                <span class="font-mono font-medium bg-white px-2 py-1 rounded border border-slate-200">
                                    <?php echo $promo['code']; ?>
                                </span>
                                <p class="text-xs text-slate-500 mt-1"><?php echo $promo['description']; ?></p>
                            </div>
                            <div class="text-right">
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                                    <?php echo $promo['current_uses']; ?>/<?php echo $promo['max_uses']; ?> used
                                </span>
                                <p class="text-xs text-slate-400 mt-1">ends <?php echo date('M j', strtotime($promo['end_date'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- performance summary -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5">
                    <h3 class="font-semibold flex items-center gap-2 mb-4">
                        <i class="fa-regular fa-chart-bar text-amber-600"></i> campaign performance
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">Conversion rate</span>
                                <span class="font-semibold">18.5%</span>
                            </div>
                            <div class="w-full bg-white/50 rounded-full h-2">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: 18.5%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">Avg. redemption value</span>
                                <span class="font-semibold">₱<?php echo number_format($avgRedemptionValue); ?></span>
                            </div>
                            <div class="w-full bg-white/50 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-amber-200">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Overall ROI</span>
                                <span class="text-xl font-bold text-green-600"><?php echo number_format($stats['avg_roi'] ?? 0, 1); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- footer note -->
            <div class="mt-8 text-center text-xs text-slate-400 border-t pt-5">
                ✅ Hotel Marketing & Promotions — campaign management, promo codes, performance tracking, scheduling
            </div>
        </main>
    </div>

    <script>
// Make the "create promo" button functional
document.querySelector('button:has(.fa-tag)').addEventListener('click', function() {
    window.location.href = 'promo_edit.php';
});

// Make the "email blast" button functional
document.querySelector('button:has(.fa-envelope)').addEventListener('click', function() {
    alert('Email blast feature coming soon!');
    // You can implement email functionality here
});

// Make the "analytics" button functional
document.querySelector('button:has(.fa-chart-bar)').addEventListener('click', function() {
    window.location.href = '../reports_&_analytics/analytics_dashboard.html';
});

// View campaign function
function viewCampaign(id) {
    window.location.href = 'campaign_view.php?id=' + id;
}

// Add promo code function
function addPromoCode() {
    window.location.href = 'promo_edit.php';
}

// Refresh data function
function refreshData() {
    location.reload();
}

// Delete campaign function (you can add this to the edit page)
function deleteCampaign(id) {
    if (confirm('Are you sure you want to delete this campaign?')) {
        fetch('api/delete_campaign.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Campaign deleted successfully');
                location.reload();
            } else {
                alert('Failed to delete campaign');
            }
        });
    }
}
</script>
</body>
</html> 