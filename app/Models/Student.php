<?php

class Student extends Model
{
    protected string $table = 'students';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO students (student_id, full_name, email, course, year_level, section, password)
            VALUES (:student_id, :full_name, :email, :course, :year_level, :section, :password)
        ");
        return $stmt->execute([
            ':student_id' => $data['student_id'],
            ':full_name'  => $data['full_name'],
            ':email'      => $data['email'],
            ':course'     => $data['course'],
            ':year_level' => $data['year_level'],
            ':section'    => $data['section'],
            ':password'   => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Bulk insert students from a parsed CSV array.
     * Each row: [student_id, full_name, email, course, year_level, section]
     * Skips duplicates on student_id or email.
     * Returns [inserted, skipped].
     */
    public function bulkInsertFromCSV(array $rows): array
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO students (student_id, full_name, email, course, year_level, section, password)
            VALUES (:student_id, :full_name, :email, :course, :year_level, :section, :password)
        ");
        $inserted = 0;
        $skipped  = 0;
        foreach ($rows as $row) {
            // Require at minimum student_id, full_name, email
            if (empty($row['student_id']) || empty($row['full_name']) || empty($row['email'])) {
                $skipped++;
                continue;
            }
            $ok = $stmt->execute([
                ':student_id' => trim($row['student_id']),
                ':full_name'  => trim($row['full_name']),
                ':email'      => trim($row['email']),
                ':course'     => trim($row['course'] ?? ''),
                ':year_level' => (int) ($row['year_level'] ?? 1),
                ':section'    => trim($row['section'] ?? ''),
                ':password'   => password_hash(trim($row['student_id']), PASSWORD_DEFAULT), // default password = student_id
            ]);
            if ($ok && $stmt->rowCount() > 0) {
                $inserted++;
            } else {
                $skipped++;
            }
        }
        return [$inserted, $skipped];
    }

    /**
     * Insert a set of pre-defined dummy students for testing.
     * Returns [inserted, skipped].
     */
    public function insertDummies(): array
    {
        $dummies = [
            ['2024-00001', 'Juan Dela Cruz',       'juan.delacruz@school.edu',   'BSIT', 2, 'A'],
            ['2024-00002', 'Maria Santos',          'maria.santos@school.edu',    'BSIT', 2, 'A'],
            ['2024-00003', 'Pedro Reyes',           'pedro.reyes@school.edu',     'BSCS', 3, 'B'],
            ['2024-00004', 'Ana Garcia',            'ana.garcia@school.edu',      'BSCS', 1, 'C'],
            ['2024-00005', 'Carlo Mendoza',         'carlo.mendoza@school.edu',   'BSIT', 4, 'A'],
            ['2024-00006', 'Liza Bautista',         'liza.bautista@school.edu',   'BSIS', 2, 'B'],
            ['2024-00007', 'Ramon Villanueva',      'ramon.villanueva@school.edu','BSIT', 3, 'C'],
            ['2024-00008', 'Kristine Fernandez',    'kristine.fernandez@school.edu','BSCS', 1, 'A'],
            ['2024-00009', 'Mark Ramos',            'mark.ramos@school.edu',      'BSIS', 4, 'B'],
            ['2024-00010', 'Alyssa Torres',         'alyssa.torres@school.edu',   'BSIT', 2, 'C'],
        ];

        $rows = [];
        foreach ($dummies as $d) {
            $rows[] = [
                'student_id' => $d[0],
                'full_name'  => $d[1],
                'email'      => $d[2],
                'course'     => $d[3],
                'year_level' => $d[4],
                'section'    => $d[5],
            ];
        }
        return $this->bulkInsertFromCSV($rows);
    }

    public function findByStudentId(string $studentId): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM students ORDER BY full_name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get all students NOT yet enrolled in given clearance.
     */
    public function findNotInClearance(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM students
            WHERE id NOT IN (
                SELECT student_id FROM clearance_students WHERE clearance_id = ?
            )
            ORDER BY full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }
}
