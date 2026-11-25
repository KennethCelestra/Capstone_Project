<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $pdo = Database::getInstance();

    // Helper to check column existence (compatible with native prepares on MySQL, MariaDB, and TiDB)
    $columnExists = function($table, $column) use ($pdo) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    };

    // 1. clearances: status column
    if (!$columnExists('clearances', 'status')) {
        $pdo->exec("
            ALTER TABLE clearances
            ADD COLUMN `status` ENUM('draft','pending_approval','active','archived') NOT NULL DEFAULT 'draft'
            AFTER started_at
        ");
        // Backfill status
        $pdo->exec("UPDATE clearances SET status = 'active' WHERE started_at IS NOT NULL AND archived = 0");
        $pdo->exec("UPDATE clearances SET status = 'archived' WHERE archived = 1");
    }

    // 2. clearance_approvals table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `clearance_approvals` (
            `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `clearance_id` INT UNSIGNED NOT NULL,
            `college`      VARCHAR(50)  NOT NULL DEFAULT 'ALL',
            `approver_id`  INT UNSIGNED NOT NULL,
            `approver_name` VARCHAR(150) NOT NULL,
            `status`       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            `note`         TEXT NULL,
            `decided_at`   DATETIME NULL,
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_capp_clearance_college` (`clearance_id`, `college`),
            CONSTRAINT `fk_capp_clearance` FOREIGN KEY (`clearance_id`) REFERENCES `clearances`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    if (!$columnExists('clearance_approvals', 'college')) {
        $pdo->exec("ALTER TABLE `clearance_approvals` ADD COLUMN `college` VARCHAR(50) NOT NULL DEFAULT 'ALL' AFTER `clearance_id`");
        try {
            $pdo->exec("ALTER TABLE `clearance_approvals` ADD UNIQUE KEY `uq_capp_clearance_college` (`clearance_id`, `college`)");
        } catch (Exception $e) {
            // index may already exist
        }
    }

    // 3. clearance_signatories: is_final column
    if (!$columnExists('clearance_signatories', 'is_final')) {
        $pdo->exec("
            ALTER TABLE clearance_signatories
            ADD COLUMN `is_final` TINYINT(1) NOT NULL DEFAULT 0
        ");
        // Backfill is_final for existing Deans
        $pdo->exec("
            UPDATE clearance_signatories cs
            JOIN signatories sg ON sg.id = cs.signatory_id
            SET cs.is_final = 1
            WHERE LOWER(sg.office) LIKE '%dean%'
        ");
    }

    // 4. signatories: account_status
    if (!$columnExists('signatories', 'account_status')) {
        $pdo->exec("
            ALTER TABLE signatories
            ADD COLUMN `account_status` ENUM('active','inactive','absentee') NOT NULL DEFAULT 'active'
        ");
    }

    // 5. enrollment_committees: account_status
    if (!$columnExists('enrollment_committees', 'account_status')) {
        $pdo->exec("
            ALTER TABLE enrollment_committees
            ADD COLUMN `account_status` ENUM('active','inactive') NOT NULL DEFAULT 'active'
        ");
    }

    // 6. clearance_templates table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `clearance_templates` (
            `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `type`         ENUM('regular','exit') NOT NULL UNIQUE,
            `header_title` VARCHAR(200) DEFAULT 'CLEARANCE FORM',
            `body_text`    TEXT,
            `footer_text`  TEXT,
            `updated_at`   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        INSERT INTO clearance_templates (type, header_title, body_text, footer_text) VALUES
        ('regular','CLEARANCE FORM','This is to certify that Mr./Ms. {student_name} is cleared from any financial and property accountability as of {semester} Semester/Summer, Academic Year {academic_year}.','Distributions: 1 – Student | 2 – Registrar'),
        ('exit','EXIT CLEARANCE FORM','This is to certify that Mr./Ms. {student_name} is cleared from any financial and property accountability as of {semester} Semester/Summer, Academic Year {academic_year}.','Distributions: 1 – Student | 2 – Registrar')
        ON DUPLICATE KEY UPDATE id=id;
    ");

    // 7. clearance_requirements table
    $pdo->exec("
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
    ");

    echo "Phase B migration successfully executed.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
