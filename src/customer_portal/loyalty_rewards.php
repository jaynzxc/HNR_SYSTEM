<?php
/**
 * Loyalty Rewards Page - PHP Version
 * Complete loyalty program with database integration
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
    
    if ($action === 'redeem_reward') {
        $rewardId = $_POST['reward_id'] ?? '';
        $pointsCost = $_POST['points_cost'] ?? 0;
        
        if (empty($rewardId)) {
            $error = 'Invalid reward selected';
        } elseif ($currentUser['loyalty_points'] < $pointsCost) {
            $error = 'Insufficient points for this reward';
        } else {
            // Redeem reward
            $result = $userModel->redeemReward($currentUser['user_id'], $rewardId, $pointsCost);
            if ($result) {
                $success = 'Reward redeemed successfully!';
                // Reload user data
                $currentUser = $userModel->getUserById($currentUser['user_id']);
            } else {
                $error = 'Failed to redeem reward';
            }
        }
    }
}

// Get user's loyalty data
$userPoints = $currentUser['loyalty_points'] ?? 0;
$membershipTier = $currentUser['membership_tier'] ?? 'member';

// Get available rewards
$availableRewards = $userModel->getAvailableRewards();

// Get user's redemption history
$redemptionHistory = $userModel->getUserRedemptions($currentUser['user_id'], 10);

// Get loyalty tier benefits
$tierBenefits = $userModel->getTierBenefits($membershipTier);

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

function getTierColor($tier) {
    $colors = [
        'member' => 'bg-slate-100 text-slate-700',
        'silver' => 'bg-gray-100 text-gray-700',
        'gold' => 'bg-amber-100 text-amber-700',
        'platinum' => 'bg-purple-100 text-purple-700'
    ];
    return $colors[$tier] ?? 'bg-slate-100 text-slate-700';
}

function getTierProgress($points) {
    if ($points >= 5000) return 100;
    if ($points >= 2000) return 80;
    if ($points >= 500) return 40;
    return ($points / 500) * 40;
}

function getPointsToNextTier($points) {
    if ($points >= 5000) return 0;
    if ($points >= 2000) return 5000 - $points;
    if ($points >= 500) return 2000 - $points;
    return 500 - $points;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Rewards · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        .tier-gradient {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .reward-card {
            transition: all 0.3s ease;
        }
        .reward-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .progress-fill {
            background: linear-gradient(90deg, #f59e0b, #d97706);
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
                <p class="text-xs text-slate-500 mt-1">customer portal · loyalty rewards</p>
            </div>
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80">
                <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg">
                    <?php echo getUserInitials($currentUser['first_name'] ?? '', $currentUser['last_name'] ?? ''); ?>
                </div>
                <div>
                    <p class="font-medium text-slate-800"><?php echo htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')); ?></p>
                    <p class="text-xs text-slate-500 flex items-center gap-1"><i class="fa-regular fa-gem text-[11px]"></i> <span><?php echo htmlspecialchars($membershipTier); ?></span> · <span><?php echo number_format($userPoints); ?></span> pts</p>
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
                <a href="loyalty_rewards.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-regular fa-star w-5 text-amber-600"></i>Loyalty Rewards</a>
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
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">loyalty rewards</h1>
                    <p class="text-slate-500">earn points and unlock exclusive benefits</p>
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

                <!-- Loyalty Overview -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Current Points -->
                        <div class="text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-sm font-medium mb-3">
                                <i class="fa-regular fa-gem"></i>
                                <?php echo htmlspecialchars($membershipTier); ?> tier
                            </div>
                            <h2 class="text-4xl font-bold text-amber-600 mb-2"><?php echo number_format($userPoints); ?></h2>
                            <p class="text-slate-500">loyalty points</p>
                            <p class="text-sm text-slate-400 mt-2"><?php echo getPointsToNextTier($userPoints); ?> points to next tier</p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="flex flex-col justify-center">
                            <div class="mb-3">
                                <div class="flex justify-between text-sm text-slate-600 mb-1">
                                    <span>Progress to next tier</span>
                                    <span><?php echo round(getTierProgress($userPoints)); ?>%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-3">
                                    <div class="progress-fill h-3 rounded-full" style="width: <?php echo getTierProgress($userPoints); ?>%"></div>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span>Member</span>
                                    <span>500 pts</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Silver</span>
                                    <span>2,000 pts</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Gold</span>
                                    <span>5,000 pts</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Platinum</span>
                                    <span>∞ pts</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tier Benefits -->
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-3">current tier benefits</h3>
                            <div class="space-y-2">
                                <?php foreach ($tierBenefits as $benefit): ?>
                                    <div class="flex items-center gap-2 text-sm">
                                        <i class="fa-solid fa-check text-green-500"></i>
                                        <span><?php echo htmlspecialchars($benefit); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Rewards -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-800 mb-4">available rewards</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($availableRewards as $reward): ?>
                            <div class="reward-card bg-white rounded-2xl border border-slate-200 overflow-hidden">
                                <div class="h-48 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center relative overflow-hidden">
                                    <?php if (!empty($reward['url_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($reward['url_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($reward['reward_name']); ?>" 
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\"fa-solid fa-gift text-4xl text-amber-600\"></i>';">
                                    <?php else: ?>
                                        <i class="fa-solid fa-gift text-4xl text-amber-600"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="p-5">
                                    <h3 class="font-semibold text-lg text-slate-800 mb-2"><?php echo htmlspecialchars($reward['reward_name']); ?></h3>
                                    <p class="text-sm text-slate-600 mb-4"><?php echo htmlspecialchars($reward['reward_description'] ?? ''); ?></p>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-2xl font-bold text-amber-600"><?php echo number_format($reward['points_cost']); ?></span>
                                        <span class="text-sm text-slate-500">points</span>
                                    </div>
                                    <form method="POST" onsubmit="return confirmRedeem(<?php echo $reward['points_cost']; ?>)">
                                        <input type="hidden" name="action" value="redeem_reward">
                                        <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                        <input type="hidden" name="points_cost" value="<?php echo $reward['points_cost']; ?>">
                                        <button type="submit" 
                                                class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-xl font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
                                                <?php echo ($userPoints < $reward['points_cost']) ? 'disabled' : ''; ?>>
                                            <?php echo ($userPoints < $reward['points_cost']) ? 'Insufficient Points' : 'Redeem Reward'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Redemption History -->
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-4">redemption history</h2>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <?php if (empty($redemptionHistory)): ?>
                            <div class="text-center py-8">
                                <i class="fa-regular fa-history text-4xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500">No redemptions yet</p>
                                <p class="text-sm text-slate-400">Start earning points to redeem rewards!</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-slate-50 border-b border-slate-200">
                                        <tr>
                                            <th class="text-left p-4 font-medium text-slate-700">Reward</th>
                                            <th class="text-left p-4 font-medium text-slate-700">Points Used</th>
                                            <th class="text-left p-4 font-medium text-slate-700">Date</th>
                                            <th class="text-left p-4 font-medium text-slate-700">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($redemptionHistory as $redemption): ?>
                                            <tr class="border-b border-slate-100">
                                                <td class="p-4">
                                                    <div>
                                                        <p class="font-medium text-slate-800"><?php echo htmlspecialchars($redemption['reward_name']); ?></p>
                                                        <p class="text-sm text-slate-500"><?php echo htmlspecialchars($redemption['description'] ?? ''); ?></p>
                                                    </div>
                                                </td>
                                                <td class="p-4">
                                                    <span class="font-medium text-amber-600"><?php echo number_format($redemption['points_used']); ?></span>
                                                </td>
                                                <td class="p-4">
                                                    <span class="text-sm text-slate-600"><?php echo formatDate($redemption['redemption_date']); ?></span>
                                                </td>
                                                <td class="p-4">
                                                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                                                        <?php echo htmlspecialchars($redemption['status'] ?? 'completed'); ?>
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

                <!-- Points Earning Tips -->
                <div class="mt-8 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-regular fa-lightbulb text-amber-600"></i>
                        how to earn more points
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-hotel text-amber-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-sm">Hotel Stays</p>
                                <p class="text-xs text-slate-500">10 points per night</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-utensils text-amber-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-sm">Dining</p>
                                <p class="text-xs text-slate-500">1 point per ₱100</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-star text-amber-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-sm">Reviews</p>
                                <p class="text-xs text-slate-500">50 points per review</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-birthday-cake text-amber-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-sm">Birthday</p>
                                <p class="text-xs text-slate-500">200 bonus points</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function confirmRedeem(pointsCost) {
            const userPoints = <?php echo $userPoints; ?>;
            if (userPoints < pointsCost) {
                alert('You don\'t have enough points for this reward.');
                return false;
            }
            
            return confirm(`Are you sure you want to redeem this reward for ${pointsCost} points?`);
        }
    </script>
</body>
</html>
