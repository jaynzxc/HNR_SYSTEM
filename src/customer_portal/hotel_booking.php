<?php
/**
 * Hotel Booking Page - Traditional Layout Version
 * Inline booking forms with working filters
 */

session_start();
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/SessionManager.php';

// Check if user is logged in
$sessionManager = new SessionManager($database);
$currentUser = $sessionManager->getCurrentUser();

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

// Initialize database and user model
$database = new Database();
$db = $database->getConnection();
$userModel = new User($database);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_booking') {
        $bookingData = [
            'room_id' => $_POST['room_id'] ?? '',
            'check_in_date' => $_POST['check_in_date'] ?? '',
            'check_out_date' => $_POST['check_out_date'] ?? '',
            'number_of_guests' => $_POST['number_of_guests'] ?? 1,
            'special_requests' => $_POST['special_requests'] ?? ''
        ];
        
        // Validate required fields
        if (empty($bookingData['room_id']) || empty($bookingData['check_in_date']) || empty($bookingData['check_out_date'])) {
            $error = 'Please fill in all required fields';
        } else {
            // Get room details for payment calculation
            $roomDetails = $userModel->getRoomDetails($bookingData['room_id']);
            
            // Calculate total amount
            $checkIn = new DateTime($bookingData['check_in_date']);
            $checkOut = new DateTime($bookingData['check_out_date']);
            $nights = $checkIn->diff($checkOut)->days;
            $totalAmount = $roomDetails['price_per_night'] * $nights;
            
            // Create booking
            $result = $userModel->createHotelBooking($currentUser['user_id'], $bookingData);
            if ($result) {
                // Get the booking ID
                $bookingId = $db->lastInsertId();
                
                // Redirect to payment processing
                $description = "Hotel Booking - {$roomDetails['room_type']} ({$nights} nights)";
                $paymentUrl = "payment_process.php?type=hotel_booking&id={$bookingId}&amount={$totalAmount}&description=" . urlencode($description);
                header("Location: {$paymentUrl}");
                exit;
            } else {
                $success = '';
                $error = 'Failed to create hotel booking. Please try again.';
            }
        }
    }
}

// Get available rooms
$availableRooms = $userModel->getAvailableRooms(20);

// Add room ranking and colors
foreach ($availableRooms as &$room) {
    $roomType = strtolower($room['room_type'] ?? '');
    
    if (strpos($roomType, 'standard') !== false) {
        $room['ranking'] = 'Standard';
        $room['color'] = 'white';
        $room['border_color'] = 'border-slate-200';
        $room['text_color'] = 'text-slate-800';
    } elseif (strpos($roomType, 'deluxe') !== false) {
        $room['ranking'] = 'Deluxe';
        $room['color'] = 'bg-blue-50';
        $room['border_color'] = 'border-blue-200';
        $room['text_color'] = 'text-blue-800';
    } elseif (strpos($roomType, 'suite') !== false) {
        $room['ranking'] = 'Suite';
        $room['color'] = 'bg-purple-50';
        $room['border_color'] = 'border-purple-200';
        $room['text_color'] = 'text-purple-800';
    } elseif (strpos($roomType, 'executive') !== false || strpos($roomType, 'presidential') !== false) {
        $room['ranking'] = 'Presidential';
        $room['color'] = 'bg-black';
        $room['border_color'] = 'border-slate-900';
        $room['text_color'] = 'text-white';
    } else {
        // Default to Standard if no specific type found
        $room['ranking'] = 'Standard';
        $room['color'] = 'bg-white';
        $room['border_color'] = 'border-slate-200';
        $room['text_color'] = 'text-slate-800';
    }
}

// Helper functions
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

