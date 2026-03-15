<?php
/**
 * Booking Confirmation Page
 * Shows booking details and processes payments with receipts
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

// Get recent booking (for demo - in real app, you'd get by booking reference)
$recentBooking = $userModel->getRecentBooking($currentUser['user_id']);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Confirmation · Lùcas Customer Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
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
    .processing-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    .processing-spinner {
      width: 50px;
      height: 50px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #f59e0b;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="bg-slate-50 font-sans antialiased">
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-80 bg-white border-r border-slate-200 shadow-lg shrink-0">
      <div class="px-6 py-7 border-b border-slate-100">
        <div class="flex items-center gap-2 text-amber-700">
          <i class="fa-solid fa-utensils text-xl"></i>
          <i class="fa-solid fa-bed text-xl"></i>
          <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.stay</span></span>
        </div>
        <p class="text-xs text-slate-500 mt-1">customer portal · booking confirmation</p>
      </div>
      <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80">
        <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg">
          <?php echo getUserInitials($currentUser['first_name'] ?? '', $currentUser['last_name'] ?? ''); ?>
        </div>
        <div>
          <p class="font-medium text-slate-800"><?php echo htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')); ?></p>
          <p class="text-xs text-slate-500 flex items-center gap-1"><i class="fa-regular fa-gem text-[11px]"></i> <span><?php echo htmlspecialchars($currentUser['membership_tier'] ?? 'member'); ?></span> · <span><?php echo number_format($currentUser['loyalty_points'] ?? 0); ?> pts</span></p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-1.5 text-sm overflow-y-auto" style="max-height: calc(100vh - 280px);">
        <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>Dashboard</a>
        <a href="my_profile.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-user w-5 text-slate-400"></i>My Profile</a>
        <a href="hotel_booking.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-hotel w-5 text-slate-400"></i>Hotel Booking</a>
        <a href="my_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-calendar-check w-5 text-slate-400"></i>My Reservations</a>
        <a href="restaurant_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-clock w-5 text-slate-400"></i>Restaurant Reservation</a>
        <a href="order_food.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-bag-shopping w-5 text-slate-400"></i>Menu / Order Food</a>
        <a href="payments.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-regular fa-credit-card w-5 text-amber-600"></i>Payments</a>
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
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">booking confirmation</h1>
            <p class="text-sm text-slate-500 mt-0.5">review your booking details and complete payment</p>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <div class="p-4 lg:p-6">
        <div class="max-w-4xl mx-auto space-y-6">
          <!-- Success Message -->
          <div class="toast success">
            <div class="toast-content">
              <i class="fa-solid fa-check-circle toast-icon"></i>
              <span class="toast-message">Booking confirmed! Your room has been reserved.</span>
            </div>
          </div>

          <?php if ($recentBooking): ?>
            <!-- Booking Details Card -->
            <div class="bg-white rounded-xl border border-slate-200 p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-slate-800">booking details</h2>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">confirmed</span>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <p class="text-sm text-slate-500 mb-1">booking reference</p>
                    <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($recentBooking['booking_reference'] ?? 'HB20250313ABC123'); ?></p>
                  </div>
                  <div>
                    <p class="text-sm text-slate-500 mb-1">room type</p>
                    <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($recentBooking['room_type'] ?? 'Deluxe Room'); ?></p>
                  </div>
                  <div>
                    <p class="text-sm text-slate-500 mb-1">check-in</p>
                    <p class="font-semibold text-slate-800"><?php echo formatDate($recentBooking['check_in_date'] ?? date('Y-m-d', strtotime('+2 days'))); ?></p>
                  </div>
                  <div>
                    <p class="text-sm text-slate-500 mb-1">check-out</p>
                    <p class="font-semibold text-slate-800"><?php echo formatDate($recentBooking['check_out_date'] ?? date('Y-m-d', strtotime('+4 days'))); ?></p>
                  </div>
                </div>
                
                <div class="space-y-4">
                  <div>
                    <p class="text-sm text-slate-500 mb-1">number of guests</p>
                    <p class="font-semibold text-slate-800"><?php echo $recentBooking['number_of_guests'] ?? 2; ?> guests</p>
                  </div>
                  <div>
                    <p class="text-sm text-slate-500 mb-1">total amount</p>
                    <p class="text-2xl font-bold text-amber-600"><?php echo formatCurrency($recentBooking['total_amount'] ?? 9000); ?></p>
                  </div>
                  <div>
                    <p class="text-sm text-slate-500 mb-1">payment status</p>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">pending</span>
                  </div>
                  <div>
                    <p class="text-sm text-slate-500 mb-1">special requests</p>
                    <p class="text-slate-800"><?php echo htmlspecialchars($recentBooking['special_requests'] ?? 'None'); ?></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Section -->
            <div class="bg-white rounded-xl border border-slate-200 p-6">
              <h2 class="text-xl font-semibold text-slate-800 mb-6">payment processing</h2>
              
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Payment Methods -->
                <div>
                  <h3 class="font-medium text-slate-700 mb-4">select payment method</h3>
                  <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-amber-50 transition">
                      <input type="radio" name="payment_method" value="credit_card" class="text-amber-600" checked>
                      <span class="flex-1">
                        <i class="fa-regular fa-credit-card mr-2"></i>
                        <span class="font-medium">Credit Card</span>
                      </span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-amber-50 transition">
                      <input type="radio" name="payment_method" value="debit_card" class="text-amber-600">
                      <span class="flex-1">
                        <i class="fa-regular fa-credit-card mr-2"></i>
                        <span class="font-medium">Debit Card</span>
                      </span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-amber-50 transition">
                      <input type="radio" name="payment_method" value="paypal" class="text-amber-600">
                      <span class="flex-1">
                        <i class="fa-brands fa-paypal mr-2"></i>
                        <span class="font-medium">PayPal</span>
                      </span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-amber-50 transition">
                      <input type="radio" name="payment_method" value="bank_transfer" class="text-amber-600">
                      <span class="flex-1">
                        <i class="fa-regular fa-building-columns mr-2"></i>
                        <span class="font-medium">Bank Transfer</span>
                      </span>
                    </label>
                  </div>
                </div>

                <!-- Payment Form -->
                <div>
                  <h3 class="font-medium text-slate-700 mb-4">payment details</h3>
                  <form id="paymentForm" class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">cardholder name</label>
                      <input type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none" placeholder="John Doe">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">card number</label>
                      <input type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none" placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">expiry date</label>
                        <input type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none" placeholder="MM/YY">
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">cvv</label>
                        <input type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none" placeholder="123">
                      </div>
                    </div>
                    <div class="flex gap-4">
                      <button type="button" onclick="processPayment()" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-lg font-medium transition">
                        <i class="fa-solid fa-lock mr-2"></i>
                        complete payment
                      </button>
                      <button type="button" onclick="generateReceipt()" class="flex-1 border border-slate-200 text-slate-700 py-3 rounded-lg font-medium hover:bg-slate-50 transition">
                        <i class="fa-solid fa-receipt mr-2"></i>
                        generate receipt
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php else: ?>
            <!-- No Booking Found -->
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
              <i class="fa-solid fa-bed text-4xl text-slate-300 mb-4"></i>
              <p class="text-lg text-slate-600">No booking found</p>
              <p class="text-sm text-slate-500">Please make a booking first.</p>
              <a href="hotel_booking.php" class="inline-block mt-4 bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-lg font-medium transition">
                make a booking
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

  <!-- Processing Overlay -->
  <div id="processingOverlay" class="processing-overlay hidden">
    <div class="bg-white rounded-xl p-8 shadow-xl text-center">
      <div class="processing-spinner mx-auto mb-4"></div>
      <p class="text-lg font-medium text-slate-800">Processing payment...</p>
      <p class="text-sm text-slate-500">Please do not close this window</p>
    </div>
  </div>

  <script>
    // Process payment function
    function processPayment() {
      const overlay = document.getElementById('processingOverlay');
      overlay.classList.remove('hidden');
      
      // Simulate payment processing
      setTimeout(() => {
        overlay.classList.add('hidden');
        showToast('Payment processed successfully! Receipt generated.', 'success');
        
        // Update payment status
        const statusElements = document.querySelectorAll('.bg-yellow-100');
        statusElements.forEach(el => {
          el.classList.remove('bg-yellow-100', 'text-yellow-800');
          el.classList.add('bg-green-100', 'text-green-800');
          el.textContent = 'paid';
        });
        
        // Generate receipt automatically
        generateReceipt();
      }, 3000);
    }
    
    // Generate receipt function
    function generateReceipt() {
      const bookingRef = '<?php echo $recentBooking['booking_reference'] ?? 'HB20250313ABC123'; ?>';
      const totalAmount = '<?php echo $recentBooking['total_amount'] ?? 9000; ?>';
      const roomType = '<?php echo $recentBooking['room_type'] ?? 'Deluxe Room'; ?>';
      const checkIn = '<?php echo formatDate($recentBooking['check_in_date'] ?? date('Y-m-d', strtotime('+2 days'))); ?>';
      const checkOut = '<?php echo formatDate($recentBooking['check_out_date'] ?? date('Y-m-d', strtotime('+4 days'))); ?>';
      
      // Create receipt content
      const receiptContent = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd;">
          <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #f59e0b; margin: 0;">LÙCAS HOTEL & RESTAURANT</h1>
            <h2 style="color: #64748b; margin: 5px 0 20px 0;">PAYMENT RECEIPT</h2>
          </div>
          <div style="margin-bottom: 20px;">
            <p><strong>Booking Reference:</strong> ${bookingRef}</p>
            <p><strong>Room Type:</strong> ${roomType}</p>
            <p><strong>Check-in:</strong> ${checkIn}</p>
            <p><strong>Check-out:</strong> ${checkOut}</p>
            <p><strong>Total Amount:</strong> ₱${totalAmount}</p>
            <p><strong>Payment Date:</strong> ${new Date().toLocaleDateString()}</p>
            <p><strong>Payment Method:</strong> Credit Card</p>
            <p><strong>Status:</strong> PAID</p>
          </div>
          <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p style="color: #64748b; font-size: 14px;">Thank you for your booking!</p>
            <p style="color: #64748b; font-size: 12px;">Please keep this receipt for your records.</p>
          </div>
        </div>
      `;
      
      // Create and download receipt
      const blob = new Blob([receiptContent], { type: 'text/html' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `receipt_${bookingRef}.html`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
      
      showToast('Receipt downloaded successfully!', 'success');
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
