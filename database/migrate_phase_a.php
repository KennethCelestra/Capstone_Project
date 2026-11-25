<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $pdo = Database::getInstance();

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

    // Official programs based on official ISAT U flyer
    $defaultPrograms = [
        // CAS
        ['CAS', 'Bachelor of Arts in English Language', 'BAEL'],
        ['CAS', 'Bachelor in Human Services', 'BHS'],
        ['CAS', 'Bachelor of Science in Biology (Biotechnology)', 'BS Bio'],
        ['CAS', 'Bachelor of Science in Community Development', 'BSCD'],
        ['CAS', 'Bachelor of Science in Mathematics', 'BS Math'],
        ['CAS', 'Bachelor of Science in Meteorology', 'BS Met'],
        // CCI
        ['CCI', 'Bachelor of Science in Computer Science', 'BSCS'],
        ['CCI', 'Bachelor of Science in Information Systems', 'BSIS'],
        ['CCI', 'Bachelor of Science in Information Technology', 'BSIT'],
        // COE
        ['COE', 'Bachelor of Elementary Education', 'BEEd'],
        ['COE', 'Bachelor of Secondary Education Major in Filipino', 'BSEd Filipino'],
        ['COE', 'Bachelor of Secondary Education Major in Mathematics', 'BSEd Math'],
        ['COE', 'Bachelor of Secondary Education Major in Science', 'BSEd Science'],
        ['COE', 'Bachelor of Technology and Livelihood Education Major in Home Economics', 'BTLEd HE'],
        ['COE', 'Bachelor of Technology and Livelihood Education Major in Industrial Arts', 'BTLEd IA'],
        ['COE', 'Bachelor of Technology and Livelihood Education Major in Information and Communication Technology', 'BTLEd ICT'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Beauty Care and Wellness', 'BTVTEd BCW'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Computer Programming', 'BTVTEd CP'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Food and Service Management', 'BTVTEd FSM'],
        // CEA
        ['CEA', 'Bachelor of Science in Architecture', 'BS Arch'],
        ['CEA', 'Bachelor of Science in Civil Engineering', 'BSCE'],
        ['CEA', 'Bachelor of Science in Electrical Engineering', 'BSEE'],
        ['CEA', 'Bachelor of Science in Electronics Engineering', 'BSECE'],
        ['CEA', 'Bachelor of Science in Mechanical Engineering', 'BSME'],
        ['CEA', 'Bachelor of Science in Agricultural and Biosystems Engineering', 'BSABE'],
        // CIT
        ['CIT', 'Bachelor of Industrial Technology Major in Apparel and Fashion Technology', 'BIT AFT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Architectural Drafting Technology', 'BIT ADT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Automotive Technology', 'BIT AT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Beauty Care and Wellness Technology', 'BIT BCWT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Construction Technology', 'BIT CT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Culinary Technology', 'BIT Culinary'],
        ['CIT', 'Bachelor of Industrial Technology Major in Electrical Technology', 'BIT ELT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Electronics Technology', 'BIT ELX'],
        ['CIT', 'Bachelor of Industrial Technology Major in Heating, Ventilating, Air-Conditioning and Refrigeration Technology', 'BIT HVAC-RT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Mechanical Technology', 'BIT MT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Welding and Fabrication Technology', 'BIT WFT'],
        // CGBE
        ['CGBE', 'Bachelor of Science in Entrepreneurship', 'BSEntrep'],
        ['CGBE', 'Bachelor of Science in Fashion Design and Merchandising', 'BSFDM'],
        ['CGBE', 'Bachelor of Science in Hospitality Management', 'BSHM'],
        ['CGBE', 'Bachelor of Science in Tourism Management', 'BSTM'],
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

