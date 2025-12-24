-- Migration: Add role-based access control to users table
-- Run this script to add role and status columns
-- Date: 2024-12-24

-- Add role column (user, mod, admin)
ALTER TABLE users 
ADD COLUMN role ENUM('user', 'mod', 'admin') NOT NULL DEFAULT 'user';

-- Add status column (pending, approved)
ALTER TABLE users 
ADD COLUMN status ENUM('pending', 'approved') NOT NULL DEFAULT 'pending';

-- Set first registered user as admin with approved status
UPDATE users 
SET role = 'admin', status = 'approved' 
WHERE id = (SELECT min_id FROM (SELECT MIN(id) AS min_id FROM users) AS temp);

-- Set all existing users (except first) as approved to avoid locking out current users
UPDATE users 
SET status = 'approved' 
WHERE status = 'pending';

-- Create index for faster role/status lookups
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
