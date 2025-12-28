-- Migration: Add 'inactive' status for users
-- Run this migration to add support for inactive member status

-- Update status ENUM to include 'inactive'
ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'approved', 'inactive') NOT NULL DEFAULT 'pending';

-- Verify the change
-- SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'status';
