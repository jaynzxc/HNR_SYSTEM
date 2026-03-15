<?php
/**
 * Premium Login Form with Toast Notifications
 * Beautiful UI with AJAX login and no page refresh
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

// Handle login submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== LOGIN FORM SUBMISSION ===");
    error_log("POST data: " . print_r($_POST, true));
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    error_log("Email: $email");
    error_log("Password length: " . strlen($password));
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
        error_log("Validation failed: empty fields");
    } else {
        // Check admin credentials FIRST before database
        if ($email === 'admin@lucas.com' && $password === 'admin123') {
            error_log("Admin login detected - redirecting to admin dashboard");
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Admin login successful! Welcome to admin dashboard.',
                'redirect' => 'http://' . $_SERVER['HTTP_HOST'] . '/HNR_SYSTEM/src/admin_portal/dashboard.html',
                'user_type' => 'admin'
            ]);
            exit;
        }
        
        // Regular user authentication
        try {
            error_log("Attempting database connection");
            $database = new Database();
            $userModel = new User($database);
            
            error_log("Attempting user authentication");
            $user = $userModel->authenticateUser($email, $password);
            
            if ($user) {
                error_log("Authentication successful for user ID: " . $user['user_id']);
                
                // Regular user redirection
                error_log("Regular user login detected");
                // Use SessionManager to create proper session
                $sessionManager = new SessionManager($database);
                $sessionManager->login($email, $password);
                
                error_log("Session created with SessionManager: " . print_r($_SESSION, true));
                
                // Return JSON response for AJAX
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful! Welcome back!',
                    'redirect' => 'http://' . $_SERVER['HTTP_HOST'] . '/HNR_SYSTEM/src/customer_portal/index.php',
                    'user_type' => 'customer'
                ]);
                exit;
            } else {
                $error = 'Invalid email or password';
                error_log("Authentication failed");
                
                // Return JSON response for AJAX
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
            error_log("Exception: " . $e->getMessage());
            
            // Return JSON response for AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ]);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Lùcas Customer Portal</title>
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
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .toast { animation: slideIn 0.3s ease-out; }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .input-focus {
            transition: all 0.3s ease;
        }
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-primary:hover::before {
            left: 100%;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-amber-100">
    <!-- Background Pattern -->
    <div class="fixed inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23f59e0b" fill-opacity="0.4"%3E%3Ccircle cx="30" cy="30" r="4"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Main Content -->
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div class="form-container w-full max-w-4xl overflow-hidden rounded-3xl shadow-2xl fade-in">
            <div class="grid md:grid-cols-2">
                <!-- Left Side - Welcome -->
                <div class="bg-gradient-to-br from-amber-600 to-orange-600 p-8 text-white hidden md:block">
                    <div class="h-full flex flex-col justify-center">
                        <div class="mb-8">
                            <h2 class="text-3xl font-bold mb-4">Welcome to Lùcas</h2>
                            <p class="text-amber-100 mb-6">Experience luxury hospitality with our premium hotel and restaurant services.</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-hotel text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Luxury Rooms</h3>
                                    <p class="text-amber-100 text-sm">Premium accommodations with modern amenities</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-utensils text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Fine Dining</h3>
                                    <p class="text-amber-100 text-sm">Exquisite cuisine from renowned chefs</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-concierge-bell text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Premium Service</h3>
                                    <p class="text-amber-100 text-sm">24/7 concierge and personalized care</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-8 border-t border-white/20">
                            <div class="flex items-center gap-2 text-amber-100 text-sm">
                                <i class="fa-solid fa-shield-check"></i>
                                <span>Secure & Trusted Platform</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Login Form -->
                <div class="p-8 bg-white">
                    <!-- Logo -->
                    <div class="flex justify-center items-center gap-2 text-amber-600 mb-6">
                        <i class="fa-solid fa-utensils text-2xl"></i>
                        <i class="fa-solid fa-bed text-2xl"></i>
                        <span class="font-bold text-2xl text-amber-700">Lùcas<span class="text-amber-500">.stay</span></span>
                    </div>
                    
                    <!-- Login Form -->
                    <div id="loginForm" class="space-y-6">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-800 mb-2">Sign In</h1>
                            <p class="text-slate-600">Welcome back! Please login to your account.</p>
                        </div>
                        
                        <form id="loginFormElement" onsubmit="return false;">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                                    <div class="relative">
                                        <i class="fa-regular fa-envelope absolute left-3 top-3 text-slate-400"></i>
                                        <input type="email" id="email" name="email" required 
                                               class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl input-focus outline-none"
                                               placeholder="your.email@example.com">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-lock absolute left-3 top-3 text-slate-400"></i>
                                        <input type="password" id="password" name="password" required 
                                               class="w-full pl-10 pr-12 py-3 border border-slate-200 rounded-xl input-focus outline-none"
                                               placeholder="Enter your password">
                                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="mr-2 rounded">
                                        <span class="text-sm text-slate-600">Remember me</span>
                                    </label>
                                    <a href="#" class="text-sm text-amber-600 hover:underline">Forgot password?</a>
                                </div>
                                
                                <button type="button" id="loginBtn" class="w-full btn-primary text-white py-3 rounded-xl font-medium transition">
                                    Sign In
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Register Link -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-slate-600">
                            Don't have an account? 
                            <a href="register_form.php" class="text-amber-600 hover:underline font-medium">Create account</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toast notification function
        function showToast(message, type = 'info', duration = 3000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-amber-500',
                warning: 'bg-orange-500'
            };
            
            const icons = {
                success: 'fa-circle-check',
                error: 'fa-circle-exclamation',
                info: 'fa-bell',
                warning: 'fa-triangle-exclamation'
            };
            
            toast.className = `toast ${colors[type]} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3`;
            toast.innerHTML = `
                <i class="fas ${icons[type]}"></i>
                <span class="text-sm font-medium">${message}</span>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fa-regular fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fa-regular fa-eye';
            }
        }

        // Handle login form submission
        async function handleLogin() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            
            if (!email || !password) {
                showToast('Please fill in all fields', 'error');
                return;
            }
            
            // Show loading state
            const originalText = loginBtn.innerHTML;
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
            
            try {
                const response = await fetch('login_form.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showToast(data.message, 'error');
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Login error:', error);
                showToast('An error occurred. Please try again.', 'error');
                loginBtn.disabled = false;
                loginBtn.innerHTML = originalText;
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add form submit listener
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.addEventListener('click', handleLogin);
            
            // Handle Enter key
            document.getElementById('email').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('password').focus();
                }
            });
            
            document.getElementById('password').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleLogin();
                }
            });
        });
    </script>
</body>
</html>
