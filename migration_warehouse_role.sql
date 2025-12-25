-- Migration: Add warehouse role and simplify device status
-- Run this script to update the database schema
-- Date: 2025-12-25

-- Step 1: Migrate existing device statuses before changing ENUM
-- Change 'in_use' and 'maintenance' to 'available'
UPDATE devices SET status = 'available' WHERE status IN ('in_use', 'maintenance');

-- Step 2: Alter the device status ENUM to only have 2 values
ALTER TABLE devices MODIFY COLUMN status ENUM('available', 'broken') DEFAULT 'available';

-- Step 3: Add 'warehouse' to the role ENUM
ALTER TABLE users MODIFY COLUMN role ENUM('user', 'mod', 'admin', 'warehouse') NOT NULL DEFAULT 'user';

-- Note: After running this migration:
-- 1. Assign the 'warehouse' role to at least one user via the Members page
-- 2. New devices will be automatically assigned to warehouse users
