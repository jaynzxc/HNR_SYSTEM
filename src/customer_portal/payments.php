<?php
/**
 * Payments Page - PHP Version
 * Complete payment management with database integration
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

// Handle form submissions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_payment_method') {
        $paymentData = [
            'method_type' => $_POST['method_type'] ?? '',
            'method_nickname' => $_POST['method_nickname'] ?? '',
            'provider_name' => $_POST['provider_name'] ?? '',
            'account_number' => $_POST['account_number'] ?? '',
            'expiry_date' => $_POST['expiry_date'] ?? ''
        ];
        
        // Validation
        if (empty($paymentData['method_type']) || empty($paymentData['method_nickname'])) {
            $error = 'Please fill in all required fields';
        } else {
            // Add payment method
            $result = $userModel->addPaymentMethod($currentUser['user_id'], $paymentData);
            if ($result) {
                $success = 'Payment method added successfully!';
            } else {
                $error = 'Failed to add payment method. Please try again.';
            }
        }
    }
}

// Get user's payment methods
$paymentMethods = $userModel->getUserPaymentMethods($currentUser['user_id']);

// Get pending payments
$pendingPayments = $userModel->getPendingPayments($currentUser['user_id']);

// Get payment history
$paymentHistory = $userModel->getPaymentHistory($currentUser['user_id'], 20);

// Helper functions
function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date, $format = 'M d, Y h:i A') {
    if (empty($date)) return 'Unknown';
    return date($format, strtotime($date));
}

function getPaymentStatusClass($status) {
    $statusClasses = [
        'completed' => 'bg-green-100 text-green-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'failed' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-slate-100 text-slate-700',
        'processing' => 'bg-blue-100 text-blue-700'
    ];
    return $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
}

function getPaymentTypeIcon($paymentType) {
    $icons = [
        'hotel_booking' => 'fa-solid fa-hotel',
        'restaurant_reservation' => 'fa-solid fa-utensils',
        'food_order' => 'fa-solid fa-bag-shopping',
        'loyalty_reward' => 'fa-solid fa-star'
    ];
    return $icons[$paymentType] ?? 'fa-solid fa-receipt';
}

function getPaymentGatewayIcon($gateway) {
    $icons = [
        'gcash' => 'fa-solid fa-wallet text-blue-600',
        'maya' => 'fa-solid fa-wallet text-purple-600',
        'credit_card' => 'fa-solid fa-credit-card text-green-600',
        'cash' => 'fa-solid fa-money-bill-wave text-amber-600'
    ];
    return $icons[$gateway] ?? 'fa-solid fa-credit-card text-slate-600';
}

function getEntityDetails($paymentType, $entityId, $userModel) {
    switch ($paymentType) {
        case 'hotel_booking':
            $booking = $userModel->getUserBookingDetails($entityId);
            return $booking ? [
                'title' => 'Hotel Booking',
                'reference' => '#' . $booking['booking_id'],
                'details' => $booking['room_type'] . ' - ' . date('M d, Y', strtotime($booking['check_in_date']))
            ] : null;
        case 'restaurant_reservation':
            $reservation = $userModel->getUserReservationDetails($entityId);
            return $reservation ? [
                'title' => 'Restaurant Reservation',
                'reference' => '#' . $reservation['reservation_id'],
                'details' => date('M d, Y h:i A', strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']))
            ] : null;
        case 'food_order':
            $order = $userModel->getFoodOrderDetails($entityId);
            return $order ? [
                'title' => 'Food Order',
                'reference' => '#' . $order['order_id'],
                'details' => $order['order_type'] . ' - ' . count($order['items']) . ' items'
            ] : null;
        default:
            return null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
            animation: fadeIn 0.6s ease-out;
        }
        .payment-card {
            transition: all 0.3s ease;
        }
        .payment-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
        }
        .tab-active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm shrink-0">
            <div class="px-6 py-7 border-b border-slate-100">
                <div class="flex items-center gap-2 text-amber-700">
                    <i class="fa-solid fa-utensils text-xl"></i>
                    <i class="fa-solid fa-bed text-xl"></i>
                    <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.stay</span></span>
                </div>
                <p class="text-xs text-slate-500 mt-1">customer portal · payments</p>
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
            <nav class="p-4 space-y-1.5 text-sm">
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
        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">payments</h1>
                    <p class="text-slate-500">manage your payment methods and transactions</p>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check-circle"></i>
                            <span><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-exclamation-circle"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <div class="flex mb-6 bg-slate-100 rounded-xl p-1">
                    <button type="button" onclick="switchTab('methods')" id="methodsTab" class="flex-1 px-4 py-2 rounded-lg font-medium transition tab-active">
                        <i class="fa-regular fa-credit-card mr-2"></i>Payment Methods
                    </button>
                    <button type="button" onclick="switchTab('pending')" id="pendingTab" class="flex-1 px-4 py-2 rounded-lg font-medium transition">
                        <i class="fa-regular fa-clock mr-2"></i>Pending Payments
                    </button>
                    <button type="button" onclick="switchTab('history')" id="historyTab" class="flex-1 px-4 py-2 rounded-lg font-medium transition">
                        <i class="fa-regular fa-history mr-2"></i>Payment History
                    </button>
                </div>

                <!-- Payment Methods -->
                <div id="paymentMethods" class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-regular fa-credit-card text-amber-600"></i>
                            add payment method
                        </h2>
                        
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add_payment_method">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">card type *</label>
                                    <select name="card_type" required class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none">
                                        <option value="">Select card type</option>
                                        <option value="visa">Visa</option>
                                        <option value="mastercard">Mastercard</option>
                                        <option value="amex">American Express</option>
                                        <option value="discover">Discover</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">cardholder name *</label>
                                    <input type="text" name="cardholder_name" required 
                                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none"
                                           placeholder="John Doe">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">card number *</label>
                                <input type="text" name="card_number" required 
                                       class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none"
                                       placeholder="1234 5678 9012 3456"
                                       maxlength="19">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">expiry date *</label>
                                    <input type="text" name="expiry_date" required 
                                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none"
                                           placeholder="MM/YY"
                                           maxlength="5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">cvv *</label>
                                    <input type="text" name="cvv" required 
                                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none"
                                           placeholder="123"
                                           maxlength="4">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">billing address</label>
                                <textarea name="billing_address" rows="2"
                                          class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none resize-none"
                                          placeholder="Enter billing address..."></textarea>
                            </div>
                            
                            <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-medium">
                                <i class="fa-regular fa-plus-circle mr-2"></i>
                                add payment method
                            </button>
                        </form>
                    </div>

                    <!-- Existing Payment Methods -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">your payment methods</h3>
                        
                        <?php if (empty($paymentMethods)): ?>
                            <div class="text-center py-8">
                                <i class="fa-regular fa-credit-card text-4xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500">No payment methods added yet</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($paymentMethods as $method): ?>
                                    <div class="payment-card border border-slate-200 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-3">
                                                <i class="<?php echo getCardTypeIcon($method['card_type']); ?> text-2xl text-slate-600"></i>
                                                <div>
                                                    <p class="font-medium text-slate-800"><?php echo htmlspecialchars($method['card_type']); ?></p>
                                                    <p class="text-sm text-slate-500">**** **** **** <?php echo substr($method['card_number'] ?? '0000', -4); ?></p>
                                                </div>
                                            </div>
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                                Active
                                            </span>
                                        </div>
                                        <div class="text-sm text-slate-600">
                                            <p><?php echo htmlspecialchars($method['cardholder_name']); ?></p>
                                            <p>Expires: <?php echo htmlspecialchars($method['expiry_date']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div id="pendingPayments" class="hidden">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-regular fa-clock text-amber-600"></i>
                            pending payments
                        </h2>
                        
                        <?php if (empty($pendingPayments)): ?>
                            <div class="text-center py-8">
                                <i class="fa-regular fa-check-circle text-4xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500">No pending payments</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($pendingPayments as $payment): ?>
                                    <div class="border border-slate-200 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h3 class="font-medium text-slate-800"><?php echo htmlspecialchars($payment['description'] ?? 'Payment'); ?></h3>
                                                <p class="text-sm text-slate-500">Due: <?php echo formatDate($payment['due_date']); ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xl font-bold text-amber-600"><?php echo formatCurrency($payment['amount']); ?></p>
                                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-full">
                                                    Pending
                                                </span>
                                            </div>
                                        </div>
                                        <form method="POST" class="flex gap-2">
                                            <input type="hidden" name="action" value="process_payment">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                            <input type="hidden" name="amount" value="<?php echo $payment['amount']; ?>">
                                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                                                Pay Now
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment History -->
                <div id="paymentHistory" class="hidden">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-regular fa-history text-amber-600"></i>
                            payment history
                        </h2>
                        
                        <?php if (empty($paymentHistory)): ?>
                            <div class="text-center py-8">
                                <i class="fa-regular fa-receipt text-4xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500">No payment history yet</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-slate-50 border-b border-slate-200">
                                        <tr>
                                            <th class="text-left p-4 font-medium text-slate-700">Date</th>
                                            <th class="text-left p-4 font-medium text-slate-700">Description</th>
                                            <th class="text-left p-4 font-medium text-slate-700">Method</th>
                                            <th class="text-right p-4 font-medium text-slate-700">Amount</th>
                                            <th class="text-left p-4 font-medium text-slate-700">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($paymentHistory as $payment): ?>
                                            <tr class="border-b border-slate-100">
                                                <td class="p-4">
                                                    <span class="text-sm text-slate-600"><?php echo formatDate($payment['payment_date']); ?></span>
                                                </td>
                                                <td class="p-4">
                                                    <span class="font-medium text-slate-800"><?php echo htmlspecialchars($payment['description'] ?? 'Payment'); ?></span>
                                                </td>
                                                <td class="p-4">
                                                    <span class="text-sm text-slate-600"><?php echo htmlspecialchars($payment['payment_method'] ?? 'Card'); ?></span>
                                                </td>
                                                <td class="p-4 text-right">
                                                    <span class="font-medium text-amber-600"><?php echo formatCurrency($payment['amount']); ?></span>
                                                </td>
                                                <td class="p-4">
                                                    <span class="text-xs px-2 py-1 rounded-full <?php echo getPaymentStatusClass($payment['status'] ?? 'completed'); ?>">
                                                        <?php echo htmlspecialchars($payment['status'] ?? 'completed'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab switching
        function switchTab(tab) {
            const methodsTab = document.getElementById('methodsTab');
            const pendingTab = document.getElementById('pendingTab');
            const historyTab = document.getElementById('historyTab');
            const paymentMethods = document.getElementById('paymentMethods');
            const pendingPayments = document.getElementById('pendingPayments');
            const paymentHistory = document.getElementById('paymentHistory');
            
            // Hide all
            paymentMethods.classList.add('hidden');
            pendingPayments.classList.add('hidden');
            paymentHistory.classList.add('hidden');
            methodsTab.classList.remove('tab-active');
            pendingTab.classList.remove('tab-active');
            historyTab.classList.remove('tab-active');
            
            // Show selected
            if (tab === 'methods') {
                methodsTab.classList.add('tab-active');
                paymentMethods.classList.remove('hidden');
            } else if (tab === 'pending') {
                pendingTab.classList.add('tab-active');
                pendingPayments.classList.remove('hidden');
            } else if (tab === 'history') {
                historyTab.classList.add('tab-active');
                paymentHistory.classList.remove('hidden');
            }
        }
        
        // Card number formatting
        document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
        
        // Expiry date formatting
        document.querySelector('input[name="expiry_date"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
        
        // CVV validation (numbers only)
        document.querySelector('input[name="cvv"]').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>
