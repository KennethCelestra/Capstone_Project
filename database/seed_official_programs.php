<?php
/**
 * Migration / Seed script:
 * Populates official ISAT U Colleges and Academic Programs as provided.
 */

require_once __DIR__ . '/../config/app.php';
require_once ROOT_PATH . '/core/Database.php';

$pdo = Database::getInstance();

echo "Starting official ISAT U academic programs migration...\n";

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

$officialPrograms = [
    // College of Arts and Sciences (CAS)
    'CAS' => [
        ['code' => 'BAEL',    'name' => 'Bachelor of Arts in English Language'],
        ['code' => 'BS Bio',  'name' => 'Bachelor of Science in Biology (with concentration in Biotechnology)'],
        ['code' => 'BSCD',    'name' => 'Bachelor of Science in Community Development'],
        ['code' => 'BHS',     'name' => 'Bachelor of Human Services'],
        ['code' => 'BS Math', 'name' => 'Bachelor of Science in Mathematics'],
        ['code' => 'BS Met',  'name' => 'Bachelor of Science in Meteorology'],
    ],

    // College of Computing and Informatics (CCI)
    'CCI' => [
        ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
        ['code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
        ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
    ],

    // College of Education (COE)
    'COE' => [
        ['code' => 'BEEd',              'name' => 'Bachelor of Elementary Education'],
        ['code' => 'BSEd English',      'name' => 'Bachelor of Secondary Education Major in English'],
        ['code' => 'BSEd Filipino',     'name' => 'Bachelor of Secondary Education Major in Filipino'],
        ['code' => 'BSEd Math',         'name' => 'Bachelor of Secondary Education Major in Mathematics'],
        ['code' => 'BSEd Science',      'name' => 'Bachelor of Secondary Education Major in Science'],
        ['code' => 'BSEd SocStud',      'name' => 'Bachelor of Secondary Education Major in Social Studies'],
        ['code' => 'BTLEd HE',          'name' => 'Bachelor of Technology and Livelihood Education Major in Home Economics'],
        ['code' => 'BTLEd IA',          'name' => 'Bachelor of Technology and Livelihood Education Major in Industrial Arts'],
        ['code' => 'BTLEd ICT',         'name' => 'Bachelor of Technology and Livelihood Education Major in Information and Communication Technology'],
        ['code' => 'BTVTEd Automotive', 'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Automotive Technology'],
        ['code' => 'BTVTEd Drafting',   'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Drafting Technology'],
        ['code' => 'BTVTEd Electrical', 'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Electrical Technology'],
        ['code' => 'BTVTEd Electronics','name' => 'Bachelor of Technical-Vocational Teacher Education Major in Electronics Technology'],
        ['code' => 'BTVTEd FSM',        'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Food and Service Management'],
        ['code' => 'BTVTEd GFD',        'name' => 'Bachelor of Technical-Vocational Teacher Education Major in Garments, Fashion, and Design'],
    ],

    // College of Engineering and Architecture (CEA)
    'CEA' => [
        ['code' => 'BS Arch',  'name' => 'Bachelor of Science in Architecture'],
        ['code' => 'BSEE',     'name' => 'Bachelor of Science in Electrical Engineering'],
        ['code' => 'BSECE',    'name' => 'Bachelor of Science in Electronics Engineering'],
        ['code' => 'BSMarE',   'name' => 'Bachelor of Science in Marine Engineering'],
        ['code' => 'BSME',     'name' => 'Bachelor of Science in Mechanical Engineering'],
        ['code' => 'BSME-AE',  'name' => 'Bachelor of Science in Mechanical Engineering Major in Automotive Engineering'],
    ],

    // College of Industrial Technology (CIT)
    'CIT' => [
        ['code' => 'BIT ADT',        'name' => 'Bachelor of Industrial Technology Major in Architectural Drafting Technology'],
        ['code' => 'BIT AT',         'name' => 'Bachelor of Industrial Technology Major in Automotive Technology'],
        ['code' => 'BIT CT',         'name' => 'Bachelor of Industrial Technology Major in Civil / Construction Technology'],
        ['code' => 'BIT Cosmetology',  'name' => 'Bachelor of Industrial Technology Major in Cosmetology'],
        ['code' => 'BIT ELT',        'name' => 'Bachelor of Industrial Technology Major in Electrical Technology'],
        ['code' => 'BIT ELX',        'name' => 'Bachelor of Industrial Technology Major in Electronics Technology'],
        ['code' => 'BIT FAT',        'name' => 'Bachelor of Industrial Technology Major in Fashion and Apparel Technology'],
        ['code' => 'BIT FT',         'name' => 'Bachelor of Industrial Technology Major in Food Technology'],
        ['code' => 'BIT FCT',        'name' => 'Bachelor of Industrial Technology Major in Furniture and Cabinet Making Technology'],
        ['code' => 'BIT HVAC-RT',    'name' => 'Bachelor of Industrial Technology Major in Heating, Ventilating, Air-Conditioning and Refrigeration Technology'],
        ['code' => 'BIT MT',         'name' => 'Bachelor of Industrial Technology Major in Mechanical Technology'],
        ['code' => 'BIT WFT',        'name' => 'Bachelor of Industrial Technology Major in Welding and Fabrication Technology'],
        ['code' => 'BSAT',           'name' => 'Bachelor of Science in Automotive Technology'],
        ['code' => 'BSELT',          'name' => 'Bachelor of Science in Electrical Technology'],
        ['code' => 'BSELX',          'name' => 'Bachelor of Science in Electronics Technology'],
    ],

    // College of Global Business and Enterprise (CGBE)
    'CGBE' => [
        ['code' => 'BSEntrep', 'name' => 'Bachelor of Science in Entrepreneurship'],
        ['code' => 'BSFDM',    'name' => 'Bachelor of Science in Fashion Design and Merchandising'],
        ['code' => 'BSHM',     'name' => 'Bachelor of Science in Hospitality Management'],
        ['code' => 'BSTM',     'name' => 'Bachelor of Science in Tourism Management'],
    ],
];

// Clean up misplaced old programs in wrong colleges:
// E.g. BSME and BSECE wrongly attached to COE (they belong to CEA)
// BSCS, BSIT wrongly attached to CAS (they belong to CCI)
$misplacedCleanups = [
    ['CAS', 'BSCS'],
    ['CAS', 'BSIT'],
    ['CAS', 'BSAM'],
    ['COE', 'BSME'],
    ['COE', 'BSECE'],
    ['CEA', 'BSArch'],
    ['CIT', 'BSAMT'],
    ['CIT', 'BSFT'],
    ['CGBE', 'BSBA'],
];
foreach ($misplacedCleanups as [$cName, $pCode]) {
    if (isset($colMap[$cName])) {
        $pdo->prepare("DELETE FROM programs WHERE college_id = ? AND code = ?")->execute([$colMap[$cName], $pCode]);
    }
}

// Insert / update official programs
$progStmt = $pdo->prepare("
    INSERT INTO programs (college_id, name, code)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name)
");

$totalInserted = 0;
foreach ($officialPrograms as $collegeName => $programs) {
    $cid = $colMap[$collegeName] ?? null;
    if (!$cid) continue;

    foreach ($programs as $p) {
        $progStmt->execute([$cid, $p['name'], $p['code']]);
        $totalInserted++;
    }
}

// Also fix dummy students college mapping if they were under misplaced combinations
$pdo->exec("UPDATE students SET college = 'CEA' WHERE college = 'COE' AND course IN ('BSME', 'BSECE')");
$pdo->exec("UPDATE students SET college = 'CCI' WHERE college = 'CAS' AND course IN ('BSCS', 'BSIT')");
$pdo->exec("UPDATE students SET course = 'BSAT' WHERE course = 'BSAMT'");

echo "Successfully seeded {$totalInserted} official programs across all 6 colleges!\n";
