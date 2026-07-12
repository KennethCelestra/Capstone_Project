-- ================================================
-- Clearance System Database Schema (v3 — current)
-- Database: clearance_system
-- Run this in phpMyAdmin: SQL tab
-- ================================================

CREATE DATABASE IF NOT EXISTS `clearance_system`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `clearance_system`;

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
-- Table: advisers
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `advisers` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`      VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL UNIQUE,
    `department`     VARCHAR(150) NOT NULL,
    `password`       VARCHAR(255) NOT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: signatories
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `signatories` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`      VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL UNIQUE,
    `office`         VARCHAR(150) NOT NULL,
    `password`       VARCHAR(255) NOT NULL,
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
    `password`    VARCHAR(255) NOT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearances
-- (flexible, admin-defined clearance entities)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearances` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(200) NOT NULL,
    `description` TEXT,
    `school_year` VARCHAR(20)  NOT NULL DEFAULT '',
    `archived`    TINYINT(1)   NOT NULL DEFAULT 0,
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
-- Table: clearance_advisers (pivot)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearance_advisers` (
    `clearance_id` INT UNSIGNED NOT NULL,
    `adviser_id`   INT UNSIGNED NOT NULL,
    PRIMARY KEY (`clearance_id`, `adviser_id`),
    CONSTRAINT `fk_ca_clearance`
        FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ca_adviser`
        FOREIGN KEY (`adviser_id`) REFERENCES `advisers`(`id`) ON DELETE CASCADE
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
        FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
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
    CONSTRAINT `fk_cstatus_clearance`
        FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cstatus_student`
        FOREIGN KEY (`student_id`)   REFERENCES `students`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_cstatus_signatory`
        FOREIGN KEY (`signatory_id`) REFERENCES `signatories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Default Admin Account
-- Email: admin@school.edu
-- Password: admin123
-- ------------------------------------------------
INSERT INTO `admins` (`full_name`, `email`, `password`) VALUES
('System Admin', 'admin@school.edu', '$2y$10$KVHKn8fH2ONqumArf3T2CuhqqoMK0H4i3Yv5g2/4aFtFFkFoAPfvS');
-- Default credentials: admin@school.edu / admin123
-- To change password, run: c:\xampp2\php\php.exe -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
