-- Create the missing payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    payment_reference VARCHAR(50) UNIQUE NOT NULL,
    payment_type ENUM('hotel_booking', 'restaurant_reservation', 'food_order', 'loyalty_reward') NOT NULL,
    related_entity_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method_id INT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_gateway VARCHAR(50) NULL, -- e.g., 'gcash', 'maya', 'credit_card', 'cash'
    gateway_transaction_id VARCHAR(100) NULL,
    processing_fee DECIMAL(8,2) DEFAULT 0.00,
    discount_amount DECIMAL(8,2) DEFAULT 0.00,
    tax_amount DECIMAL(8,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'PHP',
    due_date TIMESTAMP NULL,
    payment_description TEXT NULL,
    notes TEXT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(payment_method_id),
    INDEX idx_user_id (user_id),
    INDEX idx_payment_type (payment_type),
    INDEX idx_status (status),
    INDEX idx_related_entity (payment_type, related_entity_id),
    INDEX idx_payment_reference (payment_reference),
    INDEX idx_due_date (due_date),
    INDEX idx_created_at (created_at)
);
