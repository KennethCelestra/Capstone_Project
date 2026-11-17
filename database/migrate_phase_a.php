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

    // Official programs based on colleges
    $defaultPrograms = [
        // CAS
        ['CAS', 'Bachelor of Arts in English Language', 'BAEL'],
        ['CAS', 'Bachelor of Science in Biology (with concentration in Biotechnology)', 'BS Bio'],
        ['CAS', 'Bachelor of Science in Community Development', 'BSCD'],
        ['CAS', 'Bachelor of Human Services', 'BHS'],
        ['CAS', 'Bachelor of Science in Mathematics', 'BS Math'],
        ['CAS', 'Bachelor of Science in Meteorology', 'BS Met'],
        // CCI
        ['CCI', 'Bachelor of Science in Computer Science', 'BSCS'],
        ['CCI', 'Bachelor of Science in Information Systems', 'BSIS'],
        ['CCI', 'Bachelor of Science in Information Technology', 'BSIT'],
        // COE
        ['COE', 'Bachelor of Elementary Education', 'BEEd'],
        ['COE', 'Bachelor of Secondary Education Major in English', 'BSEd English'],
        ['COE', 'Bachelor of Secondary Education Major in Filipino', 'BSEd Filipino'],
        ['COE', 'Bachelor of Secondary Education Major in Mathematics', 'BSEd Math'],
        ['COE', 'Bachelor of Secondary Education Major in Science', 'BSEd Science'],
        ['COE', 'Bachelor of Secondary Education Major in Social Studies', 'BSEd SocStud'],
        ['COE', 'Bachelor of Technology and Livelihood Education Major in Home Economics', 'BTLEd HE'],
        ['COE', 'Bachelor of Technology and Livelihood Education Major in Industrial Arts', 'BTLEd IA'],
        ['COE', 'Bachelor of Technology and Livelihood Education Major in Information and Communication Technology', 'BTLEd ICT'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Automotive Technology', 'BTVTEd Automotive'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Drafting Technology', 'BTVTEd Drafting'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Electrical Technology', 'BTVTEd Electrical'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Electronics Technology', 'BTVTEd Electronics'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Food and Service Management', 'BTVTEd FSM'],
        ['COE', 'Bachelor of Technical-Vocational Teacher Education Major in Garments, Fashion, and Design', 'BTVTEd GFD'],
        // CEA
        ['CEA', 'Bachelor of Science in Architecture', 'BS Arch'],
        ['CEA', 'Bachelor of Science in Electrical Engineering', 'BSEE'],
        ['CEA', 'Bachelor of Science in Electronics Engineering', 'BSECE'],
        ['CEA', 'Bachelor of Science in Marine Engineering', 'BSMarE'],
        ['CEA', 'Bachelor of Science in Mechanical Engineering', 'BSME'],
        ['CEA', 'Bachelor of Science in Mechanical Engineering Major in Automotive Engineering', 'BSME-AE'],
        // CIT
        ['CIT', 'Bachelor of Industrial Technology Major in Architectural Drafting Technology', 'BIT ADT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Automotive Technology', 'BIT AT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Civil / Construction Technology', 'BIT CT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Cosmetology', 'BIT Cosmetology'],
        ['CIT', 'Bachelor of Industrial Technology Major in Electrical Technology', 'BIT ELT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Electronics Technology', 'BIT ELX'],
        ['CIT', 'Bachelor of Industrial Technology Major in Fashion and Apparel Technology', 'BIT FAT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Food Technology', 'BIT FT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Furniture and Cabinet Making Technology', 'BIT FCT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Heating, Ventilating, Air-Conditioning and Refrigeration Technology', 'BIT HVAC-RT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Mechanical Technology', 'BIT MT'],
        ['CIT', 'Bachelor of Industrial Technology Major in Welding and Fabrication Technology', 'BIT WFT'],
        ['CIT', 'Bachelor of Science in Automotive Technology', 'BSAT'],
        ['CIT', 'Bachelor of Science in Electrical Technology', 'BSELT'],
        ['CIT', 'Bachelor of Science in Electronics Technology', 'BSELX'],
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

