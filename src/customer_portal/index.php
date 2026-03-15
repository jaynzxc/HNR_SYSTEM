<?php
/**
 * Customer Dashboard - Organized & Professional Layout
 * Clean, modern dashboard with improved structure and user experience
 */

session_start();
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/SessionManager.php';

// Initialize database first
$database = new Database();

// Check if user is logged in
$sessionManager = new SessionManager($database);
$currentUser = $sessionManager->getCurrentUser();

if (!$currentUser) {
    header('Location: ../login-register/login_form.php');
    exit;
}

// Check if admin dashboard is requested
$isAdmin = isset($_GET['admin']) && $_GET['admin'] == '1';

if ($isAdmin) {
    // For admin dashboard, we can use the same user but with admin privileges
    // In a real system, you'd have a separate admin users table
    // For now, we'll treat any user with admin=1 as admin
    $currentUser['is_admin'] = true;
}

// Get database connection
$db = $database->getConnection();
$userModel = new User($database);

// Fetch user's data
$bookings = $userModel->getUserBookings($currentUser['user_id'], 5);
$reservations = $userModel->getUserReservations($currentUser['user_id'], 5);
$orders = $userModel->getUserOrders($currentUser['user_id'], 5);
$notifications = $userModel->getUserNotifications($currentUser['user_id'], 10);
$menuItems = $userModel->getMenuItems(5);
$rooms = $userModel->getAvailableRooms(4);
$paymentMethods = $userModel->getUserPaymentMethods($currentUser['user_id']);

// Calculate unread notifications
$unreadCount = array_filter($notifications, function($notification) {
    return !$notification['is_read'];
});
$unreadCount = count($unreadCount);

// Helper functions
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'Not set';
    return date($format, strtotime($date));
}

function getBookingStatusClass($status) {
    $statusClasses = [
        'confirmed' => 'bg-green-100 text-green-700',
        'checked_in' => 'bg-blue-100 text-blue-700',
        'checked_out' => 'bg-slate-100 text-slate-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'pending' => 'bg-amber-100 text-amber-700'
    ];
    return $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
}

function getLoyaltyMessage($points) {
    if ($points >= 5000) return 'platinum member';
    if ($points >= 2000) return 'gold member';
    if ($points >= 500) return 'silver member';
    return 'earn points';
}

function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

// Calculate total payment due
$totalDue = 0;
foreach ($bookings as $booking) {
    if (($booking['payment_status'] ?? '') === 'pending') {
        $totalDue += floatval($booking['total_amount'] ?? 0);
    }
}
foreach ($orders as $order) {
    if (($order['payment_status'] ?? '') === 'pending') {
        $totalDue += floatval($order['total_amount'] ?? 0);
    }
}

// Find active booking and today's reservation
$activeBooking = null;
foreach ($bookings as $booking) {
    if (in_array($booking['booking_status'] ?? '', ['confirmed', 'checked_in'])) {
        $activeBooking = $booking;
        break;
    }
}

$todayReservation = null;
$today = date('Y-m-d');
foreach ($reservations as $reservation) {
    if (($reservation['reservation_date'] ?? '') === $today && ($reservation['reservation_status'] ?? '') === 'confirmed') {
        $todayReservation = $reservation;
        break;
    }
}

