-- ================================================
-- Clearance System Database Schema (v4 — production)
-- Database: clearance_system
-- Run this in phpMyAdmin: SQL tab (fresh install)
-- For live DB upgrades, see the ALTER statements at the bottom.
-- ================================================

-- NOTE FOR INFINITYFREE / FREE HOSTING USERS:
-- Do NOT run CREATE DATABASE or USE statements on InfinityFree!
-- First select your database (e.g. `if0_38123456_clearance_system`) in phpMyAdmin on the left panel,
-- then paste and execute the table creation statements below.

-- CREATE DATABASE IF NOT EXISTS `clearance_system`
--     CHARACTER SET utf8mb4
--     COLLATE utf8mb4_unicode_ci;
-- USE `clearance_system`;

-- ------------------------------------------------
-- Table: admins
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`  VARCHAR(150) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: enrollment_committees
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `enrollment_committees` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`      VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL UNIQUE,
    `department`     VARCHAR(150) NOT NULL,
    `password`       VARCHAR(255) NOT NULL,
    `temp_password`  VARCHAR(255) DEFAULT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: signatories
-- scope_type: NULL = all students, 'college', 'course'
-- scope_value: e.g. 'CCI', 'BSIT'
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `signatories` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`      VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL UNIQUE,
    `office`         VARCHAR(150) NOT NULL,
    `password`       VARCHAR(255) NOT NULL,
    `temp_password`  VARCHAR(255) DEFAULT NULL,
    `scope_type`     VARCHAR(20)  DEFAULT NULL,
    `scope_value`    VARCHAR(150) DEFAULT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: password_resets
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
    `email`      VARCHAR(150) NOT NULL,
    `token`      VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: students
-- NOTE: Students do NOT log into this system.
--       No password column is needed.
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id`  VARCHAR(50)  NOT NULL UNIQUE,
    `last_name`   VARCHAR(150) NOT NULL,
    `first_name`  VARCHAR(150) NOT NULL,
    `email`       VARCHAR(150) NOT NULL UNIQUE,
    `college`     VARCHAR(50)  NOT NULL,
    `course`      VARCHAR(100) NOT NULL,
    `year_level`  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `section`     VARCHAR(20)  NOT NULL,
    `status`      ENUM('active','graduated','dropped') NOT NULL DEFAULT 'active',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_students_college` (`college`),
    INDEX `idx_students_course`  (`course`),
    INDEX `idx_students_status`  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearances
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearances` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(200) NOT NULL,
    `description` TEXT,
    `school_year` VARCHAR(20)  NOT NULL DEFAULT '',
    `archived`    TINYINT(1)   NOT NULL DEFAULT 0,
    `started_at`  DATETIME     NULL DEFAULT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearance_signatories (pivot)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearance_signatories` (
    `clearance_id`  INT UNSIGNED NOT NULL,
    `signatory_id`  INT UNSIGNED NOT NULL,
    PRIMARY KEY (`clearance_id`, `signatory_id`),
    CONSTRAINT `fk_cs_clearance`
        FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cs_signatory`
        FOREIGN KEY (`signatory_id`) REFERENCES `signatories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearance_enrollment_committees (pivot)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearance_enrollment_committees` (
    `clearance_id`           INT UNSIGNED NOT NULL,
    `enrollment_committee_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`clearance_id`, `enrollment_committee_id`),
    CONSTRAINT `fk_ca_clearance`
        FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ca_enrollment_committee`
        FOREIGN KEY (`enrollment_committee_id`) REFERENCES `enrollment_committees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearance_students (pivot)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearance_students` (
    `clearance_id` INT UNSIGNED NOT NULL,
    `student_id`   INT UNSIGNED NOT NULL,
    PRIMARY KEY (`clearance_id`, `student_id`),
    CONSTRAINT `fk_cst_clearance`
        FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cst_student`
        FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_cst_clearance` (`clearance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearance_status
-- (one row per student-signatory-clearance triple)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearance_status` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `clearance_id` INT UNSIGNED NOT NULL,
    `student_id`   INT UNSIGNED NOT NULL,
    `signatory_id` INT UNSIGNED NOT NULL,
    `status`       ENUM('pending','signed','cleared','flagged') NOT NULL DEFAULT 'pending',
    `flag_note`    TEXT NULL,
    `signed_at`    DATETIME DEFAULT NULL,
    `updated_at`   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_clearance_student_signatory` (`clearance_id`, `student_id`, `signatory_id`),
    INDEX `idx_cs_clearance_signatory` (`clearance_id`, `signatory_id`),
    INDEX `idx_cs_student_clearance`   (`student_id`,   `clearance_id`),
    CONSTRAINT `fk_cstatus_clearance`
        FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cstatus_student`
        FOREIGN KEY (`student_id`)   REFERENCES `students`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_cstatus_signatory`
        FOREIGN KEY (`signatory_id`) REFERENCES `signatories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearance_logs
-- Append-only history of flag / clear actions per student.
-- signatory name/office are snapshots so history survives edits/deletes.
-- (Existing databases: just run this CREATE TABLE once.)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearance_logs` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `clearance_id`   INT UNSIGNED NOT NULL,
    `student_id`     INT UNSIGNED NOT NULL,
    `signatory_id`   INT UNSIGNED NULL,
    `signatory_name` VARCHAR(150) NOT NULL,
    `office`         VARCHAR(150) NOT NULL,
    `action`         ENUM('flagged','cleared') NOT NULL,
    `flag_note`      TEXT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cl_student_clearance` (`clearance_id`, `student_id`, `created_at`),
    CONSTRAINT `fk_cl_clearance`
        FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cl_student`
        FOREIGN KEY (`student_id`)   REFERENCES `students`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_cl_signatory`
        FOREIGN KEY (`signatory_id`) REFERENCES `signatories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Default Admin Account
-- Email: admin@school.edu
-- Password: admin123
-- ------------------------------------------------
INSERT INTO `admins` (`full_name`, `email`, `password`) VALUES
('System Admin', 'admin@school.edu', '$2y$10$KVHKn8fH2ONqumArf3T2CuhqqoMK0H4i3Yv5g2/4aFtFFkFoAPfvS')
ON DUPLICATE KEY UPDATE `id` = `id`;
-- Default credentials: admin@school.edu / admin123
-- To manage admins via CLI, run: C:\xampp2\php\php.exe cli.php admin:list

-- ------------------------------------------------
-- Table: colleges
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `colleges` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(50)  NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `colleges` (`name`) VALUES
('CAS'), ('CCI'), ('CEA'), ('COE'), ('CIT'), ('CGBE')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- ------------------------------------------------
-- Table: programs (linked to colleges)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `programs` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `college_id` INT UNSIGNED NOT NULL,
    `name`       VARCHAR(150) NOT NULL,
    `code`       VARCHAR(30)  NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_prog_code_college` (`code`, `college_id`),
    CONSTRAINT `fk_prog_college` FOREIGN KEY (`college_id`) REFERENCES `colleges`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: offices
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `offices` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(150) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `offices` (`name`) VALUES
('Library'), ('Cashier'), ('Guidance Office'),
('Department Head'), ('Dean'), ('Director of Student Affairs'),
('Registrar'), ('Department Office'), ('Dean Office')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- ------------------------------------------------
-- Phase B Updates
-- ------------------------------------------------
ALTER TABLE clearances
    ADD COLUMN IF NOT EXISTS `status` ENUM('draft','pending_approval','active','archived') NOT NULL DEFAULT 'draft' AFTER `started_at`;

CREATE TABLE IF NOT EXISTS `clearance_approvals` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `clearance_id`  INT UNSIGNED NOT NULL,
    `approver_id`   INT UNSIGNED NOT NULL,
    `approver_name` VARCHAR(150) NOT NULL,
    `status`        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `note`          TEXT NULL,
    `decided_at`    DATETIME NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_capp_clearance` FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE clearance_signatories
    ADD COLUMN IF NOT EXISTS `is_final` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE signatories
    ADD COLUMN IF NOT EXISTS `account_status` ENUM('active','inactive','absentee') NOT NULL DEFAULT 'active';

ALTER TABLE enrollment_committees
    ADD COLUMN IF NOT EXISTS `account_status` ENUM('active','inactive') NOT NULL DEFAULT 'active';

CREATE TABLE IF NOT EXISTS `clearance_templates` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type`         ENUM('regular','exit') NOT NULL UNIQUE,
    `header_title` VARCHAR(200) DEFAULT 'CLEARANCE FORM',
    `body_text`    TEXT,
    `footer_text`  TEXT,
    `updated_at`   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `clearance_templates` (type, header_title, body_text, footer_text) VALUES
('regular','CLEARANCE FORM','This is to certify that Mr./Ms. {student_name} is cleared from any financial and property accountability as of {semester} Semester/Summer, Academic Year {academic_year}.','Distributions: 1 – Student | 2 – Registrar'),
('exit','EXIT CLEARANCE FORM','This is to certify that Mr./Ms. {student_name} is cleared from any financial and property accountability as of {semester} Semester/Summer, Academic Year {academic_year}.','Distributions: 1 – Student | 2 – Registrar')
ON DUPLICATE KEY UPDATE `id` = `id`;

CREATE TABLE IF NOT EXISTS `clearance_requirements` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `clearance_id`     INT UNSIGNED NOT NULL,
    `student_id`       INT UNSIGNED NOT NULL,
    `signatory_id`     INT UNSIGNED NOT NULL,
    `file_name`        VARCHAR(255) NOT NULL,
    `file_path`        VARCHAR(500) NOT NULL,
    `mime_type`        VARCHAR(100) DEFAULT NULL,
    `uploaded_by_role` ENUM('signatory','admin') NOT NULL DEFAULT 'signatory',
    `uploaded_by_id`   INT UNSIGNED NOT NULL,
    `initials`         VARCHAR(20)  DEFAULT NULL,
    `status`           ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    `uploaded_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_creq_clearance` FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_creq_student`   FOREIGN KEY (`student_id`)   REFERENCES `students`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


