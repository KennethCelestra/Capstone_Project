-- ================================================
-- Migration: Add deficiency/flagging support
-- Run this in phpMyAdmin → SQL tab
-- ================================================

USE `clearance_system`;

-- Step 1: Update the status ENUM to include 'cleared' and 'flagged'
ALTER TABLE `clearance_status`
    MODIFY COLUMN `status` ENUM('pending','cleared','flagged') NOT NULL DEFAULT 'pending';

-- Step 2: Add flag_note column for deficiency reason
ALTER TABLE `clearance_status`
    ADD COLUMN `flag_note` TEXT NULL AFTER `status`;

-- Step 3: Add updated_at timestamp
ALTER TABLE `clearance_status`
    ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

-- Done. All existing rows keep status='pending' by default.
