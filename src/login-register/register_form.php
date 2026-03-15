<?php
/**
 * Register Form - PHP Version
 * Standalone registration page with beautiful UI
 */

session_start();
require_once '../customer_portal/config/database.php';
require_once '../customer_portal/models/User.php';
require_once '../customer_portal/models/SessionManager.php';

// Check if user is already logged in
$sessionManager = new SessionManager($database);
if ($sessionManager->getCurrentUser()) {
    header('Location: ../customer_portal/index.php');
    exit;
}

// Initialize database and user model
$database = new Database();
$db = $database->getConnection();
$userModel = new User($database);

// Handle form submission
$success = '';
$error = '';

// Check for success/error from session (after redirect)
if (isset($_SESSION['registration_success'])) {
    $success = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}
if (isset($_SESSION['registration_error'])) {
    $error = $_SESSION['registration_error'];
    unset($_SESSION['registration_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $alternativePhone = $_POST['alternative_phone'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $streetAddress = $_POST['street_address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postalCode = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = 'Invalid phone number format';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain uppercase letter and number';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        if ($userModel->getUserByEmail($email)) {
            $error = 'Email already exists';
        } else {
            // Debug: Log the form data
            error_log("Form data received: " . print_r($_POST, true));
            
            // Create user
            $userData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'alternative_phone' => $alternativePhone,
                'date_of_birth' => $dateOfBirth,
                'gender' => $gender,
                'nationality' => $nationality,
                'street_address' => $streetAddress,
                'city' => $city,
                'postal_code' => $postalCode,
                'country' => $country,
                'password' => $password,
                'user_role' => 'customer',
                'membership_tier' => 'Basic',
                'loyalty_points' => 0
            ];
            
            // Debug: Log the user data
            error_log("User data array: " . print_r($userData, true));
            
            $result = $userModel->createUser($userData);
            if ($result) {
                $_SESSION['registration_success'] = 'Registration successful! Please login with your new account.';
                header('Location: register_form.php');
                exit;
            } else {
                $_SESSION['registration_error'] = 'Failed to create account. Please try again.';
                header('Location: register_form.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register · Lùcas Customer Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    @keyframes slideIn {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .toast { animation: slideIn 0.3s ease-out; }
    .fade-in { animation: fadeIn 0.6s ease-out; }
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
<body class="bg-slate-50 font-sans antialiased min-h-screen p-4">
  <!-- Notification Container -->
  <div id="notificationContainer" class="fixed top-4 right-4 z-50 max-w-sm"></div>
  
  <!-- Background Pattern -->
  <div class="fixed inset-0 opacity-10">
    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23f59e0b" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
  </div>

  <!-- Header -->
  <header class="relative z-10 bg-white/80 backdrop-blur-md border-b border-amber-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-utensils text-2xl text-amber-600"></i>
          <i class="fa-solid fa-bed text-2xl text-amber-600"></i>
          <span class="font-bold text-2xl text-amber-700">Lùcas<span class="text-amber-500">.stay</span></span>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm text-slate-600">Premium Hotel & Restaurant</span>
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-phone text-amber-600"></i>
            <span class="text-sm font-medium">+63 (2) 1234 5678</span>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="relative z-10 flex items-center justify-center min-h-[calc(100vh-80px)] p-2">
    <div class="form-container rounded-3xl shadow-2xl w-full max-w-6xl overflow-hidden fade-in">
      <div class="grid md:grid-cols-2">
        <!-- Left Side - Welcome -->
        <div class="bg-gradient-to-br from-amber-600 to-orange-600 p-6 text-white hidden md:block">
          <div class="h-full flex flex-col justify-center">
            <div class="mb-6">
              <h2 class="text-2xl font-bold mb-3">Join Lùcas Today</h2>
              <p class="text-amber-100 text-sm mb-4">Create your account and start enjoying our premium hotel and restaurant services.</p>
            </div>
            
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                  <i class="fa-solid fa-user-plus text-lg"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-sm">Easy Registration</h3>
                  <p class="text-amber-100 text-xs">Sign up in minutes with our simple form</p>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                  <i class="fa-solid fa-gift text-lg"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-sm">Welcome Bonus</h3>
                  <p class="text-amber-100 text-xs">Get exclusive rewards when you join</p>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                  <i class="fa-solid fa-star text-lg"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-sm">Loyalty Program</h3>
                  <p class="text-amber-100 text-xs">Earn points with every booking</p>
                </div>
              </div>
            </div>
            
            <div class="mt-6">
              <div class="flex items-center gap-2 text-amber-100 text-xs">
                <i class="fa-solid fa-shield-check"></i>
                <span>Secure & Trusted Platform</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Registration Form -->
        <div class="p-6 max-h-[calc(100vh-120px)] overflow-y-auto">
          <!-- Registration Form -->
          <form method="POST" class="space-y-3">
            <div>
              <h1 class="text-2xl font-bold text-slate-800 mb-1">Create Account</h1>
              <p class="text-sm text-slate-600">Join us and start your luxury experience.</p>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">First Name</label>
                <div class="relative">
                  <i class="fa-solid fa-user absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="text" name="first_name" required 
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="John">
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Last Name</label>
                <div class="relative">
                  <i class="fa-solid fa-user absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="text" name="last_name" required 
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="Doe">
                </div>
              </div>
            </div>
            
            <div>
              <label class="block text-xs font-medium text-slate-700 mb-1">Email Address</label>
              <div class="relative">
                <i class="fa-regular fa-envelope absolute left-2 top-2 text-slate-400 text-xs"></i>
                <input type="email" name="email" required 
                       class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                       placeholder="your.email@example.com">
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Phone Number</label>
                <div class="relative">
                  <i class="fa-solid fa-phone absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="tel" name="phone" required 
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="+63 912 345 6789">
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Alternative Phone</label>
                <div class="relative">
                  <i class="fa-solid fa-phone absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="tel" name="alternative_phone" 
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="+63 912 345 6789">
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Date of Birth</label>
                <div class="relative">
                  <i class="fa-solid fa-calendar absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="date" name="date_of_birth" required
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none">
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Gender</label>
                <div class="relative">
                  <i class="fa-solid fa-user absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <select name="gender" required 
                          class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none appearance-none">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div>
              <label class="block text-xs font-medium text-slate-700 mb-1">Nationality</label>
              <div class="relative">
                <i class="fa-solid fa-flag absolute left-2 top-2 text-slate-400 text-xs"></i>
                <input type="text" name="nationality" required
                       class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                       placeholder="Filipino">
              </div>
            </div>
            
            <div>
              <label class="block text-xs font-medium text-slate-700 mb-1">Street Address</label>
              <div class="relative">
                <i class="fa-solid fa-home absolute left-2 top-2 text-slate-400 text-xs"></i>
                <input type="text" name="street_address" required
                       class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                       placeholder="123 Main Street">
              </div>
            </div>
            
            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">City</label>
                <div class="relative">
                  <i class="fa-solid fa-city absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="text" name="city" required
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="Manila">
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Postal Code</label>
                <div class="relative">
                  <i class="fa-solid fa-envelope absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="text" name="postal_code" required
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="1000">
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Country</label>
                <div class="relative">
                  <i class="fa-solid fa-globe absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="text" name="country" required
                         class="w-full pl-7 pr-3 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="Philippines">
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Password</label>
                <div class="relative">
                  <i class="fa-solid fa-lock absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="password" id="registerPassword" name="password" required 
                         class="w-full pl-7 pr-8 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="Min. 8 chars, uppercase & number">
                  <button type="button" onclick="togglePassword('registerPassword')" class="absolute right-2 top-2 text-slate-400 hover:text-slate-600">
                    <i class="fa-regular fa-eye text-xs"></i>
                  </button>
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Confirm Password</label>
                <div class="relative">
                  <i class="fa-solid fa-lock absolute left-2 top-2 text-slate-400 text-xs"></i>
                  <input type="password" id="confirmPassword" name="confirm_password" required 
                         class="w-full pl-7 pr-8 py-2 text-sm border border-slate-200 rounded-lg input-focus outline-none"
                         placeholder="Re-enter password">
                  <button type="button" onclick="togglePassword('confirmPassword')" class="absolute right-2 top-2 text-slate-400 hover:text-slate-600">
                    <i class="fa-regular fa-eye text-xs"></i>
                  </button>
                </div>
              </div>
            </div>
            
            <div class="flex items-start">
              <input type="checkbox" id="terms" class="mt-0.5 mr-2 rounded text-xs" required>
              <label for="terms" class="text-xs text-slate-600">
                I agree to the <a href="#" class="text-amber-600 hover:underline">Terms of Service</a> and <a href="#" class="text-amber-600 hover:underline">Privacy Policy</a>
              </label>
            </div>
            
            <button type="submit" class="w-full btn-primary text-white py-2.5 rounded-lg font-medium transition text-sm">
              Create Account
            </button>
          </form>

          <!-- Login Link -->
          <div class="mt-6 text-center">
            <p class="text-sm text-slate-600">
              Already have an account? 
              <a href="login_form.php" class="text-amber-600 hover:underline font-medium">Sign in</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Password toggle
    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const icon = event.target.querySelector('i') || event.target;
      
      if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
      } else {
        field.type = 'password';
        icon.className = 'fa-regular fa-eye';
      }
    }

    // Notification function
    function showNotification(message, type = 'success') {
      const container = document.getElementById('notificationContainer');
      const notification = document.createElement('div');
      
      const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
      const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
      
      notification.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg mb-4 transform transition-all duration-500 translate-x-full`;
      notification.innerHTML = `
        <div class="flex items-center gap-3">
          <i class="fas ${icon} text-xl"></i>
          <div>
            <div class="font-semibold">${type === 'success' ? 'Success!' : 'Error!'}</div>
            <div class="text-sm">${message}</div>
          </div>
        </div>
      `;
      
      container.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
        notification.classList.remove('translate-x-full');
        notification.classList.add('translate-x-0');
      }, 100);
      
      // Remove after 3 seconds
      setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
          container.removeChild(notification);
        }, 500);
      }, 3000);
    }

    // Show notifications on page load if needed
    <?php if ($success): ?>
      showNotification('<?php echo htmlspecialchars($success); ?>', 'success');
      // Redirect to login after 3 seconds
      setTimeout(() => {
        window.location.href = 'login_form.php';
      }, 3000);
    <?php endif; ?>
    
    <?php if ($error): ?>
      showNotification('<?php echo htmlspecialchars($error); ?>', 'error');
    <?php endif; ?>
  </script>
</body>
</html>
