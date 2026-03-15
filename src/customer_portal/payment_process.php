<?php
/**
 * Payment Processing System
 * Handles payments for hotel bookings, restaurant reservations, and food orders
 * Generates payment scripts and processes transactions
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

// Handle form submissions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'process_payment') {
        $paymentType = $_POST['payment_type'] ?? '';
        $entityId = $_POST['entity_id'] ?? '';
        $amount = $_POST['amount'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';
        $paymentGateway = $_POST['payment_gateway'] ?? '';
        
        if (empty($paymentType) || empty($entityId) || empty($amount)) {
            $error = 'Missing required payment information';
        } else {
            // Generate payment reference
            $paymentReference = 'PAY' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            
            // Create payment record
            $sql = "INSERT INTO payments (user_id, payment_reference, payment_type, related_entity_id, amount, payment_method_id, status, payment_gateway, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, CURRENT_TIMESTAMP)";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                $currentUser['user_id'],
                $paymentReference,
                $paymentType,
                $entityId,
                $amount,
                $paymentMethod ?: null,
                $paymentGateway
            ]);
            
            if ($result) {
                $paymentId = $db->lastInsertId();
                
                // Process payment based on gateway
                $paymentResult = processPaymentGateway($paymentGateway, $paymentId, $amount, $paymentReference);
                
                if ($paymentResult['success']) {
                    // Update payment status
                    $sql = "UPDATE payments SET status = 'completed', paid_at = CURRENT_TIMESTAMP, gateway_transaction_id = ? WHERE payment_id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$paymentResult['transaction_id'], $paymentId]);
                    
                    // Update related entity status
                    updateEntityStatus($paymentType, $entityId, 'confirmed');
                    
                    $success = "Payment processed successfully! Reference: {$paymentReference}";
                } else {
                    // Update payment status to failed
                    $sql = "UPDATE payments SET status = 'failed' WHERE payment_id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$paymentId]);
                    
                    $error = "Payment failed: " . $paymentResult['message'];
                }
            } else {
                $error = 'Failed to create payment record';
            }
        }
    }
}

// Get payment details from URL parameters
$paymentType = $_GET['type'] ?? '';
$entityId = $_GET['id'] ?? '';
$amount = $_GET['amount'] ?? '';
$description = $_GET['description'] ?? '';


// Handle empty payment parameters
$paymentType = $_GET['type'] ?? '';
$entityId = $_GET['id'] ?? '';
$amount = $_GET['amount'] ?? '';
$description = $_GET['description'] ?? '';

// Validate and handle empty parameters
$errors = [];

if (empty($paymentType)) {
    $errors[] = 'Payment type is required';
}

if (empty($entityId) || !is_numeric($entityId)) {
    $errors[] = 'Valid entity ID is required';
}

if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
    $errors[] = 'Valid positive amount is required';
}

// Handle empty description based on payment type
if (empty($description)) {
    switch ($paymentType) {
        case 'restaurant_reservation':
            $description = 'Restaurant Reservation - Table Reservation';
            break;
        case 'hotel_booking':
            $description = 'Hotel Booking - Room Reservation';
            break;
        case 'food_order':
            $description = 'Food Order - Meal Purchase';
            break;
        case 'loyalty_reward':
            $description = 'Loyalty Reward - Points Redemption';
            break;
        default:
            $description = 'Payment - Service Purchase';
    }
}

// If there are errors, display them
if (!empty($errors)) {
    $error = implode(', ', $errors);
}



// Get user's payment methods
$paymentMethods = $userModel->getUserPaymentMethods($currentUser['user_id']);

/**
 * Process payment through different gateways
 */
function processPaymentGateway($gateway, $paymentId, $amount, $reference) {
    switch ($gateway) {
        case 'gcash':
            return processGcashPayment($paymentId, $amount, $reference);
        case 'maya':
            return processMayaPayment($paymentId, $amount, $reference);
        case 'credit_card':
            return processCreditCardPayment($paymentId, $amount, $reference);
        case 'cash':
            return processCashPayment($paymentId, $amount, $reference);
        default:
            return ['success' => false, 'message' => 'Unsupported payment gateway'];
    }
}

