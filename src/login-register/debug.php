<?php
/**
 * Debug Login Form
 * Ultra-simple version to test basic functionality
 */

// Start session
session_start();

// Debug: Show all request info
error_log("=== DEBUG LOGIN PAGE ===");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));
error_log("Session data: " . print_r($_SESSION, true));

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== POST REQUEST DETECTED ===");
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    error_log("Email received: '$email'");
    error_log("Password received: '$password'");
    
    // Create test session regardless of credentials
    $_SESSION['test_login'] = true;
    $_SESSION['test_email'] = $email;
    $_SESSION['test_time'] = date('Y-m-d H:i:s');
    
    error_log("Test session created: " . print_r($_SESSION, true));
    
    // Show success message instead of redirect
    $success = "Login test successful! Session created.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-800 mb-6">Debug Login Test</h1>
        
        <!-- Debug Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Debug Information</h2>
            <div class="space-y-2 text-sm">
                <p><strong>Request Method:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
                <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
                <p><strong>Session Data:</strong> <pre><?php print_r($_SESSION); ?></pre></p>
                <p><strong>POST Data:</strong> <pre><?php print_r($_POST); ?></pre></p>
            </div>
        </div>
        
        <!-- Success Message -->
        <?php if (isset($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <strong>Success:</strong> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Test Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Test Form</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg"
                           placeholder="Enter any email">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg"
                           placeholder="Enter any password">
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Submit Test
                </button>
            </form>
        </div>
        
        <!-- Manual Links -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Manual Tests</h2>
            <div class="space-y-2">
                <a href="?test=1" class="block text-blue-600 hover:underline">Test GET Request</a>
                <a href="login_test.php" class="block text-blue-600 hover:underline">Go to Login Test</a>
                <a href="../customer_portal/index.php" class="block text-blue-600 hover:underline">Go to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
