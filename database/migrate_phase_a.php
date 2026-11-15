<?php
require_once __DIR__ . '/../config/app.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. Colleges
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `colleges` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name`       VARCHAR(50)  NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $colleges = ['CAS', 'CCI', 'CEA', 'COE', 'CIT', 'CGBE'];
    $stmt = $pdo->prepare("INSERT INTO `colleges` (`name`) VALUES (?) ON DUPLICATE KEY UPDATE `id` = `id`");
    foreach ($colleges as $c) {
        $stmt->execute([$c]);
    }

    // 2. Programs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `programs` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `college_id` INT UNSIGNED NOT NULL,
            `name`       VARCHAR(150) NOT NULL,
            `code`       VARCHAR(30)  NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_prog_code_college` (`code`, `college_id`),
            CONSTRAINT `fk_prog_college` FOREIGN KEY (`college_id`) REFERENCES `colleges`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Fetch college IDs
    $colMap = [];
    $rows = $pdo->query("SELECT id, name FROM colleges")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $colMap[$r['name']] = $r['id'];
    }

    // Default programs based on colleges & existing students
    $defaultPrograms = [
        ['CCI', 'Bachelor of Science in Information Technology', 'BSIT'],
        ['CCI', 'Bachelor of Science in Computer Science', 'BSCS'],
        ['CCI', 'Bachelor of Science in Information Systems', 'BSIS'],
        ['COE', 'Bachelor of Science in Mechanical Engineering', 'BSME'],
        ['COE', 'Bachelor of Science in Electronics and Communications Engineering', 'BSECE'],
        ['CIT', 'Bachelor of Science in Automotive Mechanical Technology', 'BSAMT'],
        ['CIT', 'Bachelor of Science in Food Technology', 'BSFT'],
        ['CAS', 'Bachelor of Science in Information Technology', 'BSIT'],
        ['CAS', 'Bachelor of Science in Computer Science', 'BSCS'],
        ['CAS', 'Bachelor of Science in Applied Mathematics', 'BSAM'],
        ['CEA', 'Bachelor of Science in Electrical Engineering', 'BSEE'],
        ['CEA', 'Bachelor of Science in Civil Engineering', 'BSCE'],
        ['CEA', 'Bachelor of Science in Architecture', 'BSArch'],
        ['CGBE', 'Bachelor of Science in Business Administration', 'BSBA'],
    ];

    $progStmt = $pdo->prepare("
        INSERT INTO `programs` (`college_id`, `name`, `code`)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)
    ");
    foreach ($defaultPrograms as [$col, $pName, $pCode]) {
        if (isset($colMap[$col])) {
            $progStmt->execute([$colMap[$col], $pName, $pCode]);
        }
    }

    // 3. Offices
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `offices` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name`       VARCHAR(150) NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $defaultOffices = [
        'Library', 'Cashier', 'Guidance Office', 'Department Head',
        'Dean', 'Director of Student Affairs', 'Registrar',
        'Department Office', 'Dean Office'
    ];
    $offStmt = $pdo->prepare("INSERT INTO `offices` (`name`) VALUES (?) ON DUPLICATE KEY UPDATE `id` = `id`");
    foreach ($defaultOffices as $off) {
        $offStmt->execute([$off]);
    }

    // Ensure unique keys on signatories for email
    try {
        $pdo->exec("ALTER TABLE `signatories` ADD UNIQUE KEY `uq_signatory_email` (`email`)");
    } catch (Exception $e) {
        // key might already exist
    }

    echo "Phase A migration successfully executed.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

