<?php
/**
 * Create Test User
 * Create the test user if it doesn't exist
 */

require_once '../../customer_portal/config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo json_encode([
        'step' => 'Starting user creation process'
    ]);
    
    // Check if user already exists
    $testEmail = 'mia.cruz@email.com';
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo json_encode([
            'step' => 'User already exists',
            'email' => $testEmail,
            'user_id' => $existingUser['user_id']
        ]);
        
        // Update password to ensure it works
        $passwordHash = password_hash('customer123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, account_status = 'active' WHERE email = ?");
        $stmt->execute([$passwordHash, $testEmail]);
        
        echo json_encode([
            'step' => 'Password updated for existing user',
            'password_set' => 'customer123'
        ]);
    } else {
        echo json_encode([
            'step' => 'Creating new test user'
        ]);
        
        // Create the test user
        $passwordHash = password_hash('customer123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password_hash, phone, user_role, account_status, email_verified, phone_verified, membership_tier, loyalty_points, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $result = $stmt->execute([
            'Mia',
            'Cruz',
            $testEmail,
            $passwordHash,
            '+63 917 555 1234',
            'customer',
            'active',
            true,
            true,
            'gold',
            1240
        ]);
        
        if ($result) {
            echo json_encode([
                'step' => 'Test user created successfully',
                'email' => $testEmail,
                'password' => 'customer123',
                'user_role' => 'customer',
                'membership_tier' => 'gold',
                'loyalty_points' => 1240
            ]);
        } else {
            echo json_encode([
                'step' => 'Failed to create test user',
                'error' => 'Database insert failed'
            ]);
        }
    }
    
    // Verify the user was created/updated
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $finalUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'step' => 'Final verification',
        'user_exists' => !!$finalUser,
        'user_data' => $finalUser,
        'login_credentials' => [
            'email' => $testEmail,
            'password' => 'customer123'
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'step' => 'Error in user creation',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
