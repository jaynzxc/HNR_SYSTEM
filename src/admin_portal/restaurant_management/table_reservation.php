<?php
// admin/restaurant_management/table_reservation.php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/ReservationModel.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /hnr_system/src/admin_portal/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$reservation = new ReservationModel($db);

// Get current user info
$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? 'staff';

// Get initial data
$stats = $reservation->getStatistics();
$tables = $reservation->getTables();
$today_reservations = $reservation->getReservations(['date' => date('Y-m-d')]);
$waitlist = $reservation->getWaitlist();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · Table Reservation</title>
  <link href="/HNR_SYSTEM/src/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* tiny custom for dropdowns and side hover */
    .transition-side { transition: all 0.2s ease; }
    .dropdown-arrow { transition: transform 0.2s; }
    details[open] .dropdown-arrow { transform: rotate(90deg); }
    details > summary { list-style: none; }
    details summary::-webkit-details-marker { display: none; }
    .toast {
      position: fixed; bottom: 20px; right: 20px;
      background: #10b981; color: white; padding: 12px 24px;
      border-radius: 8px; z-index: 1000; animation: slideIn 0.3s ease;
    }
    .toast.error { background: #ef4444; }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    .table-grid-item {
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .table-grid-item:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .sidebar-active {
      background-color: #fef3c7;
      border-right: 4px solid #d97706;
    }
  </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
  <div id="toastContainer"></div>

  <!-- New Reservation Modal -->
  <div id="newReservationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">New Reservation</h2>
        <button id="closeModalBtn" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-2xl"></i></button>
      </div>
      <form id="newReservationForm">
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Guest Name</label>
          <input type="text" id="guestName" class="w-full border rounded-lg p-2" required>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Phone Number</label>
          <input type="text" id="guestPhone" class="w-full border rounded-lg p-2" required>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Email</label>
          <input type="email" id="guestEmail" class="w-full border rounded-lg p-2">
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium mb-1">Date</label>
            <input type="date" id="resDate" class="w-full border rounded-lg p-2" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Time</label>
            <input type="time" id="resTime" class="w-full border rounded-lg p-2" value="19:00" required>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium mb-1">Number of Guests</label>
            <input type="number" id="guestCount" class="w-full border rounded-lg p-2" min="1" value="2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Table (Optional)</label>
            <select id="tableId" class="w-full border rounded-lg p-2">
              <option value="">Auto-assign</option>
              <?php foreach($tables as $table): ?>
              <option value="<?php echo $table['id']; ?>"><?php echo $table['table_number']; ?> (<?php echo $table['capacity']; ?> pax)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Special Requests</label>
          <textarea id="specialRequests" class="w-full border rounded-lg p-2" rows="2"></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelModalBtn" class="px-4 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Create Reservation</button>
        </div>
      </form>
    </div>
  </div>

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
        <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">
          <?php echo strtoupper(substr($user_name, 0, 1)); ?>
        </div>
        <div>
          <p class="font-medium text-sm"><?php echo htmlspecialchars($user_name); ?></p>
          <p class="text-xs text-slate-500"><?php echo ucfirst($user_role); ?></p>
        </div>
      </div>

      <!-- ===== SIDEBAR MENU (grouped with dropdowns) ===== -->
      <nav class="p-4 space-y-2 text-sm">

        <!-- Dashboard -->
        <a href="../admin_portal/dashboard.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition-hover">
          <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>
          <span>Dashboard</span>
        </a>

        <!-- HOTEL MANAGEMENT GROUP -->
        <details class="group">
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer transition-side">
            <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">HOTEL MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/Hotel_management/front_desk_reception.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
            <a href="../admin_portal/Hotel_management/room_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
            <a href="../admin_portal/Hotel_management/reservation_&_booking.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
            <a href="../admin_portal/Hotel_management/housekeeping_&_maintenance.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
            <a href="../admin_portal/Hotel_management/event_&_conference.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
          </div>
        </details>

        <!-- RESTAURANT MANAGEMENT GROUP (open by default with active page) -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-utensils w-5 text-amber-600"></i>
            <span class="font-medium">RESTAURANT MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/restaurant_management/table_reservation.php" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-50 text-amber-700 font-medium"><i class="fa-regular fa-clock w-4 text-amber-600"></i> Table Reservation</a>
            <a href="../admin_portal/restaurant_management/menu_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bars w-4"></i> Menu Management</a>
            <a href="../admin_portal/restaurant_management/orders_pos.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cash-register w-4"></i> Orders / POS</a>
            <a href="../admin_portal/restaurant_management/kitchen_orders.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-fire w-4"></i> Kitchen Orders (KOT)</a>
            <a href="../admin_portal/restaurant_management/wait_staff_management.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-user w-4"></i> Wait Staff Management</a>
          </div>
        </details>

        <!-- CUSTOMER MANAGEMENT -->
        <details class="group">
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-regular fa-address-book w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">CUSTOMER MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../customer_management/customer_relationship.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-handshake w-4"></i> Customer Relationship (CRM)</a>
            <a href="../customer_management/loyalty_rewards.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-star w-4"></i> Loyalty & Rewards</a>
            <a href="../customer_management/customer_feedback_&_reviews.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-pen-to-square w-4"></i> Customer Feedback & Reviews</a>
          </div>
        </details>

        <!-- OPERATIONS -->
        <details class="group">
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-gears w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">OPERATIONS</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/operations/inventory_&_stocks.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-boxes w-4"></i> Inventory & Stock</a>
            <a href="../admin_portal/operations/billing_&_payment.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
            <a href="../admin_portal/operations/payment_gateway.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-wifi w-4"></i> Payment Gateway</a>
          </div>
        </details>

        <!-- MARKETING -->
        <details class="group">
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-megaphone w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">MARKETING</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/marketing/hotelmarketing_&_promotions.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions</a>
            <a href="../admin_portal/marketing/online_ordering_integration.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cart-shopping w-4"></i> Online Ordering Integration</a>
          </div>
        </details>

        <!-- REPORTS & ANALYTICS -->
        <details class="group">
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">REPORTS & ANALYTICS</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/reports_&_analytics/sales_reports.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
            <a href="../admin_portal/reports_&_analytics/booking_reports.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
            <a href="../admin_portal/reports_&_analytics/analytics_dashboard.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
          </div>
        </details>

        <!-- SYSTEM -->
        <details class="group">
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">SYSTEM</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../admin_portal/System/channel_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
            <a href="../admin_portal/System/door_lock_integration.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
            <a href="../admin_portal/System/settings.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-sliders w-4"></i> Settings</a>
          </div>
        </details>

        <!-- logout -->
        <div class="border-t border-slate-200 pt-3 mt-3">
          <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
            <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
            <span>Logout</span>
          </a>
        </div>
      </nav>
    </aside>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-gray-50">

      <!-- header / page title -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-semibold text-slate-800">Table Reservation</h1>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2">
            <i class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDate"></span>
          </span>
        </div>
      </div>

      <!-- ===== STATISTIC CARDS (5 cards) ===== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Today's reservations</p>
          <p class="text-2xl font-semibold"><?php echo $stats['today_reservations']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Total guests</p>
          <p class="text-2xl font-semibold"><?php echo $stats['total_guests']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Available tables</p>
          <p class="text-2xl font-semibold text-green-600"><?php echo $stats['available_tables']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Walk-ins</p>
          <p class="text-2xl font-semibold text-blue-600"><?php echo $stats['walk_ins']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Waitlist</p>
          <p class="text-2xl font-semibold text-amber-600"><?php echo $stats['waitlist_count']; ?></p>
        </div>
      </div>

      <!-- Action Bar -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex justify-between">
        <button id="newReservationBtn" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700">
          <i class="fa-solid fa-plus mr-1"></i> new reservation
        </button>
        <div class="flex gap-2">
          <button id="refreshBtn" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">
            <i class="fa-solid fa-rotate-right mr-1"></i> refresh
          </button>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
        <button class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm" data-filter="all">all</button>
        <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="pending">pending</button>
        <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="confirmed">confirmed</button>
        <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="seated">seated</button>
        <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm" data-filter="completed">completed</button>
      </div>

      <!-- Reservations Table -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
          <h2 class="font-semibold flex items-center gap-2">
            <i class="fa-regular fa-clock text-amber-600"></i> today's reservations
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="text-slate-400 text-xs border-b">
              <tr>
                <td class="p-3">Time</td>
                <td class="p-3">Guest</td>
                <td class="p-3">Table</td>
                <td class="p-3">Pax</td>
                <td class="p-3">Status</td>
                <td class="p-3">Requests</td>
                <td class="p-3">Actions</td>
              </tr>
            </thead>
            <tbody id="reservationsTableBody" class="divide-y">
              <?php if(empty($today_reservations)): ?>
              <tr>
                <td colspan="7" class="p-6 text-center text-slate-500">No reservations for today</td>
              </tr>
              <?php else: ?>
                <?php foreach($today_reservations as $res): ?>
                <tr>
                  <td class="p-3"><?php echo date('h:i A', strtotime($res['reservation_time'])); ?></td>
                  <td class="p-3 font-medium"><?php echo htmlspecialchars($res['guest_name']); ?></td>
                  <td class="p-3"><?php echo $res['table_number'] ?? 'TBD'; ?></td>
                  <td class="p-3"><?php echo $res['number_of_guests']; ?></td>
                  <td class="p-3">
                    <span class="bg-<?php echo $res['status'] == 'confirmed' ? 'green' : ($res['status'] == 'pending' ? 'yellow' : 'blue'); ?>-100 text-<?php echo $res['status'] == 'confirmed' ? 'green' : ($res['status'] == 'pending' ? 'yellow' : 'blue'); ?>-700 px-2 py-0.5 rounded-full text-xs">
                      <?php echo $res['status']; ?>
                    </span>
                  </td>
                  <td class="p-3"><?php echo htmlspecialchars($res['special_requests'] ?? ''); ?></td>
                  <td class="p-3">
                    <button class="seat-reservation text-green-600 text-xs hover:underline mr-2" data-id="<?php echo $res['id']; ?>">seat</button>
                    <button class="cancel-reservation text-rose-600 text-xs hover:underline" data-id="<?php echo $res['id']; ?>">cancel</button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Bottom Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Table Availability -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3">
            <i class="fa-regular fa-table text-amber-600"></i> table availability
          </h2>
          <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3" id="tablesGrid">
            <?php foreach($tables as $table): ?>
            <?php
                $statusColor = [
                    'available' => 'bg-green-50 border-green-200 text-green-700',
                    'occupied' => 'bg-red-50 border-red-200 text-red-700',
                    'reserved' => 'bg-amber-50 border-amber-200 text-amber-700'
                ][$table['status']] ?? 'bg-slate-50';
            ?>
            <div class="border rounded-lg p-2 text-center table-grid-item <?php echo $statusColor; ?>" data-id="<?php echo $table['id']; ?>">
              <span class="text-sm font-medium"><?php echo $table['table_number']; ?></span>
              <span class="text-xs block"><?php echo $table['capacity']; ?> pax</span>
              <span class="text-xs"><?php echo $table['status']; ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Waitlist -->
        <div class="space-y-5">
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3">
              <i class="fa-regular fa-clock text-amber-600"></i> waitlist (<?php echo count($waitlist); ?> parties)
            </h3>
            <?php if(empty($waitlist)): ?>
              <p class="text-sm text-slate-500 text-center py-4">No parties on waitlist</p>
            <?php else: ?>
              <ul class="space-y-2" id="waitlistContainer">
                <?php foreach($waitlist as $wl): ?>
                <li class="flex justify-between text-sm border-b pb-1">
                  <span><?php echo htmlspecialchars($wl['guest_name']); ?></span>
                  <span class="text-xs"><?php echo $wl['number_of_guests']; ?> pax · <?php echo $wl['estimated_wait_time']; ?> min</span>
                </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    const API_BASE = '../api/reservations_api.php';

    function showToast(message, type = 'success') {
      const toast = $(`<div class="toast ${type === 'error' ? 'error' : ''}"><i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}</div>`);
      $('#toastContainer').append(toast);
      setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 3000);
    }

    function updateDate() {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      $('#currentDate').text(new Date().toLocaleDateString('en-US', options));
    }

    $(document).ready(function() {
      updateDate();

      $('#newReservationBtn').click(function() {
        $('#newReservationModal').removeClass('hidden').addClass('flex');
      });

      $('#newReservationForm').submit(function(e) {
        e.preventDefault();
        
        const data = {
          guest_name: $('#guestName').val(),
          guest_phone: $('#guestPhone').val(),
          guest_email: $('#guestEmail').val(),
          reservation_date: $('#resDate').val(),
          reservation_time: $('#resTime').val(),
          number_of_guests: $('#guestCount').val(),
          table_id: $('#tableId').val() || null,
          special_requests: $('#specialRequests').val(),
          source: 'website',
          status: 'confirmed'
        };

        $.ajax({
          url: API_BASE + '?action=create',
          method: 'POST',
          data: JSON.stringify(data),
          contentType: 'application/json',
          success: function(response) {
            if(response.success) {
              showToast('Reservation created');
              $('#newReservationModal').addClass('hidden').removeClass('flex');
              $('#newReservationForm')[0].reset();
              setTimeout(() => location.reload(), 1000);
            } else {
              showToast('Error creating reservation', 'error');
            }
          },
          error: function() {
            showToast('Server error', 'error');
          }
        });
      });

      $(document).on('click', '.seat-reservation', function() {
        const id = $(this).data('id');
        if(confirm('Seat this guest?')) {
          $.ajax({
            url: API_BASE + '?action=update_status',
            method: 'POST',
            data: JSON.stringify({ reservation_id: id, status: 'seated' }),
            contentType: 'application/json',
            success: function(response) {
              if(response.success) {
                showToast('Guest seated');
                location.reload();
              }
            }
          });
        }
      });

      $(document).on('click', '.cancel-reservation', function() {
        if(confirm('Cancel this reservation?')) {
          const id = $(this).data('id');
          $.ajax({
            url: API_BASE + '?action=update_status',
            method: 'POST',
            data: JSON.stringify({ reservation_id: id, status: 'cancelled' }),
            contentType: 'application/json',
            success: function(response) {
              if(response.success) {
                showToast('Reservation cancelled');
                location.reload();
              }
            }
          });
        }
      });

      $('#refreshBtn').click(function() {
        location.reload();
      });

      $('#closeModalBtn, #cancelModalBtn').click(function() {
        $('#newReservationModal').addClass('hidden').removeClass('flex');
      });

      $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('bg-amber-600 text-white').addClass('bg-white border');
        $(this).removeClass('bg-white border').addClass('bg-amber-600 text-white');
        // Filter functionality would go here
      });
    });
  </script>
</body>
</html>