-- Migration: Add image column to devices table
-- Run this migration to add image support for devices

ALTER TABLE devices ADD COLUMN image VARCHAR(500) DEFAULT NULL AFTER description;
