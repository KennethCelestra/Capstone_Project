-- ================================================
-- Migration: Add archived column to clearances
--            Fix clearance_status enum to include 'cleared' and 'flagged'
-- Run this in phpMyAdmin: SQL tab
-- ================================================

-- 1. Add archived column to clearances table (safe to run multiple times)
ALTER TABLE `clearances`
    ADD COLUMN IF NOT EXISTS `archived` TINYINT(1) NOT NULL DEFAULT 0;

-- 2. Expand the status enum to support all current statuses
ALTER TABLE `clearance_status`
    MODIFY COLUMN `status` ENUM('pending','signed','cleared','flagged') NOT NULL DEFAULT 'pending';

-- 3. Also add flag_note and updated_at columns if they don't exist yet
ALTER TABLE `clearance_status`
    ADD COLUMN IF NOT EXISTS `flag_note`   TEXT NULL,
    ADD COLUMN IF NOT EXISTS `updated_at`  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- 4. Migrate any old 'signed' rows to 'cleared'
UPDATE `clearance_status` SET `status` = 'cleared' WHERE `status` = 'signed';
