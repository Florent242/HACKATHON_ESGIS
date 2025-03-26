-- Add status field to users table
ALTER TABLE users
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Status of the user account';

-- Update any existing NULL values to 'active'
UPDATE users SET status = 'active' WHERE status IS NULL;

-- Add an index on the status column for faster filtering
ALTER TABLE users
ADD INDEX idx_users_status (status);
