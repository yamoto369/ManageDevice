-- =====================================================================
-- Device Management System - Full Database Schema
-- This file combines all database scripts and migrations
-- Generated: 2025-12-26
-- =====================================================================

-- =====================================================================
-- PART 1: Base Database Schema (from database.sql)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS device_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE device_manager;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    role ENUM('user', 'mod', 'admin', 'warehouse') NOT NULL DEFAULT 'user',
    status ENUM('pending', 'approved', 'inactive') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Devices table
CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    imei_sn VARCHAR(100) NOT NULL UNIQUE,
    manufacturer VARCHAR(100) DEFAULT NULL,
    status ENUM('available', 'broken') DEFAULT 'available',
    description TEXT DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    current_holder_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (current_holder_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transfer requests table
CREATE TABLE IF NOT EXISTS transfer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    type ENUM('transfer', 'borrow_request') NOT NULL,
    status ENUM('pending', 'confirmed', 'rejected') DEFAULT 'pending',
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transfer history table
CREATE TABLE IF NOT EXISTS transfer_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    from_user_id INT DEFAULT NULL,
    to_user_id INT DEFAULT NULL,
    action_type ENUM('assign', 'transfer', 'return', 'borrow') NOT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User aliases table (private aliases visible only to the user who created them)
CREATE TABLE IF NOT EXISTS user_aliases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    target_user_id INT NOT NULL,
    alias VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_alias (user_id, target_user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- PART 2: Indexes
-- =====================================================================

CREATE INDEX idx_devices_holder ON devices(current_holder_id);
CREATE INDEX idx_devices_status ON devices(status);
CREATE INDEX idx_transfer_requests_status ON transfer_requests(status);
CREATE INDEX idx_transfer_requests_to_user ON transfer_requests(to_user_id);
CREATE INDEX idx_transfer_history_device ON transfer_history(device_id);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);

-- =====================================================================
-- NOTE: For fresh installations, this script creates all tables with
-- the final schema. The migrations below are only needed for existing
-- databases that were created with older versions.
-- =====================================================================

-- =====================================================================
-- MIGRATION 1: Add role-based access control (migration_add_roles.sql)
-- Run ONLY if your existing users table doesn't have role/status columns
-- =====================================================================
/*
ALTER TABLE users 
ADD COLUMN role ENUM('user', 'mod', 'admin') NOT NULL DEFAULT 'user';

ALTER TABLE users 
ADD COLUMN status ENUM('pending', 'approved') NOT NULL DEFAULT 'pending';

-- Set first registered user as admin with approved status
UPDATE users 
SET role = 'admin', status = 'approved' 
WHERE id = (SELECT min_id FROM (SELECT MIN(id) AS min_id FROM users) AS temp);

-- Set all existing users (except first) as approved
UPDATE users 
SET status = 'approved' 
WHERE status = 'pending';

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
*/

-- =====================================================================
-- MIGRATION 2: Add device image column (migration_device_image.sql)
-- Run ONLY if your existing devices table doesn't have image column
-- =====================================================================
/*
ALTER TABLE devices ADD COLUMN image VARCHAR(500) DEFAULT NULL AFTER description;
*/

-- =====================================================================
-- MIGRATION 3: Add warehouse role (migration_warehouse_role.sql)
-- Run ONLY if you need to upgrade from 3-role to 4-role system
-- =====================================================================
/*
-- Migrate existing device statuses
UPDATE devices SET status = 'available' WHERE status IN ('in_use', 'maintenance');

-- Simplify device status ENUM
ALTER TABLE devices MODIFY COLUMN status ENUM('available', 'broken') DEFAULT 'available';

-- Add warehouse role
ALTER TABLE users MODIFY COLUMN role ENUM('user', 'mod', 'admin', 'warehouse') NOT NULL DEFAULT 'user';
*/

-- =====================================================================
-- MIGRATION 4: Add inactive status (migration_inactive_status.sql)
-- Run ONLY if you need to add inactive status for deactivated members
-- =====================================================================
/*
-- Add inactive status to enable separate handling for:
-- - pending: new registrations awaiting approval
-- - inactive: members deactivated by admin
ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'approved', 'inactive') NOT NULL DEFAULT 'pending';
*/
