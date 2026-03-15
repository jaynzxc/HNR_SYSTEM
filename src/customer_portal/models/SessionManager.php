<?php
/**
 * Session Management
 * Handles user authentication and session management
 */

class SessionManager {
    private $db;
    private $sessionTimeout = 86400; // 24 hours
    
    public function __construct($database) {
        $this->db = $database;
        // Start PHP session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND account_status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        // Create session
        $sessionId = $this->generateSessionId();
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionTimeout);
        
        $stmt = $this->db->prepare("INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $sessionId,
            $user['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $expiresAt
        ]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['session_id'] = $sessionId;
        $_SESSION['expires_at'] = $expiresAt;
        $_SESSION['user_role'] = $user['user_role'];
        
        // Update last login
        $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")->execute([$user['user_id']]);
        
        return $user;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['session_id'])) {
            // Mark session as inactive
            $stmt = $this->db->prepare("UPDATE user_sessions SET is_active = FALSE WHERE session_id = ?");
            $stmt->execute([$_SESSION['session_id']]);
        }
        
        // Destroy session
        session_destroy();
        return true;
    }
    
    /**
     * Get current logged-in user
     */
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
            return null;
        }
        
        // Check if session is still valid
        $stmt = $this->db->prepare("SELECT * FROM user_sessions WHERE session_id = ? AND is_active = TRUE AND expires_at > NOW()");
        $stmt->execute([$_SESSION['session_id']]);
        $session = $stmt->fetch();
        
        if (!$session) {
            $this->logout();
            return null;
        }
        
        // Get user data directly
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ? AND account_status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->getCurrentUser() !== null;
    }
    
    /**
     * Require login - redirect if not logged in
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    /**
     * Generate secure session ID
     */
    private function generateSessionId() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions() {
        $stmt = $this->db->prepare("UPDATE user_sessions SET is_active = FALSE WHERE expires_at < NOW()");
        return $stmt->execute();
    }
    
    /**
     * Extend session
     */
    public function extendSession() {
        if (!isset($_SESSION['session_id'])) {
            return false;
        }
        
        $newExpiresAt = date('Y-m-d H:i:s', time() + $this->sessionTimeout);
        
        $stmt = $this->db->prepare("UPDATE user_sessions SET expires_at = ?, last_activity = NOW() WHERE session_id = ?");
        $result = $stmt->execute([$newExpiresAt, $_SESSION['session_id']]);
        
        if ($result) {
            $_SESSION['expires_at'] = $newExpiresAt;
        }
        
        return $result;
    }
}
?>