// Filter rooms function
function filterRooms($rooms, $type) {
    if ($type === 'all') return $rooms;
    
    $filtered = [];
    foreach ($rooms as $room) {
        $roomType = strtolower($room['room_type'] ?? '');
        if (strpos($roomType, strtolower($type)) !== false) {
            $filtered[] = $room;
        }
    }
    return $filtered;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Booking · Lùcas Customer Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    .toast { 
      animation: slideIn 0.3s ease-out; 
      background: white !important;
      border: 1px solid #e5e7eb !important;
      border-radius: 0.5rem !important;
      padding: 1rem !important;
      margin-bottom: 0.5rem !important;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
      min-width: 300px !important;
      max-width: 400px !important;
    }
    .toast.success {
      border-left: 4px solid #10b981 !important;
      background: #f0fdf4 !important;
    }
    .toast.error {
      border-left: 4px solid #ef4444 !important;
      background: #fef2f2 !important;
    }
    .toast.info {
      border-left: 4px solid #3b82f6 !important;
      background: #eff6ff !important;
    }
    .toast-content {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .toast-icon {
      flex-shrink: 0;
      font-size: 1.25rem;
    }
    .toast.success .toast-icon {
      color: #10b981;
    }
    .toast.error .toast-icon {
      color: #ef4444;
    }
    .toast.info .toast-icon {
      color: #3b82f6;
    }
    .toast-message {
      flex: 1;
      font-size: 0.875rem;
      font-weight: 500;
      color: #1f2937;
    }
    .room-card {
      transition: all 0.3s ease;
    }
    .room-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .filter-btn {
      transition: all 0.2s ease;
    }
    .filter-btn.active {
      background-color: #f59e0b !important;
      color: white !important;
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
        <p class="text-xs text-slate-500 mt-1">customer portal · hotel booking</p>
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
        <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>Dashboard</a>
        <a href="my_profile.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-user w-5 text-slate-400"></i>My Profile</a>
        <a href="hotel_booking.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-solid fa-hotel w-5 text-amber-600"></i>Hotel Booking</a>
        <a href="my_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-calendar-check w-5 text-slate-400"></i>My Reservations</a>
        <a href="restaurant_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-clock w-5 text-slate-400"></i>Restaurant Reservation</a>
        <a href="order_food.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-bag-shopping w-5 text-slate-400"></i>Menu / Order Food</a>
        <a href="payments.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-credit-card w-5 text-slate-400"></i>Payments</a>
        <a href="loyalty_rewards.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-star w-5 text-slate-400"></i>Loyalty Rewards</a>
        <a href="notifications.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition relative"><i class="fa-regular fa-bell w-5 text-slate-400"></i>Notifications<span class="ml-auto bg-amber-100 text-amber-800 text-xs px-1.5 py-0.5 rounded-full">0</span></a>
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
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">hotel booking</h1>
            <p class="text-sm text-slate-500 mt-0.5">find and book your perfect room</p>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <div class="p-4 lg:p-6">
        <div class="max-w-6xl mx-auto space-y-4">
          <!-- Success/Error Messages -->
          <?php if (isset($success)): ?>
            <div class="toast success">
              <div class="toast-content">
                <i class="fa-solid fa-check-circle toast-icon"></i>
                <span class="toast-message"><?php echo htmlspecialchars($success); ?></span>
              </div>
            </div>
          <?php endif; ?>
          
          <?php if (isset($error)): ?>
            <div class="toast error">
              <div class="toast-content">
                <i class="fa-solid fa-exclamation-circle toast-icon"></i>
                <span class="toast-message"><?php echo htmlspecialchars($error); ?></span>
              </div>
            </div>
          <?php endif; ?>

          <!-- Working Filters -->
          <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="flex flex-wrap items-center gap-3">
              <span class="text-sm font-medium text-slate-700">Filter by:</span>
              <button onclick="applyFilter('all')" class="filter-btn px-3 py-1 bg-amber-100 text-amber-800 rounded-lg text-xs hover:bg-amber-200 transition active">All Rooms</button>
              <button onclick="applyFilter('standard')" class="filter-btn px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs hover:bg-slate-200 transition">Standard</button>
              <button onclick="applyFilter('deluxe')" class="filter-btn px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs hover:bg-slate-200 transition">Deluxe</button>
              <button onclick="applyFilter('suite')" class="filter-btn px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs hover:bg-slate-200 transition">Suite</button>
              <div class="flex items-center gap-2 text-sm text-slate-500">
                <i class="fa-solid fa-bed"></i>
                <span id="roomCount"><?php echo count($availableRooms); ?> rooms available</span>
              </div>
            </div>
          </div>

          <!-- Available Rooms - Traditional Layout -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="roomsContainer">
            <?php if (empty($availableRooms)): ?>
              <div class="col-span-full bg-white rounded-xl border border-slate-200 p-8 text-center">
                <i class="fa-solid fa-bed text-4xl text-slate-300 mb-4"></i>
                <p class="text-lg text-slate-600">No rooms available</p>
                <p class="text-sm text-slate-500">Please check back later for available rooms.</p>
              </div>
            <?php else: ?>
              <?php foreach ($availableRooms as $room): ?>
                <div class="room-card <?php echo $room['color']; ?> rounded-xl border <?php echo $room['border_color']; ?> overflow-hidden">
                  <!-- Room Header -->
                  <div class="p-4">
                    <div class="flex justify-between items-start mb-3">
                      <div>
                        <div class="flex items-center gap-2 mb-1">
                          <h3 class="font-semibold <?php echo $room['text_color']; ?>"><?php echo htmlspecialchars(str_replace('_', ' ', $room['room_type'])); ?></h3>
                          <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $room['color'] === 'bg-black' ? 'bg-white text-black' : 'bg-slate-100 text-slate-600'; ?>">
                            <?php echo $room['ranking']; ?>
                          </span>
                        </div>
                        <p class="text-sm <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-500'; ?>"><?php echo htmlspecialchars($room['description'] ?? 'Comfortable and elegant room'); ?></p>
                      </div>
                      <div class="text-right">
                        <div class="text-xl font-bold <?php echo $room['color'] === 'bg-black' ? 'text-amber-400' : 'text-amber-600'; ?>"><?php echo formatCurrency($room['base_price_per_night'] ?? 0); ?></div>
                        <div class="text-xs <?php echo $room['color'] === 'bg-black' ? 'text-slate-400' : 'text-slate-500'; ?>">per night</div>
                      </div>
                    </div>
                    
                    <!-- Room Features -->
                    <div class="flex flex-wrap gap-2 mb-4">
                      <span class="flex items-center gap-1 text-xs <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-600'; ?>">
                        <i class="fa-solid fa-users text-[10px]"></i>
                        <?php echo $room['max_occupancy'] ?? 2; ?> guests
                      </span>
                      <span class="flex items-center gap-1 text-xs <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-600'; ?>">
                        <i class="fa-solid fa-bed text-[10px]"></i>
                        <?php echo $room['bed_type'] ?? '1 Queen Bed'; ?>
                      </span>
                      <span class="flex items-center gap-1 text-xs <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-600'; ?>">
                        <i class="fa-solid fa-ruler-combined text-[10px]"></i>
                        <?php echo $room['room_size'] ?? '25 sqm'; ?>
                      </span>
                    </div>
                  </div>
                  
                  <!-- Booking Form - Inline -->
                  <div class="border-t <?php echo $room['color'] === 'bg-black' ? 'border-slate-700' : 'border-slate-100'; ?> p-4 <?php echo $room['color'] === 'bg-black' ? 'bg-slate-900' : 'bg-slate-50'; ?>">
                    <form method="POST" class="space-y-3">
                      <input type="hidden" name="action" value="create_booking">
                      <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                      
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                          <label class="block text-xs font-medium <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-700'; ?> mb-1">check-in date</label>
                          <input type="date" name="check_in_date" required 
                                 class="w-full border <?php echo $room['color'] === 'bg-black' ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'; ?> rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                                 min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                          <label class="block text-xs font-medium <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-700'; ?> mb-1">check-out date</label>
                          <input type="date" name="check_out_date" required 
                                 class="w-full border <?php echo $room['color'] === 'bg-black' ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'; ?> rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                                 min="<?php echo date('Y-m-d'); ?>">
                        </div>
                      </div>
                      
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                          <label class="block text-xs font-medium <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-700'; ?> mb-1">guests</label>
                          <select name="number_of_guests" class="w-full border <?php echo $room['color'] === 'bg-black' ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'; ?> rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                            <option value="1">1 guest</option>
                            <option value="2">2 guests</option>
                            <option value="3">3 guests</option>
                            <option value="4">4 guests</option>
                          </select>
                        </div>
                        <div>
                          <label class="block text-xs font-medium <?php echo $room['color'] === 'bg-black' ? 'text-slate-300' : 'text-slate-700'; ?> mb-1">special requests</label>
                          <textarea name="special_requests" rows="2" 
                                    class="w-full border <?php echo $room['color'] === 'bg-black' ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'; ?> rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none resize-none"
                                    placeholder="Any special requests..."></textarea>
                        </div>
                      </div>
                      
                      <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg text-sm font-medium transition" onclick="handleBookingSubmit(event, this)">
                        book now
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Booking Tips -->
          <div class="bg-amber-50 rounded-xl border border-amber-200 p-4">
            <h3 class="font-semibold text-amber-800 text-sm mb-2 flex items-center gap-2">
              <i class="fa-solid fa-lightbulb"></i>
              booking tips
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-amber-700">
              <div class="flex items-start gap-2">
                <i class="fa-solid fa-check-circle mt-0.5"></i>
                <span>Book early for best rates</span>
              </div>
              <div class="flex items-start gap-2">
                <i class="fa-solid fa-check-circle mt-0.5"></i>
                <span>Weekend rates may apply</span>
              </div>
              <div class="flex items-start gap-2">
                <i class="fa-solid fa-check-circle mt-0.5"></i>
                <span>Free cancellation up to 24h</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Store all rooms globally
    let allRooms = <?php echo json_encode($availableRooms); ?>;
    let currentFilter = 'all';
    
    // Apply filter function
    function applyFilter(type) {
      currentFilter = type;
      
      // Update button states
      document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.remove('bg-amber-100', 'text-amber-800');
        btn.classList.add('bg-slate-100', 'text-slate-700');
      });
      
      // Set active button
      event.target.classList.remove('bg-slate-100', 'text-slate-700');
      event.target.classList.add('bg-amber-100', 'text-amber-800');
      
      // Filter rooms
      let filteredRooms;
      if (type === 'all') {
        filteredRooms = allRooms;
      } else {
        filteredRooms = allRooms.filter(room => {
          const roomType = (room.room_type || '').toLowerCase();
          return roomType.includes(type.toLowerCase());
        });
      }
      
      // Update room count
      document.getElementById('roomCount').textContent = filteredRooms.length + ' rooms available';
      
      // Rebuild room cards
      rebuildRoomCards(filteredRooms);
    }
    
    // Rebuild room cards function
    function rebuildRoomCards(rooms) {
      const container = document.getElementById('roomsContainer');
      
      if (rooms.length === 0) {
        container.innerHTML = `
          <div class="col-span-full bg-white rounded-xl border border-slate-200 p-8 text-center">
            <i class="fa-solid fa-bed text-4xl text-slate-300 mb-4"></i>
            <p class="text-lg text-slate-600">No rooms available</p>
            <p class="text-sm text-slate-500">Try changing your filter criteria.</p>
          </div>
        `;
        return;
      }
      
      container.innerHTML = rooms.map((room, index) => {
        // Determine room ranking and colors
        let ranking, color, borderColor, textColor;
        const roomType = (room.room_type || '').toLowerCase();
        
        if (roomType.includes('standard')) {
          ranking = 'Standard';
          color = 'bg-white';
          borderColor = 'border-slate-200';
          textColor = 'text-slate-800';
        } else if (roomType.includes('deluxe')) {
          ranking = 'Deluxe';
          color = 'bg-blue-50';
          borderColor = 'border-blue-200';
          textColor = 'text-blue-800';
        } else if (roomType.includes('suite')) {
          ranking = 'Suite';
          color = 'bg-purple-50';
          borderColor = 'border-purple-200';
          textColor = 'text-purple-800';
        } else if (roomType.includes('executive') || roomType.includes('presidential')) {
          ranking = 'Presidential';
          color = 'bg-black';
          borderColor = 'border-slate-900';
          textColor = 'text-white';
        } else {
          ranking = 'Standard';
          color = 'bg-white';
          borderColor = 'border-slate-200';
          textColor = 'text-slate-800';
        }
        
        const isBlack = color === 'bg-black';
        
        return `
        <div class="room-card ${color} rounded-xl border ${borderColor} overflow-hidden">
          <div class="p-4">
            <div class="flex justify-between items-start mb-3">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-semibold ${textColor}">${room.room_type.replace('_', ' ')}</h3>
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium ${isBlack ? 'bg-white text-black' : 'bg-slate-100 text-slate-600'}">
                    ${ranking}
                  </span>
                </div>
                <p class="text-sm ${isBlack ? 'text-slate-300' : 'text-slate-500'}">${room.description || 'Comfortable and elegant room'}</p>
              </div>
              <div class="text-right">
                <div class="text-xl font-bold ${isBlack ? 'text-amber-400' : 'text-amber-600'}">₱${Number(room.base_price_per_night || 0).toFixed(2)}</div>
                <div class="text-xs ${isBlack ? 'text-slate-400' : 'text-slate-500'}">per night</div>
              </div>
            </div>
            
            <div class="flex flex-wrap gap-2 mb-4">
              <span class="flex items-center gap-1 text-xs ${isBlack ? 'text-slate-300' : 'text-slate-600'}">
                <i class="fa-solid fa-users text-[10px]"></i>
                ${room.max_occupancy || 2} guests
              </span>
              <span class="flex items-center gap-1 text-xs ${isBlack ? 'text-slate-300' : 'text-slate-600'}">
                <i class="fa-solid fa-bed text-[10px]"></i>
                ${room.bed_type || '1 Queen Bed'}
              </span>
              <span class="flex items-center gap-1 text-xs ${isBlack ? 'text-slate-300' : 'text-slate-600'}">
                <i class="fa-solid fa-ruler-combined text-[10px]"></i>
                ${room.room_size || '25 sqm'}
              </span>
            </div>
          </div>
          
          <div class="border-t ${isBlack ? 'border-slate-700' : 'border-slate-100'} p-4 ${isBlack ? 'bg-slate-900' : 'bg-slate-50'}">
            <form method="POST" class="space-y-3">
              <input type="hidden" name="action" value="create_booking">
              <input type="hidden" name="room_id" value="${room.room_id}">
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium ${isBlack ? 'text-slate-300' : 'text-slate-700'} mb-1">check-in date</label>
                  <input type="date" name="check_in_date" required 
                         class="w-full border ${isBlack ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'} rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                         min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div>
                  <label class="block text-xs font-medium ${isBlack ? 'text-slate-300' : 'text-slate-700'} mb-1">check-out date</label>
                  <input type="date" name="check_out_date" required 
                         class="w-full border ${isBlack ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'} rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                         min="${new Date().toISOString().split('T')[0]}">
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium ${isBlack ? 'text-slate-300' : 'text-slate-700'} mb-1">guests</label>
                  <select name="number_of_guests" class="w-full border ${isBlack ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'} rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="1">1 guest</option>
                    <option value="2">2 guests</option>
                    <option value="3">3 guests</option>
                    <option value="4">4 guests</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium ${isBlack ? 'text-slate-300' : 'text-slate-700'} mb-1">special requests</label>
                  <textarea name="special_requests" rows="2" 
                            class="w-full border ${isBlack ? 'border-slate-600 bg-slate-800 text-white' : 'border-slate-200 bg-white'} rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none resize-none"
                            placeholder="Any special requests..."></textarea>
                </div>
              </div>
              
              <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg text-sm font-medium transition">
                book now
              </button>
            </form>
          </div>
        </div>
        `;
      }).join('');
    }
    
    // Handle booking submission with toast notifications
    function handleBookingSubmit(event, button) {
      event.preventDefault();
      
      const form = button.closest('form');
      const formData = new FormData(form);
      
      // Show loading state
      button.disabled = true;
      button.textContent = 'booking...';
      button.classList.add('opacity-75');
      
      // Submit via fetch API
      fetch(window.location.href, {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(html => {
        // Parse the response to check for success/error messages
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Look for success message
        const successElement = doc.querySelector('.toast.success .toast-message');
        const errorElement = doc.querySelector('.toast.error .toast-message');
        
        if (successElement) {
          showToast(successElement.textContent, 'success');
          // Redirect to booking confirmation after 2 seconds
          setTimeout(() => {
            window.location.href = 'booking_confirmation.php';
          }, 2000);
        } else if (errorElement) {
          showToast(errorElement.textContent, 'error');
        } else {
          showToast('Booking completed. Please check your email for confirmation.', 'info');
          setTimeout(() => {
            window.location.href = 'booking_confirmation.php';
          }, 2000);
        }
        
        // Reset button state
        button.disabled = false;
        button.textContent = 'book now';
        button.classList.remove('opacity-75');
      })
      .catch(error => {
        showToast('Failed to submit booking. Please try again.', 'error');
        button.disabled = false;
        button.textContent = 'book now';
        button.classList.remove('opacity-75');
      });
    }
    
    // Toast notification function
    function showToast(message, type = 'info') {
      // Remove existing toasts
      const existingToasts = document.querySelectorAll('.toast');
      existingToasts.forEach(toast => toast.remove());
      
      // Create new toast
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <div class="toast-content">
          <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} toast-icon"></i>
          <span class="toast-message">${message}</span>
        </div>
      `;
      
      // Position toast
      toast.style.position = 'fixed';
      toast.style.top = '20px';
      toast.style.right = '20px';
      toast.style.zIndex = '9999';
      
      // Add to page
      document.body.appendChild(toast);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 5000);
    }
  </script>
</body>
</html>
