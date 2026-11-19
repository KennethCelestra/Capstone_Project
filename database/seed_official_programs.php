<?php
/**
 * Migration / Seed script:
 * Populates official ISAT U Colleges and Academic Programs exactly as listed on the
 * official ISAT U Iloilo City Campus admission flyer.
 */

require_once __DIR__ . '/../config/app.php';
require_once ROOT_PATH . '/core/Database.php';

$pdo = Database::getInstance();

echo "Starting synchronization of academic programs to match official ISAT U flyer...\n";

// Ensure all 6 Colleges exist
$colleges = ['CAS', 'CCI', 'COE', 'CEA', 'CIT', 'CGBE'];
$colStmt = $pdo->prepare("INSERT INTO colleges (name) VALUES (?) ON DUPLICATE KEY UPDATE id=id");
foreach ($colleges as $c) {
    $colStmt->execute([$c]);
}

// Fetch college IDs
$colMap = [];
$cols = $pdo->query("SELECT id, name FROM colleges")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    $colMap[$c['name']] = (int)$c['id'];
}

$flyerPrograms = [
    // College of Arts and Sciences (CAS)
    'CAS' => [
        ['code' => 'BAEL',    'name' => 'Bachelor of Arts in English Language'],
        ['code' => 'BHS',     'name' => 'Bachelor in Human Services'],
        ['code' => 'BS Bio',  'name' => 'Bachelor of Science in Biology (Biotechnology)'],
        ['code' => 'BSCD',    'name' => 'Bachelor of Science in Community Development'],
        ['code' => 'BS Math', 'name' => 'Bachelor of Science in Mathematics'],
        ['code' => 'BS Met',  'name' => 'Bachelor of Science in Meteorology'],
    ],

    // College of Computing and Informatics (CCI)
    'CCI' => [
        ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
        ['code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
        ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
    ],

    // College of Engineering and Architecture (CEA)
    'CEA' => [
        ['code' => 'BS Arch',  'name' => 'Bachelor of Science in Architecture'],
        ['code' => 'BSCE',     'name' => 'Bachelor of Science in Civil Engineering'],
        ['code' => 'BSEE',     'name' => 'Bachelor of Science in Electrical Engineering'],
        ['code' => 'BSECE',    'name' => 'Bachelor of Science in Electronics Engineering'],
        ['code' => 'BSME',     'name' => 'Bachelor of Science in Mechanical Engineering'],
        ['code' => 'BSABE',    'name' => 'Bachelor of Science in Agricultural and Biosystems Engineering'],
    ],

    // College of Global Business and Enterprise (CGBE)
    'CGBE' => [
        ['code' => 'BSEntrep', 'name' => 'Bachelor of Science in Entrepreneurship'],
        ['code' => 'BSFDM',    'name' => 'Bachelor of Science in Fashion Design and Merchandising'],
        ['code' => 'BSHM',     'name' => 'Bachelor of Science in Hospitality Management'],
        ['code' => 'BSTM',     'name' => 'Bachelor of Science in Tourism Management'],
    ],

    // College of Industrial Technology (CIT)
    'CIT' => [
        ['code' => 'BIT AFT',      'name' => 'Bachelor of Industrial Technology Major in Apparel and Fashion Technology'],
        ['code' => 'BIT ADT',      'name' => 'Bachelor of Industrial Technology Major in Architectural Drafting Technology'],
        ['code' => 'BIT AT',       'name' => 'Bachelor of Industrial Technology Major in Automotive Technology'],
        ['code' => 'BIT BCWT',     'name' => 'Bachelor of Industrial Technology Major in Beauty Care and Wellness Technology'],
        ['code' => 'BIT CT',       'name' => 'Bachelor of Industrial Technology Major in Construction Technology'],
        ['code' => 'BIT Culinary', 'name' => 'Bachelor of Industrial Technology Major in Culinary Technology'],
        ['code' => 'BIT ELT',      'name' => 'Bachelor of Industrial Technology Major in Electrical Technology'],
        ['code' => 'BIT ELX',      'name' => 'Bachelor of Industrial Technology Major in Electronics Technology'],
        ['code' => 'BIT HVAC-RT',  'name' => 'Bachelor of Industrial Technology Major in Heating, Ventilating, Air-Conditioning and Refrigeration Technology'],
        ['code' => 'BIT MT',       'name' => 'Bachelor of Industrial Technology Major in Mechanical Technology'],
        ['code' => 'BIT WFT',      'name' => 'Bachelor of Industrial Technology Major in Welding and Fabrication Technology'],
    ],

    // College of Education (COE)
    'COE' => [
        ['code' => 'BEEd',         'name' => 'Bachelor of Elementary Education'],
        ['code' => 'BSEd Filipino','name' => 'Bachelor of Secondary Education Major in Filipino'],
        ['code' => 'BSEd Math',    'name' => 'Bachelor of Secondary Education Major in Mathematics'],
        ['code' => 'BSEd Science', 'name' => 'Bachelor of Secondary Education Major in Science'],
        ['code' => 'BTLEd HE',     'name' => 'Bachelor of Technology and Livelihood Education Major in Home Economics'],
        ['code' => 'BTLEd IA',     'name' => 'Bachelor of Technology and Livelihood Education Major in Industrial Arts'],
        ['code' => 'BTLEd ICT',    'name' => 'Bachelor of Technology and Livelihood Education Major in Information and Communication Technology'],
        ['code' => 'BTVTEd BCW',   'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Beauty Care and Wellness'],
        ['code' => 'BTVTEd CP',    'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Computer Programming'],
        ['code' => 'BTVTEd FSM',   'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Food and Service Management'],
    ],
];

// Clean out existing programs table so only the official flyer programs remain
$pdo->exec("DELETE FROM programs");

// Insert official flyer programs
$progStmt = $pdo->prepare("
    INSERT INTO programs (college_id, name, code)
    VALUES (?, ?, ?)
");

$totalInserted = 0;
foreach ($flyerPrograms as $collegeName => $programs) {
    $cid = $colMap[$collegeName] ?? null;
    if (!$cid) continue;

    foreach ($programs as $p) {
        $progStmt->execute([$cid, $p['name'], $p['code']]);
        $totalInserted++;
    }
}

// Map any existing student course codes to official flyer codes
$pdo->exec("UPDATE students SET college = 'CEA' WHERE college = 'COE' AND course IN ('BSME', 'BSECE', 'BSCE')");
$pdo->exec("UPDATE students SET college = 'CCI' WHERE college = 'CAS' AND course IN ('BSCS', 'BSIT')");
$pdo->exec("UPDATE students SET course = 'BIT AT' WHERE course IN ('BSAT', 'BSAMT', 'BIT AT')");
$pdo->exec("UPDATE students SET course = 'BIT Culinary' WHERE course IN ('BSFT', 'BIT FT', 'BIT Culinary')");
$pdo->exec("UPDATE students SET course = 'BIT AFT' WHERE course IN ('BIT FAT', 'BIT AFT')");
$pdo->exec("UPDATE students SET course = 'BIT BCWT' WHERE course IN ('BIT Cosmetology', 'BIT BCWT')");

// Also update any signatory scopes if needed
$pdo->exec("UPDATE signatories SET scope_value = 'BIT AT' WHERE scope_type = 'course' AND scope_value = 'BSAT'");
$pdo->exec("UPDATE signatories SET scope_value = 'BIT Culinary' WHERE scope_type = 'course' AND scope_value = 'BSFT'");

echo "Successfully synchronized {$totalInserted} official programs across all 6 colleges!\n";