$recentOrder = null;
foreach ($orders as $order) {
    if (in_array($order['order_status'] ?? '', ['confirmed', 'preparing'])) {
        $recentOrder = $order;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isAdmin ? 'Admin Dashboard' : 'Dashboard'; ?> · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .gradient-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .gradient-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar-item {
            transition: all 0.2s ease;
        }
        .sidebar-item:hover {
            transform: translateX(4px);
        }
        .notification-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-80 bg-white border-r border-slate-200 shadow-lg flex-shrink-0">
            <div class="px-6 py-7 border-b border-slate-100">
                <div class="flex items-center gap-2 text-amber-700">
                    <i class="fa-solid fa-utensils text-xl"></i>
                    <i class="fa-solid fa-bed text-xl"></i>
                    <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.stay</span></span>
                </div>
                <p class="text-xs text-slate-500 mt-1"><?php echo $isAdmin ? 'admin portal · dashboard' : 'customer portal · dashboard'; ?></p>
            </div>
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80">
                <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg">
                    <?php echo getUserInitials($currentUser['first_name'] ?? '', $currentUser['last_name'] ?? ''); ?>
                </div>
                <div>
                    <p class="font-medium text-slate-800"><?php echo htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')); ?></p>
                    <p class="text-xs text-slate-500 flex items-center gap-1"><i class="fa-regular fa-gem text-[11px]"></i> <span><?php echo htmlspecialchars($currentUser['membership_tier'] ?? 'member'); ?></span> · <span><?php echo number_format($currentUser['loyalty_points'] ?? 0); ?></span> pts</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1.5 text-sm overflow-y-auto" style="max-height: calc(100vh - 280px);">
                <?php if ($isAdmin): ?>
                    <!-- Admin Navigation -->
                    <a href="index.php?admin=1" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-red-50 text-red-800 font-medium"><i class="fa-solid fa-shield-halved w-5 text-red-600"></i>Admin Dashboard</a>
                    <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>Customer View</a>
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-users w-5 text-slate-400"></i>All Users</a>
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-chart-bar w-5 text-slate-400"></i>Analytics</a>
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-cog w-5 text-slate-400"></i>Settings</a>
                <?php else: ?>
                    <!-- Customer Navigation -->
                    <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-solid fa-table-cells-large w-5 text-amber-600"></i>Dashboard</a>
                    <a href="my_profile.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-user w-5 text-slate-400"></i>My Profile</a>
                    <a href="hotel_booking.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-hotel w-5 text-slate-400"></i>Hotel Booking</a>
                    <a href="my_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-calendar-check w-5 text-slate-400"></i>My Reservations</a>
                    <a href="order_food.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-utensils w-5 text-slate-400"></i>Order Food</a>
                    <a href="payments.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-credit-card w-5 text-slate-400"></i>Payments</a>
                    <a href="loyalty_rewards.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-gem w-5 text-slate-400"></i>Loyalty Rewards</a>
                    <a href="reviews.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-star w-5 text-slate-400"></i>Reviews</a>
                    <a href="notifications.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-bell w-5 text-slate-400"></i>Notifications</a>
                <?php endif; ?>
                <span class="ml-auto bg-amber-100 text-amber-800 text-xs px-1.5 py-0.5 rounded-full"><?php echo $unreadCount; ?></span>
                </a>
                <div class="border-t border-slate-200 pt-3 mt-3">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700 transition"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i>Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white border-b border-slate-200 px-8 py-3 sticky top-0 z-30">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-light text-slate-800">good evening, <span class="font-semibold"><?php echo htmlspecialchars($currentUser['first_name'] ?? 'guest'); ?></span> 👋</h1>
                        <p class="text-sm text-slate-500 mt-0.5">your stay · <span id="currentDate"></span></p>
                    </div>
                    
                    <!-- fixed notification + search row (icons added) -->
                    <div class="flex items-center gap-4">
                        <!-- notification bell with icon + badge (fixed) -->
                        <div class="relative">
                            <i class="fa-regular fa-bell text-2xl text-slate-500 hover:text-amber-600 transition cursor-pointer"></i>
                            <span class="absolute -top-1 -right-1 bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white"><?php echo $unreadCount; ?></span>
                        </div>
                        <!-- search bar with icon (fixed) -->
                        <div class="bg-white border border-slate-200 rounded-full px-4 py-2 flex items-center gap-2 text-sm w-64 shadow-sm">
                            <i class="fa-regular fa-search text-slate-400"></i>
                            <input type="text" placeholder="quick search..." class="bg-transparent outline-none flex-1 text-slate-600 placeholder-slate-400">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-4 lg:p-6">
                <div class="max-w-7xl mx-auto">
        <!-- Quick Stats - More Compact -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
         <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
           <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-sm"><i class="fa-solid fa-bed"></i></div>
           <div>
            <p class="font-semibold text-sm"><?php echo $activeBooking ? htmlspecialchars($activeBooking['room_number'] ?? 'Room') : '—'; ?></p>
            <span class="text-xs <?php echo $activeBooking ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'; ?> px-2 py-0.5 rounded-full">
              <?php echo $activeBooking ? htmlspecialchars($activeBooking['booking_status'] ?? 'no booking') : 'no booking'; ?>
            </span>
          </div>
        </div>
        
         <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
           <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-sm"><i class="fa-regular fa-clock"></i></div>
           <div>
            <p class="font-semibold text-sm"><?php echo $todayReservation ? htmlspecialchars($todayReservation['reservation_time'] ?? '') : '—'; ?></p>
            <span class="text-xs <?php echo $todayReservation ? 'text-green-600' : 'text-slate-400'; ?>">
              <?php echo $todayReservation ? ($todayReservation['number_of_guests'] ?? '') . ' pax' : 'no reservation'; ?>
            </span>
          </div>
        </div>
        
         <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
           <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-sm"><i class="fa-solid fa-bag-shopping"></i></div>
           <div>
            <p class="font-semibold text-sm"><?php echo $recentOrder ? formatCurrency($recentOrder['total_amount'] ?? 0) : '₱0'; ?></p>
            <span class="text-xs <?php echo $recentOrder ? 'text-amber-600' : 'text-slate-500'; ?>">
              <?php echo $recentOrder ? htmlspecialchars($recentOrder['order_status'] ?? '') : 'empty'; ?>
            </span>
          </div>
        </div>
        
         <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
           <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-sm"><i class="fa-regular fa-star"></i></div>
           <div>
            <p class="font-semibold text-sm"><?php echo number_format($currentUser['loyalty_points'] ?? 0); ?> pts</p>
            <span class="text-xs text-slate-400"><?php echo getLoyaltyMessage($currentUser['loyalty_points'] ?? 0); ?></span>
           </div>
         </div>
       </div>

       <!-- Main Content Grid - More Compact -->
       <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
         <!-- Left Column - Hotel Booking -->
         <div class="space-y-4">
           <div class="bg-white p-4 rounded-xl border border-slate-200">
             <div class="flex items-center justify-between mb-2">
               <h2 class="font-semibold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-hotel text-amber-600"></i> available rooms</h2>
              <a href="hotel_booking.php" class="text-xs text-amber-700 hover:underline">view all</a>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <?php
              $roomTypes = array_unique(array_column($rooms, 'room_type'));
              $displayRooms = array_slice($roomTypes, 0, 2);
              
              if (empty($displayRooms)) {
                  echo '<div class="text-center text-slate-400 py-3 text-sm">No rooms available</div>';
              } else {
                  foreach ($displayRooms as $roomType) {
                      $room = current(array_filter($rooms, function($r) use ($roomType) {
                          return $r['room_type'] === $roomType;
                      }));
                      ?>
                      <div class="border rounded-lg p-2 hover:shadow-sm cursor-pointer">
                        <span class="font-medium text-xs"><?php echo htmlspecialchars(str_replace('_', ' ', $roomType)); ?></span>
                        <span class="block text-xs text-slate-500"><?php echo formatCurrency($room['base_price_per_night'] ?? 0); ?> / night</span>
                      </div>
                      <?php
                  }
              }
              ?>
             </div>
             <button onclick="window.location.href='hotel_booking.php'" class="w-full bg-amber-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-amber-700 transition mt-3">check availability</button>
           </div>
         </div>

         <!-- Middle Column - Menu & Orders -->
         <div class="space-y-4">
           <div class="bg-white p-4 rounded-xl border border-slate-200">
             <div class="flex items-center justify-between mb-2">
               <h2 class="font-semibold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-bag-shopping text-amber-600"></i> menu / order</h2>
               <button onclick="window.location.href='order_food.php'" class="text-xs text-amber-700 hover:underline">view full menu</button>
             </div>
             <div class="space-y-1" id="menuPreview">
               <?php
                  $previewItems = array_slice($menuItems, 0, 3);
                  if (empty($previewItems)) {
                      echo '<div class="text-center text-slate-400 py-3 text-sm">No menu items available</div>';
                  } else {
                      $total = 0;
                      foreach ($previewItems as $item) {
                          $total += floatval($item['price'] ?? 0);
                          ?>
                          <div class="flex justify-between text-xs">
                            <span><?php echo htmlspecialchars($item['item_name'] ?? ''); ?></span>
                            <span><?php echo formatCurrency($item['price'] ?? 0); ?></span>
                          </div>
                          <?php
                      }
                      ?>
                      <div class="flex justify-between font-medium text-xs border-t pt-1 mt-1">
                        <span>sample total</span>
                        <span><?php echo formatCurrency($total); ?></span>
                      </div>
                      <?php
                  }
                  ?>
             </div>
             <button onclick="window.location.href='order_food.php'" class="w-full bg-amber-600 text-white py-2 mt-3 rounded-lg text-sm hover:bg-amber-700 transition">view full menu</button>
           </div>

           <!-- Payment & Loyalty - Combined -->
           <div class="bg-white p-4 rounded-xl border border-slate-200">
             <div class="flex justify-between items-start gap-4">
               <div class="flex-1">
                 <h3 class="font-semibold text-slate-800 flex items-center gap-2 mb-2"><i class="fa-regular fa-credit-card text-amber-600"></i> payment & loyalty</h3>
                 <div class="space-y-2">
                   <div class="flex justify-between items-center">
                     <span class="text-xs text-slate-600">payment due</span>
                     <span class="font-bold text-sm"><?php echo $totalDue > 0 ? formatCurrency($totalDue) : '₱0'; ?></span>
                   </div>
                   <div class="flex justify-between items-center">
                     <span class="text-xs text-slate-600">loyalty points</span>
                     <span class="font-bold text-sm"><?php echo number_format($currentUser['loyalty_points'] ?? 0); ?></span>
                   </div>
                 </div>
                 <div class="flex gap-2 mt-3">
                   <button onclick="window.location.href='payments.php'" class="text-xs text-amber-700 hover:underline">payments →</button>
                   <button onclick="window.location.href='loyalty_rewards.php'" class="text-xs text-amber-700 hover:underline">rewards →</button>
                 </div>
               </div>
             </div>
           </div>
         </div>

         <!-- Right Column - Bookings & Notifications -->
         <div class="space-y-4">
           <div class="bg-white p-4 rounded-xl border border-slate-200">
             <h3 class="font-semibold flex items-center gap-1 mb-2"><i class="fa-regular fa-rectangle-list text-amber-600"></i> my bookings</h3>
             <div class="space-y-2">
              <?php
              if (empty($bookings)) {
                  echo '<div class="text-center text-slate-400 py-3 text-sm">No bookings yet.</div>';
              } else {
                  $displayBookings = array_slice($bookings, 0, 2);
                  foreach ($displayBookings as $booking) {
                      ?>
                      <div class="flex justify-between items-center border-b pb-1 p-2 rounded">
                        <div>
                          <span class="font-medium text-xs"><?php echo htmlspecialchars($booking['booking_reference'] ?? ''); ?></span>
                          <p class="text-xs text-slate-500"><?php echo htmlspecialchars($booking['room_type'] ?? ''); ?> · <?php echo formatDate($booking['check_in_date'] ?? '', 'M d'); ?></p>
                        </div>
                        <span class="text-xs <?php echo getBookingStatusClass($booking['booking_status'] ?? ''); ?> px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($booking['booking_status'] ?? ''); ?></span>
                      </div>
                      <?php
                  }
              }
              ?>
             </div>
             <a href="my_reservation.php" class="text-xs text-amber-700 block mt-2 hover:underline">view all bookings →</a>
           </div>
           
           <!-- Notifications - Compact -->
           <div class="bg-white p-4 rounded-xl border border-slate-200">
             <div class="flex items-center justify-between mb-2">
               <h3 class="font-semibold flex gap-1"><i class="fa-regular fa-bell text-amber-600"></i> updates</h3>
              <span class="bg-slate-200 text-slate-700 text-xs px-2 py-0.5 rounded-full"><?php echo $unreadCount; ?></span>
             </div>
             <ul class="text-xs space-y-1">
              <?php
              if (empty($notifications)) {
                  echo '<li class="text-slate-400 text-xs text-center py-2">No notifications</li>';
              } else {
                  $displayNotifications = array_slice($notifications, 0, 3);
                  foreach ($displayNotifications as $notification) {
                      ?>
                      <li class="flex items-start gap-2 <?php echo !($notification['is_read'] ?? false) ? 'font-semibold' : ''; ?>">
                        <i class="fa-regular fa-bell text-amber-600 text-xs mt-0.5"></i>
                        <div class="flex-1">
                          <p class="text-xs"><?php echo htmlspecialchars($notification['title'] ?? ''); ?></p>
                          <p class="text-xs text-slate-500"><?php echo formatDate($notification['created_at'] ?? '', 'M d'); ?></p>
                        </div>
                      </li>
                      <?php
                  }
              }
              ?>
             </ul>
             <a href="notifications.php" class="text-xs text-amber-700 block mt-2 hover:underline">view all →</a>
           </div>
           
           <!-- Support - Compact -->
           <div class="bg-white p-4 rounded-xl border border-slate-200">
             <h3 class="font-semibold flex items-center gap-1 mb-2"><i class="fa-regular fa-headset text-amber-600"></i> support</h3>
            <div class="space-y-1 text-xs">
              <p>📞 +63 (2) 1234 5678</p>
              <p>💬 <a href="#" onclick="startChatSupport()" class="text-amber-700 hover:underline">chat with concierge</a></p>
              <p>📧 support@lucas.stay</p>
            </div>
           </div>
         </div>

         <!-- Bottom Section - Review & Actions - More Compact -->
         <div class="bg-white p-4 rounded-xl border border-slate-200 lg:col-span-3">
           <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-regular fa-star text-amber-600"></i> share your experience</h3>
           
           <div class="flex gap-4">
             <div class="flex-1">
               <!-- Star Rating -->
               <div class="flex items-center gap-2 mb-2">
                 <span class="text-sm text-slate-600">rate your stay:</span>
                 <div class="flex gap-1" id="starRating">
                   <i class="fa-regular fa-star text-yellow-400 cursor-pointer text-sm" data-rating="1"></i>
                   <i class="fa-regular fa-star text-yellow-400 cursor-pointer text-sm" data-rating="2"></i>
                   <i class="fa-regular fa-star text-yellow-400 cursor-pointer text-sm" data-rating="3"></i>
                   <i class="fa-regular fa-star text-yellow-400 cursor-pointer text-sm" data-rating="4"></i>
                   <i class="fa-regular fa-star text-yellow-400 cursor-pointer text-sm" data-rating="5"></i>
                 </div>
               </div>
               
               <!-- Review Text -->
               <textarea 
                 id="reviewText" 
                 placeholder="tell us about your experience..." 
                 class="w-full border border-slate-200 rounded-lg p-2 text-xs resize-none h-12 focus:ring-2 focus:ring-amber-500 outline-none"
               ></textarea>
             </div>
             
             <div class="flex gap-1.5">
               <button onclick="submitReview()" class="bg-amber-600 hover:bg-amber-700 text-white px-2 py-1 rounded text-xs font-medium transition">submit feedback</button>
               <button onclick="window.location.href='my_profile.php'" class="border border-slate-200 text-slate-700 px-2 py-1 rounded text-xs hover:bg-slate-50 transition">edit profile</button>
               <button onclick="window.location.href='payments.php'" class="border border-slate-200 text-slate-700 px-2 py-1 rounded text-xs hover:bg-slate-50 transition">manage payments</button>
             </div>
           </div>
         </div>
       </div>

        <!-- Bottom Hint -->
        <div class="mt-6 text-center text-xs text-slate-400 border-t pt-4">
          ✅ Dashboard module — connected to database with real-time updates
        </div>
      </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Update date/time
        function updateDate() {
           const date = new Date();
           const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
           document.getElementById('currentDate').textContent = date.toLocaleDateString('en-US', options).toLowerCase();
        }
        
        // Star rating functionality
        function initStarRating() {
           const stars = document.querySelectorAll('#starRating i');
           stars.forEach(star => {
               star.addEventListener('mouseenter', function() {
                   const rating = parseInt(this.dataset.rating);
                   stars.forEach((s, index) => {
                       if (index < rating) {
                           s.className = 'fa-solid fa-star text-yellow-400';
                       } else {
                           s.className = 'fa-regular fa-star text-yellow-400';
                       }
                   });
               });
               
               star.addEventListener('mouseleave', function() {
                   stars.forEach(s => {
                       s.className = 'fa-regular fa-star text-yellow-400';
                   });
               });
               
               star.addEventListener('click', function() {
                   const rating = parseInt(this.dataset.rating);
                   stars.forEach((s, index) => {
                       if (index < rating) {
                           s.className = 'fa-solid fa-star text-yellow-400';
                       } else {
                           s.className = 'fa-regular fa-star text-yellow-400';
                       }
                   });
               });
           });
        }
        
        // Submit review
        async function submitReview() {
           const reviewText = document.getElementById('reviewText').value;
           const selectedStars = document.querySelectorAll('#starRating .fa-solid').length;
           
           if (selectedStars === 0) {
               showToast('Please select a rating', 'error');
               return;
           }
           
           try {
               const response = await fetch('api/submit_review.php', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                   },
                   body: JSON.stringify({
                       review_type: 'hotel_stay',
                       rating: selectedStars,
                       review_text: reviewText
                   })
               });
               
               const data = await response.json();
               
               if (data.success) {
                   showToast('Thank you for your feedback!', 'success');
                   document.getElementById('reviewText').value = '';
                   
                   // Reset stars
                   document.querySelectorAll('#starRating i').forEach(s => {
                       s.className = 'fa-regular fa-star text-yellow-400';
                   });
                   
                   setTimeout(() => {
                       location.reload();
                   }, 1500);
               } else {
                   showToast(data.message || 'Failed to submit review', 'error');
               }
           } catch (error) {
               showToast('Failed to submit review', 'error');
           }
        }
        
        // Toast notification
        function showToast(message, type = 'info') {
           const toast = document.createElement('div');
           toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
               type === 'success' ? 'bg-green-500' : 
               type === 'error' ? 'bg-red-500' : 
               'bg-blue-500'
           }`;
           toast.textContent = message;
           
           document.body.appendChild(toast);
           
           setTimeout(() => {
               toast.remove();
           }, 3000);
        }
        
        // Initialize
        updateDate();
        initStarRating();
        
        // Set interval to update date every minute
        setInterval(updateDate, 60000);
    </script>
</body>
</html>
