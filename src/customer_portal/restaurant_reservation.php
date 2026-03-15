<?php
/**
 * Restaurant Reservation Page - PHP Version
 * Complete restaurant reservation system with database integration
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
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_reservation') {
        $reservationData = [
            'reservation_date' => $_POST['reservation_date'] ?? '',
            'reservation_time' => $_POST['reservation_time'] ?? '',
            'number_of_guests' => $_POST['number_of_guests'] ?? 1,
            'special_requests' => $_POST['special_requests'] ?? ''
        ];
        
        // Validation
        if (empty($reservationData['reservation_date']) || empty($reservationData['reservation_time'])) {
            $error = 'Please select a date and time';
        } elseif ($reservationData['number_of_guests'] < 1 || $reservationData['number_of_guests'] > 8) {
            $error = 'Number of guests must be between 1 and 8';
        } else {
            // Check if reservation date is in the future
            $reservationDate = new DateTime($reservationData['reservation_date']);
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            if ($reservationDate < $today) {
                $error = 'Reservation date must be in the future';
            } else {
                // Create reservation
                $result = $userModel->createRestaurantReservation($currentUser['user_id'], $reservationData);
                if ($result) {
                    // Award loyalty points for reservation
                    $pointsEarned = 25;
                    $sql = "UPDATE users SET loyalty_points = loyalty_points + ? WHERE user_id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$pointsEarned, $currentUser['user_id']]);
                    
                    $success = "Restaurant reservation confirmed! We look forward to serving you. You earned {$pointsEarned} loyalty points!";
                    $error = '';
                } else {
                    $success = '';
                    $error = 'Failed to create reservation. Please try again.';
                }
            }
        }
    }
}

// Get user's existing reservations
$existingReservations = $userModel->getUserReservations($currentUser['user_id'], 5);

// Helper functions
function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

function getAvailableTimeSlots($date) {
    $timeSlots = [
        '11:00:00' => '11:00 AM',
        '11:30:00' => '11:30 AM',
        '12:00:00' => '12:00 PM',
        '12:30:00' => '12:30 PM',
        '13:00:00' => '1:00 PM',
        '13:30:00' => '1:30 PM',
        '14:00:00' => '2:00 PM',
        '18:00:00' => '6:00 PM',
        '18:30:00' => '6:30 PM',
        '19:00:00' => '7:00 PM',
        '19:30:00' => '7:30 PM',
        '20:00:00' => '8:00 PM',
        '20:30:00' => '8:30 PM'
    ];
    
    // In a real implementation, you would check against existing reservations
    // For now, return all time slots
    return $timeSlots;
}

function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Reservation · Lùcas Customer Portal</title>
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
        .reservation-card {
            transition: all 0.3s ease;
        }
        .reservation-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .time-slot {
            transition: all 0.2s ease;
        }
        .time-slot:hover {
            background: #f59e0b;
            color: white;
            transform: scale(1.05);
        }
        .time-slot.selected {
            background: #f59e0b;
            color: white;
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
                <p class="text-xs text-slate-500 mt-1">customer portal · restaurant reservation</p>
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
                <a href="restaurant_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-regular fa-clock w-5 text-amber-600"></i>Restaurant Reservation</a>
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
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">restaurant reservation</h1>
                    <p class="text-slate-500">reserve a table at our fine dining restaurant</p>
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

                <!-- Restaurant Info -->
                <div class="reservation-card bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 p-6 mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-amber-600 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-utensils text-2xl text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Lùcas Fine Dining</h2>
                            <p class="text-slate-600">Experience exquisite cuisine in an elegant atmosphere</p>
                            <div class="flex items-center gap-4 mt-2 text-sm text-slate-500">
                                <span><i class="fa-solid fa-clock"></i> Open 11:00 AM - 10:00 PM</span>
                                <span><i class="fa-solid fa-phone"></i> +63 (2) 1234 5678</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reservation Form -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
                    <h2 class="text-xl font-semibold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fa-regular fa-calendar-plus text-amber-600"></i>
                        make a reservation
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="create_reservation">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">reservation date *</label>
                                <input type="date" name="reservation_date" required 
                                       class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none"
                                       min="<?php echo formatDate('now', 'Y-m-d'); ?>"
                                       onchange="updateTimeSlots()">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">number of guests *</label>
                                <select name="number_of_guests" required class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option value="1">1 guest</option>
                                    <option value="2" selected>2 guests</option>
                                    <option value="3">3 guests</option>
                                    <option value="4">4 guests</option>
                                    <option value="5">5 guests</option>
                                    <option value="6">6 guests</option>
                                    <option value="7">7 guests</option>
                                    <option value="8">8 guests</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">preferred time *</label>
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-3" id="timeSlots">
                                <?php
                                $timeSlots = getAvailableTimeSlots(date('Y-m-d'));
                                foreach ($timeSlots as $time => $label):
                                ?>
                                    <button type="button" 
                                            onclick="selectTimeSlot(this, '<?php echo $time; ?>')"
                                            class="time-slot border border-slate-200 rounded-xl px-3 py-2 text-sm font-medium hover:bg-amber-500 hover:text-white transition">
                                        <?php echo $label; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="reservation_time" id="selectedTime" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">special requests</label>
                            <textarea name="special_requests" rows="3" 
                                      class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none resize-none"
                                      placeholder="Any dietary restrictions, special occasions, or preferences..."></textarea>
                        </div>
                        
                        <div class="flex items-center gap-3 p-4 bg-amber-50 rounded-xl">
                            <i class="fa-solid fa-info-circle text-amber-600"></i>
                            <div class="text-sm">
                                <p class="font-medium text-amber-800">Reservation Policy</p>
                                <p class="text-amber-700">Please arrive on time. Tables will be held for 15 minutes.</p>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-medium">
                            <i class="fa-regular fa-calendar-check mr-2"></i>
                            confirm reservation
                        </button>
                    </form>
                </div>

                <!-- Existing Reservations -->
                <?php if (!empty($existingReservations)): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-regular fa-clock text-amber-600"></i>
                            your upcoming reservations
                        </h2>
                        
                        <div class="space-y-4">
                            <?php foreach ($existingReservations as $reservation): ?>
                                <div class="border border-slate-200 rounded-xl p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="font-medium text-slate-800">
                                                    <?php echo formatDate($reservation['reservation_date'], 'F j, Y'); ?>
                                                </span>
                                                <span class="text-amber-600">
                                                    <?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?>
                                                </span>
                                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                                                    <?php echo htmlspecialchars($reservation['reservation_status']); ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-slate-600">
                                                <?php echo $reservation['number_of_guests']; ?> guests
                                                <?php if (!empty($reservation['special_requests'])): ?>
                                                    • <?php echo htmlspecialchars($reservation['special_requests']); ?>
                                                <?php else: ?>
                                                    • <span class="text-slate-400 italic">No special requests</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <a href="my_reservation.php" class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                                            view details
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-regular fa-clock text-amber-600"></i>
                            your upcoming reservations
                        </h2>
                        
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-regular fa-calendar-xmark text-slate-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-slate-800 mb-2">No upcoming reservations</h3>
                            <p class="text-slate-500 mb-4">You haven't made any restaurant reservations yet.</p>
                            <p class="text-sm text-slate-400">Use the form above to book your table at Lùcas Fine Dining.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Restaurant Features -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-award text-amber-600"></i>
                        </div>
                        <h3 class="font-semibold text-slate-800 mb-2">Award Winning</h3>
                        <p class="text-sm text-slate-600">Recognized for culinary excellence</p>
                    </div>
                    
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-leaf text-amber-600"></i>
                        </div>
                        <h3 class="font-semibold text-slate-800 mb-2">Farm to Table</h3>
                        <p class="text-sm text-slate-600">Fresh, locally sourced ingredients</p>
                    </div>
                    
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-wine-glass text-amber-600"></i>
                        </div>
                        <h3 class="font-semibold text-slate-800 mb-2">Wine Selection</h3>
                        <p class="text-sm text-slate-600">Curated wine pairings available</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedTimeButton = null;
        
        function selectTimeSlot(button, time) {
            // Remove previous selection
            if (selectedTimeButton) {
                selectedTimeButton.classList.remove('selected');
            }
            
            // Add selection to clicked button
            button.classList.add('selected');
            selectedTimeButton = button;
            
            // Update hidden input
            document.getElementById('selectedTime').value = time;
        }
        
        function updateTimeSlots() {
            // In a real implementation, you would fetch available time slots for the selected date
            // For now, we'll just reset the selection
            if (selectedTimeButton) {
                selectedTimeButton.classList.remove('selected');
                selectedTimeButton = null;
            }
            document.getElementById('selectedTime').value = '';
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const selectedTime = document.getElementById('selectedTime').value;
            if (!selectedTime) {
                e.preventDefault();
                alert('Please select a preferred time');
                return false;
            }
        });
        
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.querySelector('input[name="reservation_date"]');
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
        });
    </script>
</body>
</html>
