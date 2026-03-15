<?php
/**
 * Database User Check
 * Check what users exist in the database
 */

require_once '../../customer_portal/config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo json_encode([
        'step' => 'Database connected successfully',
        'database_info' => [
            'connection' => 'OK',
            'dsn' => 'mysql:host=localhost;dbname=lucas_customer_portal'
        ]
    ]);
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    
    echo json_encode([
        'step' => 'Checking users table',
        'table_exists' => $tableExists
    ]);
    
    if ($tableExists) {
        // Get all users
        $stmt = $db->query("SELECT user_id, email, first_name, last_name, user_role, account_status FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'step' => 'Retrieved users',
            'user_count' => count($users),
            'users' => $users
        ]);
        
        // Check specific user
        $testEmail = 'mia.cruz@email.com';
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$testEmail]);
        $specificUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'step' => 'Checking specific user',
            'email_searched' => $testEmail,
            'user_found' => !!$specificUser,
            'user_data' => $specificUser
        ]);
        
        if ($specificUser) {
            // Test password verification
            $testPassword = 'customer123';
            $passwordCorrect = password_verify($testPassword, $specificUser['password_hash']);
            
            echo json_encode([
                'step' => 'Testing password verification',
                'password_test' => $testPassword,
                'password_correct' => $passwordCorrect,
                'password_hash' => $specificUser['password_hash']
            ]);
        }
    } else {
        echo json_encode([
            'step' => 'Users table does not exist',
            'suggestion' => 'Run the database schema and seed data'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'step' => 'Database connection failed',
        'error' => $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
