<?php
/**
 * Simple Test Login Form
 * Minimal version to test basic functionality
 */

session_start();

// Handle login submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== TEST LOGIN FORM SUBMISSION ===");
    error_log("POST data: " . print_r($_POST, true));
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    error_log("Email: $email");
    error_log("Password length: " . strlen($password));
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
        error_log("Validation failed: empty fields");
    } else {
        // Simple test authentication
        if ($email === 'test@example.com' && $password === 'test123') {
            error_log("Test authentication successful");
            
            // Create session
            $_SESSION['user_id'] = 1;
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = 'Test';
            $_SESSION['last_name'] = 'User';
            
            error_log("Session created: " . print_r($_SESSION, true));
            
            // Redirect to dashboard
            error_log("Redirecting to dashboard");
            header('Location: ../customer_portal/index.php');
            exit;
        } else {
            $error = 'Invalid email or password (use test@example.com / test123)';
            error_log("Authentication failed");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test · Lùcas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="flex justify-center items-center gap-2 text-amber-600 mb-2">
                    <i class="fa-solid fa-utensils text-2xl"></i>
                    <i class="fa-solid fa-bed text-2xl"></i>
                    <span class="font-bold text-2xl text-amber-700">Lùcas<span class="text-amber-500">.stay</span></span>
                </div>
                <p class="text-slate-600">Test Login - Use test@example.com / test123</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-exclamation-triangle"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-3 top-3 text-slate-400"></i>
                            <input type="email" name="email" required 
                                   class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
                                   placeholder="test@example.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-3 text-slate-400"></i>
                            <input type="password" name="password" required 
                                   class="w-full pl-10 pr-12 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
                                   placeholder="test123">
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                                <i class="fa-regular fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-lg font-medium transition">
                        Test Login
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-500">
                        Test credentials: test@example.com / test123
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.querySelector('input[name="password"]');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fa-regular fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fa-regular fa-eye';
            }
        }
    </script>
</body>
</html>
