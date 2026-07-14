<?php

class Student extends Model
{
    protected string $table = 'students';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO students (student_id, last_name, first_name, email, college, course, year_level, section, password)
            VALUES (:student_id, :last_name, :first_name, :email, :college, :course, :year_level, :section, :password)
        ");
        return $stmt->execute([
            ':student_id' => $data['student_id'],
            ':last_name'  => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':email'      => $data['email'],
            ':college'    => $data['college'],
            ':course'     => $data['course'],
            ':year_level' => $data['year_level'],
            ':section'    => $data['section'],
            ':password'   => '', // Students do not log in
        ]);
    }

    /**
     * Bulk insert students from a parsed CSV array.
     * Each row: [student_id, last_name, first_name, email, college, course, year_level, section]
     * Skips duplicates on student_id or email.
     * Returns [inserted, skipped].
     */
    public function bulkInsertFromCSV(array $rows): array
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO students (student_id, last_name, first_name, email, college, course, year_level, section, password)
            VALUES (:student_id, :last_name, :first_name, :email, :college, :course, :year_level, :section, :password)
        ");
        $inserted = 0;
        $skipped  = 0;
        foreach ($rows as $row) {
            // Require at minimum student_id, last_name, first_name, email
            if (empty($row['student_id']) || empty($row['last_name']) || empty($row['first_name']) || empty($row['email'])) {
                $skipped++;
                continue;
            }
            $ok = $stmt->execute([
                ':student_id' => trim($row['student_id']),
                ':last_name'  => trim($row['last_name']),
                ':first_name' => trim($row['first_name']),
                ':email'      => trim($row['email']),
                ':college'    => trim($row['college'] ?? ''),
                ':course'     => trim($row['course'] ?? ''),
                ':year_level' => (int) ($row['year_level'] ?? 1),
                ':section'    => trim($row['section'] ?? ''),
                ':password'   => '', // Students do not log in
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
        $stmt = $this->db->query("SELECT * FROM students ORDER BY last_name ASC, first_name ASC");
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
            ORDER BY last_name ASC, first_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }
}