/**
 * Process GCash payment
 */
function processGcashPayment($paymentId, $amount, $reference) {
    // Simulate GCash API call
    // In production, integrate with actual GCash API
    $transactionId = 'GCASH' . time();
    
    // Simulate processing delay
    sleep(2);
    
    // Simulate success (90% success rate)
    if (rand(1, 10) <= 9) {
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'GCash payment successful'
        ];
    } else {
        return [
            'success' => false,
            'transaction_id' => $transactionId,
            'message' => 'GCash payment failed'
        ];
    }
}

/**
 * Process Maya payment
 */
function processMayaPayment($paymentId, $amount, $reference) {
    // Simulate Maya API call
    $transactionId = 'MAYA' . time();
    sleep(2);
    
    if (rand(1, 10) <= 9) {
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Maya payment successful'
        ];
    } else {
        return [
            'success' => false,
            'transaction_id' => $transactionId,
            'message' => 'Maya payment failed'
        ];
    }
}

/**
 * Process credit card payment
 */
function processCreditCardPayment($paymentId, $amount, $reference) {
    // Simulate credit card processing
    $transactionId = 'CC' . time();
    sleep(3);
    
    if (rand(1, 10) <= 8) {
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Credit card payment successful'
        ];
    } else {
        return [
            'success' => false,
            'transaction_id' => $transactionId,
            'message' => 'Credit card payment declined'
        ];
    }
}

/**
 * Process cash payment
 */
function processCashPayment($paymentId, $amount, $reference) {
    // Cash payments are always successful but require manual confirmation
    $transactionId = 'CASH' . time();
    
    return [
        'success' => true,
        'transaction_id' => $transactionId,
        'message' => 'Cash payment recorded - awaiting confirmation'
    ];
}

/**
 * Update entity status after successful payment
 */
