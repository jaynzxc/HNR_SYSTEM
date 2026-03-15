-- Add missing is_active column to loyalty_rewards table
ALTER TABLE loyalty_rewards ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER reward_status;

-- Update the index to include is_active
ALTER TABLE loyalty_rewards ADD INDEX idx_is_active (is_active);

-- Verify the table structure
DESCRIBE loyalty_rewards;
