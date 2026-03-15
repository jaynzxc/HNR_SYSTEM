<?php
/**
 * My Profile Page - Compact Dropdown Version
 * Reduced scrolling with collapsible sections
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
    
    if ($action === 'update_profile') {
        // Update profile data
        $profileData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'date_of_birth' => $_POST['dob'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'nationality' => $_POST['nationality'] ?? null,
            'alternative_phone' => $_POST['altPhone'] ?? null,
            'street_address' => $_POST['address'] ?? null,
            'city' => $_POST['city'] ?? null,
            'postal_code' => $_POST['postalCode'] ?? null,
            'country' => $_POST['country'] ?? null,
            'preferred_language' => $_POST['language'] ?? 'English'
        ];
        
        // Validate required fields
        if (empty($profileData['first_name']) || empty($profileData['last_name'])) {
            $error = 'First name and last name are required';
        } elseif (empty($profileData['email'])) {
            $error = 'Email is required';
        } elseif (empty($profileData['phone'])) {
            $error = 'Phone number is required';
        } else {
            // Update profile
            $result = $userModel->updateProfile($currentUser['user_id'], $profileData);
            
            if ($result) {
                $success = 'Profile updated successfully';
                // Reload user data
                $currentUser = $userModel->getUserById($currentUser['user_id']);
            } else {
                $error = 'Failed to update profile';
            }
        }
    } elseif ($action === 'change_password') {
        // Change password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password must be at least 8 characters long';
        } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $error = 'Password must contain uppercase letter and number';
        } else {
            // Verify current password
            if ($userModel->verifyPassword($currentUser['email'], $currentPassword)) {
                // Update password
                $result = $userModel->updatePassword($currentUser['user_id'], $newPassword);
                
                if ($result) {
                    $success = 'Password changed successfully';
                } else {
                    $error = 'Failed to change password';
                }
            } else {
                $error = 'Current password is incorrect';
            }
        }
    }
}

// Helper functions
function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName, 0, 1));
    $lastInitial = strtoupper(substr($lastName, 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return 'Not set';
    }
    return date('F j, Y', strtotime($date));
}

function getPointsToNextTier($points) {
    if ($points < 100) return 100 - $points;
    if ($points < 500) return 500 - $points;
    if ($points < 1000) return 1000 - $points;
    if ($points < 2500) return 2500 - $points;
    return 0; // Platinum tier
}

// Prepare user data for form
$fullName = trim($currentUser['first_name'] . ' ' . $currentUser['last_name']);
$userInitials = getUserInitials($currentUser['first_name'], $currentUser['last_name']);
$pointsToNext = getPointsToNextTier($currentUser['loyalty_points'] ?? 0);
$pointsProgress = min(($currentUser['loyalty_points'] ?? 0) / 5000 * 100, 100);

// Handle date formatting
$dobValue = (!empty($currentUser['date_of_birth']) && $currentUser['date_of_birth'] !== '0000-00-00') 
    ? $currentUser['date_of_birth'] 
    : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile · Lùcas Customer Portal</title>
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
    .input-error { border-color: #ef4444 !important; }
    .dropdown-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
    }
    .dropdown-content.active {
      max-height: 2000px;
      transition: max-height 0.5s ease-in;
    }
    .dropdown-header {
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .dropdown-header:hover {
      background-color: #f9fafb;
    }
    .dropdown-icon {
      transition: transform 0.3s ease;
    }
    .dropdown-icon.active {
      transform: rotate(180deg);
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
        <p class="text-xs text-slate-500 mt-1">customer portal · my profile</p>
      </div>
      <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80">
        <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg"><?php echo $userInitials; ?></div>
        <div>
          <p class="font-medium text-slate-800"><?php echo htmlspecialchars($fullName); ?></p>
          <p class="text-xs text-slate-500 flex items-center gap-1"><i class="fa-regular fa-gem text-[11px]"></i> <span><?php echo htmlspecialchars($currentUser['membership_tier'] ?? 'member'); ?></span> · <span><?php echo number_format($currentUser['loyalty_points'] ?? 0); ?> pts</span></p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-1.5 text-sm overflow-y-auto" style="max-height: calc(100vh - 280px);">
        <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>Dashboard</a>
        <a href="my_profile.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-regular fa-user w-5 text-amber-600"></i>My Profile</a>
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
    <main class="flex-1 overflow-y-auto">
      <!-- Header -->
      <header class="bg-white border-b border-slate-200 px-8 py-3 sticky top-0 z-30">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">my profile</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage your personal information and preferences</p>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <div class="p-4 lg:p-6">
        <div class="max-w-4xl mx-auto space-y-4">
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

          <!-- Profile Summary Card -->
          <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="flex items-center gap-4">
              <div class="h-16 w-16 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xl"><?php echo $userInitials; ?></div>
              <div class="flex-1">
                <h2 class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($fullName); ?></h2>
                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                <div class="flex items-center gap-4 mt-2">
                  <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($currentUser['membership_tier'] ?? 'member'); ?></span>
                  <span class="text-xs text-slate-500"><?php echo number_format($currentUser['loyalty_points'] ?? 0); ?> pts</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Personal Information Dropdown -->
          <div class="bg-white rounded-xl border border-slate-200">
            <div class="dropdown-header p-4 flex items-center justify-between" onclick="toggleDropdown('personal-info')">
              <div class="flex items-center gap-3">
                <i class="fa-regular fa-user text-amber-600"></i>
                <h3 class="font-semibold text-slate-800">Personal Information</h3>
              </div>
              <i class="fa-solid fa-chevron-down text-slate-400 dropdown-icon" id="personal-info-icon"></i>
            </div>
            <div class="dropdown-content" id="personal-info">
              <div class="p-4 border-t border-slate-100">
                <form method="POST" class="space-y-3">
                  <input type="hidden" name="action" value="update_profile">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">First Name</label>
                      <input type="text" name="first_name" value="<?php echo htmlspecialchars($currentUser['first_name'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Last Name</label>
                      <input type="text" name="last_name" value="<?php echo htmlspecialchars($currentUser['last_name'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Email</label>
                      <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Phone</label>
                      <input type="tel" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Date of Birth</label>
                      <input type="date" name="dob" value="<?php echo htmlspecialchars($dobValue); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Gender</label>
                      <select name="gender" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo ($currentUser['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($currentUser['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo ($currentUser['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                      </select>
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Address Information Dropdown -->
          <div class="bg-white rounded-xl border border-slate-200">
            <div class="dropdown-header p-4 flex items-center justify-between" onclick="toggleDropdown('address-info')">
              <div class="flex items-center gap-3">
                <i class="fa-solid fa-location-dot text-amber-600"></i>
                <h3 class="font-semibold text-slate-800">Address Information</h3>
              </div>
              <i class="fa-solid fa-chevron-down text-slate-400 dropdown-icon" id="address-info-icon"></i>
            </div>
            <div class="dropdown-content" id="address-info">
              <div class="p-4 border-t border-slate-100">
                <form method="POST" class="space-y-3">
                  <input type="hidden" name="action" value="update_profile">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                      <label class="block text-xs font-medium text-slate-700 mb-1">Street Address</label>
                      <input type="text" name="address" value="<?php echo htmlspecialchars($currentUser['street_address'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">City</label>
                      <input type="text" name="city" value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Postal Code</label>
                      <input type="text" name="postalCode" value="<?php echo htmlspecialchars($currentUser['postal_code'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-xs font-medium text-slate-700 mb-1">Country</label>
                      <input type="text" name="country" value="<?php echo htmlspecialchars($currentUser['country'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Password Change Dropdown -->
          <div class="bg-white rounded-xl border border-slate-200">
            <div class="dropdown-header p-4 flex items-center justify-between" onclick="toggleDropdown('password-change')">
              <div class="flex items-center gap-3">
                <i class="fa-solid fa-lock text-amber-600"></i>
                <h3 class="font-semibold text-slate-800">Change Password</h3>
              </div>
              <i class="fa-solid fa-chevron-down text-slate-400 dropdown-icon" id="password-change-icon"></i>
            </div>
            <div class="dropdown-content" id="password-change">
              <div class="p-4 border-t border-slate-100">
                <form method="POST" class="space-y-3">
                  <input type="hidden" name="action" value="change_password">
                  <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Current Password</label>
                    <input type="password" name="current_password" required 
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">New Password</label>
                    <input type="password" name="new_password" required 
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>
                  <div class="text-xs text-slate-500">
                    Password must be at least 8 characters with uppercase letter and number
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Change Password</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Preferences Dropdown -->
          <div class="bg-white rounded-xl border border-slate-200">
            <div class="dropdown-header p-4 flex items-center justify-between" onclick="toggleDropdown('preferences')">
              <div class="flex items-center gap-3">
                <i class="fa-solid fa-gear text-amber-600"></i>
                <h3 class="font-semibold text-slate-800">Preferences</h3>
              </div>
              <i class="fa-solid fa-chevron-down text-slate-400 dropdown-icon" id="preferences-icon"></i>
            </div>
            <div class="dropdown-content" id="preferences">
              <div class="p-4 border-t border-slate-100">
                <form method="POST" class="space-y-3">
                  <input type="hidden" name="action" value="update_profile">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Preferred Language</label>
                      <select name="language" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="English" <?php echo ($currentUser['preferred_language'] ?? '') === 'English' ? 'selected' : ''; ?>>English</option>
                        <option value="Spanish" <?php echo ($currentUser['preferred_language'] ?? '') === 'Spanish' ? 'selected' : ''; ?>>Spanish</option>
                        <option value="French" <?php echo ($currentUser['preferred_language'] ?? '') === 'French' ? 'selected' : ''; ?>>French</option>
                        <option value="German" <?php echo ($currentUser['preferred_language'] ?? '') === 'German' ? 'selected' : ''; ?>>German</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Nationality</label>
                      <input type="text" name="nationality" value="<?php echo htmlspecialchars($currentUser['nationality'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-700 mb-1">Alternative Phone</label>
                      <input type="tel" name="altPhone" value="<?php echo htmlspecialchars($currentUser['alternative_phone'] ?? ''); ?>" 
                             class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Save Preferences</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Toggle dropdown functionality
    function toggleDropdown(id) {
      const content = document.getElementById(id);
      const icon = document.getElementById(id + '-icon');
      
      // Close all other dropdowns
      document.querySelectorAll('.dropdown-content').forEach(el => {
        if (el.id !== id) {
          el.classList.remove('active');
          const otherIcon = document.getElementById(el.id + '-icon');
          if (otherIcon) otherIcon.classList.remove('active');
        }
      });
      
      // Toggle current dropdown
      content.classList.toggle('active');
      icon.classList.toggle('active');
    }
    
    // Auto-close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
      if (!event.target.closest('.dropdown-header')) {
        document.querySelectorAll('.dropdown-content').forEach(el => {
          el.classList.remove('active');
          const icon = document.getElementById(el.id + '-icon');
          if (icon) icon.classList.remove('active');
        });
      }
    });
  </script>
</body>
</html>
