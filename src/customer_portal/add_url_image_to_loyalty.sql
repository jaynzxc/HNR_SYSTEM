-- Add url_image column to loyalty_rewards table
ALTER TABLE loyalty_rewards ADD COLUMN url_image VARCHAR(255) NULL AFTER reward_description;

-- Update the index to include url_image (optional, for future use)
-- ALTER TABLE loyalty_rewards ADD INDEX idx_url_image (url_image);

-- Verify the table structure
DESCRIBE loyalty_rewards;
