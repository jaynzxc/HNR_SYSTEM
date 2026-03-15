<?php
/**
 * Reviews Page - PHP Version
 * Complete review system with real-time database integration
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
    
    if ($action === 'submit_review') {
        $reviewData = [
            'review_type' => $_POST['review_type'] ?? '',
            'rating' => $_POST['rating'] ?? 0,
            'review_title' => $_POST['review_title'] ?? '',
            'review_text' => $_POST['review_text'] ?? ''
        ];
        
        // Validation
        if (empty($reviewData['review_type']) || $reviewData['rating'] < 1 || $reviewData['rating'] > 5) {
            $error = 'Please select a review type and rating';
        } elseif (empty($reviewData['review_text'])) {
            $error = 'Please provide a review comment';
        } else {
            // Submit review
            $result = $userModel->addReview($currentUser['user_id'], $reviewData);
            if ($result) {
                $success = 'Review submitted successfully! Thank you for your feedback.';
                
                // Award loyalty points for review
                $pointsEarned = 50;
                $sql = "UPDATE users SET loyalty_points = loyalty_points + ? WHERE user_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$pointsEarned, $currentUser['user_id']]);
                
                $success .= " You earned {$pointsEarned} loyalty points!";
            } else {
                $error = 'Failed to submit review. Please try again.';
            }
        }
    }
}

// Get user's reviews
$userReviews = $userModel->getUserReviews($currentUser['user_id'], 20);

// Get all reviews for display
$allReviews = $userModel->getAllReviews(50);

// Get review statistics
$reviewStats = [
    'total' => count($allReviews),
    'average_rating' => 0,
    'by_type' => []
];

if (!empty($allReviews)) {
    $totalRating = array_sum(array_column($allReviews, 'rating'));
    $reviewStats['average_rating'] = round($totalRating / count($allReviews), 1);
    
    // Group by type
    foreach ($allReviews as $review) {
        $type = $review['review_type'] ?? 'other';
        if (!isset($reviewStats['by_type'][$type])) {
            $reviewStats['by_type'][$type] = ['count' => 0, 'total_rating' => 0];
        }
        $reviewStats['by_type'][$type]['count']++;
        $reviewStats['by_type'][$type]['total_rating'] += $review['rating'];
    }
}

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

function renderStars($rating, $interactive = false) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fa-solid fa-star text-yellow-400' . ($interactive ? ' cursor-pointer hover:scale-110 transition' : '') . '"></i>';
        } else {
            $stars .= '<i class="fa-regular fa-star text-yellow-400' . ($interactive ? ' cursor-pointer hover:scale-110 transition' : '') . '"></i>';
        }
    }
    return $stars;
}

function getReviewTypeLabel($type) {
    $labels = [
        'hotel_stay' => 'Hotel Stay',
        'restaurant_dining' => 'Restaurant Dining',
        'room_service' => 'Room Service',
        'facilities' => 'Facilities',
        'staff_service' => 'Staff Service',
        'overall_experience' => 'Overall Experience'
    ];
    return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        .review-card {
            transition: all 0.3s ease;
        }
        .review-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .star-rating {
            transition: all 0.2s ease;
        }
        .star-rating i {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .star-rating i:hover {
            transform: scale(1.2);
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
                <p class="text-xs text-slate-500 mt-1">customer portal · reviews</p>
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
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">reviews & feedback</h1>
                    <p class="text-slate-500">share your experience and help us improve</p>
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

                <!-- Review Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="text-3xl font-bold text-amber-600 mb-2"><?php echo $reviewStats['average_rating']; ?></div>
                        <div class="mb-2"><?php echo renderStars(round($reviewStats['average_rating'])); ?></div>
                        <p class="text-slate-500">average rating</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="text-3xl font-bold text-slate-800 mb-2"><?php echo $reviewStats['total']; ?></div>
                        <p class="text-slate-500">total reviews</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="text-3xl font-bold text-green-600 mb-2"><?php echo count($userReviews); ?></div>
                        <p class="text-slate-500">your reviews</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="text-3xl font-bold text-purple-600 mb-2">50</div>
                        <p class="text-slate-500">points per review</p>
                    </div>
                </div>

                <!-- Submit Review Form -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
                    <h2 class="text-xl font-semibold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fa-regular fa-star text-amber-600"></i>
                        submit a review
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="submit_review">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">review type *</label>
                                <select name="review_type" required class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option value="">Select review type</option>
                                    <option value="hotel_stay">Hotel Stay</option>
                                    <option value="restaurant_dining">Restaurant Dining</option>
                                    <option value="room_service">Room Service</option>
                                    <option value="facilities">Facilities</option>
                                    <option value="staff_service">Staff Service</option>
                                    <option value="overall_experience">Overall Experience</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">rating *</label>
                                <div class="star-rating flex gap-2 text-2xl" id="starRating">
                                    <i class="fa-regular fa-star text-yellow-400" data-rating="1"></i>
                                    <i class="fa-regular fa-star text-yellow-400" data-rating="2"></i>
                                    <i class="fa-regular fa-star text-yellow-400" data-rating="3"></i>
                                    <i class="fa-regular fa-star text-yellow-400" data-rating="4"></i>
                                    <i class="fa-regular fa-star text-yellow-400" data-rating="5"></i>
                                </div>
                                <input type="hidden" name="rating" id="ratingValue" value="0" required>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">review title</label>
                            <input type="text" name="review_title" 
                                   class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none"
                                   placeholder="Brief summary of your experience">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">your review *</label>
                            <textarea name="review_text" rows="4" required
                                      class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none resize-none"
                                      placeholder="Share your detailed experience with us..."></textarea>
                        </div>
                        
                        <div class="flex items-center gap-3 p-4 bg-amber-50 rounded-xl">
                            <i class="fa-solid fa-info-circle text-amber-600"></i>
                            <div class="text-sm">
                                <p class="font-medium text-amber-800">Earn 50 Loyalty Points!</p>
                                <p class="text-amber-700">Submit a genuine review and earn points towards your next reward.</p>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-medium">
                            <i class="fa-regular fa-star mr-2"></i>
                            submit review
                        </button>
                    </form>
                </div>

                <!-- Your Reviews -->
                <?php if (!empty($userReviews)): ?>
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-regular fa-user text-amber-600"></i>
                            your reviews
                        </h2>
                        
                        <div class="space-y-4">
                            <?php foreach ($userReviews as $review): ?>
                                <div class="review-card bg-white rounded-2xl border border-slate-200 p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full">
                                                    <?php echo getReviewTypeLabel($review['review_type']); ?>
                                                </span>
                                                <div class="flex gap-1">
                                                    <?php echo renderStars($review['rating']); ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($review['review_title'])): ?>
                                                <h3 class="font-semibold text-slate-800 mb-1"><?php echo htmlspecialchars($review['review_title']); ?></h3>
                                            <?php endif; ?>
                                            <p class="text-slate-600"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-slate-500"><?php echo formatDate($review['created_at']); ?></p>
                                            <p class="text-xs text-slate-400"><?php echo getRelativeTime($review['created_at']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- All Reviews -->
                <div>
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-regular fa-comments text-amber-600"></i>
                        recent reviews
                    </h2>
                    
                    <?php if (empty($allReviews)): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                            <i class="fa-regular fa-star text-4xl text-slate-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-slate-800 mb-2">No reviews yet</h3>
                            <p class="text-slate-500">Be the first to share your experience!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($allReviews as $review): ?>
                                <div class="review-card bg-white rounded-2xl border border-slate-200 p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="w-8 h-8 bg-amber-200 rounded-full flex items-center justify-center">
                                                    <span class="text-amber-800 font-bold text-sm">
                                                        <?php echo strtoupper(substr($review['first_name'] ?? 'A', 0, 1)) . strtoupper(substr($review['last_name'] ?? 'U', 0, 1)); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-slate-800">
                                                        <?php echo htmlspecialchars(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? '')); ?>
                                                    </p>
                                                    <div class="flex items-center gap-2">
                                                        <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full">
                                                            <?php echo getReviewTypeLabel($review['review_type']); ?>
                                                        </span>
                                                        <div class="flex gap-1">
                                                            <?php echo renderStars($review['rating']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if (!empty($review['review_title'])): ?>
                                                <h3 class="font-semibold text-slate-800 mb-1"><?php echo htmlspecialchars($review['review_title']); ?></h3>
                                            <?php endif; ?>
                                            <p class="text-slate-600"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-slate-500"><?php echo formatDate($review['created_at']); ?></p>
                                            <p class="text-xs text-slate-400"><?php echo getRelativeTime($review['created_at']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedRating = 0;
        
        // Star rating functionality
        document.querySelectorAll('#starRating i').forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.rating);
                document.getElementById('ratingValue').value = selectedRating;
                updateStars(selectedRating);
            });
            
            star.addEventListener('mouseenter', function() {
                const hoverRating = parseInt(this.dataset.rating);
                updateStars(hoverRating);
            });
        });
        
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            updateStars(selectedRating);
        });
        
        function updateStars(rating) {
            document.querySelectorAll('#starRating i').forEach((star, index) => {
                if (index < rating) {
                    star.className = 'fa-solid fa-star text-yellow-400 cursor-pointer hover:scale-110 transition';
                } else {
                    star.className = 'fa-regular fa-star text-yellow-400 cursor-pointer hover:scale-110 transition';
                }
            });
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const rating = document.getElementById('ratingValue').value;
            if (rating === '0') {
                e.preventDefault();
                alert('Please select a rating');
                return false;
            }
        });
    </script>
</body>
</html>
