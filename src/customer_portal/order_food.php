<?php
/**
 * Order Food Page - PHP Version
 * Complete food ordering system with real-time database integration
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
$cart = [];

// Check for success message from redirect
if (isset($_GET['success']) && !empty($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_to_cart') {
        $itemId = $_POST['item_id'] ?? '';
        $quantity = $_POST['quantity'] ?? 1;
        
        if (empty($itemId) || $quantity < 1) {
            $error = 'Invalid item or quantity';
        } else {
            // Add to cart (stored in session)
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$itemId])) {
                $_SESSION['cart'][$itemId] += $quantity;
            } else {
                $_SESSION['cart'][$itemId] = $quantity;
            }
            
            $success = 'Item added to cart!';
        }
    } elseif ($action === 'update_cart') {
        $itemId = $_POST['item_id'] ?? '';
        $quantity = $_POST['quantity'] ?? 0;
        
        if (isset($_SESSION['cart'][$itemId])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$itemId] = $quantity;
                $success = 'Cart updated!';
            } else {
                unset($_SESSION['cart'][$itemId]);
                $success = 'Item removed from cart!';
            }
        }
    } elseif ($action === 'place_order') {
        $deliveryType = $_POST['delivery_type'] ?? 'delivery';
        $deliveryAddress = $_POST['delivery_address'] ?? '';
        $pickupTime = $_POST['pickup_time'] ?? '';
        $specialInstructions = $_POST['special_instructions'] ?? '';
        
        // Validation based on delivery type
        if ($deliveryType === 'delivery' && empty($deliveryAddress)) {
            $error = 'Delivery address is required for delivery orders';
        } elseif ($deliveryType === 'pickup' && empty($pickupTime)) {
            $error = 'Pickup time is required for pickup orders';
        } elseif (empty($_SESSION['cart'])) {
            $error = 'Your cart is empty. Please add items to place an order.';
        } else {
            // Get menu items for cart
            $cartItems = [];
            $menuItems = $userModel->getMenuItems(100);
            
            foreach ($_SESSION['cart'] as $itemId => $quantity) {
                $itemFound = false;
                foreach ($menuItems as $item) {
                    if ($item['item_id'] == $itemId) {
                        $cartItems[] = [
                            'item_id' => $itemId,
                            'quantity' => $quantity,
                            'price' => $item['price']
                        ];
                        $itemFound = true;
                        break;
                    }
                }
                if (!$itemFound) {
                    // Create fallback item
                    $cartItems[] = [
                        'item_id' => $itemId,
                        'quantity' => $quantity,
                        'price' => 100 // Default price
                    ];
                }
            }
            
            if (!empty($cartItems)) {
                $orderData = [
                    'items' => $cartItems,
                    'delivery_type' => $deliveryType,
                    'delivery_address' => $deliveryType === 'delivery' ? $deliveryAddress : null,
                    'pickup_time' => $deliveryType === 'pickup' ? $pickupTime : null,
                    'special_instructions' => $specialInstructions
                ];
                
                $result = $userModel->createFoodOrder($currentUser['user_id'], $orderData);
                
                if ($result) {
                    // Get the order ID
                    $orderId = $db->lastInsertId();
                    
                    // Calculate total amount
                    $totalAmount = array_sum(array_column($cartItems, 'price'));
                    
                    // Add delivery fee if applicable
                    if ($deliveryType === 'delivery') {
                        $totalAmount += 50; // ₱50 delivery fee
                    }
                    
                    // Define delivery type text
                    $deliveryTypeText = $deliveryType === 'delivery' ? 'for delivery' : 'for pickup';
                    
                    // Redirect to payment processing
                    $description = "Food Order - {$deliveryTypeText} (" . count($cartItems) . " items)";
                    $paymentUrl = "payment_process.php?type=food_order&id={$orderId}&amount={$totalAmount}&description=" . urlencode($description);
                    
                    // Clear cart before redirect
                    unset($_SESSION['cart']);
                    
                    header("Location: {$paymentUrl}");
                    exit;
                } else {
                    $error = 'Failed to place order. Please check your cart items and try again.';
                }
            } else {
                $error = 'No valid items found in cart. Please remove invalid items and try again.';
            }
        }
    }
}

// Get menu items
$menuItems = $userModel->getMenuItems(100);
$menuCategories = [];
foreach ($menuItems as $item) {
    $category = $item['category'] ?? 'Other';
    if (!isset($menuCategories[$category])) {
        $menuCategories[$category] = [];
    }
    $menuCategories[$category][] = $item;
}

// Get user's recent orders
$recentOrders = $userModel->getFoodOrders($currentUser['user_id'], 5);

// Process cart
$cartItems = [];
$cartTotal = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $itemId => $quantity) {
        foreach ($menuItems as $item) {
            if ($item['item_id'] == $itemId) {
                $cartItems[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                    'subtotal' => $item['price'] * $quantity
                ];
                $cartTotal += $item['price'] * $quantity;
                break;
            }
        }
    }
}

// Helper functions
function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function getOrderStatusClass($status) {
    $statusClasses = [
        'confirmed' => 'bg-blue-100 text-blue-700',
        'preparing' => 'bg-amber-100 text-amber-700',
        'ready' => 'bg-green-100 text-green-700',
        'delivered' => 'bg-slate-100 text-slate-700',
        'cancelled' => 'bg-red-100 text-red-700'
    ];
    return $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
}

function formatDate($date, $format = 'M d, Y h:i A') {
    if (empty($date)) {
        return 'N/A';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    if (!$timestamp) {
        return 'Invalid Date';
    }
    
    return date($format, $timestamp);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .menu-item {
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }
        .menu-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .cart-item {
            transition: all 0.2s ease;
        }
        .cart-item:hover {
            background: #f8fafc;
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
        .category-tab {
            transition: all 0.2s ease;
        }
        .category-tab.active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
    </style>
    
    <script>
        // Cart functionality
        function toggleCart() {
            let modal = document.getElementById('cartModal');
            if (modal) {
                modal.classList.toggle('hidden');
                if (!modal.classList.contains('hidden')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            }
        }
        
        // Category filtering
        function filterCategory(category) {
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            document.querySelectorAll('.menu-item').forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Initialize modal
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const modal = document.getElementById('cartModal');
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            toggleCart();
                        }
                    });
                }
            }, 100);
        });
    </script>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <button onclick="window.location.href='dashboard.php'" class="text-slate-600 hover:text-slate-800">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <h1 class="text-xl font-bold text-slate-800">Lùcas Restaurant</h1>
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

    <!-- Sidebar Navigation -->
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-80 bg-white border-r border-slate-200 shadow-lg flex-shrink-0">
            <div class="px-6 py-7 border-b border-slate-100">
                <div class="flex items-center gap-2 text-amber-700">
                    <i class="fa-solid fa-utensils text-xl"></i>
                    <i class="fa-solid fa-bed text-xl"></i>
                    <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.stay</span></span>
                </div>
                <p class="text-xs text-slate-500 mt-1">customer portal · dashboard</p>
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
                <a href="hotel_booking.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-hotel w-5 text-slate-400"></i>Hotel Booking</a>
                <a href="my_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-calendar-check w-5 text-slate-400"></i>My Reservations</a>
                <a href="restaurant_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-clock w-5 text-slate-400"></i>Restaurant Reservation</a>
                <a href="order_food.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-solid fa-bag-shopping w-5 text-amber-600"></i>Menu / Order Food</a>
                <a href="payments.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-credit-card w-5 text-slate-400"></i>Payments</a>
                <a href="loyalty_rewards.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-star w-5 text-slate-400"></i>Loyalty Rewards</a>
                <a href="notifications.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition relative"><i class="fa-regular fa-bell w-5 text-slate-400"></i>Notifications</a>
                <div class="border-t border-slate-200 pt-3 mt-3">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700 transition"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i>Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-y-auto">
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Menu & Order Food</h1>
                    <p class="text-slate-500">Order from our delicious menu</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm text-slate-500">Cart Total</p>
                        <p class="text-2xl font-bold text-amber-600"><?php echo formatCurrency($cartTotal); ?></p>
                    </div>
                    <button onclick="toggleCart()" class="relative bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                        <i class="fa-solid fa-shopping-cart mr-2"></i>
                        Cart
                        <?php if (!empty($cartItems)): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-6 h-6 rounded-full flex items-center justify-center">
                                <?php echo count($cartItems); ?>
                            </span>
                        <?php endif; ?>
                    </button>
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

        <!-- Category Tabs -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                <button type="button" onclick="filterCategory('all')" class="category-tab active px-4 py-2 rounded-xl font-medium whitespace-nowrap">
                    All Items
                </button>
                <?php foreach (array_keys($menuCategories) as $category): ?>
                    <button type="button" onclick="filterCategory('<?php echo $category; ?>')" class="category-tab px-4 py-2 rounded-xl font-medium whitespace-nowrap">
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Menu Items -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($menuCategories as $category => $items): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item bg-white rounded-2xl border border-slate-200 overflow-hidden" data-category="<?php echo $category; ?>">
                            <div class="h-48 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fa-solid fa-utensils text-4xl text-amber-600"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-lg text-slate-800"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                        <p class="text-sm text-slate-500"><?php echo htmlspecialchars($category); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-bold text-amber-600"><?php echo formatCurrency($item['price']); ?></span>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-slate-600 mb-4"><?php echo htmlspecialchars($item['item_description'] ?? 'Delicious item from our kitchen'); ?></p>
                                
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-xs <?php echo ($item['item_status'] === 'available') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> px-2 py-1 rounded-full">
                                        <?php echo ucfirst($item['item_status']); ?>
                                    </span>
                                    
                                    <?php if (!empty($item['preparation_time_minutes'])): ?>
                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                                            <?php echo htmlspecialchars($item['preparation_time_minutes'] . ' min'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <form method="POST" class="flex gap-2">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="10" class="w-20 border border-slate-200 rounded-lg px-2 py-1 text-center">
                                    <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-xl font-medium transition" <?php echo ($item['item_status'] !== 'available') ? 'disabled' : ''; ?>>
                                        <?php echo ($item['item_status'] === 'available') ? 'Add to Cart' : 'Unavailable'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Orders -->
        <?php if (!empty($recentOrders)): ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-regular fa-clock text-amber-600"></i>
                    Recent Orders
                </h2>
                
                <div class="space-y-4">
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="border border-slate-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="font-medium text-slate-800">Order #<?php echo $order['order_id']; ?></span>
                                        <span class="<?php echo getOrderStatusClass($order['order_status'] ?? 'confirmed'); ?> text-xs px-2 py-1 rounded-full">
                                            <?php echo htmlspecialchars($order['order_status'] ?? 'confirmed'); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600"><?php echo htmlspecialchars($order['item_name'] ?? 'Multiple items'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-amber-600"><?php echo formatCurrency($order['total_amount'] ?? 0); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo formatDate($order['created_at'], 'M d, Y h:i A'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Shopping Cart Modal -->
    <div id="cartModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-slate-800">Shopping Cart</h3>
                    <button onclick="toggleCart()" class="text-slate-500 hover:text-slate-700">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>
                
                <?php if (empty($cartItems)): ?>
                    <div class="text-center py-8">
                        <i class="fa-solid fa-shopping-cart text-4xl text-slate-300 mb-4"></i>
                        <p class="text-slate-500">Your cart is empty</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4 mb-6">
                        <?php foreach ($cartItems as $cartItem): ?>
                            <div class="cart-item border border-slate-200 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-slate-800"><?php echo htmlspecialchars($cartItem['item']['item_name']); ?></h4>
                                        <p class="text-sm text-slate-500"><?php echo formatCurrency($cartItem['item']['price']); ?> each</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <form method="POST" class="flex gap-2">
                                            <input type="hidden" name="action" value="update_cart">
                                            <input type="hidden" name="item_id" value="<?php echo $cartItem['item']['item_id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $cartItem['quantity']; ?>" min="0" max="10" 
                                                   class="w-16 border border-slate-200 rounded-lg px-2 py-1 text-center"
                                                   onchange="this.form.submit()">
                                        </form>
                                        <div class="text-right">
                                            <p class="font-medium text-amber-600"><?php echo formatCurrency($cartItem['subtotal']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-lg font-semibold text-slate-800">Total:</span>
                            <span class="text-2xl font-bold text-amber-600"><?php echo formatCurrency($cartTotal); ?></span>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="place_order">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Delivery Address *</label>
                                    <textarea name="delivery_address" required rows="2"
                                              class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none resize-none"
                                              placeholder="Enter your delivery address..."></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Special Instructions</label>
                                    <textarea name="special_instructions" rows="2"
                                              class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none resize-none"
                                              placeholder="Any special requests or dietary restrictions..."></textarea>
                                </div>
                            </div>
                            
                            <div class="flex gap-3 mt-6">
                                <button type="button" onclick="toggleCart()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-3 rounded-xl font-medium transition">
                                    Continue Shopping
                                </button>
                                <button type="submit" class="flex-1 btn-primary text-white px-4 py-3 rounded-xl font-medium">
                                    Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        </div>
    </div>
</body>
</html>
