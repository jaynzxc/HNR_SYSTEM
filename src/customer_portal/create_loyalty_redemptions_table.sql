-- Create the missing loyalty_redemptions table
CREATE TABLE loyalty_redemptions (
    redemption_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    reward_id INT NOT NULL,
    redemption_reference VARCHAR(20) UNIQUE NOT NULL,
    points_used INT NOT NULL,
    redemption_status ENUM('pending', 'confirmed', 'used', 'expired', 'cancelled') DEFAULT 'pending',
    redemption_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP NULL,
    usage_date TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES loyalty_rewards(reward_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reward_id (reward_id),
    INDEX idx_redemption_status (redemption_status),
    INDEX idx_redemption_date (redemption_date)
);

-- Verify the table structure
DESCRIBE loyalty_redemptions;
