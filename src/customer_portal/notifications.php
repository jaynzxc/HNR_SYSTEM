<?php
/**
 * Notifications Page - PHP Version
 * Complete notification system with real-time database integration
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
    
    if ($action === 'mark_read') {
        $notificationId = $_POST['notification_id'] ?? '';
        
        if (empty($notificationId)) {
            $error = 'Invalid notification ID';
        } else {
            // Mark notification as read
            $result = $userModel->markNotificationRead($notificationId, $currentUser['user_id']);
            if ($result) {
                $success = 'Notification marked as read';
            } else {
                $error = 'Failed to mark notification as read';
            }
        }
    } elseif ($action === 'mark_all_read') {
        // Mark all notifications as read
        $result = $userModel->markAllNotificationsRead($currentUser['user_id']);
        if ($result) {
            $success = 'All notifications marked as read';
        } else {
            $error = 'Failed to mark notifications as read';
        }
    } elseif ($action === 'delete_notification') {
        $notificationId = $_POST['notification_id'] ?? '';
        
        if (empty($notificationId)) {
            $error = 'Invalid notification ID';
        } else {
            // Delete notification
            $result = $userModel->deleteNotification($notificationId, $currentUser['user_id']);
            if ($result) {
                $success = 'Notification deleted';
            } else {
                $error = 'Failed to delete notification';
            }
        }
    }
}

// Get user's notifications
$allNotifications = $userModel->getUserNotifications($currentUser['user_id'], 50);
$unreadNotifications = array_filter($allNotifications, function($notification) {
    return !$notification['is_read'];
});
$readNotifications = array_filter($allNotifications, function($notification) {
    return $notification['is_read'];
});

// Get notification statistics
$notificationStats = [
    'total' => count($allNotifications),
    'unread' => count($unreadNotifications),
    'read' => count($readNotifications)
];

// Helper functions
function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'Unknown';
    return date($format, strtotime($date));
}

function formatTime($date) {
    return date('h:i A', strtotime($date));
}

function getRelativeTime($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return formatDate($date);
    }
}

function getNotificationIcon($type) {
    $icons = [
        'booking' => 'fa-solid fa-hotel',
        'reservation' => 'fa-solid fa-utensils',
        'payment' => 'fa-regular fa-credit-card',
        'loyalty' => 'fa-regular fa-star',
        'profile' => 'fa-regular fa-user',
        'system' => 'fa-solid fa-cog',
        'promotion' => 'fa-solid fa-tag',
        'reminder' => 'fa-regular fa-clock'
    ];
    return $icons[$type] ?? 'fa-regular fa-bell';
}

function getNotificationColor($type) {
    $colors = [
        'booking' => 'bg-blue-100 text-blue-600',
        'reservation' => 'bg-amber-100 text-amber-600',
        'payment' => 'bg-green-100 text-green-600',
        'loyalty' => 'bg-purple-100 text-purple-600',
        'profile' => 'bg-slate-100 text-slate-600',
        'system' => 'bg-red-100 text-red-600',
        'promotion' => 'bg-pink-100 text-pink-600',
        'reminder' => 'bg-orange-100 text-orange-600'
    ];
    return $colors[$type] ?? 'bg-slate-100 text-slate-600';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification-item {
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .tab-active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        .notification-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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
                <p class="text-xs text-slate-500 mt-1">customer portal · notifications</p>
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
                <a href="payments.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-credit-card w-5 text-slate-400"></i>Payments</a>
                <a href="loyalty_rewards.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-star w-5 text-slate-400"></i>Loyalty Rewards</a>
                <a href="notifications.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium relative"><i class="fa-regular fa-bell w-5 text-amber-600"></i>Notifications<span class="ml-auto bg-amber-600 text-white text-xs px-1.5 py-0.5 rounded-full notification-badge"><?php echo $notificationStats['unread']; ?></span></a>
                <div class="border-t border-slate-200 pt-3 mt-3">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700 transition"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i>Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800 mb-2">notifications</h1>
                            <p class="text-slate-500">stay updated with your latest activities</p>
                        </div>
                        <?php if ($notificationStats['unread'] > 0): ?>
                            <form method="POST" onsubmit="return confirmMarkAllRead()">
                                <input type="hidden" name="action" value="mark_all_read">
                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                                    <i class="fa-regular fa-check-double mr-2"></i>
                                    Mark All Read
                                </button>
                            </form>
                        <?php endif; ?>
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

                <!-- Notification Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="text-3xl font-bold text-slate-800 mb-2"><?php echo $notificationStats['total']; ?></div>
                        <p class="text-slate-500">total notifications</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="text-3xl font-bold text-amber-600 mb-2"><?php echo $notificationStats['unread']; ?></div>
                        <p class="text-slate-500">unread</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="text-3xl font-bold text-slate-600 mb-2"><?php echo $notificationStats['read']; ?></div>
                        <p class="text-slate-500">read</p>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="flex mb-6 bg-slate-100 rounded-xl p-1">
                    <button type="button" onclick="switchTab('unread')" id="unreadTab" class="flex-1 px-4 py-2 rounded-lg font-medium transition tab-active">
                        <i class="fa-regular fa-envelope mr-2"></i>Unread (<?php echo count($unreadNotifications); ?>)
                    </button>
                    <button type="button" onclick="switchTab('all')" id="allTab" class="flex-1 px-4 py-2 rounded-lg font-medium transition">
                        <i class="fa-regular fa-bell mr-2"></i>All Notifications
                    </button>
                </div>

                <!-- Unread Notifications -->
                <div id="unreadNotifications" class="space-y-4">
                    <?php if (empty($unreadNotifications)): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                            <i class="fa-regular fa-bell text-4xl text-slate-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-slate-800 mb-2">No unread notifications</h3>
                            <p class="text-slate-500">You're all caught up!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($unreadNotifications as $notification): ?>
                            <div class="notification-item bg-white rounded-2xl border border-slate-200 p-6 <?php echo !$notification['is_read'] ? 'border-l-4 border-l-amber-500' : ''; ?>">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 <?php echo getNotificationColor($notification['notification_type'] ?? 'system'); ?> rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="<?php echo getNotificationIcon($notification['notification_type'] ?? 'system'); ?>"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <h3 class="font-semibold text-slate-800 mb-1"><?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?></h3>
                                                <p class="text-slate-600 text-sm mb-2"><?php echo htmlspecialchars($notification['message'] ?? ''); ?></p>
                                                <div class="flex items-center gap-4 text-xs text-slate-500">
                                                    <span><?php echo getRelativeTime($notification['created_at']); ?></span>
                                                    <span><?php echo formatDate($notification['created_at']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($notification['action_url'])): ?>
                                            <div class="mt-3">
                                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="inline-flex items-center gap-2 text-amber-600 hover:text-amber-700 text-sm font-medium">
                                                    <i class="fa-regular fa-arrow-right"></i>
                                                    <?php echo htmlspecialchars($notification['action_text'] ?? 'View Details'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1 rounded-lg text-sm font-medium transition">
                                                <i class="fa-regular fa-check mr-1"></i>
                                                Mark Read
                                            </button>
                                        </form>
                                        <form method="POST" class="inline" onsubmit="return confirmDelete()">
                                            <input type="hidden" name="action" value="delete_notification">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-700 px-3 py-1 rounded-lg text-sm font-medium transition">
                                                <i class="fa-regular fa-trash mr-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- All Notifications -->
                <div id="allNotifications" class="space-y-4 hidden">
                    <?php if (empty($allNotifications)): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                            <i class="fa-regular fa-bell text-4xl text-slate-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-slate-800 mb-2">No notifications yet</h3>
                            <p class="text-slate-500">We'll notify you about important updates</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($allNotifications as $notification): ?>
                            <div class="notification-item bg-white rounded-2xl border border-slate-200 p-6 <?php echo !$notification['is_read'] ? 'border-l-4 border-l-amber-500' : ''; ?>">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 <?php echo getNotificationColor($notification['notification_type'] ?? 'system'); ?> rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="<?php echo getNotificationIcon($notification['notification_type'] ?? 'system'); ?>"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <h3 class="font-semibold text-slate-800 mb-1 <?php echo !$notification['is_read'] ? 'font-bold' : ''; ?>">
                                                    <?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?>
                                                </h3>
                                                <p class="text-slate-600 text-sm mb-2"><?php echo htmlspecialchars($notification['message'] ?? ''); ?></p>
                                                <div class="flex items-center gap-4 text-xs text-slate-500">
                                                    <span><?php echo getRelativeTime($notification['created_at']); ?></span>
                                                    <span><?php echo formatDate($notification['created_at']); ?></span>
                                                    <?php if ($notification['is_read']): ?>
                                                        <span class="text-green-600"><i class="fa-regular fa-check-circle mr-1"></i>Read</span>
                                                    <?php else: ?>
                                                        <span class="text-amber-600"><i class="fa-regular fa-envelope mr-1"></i>Unread</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($notification['action_url'])): ?>
                                            <div class="mt-3">
                                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="inline-flex items-center gap-2 text-amber-600 hover:text-amber-700 text-sm font-medium">
                                                    <i class="fa-regular fa-arrow-right"></i>
                                                    <?php echo htmlspecialchars($notification['action_text'] ?? 'View Details'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2">
                                        <?php if (!$notification['is_read']): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1 rounded-lg text-sm font-medium transition">
                                                    <i class="fa-regular fa-check mr-1"></i>
                                                    Mark Read
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" class="inline" onsubmit="return confirmDelete()">
                                            <input type="hidden" name="action" value="delete_notification">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-700 px-3 py-1 rounded-lg text-sm font-medium transition">
                                                <i class="fa-regular fa-trash mr-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab switching
        function switchTab(tab) {
            const unreadTab = document.getElementById('unreadTab');
            const allTab = document.getElementById('allTab');
            const unreadNotifications = document.getElementById('unreadNotifications');
            const allNotifications = document.getElementById('allNotifications');
            
            if (tab === 'unread') {
                unreadTab.classList.add('tab-active');
                allTab.classList.remove('tab-active');
                unreadNotifications.classList.remove('hidden');
                allNotifications.classList.add('hidden');
            } else {
                allTab.classList.add('tab-active');
                unreadTab.classList.remove('tab-active');
                allNotifications.classList.remove('hidden');
                unreadNotifications.classList.add('hidden');
            }
        }
        
        // Confirmation dialogs
        function confirmMarkAllRead() {
            return confirm('Are you sure you want to mark all notifications as read?');
        }
        
        function confirmDelete() {
            return confirm('Are you sure you want to delete this notification? This action cannot be undone.');
        }
        
        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
