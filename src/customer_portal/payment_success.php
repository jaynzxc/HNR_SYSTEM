<?php
/**
 * Payment Success Page
 * Shows payment confirmation and details
 */

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

// Get payment details from URL
$paymentReference = $_GET['reference'] ?? '';
$paymentId = $_GET['payment_id'] ?? '';
$success = $_GET['success'] ?? '';

// Get payment details
$paymentDetails = null;
if ($paymentId) {
    $sql = "SELECT p.*, u.first_name, u.last_name FROM payments p JOIN users u ON p.user_id = u.user_id WHERE p.payment_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$paymentId]);
    $paymentDetails = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Function to get entity details
function getEntityDetails($paymentType, $entityId, $userModel) {
    switch ($paymentType) {
        case 'hotel_booking':
            return $userModel->getUserBookingDetails($entityId);
        case 'restaurant_reservation':
            return $userModel->getUserReservationDetails($entityId);
        case 'food_order':
            return $userModel->getFoodOrderDetails($entityId);
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
    <title>Payment Successful · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .success-animation {
            animation: slideIn 0.5s ease-out;
        }
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #f59e0b;
            animation: fall 3s linear;
        }
        @keyframes fall {
            to { transform: translateY(100vh) rotate(360deg); }
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <button onclick="history.back()" class="text-slate-600 hover:text-slate-800">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <h1 class="text-xl font-bold text-slate-800">Payment Confirmation</h1>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm text-slate-500">Welcome back,</p>
                        <p class="font-medium text-slate-800"><?php echo htmlspecialchars($currentUser['first_name']); ?></p>
                    </div>
                    <div class="w-10 h-10 bg-amber-600 text-white rounded-full flex items-center justify-center font-medium">
                        <?php echo getUserInitials($currentUser['first_name'], $currentUser['last_name']); ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($success === 'true' && $paymentDetails): ?>
            <!-- Success Message -->
            <div class="bg-green-50 border border-green-200 rounded-2xl p-8 mb-6 success-animation">
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-check text-green-600 text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-green-800 mb-2">Payment Successful!</h2>
                    <p class="text-green-700 mb-4">Your payment has been processed successfully</p>
                    <div class="inline-flex items-center gap-2 bg-green-100 px-4 py-2 rounded-full">
                        <i class="fa-solid fa-receipt text-green-600"></i>
                        <span class="font-medium text-green-800">Reference: <?php echo htmlspecialchars($paymentDetails['payment_reference']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">Payment Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Payment Reference</p>
                        <p class="font-medium text-slate-800"><?php echo htmlspecialchars($paymentDetails['payment_reference']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Payment Date</p>
                        <p class="font-medium text-slate-800"><?php echo date('M d, Y h:i A', strtotime($paymentDetails['paid_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Payment Method</p>
                        <p class="font-medium text-slate-800"><?php echo ucfirst($paymentDetails['payment_gateway']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Transaction ID</p>
                        <p class="font-medium text-slate-800"><?php echo htmlspecialchars($paymentDetails['gateway_transaction_id']); ?></p>
                    </div>
                </div>
                
                <div class="border-t border-slate-200 pt-4 mt-4">
                    <div class="flex items-center justify-between">
                        <p class="text-lg font-semibold text-slate-800">Total Amount Paid</p>
                        <p class="text-2xl font-bold text-amber-600"><?php echo formatCurrency($paymentDetails['amount']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Entity Details -->
            <?php
            $entityDetails = getEntityDetails($paymentDetails['payment_type'], $paymentDetails['related_entity_id'], $userModel);
            if ($entityDetails):
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">
                    <?php 
                    switch ($paymentDetails['payment_type']) {
                        case 'hotel_booking': echo 'Hotel Booking Details'; break;
                        case 'restaurant_reservation': echo 'Restaurant Reservation Details'; break;
                        case 'food_order': echo 'Food Order Details'; break;
                        default: echo 'Booking Details';
                    }
                    ?>
                </h3>
                
                <div class="space-y-3">
                    <?php if ($paymentDetails['payment_type'] === 'hotel_booking'): ?>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Booking ID</span>
                            <span class="font-medium text-slate-800">#<?php echo $entityDetails['booking_id']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Room Type</span>
                            <span class="font-medium text-slate-800"><?php echo htmlspecialchars($entityDetails['room_type']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Check-in</span>
                            <span class="font-medium text-slate-800"><?php echo date('M d, Y', strtotime($entityDetails['check_in_date'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Check-out</span>
                            <span class="font-medium text-slate-800"><?php echo date('M d, Y', strtotime($entityDetails['check_out_date'])); ?></span>
                        </div>
                    <?php elseif ($paymentDetails['payment_type'] === 'restaurant_reservation'): ?>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Reservation ID</span>
                            <span class="font-medium text-slate-800">#<?php echo $entityDetails['reservation_id']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Date</span>
                            <span class="font-medium text-slate-800"><?php echo date('M d, Y', strtotime($entityDetails['reservation_date'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Time</span>
                            <span class="font-medium text-slate-800"><?php echo date('h:i A', strtotime($entityDetails['reservation_time'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Guests</span>
                            <span class="font-medium text-slate-800"><?php echo $entityDetails['number_of_guests']; ?> people</span>
                        </div>
                    <?php elseif ($paymentDetails['payment_type'] === 'food_order'): ?>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Order ID</span>
                            <span class="font-medium text-slate-800">#<?php echo $entityDetails['order_id']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Order Type</span>
                            <span class="font-medium text-slate-800"><?php echo ucfirst($entityDetails['order_type']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Items</span>
                            <span class="font-medium text-slate-800"><?php echo count($entityDetails['items']); ?> items</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <button onclick="window.print()" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-medium transition">
                    <i class="fa-solid fa-print mr-2"></i>
                    Print Receipt
                </button>
                <a href="index.php" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3 rounded-xl font-medium transition text-center">
                    <i class="fa-solid fa-home mr-2"></i>
                    Back to Dashboard
                </a>
            </div>

        <?php else: ?>
            <!-- Payment Failed -->
            <div class="bg-red-50 border border-red-200 rounded-2xl p-8 mb-6">
                <div class="text-center">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-times text-red-600 text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-red-800 mb-2">Payment Failed</h2>
                    <p class="text-red-700 mb-4">We couldn't process your payment. Please try again.</p>
                    <div class="inline-flex items-center gap-2 bg-red-100 px-4 py-2 rounded-full">
                        <i class="fa-solid fa-exclamation-triangle text-red-600"></i>
                        <span class="font-medium text-red-800">Transaction Failed</span>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="payment_process.php?type=<?php echo urlencode($_GET['type'] ?? ''); ?>&id=<?php echo urlencode($_GET['id'] ?? ''); ?>&amount=<?php echo urlencode($_GET['amount'] ?? ''); ?>" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-medium transition text-center">
                    <i class="fa-solid fa-redo mr-2"></i>
                    Try Again
                </a>
                <a href="index.php" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3 rounded-xl font-medium transition text-center">
                    <i class="fa-solid fa-home mr-2"></i>
                    Back to Dashboard
                </a>
            </div>

        <?php endif; ?>
    </main>

    <script>
        // Create confetti effect for successful payments
        <?php if ($success === 'true'): ?>
        function createConfetti() {
            const colors = ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 2 + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 3000);
                }, i * 50);
            }
        }
        
        // Start confetti on page load
        window.addEventListener('load', createConfetti);
        <?php endif; ?>
    </script>
</body>
</html>
