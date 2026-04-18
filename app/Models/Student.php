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
