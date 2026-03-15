-- Add missing payment_date column to payments table
ALTER TABLE payments ADD COLUMN payment_date TIMESTAMP NULL AFTER due_date;

-- Update the index to include payment_date
ALTER TABLE payments ADD INDEX idx_payment_date (payment_date);

-- Verify the table structure
DESCRIBE payments;