function updateEntityStatus($paymentType, $entityId, $status) {
    global $db;
    
    switch ($paymentType) {
        case 'hotel_booking':
            $sql = "UPDATE hotel_bookings SET booking_status = ? WHERE booking_id = ?";
            break;
        case 'restaurant_reservation':
            $sql = "UPDATE restaurant_reservations SET reservation_status = ? WHERE reservation_id = ?";
            break;
        case 'food_order':
            $sql = "UPDATE food_orders SET order_status = ? WHERE order_id = ?";
            break;
        default:
            return false;
    }
    
    $stmt = $db->prepare($sql);
    return $stmt->execute([$status, $entityId]);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .payment-gateway {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .payment-gateway:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .payment-gateway.selected {
            border-color: #f59e0b;
            background: #fef3c7;
        }
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #f59e0b;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                    <h1 class="text-xl font-bold text-slate-800">Payment Processing</h1>
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
        <!-- Payment Details -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-4">Payment Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Payment Type</p>
                    <p class="font-medium text-slate-800"><?php echo ucfirst(str_replace('_', ' ', $paymentType)); ?></p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Reference ID</p>
                    <p class="font-medium text-slate-800">#<?php echo $entityId; ?></p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Description</p>
                    <p class="font-medium text-slate-800"><?php echo htmlspecialchars($description); ?></p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Amount</p>
                    <p class="text-2xl font-bold text-amber-600"><?php echo formatCurrency($amount); ?></p>
                </div>
            </div>
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

        <!-- Payment Form -->
        <form method="POST" id="paymentForm">
            <input type="hidden" name="action" value="process_payment">
            <input type="hidden" name="payment_type" value="<?php echo htmlspecialchars($paymentType); ?>">
            <input type="hidden" name="entity_id" value="<?php echo htmlspecialchars($entityId); ?>">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">

            <!-- Payment Method Selection -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">Select Payment Method</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Saved Payment Methods -->
                    <?php if (!empty($paymentMethods)): ?>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-slate-700 mb-2">Saved Methods</p>
                            <?php foreach ($paymentMethods as $method): ?>
                                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" name="payment_method" value="<?php echo $method['payment_method_id']; ?>" class="text-amber-600">
                                    <div class="flex-1">
                                        <p class="font-medium text-slate-800"><?php echo htmlspecialchars($method['method_nickname']); ?></p>
                                        <p class="text-sm text-slate-500"><?php echo htmlspecialchars($method['provider_name']); ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- New Payment Method -->
                    <div>
                        <p class="text-sm font-medium text-slate-700 mb-2">New Method</p>
                        <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="payment_method" value="" class="text-amber-600">
                            <div class="flex-1">
                                <p class="font-medium text-slate-800">Add New Payment Method</p>
                                <p class="text-sm text-slate-500">Enter new card or wallet details</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Payment Gateway Selection -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">Choose Payment Gateway</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="payment-gateway border-2 border-slate-200 rounded-xl p-4 text-center" onclick="selectGateway('gcash')">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-wallet text-blue-600 text-xl"></i>
                        </div>
                        <p class="font-medium text-slate-800">GCash</p>
                        <p class="text-xs text-slate-500">Mobile Wallet</p>
                    </div>
                    
                    <div class="payment-gateway border-2 border-slate-200 rounded-xl p-4 text-center" onclick="selectGateway('maya')">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-wallet text-purple-600 text-xl"></i>
                        </div>
                        <p class="font-medium text-slate-800">Maya</p>
                        <p class="text-xs text-slate-500">Mobile Wallet</p>
                    </div>
                    
                    <div class="payment-gateway border-2 border-slate-200 rounded-xl p-4 text-center" onclick="selectGateway('credit_card')">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-credit-card text-green-600 text-xl"></i>
                        </div>
                        <p class="font-medium text-slate-800">Credit Card</p>
                        <p class="text-xs text-slate-500">Visa/Mastercard</p>
                    </div>
                    
                    <div class="payment-gateway border-2 border-slate-200 rounded-xl p-4 text-center" onclick="selectGateway('cash')">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-money-bill-wave text-amber-600 text-xl"></i>
                        </div>
                        <p class="font-medium text-slate-800">Cash</p>
                        <p class="text-xs text-slate-500">Pay on Site</p>
                    </div>
                </div>
                
                <input type="hidden" name="payment_gateway" id="payment_gateway" required>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4">
                <button type="button" onclick="history.back()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3 rounded-xl font-medium transition">
                    Cancel
                </button>
                <button type="submit" id="submitBtn" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-medium transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <span id="btnText">Select Payment Gateway</span>
                </button>
            </div>
        </form>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="fixed inset-0 flex items-center justify-center">
                <div class="bg-white rounded-2xl p-8 text-center">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p class="text-lg font-medium text-slate-800">Processing Payment...</p>
                    <p class="text-sm text-slate-500">Please wait while we process your payment</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        let selectedGateway = null;

        function selectGateway(gateway) {
            // Remove previous selection
            document.querySelectorAll('.payment-gateway').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selection to clicked gateway
            event.currentTarget.classList.add('selected');
            
            // Update hidden input
            document.getElementById('payment_gateway').value = gateway;
            selectedGateway = gateway;
            
            // Enable submit button
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = false;
            document.getElementById('btnText').textContent = `Pay with ${gateway.charAt(0).toUpperCase() + gateway.slice(1).replace('_', ' ')}`;
        }

        // Handle form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedGateway) {
                alert('Please select a payment gateway');
                return;
            }
            
            // Show loading overlay
            document.getElementById('loadingOverlay').classList.remove('hidden');
            
            // Submit form
            this.submit();
        });
    </script>
</body>
</html>
