<?php
// file: HNR_SYSTEM/src/admin_portal/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HNR Admin</title>
  <link href="/HNR_SYSTEM/src/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* tiny custom for dropdowns and side hover */
    .transition-side { transition: all 0.2s ease; }
    .dropdown-arrow { transition: transform 0.2s; }
    details[open] .dropdown-arrow { transform: rotate(90deg); }
    details > summary { list-style: none; }
    details summary::-webkit-details-marker { display: none; }
  </style>
</head>
<body>

<!-- APP CONTAINER: flex row (sidebar + main) -->
<div class="min-h-screen flex flex-col lg:flex-row">

  <!-- ========== SIDEBAR (GROUPED WITH DROPDOWNS) ========== -->
  <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
    <!-- brand -->
    <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
      <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
      <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
      <span class="font-semibold text-lg tracking-tight text-slate-800">HNR<span class="text-amber-600">.admin</span></span>
    </div>

    <!-- admin badge -->
    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
      <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">A</div>
      <div>
        <p class="font-medium text-sm">Admin User</p>
        <p class="text-xs text-slate-500">Administrator</p>
      </div>
    </div>

    <!-- ===== SIDEBAR MENU (grouped with dropdowns) ===== -->
    <nav class="p-4 space-y-2 text-sm">

      <!-- Dashboard -->
      <a href="/HNR_SYSTEM/src/admin_portal/dashboard.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-amber-50 text-amber-800 font-medium' : ''; ?>">
        <i class="fa-solid fa-table-cells-large w-5 text-amber-600"></i>
        <span>Dashboard</span>
      </a>

      <!-- HOTEL MANAGEMENT GROUP -->
      <details class="group" <?php echo strpos($_SERVER['PHP_SELF'], 'Hotel_management') !== false ? 'open' : ''; ?>>
        <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer transition-side">
          <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
          <span class="font-medium">HOTEL MANAGEMENT</span>
          <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
        </summary>
        <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
          <a href="/HNR_SYSTEM/src/admin_portal/Hotel_management/front_desk_reception.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'front_desk_reception.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/Hotel_management/room_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'room_management.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/Hotel_management/reservation_&_booking.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'reservation_&_booking.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/Hotel_management/housekeeping_&_maintenance.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'housekeeping_&_maintenance.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/Hotel_management/event_&_conference.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'event_&_conference.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference
          </a>
        </div>
      </details>

      <!-- RESTAURANT MANAGEMENT GROUP -->
      <details class="group" <?php echo strpos($_SERVER['PHP_SELF'], 'restaurant') !== false ? 'open' : ''; ?>>
        <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
          <i class="fa-solid fa-utensils w-5 text-slate-400 group-open:text-amber-600"></i>
          <span class="font-medium">RESTAURANT MANAGEMENT</span>
          <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
        </summary>
        <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
          <a href="/HNR_SYSTEM/src/admin_portal/restaurant/table_reservation.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'table_reservation.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-clock w-4"></i> Table Reservation
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/restaurant/menu_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'menu_management.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-bars w-4"></i> Menu Management
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/restaurant/orders_pos.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'orders_pos.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-cash-register w-4"></i> Orders / POS
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/restaurant/kitchen_orders.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'kitchen_orders.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-fire w-4"></i> Kitchen Orders (KOT)
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/restaurant/wait_staff_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'wait_staff_management.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-user w-4"></i> Wait Staff Management
          </a>
        </div>
      </details>

      <!-- CUSTOMER MANAGEMENT -->
      <details class="group" <?php echo strpos($_SERVER['PHP_SELF'], 'customer_management') !== false ? 'open' : ''; ?>>
        <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
          <i class="fa-regular fa-address-book w-5 text-slate-400 group-open:text-amber-600"></i>
          <span class="font-medium">CUSTOMER MANAGEMENT</span>
          <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
        </summary>
        <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
          <a href="/HNR_SYSTEM/src/admin_portal/customer_management/customer_relationship.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'customer_relationship.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-handshake w-4"></i> Customer Relationship (CRM)
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/customer_management/loyalty_rewards.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'loyalty_rewards.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-star w-4"></i> Loyalty & Rewards
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/customer_management/customer_feedback_&_reviews.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'customer_feedback_&_reviews.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-pen-to-square w-4"></i> Customer Feedback & Reviews
          </a>
        </div>
      </details>

      <!-- OPERATIONS -->
      <details class="group" <?php echo strpos($_SERVER['PHP_SELF'], 'operations') !== false ? 'open' : ''; ?>>
        <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
          <i class="fa-solid fa-gears w-5 text-slate-400 group-open:text-amber-600"></i>
          <span class="font-medium">OPERATIONS</span>
          <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
        </summary>
        <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
          <a href="/HNR_SYSTEM/src/admin_portal/operations/inventory_&_stocks.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'inventory_&_stocks.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-boxes w-4"></i> Inventory & Stock
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/operations/billing_&_payment.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'billing_&_payment.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-credit-card w-4"></i> Billing & Payments
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/operations/payment_gateway.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'payment_gateway.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-wifi w-4"></i> Payment Gateway
          </a>
        </div>
      </details>

      <!-- MARKETING -->
      <details class="group" <?php echo strpos($_SERVER['PHP_SELF'], 'marketing') !== false ? 'open' : ''; ?>>
        <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
          <i class="fa-solid fa-megaphone w-5 text-slate-400 group-open:text-amber-600"></i>
          <span class="font-medium">MARKETING</span>
          <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
        </summary>
        <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
          <a href="/HNR_SYSTEM/src/admin_portal/marketing/hotelmarketing_&_promotions.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'hotelmarketing_&_promotions.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/marketing/online_ordering_integration.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'online_ordering_integration.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-cart-shopping w-4"></i> Online Ordering Integration
          </a>
        </div>
      </details>

      <!-- REPORTS & ANALYTICS -->
      <details class="group" <?php echo strpos($_SERVER['PHP_SELF'], 'reports_&_analytics') !== false ? 'open' : ''; ?>>
        <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
          <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
          <span class="font-medium">REPORTS & ANALYTICS</span>
          <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
        </summary>
        <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
          <a href="/HNR_SYSTEM/src/admin_portal/reports_&_analytics/sales_reports.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'sales_reports.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-chart-line w-4"></i> Sales Reports
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/reports_&_analytics/booking_reports.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'booking_reports.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-regular fa-calendar w-4"></i> Booking Reports
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/reports_&_analytics/analytics_dashboard.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'analytics_dashboard.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard
          </a>
        </div>
      </details>

      <!-- SYSTEM -->
      <details class="group" <?php echo strpos($_SERVER['PHP_SELF'], 'System') !== false ? 'open' : ''; ?>>
        <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
          <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
          <span class="font-medium">SYSTEM</span>
          <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
        </summary>
        <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
          <a href="/HNR_SYSTEM/src/admin_portal/System/channel_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'channel_management.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-code-branch w-4"></i> Channel Management
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/System/door_lock_integration.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'door_lock_integration.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-lock w-4"></i> Door Lock Integration
          </a>
          <a href="/HNR_SYSTEM/src/admin_portal/System/settings.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50 <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-amber-100/50 text-amber-700' : ''; ?>">
            <i class="fa-solid fa-sliders w-4"></i> Settings
          </a>
        </div>
      </details>

      <!-- logout -->
      <div class="border-t border-slate-200 pt-3 mt-3">
        <a href="/HNR_SYSTEM/src/admin_portal/logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
          <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
          <span>Logout</span>
        </a>
      </div>
    </nav>
  </aside>

  <!-- ========== MAIN CONTENT STARTS HERE ========== -->
  <main class="flex-1 p-5 lg:p-8 overflow-y-auto">